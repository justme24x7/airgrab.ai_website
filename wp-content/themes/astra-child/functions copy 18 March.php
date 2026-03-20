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
// add_shortcode('waitlist_form', 'waitlist_form_shortcode');
add_shortcode('priority_waitlist_form', 'priority_waitlist_form_shortcode');
add_shortcode('waitlisted_users', 'waitlisted_users_shortcode');
add_shortcode('team_page', 'team_page_shortcode');

/**
 * Waitlist form submit handler (works for logged-in + logged-out users).
 */
add_action('admin_post_waitlist_submit', 'handle_waitlist_submit');
add_action('admin_post_nopriv_waitlist_submit', 'handle_waitlist_submit');

/**
 * Export waitlisted users as CSV (admin-only).
 */
add_action('admin_post_waitlist_export_csv', 'waitlist_export_csv');
add_action('admin_post_nopriv_waitlist_export_csv', 'waitlist_export_csv');

function waitlist_export_csv() {
    if ( ! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'waitlist_export_csv') ) {
        wp_die('Invalid request.', 400);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'waitlist';
    $rows = $wpdb->get_results("SELECT ID, user_full_name, user_phone, user_pincode, misc_desc, waitlist_type, device_type, created_at FROM {$table} ORDER BY ID DESC", ARRAY_A);

    $filename = 'waitlisted_users_' . gmdate('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // Header row
    fputcsv($out, array('ID', 'user_full_name', 'user_phone', 'user_pincode', 'misc_desc', 'waitlist_type', 'device_type', 'created_at'));
    if (is_array($rows)) {
        foreach ($rows as $row) {
            fputcsv($out, array(
                $row['ID'] ?? '',
                $row['user_full_name'] ?? '',
                $row['user_phone'] ?? '',
                $row['user_pincode'] ?? '',
                $row['misc_desc'] ?? '',
                $row['waitlist_type'] ?? '',
                $row['device_type'] ?? '',
                $row['created_at'] ?? '',
            ));
        }
    }
    fclose($out);
    exit;
}

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
    $device_raw = isset($_POST['waitlist_device_type']) ? wp_unslash($_POST['waitlist_device_type']) : '';

    $name = sanitize_text_field($name_raw);
    $phone_digits = preg_replace('/\D+/', '', $phone_raw);
    $pincode_digits = preg_replace('/\D+/', '', $pincode_raw);
    $misc_desc = sanitize_text_field($misc_raw);
    $waitlist_type = sanitize_key($type_raw);
    $waitlist_type = ($waitlist_type === 'priority') ? 'priority' : 'normal';
    $device_type = in_array($device_raw, array('ios', 'android'), true) ? $device_raw : 'NA';

    

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
            'device_type'    => $device_type,
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted === false) {
        wp_safe_redirect( add_query_arg(array('waitlist_status' => 'error', 'reason' => 'db'), $redirect_base) );
        exit;
    }

    wp_safe_redirect( add_query_arg(array('waitlist_status' => 'success'), $redirect_base) );
    exit;
}

