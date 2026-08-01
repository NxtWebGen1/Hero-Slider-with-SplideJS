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

    // One-time migration: the plugin used to store a single global slide list
    // (hero_slider_slides + hero_slider_general_settings). Fold that into the
    // new multi-slider option as a slider named "default" so existing sites/
    // shortcodes keep working after upgrading.
    private function maybe_migrate_legacy_data() {
        if (get_option('hero_slider_sliders') !== false) {
            return;
        }

        $legacy_slides  = get_option('hero_slider_slides');
        $legacy_general = get_option('hero_slider_general_settings');

        if (empty($legacy_slides) && empty($legacy_general)) {
            return;
        }

        update_option('hero_slider_sliders', array(
            'default' => array(
                'autoplay' => !empty($legacy_general['autoplay']) ? 1 : 0,
                'interval' => !empty($legacy_general['interval']) ? absint($legacy_general['interval']) : 4000,
                'slides'   => is_array($legacy_slides) ? $legacy_slides : array(),
            ),
        ));
    }

    // Function to register our options with WordPress
    public function register_settings() {
        $this->maybe_migrate_legacy_data();

        register_setting(
            'hero_slider_settings_group',     // Group name (must match settings_fields())
            'hero_slider_sliders',            // Option name (saved in database)
            array('sanitize_callback' => array($this, 'sanitize_sliders'))
        );

        register_setting(
            'hero_slider_settings_group',
            'hero_slider_appearance_settings',
            array('sanitize_callback' => array($this, 'sanitize_appearance_settings'))
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

    // Sanitize the whole multi-slider option: each slider gets its own autoplay/interval and slide list
    public function sanitize_sliders($input) {
        if (!is_array($input)) {
            return array('default' => array('autoplay' => 0, 'interval' => 4000, 'slides' => array()));
        }

        $sanitized = array();
        foreach ($input as $slider_id => $slider) {
            $slider_id = sanitize_key($slider_id);
            if ($slider_id === '' || !is_array($slider)) {
                continue;
            }

            $interval = isset($slider['interval']) ? absint($slider['interval']) : 4000;

            $sanitized[$slider_id] = array(
                'autoplay' => !empty($slider['autoplay']) ? 1 : 0,
                'interval' => max(1000, $interval),
                'slides'   => $this->sanitize_slides($slider['slides'] ?? array()),
            );
        }

        // Always keep at least one slider so the settings page and any existing
        // [hero_slider] shortcodes on the site have something to fall back to.
        if (empty($sanitized)) {
            $sanitized['default'] = array('autoplay' => 0, 'interval' => 4000, 'slides' => array());
        }

        return $sanitized;
    }

    // Sanitize each slide field before it's saved to the database
    private function sanitize_slides($slides) {
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
        $sliders = get_option('hero_slider_sliders');
        if (empty($sliders) || !is_array($sliders)) {
            $sliders = array('default' => array('autoplay' => 0, 'interval' => 4000, 'slides' => array(array())));
        }
        $slider_ids = array_keys($sliders);

        $appearance = wp_parse_args(get_option('hero_slider_appearance_settings'), hero_slider_get_appearance_defaults());
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
                    <h2 class="hs-card-title"><span class="dashicons dashicons-admin-appearance"></span> Appearance Defaults</h2>
                    <p class="hs-card-subtitle">These apply to every <code>[hero_slider]</code> shortcode (across all sliders), unless overridden by its attributes — see the Shortcode Guide below.</p>
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
                    <h2 class="hs-card-title"><span class="dashicons dashicons-images-alt2"></span> Sliders</h2>
                    <p class="hs-card-subtitle">Create multiple independent sliders — each with its own slides and autoplay setting — and embed any of them with <code>[hero_slider id="..."]</code>.</p>
                    <div class="hs-card-body">
                        <div class="hs-slider-switcher">
                            <label for="hero-slider-active-select">Editing slider:</label>
                            <select id="hero-slider-active-select" class="hs-select">
                                <?php foreach ($slider_ids as $sid): ?>
                                    <option value="<?php echo esc_attr($sid); ?>"><?php echo esc_html($sid); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="hero-slider-new-slider" class="button">+ New Slider</button>
                            <button type="button" id="hero-slider-delete-slider" class="button">Delete This Slider</button>
                        </div>

                        <div id="hero-slider-panels">
                            <?php foreach ($sliders as $slider_id => $slider): ?>
                                <?php echo $this->render_slider_panel($slider_id, $slider); ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Hidden template used by JS to create a brand-new slider -->
                        <template id="hero-slider-panel-template">
                            <?php echo $this->render_slider_panel('__SLIDER_ID__', array('autoplay' => 0, 'interval' => 4000, 'slides' => array(array()))); ?>
                        </template>
                    </div>
                </div>

                <div class="hs-card hs-shortcode-guide">
                    <h2 class="hs-card-title"><span class="dashicons dashicons-editor-code"></span> Shortcode Guide</h2>
                    <div class="hs-card-body">
                        <p>Place one of these anywhere (a page, post, or any widget/block that renders shortcodes):</p>
                        <ul class="hs-shortcode-list">
                            <?php foreach ($slider_ids as $sid): ?>
                                <li>
                                    <code class="hs-shortcode-snippet">[hero_slider id="<?php echo esc_html($sid); ?>"]</code>
                                    <button type="button" class="button hero-slider-copy-shortcode" data-shortcode='[hero_slider id="<?php echo esc_attr($sid); ?>"]'>Copy</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="hs-card-subtitle">Omitting <code>id</code> — just <code>[hero_slider]</code> — displays your first configured slider (currently <code><?php echo esc_html($slider_ids[0]); ?></code>). New sliders you add above will appear in this list after you save. See the README's Shortcode Guide for the full list of optional attributes (heading/paragraph/overlay toggles, per-element colors, fonts, and sizes).</p>
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

    // Renders one slider's whole editable panel: autoplay/interval + its slide repeater.
    private function render_slider_panel($slider_id, $slider) {
        $slides = !empty($slider['slides']) ? $slider['slides'] : array(array());
        ob_start();
        ?>
        <div class="hero-slider-panel" data-slider-id="<?php echo esc_attr($slider_id); ?>">
            <div class="hs-field-row">
                <?php echo $this->render_toggle('hero_slider_autoplay_' . $slider_id, 'hero_slider_sliders[' . $slider_id . '][autoplay]', $slider['autoplay'] ?? 0, 'Automatically advance slides'); ?>
                <label for="hero_slider_interval_<?php echo esc_attr($slider_id); ?>">Autoplay Interval (ms)</label>
                <input type="number" id="hero_slider_interval_<?php echo esc_attr($slider_id); ?>" name="hero_slider_sliders[<?php echo esc_attr($slider_id); ?>][interval]" min="1000" step="500" value="<?php echo esc_attr($slider['interval'] ?? 4000); ?>" class="small-text" />
            </div>

            <div class="hero-slider-slides-container">
                <?php foreach ($slides as $i => $slide): ?>
                    <?php echo $this->render_slide_fields($slider_id, $i, $slide); ?>
                <?php endforeach; ?>
            </div>

            <p>
                <button type="button" class="button hero-slider-add-slide">+ Add Slide</button>
            </p>

            <!-- Hidden template used by JS to add new slides to this slider -->
            <template class="hero-slider-slide-template">
                <?php echo $this->render_slide_fields($slider_id, '__INDEX__', array()); ?>
            </template>
        </div>
        <?php
        return ob_get_clean();
    }

    // Renders a single slide's fields, namespaced under its parent slider's id.
    private function render_slide_fields($slider_id, $index, $slide) {
        $number      = is_numeric($index) ? $index + 1 : '';
        $name_prefix = 'hero_slider_sliders[' . $slider_id . '][slides][' . $index . ']';
        $id_prefix   = 'slide_' . $slider_id . '_' . $index;
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
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_image">Image URL</label></th>
                        <td>
                            <input type="text" id="<?php echo esc_attr($id_prefix); ?>_image" name="<?php echo esc_attr($name_prefix); ?>[image]" value="<?php echo esc_attr($slide['image'] ?? ''); ?>" class="regular-text hero-slider-image-url" placeholder="Enter image URL" />
                            <button type="button" class="button hero-slider-upload-btn">Choose Image</button>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_alt">Image Alt Text</label></th>
                        <td><input type="text" id="<?php echo esc_attr($id_prefix); ?>_alt" name="<?php echo esc_attr($name_prefix); ?>[alt]" value="<?php echo esc_attr($slide['alt'] ?? ''); ?>" class="regular-text" placeholder="Describe the image for accessibility" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_heading">Heading</label></th>
                        <td><input type="text" id="<?php echo esc_attr($id_prefix); ?>_heading" name="<?php echo esc_attr($name_prefix); ?>[heading]" value="<?php echo esc_attr($slide['heading'] ?? ''); ?>" class="regular-text" placeholder="Enter heading" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_paragraph">Paragraph</label></th>
                        <td><textarea id="<?php echo esc_attr($id_prefix); ?>_paragraph" name="<?php echo esc_attr($name_prefix); ?>[paragraph]" rows="4" class="large-text" placeholder="Enter paragraph text"><?php echo esc_textarea($slide['paragraph'] ?? ''); ?></textarea></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_button_text">Button Text</label></th>
                        <td><input type="text" id="<?php echo esc_attr($id_prefix); ?>_button_text" name="<?php echo esc_attr($name_prefix); ?>[button_text]" value="<?php echo esc_attr($slide['button_text'] ?? ''); ?>" class="regular-text" placeholder="Enter button text" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>_button_link">Button Link</label></th>
                        <td><input type="url" id="<?php echo esc_attr($id_prefix); ?>_button_link" name="<?php echo esc_attr($name_prefix); ?>[button_link]" value="<?php echo esc_attr($slide['button_link'] ?? ''); ?>" class="regular-text" placeholder="Enter button link" /></td>
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
