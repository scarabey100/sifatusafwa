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


var magictoolboxImagesOrder;
var updateMainImageIntervalID = null;
var updateSelectorsIntervalID = null;
var refreshMagicZoomPlusIntervalID = null;
var waitForMagicScrollToStop = false;
var magicToolboxSelectorsBackup = null;
var mtProductCoverImageId = 1;

function switchProductMainImage(anchor) {
    //NOTE: for quick update image on page load (when tool is not ready)
    if (!isProductMagicZoomReady) {
        $('#MagicZoomPlusImageMainImage').attr({
            'href': anchor.href,
            'title' : anchor.getAttribute('title'),
        }).find('img').attr({
            'src' : anchor.getAttribute('data-image'),
            //'alt' : anchor.getAttribute('alt'),
        });
    }

    //NOTE: clears a timer
    if (updateMainImageIntervalID != null) {
        clearInterval(updateMainImageIntervalID);
        updateMainImageIntervalID = null;
    }
    //NOTE: set a timer
    mtIntervals = isProductMagicZoomReady ? 0 : 500;
    updateMainImageIntervalID = setInterval(function() {
        if (isProductMagicZoomReady) {
            clearInterval(updateMainImageIntervalID);
            updateMainImageIntervalID = null;
            MagicZoom.update('MagicZoomPlusImageMainImage', anchor.href, anchor.getAttribute('data-image'));

            //NOTE: to switch slide container
            mtHighlightActiveSelector(anchor);
            mtSwitchContainer(anchor);
        }
    }, mtIntervals);

}

