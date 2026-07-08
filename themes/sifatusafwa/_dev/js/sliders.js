import './lib/slick.min';
$(document).ready(() => {

    var rtlStatus = $('html').attr('dir') === 'rtl';

    // Header topBanner
    $('.header__top--banner--inner').not('.slick-initialized').slick({
        infinite: true,
        autoplay: true,
        autoplaySpeed: 5000,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 1000,
        dots: false,
        arrows: true,
        rtl: rtlStatus
    });

    // HP banner
    $('.carousel__items').not('.slick-initialized').slick({
        infinite: true,
        autoplay: true,
        autoplaySpeed: 5000,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 1000,
        dots: true,
        arrows: false,
        rtl: rtlStatus
    });

    // Product page
    $('.product-cover').not('.slick-initialized').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 500,
        dots: false,
        arrows: true,
        asNavFor: '.product-nav',
        rtl: rtlStatus
    });
    $('.product-nav').slick({
        infinite: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        speed: 500,
        dots: false,
        arrows: false,
        asNavFor: '.product-cover',
        focusOnSelect: true,
        rtl: rtlStatus
    });

    // Featured products
    $('.featured-products__slider .products').not('.slick-initialized').slick({
        infinite: true,
        slidesToShow: 7,
        slidesToScroll: 2,
        speed: 800,
        dots: false,
        arrows: true,
        rtl: rtlStatus,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }
        ]
    });

    $('.featured-products__sellerplus--slider .products').not('.slick-initialized').slick({
        infinite: true,
        slidesToShow: 9,
        slidesToScroll: 3,
        speed: 800,
        dots: false,
        arrows: true,
        rtl: rtlStatus,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }
        ]
    });


    $(document).on("ajaxComplete", function() {
        // Featured products
        $('.featured-products__slider .products').not('.slick-initialized').slick({
            infinite: true,
            slidesToShow: 7,
            slidesToScroll: 2,
            speed: 800,
            dots: false,
            arrows: true,
            rtl: rtlStatus,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }
            ]
        });

        $('.featured-products__sellerplus--slider .products').not('.slick-initialized').slick({
            infinite: true,
            slidesToShow: 9,
            slidesToScroll: 3,
            speed: 800,
            dots: false,
            arrows: true,
            rtl: rtlStatus,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }
            ]
        });
    });

});