<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'hero_slider_slides' );
delete_option( 'hero_slider_general_settings' );
