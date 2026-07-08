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

if (jQueryNoConflictLevel == 1) {
    var $jq = jQuery.noConflict();
} else if (jQueryNoConflictLevel == 2) {
    var $jq = jQuery.noConflict(true);
} else {
    var $jq = jQuery;
}
$jq(document).ready(function($) {
    $('.mt-tabs a[data-mt-tab]').click(function(e){
        $('.mt-tabs a[data-mt-tab], .mt-tab-pane').removeClass('mt-active');
        $('#'+$(this).attr('data-mt-tab')).addClass('mt-active');
        $(this).addClass('mt-active');
        $('#magiczoomplus-active-tab').val($(this).attr('id').replace('-tab', ''));

        if ($('#'+$(this).attr('data-mt-tab')).attr('class').match(/sirv/gm)) {
            $('.mt-buttons').hide();
        } else {
            $('.mt-buttons').show();
        }

        e.preventDefault();
    })

    $('.mt-settings-form a.show-upgrade-instructions').click(function(e){
        $(this).parent().parent().find('ol').show();
        e.preventDefault();
    })

    $(".mt-parameter-keyword").keyup(function(){
        var filter = $(this).val().trim();

        var searchsource = $('#'+$(this).attr('data-search-source'));

        searchsource.find("fieldset").attr('data-hidden',1);

        searchsource.find(".mt-param-name").each(function(){
            $(this).removeHighlight();
            $(this).parent().removeClass('mt-not-matched-search');
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).parent().addClass('mt-not-matched-search');
                //$(this).parent().next().fadeOut(0);
            } else {
                $(this).highlight(filter);
                //$(this).parent().parent().parent().attr('data-hidden',0);
            }
        });

        mt_reset_fieldsets(searchsource);

    });

    $('.mt-show-hide-advanced').click(function(){
        var searchsource = $('#'+$(this).attr('data-search-source'));
        if ($(this).is(':checked')) {
            searchsource.addClass('mt-show-advanced');
        } else {
            searchsource.removeClass('mt-show-advanced');
        }

        $(".mt-parameter-keyword").trigger('keyup')

        mt_reset_fieldsets(searchsource);
    })

    $('.mt-table .mt-icon-trash').mouseover(function(){
        $(this).parents('tr:first').addClass('mt-red');
    });
    $('.mt-table .mt-icon-trash').mouseout(function(){
        $(this).parents('tr:first').removeClass('mt-red');
    });

    function mt_reset_fieldsets(searchsource) {
        searchsource.find("fieldset").each(function(){
            var visible = false;
            $(this).find('.mt-form-item').each(function(){
                visible = visible || !($(this).hasClass('mt-not-matched-search') || $(this).hasClass('mt-advanced') && !$(this).parents('.mt-tab-pane:first').find('.mt-show-hide-advanced:first').prop('checked') );
            })
            if (visible) {
                $(this).show();
            } else {
                $(this).fadeOut(0);
            }
        });
    }

    $('div.mt-buttons').each(function() {
        var topPosition = $(this).offset().top;
        $(this).affix({
            offset: {
                top: function () {
                    return (this.top = topPosition)
                }
            }
        });
    });

    function doConfirmDialogBox(message, yesFunction, noFunction, swapButtons, yesCaption, noCaption) {
        message = typeof(message) === 'string' ?  message : 'Are you sure?';
        var emptyFunction = function() {};
        yesFunction = typeof(yesFunction) === 'function' ?  yesFunction : emptyFunction;
        noFunction = typeof(noFunction) === 'function' ?  noFunction : emptyFunction;
        swapButtons = typeof(swapButtons) === 'undefined' ?  false : swapButtons;
        yesCaption = typeof(yesCaption) === 'string' ?  yesCaption : 'Yes';
        noCaption = typeof(noCaption) === 'string' ?  noCaption : 'No';
        var confirmBox = $('<div id="mt-confirm-box-overlay">'+
                            '<div id="mt-confirm-box">'+
                            '<div class="mt-confirm-box-message"></div>'+
                            '<input type="button" class="mt-button mt-border-r-4px mt-confirm-box-button mt-confirm-box-button-yes" value="Yes"/>'+
                            '<input type="button" class="mt-button mt-border-r-4px mt-confirm-box-button mt-confirm-box-button-no" value="No"/>'+
                            '</div></div>');
        if (swapButtons) {
            confirmBox.find('.mt-confirm-box-button-yes').insertAfter(confirmBox.find('.mt-confirm-box-button-no'));
        }
        confirmBox.find('.mt-confirm-box-message').text(message);
        confirmBox.find('.mt-confirm-box-button').unbind().click(function () {
            confirmBox.hide().remove();
        });
        confirmBox.find('.mt-confirm-box-button-yes').click(yesFunction).val(yesCaption);
        confirmBox.find('.mt-confirm-box-button-no').click(noFunction).val(noCaption);
        confirmBox.appendTo('body');
        confirmBox.show();
    }

    $('input.mt-button').click(function(e){
        e.preventDefault();
        var action = $(this).attr('data-submit-action');
        if (action == 'reset') {
            doConfirmDialogBox(
                'Are you sure? If you continue, you will lose any custom settings you have chosen on this page.',
                function() {
                    $('#magiczoomplus-submit-action').val(action);
                    $('#magictoolbox-settings-form').submit();
                },
                function() {},
                true,
                'Reset to defaults',
                'Cancel'
            );
            return false;
        }
        $('#magiczoomplus-submit-action').val(action);
        $('#magictoolbox-settings-form').submit();
    });

    mt_reset_fieldsets($('.mt-tab-pane:not([data-skip-showhide])'));

    $('a.mt-switch-option-link').click(function(e){
        var name = $(this).attr('data-name');
        var generalName = $(this).attr('data-general-name');

        if ($(this).hasClass('option-disabled')) {
            $('#magictoolbox-settings-form [name=\''+name+'\']').removeAttr('disabled');
            $(this).html('use default option').removeClass('option-disabled');
        } else {
            var elements = $('#magictoolbox-settings-form').find('select[name=\''+name+'\'], input[type=\'text\'][name=\''+name+'\']');
            if (elements.length) {
                var value = $('#magictoolbox-settings-form [name=\''+generalName+'\']').val();
                elements.val(value).attr('disabled', true);
            } else {
                elements = elements.end().find('input[type=\'radio\'][name=\''+name+'\']');
                var value = $('#magictoolbox-settings-form [name=\''+generalName+'\']:checked').val();
                elements.val([value]).attr('disabled', true);
            }
            $(this).html('edit').addClass('option-disabled');
        }
        return false;
    });

    $('#mt-tab-0').find('select, input[type=\'text\']').bind('change', function(){
        var value = $(this).val();
        var id = this.id.replace(magictoolboxProfiles[0]+'-', '');
        for (mtProfileIndex in magictoolboxProfiles) {
            if (mtProfileIndex == 0) {
                continue;
            }
            $('#'+magictoolboxProfiles[mtProfileIndex]+'-'+id+':disabled').val(value);
        }
    }).end().find('input[type=\'radio\']').bind('change', function(){
        var value = $(this).val();
        var name = '';
        for (mtProfileIndex in magictoolboxProfiles) {
            if (mtProfileIndex == 0) {
                continue;
            }
            name = this.name.replace(magictoolboxProfiles[0], magictoolboxProfiles[mtProfileIndex]);
            $('input[name=\''+name+'\']:disabled').val([value]);
        }
    });

});
