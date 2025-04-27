<?php 

class Hero_Slider_Shortcode{

    public function __construct(){
        add_shortcode( 'hero-slider', array($this, 'hero_slider_display') );
    }


    public function hero_slider_display(){

        $slides = get_option( 'hero_slider_slides' ); // Get all saved slides
        
        if(empty($slides)){
            return '<p>No slides found. Please add some slides in the settings.</p>';
        }

        ob_start();
        ?>

        <!-- Main Slider  -->
            <section id="main-carousel" class="splide" aria-label="The main carousel with large slides.">
                <div class="splide__track">
                    <ul class="splide__list">
                    <?php foreach($slides as $slide): ?>
                        <?php if(!empty($slide['image'])): ?>
                            <li class="splide__slide">
                                <img src="<?php echo esc_url($slide['image']); ?>" alt="">
                                <div class="slide-content">
                                    <h2><?php echo esc_html($slide['heading']); ?></h2>
                                    <p><?php echo esc_html($slide['paragraph']); ?></p>

                                    <?php if(!empty($slide['button_link']) && !empty($slide['button_text'])): ?>
                                        <a href="<?php echo esc_url($slide['button_link']); ?>" class="slide-button"><?php echo esc_html($slide['button_text']); ?></a>
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
                                    <img src="<?php echo esc_url($slide['image']); ?>" alt="">
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