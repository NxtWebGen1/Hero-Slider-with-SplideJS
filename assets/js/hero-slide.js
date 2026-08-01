document.addEventListener('DOMContentLoaded', function () {
    // Each [hero_slider] instance is wrapped in its own .hero-slider-instance
    // container so multiple independent sliders can appear on the same page
    // without colliding — settings are read per-instance from data attributes
    // instead of a single page-wide config.
    document.querySelectorAll('.hero-slider-instance').forEach(function (instance) {
        var mainEl = instance.querySelector('.hero-main-carousel');
        var thumbEl = instance.querySelector('.hero-thumb-carousel');

        if (!mainEl) {
            return;
        }

        var main = new Splide(mainEl, {
            type       : 'fade',
            heightRatio: 0.55,
            pagination : false,
            speed      : 2000,   // slow down transition between slides
            arrows     : false,
            cover      : true,
            autoplay   : mainEl.dataset.autoplay === '1',
            interval   : parseInt(mainEl.dataset.interval, 10) || 4000,
        });

        if (thumbEl) {
            var thumbnails = new Splide(thumbEl, {
                fixedWidth  : 100,
                fixedHeight : 64,
                isNavigation: true,
                gap         : 10,
                focus       : 'center',
                pagination  : false,
                cover       : true,
                arrows      : false,
                breakpoints : {
                    600: {
                        fixedWidth : 66,
                        fixedHeight: 40,
                    },
                },
            });

            main.sync(thumbnails);
            main.mount();
            thumbnails.mount();
        } else {
            main.mount();
        }
    });
});
