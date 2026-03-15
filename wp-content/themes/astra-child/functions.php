<?php
function astra__child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'astra__child_theme_enqueue_styles' );

/**
 * Define Global Variables
 */
$GLOBALS['insta_account'] = 'https://www.instagram.com/';
$GLOBALS['twitter_account'] = 'https://x.com/slayai_app/';
$GLOBALS['youtube_account'] = 'https://youtube.com';
$GLOBALS['app_store'] = 'https://apps.apple.com/in/app/slay-ai-dating-assistant/id6736589332';
$GLOBALS['play_store'] = 'https://play.google.com/store/apps/details?id=app.slayai.slayapp';



/**
 *  Custom CSS and HTML for homepage carousel
*/
add_shortcode('homepage_landing', 'homepage_landing_shortcode');

function homepage_landing_shortcode() {
    //add_action('wp_footer', 'add_custom_scripts');
    ob_start();
    ?>


<div class="homepage-div">
    <div class="homepage-text-div">
        <div class="headtextspan-homepage-text-div">
            The smarter way to order food<span class="focus-headtextspan-homepage-text-div"><b>. </b></span>
        </div>
        <div class="subtextspan-homepage-text-div">
            AI gets you exactly what you need.
        </div>
        <br>
        <div class="subtextspan-homepage-text-div">
            <i>Coming Soon</i>
            <br>
            <i></i>
            <br>
        </div>
        <div class="custom-center-div">
            
            <a href="<?php echo $GLOBALS['insta_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/instagram_icon.png')?>" alt="Instagram" style="width: 35px; height: 35px;">
            </a>
            <!--
            <a href="<?php echo $GLOBALS['twitter_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/x_icon.png')?>" alt="Twitter" style="width: 35px; height: 35px;">
            </a>
            <a href="<?php echo $GLOBALS['youtube_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/youtube_icon.png')?>" alt="YouTube" style="width: 35px; height: 35px;">
            </a>
            -->
        </div>

    </div>
    <div class="homepage-images-div">
        <div class="mobile-download-buttons">
            <div class="download-links">
                <a href="<?php echo $GLOBALS['app_store']?>" target="_blank">
                    <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="Download on the App Store" class="store-button appstore-button">
                </a>
                <a href="<?php echo $GLOBALS['play_store']?>" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="store-button playstore-button">
                </a>
            </div>
        </div>

        <img src="<?php echo home_url('/wp-content/uploads/airgrab_ss.png')?>" alt="Sample Image" class="app-ss-image">

        <div class="download-buttons">
            <div class="download-links">
                <a href="<?php echo $GLOBALS['app_store']?>" target="_blank">
                    <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="Download on the App Store" class="store-button appstore-button">
                </a>
                <a href="<?php echo $GLOBALS['play_store']?>" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="store-button playstore-button">
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mobile-social-icons-div">
    <div class="custom-center-div">
            <a href="<?php echo $GLOBALS['insta_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/instagram_icon.png')?>" alt="Instagram" style="width: 35px; height: 35px;">
            </a>
            <!--
            <a href="<?php echo $GLOBALS['twitter_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/twitter_icon.png')?>" alt="Twitter" style="width: 35px; height: 35px;">
            </a>
            <a href="<?php echo $GLOBALS['youtube_account']?>" target="_blank" style="margin: 0 10px;">
                <img src="<?php echo home_url('/wp-content/uploads/youtube_icon.png')?>" alt="YouTube" style="width: 35px; height: 35px;">
            </a>
            -->
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



?>
