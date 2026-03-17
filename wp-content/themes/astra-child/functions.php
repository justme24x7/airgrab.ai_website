<?php
function astra__child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'astra__child_theme_enqueue_styles' );

/**
 * Define Global Variables
 */
$GLOBALS['insta_account'] = 'https://www.instagram.com/slayai.app/';
$GLOBALS['twitter_account'] = 'https://x.com/slayai_app/';
$GLOBALS['youtube_account'] = 'https://youtube.com';
$GLOBALS['app_store'] = 'https://apps.apple.com/in/app/slay-ai-dating-assistant/id6736589332';
$GLOBALS['play_store'] = 'https://play.google.com/store/apps/details?id=app.slayai.slayapp';

/**
 *  Custom CSS and HTML for homepage carousel
*/
add_shortcode('homepage_landing', 'homepage_landing_shortcode');
add_shortcode('waitlist_form', 'waitlist_form_shortcode');
add_shortcode('priority_waitlist_form', 'priority_waitlist_form_shortcode');

/**
 * Waitlist form submit handler (works for logged-in + logged-out users).
 */
add_action('admin_post_waitlist_submit', 'handle_waitlist_submit');
add_action('admin_post_nopriv_waitlist_submit', 'handle_waitlist_submit');

function handle_waitlist_submit() {
    if ( ! isset($_POST['waitlist_nonce']) || ! wp_verify_nonce($_POST['waitlist_nonce'], 'waitlist_submit') ) {
        wp_safe_redirect( home_url('/waitlist?waitlist_status=error&reason=nonce#join-waitlist') );
        exit;
    }

    $name_raw = isset($_POST['waitlist_name']) ? wp_unslash($_POST['waitlist_name']) : '';
    $phone_raw = isset($_POST['waitlist_phone']) ? wp_unslash($_POST['waitlist_phone']) : '';
    $pincode_raw = isset($_POST['waitlist_pincode']) ? wp_unslash($_POST['waitlist_pincode']) : '';
    $misc_raw = isset($_POST['waitlist_misc']) ? wp_unslash($_POST['waitlist_misc']) : '';
    $type_raw = isset($_POST['waitlist_type']) ? wp_unslash($_POST['waitlist_type']) : '';
    $redirect_raw = isset($_POST['waitlist_redirect']) ? wp_unslash($_POST['waitlist_redirect']) : '';

    $name = sanitize_text_field($name_raw);
    $phone_digits = preg_replace('/\D+/', '', $phone_raw);
    $pincode_digits = preg_replace('/\D+/', '', $pincode_raw);
    $misc_desc = sanitize_text_field($misc_raw);
    $waitlist_type = sanitize_key($type_raw);

    // Only allow known types.
    if ($waitlist_type !== 'priority') {
        $waitlist_type = 'normal';
    }

    // Redirect back to the originating page (fallback to /waitlist).
    $redirect_base = $redirect_raw !== '' ? esc_url_raw($redirect_raw) : '';
    if ($redirect_base === '') {
        $redirect_base = home_url('/waitlist');
    }
    // Keep users anchored to the heading.
    $redirect_base = preg_replace('/#.*$/', '', $redirect_base) . '#join-waitlist';

    if ($name === '' || $phone_digits === '' || $pincode_digits === '') {
        wp_safe_redirect( add_query_arg(array('waitlist_status' => 'error', 'reason' => 'required'), $redirect_base) );
        exit;
    }

    // Phone validation: must be 10 digits.
    if (strlen($phone_digits) !== 10) {
        wp_safe_redirect( add_query_arg(array('waitlist_status' => 'error', 'reason' => 'phone'), $redirect_base) );
        exit;
    }

    // Pincode validation: 6-digit.
    if (strlen($pincode_digits) !== 6) {
        wp_safe_redirect( add_query_arg(array('waitlist_status' => 'error', 'reason' => 'pincode'), $redirect_base) );
        exit;
    }

    if ($misc_desc === '') {
        $misc_desc = 'NA';
    }

    global $wpdb;
    $table = $wpdb->prefix . 'waitlist';

    $inserted = $wpdb->insert(
        $table,
        array(
            'user_full_name' => $name,
            'user_phone'     => $phone_digits,
            'user_pincode'   => $pincode_digits,
            'misc_desc'      => $misc_desc,
            'waitlist_type'  => $waitlist_type,
        ),
        array('%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted === false) {
        wp_safe_redirect( add_query_arg(array('waitlist_status' => 'error', 'reason' => 'db'), $redirect_base) );
        exit;
    }

    wp_safe_redirect( add_query_arg(array('waitlist_status' => 'success'), $redirect_base) );
    exit;
}

function render_waitlist_form_block($args = array()) {
    $defaults = array(
        'waitlist_type' => 'normal',
        'include_misc'  => true,
        'title_text'    => 'Join the Waitlist',
        'intro_text'    => 'Beta launching soon in India!',
        'benefits'      => array(
            'Get early access, exclusive launch discounts and priority support.',
            'Join the community to shape the future of Agentic Commerce in India.',
        ),
    );
    $args = wp_parse_args($args, $defaults);

    $status = isset($_GET['waitlist_status']) ? sanitize_key($_GET['waitlist_status']) : '';
    $reason = isset($_GET['reason']) ? sanitize_key($_GET['reason']) : '';
    $error_msg = '';
    if ($status === 'error') {
        if ($reason === 'required') $error_msg = 'Please fill all required fields.';
        if ($reason === 'phone') $error_msg = 'Phone number must be a 10-digit number.';
        if ($reason === 'pincode') $error_msg = 'Pincode must be a 6-digit number.';
        if ($reason === 'nonce' || $reason === 'db' || $reason === '') {
            $error_msg = 'Something went wrong. Please refresh the page and try again.';
        }
    }

    // The current URL is used for redirect after submit.
    $current_url = (is_ssl() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
    $current_url = esc_url_raw($current_url);

    ob_start();
    ?>
<div class="waitlist-section">
    <h2 class="waitlist-section-title" id="join-waitlist"><?php echo esc_html($args['title_text']); ?></h2>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('join-waitlist');
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    })();
    </script>
    <?php if ($status === 'success') { ?>
        <div class="waitlist-success-message">Thank you! We will send you the access code soon. Cheers!</div>
    <?php } else { ?>
        <?php echo esc_html($args['intro_text']); ?>
        <br> <br>
        <ul class="waitlist-benefits">
            <?php foreach ($args['benefits'] as $benefit) { ?>
                <li><?php echo esc_html($benefit); ?></li>
            <?php } ?>
        </ul>
        <form class="waitlist-form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="waitlist_submit">
            <input type="hidden" name="waitlist_type" value="<?php echo esc_attr($args['waitlist_type']); ?>">
            <input type="hidden" name="waitlist_redirect" value="<?php echo esc_attr($current_url); ?>">
            <?php wp_nonce_field('waitlist_submit', 'waitlist_nonce'); ?>
            <div class="waitlist-field">
                <label for="waitlist-name">Name</label>
                <input type="text" id="waitlist-name" name="waitlist_name" placeholder="Your name" required>
            </div>
            <div class="waitlist-field">
                <label for="waitlist-phone">Phone number (+91)</label>
                <input type="tel" id="waitlist-phone" name="waitlist_phone" placeholder="e.g. 9876543210" required inputmode="numeric" autocomplete="tel" maxlength="10" pattern="^[0-9]{10}$">
                <span class="waitlist-field-desc">The access code will be sent to this phone number. This will also be your login ID. The beta version will be available in <b> India </b> first.</span>
                <div class="waitlist-field-error" id="waitlist-phone-error" aria-live="polite"></div>
            </div>
            <div class="waitlist-field">
                <label for="waitlist-pincode">Pincode</label>
                <input type="text" id="waitlist-pincode" name="waitlist_pincode" placeholder="e.g. 560066" required inputmode="numeric" autocomplete="postal-code" maxlength="6" pattern="^[0-9]{6}$">
                <span class="waitlist-field-desc">Helps ensure service availability for your area.</span>
                <div class="waitlist-field-error" id="waitlist-pincode-error" aria-live="polite"></div>
            </div>
            <?php if ($args['include_misc']) { ?>
                <div class="waitlist-field">
                    <label for="waitlist-misc">Anything else you'd like to share... or not? <span class="optional">(optional)</span></label>
                    <textarea id="waitlist-misc" name="waitlist_misc" class="waitlist-misc-textarea" placeholder="E.g. Foodie and techie in Bengaluru" maxlength="500" rows="4"></textarea>
                    <span class="waitlist-field-desc">We are trying to keep the waitlist diverse and inclusive. Knowing a bit about you helps. Don't know what to share? Just share your social media URL.</span>
                </div>
            <?php } ?>
            <button type="submit" class="waitlist-submit">Submit</button>
            <?php if ($error_msg !== '') { ?>
                <div class="waitlist-submit-error"><?php echo esc_html($error_msg); ?></div>
            <?php } ?>
        </form>
        <script>
        (function() {
            function digitsOnly(s) { return (s || '').replace(/\D+/g, ''); }
            function setErr(el, msg) { if (el) el.textContent = msg || ''; }
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.querySelector('.waitlist-form');
                if (!form) return;
                var phone = document.getElementById('waitlist-phone');
                var pin = document.getElementById('waitlist-pincode');
                var phoneErr = document.getElementById('waitlist-phone-error');
                var pinErr = document.getElementById('waitlist-pincode-error');
                form.addEventListener('submit', function(e) {
                    setErr(phoneErr, '');
                    setErr(pinErr, '');
                    var ok = true;

                    var phoneVal = digitsOnly(phone && phone.value);
                    var pinVal = digitsOnly(pin && pin.value);

                    if (!phoneVal || phoneVal.length !== 10) {
                        setErr(phoneErr, 'Phone number must be a 10-digit number.');
                        ok = false;
                    }
                    if (!pinVal || pinVal.length !== 6) {
                        setErr(pinErr, 'Pincode must be a 6-digit number.');
                        ok = false;
                    }

                    if (!ok) {
                        e.preventDefault();
                        return;
                    }

                    var submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';
                    }
                });
            });
        })();
        </script>
    <?php } ?>
