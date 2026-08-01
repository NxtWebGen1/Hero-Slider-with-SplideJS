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
        wp_enqueue_style('dashicons');
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

        register_setting(
            'hero_slider_settings_group',
            'hero_slider_appearance_settings',
            array('sanitize_callback' => array($this, 'sanitize_appearance_settings'))
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

    private function sanitize_color($value) {
        return $value ? (sanitize_hex_color($value) ?: '') : '';
    }

    private function sanitize_font_size($value) {
        $value = trim((string) $value);
        return $value !== '' ? absint($value) : '';
    }

    private function sanitize_font_family($value) {
        $choices = hero_slider_get_font_choices();
        return array_key_exists($value, $choices) ? $value : '';
    }

    // Sanitize the default appearance settings (can be overridden per-shortcode)
    public function sanitize_appearance_settings($input) {
        return array(
            'show_heading'   => !empty($input['show_heading']) ? 1 : 0,
            'show_paragraph' => !empty($input['show_paragraph']) ? 1 : 0,
            'show_overlay'   => !empty($input['show_overlay']) ? 1 : 0,

            'heading_color'       => $this->sanitize_color($input['heading_color'] ?? ''),
            'heading_font_size'   => $this->sanitize_font_size($input['heading_font_size'] ?? ''),
            'heading_font_family' => $this->sanitize_font_family($input['heading_font_family'] ?? ''),

            'paragraph_color'       => $this->sanitize_color($input['paragraph_color'] ?? ''),
            'paragraph_font_size'   => $this->sanitize_font_size($input['paragraph_font_size'] ?? ''),
            'paragraph_font_family' => $this->sanitize_font_family($input['paragraph_font_family'] ?? ''),

            'button_text_color'  => $this->sanitize_color($input['button_text_color'] ?? ''),
            'button_bg_color'    => $this->sanitize_color($input['button_bg_color'] ?? ''),
            'button_font_size'   => $this->sanitize_font_size($input['button_font_size'] ?? ''),
            'button_font_family' => $this->sanitize_font_family($input['button_font_family'] ?? ''),
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

    // Renders a toggle-switch styled checkbox
    private function render_toggle($id, $name, $checked, $label) {
        ob_start();
        ?>
        <label class="hs-toggle" for="<?php echo esc_attr($id); ?>">
            <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="1" class="hs-toggle-input" <?php checked($checked, 1); ?> />
            <span class="hs-toggle-track"><span class="hs-toggle-thumb"></span></span>
            <span class="hs-toggle-label-text"><?php echo esc_html($label); ?></span>
        </label>
        <?php
        return ob_get_clean();
    }

    // Renders a <select> of the whitelisted web-safe font choices
    private function render_font_select($id, $name, $selected) {
        ob_start();
        ?>
        <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="hs-select">
            <?php foreach (hero_slider_get_font_choices() as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($selected, $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        return ob_get_clean();
    }

    // Renders a labeled color/size/font-family trio for one element (heading, paragraph, button).
    // $font_prefix names the font_size/font_family option keys (e.g. "button" -> button_font_size);
    // $color_key names the color option key on its own (e.g. "button_text_color"), since it doesn't
    // always share the same prefix as the font fields.
    private function render_typography_controls($font_prefix, $color_key, $color, $font_size, $font_family, $default_size_placeholder, $extra_color_row = '') {
        ob_start();
        ?>
        <div class="hs-typography-row">
            <div class="hs-typography-field">
                <label for="hero_slider_<?php echo esc_attr($color_key); ?>">Text Color</label>
                <input type="color" id="hero_slider_<?php echo esc_attr($color_key); ?>" name="hero_slider_appearance_settings[<?php echo esc_attr($color_key); ?>]" value="<?php echo esc_attr($color ?: '#ffffff'); ?>" />
            </div>
            <div class="hs-typography-field">
                <label for="hero_slider_<?php echo esc_attr($font_prefix); ?>_font_size">Font Size (px)</label>
                <input type="number" id="hero_slider_<?php echo esc_attr($font_prefix); ?>_font_size" name="hero_slider_appearance_settings[<?php echo esc_attr($font_prefix); ?>_font_size]" min="8" class="small-text" placeholder="<?php echo esc_attr($default_size_placeholder); ?>" value="<?php echo esc_attr($font_size); ?>" />
            </div>
            <div class="hs-typography-field">
                <label for="hero_slider_<?php echo esc_attr($font_prefix); ?>_font_family">Font</label>
                <?php echo $this->render_font_select('hero_slider_' . $font_prefix . '_font_family', 'hero_slider_appearance_settings[' . $font_prefix . '_font_family]', $font_family); ?>
            </div>
            <?php echo $extra_color_row; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // Function to output the actual settings page form
    public function hero_slider_setting_html() {
        $slides   = get_option('hero_slider_slides');
        $general  = wp_parse_args(get_option('hero_slider_general_settings'), array(
            'autoplay' => 0,
            'interval' => 4000,
        ));
        $appearance = wp_parse_args(get_option('hero_slider_appearance_settings'), hero_slider_get_appearance_defaults());

        if (empty($slides)) {
            $slides = array(array()); // Always show at least one empty slide block.
        }
        ?>
        <div class="wrap hero-slider-wrap">

            <div class="hero-slider-topbar">
                <div class="hero-slider-topbar-title">
                    <span class="dashicons dashicons-images-alt2"></span>
                    <span>Hero Slider Settings</span>
                </div>
                <label class="hs-toggle hs-theme-toggle" for="hero-slider-theme-switch">
                    <input type="checkbox" id="hero-slider-theme-switch" class="hs-toggle-input" />
                    <span class="hs-toggle-track"><span class="hs-toggle-thumb"></span></span>
                    <span class="hs-toggle-label-text">Dark Mode</span>
                </label>
            </div>

            <?php settings_errors('hero_slider_slides'); ?>

            <!-- Settings Form -->
            <form action="options.php" method="post" class="hero-slider-form">
                <?php
                settings_fields('hero_slider_settings_group');  // Output hidden fields for security and proper saving
                ?>

                <div class="hs-card">
                    <h2 class="hs-card-title"><span class="dashicons dashicons-controls-play"></span> Slider Behavior</h2>
                    <div class="hs-card-body">
                        <?php echo $this->render_toggle('hero_slider_autoplay', 'hero_slider_general_settings[autoplay]', $general['autoplay'], 'Automatically advance slides'); ?>

                        <div class="hs-field-row">
                            <label for="hero_slider_interval">Autoplay Interval (ms)</label>
                            <input type="number" id="hero_slider_interval" name="hero_slider_general_settings[interval]" min="1000" step="500" value="<?php echo esc_attr($general['interval']); ?>" class="small-text" />
                        </div>
                    </div>
                </div>

                <div class="hs-card">
                    <h2 class="hs-card-title"><span class="dashicons dashicons-admin-appearance"></span> Appearance Defaults</h2>
                    <p class="hs-card-subtitle">These apply to every <code>[hero_slider]</code> shortcode, unless overridden by its attributes — see the README's Shortcode Guide.</p>
                    <div class="hs-card-body">
                        <div class="hs-toggle-group">
                            <?php echo $this->render_toggle('hero_slider_show_heading', 'hero_slider_appearance_settings[show_heading]', $appearance['show_heading'], 'Show heading'); ?>
                            <?php echo $this->render_toggle('hero_slider_show_paragraph', 'hero_slider_appearance_settings[show_paragraph]', $appearance['show_paragraph'], 'Show paragraph / description'); ?>
                            <?php echo $this->render_toggle('hero_slider_show_overlay', 'hero_slider_appearance_settings[show_overlay]', $appearance['show_overlay'], 'Show dark overlay behind text'); ?>
                        </div>

                        <h3 class="hs-subheading">Heading Typography</h3>
                        <?php echo $this->render_typography_controls('heading', 'heading_color', $appearance['heading_color'], $appearance['heading_font_size'], $appearance['heading_font_family'], 'Default: 55'); ?>

                        <h3 class="hs-subheading">Paragraph Typography</h3>
                        <?php echo $this->render_typography_controls('paragraph', 'paragraph_color', $appearance['paragraph_color'], $appearance['paragraph_font_size'], $appearance['paragraph_font_family'], 'Default: 20'); ?>

                        <h3 class="hs-subheading">Button Typography</h3>
                        <?php
                        $button_bg_row = '<div class="hs-typography-field"><label for="hero_slider_button_bg_color">Background</label><input type="color" id="hero_slider_button_bg_color" name="hero_slider_appearance_settings[button_bg_color]" value="' . esc_attr($appearance['button_bg_color'] ?: '#ff7e5f') . '" /></div>';
                        echo $this->render_typography_controls('button', 'button_text_color', $appearance['button_text_color'], $appearance['button_font_size'], $appearance['button_font_family'], 'Default: 18', $button_bg_row);
                        ?>
                    </div>
                </div>

                <div class="hs-card">
                    <h2 class="hs-card-title"><span class="dashicons dashicons-images-alt2"></span> Slides</h2>
                    <div class="hs-card-body">
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
                    </div>
                </div>

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
        $number = is_numeric($index) ? $index + 1 : '';
        ob_start();
        ?>
        <div class="hero-slide-section">
            <div class="hero-slide-header" role="button" tabindex="0">
                <h2 class="hero-slide-title">
                    <span class="hero-slide-number">Slide <?php echo esc_html($number); ?></span>
                    <?php if (!empty($slide['heading'])): ?>
                        <span class="hero-slide-title-preview">&mdash; <?php echo esc_html($slide['heading']); ?></span>
                    <?php endif; ?>
                </h2>
                <span class="dashicons dashicons-arrow-up-alt2 hero-slide-toggle-icon"></span>
            </div>
            <div class="hero-slide-body">
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

                <div class="hero-slide-actions">
                    <button type="button" class="button hero-slider-duplicate-slide">Duplicate Slide</button>
                    <button type="button" class="button hero-slider-remove-slide">Remove Slide</button>
                </div>
            </div>
        </div>
        <hr class="hero-slider-divider">
        <?php
        return ob_get_clean();
    }
}
