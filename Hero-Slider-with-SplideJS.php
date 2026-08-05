<?php

/*
 * Plugin Name:       Hero Slider with SplideJS
 * Plugin URI:        https://github.com/murslinshehzad-code/Hero-Slider-with-SplideJS
 * Description:       Adds a customizable hero slider using SplideJS. Includes a shortcode [hero_slider] to display a responsive slider with image, heading, description, and button. Lightweight and easy to use.
 * Version:           1.5.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Murslin Shehzad
 * Author URI:        https://github.com/murslinshehzad-code
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hero-slider-splide
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


 // Define global plugin path
define('HERO_SLIDER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Define global plugin URL
define('HERO_SLIDER_PLUGIN_URL', plugin_dir_url(__FILE__));



// Web-safe font choices offered in the Appearance settings and validated against
// for shortcode attribute overrides, so only known-safe CSS ever reaches inline styles.
function hero_slider_get_font_choices() {
    return array(
        ''                                     => 'Theme Default',
        "Arial, Helvetica, sans-serif"          => 'Arial',
        "'Segoe UI', Tahoma, sans-serif"        => 'Segoe UI',
        "Verdana, Geneva, sans-serif"           => 'Verdana',
        "'Trebuchet MS', sans-serif"            => 'Trebuchet MS',
        "Georgia, 'Times New Roman', serif"     => 'Georgia',
        "'Times New Roman', Times, serif"       => 'Times New Roman',
        "'Courier New', Courier, monospace"     => 'Courier New',
    );
}

// Default shape of the appearance option, shared by the settings page and the shortcode renderer.
function hero_slider_get_appearance_defaults() {
    return array(
        'show_heading'   => 1,
        'show_paragraph' => 1,
        'show_overlay'   => 1,

        'heading_color'       => '',
        'heading_font_size'   => '',
        'heading_font_family' => '',

        'paragraph_color'       => '',
        'paragraph_font_size'   => '',
        'paragraph_font_family' => '',

        'button_text_color'  => '',
        'button_bg_color'    => '',
        'button_font_size'   => '',
        'button_font_family' => '',
    );
}

//including the class-hero-slider.php file
require_once HERO_SLIDER_PLUGIN_DIR . 'includes/class-hero-slider.php';
require_once HERO_SLIDER_PLUGIN_DIR . 'includes/class-hero-slider-settings.php';



//Enqueuing Styles and Scripts
add_action( 'wp_enqueue_scripts', 'hero_slider_enqueue_assets' );
function hero_slider_enqueue_assets(){
    wp_enqueue_style('hero-slide-css', HERO_SLIDER_PLUGIN_URL . 'assets/css/hero-slide.css', array(), filemtime(HERO_SLIDER_PLUGIN_DIR . 'assets/css/hero-slide.css'));
    wp_enqueue_style( 'splide-css', HERO_SLIDER_PLUGIN_URL.'assets/css/splide.min.css');

    wp_enqueue_script( 'splide-js', HERO_SLIDER_PLUGIN_URL.'assets/js/splide.min.js', array(), null, true);

    // Depends on splide-js so the Splide library is guaranteed to load first.
    // Autoplay/interval are no longer passed globally here — each [hero_slider] instance
    // carries its own slider's settings via data-autoplay/data-interval attributes,
    // since a page can now show more than one independently-configured slider.
    wp_enqueue_script( 'hero-slide-js', HERO_SLIDER_PLUGIN_URL.'assets/js/hero-slide.js', array('jquery', 'splide-js'), filemtime(HERO_SLIDER_PLUGIN_DIR . 'assets/js/hero-slide.js'), true);
}

function hero_slider_admin_styles() {
    wp_enqueue_style('hero-slide-admin-css', HERO_SLIDER_PLUGIN_URL . 'assets/css/hero-slide-admin.css', array(), filemtime(HERO_SLIDER_PLUGIN_DIR . 'assets/css/hero-slide-admin.css'));
}

add_action('admin_enqueue_scripts', 'hero_slider_admin_styles');




new Hero_Slider_Shortcode();
new Hero_Slider_Settings();
