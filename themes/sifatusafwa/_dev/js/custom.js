import $ from 'jquery';
import './validation';
import './sliders';
import './components/gift-card'

$(document).ready(() => {

    // Sticky menu
    var header = $('#header');
    $(window).scroll(function() {
        var scroll = $(this).scrollTop();
        if (scroll >= 300) {
            header.addClass("sticky");
        } else {
            header.removeClass("sticky");
        }
    });

    $(document).on('click', '.editions__tabs--link', function () {
        let getID = $(this).data('tab');
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $('#' + getID).addClass('active').siblings().removeClass('active');
    });

    $(document).on('click', '.header__toggle--button', function () {
        $(this).toggleClass('active');
        $('.header__bottom').toggleClass('active');
        $('html, body').toggleClass('hidden');
    });

    $(document).on('click', '.filter-button__open button', function () {
        $('body').addClass('hidden');
        $('#search_filters_wrapper').addClass('active');
    });

    $(document).on('click', '.filter-button__close button', function () {
        $('#search_filters_wrapper').removeClass('active');
        $('body').removeClass('hidden');
    });

    // Manage Popups
    $(document).on('click', '.popup__wrapper', function (ev) {
        ev.stopPropagation();
    });
    $(document).on('click', '.popup', function (ev) {
        ev.stopPropagation();
        $(this).removeClass('active');
        $('body').removeClass('hidden');
    });
    $(document).on('click', '.popup__open', function (ev) {
        ev.preventDefault();
        let getPopup = $(this).attr('data-popup');
        $('body').addClass('hidden');
        $(`.popup#${getPopup}`).addClass('active');
    });
    $(document).on('click', '.popup__close', function (ev) {
        ev.preventDefault();
        $(this).parents('.popup').removeClass('active in');
        $('body').removeClass('hidden modal-open');
    });

    $(document).on('click', '.product__share .share-button', function (ev) {
        ev.preventDefault();
        if (navigator.share) {
            navigator.share({
                title: document.title,
                text: "Regarde cette page intéressante !",
                url: window.location.href
            }).then(() => {}).catch((error) => {});
        } else {
            alert("La fonction de partage n'est pas supportée sur ce navigateur.");
        }
    });


    var categoryDesc = $('.category__view--desc');
    var categoryDescWrapper = $('.category__view--desc--wrapper');
    var categoryDescInner = $('.category__view--desc--inner');
    var showMore = $('.category__view--desc .show-more');
    var showLess = $('.category__view--desc .show-less');

    if (categoryDescInner.innerHeight() > categoryDescWrapper.innerHeight()) {
        categoryDesc.addClass('active');
    }

    showMore.on('click', function () {
        $(this).removeClass('active');
        categoryDescWrapper.addClass('active');
        showLess.addClass('active');
    });
    showLess.on('click', function () {
        $(this).removeClass('active');
        categoryDescWrapper.removeClass('active');
        showMore.addClass('active');
    });

    $(document).on('click', '#cart .cart-summary-toggle', function () {
        $(this).toggleClass('active');
        $('#cart .cart-detailed-subtotals').toggleClass('active');
    });

    $(document).on("ajaxComplete", function() {
        if (('body#category').length) {
            $(document).on('click', 'a.klevuPaginate', function () {
                $('html, body').animate({
                    scrollTop: 0,
                }, 1000);
            });

            $(document).on('click', '.filter-button__open button', function () {
                $('body').addClass('hidden');
                $('#search_filters_wrapper').addClass('active');
            });

            $(document).on('click', '.filter-button__close button', function () {
                $('#search_filters_wrapper').removeClass('active');
                $('body').removeClass('hidden');
            });

            $('body#category').removeClass('hidden');
        }
    });

    // Resize
    const mq = window.matchMedia( "(min-width: 992px)" );
    function checkResize () {
        if (mq.matches) {
            // window width >= 992px
            $(document).on('click', '.menu__toggle .title__menu--toggle', function (ev) {
                ev.preventDefault();
                $(this).toggleClass('active');
                $(this).closest('li.mm_blocks_li').siblings().slideToggle();
            });
            $(document).on('click', '.menu__toggle .title__menu--toggle--simple', function () {
                $(this).toggleClass('active');
                setTimeout(() => {
                    $(this).closest('li.mm_blocks_li').siblings().find('.ets_mm_block_content').slideToggle();
                }, 100);
            });
            $(document).on('click', '.menu__toggle .mm_block_type_mnft .h4', function (ev) {
                ev.preventDefault();
                $(this).toggleClass('active');
                $(this).next('.ets_mm_block_content').slideToggle();
            });
            $(window).load(function () {
                $('.menu__toggle .title__menu--toggle--simple').trigger('click');
            });
        } else {
            // window width < 992px
            $(document).on('click', '.mm_menus_li.mm_has_sub > a', function (ev) {
                ev.preventDefault();
                $(this).parent().toggleClass('active');
            });
        }
    }
    checkResize();

});