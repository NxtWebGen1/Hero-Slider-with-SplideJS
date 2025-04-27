<?php 

class Hero_Slider_Shortcode{

    public function __construct(){
        add_shortcode( 'hero-slider', array($this, 'hero_slider_display') );
    }


    public function hero_slider_display(){
        ob_start();
        ?>

        <!-- Main Slider  -->
            <section id="main-carousel" class="splide" aria-label="The main carousel with large slides.">
                <div class="splide__track">
                    <ul class="splide__list">

                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Untitled_design___2023_05_08T170835_361-scaled.png" alt="Slide 1">
                            <div class="slide-content">
                                <h2>Discover the Unseen</h2>
                                <p>Explore places you've never imagined before.</p>
                                <a href="#" class="slide-button">Start Journey</a>
                            </div>
                        </li>

                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Dal20Lake_GettyImages-1323846766.jpg" alt="Slide 2">
                            <div class="slide-content">
                                <h2>Capture Every Moment</h2>
                                <p>Your adventure deserves to be remembered forever</p>
                                <a href="#" class="slide-button">View Gallery</a>
                            </div>
                        </li>

                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/5e5f7e9aaa7d11.jpg" alt="Slide 3">
                            <div class="slide-content">
                                <h2> Unlock New Destinations</h2>
                                <p>Find your next escape with us today.</p>
                                <a href="#" class="slide-button">Explore More</a>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Sharda_Neelum_Valley_Azad_Kashmir_2015-03-22.jpg" alt="Slide 3">
                            <div class="slide-content">
                                <h2>Adventure Awaits</h2>
                                <p>Step outside the ordinary and into the extraordinary.</p>
                                <a href="#" class="slide-button">Join Now</a>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/92057881-scaled.jpeg" alt="Slide 3">
                            <div class="slide-content">
                                <h2>Experience True Freedom</h2>
                                <p>Travel with comfort, style, and unforgettable memories.</p>
                                <a href="#" class="slide-button">Book Today</a>
                            </div>
                        </li>

                    </ul>
                </div>
            </section>

        <!-- Thumbnail Slider  -->
            <section id="thumbnail-carousel" class="splide"
                aria-label="The carousel with thumbnails. Selecting a thumbnail will change the Beautiful Gallery carousel.">
                <div class="splide__track">
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Untitled_design___2023_05_08T170835_361-scaled.png" alt="">
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Dal20Lake_GettyImages-1323846766.jpg" alt="">
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/5e5f7e9aaa7d11.jpg" alt="">
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/Sharda_Neelum_Valley_Azad_Kashmir_2015-03-22.jpg" alt="">
                        </li>
                        <li class="splide__slide">
                            <img src="http://localhost/portfolio/wp-content/uploads/2025/04/92057881-scaled.jpeg" alt="">
                        </li>
                    </ul>
                </div>
            </section>


        <?php
                return ob_get_clean();
            }




}