<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Hero_Slider_Shortcode{

    public function __construct(){
        add_shortcode( 'hero_slider', array($this, 'hero_slider_display') );
    }

    // Builds a style="" attribute (including the leading space) from property => value pairs.
    // Empty/null values are skipped, so unset overrides never emit an empty declaration.
    private function build_style_attr($props) {
        $declarations = array();
        foreach ($props as $property => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $declarations[] = $property . ':' . $value . ';';
        }
        return $declarations ? ' style="' . esc_attr(implode('', $declarations)) . '"' : '';
    }

    private function sanitize_font_family($value) {
        $choices = hero_slider_get_font_choices();
        return array_key_exists($value, $choices) ? $value : '';
    }

    public function hero_slider_display($atts){

        $slides = get_option( 'hero_slider_slides' ); // Get all saved slides

        if(empty($slides)){
            return '<p>No slides found. Please add some slides in the settings.</p>';
        }

        $appearance = wp_parse_args(get_option('hero_slider_appearance_settings'), hero_slider_get_appearance_defaults());

        // Shortcode attributes override the site-wide Appearance defaults for this instance.
        $atts = shortcode_atts(array(
            'heading'   => $appearance['show_heading'] ? 'yes' : 'no',
            'paragraph' => $appearance['show_paragraph'] ? 'yes' : 'no',
            'overlay'   => $appearance['show_overlay'] ? 'yes' : 'no',

            'heading_color'       => $appearance['heading_color'],
            'heading_font_size'   => $appearance['heading_font_size'],
            'heading_font_family' => $appearance['heading_font_family'],

            'paragraph_color'       => $appearance['paragraph_color'],
            'paragraph_font_size'   => $appearance['paragraph_font_size'],
            'paragraph_font_family' => $appearance['paragraph_font_family'],

            'button_color'       => $appearance['button_text_color'],
            'button_bg_color'    => $appearance['button_bg_color'],
            'button_font_size'   => $appearance['button_font_size'],
            'button_font_family' => $appearance['button_font_family'],
        ), $atts, 'hero_slider');

        $show_heading   = filter_var($atts['heading'], FILTER_VALIDATE_BOOLEAN);
        $show_paragraph = filter_var($atts['paragraph'], FILTER_VALIDATE_BOOLEAN);
        $show_overlay   = filter_var($atts['overlay'], FILTER_VALIDATE_BOOLEAN);

        $carousel_class = 'splide' . ($show_overlay ? '' : ' hero-slider-no-overlay');

        $heading_style = $this->build_style_attr(array(
            'color'       => sanitize_hex_color($atts['heading_color']),
            'font-size'   => absint($atts['heading_font_size']) ? absint($atts['heading_font_size']) . 'px' : '',
            'font-family' => $this->sanitize_font_family($atts['heading_font_family']),
        ));

        $paragraph_style = $this->build_style_attr(array(
            'color'       => sanitize_hex_color($atts['paragraph_color']),
            'font-size'   => absint($atts['paragraph_font_size']) ? absint($atts['paragraph_font_size']) . 'px' : '',
            'font-family' => $this->sanitize_font_family($atts['paragraph_font_family']),
        ));

        // "background" (not "background-color") is used deliberately: the default .slide-button
        // style sets a gradient via the background shorthand, and only the shorthand fully
        // replaces it — a lone background-color would sit underneath the opaque gradient image.
        $button_style = $this->build_style_attr(array(
            'color'       => sanitize_hex_color($atts['button_color']),
            'background'  => sanitize_hex_color($atts['button_bg_color']),
            'font-size'   => absint($atts['button_font_size']) ? absint($atts['button_font_size']) . 'px' : '',
            'font-family' => $this->sanitize_font_family($atts['button_font_family']),
        ));

        ob_start();
        ?>

        <!-- Main Slider  -->
            <section id="main-carousel" class="<?php echo esc_attr($carousel_class); ?>" aria-label="The main carousel with large slides.">
                <div class="splide__track">
                    <ul class="splide__list">
                    <?php foreach($slides as $slide): ?>
                        <?php if(!empty($slide['image'])): ?>
                            <li class="splide__slide">
                                <img src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr(($slide['alt'] ?? '') ?: ($slide['heading'] ?? '')); ?>">
                                <div class="slide-content">
                                    <?php if($show_heading && !empty($slide['heading'])): ?>
                                        <h2<?php echo $heading_style; ?>><?php echo esc_html($slide['heading']); ?></h2>
                                    <?php endif; ?>

                                    <?php if($show_paragraph && !empty($slide['paragraph'])): ?>
                                        <p<?php echo $paragraph_style; ?>><?php echo esc_html($slide['paragraph']); ?></p>
                                    <?php endif; ?>

                                    <?php if(!empty($slide['button_link']) && !empty($slide['button_text'])): ?>
                                        <a href="<?php echo esc_url($slide['button_link']); ?>" class="slide-button"<?php echo $button_style; ?>><?php echo esc_html($slide['button_text']); ?></a>
                                    <?php endif; ?>

                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </section>

         <!-- Thumbnail Slider -->
            <section id="thumbnail-carousel" class="splide">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php foreach ($slides as $slide): ?>
                            <?php if (!empty($slide['image'])): ?>
                                <li class="splide__slide">
                                    <img src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr(($slide['alt'] ?? '') ?: ($slide['heading'] ?? '')); ?> thumbnail">
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>


        <?php
                return ob_get_clean();
            }




}