</div>
<?php
    return ob_get_clean();
}

function homepage_landing_shortcode() {
    //add_action('wp_footer', 'add_custom_scripts');
    ob_start();
    ?>


<div class="homepage-div">
    <div class="homepage-text-div">
        <div class="headtextspan-homepage-text-div">
            Agentic <span class="focus-headtextspan-homepage-text-div"><b>Commerce </b></span> like no other
        </div>
        <div class="subtextspan-homepage-text-div">
            Personal AI <span class="focus-headtextspan-homepage-text-div"><b> agents </b></span> get you exactly what you need.
        </div>
        <br>
        <div class="launch-row subtextspan-homepage-text-div launch-row-desktop">
            <span>Launching soon!</span>
            <a href="<?php echo esc_url( home_url( '/waitlist#join-waitlist' ) ); ?>" class="waitlist-cta-button">Join Waitlist</a>
        </div>
    </div>
    <div class="homepage-images-div">
        <img src="<?php echo home_url('/wp-content/uploads/test_aai.png')?>" alt="Sample Image" class="app-ss-image"> 
    </div>
    <div class="launch-row subtextspan-homepage-text-div launch-row-mobile">
        <span>Launching soon! <br></span>
        <a href="<?php echo esc_url( home_url( '/waitlist#join-waitlist' ) ); ?>" class="waitlist-cta-button">Join Waitlist</a>
    </div>
</div>


<?php 
return ob_get_clean();
}

