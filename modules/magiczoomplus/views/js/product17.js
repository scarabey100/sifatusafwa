/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

function mtDefer(method) {
    if (window.jQuery) {
        method();
    } else {
        setTimeout(function() { mtDefer(method) }, 50);
    }
}

mtDefer(function () {

    $(document).ready(function() {

        prestashop.on('updatedProduct', function (resp) {

            //NOTE: fix after .images-container has been updated via ajax
            $('#main .hidden-important .js-qv-product-images').removeClass('js-qv-product-images').addClass('js-qv-product-images-disabled');

            var productId = $('#product_page_product_id').val(),
                selectorContainer = $('#MagicToolboxSelectors'+productId);

            if (selectorContainer.length) {
                //NOTE: stop Magic Scroll
                if (typeof(MagicScroll) != 'undefined' && selectorContainer.hasClass('MagicScroll')) {
                    MagicScroll.stop(selectorContainer.get(0));
                }
            } else {
                //NOTE: create selector container if need it
                mtCreateSelectorContainer();
                selectorContainer = $('#MagicToolboxSelectors'+productId);
            }

            //NOTE: stop main tool
            window[mtCombinationData.toolClass].stop(mtCombinationData.toolId+'ImageMainImage');

            //NOTE: remove selectors
            selectorContainer.html('');

            //NOTE: add selector for Magic 360
            if (mtCombinationData.m360Selector) {
                if (originalLayout) {
                    selectorContainer.append('<li class="thumb-container">'+mtCombinationData.m360Selector+'</li>');
                } else {
                    selectorContainer.append(mtCombinationData.m360Selector);
                }
            }

            //NOTE: add selectors
            var ids = [];
            if (typeof(mtCombinationData.attributes[resp.id_product_attribute]) == 'undefined') {
                //NOTE: display all selectors if combination does not have any
                for (var k in mtCombinationData.selectors) {
                    ids.push(k);
                }
            } else {
                ids = mtCombinationData.attributes[resp.id_product_attribute];
            }
            if (ids) {
                for (var i = 0; i < ids.length; i++) {
                    if (originalLayout) {
                        selectorContainer.append('<li class="thumb-container">'+mtCombinationData.selectors[ids[i]]+'</li>');
                    } else {
                        selectorContainer.append(mtCombinationData.selectors[ids[i]]);
                    }
                }
            }

            //NOTE: add video selectors
            for (var i = 0; i < mtCombinationData.videoSelectors.length; i++) {
                if (originalLayout) {
                    selectorContainer.append('<li class="thumb-container">'+mtCombinationData.videoSelectors[i]+'</li>');
                } else {
                    selectorContainer.append(mtCombinationData.videoSelectors[i]);
                }
            }

            var main = $('#'+mtCombinationData.toolId+'ImageMainImage'),
                primarySelector = mtGetPrimarySelector();

            //NOTE: to avoid the effect of double switching pictures
            //      and unwanted image in expand mode
            if (primarySelector.length) {
                //NOTE: update main image before tool start
                main.attr('href', primarySelector.attr('href'));
                main.find('img').attr('src', primarySelector.attr('data-image'));
            }

            setTimeout(function(){
                //NOTE: start main tool
                window[mtCombinationData.toolClass].start(mtCombinationData.toolId+'ImageMainImage');
            }, 500);

            mtBindSelectors();

            //NOTE: switch container + highlight active selector
            if (primarySelector.length) {
                mtHighlightActiveSelector(primarySelector);
                mtSwitchContainer(primarySelector.get(0));
            }

            if( selectorContainer.find('a > img').length > 1) {
                selectorContainer.show();
            } else {
                selectorContainer.hide();
            }

            //NOTE: prepare container for Magic Scroll
            if (mtScrollEnabled && mtScrollItems.match(/^\d+$/) && selectorContainer.find('a > img').length >= parseInt(mtScrollItems)) {
                selectorContainer.addClass('MagicScroll');
                if (mtScrollOptions) {
                    selectorContainer.attr('data-options', mtScrollOptions);
                }
            }
            //NOTE: start Magic Scroll
            if (typeof(MagicScroll) != 'undefined' && selectorContainer.hasClass('MagicScroll')) {
                MagicScroll.start(selectorContainer.get(0));
            }
            if (originalLayout) {
                if (selectorContainer.find('li').length <= 2) {
                    $('.scroll-box-arrows').removeClass('scroll');
                }
            }
        });

        mtBindSelectors();
    });
});

function mtCreateSelectorContainer() {
    var productId = $('#product_page_product_id').val();

    switch(mtLayout) {
        case 'original':
            $('.mt-images-container .product-cover').after(
                '<div class="MagicToolboxSelectorsContainer js-qv-mask mask">'+
                  '<ul id="MagicToolboxSelectors'+productId+'" class="product-images js-qv-product-images">'+
                  '</ul>'+
                '</div>'
            );
            break;
        case 'bottom':
        case 'right':
            $('#content .MagicToolboxContainer').append(
                '<div class="MagicToolboxSelectorsContainer">'+
                  '<div id="MagicToolboxSelectors'+productId+'">'+
                  '</div>'+
                '</div>'
            );
            break;
        case 'top':
        case 'left':
            $('#content .MagicToolboxContainer').prepend(
                '<div class="MagicToolboxSelectorsContainer">'+
                  '<div id="MagicToolboxSelectors'+productId+'">'+
                  '</div>'+
                '</div>'
            );
            break;
        default:
            break;
    }
}

function mtGetPrimarySelector(with360) {
    var queries = [], selector = null;

    if (typeof(with360) == 'undefined') {
        with360 = true;
    }

    if (with360 && m360AsPrimaryImage) {
        queries.push('.m360-selector');
    }
    queries.push('[data-mt-selector-id="'+mtCombinationData.coverId+'"]');
    queries.push('[data-mt-selector-id]:first');
    if (with360 && !m360AsPrimaryImage) {
        queries.push('.m360-selector');
    }

    for (var i = 0; i < queries.length; i++) {
        selector = $(queries[i]);
        if (selector.length) {
            break;
        }
    }
    return selector;
}
