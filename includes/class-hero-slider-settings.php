<?php 

class Hero_Slider_Settings {

    // Constructor: Attach functions to WordPress hooks
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));   // Add our settings page link under 'Settings' menu
        add_action('admin_init', array($this, 'register_settings'));   // Register settings so WordPress can save the options
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

    // Function to register our option with WordPress
    public function register_settings() {
        register_setting(
            'hero_slider_settings_group',     // Group name (must match settings_fields())
            'hero_slider_slides'              // Option name (saved in database)
        );
    }

    // Function to output the actual settings page form
    public function hero_slider_setting_html() {
        $slides = get_option('hero_slider_slides'); // Load saved slides array
        ?>
        <div class="wrap hero-slider-wrap">
            <h1 class="hero-slider-header">Hero Slider Settings</h1>

            <!-- Settings Form -->
            <form action="options.php" method="post" class="hero-slider-form">
                <?php 
                settings_fields('hero_slider_settings_group');  // Output hidden fields for security and proper saving
                ?>

                <?php 
                    for($i = 0; $i < 5; $i++):  // Loop through slides 1-5
                ?>
                <div class="hero-slide-section">
                    <h2 class="hero-slide-title">Slide <?php echo $i + 1; ?></h2>

                    <table class="form-table hero-slide-table">
                        <tr>
                            <th scope="row"><label for="image_url_<?php echo $i; ?>">Image URL</label></th>
                            <td><input type="text" name="hero_slider_slides[<?php echo $i; ?>][image]" value="<?php echo esc_attr($slides[$i]['image'] ?? ''); ?>" class="regular-text" placeholder="Enter image URL" /></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="heading_<?php echo $i; ?>">Heading</label></th>
                            <td><input type="text" name="hero_slider_slides[<?php echo $i; ?>][heading]" value="<?php echo esc_attr($slides[$i]['heading'] ?? ''); ?>" class="regular-text" placeholder="Enter heading" /></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="paragraph_<?php echo $i; ?>">Paragraph</label></th>
                            <td><textarea name="hero_slider_slides[<?php echo $i; ?>][paragraph]" rows="4" class="large-text" placeholder="Enter paragraph text"><?php echo esc_textarea($slides[$i]['paragraph'] ?? ''); ?></textarea></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="button_text_<?php echo $i; ?>">Button Text</label></th>
                            <td><input type="text" name="hero_slider_slides[<?php echo $i; ?>][button_text]" value="<?php echo esc_attr($slides[$i]['button_text'] ?? ''); ?>" class="regular-text" placeholder="Enter button text" /></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="button_link_<?php echo $i; ?>">Button Link</label></th>
                            <td><input type="url" name="hero_slider_slides[<?php echo $i; ?>][button_link]" value="<?php echo esc_attr($slides[$i]['button_link'] ?? ''); ?>" class="regular-text" placeholder="Enter button link" /></td>
                        </tr>
                    </table>
                </div>
                <hr class="hero-slider-divider">
                <?php endfor; ?>    
                <!-- Save Button -->
                <div class="submit-btn-container">
                    <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
                </div>
            </form>
        </div>
        <?php
    }
}
