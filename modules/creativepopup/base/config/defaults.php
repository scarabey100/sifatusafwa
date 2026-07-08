<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'creativepopup/classes/CpHelper.php';

$cpDefaults = [
    'slider' => [
        'createdWith' => [
            'value' => '',
            'keys' => 'createdWith',
        ],

        'popupVersion' => [
            'value' => '',
            'keys' => 'popupVersion',
            'props' => [
                'forceoutput' => true,
            ],
        ],

        'status' => [
            'value' => false,
            'name' => cp__('Published'),
            'keys' => 'status',
            'desc' => cp__('Unpublished popups will not be visible for your visitors until you enable this option.'),
            'props' => [
                'meta' => true,
            ],
        ],

        'scheduleStart' => [
            'value' => '',
            'name' => cp__('Start on'),
            'keys' => 'schedule_start',
            'desc' => cp__(
                'Scheduled popups will only be visible to your visitors between the time period you set here.<br>' .
                "We're using international date and time format to avoid ambiguity."
            ),
            'attrs' => [
                'placeholder' => cp__('No schedule'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'scheduleEnd' => [
            'value' => '',
            'name' => cp__('Stop on'),
            'keys' => 'schedule_end',
            'desc' => cp__(
                'Clear these text fields and left them empty if you want to cancel the schedule.<br>' .
                '<span>IMPORTANT:</span> You will still need to set the popup status as published'
            ),
            'attrs' => [
                'placeholder' => cp__('No schedule'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        // ============= //
        // |   Layout  | //
        // ============= //

        // responsive | fullwidth | fullsize | fixedsize
        'type' => [
            'value' => 'responsive',
            'name' => cp__('Popup type'),
            'keys' => 'type',
            'desc' => '',
            'attrs' => [
                'type' => 'hidden',
            ],
        ],

        'maxRatio' => [
            'value' => '',
            'name' => cp__('Maximum responsive ratio'),
            'keys' => 'maxRatio',
            'desc' => cp__(
                'The popup will not enlarge your layers above the target ratio. ' .
                'The value 1 will keep your layers in their initial size, without any upscaling.'
            ),
            'advanced' => true,
        ],

        'clipSlideTransition' => [
            'value' => 'disabled',
            'name' => cp__('Clip page transition'),
            'keys' => 'clipSlideTransition',
            'desc' => cp__(
                'Choose on which axis (if any) you want to clip the overflowing content ' .
                '(i.e. that breaks outside of the popup bounds).'
            ),
            'advanced' => true,
            'options' => [
                'disabled' => cp__('Do not hide'),
                'enabled' => cp__('Hide on both axis'),
                'x' => cp__('X Axis'),
                'y' => cp__('Y Axis'),
            ],
        ],

        // == COMPATIBILITY ==

        'responsiveness' => [
            'value' => true,
            'keys' => 'responsive',
            'props' => [
                'meta' => true,
                'output' => true,
            ],
        ],
        'fullWidth' => [
            'value' => false,
            'keys' => 'forceresponsive',
            'props' => [
                'meta' => true,
                'output' => true,
            ],
        ],

        // == END OF COMPATIBILITY ==

        'slideBGSize' => [
            'value' => 'cover',
            'name' => cp__('Background size'),
            'keys' => 'slideBGSize',
            'desc' => cp__(
                'This will be used as a default on all pages, ' .
                'unless you choose to explicitly override it on a per page basis.'
            ),
            'options' => [
                'auto' => cp__('Auto'),
                'cover' => cp__('Cover'),
                'contain' => cp__('Contain'),
                '100% 100%' => cp__('Stretch'),
            ],
        ],

        'slideBGPosition' => [
            'value' => '50% 50%',
            'name' => cp__('Background position'),
            'keys' => 'slideBGPosition',
            'desc' => cp__(
                'This will be used as a default on all pages, ' .
                'unless you choose the explicitly override it on a per page basis.'
            ),
            'options' => [
                '0% 0%' => cp__('left top'),
                '0% 50%' => cp__('left center'),
                '0% 100%' => cp__('left bottom'),
                '50% 0%' => cp__('center top'),
                '50% 50%' => cp__('center center'),
                '50% 100%' => cp__('center bottom'),
                '100% 0%' => cp__('right top'),
                '100% 50%' => cp__('right center'),
                '100% 100%' => cp__('right bottom'),
            ],
        ],

        'parallaxSensitivity' => [
            'value' => 10,
            'name' => cp__('Parallax sensitivity'),
            'keys' => 'parallaxSensitivity',
            'desc' => cp__(
                'Increase or decrease the sensitivity of parallax content ' .
                'when moving your mouse cursor or tilting your mobile device.'
            ),
        ],

        'parallaxCenterLayers' => [
            'value' => 'center',
            'name' => cp__('Parallax center layers'),
            'keys' => 'parallaxCenterLayers',
            'desc' => cp__(
                'Choose a center point for parallax content ' .
                'where all layers will be aligned perfectly according to their original position.'
            ),
            'options' => [
                'center' => cp__('At center of the viewport'),
                'top' => cp__('At the top of the viewport'),
            ],
        ],

        'parallaxCenterDegree' => [
            'value' => 40,
            'name' => cp__('Parallax center degree'),
            'keys' => 'parallaxCenterDegree',
            'desc' => cp__(
                'Provide a comfortable holding position (in degrees) for mobile devices, ' .
                'which should be the center point for parallax content where all layers should align perfectly.'
            ),
        ],

        'parallaxScrollReverse' => [
            'value' => false,
            'name' => 'Reverse scroll direction',
            'keys' => 'parallaxScrollReverse',
            'desc' => cp__('Your parallax layers will move to the opposite direction when scrolling the page.'),
        ],

        // ================= //
        // |    Mobile    | //
        // ================= //

        'optimizeForMobile' => [
            'value' => true,
            'name' => cp__('Optimize for mobile'),
            'keys' => 'optimizeForMobile',
            'advanced' => true,
            'desc' => cp__(
                'Enable optimizations on mobile devices to avoid performance issues (e.g. fewer tiles ' .
                'in page transitions, reducing performance-heavy effects with very similar results, etc).'
            ),
        ],

        'disableOnMobile' => [
            'value' => false,
            'name' => cp__('Disable on mobile'),
            'keys' => 'disableonmobile',
            'desc' => cp__('Disable the popup on mobile devices.'),
            'props' => ['meta' => true],
        ],

        'disableOnTablet' => [
            'value' => false,
            'name' => cp__('Disable on tablet'),
            'keys' => 'disableontablet',
            'desc' => cp__('Disable the popup on tablet devices.'),
            'props' => ['meta' => true],
        ],

        'disableOnDesktop' => [
            'value' => false,
            'name' => cp__('Disable on desktop'),
            'keys' => 'disableondesktop',
            'desc' => cp__('Disable the popup on desktop devices.'),
            'props' => ['meta' => true],
        ],

        // Hides the popup under the given value of browser width in pixels.
        // Defaults to: 0
        'hideUnder' => [
            'value' => '',
            'name' => cp__('Hide under'),
            'keys' => ['hideunder', 'hideUnder'],
            'desc' => cp__('Hides the popup when the viewport width goes under the specified value.'),
            'attrs' => [
                'type' => 'number',
                'min' => -1,
            ],
        ],

        // Hides the popup over the given value of browser width in pixel.
        // Defaults to: 100000
        'hideOver' => [
            'value' => '',
            'name' => cp__('Hide over'),
            'keys' => ['hideover', 'hideOver'],
            'desc' => cp__('Hides the popup when the viewport becomes wider than the specified value.'),
            'attrs' => [
                'type' => 'number',
                'min' => -1,
            ],
        ],

        'slideOnSwipe' => [
            'value' => true,
            'name' => cp__('Use slide effect when swiping'),
            'keys' => 'slideOnSwipe',
            'desc' => cp__(
                'Ignore selected page transitions and use sliding effects only ' .
                'when users are changing pages with a swipe gesture on mobile devices.'
            ),
        ],

        // ================ //
        // |   Slideshow  | //
        // ================ //

        // Automatically start popup.
        'autoStart' => [
            'value' => false,
            'name' => cp__('Auto-play pages'),
            'keys' => ['autostart', 'autoStart'],
            'desc' => cp__('Next page will automatically play after actual page is finished.'),
        ],

        'hashChange' => [
            'value' => false,
            'name' => cp__('Change URL hash'),
            'keys' => 'hashChange',
            'desc' => cp__(
                'Updates the hash in the site URL ' .
                'when changing pages based on the deeplinks you’ve set to your pages. ' .
                'This makes it possible to share URLs that will start the popup with the currently visible page.'
            ),
            'advanced' => true,
        ],

        'pauseLayers' => [
            'value' => false,
            'name' => cp__('Pause layers'),
            'keys' => 'pauseLayers',
            'desc' => cp__(
                'If you enable this option, ' .
                'layer transitions will not start playing as long the pageshow is in a paused state.'
            ),
            'advanced' => true,
        ],

        'pauseOnHover' => [
            'value' => 'enabled',
            'name' => cp__('Pause on hover'),
            'keys' => ['pauseonhover', 'pauseOnHover'],
            'options' => [
                'disabled' => cp__('Disabled'),
                'enabled' => cp__('Pause pageshow'),
                'layers' => cp__('Pause pageshow and layer transitions'),
                'looplayers' => cp__('Pause pageshow and layer transitions, including loops'),
            ],
            'desc' => cp__('Decide what should happen when you move your mouse cursor over the popup.'),
        ],

        // The starting page of a popup. Non-index value, starts with 1.
        'firstSlide' => [
            'value' => 1,
            'name' => cp__('Start with page'),
            'keys' => ['firstlayer', 'firstSlide'],
            'desc' => cp__('The popup will start with the specified page. You can also use the value "random".'),
            'attrs' => ['type' => 'text', 'data-options' => '["random"]'],
        ],

        // Use global shortcuts to control the popup.
        'keybNavigation' => [
            'value' => false,
            'name' => cp__('Keyboard navigation'),
            'keys' => ['keybnav', 'keybNav'],
            'desc' => cp__('You can navigate through pages with the left and right arrow keys.'),
        ],

        // Accepts touch gestures if enabled.
        'touchNavigation' => [
            'value' => false,
            'name' => cp__('Touch navigation'),
            'keys' => ['touchnav', 'touchNav'],
            'desc' => cp__('Gesture-based navigation when swiping on touch-enabled devices.'),
        ],

        // Number of loops taking by the popup.
        // Depends on: shuffle. Defaults to: 0 => infinite
        'loops' => [
            'value' => 0,
            'name' => cp__('Cycles'),
            'keys' => ['loops', 'cycles'],
            'desc' => cp__('Number of cycles if auto-play is enabled. (0 means infinity)'),
            'attrs' => [
                'type' => 'number',
                'min' => 0,
            ],
        ],

        // The popup will always stop at the given number of
        // loops, even when the user restarts popup.
        // Depends on: loop. Defaults to: true
        'forceLoopNumber' => [
            'value' => true,
            'name' => cp__('Force number of cycles'),
            'keys' => ['forceloopnum', 'forceCycles'],
            'advanced' => true,
            'desc' => cp__('The popup will always stop at the given number of cycles, even if the pageshow restarts.'),
        ],

        // The popup will change pages in random order.
        'shuffle' => [
            'value' => false,
            'name' => cp__('Shuffle mode'),
            'keys' => ['randomslideshow', 'shuffleSlideshow'],
            'desc' => cp__('Pages will proceed in random order.'),
        ],

        // Whether popup should goind backwards or not
        // when you switch to a previous page.
        'twoWaySlideshow' => [
            'value' => false,
            'name' => cp__('Two way pageshow'),
            'keys' => ['twowayslideshow', 'twoWaySlideshow'],
            'advanced' => true,
            'desc' => cp__('Pageshow can go backwards if someone switches to a previous page.'),
        ],

        'forceLayersOutDuration' => [
            'value' => 750,
            'name' => cp__('Forced animation duration'),
            'keys' => 'forceLayersOutDuration',
            'advanced' => true,
            'desc' => cp__(
                'The animation speed in milliseconds ' .
                'when the popup forces remaining layers out of scene before swapping pages.'
            ),
            'attrs' => [
                'min' => 0,
            ],
        ],

        // ================= //
        // |   Appearance  | //
        // ================= //

        // The default skin.
        'skin' => [
            'value' => 'noskin',
            'name' => cp__('Skin'),
            'keys' => 'skin',
            'desc' => cp__(
                "The skin used for this popup. The 'noskin' skin is a border- and buttonless skin. " .
                'Your custom skins will appear in the list when you create their folders.'
            ),
            'props' => [
                'output' => true,
            ],
        ],

        'sliderFadeInDuration' => [
            'value' => 0,
            'name' => cp__('Initial fade duration'),
            'keys' => ['sliderfadeinduration', 'sliderFadeInDuration'],
            'advanced' => true,
            'desc' => cp__(
                'Change the duration of the initial fade animation when the page loads. Enter 0 to disable fading.'
            ),
            'attrs' => [
                'min' => 0,
            ],
        ],

        'popupClasses' => [
            'value' => '',
            'name' => cp__('Popup Classes'),
            'keys' => 'popupclass',
            'desc' => cp__('One or more space-separated class names to be added to the popup container element.'),
            'props' => [
                'meta' => true,
            ],
        ],

        // Some CSS values you can append on each page individually
        // to make some adjustments if needed.
        'sliderStyle' => [
            'value' => '',
            'name' => cp__('Popup CSS'),
            'keys' => ['sliderstyle', 'sliderStyle'],
            'desc' => cp__(
                'You can enter custom CSS to change some style properties on the popup wrapper element. ' .
                'More complex CSS should be applied with the Custom Styles Editor.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        // Global background color on all pages.
        'globalBGColor' => [
            'value' => '',
            'name' => cp__('Background color'),
            'keys' => ['backgroundcolor', 'globalBGColor'],
            'desc' => cp__(
                'Global background color of the popup. Pages with non-transparent background will cover this one. ' .
                'You can use all CSS methods such as HEX or RGB(A) values.'
            ),
        ],

        // Global background image on all pages.
        'globalBGImage' => [
            'value' => '',
            'name' => cp__('Background image'),
            'keys' => ['backgroundimage', 'globalBGImage'],
            'desc' => cp__(
                'Global background image of the popup. Pages with non-transparent backgrounds will cover it. ' .
                'This image will not scale in responsive mode.'
            ),
        ],

        'globalBGImageId' => [
            'value' => '',
            'keys' => ['backgroundimageId', 'globalBGImageId'],
            'props' => [
                'meta' => true,
            ],
        ],

        // Global background image repeat
        'globalBGRepeat' => [
            'value' => 'no-repeat',
            'name' => cp__('Background repeat'),
            'keys' => 'globalBGRepeat',
            'desc' => cp__('Global background image repeat.'),
            'options' => [
                'no-repeat' => cp__('No-repeat'),
                'repeat' => cp__('Repeat'),
                'repeat-x' => cp__('Repeat-x'),
                'repeat-y' => cp__('Repeat-y'),
            ],
        ],

        // Global background image behavior
        'globalBGAttachment' => [
            'value' => 'scroll',
            'name' => cp__('Background behavior'),
            'keys' => 'globalBGAttachment',
            'desc' => cp__('Choose between a scrollable or fixed global background image.'),
            'options' => [
                'scroll' => cp__('Scroll'),
                'fixed' => cp__('Fixed'),
            ],
        ],

        // Global background image position
        'globalBGPosition' => [
            'value' => '50% 50%',
            'name' => cp__('Background position'),
            'keys' => 'globalBGPosition',
            'desc' => cp__(
                'Global background image position of the popup. ' .
                'The first value is the horizontal position and the second value is the vertical.'
            ),
        ],

        // Global background image size
        'globalBGSize' => [
            'value' => 'cover',
            'name' => cp__('Background size'),
            'keys' => 'globalBGSize',
            'desc' => cp__(
                'Global background size of the popup. ' .
                'You can set the size in pixels, percentages, or constants: auto | cover | contain '
            ),
            'attrs' => [
                'data-options' => '[{
                    "name": "auto",
                    "value": "auto"
                }, {
                    "name": "cover",
                    "value": "cover"
                }, {
                    "name": "contain",
                    "value": "contain"
                }, {
                    "name": "stretch",
                    "value": "100% 100%"
                }]',
            ],
        ],

        // ================= //
        // |   Navigation  | //
        // ================= //

        // Show the next and previous buttons.
        'navPrevNextButtons' => [
            'value' => false,
            'name' => cp__('Show Prev & Next buttons'),
            'keys' => ['navprevnext', 'navPrevNext'],
            'desc' => cp__('Disabling this option will hide the Prev and Next buttons.'),
        ],

        // Show the next and previous buttons
        // only when hovering over the popup.
        'hoverPrevNextButtons' => [
            'value' => false,
            'name' => cp__('Show Prev & Next buttons on hover'),
            'keys' => ['hoverprevnext', 'hoverPrevNext'],
            'desc' => cp__(
                'Show the buttons only when someone moves the mouse cursor over the popup. ' .
                'This option depends on the previous setting.'
            ),
        ],

        // Show the start and stop buttons
        'navStartStopButtons' => [
            'value' => false,
            'name' => cp__('Show Start & Stop buttons'),
            'keys' => ['navstartstop', 'navStartStop'],
            'desc' => cp__('Disabling this option will hide the Start & Stop buttons.'),
        ],

        // Show the page buttons or thumbnails.
        'navSlideButtons' => [
            'value' => false,
            'name' => cp__('Show page navigation buttons'),
            'keys' => ['navbuttons', 'navButtons'],
            'desc' => cp__('Disabling this option will hide page navigation buttons or thumbnails.'),
        ],

        // Show the popup buttons or thumbnails
        // ony when hovering over the popup.
        'hoverSlideButtons' => [
            'value' => false,
            'name' => cp__('Page navigation on hover'),
            'keys' => ['hoverbottomnav', 'hoverBottomNav'],
            'desc' => cp__('Page navigation buttons (including thumbnails) will be shown on mouse hover only.'),
        ],

        // Show bar timer
        'barTimer' => [
            'value' => false,
            'name' => cp__('Show bar timer'),
            'keys' => ['bartimer', 'showBarTimer'],
            'desc' => cp__('Show the bar timer to indicate pageshow progression.'),
        ],

        // Show circle timer. Requires CSS3 capable browser.
        // This setting will overrule the 'barTimer' option.
        'circleTimer' => [
            'value' => false,
            'name' => cp__('Show circle timer'),
            'keys' => ['circletimer', 'showCircleTimer'],
            'desc' => cp__('Use circle timer to indicate pageshow progression.'),
        ],

        'slideBarTimer' => [
            'value' => false,
            'name' => cp__('Show pagebar timer'),
            'keys' => ['slidebartimer', 'showSlideBarTimer'],
            'desc' => cp__('You can grab the pagebar timer playhead and seek the whole page real-time like a movie.'),
        ],

        // ========================== //
        // |  Thumbnail navigation  | //
        // ========================== //

        // Use thumbnails for page buttons
        // Depends on: navSlideButtons.
        // Possible values: 'disabled', 'hover', 'always'
        'thumbnailNavigation' => [
            'value' => 'hover',
            'name' => cp__('Thumbnail navigation'),
            'keys' => ['thumb_nav', 'thumbnailNavigation'],
            'desc' => cp__('Use thumbnail navigation instead of page bullet buttons.'),
            'options' => [
                'disabled' => cp__('Disabled'),
                'hover' => cp__('Hover'),
                'always' => cp__('Always'),
            ],
        ],

        // The width of the thumbnail area in percents.
        'thumbnailAreaWidth' => [
            'value' => '60%',
            'name' => cp__('Thumbnail container width'),
            'keys' => ['thumb_container_width', 'tnContainerWidth'],
            'desc' => cp__('The width of the thumbnail area relative to the popup size.'),
        ],

        // Thumbnails' width in pixels.
        'thumbnailWidth' => [
            'value' => 100,
            'name' => cp__('Thumbnail width'),
            'keys' => ['thumb_width', 'tnWidth'],
            'desc' => cp__('The width of thumbnails in the navigation area.'),
            'attrs' => [
                'min' => 0,
            ],
        ],

        // Thumbnails' height in pixels.
        'thumbnailHeight' => [
            'value' => 60,
            'name' => cp__('Thumbnail height'),
            'keys' => ['thumb_height', 'tnHeight'],
            'desc' => cp__('The height of thumbnails in the navigation area.'),
            'attrs' => [
                'min' => 0,
            ],
        ],

        // The opacity of the active thumbnail in percents.
        'thumbnailActiveOpacity' => [
            'value' => 35,
            'name' => cp__('Active thumbnail opacity'),
            'keys' => ['thumb_active_opacity', 'tnActiveOpacity'],
            'desc' => cp__("Opacity in percentage of the active page's thumbnail."),
            'attrs' => [
                'min' => 0,
                'max' => 100,
            ],
        ],

        // The opacity of inactive thumbnails in percents.
        'thumbnailInactiveOpacity' => [
            'value' => 100,
            'name' => cp__('Inactive thumbnail opacity'),
            'keys' => ['thumb_inactive_opacity', 'tnInactiveOpacity'],
            'desc' => cp__('Opacity in percentage of inactive page thumbnails.'),
            'attrs' => [
                'min' => 0,
                'max' => 100,
            ],
        ],

        // ============ //
        // |  Videos  | //
        // ============ //

        // Automatically starts vidoes on the given page.
        'autoPlayVideos' => [
            'value' => true,
            'name' => cp__('Automatically play videos'),
            'keys' => ['autoplayvideos', 'autoPlayVideos'],
            'desc' => cp__('Videos will be automatically started on the active page.'),
        ],

        // Automatically pauses the pageshow when a video is playing.
        // Auto means it only pauses the pageshow while the video is playing.
        // Possible values: 'auto', 'enabled', 'disabled'
        'autoPauseSlideshow' => [
            'value' => 'auto',
            'name' => cp__('Pause pageshow'),
            'keys' => ['autopauseslideshow', 'autoPauseSlideshow'],
            'desc' => cp__(
                'The pageshow can temporally be paused while videos are playing. ' .
                'You can choose to permanently stop the pause until manual restarting.'
            ),
            'options' => [
                'auto' => cp__('While playing'),
                'enabled' => cp__('Permanently'),
                'disabled' => cp__('No action'),
            ],
        ],

        // The preview image quality of a YouTube video.
        // Some videos doesn't have HD preview images and
        // you may have to lower the quality settings.
        // Possible values:
        // - 'maxresdefault.jpg',
        // - 'hqdefault.jpg',
        // - 'mqdefault.jpg',
        // - 'default.jpg'
        'youtubePreviewQuality' => [
            'value' => 'maxresdefault.jpg',
            'name' => cp__('Youtube preview'),
            'keys' => ['youtubepreview', 'youtubePreview'],
            'desc' => cp__(
                'The automatically fetched preview image quaility for YouTube videos when you do not set your own. ' .
                'Please note, some videos do not have HD previews, and you may need to choose a lower quaility.'
            ),
            'options' => [
                'maxresdefault.jpg' => cp__('Maximum quality'),
                'hqdefault.jpg' => cp__('High quality'),
                'mqdefault.jpg' => cp__('Medium quality'),
                'default.jpg' => cp__('Default quality'),
            ],
        ],

        // =========== //
        // |  Popup  | //
        // =========== //

        'popupShowOnClick' => [
            'value' => '',
            'name' => cp__('Open by click'),
            'keys' => 'popupShowOnClick',
            'desc' => cp__(
                'Enter a selector (CSS / jQuery) to open the Popup by clicking on the target element(s). ' .
                'Acting as a toggle, a secondary click will close the Popup. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
            'attrs' => [
                'placeholder' => '#id, .class',
            ],
        ],

        'popupAvoidMultiple' => [
            'value' => '',
            'name' => cp__('Avoid multiple popups'),
            'keys' => 'popupAvoidMultiple',
            'desc' => cp__(
                'If another popup is already on the screen, then this popup will open only after the other will close.'
            ),
        ],

        'popupContainer' => [
            'value' => 'body',
            'name' => cp__('Popup container'),
            'keys' => 'popupContainer',
            'desc' => cp__('Enter a selector (CSS / jQuery) where the popup will be placed.'),
            'attrs' => [
                'placeholder' => '#id, .class',
            ],
            'advanced' => true,
        ],

        'popupShowOnScroll' => [
            'value' => '',
            'name' => cp__('Open at scroll position'),
            'keys' => 'popupShowOnScroll',
            'desc' => cp__(
                'Enter a scroll position in pixels or percents, ' .
                'which will open the Popup when visitors scroll to that location. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
        ],

        'popupCloseOnScroll' => [
            'value' => '',
            'name' => cp__('Close at scroll position'),
            'keys' => 'popupCloseOnScroll',
            'desc' => cp__(
                'Enter a scroll position in pixels or percents, ' .
                'which will close the Popup when visitors scroll to that location. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
        ],

        'popupCloseOnTimeout' => [
            'value' => '',
            'name' => cp__('Close automatically after'),
            'keys' => 'popupCloseOnTimeout',
            'desc' => cp__(
                'Automatically closes the Popup in the specified number of seconds after it was opened. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
        ],

        'popupCloseOnSliderEnd' => [
            'value' => false,
            'name' => cp__('Close on popup end'),
            'keys' => 'popupCloseOnSliderEnd',
            'desc' => cp__(
                'Closes the Popup after the pageshow has completed a full cycle and all your pages were displayed.'
            ),
        ],

        'popupPreventCloseOnEsc' => [
            'value' => false,
            'name' => cp__('Prevent closing on Esc key'),
            'keys' => 'popupPreventCloseOnEsc',
            'desc' => cp__(
                'Turn on this option to prevent users from being able to close the popup by pressing the ESC key.'
            ),
        ],

        'popupShowOnLeave' => [
            'value' => false,
            'name' => cp__('Before leaving the page'),
            'keys' => 'popupShowOnLeave',
            'desc' => cp__(
                'Opens the Popup before leaving the page. A leave intent is considered ' .
                'when visitors leave the browser window with their mouse cursor in the direction ' .
                'where the window controls and the tab bar is located.'
            ),
        ],

        'popupShowOnIdle' => [
            'value' => '',
            'name' => cp__('Open when idle for'),
            'keys' => 'popupShowOnIdle',
            'desc' => cp__(
                'Opens the Popup after the specified number of seconds ' .
                'when the user is inactive without moving the mouse cursor or pressing any button. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
        ],

        'popupShowOnTimeout' => [
            'value' => '',
            'name' => cp__('Open automatically after'),
            'keys' => 'popupShowOnTimeout',
            'desc' => cp__(
                'Automatically opens the Popup after the specified number of seconds. ' .
                'Leave this field empty if you don’t want to use this trigger.'
            ),
        ],

        'popupShowOnce' => [
            'value' => true,
            'name' => cp__('Prevent reopening'),
            'keys' => 'popupShowOnce',
            'desc' => cp__(
                'Depending on your settings, the same Popup can be displayed in multiple times ' .
                'without reloading the page. Such example would be ' .
                'when you use a scroll trigger and the user scrolls to that location a number of times. ' .
                'Enabling this option will prevent opening this Popup consequently.'
            ),
        ],

        'popupDisableOverlay' => [
            'value' => false,
            'name' => cp__('Disable overlay'),
            'keys' => 'popupDisableOverlay',
            'desc' => cp__('Disable this option to hide the overlay behind the Popup.'),
        ],

        'popupShowCloseButton' => [
            'value' => true,
            'name' => cp__('Show close button'),
            'keys' => 'popupShowCloseButton',
            'desc' => cp__(
                'Disable this option to hide the Popup close button. ' .
                'This option is also useful when you would like to use a custom close button. ' .
                'To do that, select the “Close the Popup” option from the layer linking field.'
            ),
        ],

        'popupAjaxLoadColor' => [
            'value' => '#ffffff',
            'name' => cp__('AJAX loader color'),
            'keys' => 'popupAjaxLoadColor',
            'desc' => cp__('The AJAX loader color. You can use color names, hexadecimal, RGB or RGBA values.'),
        ],

        'popupCloseButtonStyle' => [
            'value' => '',
            'name' => cp__('Close button custom CSS'),
            'keys' => 'popupCloseButtonStyle',
            'desc' => cp__(
                'Enter a list of CSS properties, ' .
                'which will be applied to the built-in close button (if enabled) to customize it’s appearance.'
            ),
            'advanced' => true,
        ],

        'popupOverlayClickToClose' => [
            'value' => true,
            'name' => cp__('Close by clicking away'),
            'keys' => 'popupOverlayClickToClose',
            'desc' => cp__('Close the Popup by clicking on the overlay.'),
        ],

        'popupStartSliderImmediately' => [
            'value' => true,
            'name' => cp__('Start popup immediately'),
            'keys' => 'popupStartSliderImmediately',
            'desc' => cp__(
                'Enable this option to start your popup immediately, ' .
                'without waiting for the Popup to complete its opening transition.'
            ),
            'advanced' => true,
        ],

        'popupResetOnClose' => [
            'value' => 'slide',
            'name' => cp__('Reset on close'),
            'keys' => 'popupResetOnClose',
            'desc' => cp__(
                'Choose whether the popup should play all page transitions over again when re-opening the popup.'
            ),
            'advanced' => true,
            'options' => [
                'disabled' => cp__('Disabled'),
                'slide' => cp__('Enabled'),
            ],
        ],
        /*
        'popupCustomStyle' => [
            'value' => '',
            'name' => cp__('Popup custom CSS'),
            'keys' => 'popupCustomStyle',
            'desc' => cp__(
                'Enter CSS properties, which will be applied to the popup main container element to customize it’s appearance.'
            ),
        ],
        */
        'popupWidth' => [
            'value' => 640,
            'name' => cp__('Popup Width'),
            'keys' => 'popupWidth',
            'attrs' => [
                'type' => 'number',
                'min' => 0,
            ],
            'props' => [
                'output' => true,
            ],
        ],

        'popupHeight' => [
            'value' => 360,
            'name' => cp__('Popup Height'),
            'keys' => 'popupHeight',
            'attrs' => [
                'type' => 'number',
                'min' => 0,
            ],
            'props' => [
                'output' => true,
            ],
        ],

        'popupFitWidth' => [
            'value' => false,
            'name' => cp__('Fit Width'),
            'keys' => 'popupFitWidth',
        ],

        'popupFitHeight' => [
            'value' => false,
            'name' => cp__('Fit Height'),
            'keys' => 'popupFitHeight',
        ],

        'popupPositionHorizontal' => [
            'value' => 'center',
            'keys' => 'popupPositionHorizontal',
        ],

        'popupPositionVertical' => [
            'value' => 'middle',
            'keys' => 'popupPositionVertical',
        ],

        'popupDistanceLeft' => [
            'value' => 10,
            'name' => cp__('Distance left'),
            'keys' => 'popupDistanceLeft',
            'tooltip' => cp__('Distance specified in pixels from the left side of the browser window.'),
        ],

        'popupDistanceRight' => [
            'value' => 10,
            'name' => cp__('Distance right'),
            'keys' => 'popupDistanceRight',
            'tooltip' => cp__('Distance specified in pixels from the right side of the browser window.'),
        ],

        'popupDistanceTop' => [
            'value' => 10,
            'name' => cp__('Distance top'),
            'keys' => 'popupDistanceTop',
            'tooltip' => cp__('Distance specified in pixels from the top of the browser window.'),
        ],

        'popupDistanceBottom' => [
            'value' => 10,
            'name' => cp__('Distance bottom'),
            'keys' => 'popupDistanceBottom',
            'tooltip' => cp__('Distance specified in pixels from the bottom of the browser window.'),
        ],

        'popupDurationIn' => [
            'value' => 1000,
            'name' => cp__('Opening duration'),
            'keys' => 'popupDurationIn',
            'desc' => cp__(
                'The Popup opening transition duration specified in milliseconds. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => [
                'min' => 0,
                'step' => 100,
            ],
        ],

        'popupDurationOut' => [
            'value' => 500,
            'name' => cp__('Closing duration'),
            'keys' => 'popupDurationOut',
            'desc' => cp__(
                'The Popup closing transition duration specified in milliseconds. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => [
                'min' => 0,
                'step' => 100,
            ],
        ],

        'popupDelayIn' => [
            'value' => 200,
            'name' => cp__('Opening delay'),
            'keys' => 'popupDelayIn',
            'desc' => cp__(
                'Delay before opening the Popup specified in milliseconds. A second equals to 1000 milliseconds.'
            ),
            'advanced' => true,
            'attrs' => [
                'min' => 0,
                'step' => 100,
            ],
        ],
        /*
        'popupEaseIn' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Opening easing'),
            'keys' => 'popupEaseIn',
            'desc' => cp__(
                'The timing function of the animation. With it you can manipulate the movement of animated objects. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],

        'popupEaseOut' => [
            'value' => 'easeInQuint',
            'name' => cp__('Closing easing'),
            'keys' => 'popupEaseOut',
            'desc' => cp__(
                'The timing function of the animation. With it you can manipulate the movement of animated objects. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],
        */
        'popupTransitionIn' => [
            'value' => 'fade',
            'name' => cp__('Opening transition'),
            'keys' => 'popupTransitionIn',
            'desc' => cp__('Choose from one of the pre-defined Popup opening transitions.'),
            'options' => [
                'fade' => cp__('Fade'),
                'slidefromtop' => cp__('Slide from top'),
                'slidefrombottom' => cp__('Slide from bottom'),
                'slidefromleft' => cp__('Slide from left'),
                'slidefromright' => cp__('Slide from right'),
                'rotatefromtop' => cp__('Rotate from top'),
                'rotatefrombottom' => cp__('Rotate from bottom'),
                'rotatefromleft' => cp__('Rotate from left'),
                'rotatefromright' => cp__('Rotate from right'),
                'scalefromtop' => cp__('Scale from top'),
                'scalefrombottom' => cp__('Scale from bottom'),
                'scalefromleft' => cp__('Scale from left'),
                'scalefromright' => cp__('Scale from right'),
                'scale' => cp__('Scale'),
                'spin' => cp__('Spin'),
                'spinx' => cp__('Spin horizontally'),
                'spiny' => cp__('Spin vertically'),
                'elastic' => cp__('Elastic'),
            ],
        ],

        'popupTransitionOut' => [
            'value' => 'fade',
            'name' => cp__('Closing transition'),
            'keys' => 'popupTransitionOut',
            'desc' => cp__('Choose from one of the pre-defined Popup closing transitions.'),
            'options' => [
                'fade' => cp__('Fade'),
                'slidetotop' => cp__('Slide to top'),
                'slidetobottom' => cp__('Slide to bottom'),
                'slidetoleft' => cp__('Slide to left'),
                'slidetoright' => cp__('Slide to right'),
                'rotatetotop' => cp__('Rotate to top'),
                'rotatetobottom' => cp__('Rotate to bottom'),
                'rotatetoleft' => cp__('Rotate to left'),
                'rotatetoright' => cp__('Rotate to right'),
                'scaletotop' => cp__('Scale to top'),
                'scaletobottom' => cp__('Scale to bottom'),
                'scaletoleft' => cp__('Scale to left'),
                'scaletoright' => cp__('Scale to right'),
                'scale' => cp__('Scale'),
                'spin' => cp__('Spin'),
                'spinx' => cp__('Spin horizontally'),
                'spiny' => cp__('Spin vertically'),
                'elastic' => cp__('Elastic'),
            ],
        ],
        /*
        'popupCustomTransitionIn' => [
            'value' => '',
            'name' => cp__('Custom opening transition'),
            'keys' => 'popupCustomTransitionIn',
        ],

        'popupCustomTransitionOut' => [
            'value' => '',
            'name' => cp__('Custom closing transition'),
            'keys' => 'popupCustomTransitionOut',
        ],
        */
        'popupOverlayBackground' => [
            'value' => 'rgba(0,0,0,.85)',
            'name' => cp__('Overlay color'),
            'keys' => 'popupOverlayBackground',
            'desc' => cp__('The overlay color. You can use color names, hexadecimal, RGB or RGBA values.'),
        ],

        'popupOverlayDurationIn' => [
            'value' => 400,
            'name' => cp__('Overlay opening duration'),
            'keys' => 'popupOverlayDurationIn',
            'desc' => cp__(
                'The overlay opening transition duration specified in milliseconds. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => [
                'min' => 0,
                'step' => 100,
            ],
        ],

        'popupOverlayDurationOut' => [
            'value' => 400,
            'name' => cp__('Overlay closing duration'),
            'keys' => 'popupOverlayDurationOut',
            'desc' => cp__(
                'The overlay closing transition duration specified in milliseconds. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => [
                'min' => 0,
                'step' => 100,
            ],
        ],
        /*
        'popupOverlayEaseIn' => [
            'value' => 'easeInQuint',
            'name' => cp__('Overlay opening easing'),
            'keys' => 'popupOverlayEaseIn',
            'desc' => cp__(
                'The timing function of the animation. With it you can manipulate the movement of animated objects. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],

        'popupOverlayEaseOut' => [
            'value' => 'easeInQuint',
            'name' => cp__('Overlay closing easing'),
            'keys' => 'popupOverlayEaseOut',
            'desc' => cp__(
                'The timing function of the animation. With it you can manipulate the movement of animated objects. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],
        */
        'popupOverlayTransitionIn' => [
            'value' => 'fade',
            'name' => cp__('Opening transition'),
            'keys' => 'popupOverlayTransitionIn',
            'desc' => cp__('Choose from one of the pre-defined overlay opening transitions.'),
            'options' => [
                'fade' => cp__('Fade'),
                'slidefromtop' => cp__('Slide from top'),
                'slidefrombottom' => cp__('Slide from bottom'),
                'slidefromleft' => cp__('Slide from left'),
                'slidefromright' => cp__('Slide from right'),
                'fadefromtopright' => cp__('Fade from top right'),
                'fadefromtopleft' => cp__('Fade from top left'),
                'fadefrombottomright' => cp__('Fade from bottom right'),
                'fadefrombottomleft' => cp__('Fade from bottom left'),
                'scale' => cp__('Scale'),
            ],
        ],

        'popupOverlayTransitionOut' => [
            'value' => 'fade',
            'name' => cp__('Closing transition'),
            'keys' => 'popupOverlayTransitionOut',
            'desc' => cp__('Choose from one of the pre-defined overlay closing transitions.'),
            'options' => [
                'fade' => cp__('Fade'),
                'slidetotop' => cp__('Slide to top'),
                'slidetobottom' => cp__('Slide to bottom'),
                'slidetoleft' => cp__('Slide to left'),
                'slidetoright' => cp__('Slide to right'),
                'fadetotopright' => cp__('Fade to top right'),
                'fadetotopleft' => cp__('Fade to top left'),
                'fadetobottomright' => cp__('Fade to bottom right'),
                'fadetobottomleft' => cp__('Fade to bottom left'),
                'scale' => cp__('Scale'),
            ],
        ],

        'popupRoles' => [
            'value' => ['0'],
            'name' => cp__('Show Popup for group(s)'),
            'keys' => 'popup_roles',
            'desc' => cp__(
                'PrestaShop has three default customer groups<br>' .
                '<i>Visitor</i> - All persons without a customer account or customers that are not logged in.<br>' .
                '<i>Guest</i> - All persons who placed an order through Guest Checkout.<br>' .
                '<i>Customer</i> - All persons who created an account on this site.'
            ),
            'props' => ['meta' => true],
            'attrs' => ['multiple' => true, 'size' => 4, 'style' => 'height:auto'],
            'options' => cp_get_options(Group::getGroups(Context::getContext()->language->id), 'id_group'),
        ],

        'popupFirstTimeVisitor' => [
            'value' => false,
            'name' => cp__('First time visitors'),
            'keys' => 'popup_first_time_visitor',
            'desc' => cp__('Show only for first time visitors'),
            'props' => ['meta' => true],
        ],

        'popupSubscribedVisitor' => [
            'value' => '',
            'name' => cp__('Filter by subscription'),
            'keys' => ['popup_subscribed_visitor', 'subscribed'],
            'desc' => cp__(
                'This option is useful when you do not want to display a sign-up for an already subscribed visitor.'
            ),
            'options' => [
                '' => cp__('- All -'),
                '1' => cp__('Subscribed'),
                '-1' => cp__('Non-subscribed'),
            ],
        ],

        'popupRepeat' => [
            'value' => true,
            'name' => cp__('Repeat Popup'),
            'keys' => 'popup_repeat',
            'desc' => cp__(
                'Enables or disables repeating this Popup to your target audience with the below specified frequency.'
            ),
            'props' => ['meta' => true],
        ],

        'popupRepeatDays' => [
            'value' => '',
            'name' => cp__('Repeat after'),
            'keys' => ['popup_repeat_days', 'repeatDays'],
            'desc' => cp__(
                'Controls the repeat frequency of this Popup specified in days. ' .
                'Leave this option empty if you want to display the Popup on each page load. ' .
                'Enter 0 to repeat after the end of a browsing session i.e. when the browser closes ' .
                '(if "Continue where I left off" is not activated at browser settings).'
            ),
            'attrs' => [
                'type' => 'number',
                'min' => 0,
                'max' => 365,
            ],
        ],

        // ========== //
        // |  Misc  | //
        // ========== //

        'shop' => [
            'value' => ['0'],
            'name' => cp__('Store'),
            'keys' => 'shop',
            'desc' => cp__('Popup will appear only on the selected shop. (In case of Multistore)'),
            'props' => ['meta' => true],
            'options' => cp_get_options($shops = Shop::getShops(), 'id_shop'),
            'attrs' => ['multiple' => true, 'size' => count($shops) + 1, 'style' => 'height:auto;'],
        ],

        'lang' => [
            'value' => '0',
            'name' => cp__('Language'),
            'keys' => 'lang',
            'desc' => cp__('Popup will appear only on the selected language. (In case of multilanguage)'),
            'props' => ['meta' => true],
            'options' => cp_get_options(Language::getLanguages(), 'id_lang'),
        ],

        'cats' => [
            'value' => ['all'],
            'keys' => 'cats',
            'props' => ['meta' => true],
            'attrs' => ['multiple' => true, 'size' => 15, 'style' => 'width:auto; height:auto;'],
            'options' => cp_get_options(CpHelper::getCategories(), 'value', 'option', false),
        ],

        'pages' => [
            'value' => ['all'],
            'keys' => 'pages',
            'props' => ['meta' => true],
            'attrs' => ['multiple' => true, 'size' => 15, 'style' => 'width:auto; height:auto;'],
            'options' => cp_get_options(CpHelper::getCMSCategories(), 'value', 'option', false),
        ],

        'urls' => [
            'value' => '',
            'name' => cp__('Custom URLs'),
            'keys' => 'urls',
            'desc' => cp__(
                'Display popup on these URLs<br>* (Asterisk) sign represents zero, one, or multiple characters'
            ),
            'props' => ['meta' => true],
        ],

        'allowRestartOnResize' => [
            'value' => false,
            'name' => cp__('Allow restarting pages on resize'),
            'keys' => 'allowRestartOnResize',
            'desc' => cp__(
                'Certain transformation and transition options cannot be updated on the fly ' .
                'when the browser size or device orientation changes. By enabling this option, ' .
                'the popup will automatically detect such situations ' .
                'and will restart the itself to preserve its appearance.'
            ),
            'advanced' => true,
        ],

        'preferBlendMode' => [
            'value' => 'disabled',
            'name' => cp__('Prefer Blend Mode'),
            'keys' => 'preferBlendMode',
            'desc' => cp__(
                'Enable this option to avoid blend mode issues with page transitions. ' .
                'Due to technical limitations, this will also clip your page transitions regardless of your settings.'
            ),
            'options' => [
                'enabled' => cp__('Enabled'),
                'disabled' => cp__('Disabled'),
            ],
            'advanced' => true,
        ],

        // Post options
        'postType' => [
            'value' => '',
            'keys' => 'post_type',
            'props' => [
                'meta' => true,
            ],
        ],

        'postOrderBy' => [
            'value' => 'date_add',
            'keys' => 'post_orderby',
            'options' => [
                'date_add' => cp__('Date Created'),
                'date_upd' => cp__('Last Modified'),
                'position' => cp__('Popularity'),
                'quantity' => cp__('Sold quantity'),
                'reduction' => cp__('Special offer'),
                'name' => cp__('Product name'),
                'price' => cp__('Product price'),
                'rand' => cp__('Random'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'postOrder' => [
            'value' => 'DESC',
            'keys' => 'post_order',
            'options' => [
                'ASC' => cp__('Ascending'),
                'DESC' => cp__('Descending'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'postCategories' => [
            'value' => '',
            'keys' => 'post_categories',
            'props' => [
                'meta' => true,
            ],
        ],

        'postTags' => [
            'value' => '',
            'keys' => 'post_tags',
            'props' => [
                'meta' => true,
            ],
        ],

        'postTaxonomy' => [
            'value' => '',
            'keys' => 'post_taxonomy',
            'props' => [
                'meta' => true,
            ],
        ],

        'postTaxTerms' => [
            'value' => '',
            'keys' => 'post_tax_terms',
            'options' => CpHelper::getProductImgTypes(),
            'props' => [
                'meta' => true,
            ],
        ],

        // Old and obsolete API
        'cbInit' => [
            'value' => "function(element) {\r\n\r\n}",
            'keys' => ['cbinit', 'cbInit'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbStart' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbstart', 'cbStart'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbStop' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbstop', 'cbStop'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbPause' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbpause', 'cbPause'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbAnimStart' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbanimstart', 'cbAnimStart'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbAnimStop' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbanimstop', 'cbAnimStop'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbPrev' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbprev', 'cbPrev'],
            'props' => [
                'meta' => true,
            ],
        ],

        'cbNext' => [
            'value' => "function(data) {\r\n\r\n}",
            'keys' => ['cbnext', 'cbNext'],
            'props' => [
                'meta' => true,
            ],
        ],
    ],

    'slides' => [
        // The background image of pages
        // Defaults to: void
        'image' => [
            'value' => '',
            'name' => cp__('Set a page image'),
            'keys' => 'background',
            'tooltip' => cp__(
                'The page image/background. Click on the image to open the Image Manager to choose or upload an image.'
            ),
            'props' => ['meta' => true],
        ],

        'imageId' => [
            'value' => '',
            'keys' => 'backgroundId',
            'props' => ['meta' => true],
        ],

        'imageSize' => [
            'value' => 'inherit',
            'name' => cp__('Size'),
            'keys' => 'bgsize',
            'tooltip' => cp__(
                'The size of the page background image. ' .
                'Leave this option on inherit if you want to set it globally from More Settings / Default Options.'
            ),
            'options' => [
                'inherit' => cp__('Inherit'),
                'auto' => cp__('Auto'),
                'cover' => cp__('Cover'),
                'contain' => cp__('Contain'),
                '100% 100%' => cp__('Stretch'),
            ],
        ],

        'imagePosition' => [
            'value' => 'inherit',
            'name' => cp__('Position'),
            'keys' => 'bgposition',
            'tooltip' => cp__(
                'The position of the page background image. ' .
                'Leave this option on inherit if you want to set it globally from More Settings / Default Options.'
            ),
            'options' => [
                'inherit' => cp__('Inherit'),
                '0% 0%' => cp__('left top'),
                '0% 50%' => cp__('left center'),
                '0% 100%' => cp__('left bottom'),
                '50% 0%' => cp__('center top'),
                '50% 50%' => cp__('center center'),
                '50% 100%' => cp__('center bottom'),
                '100% 0%' => cp__('right top'),
                '100% 50%' => cp__('right center'),
                '100% 100%' => cp__('right bottom'),
            ],
        ],

        'imageColor' => [
            'value' => '',
            'name' => cp__('Color'),
            'keys' => 'bgcolor',
            'tooltip' => cp__('The page background color. You can use color names, hexadecimal, RGB or RGBA values.'),
        ],

        'thumbnail' => [
            'value' => '',
            'name' => cp__('Set a page thumbnail'),
            'keys' => 'thumbnail',
            'tooltip' => cp__(
                'The thumbnail image of this page. ' .
                'Click on the image to open the Image Manager to choose or upload an image. ' .
                'If you leave this field empty, the page image will be used.'
            ),
            'props' => ['meta' => true],
        ],

        'thumbnailId' => [
            'value' => '',
            'keys' => 'thumbnailId',
            'props' => ['meta' => true],
        ],

        // Default page delay in millisecs.
        // Defaults to: 4000 (ms) => 4secs
        'delay' => [
            'value' => '',
            'name' => cp__('Duration'),
            'keys' => ['slidedelay', 'duration'],
            'tooltip' => cp__(
                'Here you can set the time interval between page changes, ' .
                'this page will stay visible for the time specified here. ' .
                'This value is in millisecs, so the value 1000 means 1 second. ' .
                "Please don't use 0 or very low values."
            ),
            'attrs' => [
                'type' => 'number',
                'min' => 0,
                'step' => 500,
                'placeholder' => 'auto',
            ],
        ],

        '2dTransitions' => [
            'value' => '',
            'keys' => ['2d_transitions', 'transition2d'],
        ],

        '3dTransitions' => [
            'value' => '',
            'keys' => ['3d_transitions', 'transition3d'],
        ],

        'custom2dTransitions' => [
            'value' => '',
            'keys' => ['custom_2d_transitions', 'customtransition2d'],
        ],

        'custom3dTransitions' => [
            'value' => '',
            'keys' => ['custom_3d_transitions', 'customtransition3d'],
        ],

        'transitionOrigami' => [
            'value' => false,
            'keys' => 'transitionorigami',
            'premium' => true,
        ],

        'transitionDuration' => [
            'value' => '',
            'name' => cp__('Duration'),
            'keys' => 'transitionduration',
            'tooltip' => cp__(
                "We've made our pre-defined page transitions with special care to fit in most use cases. " .
                'However, if you would like to increase or decrease the speed of these transitions, ' .
                'you can override their timing here by providing your own transition length in milliseconds. ' .
                '(1 second = 1000 milliseconds)'
            ),
            'attrs' => [
                'type' => 'number',
                'min' => 0,
                'step' => 500,
                'placeholder' => cp__('custom duration'),
            ],
        ],

        'timeshift' => [
            'value' => 0,
            'name' => cp__('Time Shift'),
            'keys' => 'timeshift',
            'tooltip' => cp__(
                'You can shift the starting point of the page animation timeline, ' .
                'so layers can animate in an earlier time after a page change. ' .
                'This value is in milliseconds. A second is 1000 milliseconds. ' .
                'You can only use a negative value.'
            ),
            'attrs' => [
                'step' => 50,
            ],
        ],

        'linkUrl' => [
            'value' => '',
            'name' => cp__('Enter URL'),
            'keys' => ['layer_link', 'linkUrl'],
            'tooltip' => cp__(
                'If you want to link the whole page, type the URL here. ' .
                'You can choose one of the pre-defined options from the dropdown list when you click on this field. ' .
                'You can also type a hash mark followed by a number to link this layer to another page. ' .
                'Example: #3 - this will switch to the third page.'
            ),
            'attrs' => [
                'data-options' => '[{
                    "name": "Switch to the next page",
                    "value": "#next"
                }, {
                    "name": "Switch to the previous page",
                    "value": "#prev"
                }, {
                    "name": "Stop the pageshow",
                    "value": "#stop"
                }, {
                    "name": "Resume the pageshow",
                    "value": "#start"
                }, {
                    "name": "Replay the page from the start",
                    "value": "#replay"
                }, {
                    "name": "Reverse the page, then pause it",
                    "value": "#reverse"
                }, {
                    "name": "Reverse the page, then replay it",
                    "value": "#reverse-replay"
                }, {
                    "name": "Close the Popup",
                    "value": "#closepopup"
                }]',
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'linkId' => [
            'value' => '',
            'keys' => 'linkId',
            'props' => ['meta' => true],
        ],

        'linkTarget' => [
            'value' => '_self',
            'name' => cp__('Link Target'),
            'keys' => ['layer_link_target', 'linkTarget'],
            'options' => [
                '_self' => cp__('Open on the same page'),
                '_blank' => cp__('Open on new page'),
                '_parent' => cp__('Open in parent frame'),
                '_top' => cp__('Open in main frame'),
                'cp-scroll' => cp__('Scroll to element (Enter selector)'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'linkType' => [
            'value' => 'over',
            'keys' => ['layer_link_type', 'linkType'],
            'tooltip' => cp__(
                'Choose whether the page link should be on top or underneath your layers. ' .
                'The later option makes the link clickable only at empty spaces where the page background is visible ' .
                'and enables you to link both pages and layers independently from each other.'
            ),
            'options' => [
                'over' => cp__('On top of layers'),
                'under' => cp__('Underneath layers'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'ID' => [
            'value' => '',
            'name' => cp__('#ID'),
            'keys' => 'id',
            'tooltip' => cp__(
                'You can apply an ID attribute on the HTML element of this page ' .
                'to work with it in your custom CSS or Javascript code.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'deeplink' => [
            'value' => '',
            'name' => cp__('Deeplink'),
            'keys' => 'deeplink',
            'tooltip' => cp__(
                'You can specify a page alias name which you can use in your URLs with a hash mark, ' .
                'so popup will start with the correspondig page.'
            ),
        ],

        'globalHover' => [
            'value' => false,
            'name' => cp__('Global Hover'),
            'keys' => 'globalhover',
            'tooltip' => cp__(
                'By turning this option on, all layers will trigger their Hover Transitions at the same time ' .
                'when you hover over the popup with your mouse cursor. ' .
                'It’s useful to create spectacular effects that involve multiple layer transitions ' .
                'and activate on hovering over the popup instead of individual layers.'
            ),
            'premium' => true,
        ],

        'postContent' => [
            'value' => null,
            'keys' => 'post_content',
            'props' => [
                'meta' => true,
            ],
        ],

        'postOffset' => [
            'value' => '',
            'keys' => 'post_offset',
            'props' => [
                'meta' => true,
            ],
        ],

        'skipSlide' => [
            'value' => false,
            'name' => cp__('Hidden'),
            'keys' => 'skip',
            'tooltip' => cp__(
                "If you don't want to use this page in your front-page, " .
                'but you want to keep it, you can hide it with this switch.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'overflow' => [
            'value' => false,
            'name' => cp__('Overflow layers'),
            'keys' => 'overflow',
            'tooltip' => cp__(
                'By default the popup clips the layers outside of its bounds. ' .
                'Enable this option to allow overflowing content.'
            ),
        ],

        // Ken Burns effect
        'kenBurnsZoom' => [
            'value' => 'disabled',
            'name' => cp__('Zoom'),
            'keys' => 'kenburnszoom',
            'options' => [
                'disabled' => cp__('Disabled'),
                'in' => cp__('Zoom In'),
                'out' => cp__('Zoom Out'),
            ],
        ],

        'kenBurnsRotate' => [
            'value' => '',
            'name' => cp__('Rotate'),
            'keys' => 'kenburnsrotate',
            'tooltip' => cp__(
                'The amount of rotation (if any) in degrees used in the Ken Burns effect. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
        ],

        'kenBurnsScale' => [
            'value' => 1.2,
            'name' => cp__('Scale'),
            'keys' => 'kenburnsscale',
            'tooltip' => cp__(
                'Increase or decrease the size of the page background image in the Ken Burns effect. ' .
                'The default value is 1, the value 2 will double the image, ' .
                'while 0.5 results half the size. Negative values will flip the image.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 0.1,
            ],
            'props' => [
                'output' => true,
            ],
        ],

        // Parallax
        'parallaxType' => [
            'value' => '2d',
            'name' => cp__('Type'),
            'keys' => 'parallaxtype',
            'tooltip' => cp__(
                'The default value for parallax layers on this page, which they will inherit, ' .
                'unless you set it otherwise on the affected layers.'
            ),
            'options' => [
                '2d' => cp__('2D'),
                '3d' => cp__('3D'),
            ],
        ],

        'parallaxEvent' => [
            'value' => 'cursor',
            'name' => cp__('Event'),
            'keys' => 'parallaxevent',
            'tooltip' => cp__(
                'You can trigger the parallax effect by either scrolling the site, ' .
                'or by moving your mouse cursor / tilting your mobile device. ' .
                'This is the default value on this page, which parallax layers will inherit, ' .
                'unless you set it otherwise directly on them.'
            ),
            'options' => [
                'cursor' => cp__('Cursor or Tilt'),
                'scroll' => cp__('Scroll'),
            ],
        ],

        'parallaxAxis' => [
            'value' => 'both',
            'name' => cp__('Axes'),
            'keys' => 'parallaxaxis',
            'tooltip' => cp__(
                'Choose on which axes parallax layers should move. This is the default value on this page, ' .
                'which parallax layers will inherit, unless you set it otherwise directly on them.'
            ),
            'options' => [
                'none' => cp__('None'),
                'both' => cp__('Both axes'),
                'x' => cp__('Horizontal only'),
                'y' => cp__('Vertical only'),
            ],
        ],

        'parallaxTransformOrigin' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'keys' => 'parallaxtransformorigin',
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. ' .
                'For example, a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center.'
            ),
        ],

        'parallaxDurationMove' => [
            'value' => 1500,
            'name' => cp__('Move duration'),
            'keys' => 'parallaxdurationmove',
            'tooltip' => cp__(
                'Controls the speed of animating layers when you move your mouse cursor or tilt your mobile device. ' .
                'This is the default value on this page, which parallax layers will inherit, ' .
                'unless you set it otherwise directly on them.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 100,
                'min' => 0,
            ],
        ],

        'parallaxDurationLeave' => [
            'value' => 1200,
            'name' => cp__('Leave duration'),
            'keys' => 'parallaxdurationleave',
            'tooltip' => cp__(
                'Controls how quickly your layers revert to their original position ' .
                'when you move your mouse cursor outside of a parallax popup. ' .
                'This value is in milliseconds. 1 second = 1000 milliseconds. ' .
                'This is the default value on this page, which parallax layers will inherit, ' .
                'unless you set it otherwise directly on them.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 100,
                'min' => 0,
            ],
        ],

        'parallaxDistance' => [
            'value' => 10,
            'name' => cp__('Distance'),
            'keys' => 'parallaxdistance',
            'tooltip' => cp__(
                'Increase or decrease the amount of layer movement ' .
                'when moving your mouse cursor or tilting on a mobile device. ' .
                'This is the default value on this page, which parallax layers will inherit, ' .
                'unless you set it otherwise directly on them.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 1,
            ],
        ],

        'parallaxRotate' => [
            'value' => 10,
            'name' => cp__('Rotation'),
            'keys' => 'parallaxrotate',
            'tooltip' => cp__(
                'Increase or decrease the amount of layer rotation in the 3D space ' .
                'when moving your mouse cursor or tilting on a mobile device. ' .
                'This is the default value on this page, which parallax layers will inherit, ' .
                'unless you set it otherwise directly on them.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 1,
            ],
        ],

        'parallaxPerspective' => [
            'value' => 500,
            'name' => cp__('Perspective'),
            'keys' => 'parallaxtransformperspective',
            'tooltip' => cp__(
                'Changes the perspective of layers in the 3D space. This is the default value on this page, ' .
                'which parallax layers will inherit, unless you set it otherwise directly on them.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 100,
            ],
        ],
        /*
        'filterFrom' => [
            'value' => '',
            'name' => cp__('Filter From'),
            'keys' => 'filterfrom',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list ',
                'of all the filters you would like to use.'
            ),
            'advanced' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {

                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],

        'filterTo' => [
            'value' => '',
            'name' => cp__('Filter To'),
            'keys' => 'filterto',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list ' .
                'of all the filters you would like to use.'
            ),
            'advanced' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {

                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],
        */
    ],

    'layers' => [
        // ======================= //
        // |  Content  | //
        // ======================= //

        'uuid' => [
            'value' => '',
            'keys' => 'uuid',
            'props' => [
                'meta' => true,
            ],
        ],

        'type' => [
            'value' => '',
            'keys' => 'type',
            'props' => [
                'meta' => true,
            ],
        ],

        'hide_on_desktop' => [
            'value' => false,
            'keys' => 'hide_on_desktop',
            'props' => [
                'meta' => true,
            ],
        ],

        'hide_on_tablet' => [
            'value' => false,
            'keys' => 'hide_on_tablet',
            'props' => [
                'meta' => true,
            ],
        ],

        'hide_on_phone' => [
            'value' => false,
            'keys' => 'hide_on_phone',
            'props' => [
                'meta' => true,
            ],
        ],

        'media' => [
            'value' => '',
            'keys' => 'media',
            'props' => [
                'meta' => true,
            ],
        ],

        'image' => [
            'value' => '',
            'keys' => 'image',
            'props' => [
                'meta' => true,
            ],
        ],

        'imageId' => [
            'value' => '',
            'keys' => 'imageId',
            'props' => ['meta' => true],
        ],

        'html' => [
            'value' => '',
            'keys' => 'html',
            'props' => [
                'meta' => true,
            ],
        ],

        'mediaAutoPlay' => [
            'value' => 'inherit',
            'name' => cp__('Autoplay'),
            'keys' => 'autoplay',
            'options' => [
                'inherit' => cp__('Inherit'),
                'enabled' => cp__('Enabled'),
                'disabled' => cp__('Disabled'),
            ],
        ],

        'mediaInfo' => [
            'value' => true,
            'name' => cp__('Show Info'),
            'keys' => 'showinfo',
            'options' => [
                'auto' => cp__('Auto'),
                'enabled' => cp__('Enabled'),
                'disabled' => cp__('Disabled'),
            ],
        ],

        'mediaControls' => [
            'value' => true,
            'name' => cp__('Controls'),
            'keys' => 'controls',
            'options' => [
                'auto' => cp__('Auto'),
                'enabled' => cp__('Enabled'),
                'disabled' => cp__('Disabled'),
            ],
        ],

        'mediaPoster' => [
            'value' => '',
            'keys' => 'poster',
        ],

        'mediaFillMode' => [
            'value' => 'cover',
            'name' => cp__('Fill mode'),
            'keys' => 'fillmode',
            'options' => [
                'contain' => cp__('Contain'),
                'cover' => cp__('Cover'),
            ],
        ],

        'mediaVolume' => [
            'value' => '',
            'name' => cp__('Volume'),
            'keys' => 'volume',
            'attrs' => [
                'type' => 'number',
                'min' => 0,
                'max' => 100,
                'placeholder' => 'auto',
            ],
        ],

        'mediaBackgroundVideo' => [
            'value' => false,
            'name' => cp__('Use this video as page background'),
            'keys' => 'backgroundvideo',
            'tooltip' => cp__(
                'Forces this layer to act like the page background ' .
                'by covering the whole popup and ignoring some transitions. ' .
                'Please make sure to provide your own poster image with the option above, ' .
                'so the popup can display it immediately.'
            ),
        ],

        'mediaOverlay' => [
            'value' => 'disabled',
            'name' => cp__('Choose an overlay image:'),
            'keys' => 'overlay',
            'tooltip' => cp__('Cover your videos with an overlay image to have dotted or striped effects on them.'),
        ],

        'postTextLength' => [
            'value' => '',
            'keys' => 'post_text_length',
            'props' => [
                'meta' => true,
            ],
        ],

        // ======================= //
        // |  Animation options  | //
        // ======================= //
        'transition' => ['value' => '', 'keys' => 'transition', 'props' => ['meta' => true]],

        'transitionIn' => [
            'value' => true,
            'keys' => 'transitionin',
        ],

        'transitionInOffsetX' => [
            'value' => '0',
            'name' => cp__('OffsetX'),
            'keys' => 'offsetxin',
            'tooltip' => cp__(
                'Shifts the layer starting position from its original on the horizontal axis ' .
                'with the given number of pixels. Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'left' or 'right' position the layer out the staging area, " .
                'so it enters the scene from either side when animating to its destination location.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Enter the stage from left",
                "value": "left"
            }, {
                "name": "Enter the stage from right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% popup width",
                "value": "50sw"
            }, {
                "name": "-50% popup width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'transitionInOffsetY' => [
            'value' => '0',
            'name' => cp__('OffsetY'),
            'keys' => 'offsetyin',
            'tooltip' => cp__(
                'Shifts the layer starting position from its original on the vertical axis ' .
                'with the given number of pixels. Use negative values for the opposite direction. ' .
                'Percentage values are relative to the height of this layer. ' .
                "The values 'top' or 'bottom' position the layer out the staging area, " .
                'so it enters the scene from either vertical side when animating to its destination location.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Enter the stage from top",
                "value": "top"
            }, {
                "name": "Enter the stage from bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% popup height",
                "value": "50sh"
            }, {
                "name": "-50% popup height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        // Duration of the transition in millisecs when a layer animates in.
        // Original: durationin
        // Defaults to: 1000 (ms) => 1sec
        'transitionInDuration' => [
            'value' => 1000,
            'name' => cp__('Duration'),
            'keys' => 'durationin',
            'tooltip' => cp__(
                'The length of the transition in milliseconds when the layer enters the scene. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => ['min' => 0, 'step' => 50, 'placeholder' => 1000],
        ],

        // Delay before the transition in millisecs when a layer animates in.
        // Original: delayin
        // Defaults to: 0 (ms)
        'transitionInDelay' => [
            'value' => 0,
            'name' => cp__('Start at'),
            'keys' => 'delayin',
            'tooltip' => cp__(
                'Delays the transition with the given amount of milliseconds before the layer enters the scene. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => ['min' => 0, 'step' => 50, 'placeholder' => 0],
        ],

        // Easing of the transition when a layer animates in.
        // Original: easingin
        // Defaults to: 'easeInOutQuint'
        'transitionInEasing' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Easing'),
            'keys' => 'easingin',
            'tooltip' => cp__(
                'The timing function of the animation. ' .
                'With this function you can manipulate the movement of the animated object. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],

        'transitionInFade' => [
            'value' => true,
            'name' => cp__('Fade'),
            'keys' => 'fadein',
            'tooltip' => cp__('Fade the layer during the transition.'),
        ],

        // Initial rotation degrees when a layer animates in.
        // Original: rotatein
        // Defaults to: 0 (deg)
        'transitionInRotate' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'keys' => 'rotatein',
            'tooltip' => cp__(
                'Rotates the layer by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionInRotateX' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'keys' => 'rotatexin',
            'tooltip' => cp__(
                'Rotates the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionInRotateY' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'keys' => 'rotateyin',
            'tooltip' => cp__(
                'Rotates the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionInSkewX' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'keys' => 'skewxin',
            'tooltip' => cp__(
                'Skews the layer along the X (horizontal) by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionInSkewY' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'keys' => 'skewyin',
            'tooltip' => cp__(
                'Skews the layer along the Y (vertical) by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionInScaleX' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'scalexin',
            'tooltip' => cp__(
                'Scales the layer along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. ' .
                'The value 2 will double, while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'transitionInScaleY' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'scaleyin',
            'tooltip' => cp__(
                'Scales the layer along the Y (vertical) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'transitionInTransformOrigin' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'keys' => 'transformoriginin',
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. ' .
                'For example, a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center, ' .
                'popupcenter, popupmiddle, popuptop, popupright, popupbottom, popupleft.'
            ),
            'attrs' => ['placeholder' => '50% 50% 0'],
        ],

        'transitionInClip' => [
            'value' => '',
            'name' => cp__('Mask'),
            'keys' => 'clipin',
            'tooltip' => cp__(
                'Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. ' .
                'The 4 value in order: top, right, bottom and the left side of the layer.'
            ),
            'attrs' => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
        ],

        'transitionInBGColor' => [
            'value' => '',
            'name' => cp__('Background'),
            'keys' => 'bgcolorin',
            'tooltip' => cp__(
                'The background color of your layer. You can use color names, hexadecimal, ' .
                "RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF"
            ),
        ],

        'transitionInColor' => [
            'value' => '',
            'name' => cp__('Color'),
            'keys' => 'colorin',
            'tooltip' => cp__(
                'The color of your text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333'
            ),
        ],

        'transitionInRadius' => [
            'value' => '',
            'name' => cp__('Rounded Corners'),
            'keys' => 'radiusin',
            'tooltip' => cp__('If you want rounded corners, you can set its radius here in pixels. Example: 5px'),
        ],

        'transitionInWidth' => [
            'value' => '',
            'name' => cp__('Width'),
            'keys' => 'widthin',
            'tooltip' => cp__(
                'The initial width of this layer ' .
                'from which it will be animated to its proper size during the transition.'
            ),
        ],

        'transitionInHeight' => [
            'value' => '',
            'name' => cp__('Height'),
            'keys' => 'heightin',
            'tooltip' => cp__(
                'The initial height of this layer ' .
                'from which it will be animated to its proper size during the transition.'
            ),
        ],

        'transitionInFilter' => [
            'value' => '',
            'name' => cp__('Filter'),
            'keys' => 'filterin',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list of all the filters ' .
                'you would like to use. Click on the "Filter" link for more information.'
            ),
            'premium' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],

        'transitionInPerspective' => [
            'value' => '500',
            'name' => cp__('Perspective'),
            'keys' => 'transformperspectivein',
            'tooltip' => cp__('Changes the perspective of this layer in the 3D space.'),
            'attrs' => ['placeholder' => 500],
        ],

        // ======

        'transitionOut' => [
            'value' => true,
            'keys' => 'transitionout',
        ],

        'transitionOutOffsetX' => [
            'value' => 0,
            'name' => cp__('OffsetX'),
            'keys' => 'offsetxout',
            'tooltip' => cp__(
                'Shifts the layer from its original position on the horizontal axis with the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'left' or 'right' animate the layer out the staging area, " .
                'so it can leave the scene on either side.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Leave the stage on left",
                "value": "left"
            }, {
                "name": "Leave the stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% popup width",
                "value": "50sw"
            }, {
                "name": "-50% popup width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'transitionOutOffsetY' => [
            'value' => 0,
            'name' => cp__('OffsetY'),
            'keys' => 'offsetyout',
            'tooltip' => cp__(
                'Shifts the layer from its original position on the vertical axis with the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the height of this layer. ' .
                "The values 'top' or 'bottom' animate the layer out the staging area, " .
                'so it can leave the scene on either vertical side.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Leave the stage on top",
                "value": "top"
            }, {
                "name": "Leave the stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% popup height",
                "value": "50sh"
            }, {
                "name": "-50% popup height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        // Duration of the transition in millisecs when a layer animates out.
        // Original: durationout
        // Defaults to: 1000 (ms) => 1sec
        'transitionOutDuration' => [
            'value' => 1000,
            'name' => cp__('Duration'),
            'keys' => 'durationout',
            'tooltip' => cp__(
                'The length of the transition in milliseconds when the layer leaves the page. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'attrs' => ['min' => 0, 'step' => 50, 'placeholder' => 1000],
        ],

        'showUntil' => [
            'value' => '0',
            'keys' => 'showuntil',
        ],

        'transitionOutStartAt' => [
            'value' => 'slidechangeonly',
            'name' => cp__('Start at'),
            'keys' => 'startatout',
            'tooltip' => cp__(
                'You can set the starting time of this transition. ' .
                'Use one of the pre-defined options to use relative timing, ' .
                'which can be shifted with custom operations.'
            ),
            'attrs' => ['type' => 'hidden'],
        ],

        'transitionOutStartAtTiming' => [
            'value' => 'slidechangeonly',
            'keys' => 'startatouttiming',
            'props' => ['meta' => true],
            'options' => [
                'slidechangeonly' => cp__('Page change starts (ignoring modifier)'),
                'transitioninend' => cp__('Opening Transition completes'),
                'textinstart' => cp__('Opening Text Transition starts'),
                'textinend' => cp__('Opening Text Transition completes'),
                'allinend' => cp__('Opening and Opening Text Transition complete'),
                'loopstart' => cp__('Loop starts'),
                'loopend' => cp__('Loop completes'),
                'transitioninandloopend' => cp__('Opening and Loop Transitions complete'),
                'textinandloopend' => cp__('Opening Text and Loop Transitions complete'),
                'allinandloopend' => cp__('Opening, Opening Text and Loop Transitions complete'),
                'textoutstart' => cp__('Ending Text Transition starts'),
                'textoutend' => cp__('Ending Text Transition completes'),
                'textoutandloopend' => cp__('Ending Text and Loop Transitions complete'),
            ],
        ],

        'transitionOutStartAtOperator' => [
            'value' => '+',
            'keys' => 'startatoutoperator',
            'props' => ['meta' => true],
            'options' => ['+', '-', '/', '*'],
        ],

        'transitionOutStartAtValue' => [
            'value' => 0,
            'keys' => 'startatoutvalue',
            'props' => ['meta' => true],
            'attrs' => ['step' => 50, 'placeholder' => 0],
        ],

        // Easing of the transition when a layer animates out.
        // Original: easingout
        // Defaults to: 'easeInOutQuint'
        'transitionOutEasing' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Easing'),
            'keys' => 'easingout',
            'tooltip' => cp__(
                'The timing function of the animation. ' .
                'With this function you can manipulate the movement of the animated object. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
        ],

        'transitionOutFade' => [
            'value' => true,
            'name' => cp__('Fade'),
            'keys' => 'fadeout',
            'tooltip' => cp__('Fade the layer during the transition.'),
        ],

        // Initial rotation degrees when a layer animates out.
        // Original: rotateout
        // Defaults to: 0 (deg)
        'transitionOutRotate' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'keys' => 'rotateout',
            'tooltip' => cp__(
                'Rotates the layer by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionOutRotateX' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'keys' => 'rotatexout',
            'tooltip' => cp__(
                'Rotates the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionOutRotateY' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'keys' => 'rotateyout',
            'tooltip' => cp__(
                'Rotates the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionOutSkewX' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'keys' => 'skewxout',
            'tooltip' => cp__(
                'Skews the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionOutSkewY' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'keys' => 'skewyout',
            'tooltip' => cp__(
                'Skews the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'transitionOutScaleX' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'scalexout',
            'tooltip' => cp__(
                'Scales the layer along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'transitionOutScaleY' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'scaleyout',
            'tooltip' => cp__(
                'Scales the layer along the Y (vertical) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'transitionOutTransformOrigin' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'keys' => 'transformoriginout',
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. For example, ' .
                'a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center, ' .
                'popupcenter, popupmiddle, popuptop, popupright, popupbottom, popupleft.'
            ),
            'attrs' => ['placeholder' => '50% 50% 0'],
        ],

        'transitionOutClip' => [
            'value' => '',
            'name' => cp__('Mask'),
            'keys' => 'clipout',
            'tooltip' => cp__(
                'Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. ' .
                'The 4 value in order: top, right, bottom and the left side of the layer.'
            ),
            'attrs' => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
        ],

        'transitionOutFilter' => [
            'value' => '',
            'name' => cp__('Filter'),
            'keys' => 'filterout',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list of all the filters ' .
                'you would like to use. Click on the "Filter" link for more information.'
            ),
            'premium' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],

        'transitionOutPerspective' => [
            'value' => '500',
            'name' => cp__('Perspective'),
            'keys' => 'transformperspectiveout',
            'tooltip' => cp__('Changes the perspective of this layer in the 3D space.'),
            'attrs' => ['placeholder' => 500],
        ],

        // -----

        'skipLayer' => [
            'value' => false,
            'name' => cp__('Hidden'),
            'keys' => 'skip',
            'tooltip' => cp__(
                "If you don't want to use this layer, but you want to keep it, you can hide it with this switch."
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'transitionOutBGColor' => [
            'value' => '',
            'name' => cp__('Background'),
            'keys' => 'bgcolorout',
            'tooltip' => cp__(
                'Animates the background toward the color you specify here when the layer leaves the popup canvas.'
            ),
        ],

        'transitionOutColor' => [
            'value' => '',
            'name' => cp__('Color'),
            'keys' => 'colorout',
            'tooltip' => cp__(
                'Animates the text color toward the color you specify here when the layer leaves the popup canvas.'
            ),
        ],

        'transitionOutRadius' => [
            'value' => '',
            'name' => cp__('Rounded Corners'),
            'keys' => 'radiusout',
            'tooltip' => cp__(
                'Animates rounded corners toward the value you specify here when the layer leaves the popup canvas.'
            ),
        ],

        'transitionOutWidth' => [
            'value' => '',
            'name' => cp__('Width'),
            'keys' => 'widthout',
            'tooltip' => cp__(
                'Animates the layer width toward the value you specify here when the layer leaves the popup canvas.'
            ),
        ],

        'transitionOutHeight' => [
            'value' => '',
            'name' => cp__('Height'),
            'keys' => 'heightout',
            'tooltip' => cp__(
                'Animates the layer height toward the value you specify here when the layer leaves the popup canvas.'
            ),
        ],

        // == Compatibility ==
        'transitionInType' => [
            'value' => 'auto',
            'keys' => 'slidedirection',
        ],
        'transitionOutType' => [
            'value' => 'auto',
            'keys' => 'slideoutdirection',
        ],

        'transitionOutDelay' => [
            'value' => 0,
            'keys' => 'delayout',
        ],

        'transitionInScale' => [
            'value' => '1.0',
            'keys' => 'scalein',
        ],

        'transitionOutScale' => [
            'value' => '1.0',
            'keys' => 'scaleout',
        ],

        // Text Animation IN
        // -----------------

        'textTransitionIn' => [
            'value' => false,
            'keys' => 'texttransitionin',
        ],

        'textTypeIn' => [
            'value' => 'chars_asc',
            'name' => cp__('Text Animation'),
            'keys' => 'texttypein',
            'tooltip' => cp__('Select how your text should be split and animated.'),
            'options' => [
                'chars_asc' => cp__('by chars ascending'),
                'chars_desc' => cp__('by chars descending'),
                'chars_rand' => cp__('by chars random'),
                'chars_center' => cp__('by chars center to edge'),
                'chars_edge' => cp__('by chars edge to center'),
                'words_asc' => cp__('by words ascending'),
                'words_desc' => cp__('by words descending'),
                'words_rand' => cp__('by words random'),
                'words_center' => cp__('by words center to edge'),
                'words_edge' => cp__('by words edge to center'),
                'lines_asc' => cp__('by lines ascending'),
                'lines_desc' => cp__('by lines descending'),
                'lines_rand' => cp__('by lines random'),
                'lines_center' => cp__('by lines center to edge'),
                'lines_edge' => cp__('by lines edge to center'),
            ],
            'props' => [
                'output' => true,
            ],
        ],

        'textShiftIn' => [
            'value' => 50,
            'name' => cp__('Shift In'),
            'tooltip' => cp__(
                'Delays the transition of each text nodes relative to each other. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'keys' => 'textshiftin',
            'attrs' => ['type' => 'number', 'placeholder' => 50],
        ],

        'textOffsetXIn' => [
            'value' => 0,
            'name' => cp__('OffsetX'),
            'tooltip' => cp__(
                'Shifts the starting position of text nodes ' .
                'from their original on the horizontal axis with the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'left' or 'right' position text nodes out the staging area, " .
                'so they enter the scene from either side when animating to their destination location. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textoffsetxin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Enter the stage from left",
                "value": "left"
            }, {
                "name": "Enter the stage from right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% popup width",
                "value": "50sw"
            }, {
                "name": "-50% popup width",
                "value": "-50sw"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'textOffsetYIn' => [
            'value' => 0,
            'name' => cp__('OffsetY'),
            'tooltip' => cp__(
                'Shifts the starting position of text nodes ' .
                'from their original on the vertical axis with the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'top' or 'bottom' position text nodes out the staging area, " .
                'so they enter the scene from either vertical side when animating to their destination location. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textoffsetyin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Enter the stage from top",
                "value": "top"
            }, {
                "name": "Enter the stage from bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% popup height",
                "value": "50sh"
            }, {
                "name": "-50% popup height",
                "value": "-50sh"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'textDurationIn' => [
            'value' => 1000,
            'name' => cp__('Duration'),
            'tooltip' => cp__(
                'The transition length in milliseconds of the individual text fragments. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'keys' => 'textdurationin',
            'attrs' => ['min' => 0, 'step' => 50, 'placeholder' => 1000],
        ],

        'textEasingIn' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Easing'),
            'tooltip' => cp__(
                'The timing function of the animation. ' .
                'With this function you can manipulate the movement of animated text fragments. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
            'keys' => 'texteasingin',
        ],

        'textFadeIn' => [
            'value' => true,
            'name' => cp__('Fade'),
            'tooltip' => cp__('Fade the text fragments during their transition.'),
            'keys' => 'textfadein',
        ],

        'textStartAtIn' => [
            'value' => 'transitioninend',
            'name' => cp__('StartAt'),
            'tooltip' => cp__(
                'You can set the starting time of this transition. ' .
                'Use one of the pre-defined options to use relative timing, ' .
                'which can be shifted with custom operations.'
            ),
            'keys' => 'textstartatin',
            'attrs' => ['type' => 'hidden'],
        ],

        'textStartAtInTiming' => [
            'value' => 'transitioninend',
            'keys' => 'textstartatintiming',
            'props' => ['meta' => true],
            'options' => [
                'transitioninstart' => cp__('Opening Transition starts'),
                'transitioninend' => cp__('Opening Transition completes'),
                'loopstart' => cp__('Loop starts'),
                'loopend' => cp__('Loop completes'),
                'transitioninandloopend' => cp__('Opening and Loop Transitions complete'),
            ],
        ],

        'textStartAtInOperator' => [
            'value' => '+',
            'keys' => 'textstartatinoperator',
            'props' => ['meta' => true],
            'options' => ['+', '-', '/', '*'],
        ],

        'textStartAtInValue' => [
            'value' => 0,
            'keys' => 'textstartatinvalue',
            'props' => ['meta' => true],
            'attrs' => ['step' => 50, 'placeholder' => 0],
        ],

        'textRotateIn' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'tooltip' => cp__(
                'Rotates text fragments clockwise by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textrotatein',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textRotateXIn' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'tooltip' => cp__(
                'Rotates text fragments along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textrotatexin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textRotateYIn' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'tooltip' => cp__(
                'Rotates text fragments along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textrotateyin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textScaleXIn' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'textscalexin',
            'tooltip' => cp__(
                'Scales text fragments along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. ' .
                'The value 2 will double, while 0.5 shrinks text fragments compared to their original size. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'textScaleYIn' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'textscaleyin',
            'tooltip' => cp__(
                'Scales text fragments along the Y (vertical) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks text fragments compared to their original size. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'textSkewXIn' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'tooltip' => cp__(
                'Skews text fragments along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textskewxin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textSkewYIn' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'tooltip' => cp__(
                'Skews text fragments along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textskewyin',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textTransformOriginIn' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. ' .
                'For example, a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center, ' .
                'popupcenter, popupmiddle, popuptop, popupright, popupbottom, popupleft.'
            ),
            'keys' => 'texttransformoriginin',
            'attrs' => ['placeholder' => '50% 50% 0', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "50% 50% 0|100% 100% 0"
            }]'],
        ],

        'textPerspectiveIn' => [
            'value' => '500',
            'name' => cp__('Perspective'),
            'keys' => 'texttransformperspectivein',
            'tooltip' => cp__('Changes the perspective of this layer in the 3D space.'),
            'attrs' => ['placeholder' => 500],
        ],

        // Text Animation OUT
        // -----------------

        'textTransitionOut' => [
            'value' => false,
            'keys' => 'texttransitionout',
        ],

        'textTypeOut' => [
            'value' => 'chars_desc',
            'name' => cp__('Text Animation'),
            'keys' => 'texttypeout',
            'tooltip' => cp__('Select how your text should be split and animated.'),
            'options' => [
                'chars_asc' => cp__('by chars ascending'),
                'chars_desc' => cp__('by chars descending'),
                'chars_rand' => cp__('by chars random'),
                'chars_center' => cp__('by chars center to edge'),
                'chars_edge' => cp__('by chars edge to center'),
                'words_asc' => cp__('by words ascending'),
                'words_desc' => cp__('by words descending'),
                'words_rand' => cp__('by words random'),
                'words_center' => cp__('by words center to edge'),
                'words_edge' => cp__('by words edge to center'),
                'lines_asc' => cp__('by lines ascending'),
                'lines_desc' => cp__('by lines descending'),
                'lines_rand' => cp__('by lines random'),
                'lines_center' => cp__('by lines center to edge'),
                'lines_edge' => cp__('by lines edge to center'),
            ],
            'props' => [
                'output' => true,
            ],
        ],

        'textShiftOut' => [
            'value' => '',
            'name' => cp__('Shift Out'),
            'tooltip' => cp__(
                'Delays the transition of each text nodes relative to each other. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'keys' => 'textshiftout',
            'attrs' => ['type' => 'number'],
        ],

        'textOffsetXOut' => [
            'value' => 0,
            'name' => cp__('OffsetX'),
            'tooltip' => cp__(
                'Shifts the ending position of text nodes from their original ' .
                'on the horizontal axis with the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'left' or 'right' position text nodes out the staging area, " .
                'so they leave the scene from either side when animating to their destination location. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textoffsetxout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Leave the stage on left",
                "value": "left"
            }, {
                "name": "Leave the stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% popup width",
                "value": "50sw"
            }, {
                "name": "-50% popup width",
                "value": "-50sw"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'textOffsetYOut' => [
            'value' => 0,
            'name' => cp__('OffsetY'),
            'tooltip' => cp__(
                'Shifts the ending position of text nodes from their original on the vertical axis ' .
                'with the given number of pixels. Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'top' or 'bottom' position text nodes out the staging area, " .
                'so they leave the scene from either vertical side when animating to their destination location. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'keys' => 'textoffsetyout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Leave the stage on top",
                "value": "top"
            }, {
                "name": "Leave the stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% popup height",
                "value": "50sh"
            }, {
                "name": "-50% popup height",
                "value": "-50sh"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'textDurationOut' => [
            'value' => 1000,
            'name' => cp__('Duration'),
            'tooltip' => cp__(
                'The transition length in milliseconds of the individual text fragments. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'keys' => 'textdurationout',
            'attrs' => ['min' => 0, 'step' => 50, 'placeholder' => 1000],
        ],

        'textEasingOut' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Easing'),
            'tooltip' => cp__(
                'The timing function of the animation. ' .
                'With this function you can manipulate the movement of animated text fragments. ' .
                'Please click on the link next to this select field to open easings.net ' .
                'for more information and real-time examples.'
            ),
            'keys' => 'texteasingout',
            'attrs' => ['type' => 'hidden'],
        ],

        'textFadeOut' => [
            'value' => true,
            'name' => cp__('Fade'),
            'tooltip' => cp__('Fade the text fragments during their transition.'),
            'keys' => 'textfadeout',
        ],

        'textStartAtOut' => [
            'value' => 'allinandloopend',
            'name' => cp__('StartAt'),
            'tooltip' => cp__(
                'You can set the starting time of this transition. ' .
                'Use one of the pre-defined options to use relative timing, ' .
                'which can be shifted with custom operations.'
            ),
            'keys' => 'textstartatout',
            'attrs' => ['type' => 'hidden'],
        ],

        'textStartAtOutTiming' => [
            'value' => 'allinandloopend',
            'keys' => 'textstartatouttiming',
            'props' => ['meta' => true],
            'options' => [
                'transitioninend' => cp__('Opening Transition completes'),
                'textinstart' => cp__('Opening Text Transition starts'),
                'textinend' => cp__('Opening Text Transition completes'),
                'allinend' => cp__('Opening and Opening Text Transition complete'),
                'loopstart' => cp__('Loop starts'),
                'loopend' => cp__('Loop completes'),
                'transitioninandloopend' => cp__('Opening and Loop Transitions complete'),
                'textinandloopend' => cp__('Opening Text and Loop Transitions complete'),
                'allinandloopend' => cp__('Opening, Opening Text and Loop Transitions complete'),
            ],
        ],

        'textStartAtOutOperator' => [
            'value' => '+',
            'keys' => 'textstartatoutoperator',
            'props' => ['meta' => true],
            'options' => ['+', '-', '/', '*'],
        ],

        'textStartAtOutValue' => [
            'value' => 0,
            'keys' => 'textstartatoutvalue',
            'props' => ['meta' => true],
            'attrs' => ['step' => 50, 'placeholder' => 0],
        ],

        'textRotateOut' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'tooltip' => cp__(
                'Rotates text fragments clockwise by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'keys' => 'textrotateout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
            "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textRotateXOut' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'tooltip' => cp__(
                'Rotates text fragments along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'keys' => 'textrotatexout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textRotateYOut' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'tooltip' => cp__(
                'Rotates text fragments along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'keys' => 'textrotateyout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textScaleXOut' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'textscalexout',
            'tooltip' => cp__(
                'Scales text fragments along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks text fragments compared to their original size. ' .
                'By listing multiple values separated with a | character, the popup will use different transition ' .
                'variations on each text node by cycling between the provided values.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'textScaleYOut' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'textscaleyout',
            'tooltip' => cp__(
                'Scales text fragments along the Y (vertical) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks text fragments compared to their original size. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'textSkewXOut' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'tooltip' => cp__(
                'Skews text fragments along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'keys' => 'textskewxout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textSkewYOut' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'tooltip' => cp__(
                'Skews text fragments along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction. ' .
                'By listing multiple values separated with a | character, ' .
                'the popup will use different transition variations on each text node ' .
                'by cycling between the provided values.'
            ),
            'keys' => 'textskewyout',
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'textTransformOriginOut' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. ' .
                'For example, a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center, ' .
                'popupcenter, popupmiddle, popuptop, popupright, popupbottom, popupleft.'
            ),
            'keys' => 'texttransformoriginout',
            'attrs' => ['type' => 'text', 'placeholder' => '50% 50% 0', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "50% 50% 0|100% 100% 0"
            }]'],
        ],

        'textPerspectiveOut' => [
            'value' => '500',
            'name' => cp__('Perspective'),
            'keys' => 'texttransformperspectiveout',
            'tooltip' => cp__('Changes the perspective of this layer in the 3D space.'),
        ],

        // ======

        // LOOP

        'loop' => [
            'value' => false,
            'keys' => 'loop',
        ],

        'loopOffsetX' => [
            'value' => 0,
            'name' => cp__('OffsetX'),
            'keys' => 'loopoffsetx',
            'tooltip' => cp__(
                'Shifts the layer starting position from its original on the horizontal axis ' .
                'with the given number of pixels. Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer. ' .
                "The values 'left' or 'right' position the layer out the staging area, " .
                'so it can leave and re-enter the scene from either side during the transition.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Move out of stage on left",
                "value": "left"
            }, {
                "name": "Move out of stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% popup width",
                "value": "50sw"
            }, {
                "name": "-50% popup width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'loopOffsetY' => [
            'value' => 0,
            'name' => cp__('OffsetY'),
            'keys' => 'loopoffsety',
            'tooltip' => cp__(
                'Shifts the layer starting position from its original on the vertical axis ' .
                'with the given number of pixels. Use negative values for the opposite direction. ' .
                'Percentage values are relative to the height of this layer. ' .
                "The values 'top' or 'bottom' position the layer out the staging area, " .
                'so it can leave and re-enter the scene from either vertical side during the transition.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Move out of stage on top",
                "value": "top"
            }, {
                "name": "Move out of stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% popup height",
                "value": "50sh"
            }, {
                "name": "-50% popup height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'loopDuration' => [
            'value' => 1000,
            'name' => cp__('Duration'),
            'keys' => 'loopduration',
            'tooltip' => cp__('The length of the transition in milliseconds. A second is equal to 1000 milliseconds.'),
            'attrs' => ['min' => 0, 'step' => 100, 'placeholder' => 1000],
        ],

        'loopStartAt' => [
            'value' => 'allinend',
            'name' => cp__('Start at'),
            'keys' => 'loopstartat',
            'tooltip' => cp__(
                'You can set the starting time of this transition. ' .
                'Use one of the pre-defined options to use relative timing, ' .
                'which can be shifted with custom operations.'
            ),
            'attrs' => ['type' => 'hidden'],
        ],

        'loopStartAtTiming' => [
            'value' => 'allinend',
            'keys' => 'loopstartattiming',
            'props' => ['meta' => true],
            'options' => [
                'transitioninstart' => cp__('Opening Transition starts'),
                'transitioninend' => cp__('Opening Transition completes'),
                'textinstart' => cp__('Opening Text Transition starts'),
                'textinend' => cp__('Opening Text Transition completes'),
                'allinend' => cp__('Opening and Opening Text Transition complete'),
            ],
        ],

        'loopStartAtOperator' => [
            'value' => '+',
            'keys' => 'loopstartatoperator',
            'props' => ['meta' => true],
            'options' => ['+', '-', '/', '*'],
        ],

        'loopStartAtValue' => [
            'value' => 0,
            'keys' => 'loopstartatvalue',
            'props' => ['meta' => true],
            'attrs' => ['step' => 50, 'placeholder' => 0],
        ],

        'loopEasing' => [
            'value' => 'linear',
            'name' => cp__('Easing'),
            'keys' => 'loopeasing',
            'tooltip' => cp__(
                "The timing function of the animation to manipualte the layer's movement. " .
                'Click on the link next to this field to open easings.net for examples and more information'
            ),
        ],

        'loopOpacity' => [
            'value' => 1,
            'name' => cp__('Opacity'),
            'keys' => 'loopopacity',
            'tooltip' => cp__(
                'Fades the layer. You can use values between 1 and 0 ' .
                'to set the layer fully opaque or transparent respectively. ' .
                'For example, the value 0.5 will make the layer semi-transparent.'
            ),
            'attrs' => ['min' => 0, 'max' => 1, 'step' => 0.01, 'placeholder' => 1],
        ],

        'loopRotate' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'keys' => 'looprotate',
            'tooltip' => cp__(
                'Rotates the layer by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'loopRotateX' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'keys' => 'looprotatex',
            'tooltip' => cp__(
                'Rotates the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'loopRotateY' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'keys' => 'looprotatey',
            'tooltip' => cp__(
                'Rotates the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'loopSkewX' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'keys' => 'loopskewx',
            'tooltip' => cp__(
                'Skews the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'loopSkewY' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'keys' => 'loopskewy',
            'tooltip' => cp__(
                'Skews the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'loopScaleX' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'loopscalex',
            'tooltip' => cp__(
                'Scales the layer along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'loopScaleY' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'loopscaley',
            'tooltip' => cp__(
                'Scales the layer along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'loopTransformOrigin' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'keys' => 'looptransformorigin',
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. ' .
                'For example, a layer may rotate around its center axis or a completely custom point, ' .
                'such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center, ' .
                'popupcenter, popupmiddle, popuptop, popupright, popupbottom, popupleft.'
            ),
            'attrs' => ['placeholder' => '50% 50% 0'],
        ],

        'loopClip' => [
            'value' => '',
            'name' => cp__('Mask'),
            'keys' => 'loopclip',
            'tooltip' => cp__(
                'Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. ' .
                'The 4 value in order: top, right, bottom and the left side of the layer.'
            ),
            'attrs' => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
        ],

        'loopCount' => [
            'value' => 1,
            'name' => cp__('Count'),
            'keys' => 'loopcount',
            'tooltip' => cp__(
                'The number of times repeating the Loop transition. ' .
                'The count includes the reverse part of the transitions when you use the Yoyo feature. ' .
                'Use the value -1 to repeat infinitely or zero to disable looping.'
            ),
            'attrs' => [
                'step' => 1,
                'placeholder' => 1,
                'data-options' => '[{
                    "name": "Infinite",
                    "value": -1
                }]',
            ],
            'props' => [
                'output' => true,
            ],
        ],

        'loopWait' => [
            'value' => 0,
            'name' => cp__('Wait'),
            'keys' => 'looprepeatdelay',
            'tooltip' => cp__('Waiting time between repeats in milliseconds. A second is 1000 milliseconds.'),
            'attrs' => ['min' => 0, 'step' => 100, 'placeholder' => 0],
        ],

        'loopYoyo' => [
            'value' => false,
            'name' => cp__('Yoyo'),
            'keys' => 'loopyoyo',
            'tooltip' => cp__(
                'Enable this option to allow reverse transition, so you can loop back and forth seamlessly.'
            ),
        ],

        'loopPerspective' => [
            'value' => '500',
            'name' => cp__('Perspective'),
            'keys' => 'looptransformperspective',
            'tooltip' => cp__('Changes the perspective of this layer in the 3D space.'),
            'attrs' => ['placeholder' => 500],
        ],

        'loopFilter' => [
            'value' => '',
            'name' => cp__('Filter'),
            'keys' => 'loopfilter',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list ' .
                'of all the filters you would like to use. Click on the "Filter" link for more information.'
            ),
            'premium' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],

        // HOVER

        'hover' => [
            'value' => false,
            'keys' => 'hover',
        ],

        'hoverOffsetX' => [
            'value' => 0,
            'name' => cp__('OffsetX'),
            'keys' => 'hoveroffsetx',
            'tooltip' => cp__(
                'Moves the layer horizontally by the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "20% layer width",
                "value": "20lw"
            }, {
                "name": "-20% layer width",
                "value": "-20lw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'hoverOffsetY' => [
            'value' => 0,
            'name' => cp__('OffsetY'),
            'keys' => 'hoveroffsety',
            'tooltip' => cp__(
                'Moves the layer vertically by the given number of pixels. ' .
                'Use negative values for the opposite direction. ' .
                'Percentage values are relative to the width of this layer.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "20% layer height",
                "value": "20lh"
            }, {
                "name": "-20% layer height",
                "value": "-20lh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
        ],

        'hoverInDuration' => [
            'value' => 500,
            'name' => cp__('Duration'),
            'keys' => 'hoverdurationin',
            'tooltip' => cp__('The length of the transition in milliseconds. A second is equal to 1000 milliseconds.'),
            'attrs' => ['min' => 0, 'step' => 100, 'placeholder' => 500],
        ],

        'hoverOutDuration' => [
            'value' => '',
            'name' => cp__('Reverse<br>duration'),
            'keys' => 'hoverdurationout',
            'tooltip' => cp__(
                'The duration of the reverse transition in milliseconds. A second is equal to 1000 milliseconds.'
            ),
            'attrs' => ['min' => 0, 'step' => 100, 'placeholder' => 'same'],
        ],

        'hoverInEasing' => [
            'value' => 'easeInOutQuint',
            'name' => cp__('Easing'),
            'keys' => 'hovereasingin',
            'tooltip' => cp__(
                "The timing function of the animation to manipualte the layer's movement. " .
                'Click on the link next to this field to open easings.net for examples and more information'
            ),
        ],

        'hoverOutEasing' => [
            'value' => '',
            'name' => cp__('Reverse<br>easing'),
            'keys' => 'hovereasingout',
            'tooltip' => cp__(
                "The timing function of the reverse animation to manipualte the layer's movement. " .
                'Click on the link next to this field to open easings.net for examples and more information'
            ),
            'attrs' => ['placeholder' => 'same'],
        ],

        'hoverOpacity' => [
            'value' => '',
            'name' => cp__('Opacity'),
            'keys' => 'hoveropacity',
            'tooltip' => cp__(
                'Fades the layer. You can use values between 1 and 0 ' .
                'to set the layer fully opaque or transparent respectively. ' .
                'For example, the value 0.5 will make the layer semi-transparent.'
            ),
            'attrs' => [
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
            ],
        ],

        'hoverRotate' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'keys' => 'hoverrotate',
            'tooltip' => cp__(
                'Rotates the layer clockwise by the given number of degrees. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'hoverRotateX' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'keys' => 'hoverrotatex',
            'tooltip' => cp__(
                'Rotates the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'hoverRotateY' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'keys' => 'hoverrotatey',
            'tooltip' => cp__(
                'Rotates the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'hoverSkewX' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'keys' => 'hoverskewx',
            'tooltip' => cp__(
                'Skews the layer along the X (horizontal) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'hoverSkewY' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'keys' => 'hoverskewy',
            'tooltip' => cp__(
                'Skews the layer along the Y (vertical) axis by the given number of degrees. ' .
                'Negative values are allowed for reverse direction.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 0, 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
        ],

        'hoverScaleX' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'hoverscalex',
            'tooltip' => cp__(
                'Scales the layer along the X (horizontal) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'hoverScaleY' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'hoverscaley',
            'tooltip' => cp__(
                'Scales the layer along the Y (vertical) axis by the specified vector. ' .
                'Use the value 1 for the original size. The value 2 will double, ' .
                'while 0.5 shrinks the layer compared to its original size.'
            ),
            'attrs' => ['type' => 'text', 'placeholder' => 1, 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
        ],

        'hoverTransformOrigin' => [
            'value' => '50% 50% 0',
            'name' => cp__('Transform Origin'),
            'keys' => 'hovertransformorigin',
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. For example, ".
                "a layer may rotate around its center axis or a completely custom point, ".
                "such as one of its corners. The three values represent the X, Y and Z axes in 3D space. ".
                "Apart from the pixel and percentage values, you can also use the following constants: ".
                "top, right, bottom, left, center.'
            ),
            'attrs' => ['placeholder' => '50% 50% 0'],
        ],

        'hoverBGColor' => [
            'value' => '',
            'name' => cp__('Background'),
            'keys' => 'hoverbgcolor',
            'tooltip' => cp__(
                'The background color of this layer. You can use color names, hexadecimal, ' .
                "RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF"
            ),
        ],

        'hoverColor' => [
            'value' => '',
            'name' => cp__('Color'),
            'keys' => 'hovercolor',
            'tooltip' => cp__(
                'The text color of this text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333'
            ),
        ],

        'hoverBorderRadius' => [
            'value' => '',
            'name' => cp__('Rounded corners'),
            'keys' => 'hoverborderradius',
            'tooltip' => cp__('If you want rounded corners, you can set here its radius in pixels. Example: 5px'),
        ],

        'hoverTransformPerspective' => [
            'value' => 500,
            'name' => cp__('Perspective'),
            'keys' => 'hovertransformperspective',
            'tooltip' => cp__('Changes the perspective of layers in the 3D space.'),
            'attrs' => ['placeholder' => 500],
        ],

        'hoverTopOn' => [
            'value' => true,
            'name' => cp__('Always on top'),
            'keys' => 'hoveralwaysontop',
            'tooltip' => cp__('Show this layer above every other layer while hovering.'),
        ],

        // Parallax
        'parallax' => [
            'value' => false,
            'keys' => 'parallax',
        ],

        'parallaxLevel' => [
            'value' => 10,
            'name' => cp__('Parallax Level'),
            'tooltip' => cp__(
                'Set the intensity of the parallax effect. ' .
                'Use negative values to shift layers in the opposite direction.'
            ),
            'keys' => 'parallaxlevel',
            'props' => [
                'output' => true,
            ],
            'attrs' => ['placeholder' => 10],
        ],

        'parallaxType' => [
            'value' => 'inherit',
            'name' => cp__('Type'),
            'tooltip' => cp__('Choose if you want 2D or 3D parallax layers.'),
            'keys' => 'parallaxtype',
            'options' => [
                'inherit' => cp__('Inherit from Page Options'),
                '2d' => cp__('2D'),
                '3d' => cp__('3D'),
            ],
        ],

        'parallaxEvent' => [
            'value' => 'inherit',
            'name' => cp__('Event'),
            'tooltip' => cp__(
                'You can trigger the parallax effect by either scrolling the page, ' .
                'or by moving your mouse cursor / tilting your mobile device.'
            ),
            'keys' => 'parallaxevent',
            'options' => [
                'inherit' => cp__('Inherit from Page Options'),
                'cursor' => cp__('Cursor or Tilt'),
                'scroll' => cp__('Scroll'),
            ],
        ],

        'parallaxAxis' => [
            'value' => 'inherit',
            'name' => cp__('Axes'),
            'tooltip' => cp__('Choose on which axes parallax layers should move.'),
            'keys' => 'parallaxaxis',
            'options' => [
                'inherit' => cp__('Inherit from Page Options'),
                'none' => cp__('None'),
                'both' => cp__('Both'),
                'x' => cp__('Horizontal only'),
                'y' => cp__('Vertical only'),
            ],
        ],

        'parallaxTransformOrigin' => [
            'value' => '',
            'name' => cp__('Transform Origin'),
            'tooltip' => cp__(
                'Sets a point on canvas from which transformations are calculated. For example, ' .
                'a layer may rotate around its center axis or a completely custom point, such as one of its corners. ' .
                'The three values represent the X, Y and Z axes in 3D space. ' .
                'Apart from the pixel and percentage values, you can also use the following constants: ' .
                'top, right, bottom, left, center.'
            ),
            'keys' => 'parallaxtransformorigin',
            'attrs' => [
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        'parallaxDurationMove' => [
            'value' => '',
            'name' => cp__('Move Duration'),
            'tooltip' => cp__(
                'Controls the speed of animating layers when you move your mouse cursor or tilt your mobile device.'
            ),
            'keys' => 'parallaxdurationmove',
            'attrs' => [
                'type' => 'number',
                'step' => 100,
                'min' => 0,
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        'parallaxDurationLeave' => [
            'value' => '',
            'name' => cp__('Leave Duration'),
            'tooltip' => cp__(
                'Controls how quickly parallax layers revert to their original position ' .
                'when you move your mouse cursor outside of the popup. This value is in milliseconds. ' .
                'A second equals to 1000 milliseconds.'
            ),
            'keys' => 'parallaxdurationleave',
            'attrs' => [
                'type' => 'number',
                'step' => 100,
                'min' => 0,
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        'parallaxRotate' => [
            'value' => '',
            'name' => cp__('Rotation'),
            'tooltip' => cp__(
                'Increase or decrease the amount of layer rotation in the 3D space ' .
                'when moving your mouse cursor or tilting on a mobile device.'
            ),
            'keys' => 'parallaxrotate',
            'attrs' => [
                'type' => 'number',
                'step' => 1,
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        'parallaxDistance' => [
            'value' => '',
            'name' => cp__('Distance'),
            'tooltip' => cp__(
                'Increase or decrease the amount of layer movement ' .
                'when moving your mouse cursor or tilting on a mobile device.'
            ),
            'keys' => 'parallaxdistance',
            'attrs' => [
                'type' => 'number',
                'step' => 1,
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        'parallaxPerspective' => [
            'value' => '',
            'name' => cp__('Perspective'),
            'tooltip' => cp__('Changes the perspective of layers in the 3D space.'),
            'keys' => 'parallaxtransformperspective',
            'attrs' => [
                'type' => 'number',
                'step' => 100,
                'placeholder' => cp__('Inherit from Page Options'),
            ],
        ],

        // TRANSITON MISC
        'transitionStatic' => [
            'value' => 'none',
            'name' => cp__('Static layer'),
            'keys' => 'static',
            'tooltip' => cp__(
                'You can keep this layer on top of the popup across multiple pages. ' .
                'Just select the page on which this layer should animate out. ' .
                'Alternatively, you can make this layer global on all pages after it transitioned in.'
            ),
            'options' => [
                'none' => cp__('Disabled (default)'),
                'forever' => cp__('Enabled (never animate out)'),
            ],
        ],

        // Attributes

        'linkURL' => [
            'value' => '',
            'name' => cp__('Enter URL'),
            'keys' => 'url',
            'tooltip' => cp__(
                'If you want to link your layer, type here the URL. ' .
                'You can use a hash mark followed by a number to link this layer to another popup page. ' .
                'Example: #3 - this will switch to the third page.'
            ),
            'attrs' => [
                'data-options' => '[{
                    "name": "Switch to the next page",
                    "value": "#next"
                }, {
                    "name": "Switch to the previous page",
                    "value": "#prev"
                }, {
                    "name": "Stop the pageshow",
                    "value": "#stop"
                }, {
                    "name": "Resume the pageshow",
                    "value": "#start"
                }, {
                    "name": "Replay the page from the start",
                    "value": "#replay"
                }, {
                    "name": "Reverse the page, then pause it",
                    "value": "#reverse"
                }, {
                    "name": "Reverse the page, then replay it",
                    "value": "#reverse-replay"
                }, {
                    "name": "Close the Popup",
                    "value": "#closepopup"
                }]',
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'linkTarget' => [
            'value' => '_self',
            'name' => cp__('URL target'),
            'keys' => 'target',
            'options' => [
                '_self' => cp__('Open on the same page'),
                '_blank' => cp__('Open on new page'),
                '_parent' => cp__('Open in parent frame'),
                '_top' => cp__('Open in main frame'),
                'cp-scroll' => cp__('Scroll to element (Enter selector)'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'innerAttributes' => [
            'value' => '',
            'name' => cp__('Custom Attributes'),
            'keys' => 'innerAttributes',
            'desc' => cp__(
                'Your list of custom attributes. Use this feature if your needs are not covered ' .
                'by the common attributes above or you want to override them. You can use data-* ' .
                'as well as regular attribute names. Empty attributes (without value) are also allowed. ' .
                'For example, to make a FancyBox gallery, you may enter "data-fancybox-group" and "gallery1" ' .
                'for the attribute name and value, respectively.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'outerAttributes' => [
            'value' => '',
            'name' => cp__('Custom Attributes'),
            'keys' => 'outerAttributes',
            'desc' => cp__(
                'Your list of custom attributes. Use this feature if your needs are not covered ' .
                'by the common attributes above or you want to override them. You can use data-* ' .
                'as well as regular attribute names. Empty attributes (without value) are also allowed. ' .
                'For example, to make a FancyBox gallery, you may enter "data-fancybox-group" and "gallery1" ' .
                'for the attribute name and value, respectively.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        // Styles

        'width' => [
            'value' => '',
            'name' => cp__('Width'),
            'keys' => 'width',
            'tooltip' => cp__(
                'You can set the width of your layer. You can use pixels, percentage, ' .
                "or the default value 'auto'. Examples: 100px, 50% or auto."
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'height' => [
            'value' => '',
            'name' => cp__('Height'),
            'keys' => 'height',
            'tooltip' => cp__(
                'You can set the height of your layer. You can use pixels, percentage, ' .
                "or the default value 'auto'. Examples: 100px, 50% or auto"
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'top' => [
            'value' => '10px',
            'name' => cp__('Top'),
            'keys' => 'top',
            'tooltip' => cp__(
                'The layer position from the top of the page. You can use pixels and percentage. ' .
                'Examples: 100px or 50%. You can move your layers in the preview above with a drag and drop, ' .
                'or set the exact values here.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'left' => [
            'value' => '10px',
            'name' => cp__('Left'),
            'keys' => 'left',
            'tooltip' => cp__(
                'The layer position from the left side of the page. You can use pixels and percentage. ' .
                'Examples: 100px or 50%. You can move your layers in the preview above with a drag and drop, ' .
                'or set the exact values here.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'paddingTop' => [
            'value' => '',
            'name' => cp__('Top'),
            'keys' => 'padding-top',
            'tooltip' => cp__('Padding on the top of the layer. Example: 10px'),
            'props' => [
                'meta' => true,
            ],
        ],

        'paddingRight' => [
            'value' => '',
            'name' => cp__('Right'),
            'keys' => 'padding-right',
            'tooltip' => cp__('Padding on the right side of the layer. Example: 10px'),
            'props' => [
                'meta' => true,
            ],
        ],

        'paddingBottom' => [
            'value' => '',
            'name' => cp__('Bottom'),
            'keys' => 'padding-bottom',
            'tooltip' => cp__('Padding on the bottom of the layer. Example: 10px'),
            'props' => [
                'meta' => true,
            ],
        ],

        'paddingLeft' => [
            'value' => '',
            'name' => cp__('Left'),
            'keys' => 'padding-left',
            'tooltip' => cp__('Padding on the left side of the layer. Example: 10px'),
            'props' => [
                'meta' => true,
            ],
        ],

        'borderTop' => [
            'value' => '',
            'name' => cp__('Top'),
            'keys' => 'border-top',
            'tooltip' => cp__('Border on the top of the layer. Example: 5px solid #000'),
            'props' => [
                'meta' => true,
            ],
        ],

        'borderRight' => [
            'value' => '',
            'name' => cp__('Right'),
            'keys' => 'border-right',
            'tooltip' => cp__('Border on the right side of the layer. Example: 5px solid #000'),
            'props' => [
                'meta' => true,
            ],
        ],

        'borderBottom' => [
            'value' => '',
            'name' => cp__('Bottom'),
            'keys' => 'border-bottom',
            'tooltip' => cp__('Border on the bottom of the layer. Example: 5px solid #000'),
            'props' => [
                'meta' => true,
            ],
        ],

        'borderLeft' => [
            'value' => '',
            'name' => cp__('Left'),
            'keys' => 'border-left',
            'tooltip' => cp__('Border on the left side of the layer. Example: 5px solid #000'),
            'props' => [
                'meta' => true,
            ],
        ],

        'fontFamily' => [
            'value' => '',
            'name' => cp__('Family'),
            'keys' => 'font-family',
            'tooltip' => cp__(
                'List of your chosen fonts separated with a comma. ' .
                'Please use apostrophes if your font names contains white spaces. ' .
                'Example: Helvetica, Arial, sans-serif'
            ),
        ],

        'fontSize' => [
            'value' => '',
            'name' => cp__('Font size'),
            'keys' => 'font-size',
            'tooltip' => cp__('The font size in pixels. Example: 16px.'),
            'attrs' => [
                'data-options' => '["9", "10", "11", "12", "13", "14", "18", "24", "36", "48", "64", "96"]',
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'lineHeight' => [
            'value' => '',
            'name' => cp__('Line height'),
            'keys' => 'line-height',
            'tooltip' => cp__("The line height of your text. The default setting is 'normal'. Example: 22px"),
            'props' => [
                'meta' => true,
            ],
        ],

        'fontWeight' => [
            'value' => 400,
            'name' => cp__('Font weight'),
            'keys' => 'font-weight',
            'tooltip' => cp__(
                'Sets the font boldness. Please note, not every font supports all the listed variants, ' .
                'thus some settings may have the same result.'
            ),
            'options' => [
                '100' => cp__('100 (UltraLight)'),
                '200' => cp__('200 (Thin)'),
                '300' => cp__('300 (Light)'),
                '400' => cp__('400 (Regular)'),
                '500' => cp__('500 (Medium)'),
                '600' => cp__('600 (Semibold)'),
                '700' => cp__('700 (Bold)'),
                '800' => cp__('800 (Heavy)'),
                '900' => cp__('900 (Black)'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'fontStyle' => [
            'value' => 'normal',
            'name' => cp__('Font style'),
            'keys' => 'font-style',
            'tooltip' => cp__(
                'Oblique is an auto-generated italic version of your chosen font and can force slating even ' .
                'if there is no italic font variant available. ' .
                'However, you should use the regular italic option whenever is possible. ' .
                'Please double check to load italic font variants when using Google Fonts.'
            ),
            'options' => [
                'normal' => cp__('Normal'),
                'italic' => cp__('Italic'),
                'oblique' => cp__('Oblique (Forced slant)'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'textDecoration' => [
            'value' => 'none',
            'name' => cp__('Text decoration'),
            'keys' => 'text-decoration',
            'options' => [
                'none' => 'None',
                'underline' => cp__('Underline'),
                'overline' => cp__('Overline'),
                'line-through' => cp__('Line through'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'letterSpacing' => [
            'value' => '',
            'name' => cp__('Letter spacing'),
            'keys' => 'letter-spacing',
            'tooltip' => cp__(
                'Controls the amount of space between each character. Useful the change letter density in a line or ' .
                'block of text. Negative values and decimals can be used.'
            ),
            'attrs' => [
                'type' => 'number',
                'step' => 0.5,
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'textAlign' => [
            'value' => 'none',
            'name' => cp__('Text align'),
            'keys' => 'text-align',
            'options' => [
                'initial' => cp__('Initial (Language default)'),
                'left' => cp__('Left'),
                'right' => cp__('Right'),
                'center' => cp__('Center'),
                'justify' => cp__('Justify'),
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'opacity' => [
            'value' => 1,
            'name' => cp__('Opacity'),
            'keys' => 'opacity',
            'tooltip' => cp__(
                'Fades the layer. You can use values between 1 and 0 to set the layer fully opaque or ' .
                'transparent respectively. For example, the value 0.5 will make the layer semi-transparent.'
            ),
            'attrs' => [
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
            ],
            'props' => [
                'meta' => true,
            ],
        ],

        'minFontSize' => [
            'value' => '',
            'name' => cp__('Min. font size'),
            'keys' => 'minfontsize',
            'tooltip' => cp__(
                'The minimum font size in the popup. This option allows you to prevent your texts layers ' .
                'becoming too small on smaller screens.'
            ),
        ],

        'minMobileFontSize' => [
            'value' => '',
            'name' => cp__('Min. mobile font size'),
            'keys' => 'minmobilefontsize',
            'tooltip' => cp__(
                'The minimum font size in the popup on mobile devices. ' .
                'This option allows you to prevent your texts layers becoming too small on smaller screens.'
            ),
        ],

        'color' => [
            'value' => '',
            'name' => cp__('Color'),
            'keys' => 'color',
            'tooltip' => cp__(
                'The color of your text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'background' => [
            'value' => '',
            'name' => cp__('Background'),
            'keys' => 'background',
            'tooltip' => cp__(
                'The background color of your layer. You can use color names, hexadecimal, ' .
                "RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF"
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'borderRadius' => [
            'value' => '',
            'name' => cp__('Rounded corners'),
            'keys' => 'border-radius',
            'tooltip' => cp__('If you want rounded corners, you can set its radius here. Example: 5px'),
            'props' => [
                'meta' => true,
            ],
        ],

        'wordWrap' => [
            'value' => false,
            'name' => 'Word-wrap',
            'keys' => 'wordwrap',
            'tooltip' => cp__(
                'Enable this option to allow line breaking if your text content does not fit into one line. ' .
                'By default, layers have auto sizes based on the text length. If you set custom sizes, ' .
                'it\'s recommended to enable this option in most cases.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'style' => [
            'value' => '',
            'name' => cp__('Custom styles'),
            'keys' => 'style',
            'tooltip' => cp__(
                'If you want to set style settings other than above, you can use here any CSS codes. ' .
                'Please make sure to write valid markup.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'styles' => [
            'value' => '',
            'keys' => 'styles',
            'props' => [
                'meta' => true,
                'raw' => true,
            ],
        ],

        'rotate' => [
            'value' => 0,
            'name' => cp__('Rotate'),
            'keys' => 'rotation',
            'tooltip' => cp__(
                'The rotation angle where this layer animates toward when entering into the popup canvas. ' .
                'Negative values are allowed for counterclockwise rotation.'
            ),
        ],

        'rotateX' => [
            'value' => 0,
            'name' => cp__('RotateX'),
            'keys' => 'rotationX',
            'tooltip' => cp__(
                'The rotation angle on the horizontal axis where this animates toward ' .
                'when entering into the popup canvas. Negative values are allowed for reversed direction.'
            ),
        ],

        'rotateY' => [
            'value' => 0,
            'name' => cp__('RotateY'),
            'keys' => 'rotationY',
            'tooltip' => cp__(
                'The rotation angle on the vertical axis where this layer animates toward ' .
                'when entering into the popup canvas. Negative values are allowed for reversed direction.'
            ),
        ],

        'scaleX' => [
            'value' => 1,
            'name' => cp__('ScaleX'),
            'keys' => 'scaleX',
            'tooltip' => cp__(
                'The layer horizontal scale where this layer animates toward when entering into the popup canvas.'
            ),
            'attrs' => [
                'step' => 0.1,
            ],
        ],

        'scaleY' => [
            'value' => 1,
            'name' => cp__('ScaleY'),
            'keys' => 'scaleY',
            'tooltip' => cp__(
                'The layer vertical scale where this layer animates toward when entering into the popup canvas.'
            ),
            'attrs' => [
                'step' => 0.1,
            ],
        ],

        'skewX' => [
            'value' => 0,
            'name' => cp__('SkewX'),
            'keys' => 'skewX',
            'tooltip' => cp__(
                'The layer horizontal skewing angle where this layer animates toward ' .
                'when entering into the popup canvas.'
            ),
        ],

        'skewY' => [
            'value' => 0,
            'name' => cp__('SkewY'),
            'keys' => 'skewY',
            'tooltip' => cp__(
                'The layer vertical skewing angle where this layer animates toward ' .
                'when entering into the popup canvas.'
            ),
        ],

        'position' => [
            'value' => 'relative',
            'name' => cp__('Calculate positions from'),
            'keys' => 'position',
            'tooltip' => cp__(
                'Sets the layer position origin from which top and left values are calculated. ' .
                'The default is the upper left corner of the popup canvas. In a full width and full size popup, ' .
                'your content is centered based on the screen size to achieve the best possible fit. ' .
                'By selecting the "sides of the screen" option in those scenarios, ' .
                'you can allow layers to escape the centered inner area and stick to the sides of the screen.'
            ),
            'options' => [
                'relative' => cp__('sides of the popup'),
                'fixed' => cp__('sides of the screen'),
            ],
        ],

        'zIndex' => [
            'value' => '',
            'name' => cp__('Stacking order'),
            'keys' => 'z-index',
            'tooltip' => cp__(
                'This option controls the vertical stacking order of layers that overlap. In CSS, ' .
                "it's commonly called as z-index. Elements with a higher value are stacked in front of elements " .
                'with a lower one, effectively covering them. By default, this value is calculated automatically ' .
                'based on the order of your layers, thus simply re-ordering them can fix overlap issues. ' .
                'Use this option only if you want to set your own value manually in special cases ' .
                'like using static layers.<br><br>On each page, the stacking order starts counting from 100. ' .
                'Providing a number less than 100 will put the layer behind every other layer on all pages. ' .
                'Specifying a much greater number, for example 500, ' .
                'will make the layer to be on top of everything else.'
            ),
            'attrs' => [
                'type' => 'number',
                'min' => 1,
                'placeholder' => 'auto',
            ],
        ],

        'blendMode' => [
            'value' => 'normal',
            'name' => cp__('Blend mode'),
            'keys' => 'mix-blend-mode',
            'tooltip' => cp__(
                'Choose how layers and the page background should blend into each other. ' .
                'Blend modes are an easy way to add eye-catching effects and is one of the ' .
                'most frequently used features in graphic and print design.'
            ),
            'premium' => true,
            'options' => [
                'normal' => 'Normal',
                'multiply' => 'Multiply',
                'screen' => 'Screen',
                'overlay' => 'Overlay',
                'darken' => 'Darken',
                'lighten' => 'Lighten',
                'color-dodge' => 'Color-dodge',
                'color-burn' => 'Color-burn',
                'hard-light' => 'Hard-light',
                'soft-light' => 'Soft-light',
                'difference' => 'Difference',
                'exclusion' => 'Exclusion',
                'hue' => 'Hue',
                'saturation' => 'Saturation',
                'color' => 'Color',
                'luminosity' => 'Luminosity',
            ],
        ],

        'filter' => [
            'value' => '',
            'name' => cp__('Filter'),
            'keys' => 'filter',
            'tooltip' => cp__(
                'Filters provide effects like blurring or color shifting your layers. ' .
                'Click into the text field to see a selection of filters you can use. ' .
                'Although clicking on the pre-defined options will reset the text field, ' .
                'you can apply multiple filters simply by providing a space separated list of all the filters ' .
                'you would like to use. Click on the "Filter" link for more information.'
            ),
            'premium' => true,
            'attrs' => [
                'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
            ],
        ],

        // Attributes

        'ID' => [
            'value' => '',
            'name' => cp__('ID'),
            'keys' => 'id',
            'tooltip' => cp__(
                'You can apply an ID attribute on the HTML element of this layer ' .
                'to work with it in your custom CSS or Javascript code.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'class' => [
            'value' => '',
            'name' => cp__('Classes'),
            'keys' => 'class',
            'tooltip' => cp__(
                'You can apply classes on the HTML element of this layer ' .
                'to work with it in your custom CSS or Javascript code.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'title' => [
            'value' => '',
            'name' => cp__('Title'),
            'keys' => 'title',
            'tooltip' => cp__(
                'You can add a title to this layer which will display as a tooltip ' .
                'if someone holds his mouse cursor over the layer.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'alt' => [
            'value' => '',
            'name' => cp__('Alt'),
            'keys' => 'alt',
            'tooltip' => cp__(
                'Name or describe your image layer, ' .
                'so search engines and VoiceOver softwares can properly identify it.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],

        'rel' => [
            'value' => '',
            'name' => cp__('Rel'),
            'keys' => 'rel',
            'tooltip' => cp__(
                'Plugins and search engines may use this attribute ' .
                'to get more information about the role and behavior of a link.'
            ),
            'props' => [
                'meta' => true,
            ],
        ],
    ],

    'easings' => [
        'linear',
        'swing',
        'easeInQuad',
        'easeOutQuad',
        'easeInOutQuad',
        'easeInCubic',
        'easeOutCubic',
        'easeInOutCubic',
        'easeInQuart',
        'easeOutQuart',
        'easeInOutQuart',
        'easeInQuint',
        'easeOutQuint',
        'easeInOutQuint',
        'easeInSine',
        'easeOutSine',
        'easeInOutSine',
        'easeInExpo',
        'easeOutExpo',
        'easeInOutExpo',
        'easeInCirc',
        'easeOutCirc',
        'easeInOutCirc',
        'easeInElastic',
        'easeOutElastic',
        'easeInOutElastic',
        'easeInBack',
        'easeOutBack',
        'easeInOutBack',
        'easeInBounce',
        'easeOutBounce',
        'easeInOutBounce',
    ],
];
