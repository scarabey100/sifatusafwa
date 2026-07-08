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
    toggleFields();
    $('select[name="C_P_MODE"], input[name="C_P_OVERLAY"], input[name="C_P_DISPLAY_TITLE"], input[name="technical"], input[name="C_P_GTM_ENABLE"], input[name="C_P_MUET_ENABLE"], input[name="C_P_FB_ENABLE"], input[name="C_P_YT_ENABLE"], input[name="C_P_YT_ENABLE_FORCE"], input[name="C_P_DISPLAY_ENCOURAGEMENT"]').change(function () {
        toggleFields();
    });
});

function toggleFields() {
    /*if ($('select[name="C_P_MODE"] option:selected').val() === '2') {
        $('#C_P_CLASSIC_ACCEPT').closest('.form-group').show();
        $('[name="C_P_MORE_INFO_DISPLAY"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_MORE_INFO_BACKGROUND_COLOR"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_MORE_INFO_FONT_COLOR"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_REJECT_DISPLAY"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_REJECT_BACKGROUND_COLOR"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_REJECT_FONT_COLOR"]').closest('.form-wrapper > .form-group').hide();
        $("#module-tabs a[href='#fieldset_3_3']").hide();
        $("#module-tabs a[href='#fieldset_4_4']").hide();
        $("#module-tabs a[href='#fieldset_5_5']").hide();
    } else {
        $('#C_P_CLASSIC_ACCEPT').closest('.form-group').hide();
        $('[name="C_P_MORE_INFO_DISPLAY"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_MORE_INFO_BACKGROUND_COLOR"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_MORE_INFO_FONT_COLOR"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_REJECT_DISPLAY"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_REJECT_BACKGROUND_COLOR"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_REJECT_FONT_COLOR"]').closest('.form-wrapper > .form-group').show();
        $("#module-tabs a[href='#fieldset_3_3']").show();
        $("#module-tabs a[href='#fieldset_4_4']").show();
        $("#module-tabs a[href='#fieldset_5_5']").show();
    }*/
    // Overlay
    if ($('input[name="C_P_OVERLAY"]:checked').val() === '1') {
        $('[name="C_P_OVERLAY_OPACITY"]').closest('.form-wrapper > .form-group').show();
        $('[name="C_P_OVERLAY_OPACITY"]').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('[id="C_P_OVERLAY_MSG"]').closest('.form-wrapper > .form-group').show();
        $('[id="C_P_OVERLAY_MSG"]').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('[name="C_P_OVERLAY_OPACITY"]').closest('.form-wrapper > .form-group').hide();
        $('[name="C_P_OVERLAY_OPACITY"]').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('[id="C_P_OVERLAY_MSG"]').closest('.form-wrapper > .form-group').hide();
        $('[id="C_P_OVERLAY_MSG"]').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }

    // Encouragement text
    if ($('input[name="C_P_DISPLAY_ENCOURAGEMENT"]:checked').val() === '1') {
        $('[name^="C_P_TEXT_ENCOURAGEMENT"]').closest('.form-wrapper > .form-group').show();
        $('[name^="C_P_TEXT_ENCOURAGEMENT"]').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('[name^="C_P_TEXT_ENCOURAGEMENT"]').closest('.form-wrapper > .form-group').hide();
        $('[name^="C_P_TEXT_ENCOURAGEMENT"]').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }

    // Title
    let defaultLang;
    if (typeof default_language !== 'undefined') {
        defaultLang = default_language;
    } else if (typeof defaultLanguage !== 'undefined') {
        defaultLang = defaultLanguage['id_lang'];
    } else {
        defaultLang = id_language;
    }

    /*if ($('input[name="C_P_DISPLAY_TITLE"]:checked').val() === '1') {
        $('[name="C_P_TITLE_'+ defaultLang +'"]').closest('.form-wrapper > .form-group').show();
    } else {
        $('[name="C_P_TITLE_'+ defaultLang +'"]').closest('.form-wrapper > .form-group').hide();
    }*/

    // Modules with technical cookies
    if ($('input[name="technical"]:checked').val() === '1') {
        $('#cookiesplus-module-list').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-module-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('#js_script').closest('.form-wrapper > .form-group').hide();
        $('#js_script').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('#js_not_script').closest('.form-wrapper > .form-group').hide();
        $('#js_not_script').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('#cookiesplus-script-block').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-script-block').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    } else {
        $('#cookiesplus-module-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-module-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('#js_script').closest('.form-wrapper > .form-group').show();
        $('#js_script').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('#js_not_script').closest('.form-wrapper > .form-group').show();
        $('#js_not_script').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('#cookiesplus-script-block').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-script-block').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    }

    // GTM fire
    if ($('input[name="C_P_GTM_ENABLE"]:checked').val() === '1') {
        $('#cookiesplus-gtm-fire-list').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-gtm-fire-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('#cookiesplus-gtm-template').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-gtm-template').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('#cookiesplus-gtm-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-gtm-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('input[name="C_P_GTM_URL_PASSTHROUGH"]').closest('.form-wrapper > .form-group').show();
        $('input[name="C_P_GTM_URL_PASSTHROUGH"]').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('input[name="C_P_GTM_ADS_DATA_REDACTION"]').closest('.form-wrapper > .form-group').show();
        $('input[name="C_P_GTM_ADS_DATA_REDACTION"]').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('#cookiesplus-gtm-fire-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-gtm-fire-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
        $('#cookiesplus-gtm-template').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-gtm-template').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('#cookiesplus-gtm-list').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-gtm-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('input[name="C_P_GTM_URL_PASSTHROUGH"]').closest('.form-wrapper > .form-group').hide();
        $('input[name="C_P_GTM_URL_PASSTHROUGH"]').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
        $('input[name="C_P_GTM_ADS_DATA_REDACTION"]').closest('.form-wrapper > .form-group').hide();
        $('input[name="C_P_GTM_ADS_DATA_REDACTION"]').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }

    // MUET
    if ($('input[name="C_P_MUET_ENABLE"]:checked').val() === '1') {
        $('#cookiesplus-muet-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-muet-list').closest('.form-wrapper > .form-group').show().prev().show();
        $('#cookiesplus-muet-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('#cookiesplus-muet-list').closest('.form-wrapper > .form-group').hide().prev().hide();
        $('#cookiesplus-muet-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }

    // FB
    if ($('input[name="C_P_FB_ENABLE"]:checked').val() === '1') {
        $('#cookiesplus-fb-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-fb-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('#cookiesplus-fb-list').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-fb-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }

    // YT
    if ($('input[name="C_P_YT_ENABLE"]:checked').val() === '1'
        || $('input[name="C_P_YT_ENABLE_FORCE"]:checked').val() === '1') {
        $('#cookiesplus-yt-list').closest('.form-wrapper > .form-group').show();
        $('#cookiesplus-yt-list').closest('fieldset > .margin-form').show().prev('label').show(); // PS 1.5
    } else {
        $('#cookiesplus-yt-list').closest('.form-wrapper > .form-group').hide();
        $('#cookiesplus-yt-list').closest('fieldset > .margin-form').hide().prev('label').hide(); // PS 1.5
    }
}

$(document).ready(function () {
    if (typeof (tinySetup) != "undefined") {
        tinySetup({
            editor_selector: "cp_tiny",
            valid_children: "+body[style|script|iframe|section|link],pre[iframe|section|script|div|p|br|span|img|style|h1|h2|h3|h4|h5],*[*]",
            forced_root_block: '',
            // formats: {'marginTop': {selector: 'p', styles: {'margin-top': '%value'}}},
            image_advtab: true,
            plugins: 'print preview code searchreplace directionality autolink visualblocks visualchars fullscreen image link table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists textcolor wordcount media contextmenu colorpicker',
            external_plugins: {
                "filemanager": ad + "/filemanager/plugin.min.js"
            },
            menubar: {
                file: {title: 'File', items: 'newdocument'},
                edit: {title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall'},
                insert: {title: 'Insert', items: 'link media | button'},
                format: {title: 'Format', items: 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
                table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'},
                tools: {title: 'Tools', items: 'code'}
            },
            toolbar1: 'code, bold, italic, underline, strikethrough, colorpicker, alignleft, aligncenter, alignright, alignjustify, casechange, permanentpen, formatpainter, removeformat, fontselect, fontsizeselect, bullist, numlist, checklist, outdent, indent, custommargin',
            /*setup: function(editor) {
                editor.addButton('custommargin', {
                    text: 'Margin',
                    icon: false,
                    onclick: function() {
                        editor.windowManager.open({
                            title: 'Margin (in px)',
                            body: [
                                {type: 'textbox', name: 'margin-top', label: 'margin-top'},
                                {type: 'textbox', name: 'margin-right', label: 'margin-right'},
                                {type: 'textbox', name: 'margin-bottom', label: 'margin-bottom'},
                                {type: 'textbox', name: 'margin-left', label: 'margin-left'}
                            ],
                            onsubmit: function(e) {
                                tinymce.activeEditor.focus();
                                tinymce.activeEditor.formatter.toggle('marginTop', {value : e.data['margin-top'] + 'px'});
                                // editor.execCommand('mceToggleFormat', false, 'marginTop', e.data['margin-top'] + 'px');
                                //
                                // editor.selection.setContent('<pre class="language-' + e.data.language + ' line-numbers"><code>' + ed.selection.getContent() + '</code></pre>');
                            }
                        });
                    }
                })
            }*/
        })
    }

    // Apply CodeMirror in JS field
    $('.codemirror.codemirror-js').each(function () {
        CodeMirror.fromTextArea($(this)[0], {
            mode: "javascript",
            theme: "monokai",
            autoRefresh: true,
            lineNumbers: true,
            'CodeMirror-lines': 10
        });
    });

    // Apply CodeMirror in CSS field
    $('.codemirror.codemirror-css').each(function () {
        CodeMirror.fromTextArea($(this)[0], {
            mode: "css",
            theme: "monokai",
            autoRefresh: true,
            lineNumbers: true,
            'CodeMirror-lines': 10
        });
    });
});
