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

var magiczoomplusState = '';

$(document).ready(function() {
    if (typeof(window['display']) != 'undefined') {
        window['display_original'] = window['display'];
        window['display'] = function display(view) {
            if (typeof(MagicZoomPlus) != 'undefined' && magiczoomplusState != 'stopped') {
                magiczoomplusState = 'stopped';
                MagicZoom.stop();
            }
            var r = window['display_original'].apply(window, arguments);
            if (typeof(MagicZoomPlus) != 'undefined' && magiczoomplusState != 'started') {
                magiczoomplusState = 'started';
                MagicZoom.start();
            }
            return r;
        }
    }
});

if ($ && $.ajax) {
    (function($) {
        //NOTE: override default ajax method
        var ajax = $.ajax;
        $.ajax = function(url, options) {
            var settings = {};
            if (typeof url === 'object') {
                settings = url;
            } else {
                settings = options || {};
            }
            if (settings.type == 'GET' && settings.url == baseDir+'modules/blocklayered/blocklayered-ajax.php') {
                if (typeof(MagicZoomPlus) != 'undefined' && magiczoomplusState != 'stopped') {
                    magiczoomplusState = 'stopped';
                    MagicZoom.stop();
                }
                settings.url = baseDir+'modules/magiczoomplus/blocklayered-ajax.php';
                settings.successOriginal = settings.success;
                settings.success = function(result) {
                    var r = settings.successOriginal.apply(settings, arguments);
                    if (typeof(MagicZoomPlus) != 'undefined' && magiczoomplusState != 'started') {
                        magiczoomplusState = 'started';
                        MagicZoom.start();
                    }
                    return r;
                };
            }
            return ajax(url, options);
        }
    })($);
}