function waitlisted_users_shortcode() {
    // if ( ! current_user_can('manage_options') ) {
    //     return '<div class="waitlist-submit-error">Not allowed.</div>';
    // }

    global $wpdb;
    $table = $wpdb->prefix . 'waitlist';
    $rows = $wpdb->get_results("SELECT ID, user_full_name, user_phone, user_pincode, misc_desc, waitlist_type, device_type, created_at FROM {$table} ORDER BY ID DESC", ARRAY_A);

    $csv_url = wp_nonce_url(
        admin_url('admin-post.php?action=waitlist_export_csv'),
        'waitlist_export_csv'
    );

    ob_start();
    ?>
    <div class="waitlist-section" style="max-width: 100%; padding: 0 5%;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <h2 class="waitlist-section-title" style="margin:0;">Waitlisted Users</h2>
            <a class="waitlist-cta-button" href="<?php echo esc_url($csv_url); ?>">Download CSV</a>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">ID</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">user_full_name</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">user_phone</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">user_pincode</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">misc_desc</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">waitlist_type</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">device_type</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #333;">created_at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) { ?>
                        <tr>
                            <td colspan="8" style="padding:10px; color:#999999;">No users in waitlist yet.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($rows as $row) { ?>
                            <tr>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['ID'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['user_full_name'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['user_phone'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['user_pincode'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['misc_desc'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['waitlist_type'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['device_type'] ?? ''); ?></td>
                                <td style="padding:10px; border-bottom:1px solid #222;"><?php echo esc_html($row['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function render_waitlist_form_block($args = array()) {
    $defaults = array(
        'waitlist_type'      => 'normal',
        'include_misc'       => true,
        'include_device_type'=> false,
        'autoscroll'         => true,
        'title_text'        => 'Join the Waitlist',
        'intro_text'        => 'Beta launching soon in India!',
        'benefits'          => array(
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
    <?php if ( ! empty($args['autoscroll']) ) { ?>
    <script>
    (function() {
        function scrollToWaitlistHeader() {
            var header = document.getElementById('join-waitlist');
            if (!header) return;

            // Don't scroll to elements inside a hidden container (e.g. homepage embedded form).
            var hiddenAncestor = header.closest && header.closest('[hidden]');
            if (hiddenAncestor) return;

            header.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scrollToWaitlistHeader);
        } else {
            scrollToWaitlistHeader();
        }
    })();
    </script>
    <?php } ?>
    <?php if ($status === 'success') { ?>
        <div class="waitlist-success-message">Thank you! We will send you the access code soon. Cheers!</div>
    <?php } else { ?>
        <div class="waitlist-intro"><?php echo wp_kses($args['intro_text'], array('br' => array())); ?></div>
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
                <span class="waitlist-field-desc">The access code will be sent to this phone number. This will also be your login ID. Airgrab will be available in <b> India </b> first.</span>
                <div class="waitlist-field-error" id="waitlist-phone-error" aria-live="polite"></div>
            </div>
            <div class="waitlist-field">
                <label for="waitlist-pincode">Pincode</label>
                <input type="text" id="waitlist-pincode" name="waitlist_pincode" placeholder="e.g. 560066" required inputmode="numeric" autocomplete="postal-code" maxlength="6" pattern="^[0-9]{6}$">
                <span class="waitlist-field-desc">Helps ensure service availability for your area.</span>
                <div class="waitlist-field-error" id="waitlist-pincode-error" aria-live="polite"></div>
            </div>
            <?php if ($args['include_device_type']) { ?>
                <div class="waitlist-field">
                    <label for="waitlist-device-type">Device type</label>
                    <select id="waitlist-device-type" name="waitlist_device_type" required>
                        <option value="ios">iOS</option>
                        <option value="android">Android</option>
                    </select>
                    <span class="waitlist-field-desc">Get notified when the app is available for your device.</span>
                </div>
            <?php } else { ?>
                <input type="hidden" name="waitlist_device_type" value="NA">
            <?php } ?>
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
            From "I want.." to <span class="focus-headtextspan-homepage-text-div"><b>  Delivered. </b></span>
        </div>
        <div class="subtextspan-homepage-text-div">
          Order online like never before.
        <br>
          Discover, pay, track - without leaving the chat.
        <br> 
          Agentic commerce, built for <span class="focus-headtextspan-homepage-text-div"><b> real life. </b></span>
        </div>
        <br>
        <div class="launch-row subtextspan-homepage-text-div launch-row-desktop">
            <span>Beta dropping soon!</span>
            <a href="#join-waitlist" class="waitlist-cta-button js-homepage-waitlist-toggle">JOIN WAITLIST</a>
        </div>
    </div>
    <div class="homepage-images-div">
        <img src="<?php echo home_url('/wp-content/uploads/ag_ss_e.png')?>" alt="Sample Image" class="app-ss-image"> 
    </div>
    <!-- <div class="launch-row subtextspan-homepage-text-div launch-row-mobile">
        <span>Beta dropping soon! <br></span>
        <a href="#join-waitlist" class="waitlist-cta-button js-homepage-waitlist-toggle">JOIN WAITLIST</a>
    </div> -->
</div>

<br>
<br>
<div id="homepage-waitlist-container" class="homepage-waitlist-container" hidden>
    <?php
    echo render_waitlist_form_block(array(
        'waitlist_type' => 'normal',
        'include_misc'  => true,
        'title_text'    => 'Join the Waitlist',
        'intro_text'    => 'We\'re launching a Beta for limited users in India. Starting with something you already order online.<br>Reserve your spot now!',
    ));
    ?>
</div>

<script>
(function() {
    function showAndScrollToWaitlist(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();

        var container = document.getElementById('homepage-waitlist-container');
        if (container) container.hidden = false;

        var header = document.getElementById('join-waitlist');
        if (header && typeof header.scrollIntoView === 'function') {
            header.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var toggles = document.querySelectorAll('.js-homepage-waitlist-toggle');
        if (!toggles || !toggles.length) return;
        for (var i = 0; i < toggles.length; i++) {
            toggles[i].addEventListener('click', showAndScrollToWaitlist);
        }
    });
})();
</script>


<?php 
return ob_get_clean();
}

function team_page_shortcode() {
    $members = array(
        array(
            'name' => 'Animesh Swain',
            'designation' => 'Co-founder & CEO',
            'photo_url' => home_url('/wp-content/uploads/animesh_aai.png'),
            'linkedin_url' => 'https://www.linkedin.com/in/akswain/',
        ),
        array(
            'name' => 'Nidhi Yadav',
            'designation' => 'Co-founder & CTO',
            'photo_url' => home_url('/wp-content/uploads/nidhi_aai.png'),
            'linkedin_url' => 'https://www.linkedin.com/in/nidhi1307/',
        ),
    );

    ob_start();
    ?>
    <div class="team-page-section">
        <div class="team-grid">
            <?php foreach ($members as $member) { ?>
                <div class="team-member-card">
                    <img class="team-member-photo" src="<?php echo esc_url($member['photo_url']); ?>" alt="<?php echo esc_attr($member['name']); ?>">
                    <h4 class="team-member-name"><?php echo esc_html($member['name']); ?></h4>
                    <p class="team-member-role"><?php echo esc_html($member['designation']); ?></p>
                    <a class="team-member-linkedin" href="<?php echo esc_url($member['linkedin_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr('LinkedIn profile of ' . $member['name']); ?>">
                        <img class="team-member-linkedin-icon" src="<?php echo esc_url(home_url('/wp-content/uploads/linkedin_icon.png')); ?>" alt="LinkedIn">
                    </a>
                </div>
            <?php } ?>
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

// function waitlist_form_shortcode() {
//     return render_waitlist_form_block(array(
//         'waitlist_type' => 'normal',
//         'include_misc'  => true,
//         'title_text'    => 'Join the Waitlist',
//         'intro_text'    => 'Beta launching soon in India! Be the the first to experience it.',
//     ));
// }

function priority_waitlist_form_shortcode() {
    return render_waitlist_form_block(array(
        'waitlist_type'       => 'priority',
        'include_misc'       => false,
        'include_device_type'=> true,
        'title_text'         => 'Join Priority Access',
        'intro_text'    => 'Priority access is for people who have actively helped to shape the product.',
        'benefits'      => array(
            'Exclusive discounts and early feature drops.',
            'Be the first to receive your access code.',
        ),
    ));
}

?>
