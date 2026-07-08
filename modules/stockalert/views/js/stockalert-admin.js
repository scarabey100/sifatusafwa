/*
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
$(document).ready(function () {
    // Display/hide values when switch changes
    $('[name=send_mail], [name=send_mail_admin], [name^=switch_]').change(function () {
        toggleFilters();
    });

    //Apply CodeMirror in CSS field
    $('.codemirror.codemirror-css').each(function () {
        CodeMirror.fromTextArea($(this)[0], {
            ode: "css",
            theme: "monokai",
            autoRefresh: true,
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            'CodeMirror-lines': 10
        });
    });

    //Apply CodeMirror in CSS field
    $('.codemirror.codemirror-js').each(function () {
        CodeMirror.fromTextArea($(this)[0], {
            ode: "js",
            theme: "monokai",
            autoRefresh: true,
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            'CodeMirror-lines': 10
        });
    });
});

$(window).load(function () {
    $('[name^=switch_]:checked').each(function () {
        elements = $('.' + $(this).attr('name') + '.selectedSwap, #' + $(this).attr('name') + '.selectedSwap, ul#' + $(this).attr('name')).not('div');
        that = this;
        elements.each(function(index, element) {
            if ($(element).is('input')) {
                if ($(element).filter(function () {
                    return $(that).val();
                }).length > 0) {
                    $('#' + $(that).attr('name') + '_on').attr('checked', true);
                };
            } else if ($(element).is('select')) {
                if ($(element).find('option').length > 0) {
                    $('#' + $(that).attr('name') + '_on').attr('checked', true);
                }
            } else if ($(element).is('ul')) {
                //Category tree
                if ($(element).find(":input:checked").length > 0) {
                    $('#' + $(that).attr('name') + '_on').attr('checked', true);
                }
            }
        });
    });
    toggleFilters();
});


function toggleFilters() {
    $('[name=send_mail]:checked').each(function () {
        if ($(this).val() === "0") {
            $(this).closest('.form-group').next().hide();
        } else {
            $(this).closest('.form-group').next().show();
        }
    });

    $('[name=send_mail_admin]:checked').each(function () {
        if ($(this).val() === "0") {
            $(this).closest('.form-group').next().hide();
        } else {
            $(this).closest('.form-group').next().show();
        }
    });

    $('[name^=switch_]:checked').each(function () {
        let selector = '.' + $(this).attr('name') + ', #' + $(this).attr('name') + ', #' + $(this).attr('name') + '_minimum, #' + $(this).attr('name') + '_maximum, ul#' + $(this).attr('name') + ', ul#' + $(this).attr('name') + '_excluded';
        if ($(this).val() === "0") {
            $(selector).not('div').closest('.form-wrapper > .form-group').hide();
            $(selector).not('div').closest('.panel > .form-group').hide();
            $(selector).not('div').closest('.margin-group').hide();
            $(selector).not('div').closest('.swap-container-custom').hide();
            $(selector).not('div').closest('.margin-form').hide().prev().hide();
        } else {
            $(selector).not('div').closest('.form-wrapper > .form-group').show();
            $(selector).not('div').closest('.panel > .form-group').show();
            $(selector).not('div').closest('.margin-group').show();
            $(selector).not('div').closest('.swap-container-custom').show();
            $(selector).not('div').closest('.margin-form').show().prev().show();
        }
    });
}
