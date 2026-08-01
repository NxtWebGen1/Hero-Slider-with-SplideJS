<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'hero_slider_sliders' );
delete_option( 'hero_slider_appearance_settings' );

// Legacy options from before multi-slider support (kept for cleanup on sites
// that uninstall without ever having re-saved settings since upgrading).
delete_option( 'hero_slider_slides' );
delete_option( 'hero_slider_general_settings' );
