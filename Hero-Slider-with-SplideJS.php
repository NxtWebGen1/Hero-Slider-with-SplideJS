<?php

/*
 * Plugin Name:       Hero Slider with SplideJS
 * Plugin URI:        https://github.com/NxtWebGen1/Hero-Slider-with-SplideJS
 * Description:       Adds a customizable hero slider using SplideJS. Includes a shortcode [hero_slider] to display a responsive slider with image, heading, description, and button. Lightweight and easy to use.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Murslin Shehzad
 * Author URI:        https://github.com/NxtWebGen1
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hero-slider-splide
 */





 // Define global plugin path
define('HERO_SLIDER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Define global plugin URL
define('HERO_SLIDER_PLUGIN_URL', plugin_dir_url(__FILE__));



//including the class-hero-slider.php file
require_once HERO_SLIDER_PLUGIN_DIR . 'includes/class-hero-slider.php';



//Enqueuing Styles and Scripts
add_action( 'wp_enqueue_scripts', 'hero_slider_enqueue_assets' );
function hero_slider_enqueue_assets(){
    wp_enqueue_style( 'hero-slide-css', HERO_SLIDER_PLUGIN_URL.'assets/css/hero-slide.css');
    wp_enqueue_style( 'splide-css', HERO_SLIDER_PLUGIN_URL.'assets/css/splide.min.css');
    wp_enqueue_script( 'hero-slide-js', HERO_SLIDER_PLUGIN_URL.'assets/js/hero-slide.js', array('jquery'), null, true);
    wp_enqueue_script( 'splide-js', HERO_SLIDER_PLUGIN_URL.'assets/js/splide.min.js', array(), null, true);
}



new Hero_Slider_Shortcode();