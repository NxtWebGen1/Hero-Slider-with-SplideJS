document.addEventListener('DOMContentLoaded', function () {
    var main = new Splide('#main-carousel', {
        type      : 'fade',
        heightRatio: 0.5,
        pagination: false,
        arrows    : false,
        cover     : true,
    });

    var thumbnails = new Splide('#thumbnail-carousel', {
        fixedWidth  : 100,
        fixedHeight : 64,
        isNavigation: true,
        gap         : 10,
        focus       : 'center',
        pagination  : false,
        cover       : true,
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
});
