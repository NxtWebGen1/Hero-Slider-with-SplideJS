<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Hero_Slider_Shortcode{

    // Counter so multiple [hero_slider] instances on the same page each get a
    // unique element id (Splide needs one per instance for its ARIA wiring).
    private static $instance_count = 0;

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

        $sliders = get_option( 'hero_slider_sliders' );

        if (empty($sliders) || !is_array($sliders)) {
            return '<p>No slides found. Please add some slides in the settings.</p>';
        }

        // shortcode_atts() needs 'id' present in $atts up front so it isn't stripped;
        // resolve which configured slider this instance targets before merging the rest.
        // array_keys()[0] is used instead of array_key_first() to stay compatible with PHP 7.2.
        $requested_id = is_array($atts) && isset($atts['id']) ? sanitize_key($atts['id']) : '';
        $slider_keys  = array_keys($sliders);
        $slider_id    = ($requested_id && isset($sliders[$requested_id])) ? $requested_id : $slider_keys[0];
        $slider       = $sliders[$slider_id];
        $slides       = $slider['slides'] ?? array();

        if (empty($slides)) {
            return '<p>No slides found. Please add some slides in the settings.</p>';
        }

        $appearance = wp_parse_args(get_option('hero_slider_appearance_settings'), hero_slider_get_appearance_defaults());

        // Shortcode attributes override the site-wide Appearance defaults for this instance.
        $atts = shortcode_atts(array(
            'id' => $slider_id,

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

        $carousel_class = 'hero-main-carousel splide' . ($show_overlay ? '' : ' hero-slider-no-overlay');

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

        self::$instance_count++;
        $unique_id = 'hero-slider-' . self::$instance_count;
        $autoplay  = !empty($slider['autoplay']) ? 1 : 0;
        $interval  = !empty($slider['interval']) ? absint($slider['interval']) : 4000;

        ob_start();
        ?>

        <div class="hero-slider-instance">

        <!-- Main Slider  -->
            <section id="<?php echo esc_attr($unique_id); ?>-main" class="<?php echo esc_attr($carousel_class); ?>" aria-label="The main carousel with large slides." data-autoplay="<?php echo esc_attr($autoplay); ?>" data-interval="<?php echo esc_attr($interval); ?>">
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
            <section id="<?php echo esc_attr($unique_id); ?>-thumbs" class="hero-thumb-carousel splide">
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

        </div>

        <?php
                return ob_get_clean();
            }




}