if (originalLayout) {
    window['displayImageOriginal'] = window['displayImage'];
    window['displayImage'] = function(domAAroundImgThumb) {

        if (typeof(domAAroundImgThumb) == 'undefined' || !domAAroundImgThumb.length || !domAAroundImgThumb.attr('href')) {
            return;
        }

        //NOTE: we have to skip the first function call (that is produced by PrestaShop)
        //      so as not to switch the container on page load
        if (m360AsPrimaryImage && !isProductMagicZoomReady) {
            return;
        }

        var anchor = domAAroundImgThumb.get(0),
            currentImg = '',
            el = null;

        el = document.querySelector('.mz-zoom-window > img');
        if (el) {
            currentImg = el.src;
        } else {
            el = document.getElementById('MagicZoomPlusImageMainImage');
            if (el) {
                currentImg = el.href;
            }
        }

        //NOTE: to avoid double update
        if (currentImg == anchor.href) {
            return;
        }

        switchProductMainImage(anchor);
    }
} else {

    function refreshProductSelectors(thumbIDs) {

        if (useMagicScroll) {

            if (!waitForMagicScrollToStop && !isProductMagicScrollStopped) {
                waitForMagicScrollToStop = true;
                MagicScroll.stop('MagicToolboxSelectors'+id_product);
            }

            if (doWaitForMagicScrollToStart || (waitForMagicScrollToStop && !isProductMagicScrollStopped) /*|| !MagicScroll.searchIsOver*/ || !isProductMagicZoomReady) {
                //NOTE: clears a timer
                if (updateSelectorsIntervalID != null) {
                    clearTimeout(updateSelectorsIntervalID);
                    updateSelectorsIntervalID = null;
                }
                //NOTE: set a timer
                updateSelectorsIntervalID = setTimeout(function() {
                    refreshProductSelectors(thumbIDs);
                }, 500);
                return;
            }

            //NOTE: reset flag
            waitForMagicScrollToStop = false;

            //NOTE: stopped

            magicToolboxSelectorsBackup = document.getElementById('MagicToolboxHiddenSelectors');

            //NOTE: backup all visible selectors into the hidden place
            $('#MagicToolboxSelectors'+id_product+' > a').each(function() {
                $(magicToolboxSelectorsBackup).append(this);
            });

            //NOTE: clear div.MagicScroll
            $('#MagicToolboxSelectors'+id_product).html('');

            var selectorObj = null;
            //NOTE: append magic360 selector into div.MagicScroll
            selectorObj = $('#MagicToolboxHiddenSelectors > a.m360-selector');
            $('#MagicToolboxSelectors'+id_product).append(selectorObj);
            if (thumbIDs.length) {
                //NOTE: append selectors into div.MagicScroll
                for (var i = 0; i < thumbIDs.length; i++) {
                    selectorObj = $('#MagicToolboxHiddenSelectors > a[data-mt-selector-id='+thumbIDs[i]+']');
                    $('#MagicToolboxSelectors'+id_product).append(selectorObj);
                }
                $('#wrapResetImages').removeClass('hidden-important');
            } else {
                //NOTE: append all selectors into div.MagicScroll
                for (var i = 0; i < magictoolboxImagesOrder.length; i++) {
                    selectorObj = $('#MagicToolboxHiddenSelectors > a[data-mt-selector-id='+magictoolboxImagesOrder[i]+']');
                    $('#MagicToolboxSelectors'+id_product).append(selectorObj);
                }
                $('#wrapResetImages').addClass('hidden-important');
            }
            //NOTE: append video selector into div.MagicScroll
            if (typeof(videoThumbIDs) == 'undefined') {
                videoThumbIDs = [];
            }
            for (var i = 0; i < videoThumbIDs.length; i++) {
                selectorObj = $('#MagicToolboxHiddenSelectors > a[data-mt-selector-id='+videoThumbIDs[i]+']');
                $('#MagicToolboxSelectors'+id_product).append(selectorObj);
            }

            var selectors = document.getElementById('MagicToolboxSelectors'+id_product);

            //NOTE: if product has no selectors
            if (!(selectors && selectors.childNodes && selectors.childNodes.length)) {
                return;
            }

            //DEPRECATED: used 'autostart:false' instead
            //selectors.className = selectors.className.replace(/\bMagicScrollDisabled\b/i, 'MagicScroll');

            selectors.className = selectors.className.replace(/\bhidden-important\b/i, '');

            //NOTE: to prevent double start when MagicScroll has not been started before
            doWaitForMagicScrollToStart = true;

            MagicScroll.start('MagicToolboxSelectors'+id_product);

            return;
        }

        if (thumbIDs.length) {
            $('#MagicToolboxSelectors'+id_product+' > a').addClass('hidden-selector');
            for (var i = 0; i < thumbIDs.length; i++) {
                $('#MagicToolboxSelectors'+id_product+' > a[data-mt-selector-id="'+thumbIDs[i]+'"]').removeClass('hidden-selector');
            }
            //NOTE: make magic360 selector visible
            $('#MagicToolboxSelectors'+id_product+' > a.m360-selector').removeClass('hidden-selector');
        } else {
            $('#MagicToolboxSelectors'+id_product+' > a').removeClass('hidden-selector');
        }

        var hiddenSelectorsCount = $('#MagicToolboxSelectors'+id_product+' > a.hidden-selector').length;
        if (hiddenSelectorsCount) {
            $('#wrapResetImages').removeClass('hidden-important');
        } else {
            $('#wrapResetImages').addClass('hidden-important');
        }

    }

    window['refreshProductImagesOriginal'] = window['refreshProductImages'];
    window['refreshProductImages'] = function(id_product_attribute) {

        id_product_attribute = parseInt(id_product_attribute);

        //NOTE: to avoid double restart
        if (typeof(arguments.callee.last_id_product_attribute) != 'undefined' && (arguments.callee.last_id_product_attribute == id_product_attribute)) {
            var r = window['refreshProductImagesOriginal'].apply(window, arguments);
            return r;
        }
        arguments.callee.last_id_product_attribute = id_product_attribute;

        var thumbIDs = [];

        if (id_product_attribute > 0 && typeof(combinationImages) != 'undefined' && typeof(combinationImages[id_product_attribute]) != 'undefined') {
            for (var i = 0; i < combinationImages[id_product_attribute].length; i++) {
                thumbIDs.push(parseInt(combinationImages[id_product_attribute][i]));
            }
        }

        //NOTE: switch main image
        var anchor = null;
        if (thumbIDs.length) {
            //NOTE: thumb id to display by default
            var thumbId = thumbIDs[0];

            //NOTE: find the product cover image
            for (var i = 0; i < thumbIDs.length; i++) {
                if (thumbIDs[i] == mtProductCoverImageId) {
                    thumbId = mtProductCoverImageId;
                    break;
                }
            }

            anchor = $('#MagicToolboxSelectors'+id_product+' a[data-mt-selector-id="'+thumbId+'"]');

            //NOTE: because MagicScroll creates clones
            if (useMagicScroll && anchor.length == 3) {
                anchor = anchor.get(1);
            } else {
                anchor = anchor.get(0);
            }

            //NOTE: if anchor is hidden (when using MagicScroll)
            if (typeof(anchor) == 'undefined') {
                anchor = $('#MagicToolboxHiddenSelectors a[data-mt-selector-id="'+thumbId+'"]').get(0);
            }

            //NOTICE: may be we should use 360 icon as first
            //var anchor = $('#MagicToolboxSelectors'+id_product+' > a').not('.hidden-selector').first().get(0);

            //NOTE: if product has selectors
            if (typeof(anchor) != 'undefined') {
                arguments.callee.last_anchor = anchor;
                //NOTE: not to switch the container on page load if 360 used as primary image
                (m360AsPrimaryImage && !isProductMagicZoomReady) ||
                switchProductMainImage(anchor);
            }
        } else {
            if (typeof(arguments.callee.last_anchor) == 'undefined') {
                var selectors = $('a[data-magic-slide-id="zoom"]');
                if (selectors.length) {
                    //NOTE: not to switch the container on page load if 360 used as primary image
                    (m360AsPrimaryImage && !isProductMagicZoomReady) ||
                    switchProductMainImage(selectors.get(0));
                }
            } else {
                //NOTE: not to switch the container on page load if 360 used as primary image
                (m360AsPrimaryImage && !isProductMagicZoomReady) ||
                switchProductMainImage(arguments.callee.last_anchor);
            }
        }

        refreshProductSelectors(thumbIDs);

        return window['refreshProductImagesOriginal'].apply(window, arguments);
    }

}



$(document).ready(function() {

    var mtSelectors = $('#views_block li a.magictoolbox-selector');
    mtSelectors.unbind('mouseenter mouseleave').click(function() {
        //NOTE: for blockcart module
        $('#bigpic').attr('src', $(this).attr('data-image'));
    }).removeClass('shown');
    //mtSelectors.not('.m360-selector').slice(0, 1).addClass('shown');

    //NOTE: remove handlers
    //NOTE: .off() was added in version 1.7
    if ($(document).off) {
        $(document).off('click', '#view_full_size, #image-block img');
        $(document).off('click', '#view_full_size, #image-block');
        $(document).off('click', '#image-block');
    }
    //$('span#view_full_size, div#image-block img').unbind('click');
    $('#image-block img').unbind('click');
    //NOTE: .off() was added in version 1.7
    if ($(document).off) {
        $(document).off('mouseover', '#views_block li a');
    }

    if (isPrestaShop15x) {
        mtBindSelectors();
    } else {
        setTimeout(function() {mtBindSelectors();}, 50);
    }

});

