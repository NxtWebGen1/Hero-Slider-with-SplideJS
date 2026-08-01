<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Hero_Slider_Settings {

    const SETTINGS_PAGE_HOOK = 'settings_page_hero-slider-settings';

    // Constructor: Attach functions to WordPress hooks
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));   // Add our settings page link under 'Settings' menu
        add_action('admin_init', array($this, 'register_settings'));   // Register settings so WordPress can save the options
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_uploader'));
    }

    // Load the WP Media Library uploader and repeater script only on our settings page
    public function enqueue_media_uploader($hook) {
        if ($hook !== self::SETTINGS_PAGE_HOOK) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'hero-slider-admin-js',
            HERO_SLIDER_PLUGIN_URL . 'assets/js/hero-slide-admin.js',
            array('jquery'),
            filemtime(HERO_SLIDER_PLUGIN_DIR . 'assets/js/hero-slide-admin.js'),
            true
        );
    }

    // Function to add a new settings page
    public function add_settings_page() {
        add_options_page(
            'Hero Slider Settings',           // Page title (shown inside settings page)
            'Hero Slider',                    // Menu title (shown in sidebar)
            'manage_options',                 // Capability (only admin users)
            'hero-slider-settings',           // Menu slug (used in URL)
            array($this, 'hero_slider_setting_html') // Function to output the form HTML
        );
    }

    // Function to register our options with WordPress
    public function register_settings() {
        register_setting(
            'hero_slider_settings_group',     // Group name (must match settings_fields())
            'hero_slider_slides',             // Option name (saved in database)
            array('sanitize_callback' => array($this, 'sanitize_slides'))
        );

        register_setting(
            'hero_slider_settings_group',
            'hero_slider_general_settings',
            array('sanitize_callback' => array($this, 'sanitize_general_settings'))
        );
    }

    // Sanitize the autoplay/interval settings
    public function sanitize_general_settings($input) {
        $interval = isset($input['interval']) ? absint($input['interval']) : 4000;

        return array(
            'autoplay' => !empty($input['autoplay']) ? 1 : 0,
            'interval' => max(1000, $interval),
        );
    }

    // Sanitize each slide field before it's saved to the database
    public function sanitize_slides($slides) {
        if (!is_array($slides)) {
            return array();
        }

        $sanitized = array();
        foreach ($slides as $slide) {
            $slide = array(
                'image'       => isset($slide['image']) ? esc_url_raw(trim($slide['image'])) : '',
                'alt'         => isset($slide['alt']) ? sanitize_text_field($slide['alt']) : '',
                'heading'     => isset($slide['heading']) ? sanitize_text_field($slide['heading']) : '',
                'paragraph'   => isset($slide['paragraph']) ? sanitize_textarea_field($slide['paragraph']) : '',
                'button_text' => isset($slide['button_text']) ? sanitize_text_field($slide['button_text']) : '',
                'button_link' => isset($slide['button_link']) ? esc_url_raw(trim($slide['button_link'])) : '',
            );

            // Skip slides where every field is blank (e.g. a leftover template row).
            if (count(array_filter($slide)) === 0) {
                continue;
            }

            if (empty($slide['image'])) {
                add_settings_error(
                    'hero_slider_slides',
                    'hero-slider-missing-image',
                    sprintf('Slide "%s" has no image and will not appear on the frontend.', $slide['heading'] ?: 'Untitled'),
                    'warning'
                );
            }

            $sanitized[] = $slide;
        }

        return $sanitized;
    }

    // Function to output the actual settings page form
    public function hero_slider_setting_html() {
        $slides   = get_option('hero_slider_slides');
        $general  = wp_parse_args(get_option('hero_slider_general_settings'), array(
            'autoplay' => 0,
            'interval' => 4000,
        ));

        if (empty($slides)) {
            $slides = array(array()); // Always show at least one empty slide block.
        }
        ?>
        <div class="wrap hero-slider-wrap">
            <h1 class="hero-slider-header">Hero Slider Settings</h1>

            <?php settings_errors('hero_slider_slides'); ?>

            <!-- Settings Form -->
            <form action="options.php" method="post" class="hero-slider-form">
                <?php
                settings_fields('hero_slider_settings_group');  // Output hidden fields for security and proper saving
                ?>

                <div class="hero-slider-general-settings">
                    <h2>Slider Behavior</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="hero_slider_autoplay">Autoplay</label></th>
                            <td>
                                <input type="checkbox" id="hero_slider_autoplay" name="hero_slider_general_settings[autoplay]" value="1" <?php checked($general['autoplay'], 1); ?> />
                                <label for="hero_slider_autoplay">Automatically advance slides</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="hero_slider_interval">Autoplay Interval (ms)</label></th>
                            <td><input type="number" id="hero_slider_interval" name="hero_slider_general_settings[interval]" min="1000" step="500" value="<?php echo esc_attr($general['interval']); ?>" class="small-text" /></td>
                        </tr>
                    </table>
                </div>

                <hr class="hero-slider-divider">

                <div id="hero-slider-slides-container">
                    <?php foreach ($slides as $i => $slide): ?>
                        <?php echo $this->render_slide_fields($i, $slide); ?>
                    <?php endforeach; ?>
                </div>

                <p>
                    <button type="button" id="hero-slider-add-slide" class="button">+ Add Slide</button>
                </p>

                <!-- Hidden template used by JS to add new slides -->
                <template id="hero-slider-slide-template">
                    <?php echo $this->render_slide_fields('__INDEX__', array()); ?>
                </template>

                <!-- Save Button -->
                <div class="submit-btn-container">
                    <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
                </div>
            </form>
        </div>
        <?php
    }

    // Renders a single slide's fields. Used both for saved slides and the JS "add slide" template.
    private function render_slide_fields($index, $slide) {
        ob_start();
        ?>
        <div class="hero-slide-section">
            <h2 class="hero-slide-title">Slide <?php echo is_numeric($index) ? $index + 1 : ''; ?></h2>

            <table class="form-table hero-slide-table">
                <tr>
                    <th scope="row"><label for="image_url_<?php echo esc_attr($index); ?>">Image URL</label></th>
                    <td>
                        <input type="text" id="image_url_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][image]" value="<?php echo esc_attr($slide['image'] ?? ''); ?>" class="regular-text hero-slider-image-url" placeholder="Enter image URL" />
                        <button type="button" class="button hero-slider-upload-btn">Choose Image</button>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="alt_<?php echo esc_attr($index); ?>">Image Alt Text</label></th>
                    <td><input type="text" id="alt_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][alt]" value="<?php echo esc_attr($slide['alt'] ?? ''); ?>" class="regular-text" placeholder="Describe the image for accessibility" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="heading_<?php echo esc_attr($index); ?>">Heading</label></th>
                    <td><input type="text" id="heading_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][heading]" value="<?php echo esc_attr($slide['heading'] ?? ''); ?>" class="regular-text" placeholder="Enter heading" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="paragraph_<?php echo esc_attr($index); ?>">Paragraph</label></th>
                    <td><textarea id="paragraph_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][paragraph]" rows="4" class="large-text" placeholder="Enter paragraph text"><?php echo esc_textarea($slide['paragraph'] ?? ''); ?></textarea></td>
                </tr>

                <tr>
                    <th scope="row"><label for="button_text_<?php echo esc_attr($index); ?>">Button Text</label></th>
                    <td><input type="text" id="button_text_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][button_text]" value="<?php echo esc_attr($slide['button_text'] ?? ''); ?>" class="regular-text" placeholder="Enter button text" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="button_link_<?php echo esc_attr($index); ?>">Button Link</label></th>
                    <td><input type="url" id="button_link_<?php echo esc_attr($index); ?>" name="hero_slider_slides[<?php echo esc_attr($index); ?>][button_link]" value="<?php echo esc_attr($slide['button_link'] ?? ''); ?>" class="regular-text" placeholder="Enter button link" /></td>
                </tr>
            </table>

            <button type="button" class="button hero-slider-remove-slide">Remove Slide</button>
        </div>
        <hr class="hero-slider-divider">
        <?php
        return ob_get_clean();
    }
}
