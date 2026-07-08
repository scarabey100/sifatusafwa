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
/*jshint unused:false */
/*global tinymce:true */
tinymce.PluginManager.requireLangPack('codemirror');

tinymce.PluginManager.add('codemirror', function (editor, url) {
    function showSourceEditor() {
        editor.focus();
        editor.selection.collapse(true);

        // Insert caret marker
        if (editor.settings.codemirror.saveCursorPosition) {
            editor.selection.setContent('<span style="display: none;" class="CmCaReT">&#x0;</span>');
        }

        codemirrorWidth = 800;
        if (editor.settings.codemirror.width) {
            codemirrorWidth = editor.settings.codemirror.width;
        }

        codemirrorHeight = 550;
        if (editor.settings.codemirror.width) {
            codemirrorHeight = editor.settings.codemirror.height;
        }

        var config = {
            title: 'HTML source code',
            url: url + '/source.html',
            width: codemirrorWidth,
            height: codemirrorHeight,
            resizable: true,
            maximizable: true,
            fullScreen: editor.settings.codemirror.fullscreen,
            saveCursorPosition: false,
            search: false,
            buttons: [
                {
                    text: 'Ok', subtype: 'primary', onclick: function () {
                        var doc = document.querySelectorAll('.mce-container-body>iframe')[0];
                        doc.contentWindow.submit();
                        win.close();
                    }
                },
                {text: 'Cancel', onclick: 'close'}
            ]
        };

        var win = editor.windowManager.open(config);

        if (editor.settings.codemirror.fullscreen) {
            win.fullscreen(true);
        }
    };

    // Add a button to the button bar
    editor.addButton('code', {
        title: 'Source code',
        icon: 'code',
        onclick: showSourceEditor
    });

    // Add a menu item to the tools menu
    editor.addMenuItem('code', {
        icon: 'code',
        text: 'Source code',
        context: 'tools',
        onclick: showSourceEditor
    });
});
