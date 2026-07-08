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


function pauseYoutubePlayer(iframe) {
    if (typeof(arguments.callee.youtubePlayers) === 'undefined') {
        arguments.callee.youtubePlayers = {};
    }
    var id = iframe.getAttribute('id');
    if (id && typeof(arguments.callee.youtubePlayers[id]) != 'undefined') {
        arguments.callee.youtubePlayers[id].pauseVideo();
        return;
    }
    var player = new window.YT.Player(iframe, {
        events: {
            'onReady': function(event) {
                event.target.pauseVideo();
            }
        }
    });
    id = iframe.getAttribute('id');
    arguments.callee.youtubePlayers[id] = player;
    return;
}

function mtBindSelectors() {

    //NOTE: to swicth between 360, zoom and video
    var magicToolboxTool = 'magiczoomplus',
        magicToolboxToolMainId = 'MagicZoomPlusImageMainImage',
        isMagicZoom = (magicToolboxTool == 'magiczoom' || magicToolboxTool == 'magiczoomplus'),
        magicToolboxSwitchMetod = mEvent,
        loadVimeoJSFramework = function() {
            //NOTE: to avoid multiple loading
            if (typeof(arguments.callee.loadedVimeoJSFramework) !== 'undefined') {
                return;
            }
            arguments.callee.loadedVimeoJSFramework = true;

            //NOTE: load vimeo js framework
            if (typeof(Vimeo) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://player.vimeo.com/api/player.js';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        loadYoutubeApi = function() {
            //NOTE: to avoid multiple loading
            if (typeof(arguments.callee.loadedYoutubeApi) !== 'undefined') {
                return;
            }
            arguments.callee.loadedYoutubeApi = true;

            //NOTE: load youtube api
            if (typeof(window.YT) == 'undefined' || typeof(window.YT.Player) == 'undefined') {
                var firstScriptTag = document.getElementsByTagName('script')[0],
                    newScriptTag = document.createElement('script');
                newScriptTag.async = true;
                newScriptTag.src = 'https://www.youtube.com/iframe_api';
                firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
            }
        },
        switchFunction = function(event) {

            event = event || window.event;

            var element = event.target || event.srcElement,
                currentContainer = document.querySelector('.mt-active'),
                currentSlideId = null,
                newSlideId = null,
                newContainer = null,
                switchContainer = false;

            if (!currentContainer) {
                return false;
            }

            if (element.tagName.toLowerCase() != 'a') {
                element = element.parentNode;
                if (element.tagName.toLowerCase() != 'a') {
                    return false;
                }
            }

            currentSlideId = currentContainer.getAttribute('data-magic-slide');
            newSlideId = element.getAttribute('data-magic-slide-id');
            if (currentSlideId == newSlideId/* && currentSlideId == 'zoom'*/) {
                if (isMagicZoom) {
                    allowHighlightActiveSelectorOnUpdate = false;
                }
                mtHighlightActiveSelector(element);
                event.preventDefault ? event.preventDefault() : (event.returnValue = false);
                return false;
            }

            //NOTE: check when one image + 360 selector
            newContainer = document.querySelector('div[data-magic-slide="'+newSlideId+'"]');

            if (!newContainer) {
                return false;
            }

            if (newSlideId == 'zoom' && isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            //NOTE: switch slide container
            currentContainer.className = currentContainer.className.replace(/(\s|^)mt-active(\s|$)/, ' ');
            newContainer.className += ' mt-active';

            if (newSlideId == 'zoom') {
                if (isMagicZoom) {
                    //NOTE: hide image to skip magiczoom(plus) switching effect
                    if (!$mjs(element).jHasClass('mz-thumb-selected')) {
                        document.querySelector('#'+magicToolboxToolMainId+' .mz-figure > img').style.visibility = 'hidden';
                    }
                    //NOTE: switch image
                    MagicZoom.switchTo(magicToolboxToolMainId, element);
                    allowHighlightActiveSelectorOnUpdate = false;
                }
                mtHighlightActiveSelector(element);
            }

            var videoType = null;

            //NOTE: stop previous video slide
            if (currentSlideId.match(/^video\-\d+$/)) {
                //NOTE: need to stop current video
                var iframe = currentContainer.querySelector('iframe');
                if (iframe) {
                    videoType = iframe.getAttribute('data-video-type');
                    if (videoType == 'vimeo') {
                        var vimeoPlayer = new Vimeo.Player(iframe);
                        if (vimeoPlayer) {
                            vimeoPlayer.pause();
                        }
                    } else if (videoType == 'youtube') {
                        pauseYoutubePlayer(iframe);
                    }
                }
            }

            //NOTE: load api for video if need it
            if (newSlideId.match(/^video\-\d+$/)) {
                videoType = element.getAttribute('data-video-type');
                if (videoType == 'vimeo') {
                    loadVimeoJSFramework();
                } else if (videoType == 'youtube') {
                    loadYoutubeApi();
                }
                mtHighlightActiveSelector(element);
            }

            if (newSlideId == '360') {
                mtHighlightActiveSelector(element);
            }

            event.preventDefault ? event.preventDefault() : (event.returnValue = false);

            return false;
        },
        switchEvent,
        magicToolboxLinks = $('.magictoolbox-selector');

    if (isMagicZoom || magicToolboxTool == 'magicthumb') {

        if (isMagicZoom) {
            switchEvent = (magicToolboxSwitchMetod == 'click' ? 'btnclick' : magicToolboxSwitchMetod);
        }
        mtFindAndHighlightActiveSelector();
        //NOTE: a[data-magic-slide-id]
        for (var j = 0; j < magicToolboxLinks.length; j++) {
            if (isMagicZoom) {
                $mjs(magicToolboxLinks[j])['jAddEvent'](switchEvent+' tap', switchFunction, 1);
            } else if (magicToolboxTool == 'magicthumb') {
                $mjs(magicToolboxLinks[j])['jAddEvent'](magicToolboxSwitchMetod+' tap', switchFunction);
            }
        }
    }

}

function mtHighlightActiveSelector(selectedElement) {
    //NOTE: if element is already highlighted
    if ($(selectedElement).hasClass('active-selector')) {
        return;
    }
    //NOTE: to highlight selector when switching thumbnails
    var selectors = $('.magictoolbox-selector');
    $(selectors).removeClass('active-selector');
    selectedElement && $(selectedElement).addClass('active-selector');
    if (originalLayout) {
        if (isPrestaShop17x) {
            $(selectors).removeClass('selected');
            selectedElement && $(selectedElement).addClass('selected');
        } else {
            $(selectors).removeClass('shown');
            selectedElement && $(selectedElement).addClass('shown');
        }
    }
}

function mtFindAndHighlightActiveSelector() {
    var activeSlide, slideId, query, thumbnail;
    activeSlide = document.querySelector('.magic-slide.mt-active');
    if (activeSlide) {
        slideId = activeSlide.getAttribute('data-magic-slide');
        query = slideId != 'zoom' ? '[data-magic-slide-id="'+slideId+'"]' : '.mz-thumb.mz-thumb-selected';
        thumbnail = document.querySelector(query);
        if (thumbnail) {
            mtHighlightActiveSelector(thumbnail);
        }
    }
}

function mtClickElement(element, eventType, eventName) {
    var event;
    if (document.createEvent) {
        event = document.createEvent(eventType);
        event.initEvent(eventName, true, true);
        element.dispatchEvent(event);
    } else {
        event = document.createEventObject();
        event.eventType = eventType;
        element.fireEvent('on' + eventName, event);
    }
    return event;
}

function mtSwitchContainer(selector) {

    var currentContainer = document.querySelector('.mt-active'),
        currentSlideId = null,
        newSlideId = null,
        newContainer = null,
        switchContainer = false;

    if (!currentContainer) {
        return false;
    }

    currentSlideId = currentContainer.getAttribute('data-magic-slide');
    newSlideId = selector.getAttribute('data-magic-slide-id');
    if (currentSlideId == newSlideId) {
        return false;
    }

    newContainer = document.querySelector('div[data-magic-slide="'+newSlideId+'"]');

    if (!newContainer) {
        return false;
    }

    //NOTE: switch container
    currentContainer.className = currentContainer.className.replace(/(\s|^)mt-active(\s|$)/, ' ');
    newContainer.className += ' mt-active';

    var videoType = null;

    //NOTE: stop previous video slide
    if (currentSlideId.match(/^video\-\d+$/)) {
        var iframe = currentContainer.querySelector('iframe');
        if (iframe) {
            videoType = iframe.getAttribute('data-video-type');
            if (videoType == 'vimeo') {
                var vimeoPlayer = new Vimeo.Player(iframe);
                if (vimeoPlayer) {
                    vimeoPlayer.pause();
                }
            } else if (videoType == 'youtube') {
                pauseYoutubePlayer(iframe);
            }
        }
    }

    return false;
}