/**
 *  Custom CSS and HTML for contact us
*/
add_shortcode('social_links', 'social_links_shortcode');

function social_links_shortcode() {
    //add_action('wp_footer', 'add_custom_scripts');
    ob_start();
    ?>

<div class="custom-center-div">
    <a href="<?php echo $GLOBALS['insta_account']?>" target="_blank" style="margin: 0 10px;">
        <img src="<?php echo home_url('/wp-content/uploads/instagram_icon.png')?>" alt="Instagram" style="width: 35px; height: 35px;">
    </a>
    <a href="<?php echo $GLOBALS['twitter_account']?>" target="_blank" style="margin: 0 10px;">
        <img src="<?php echo home_url('/wp-content/uploads/twitter_icon.png')?>" alt="Twitter" style="width: 35px; height: 35px;">
    </a>
    <a href="<?php echo $GLOBALS['youtube_account']?>" target="_blank" style="margin: 0 10px;">
        <img src="<?php echo home_url('/wp-content/uploads/youtube_icon.png')?>" alt="YouTube" style="width: 35px; height: 35px;">
    </a>
</div>



<?php 
return ob_get_clean();
}

function waitlist_form_shortcode() {
    return render_waitlist_form_block(array(
        'waitlist_type' => 'normal',
        'include_misc'  => true,
        'title_text'    => 'Join the Waitlist',
        'intro_text'    => 'Beta launching soon in India!',
    ));
}

function priority_waitlist_form_shortcode() {
    return render_waitlist_form_block(array(
        'waitlist_type' => 'priority',
        'include_misc'  => false,
        'title_text'    => 'Join Priority Access',
        'intro_text'    => 'Priority access launching soon in India!',
        'benefits'      => array(
            'Priority access to the beta and early feature drops.',
            'Be among the first to receive your invite code.',
        ),
    ));
}

?>
