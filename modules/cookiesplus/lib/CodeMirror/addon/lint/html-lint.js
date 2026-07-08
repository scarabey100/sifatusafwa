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

// Depends on htmlhint.js from http://htmlhint.com/js/htmlhint.js

// declare global: HTMLHint

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"), require("htmlhint"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror", "htmlhint"], mod);
  else // Plain browser env
    mod(CodeMirror, window.HTMLHint);
})(function(CodeMirror, HTMLHint) {
  "use strict";

  var defaultRules = {
    "tagname-lowercase": true,
    "attr-lowercase": true,
    "attr-value-double-quotes": true,
    "doctype-first": false,
    "tag-pair": true,
    "spec-char-escape": true,
    "id-unique": true,
    "src-not-empty": true,
    "attr-no-duplication": true
  };

  CodeMirror.registerHelper("lint", "html", function(text, options) {
    var found = [];
    if (HTMLHint && !HTMLHint.verify) {
      if(typeof HTMLHint.default !== 'undefined') {
        HTMLHint = HTMLHint.default;
      } else {
        HTMLHint = HTMLHint.HTMLHint;
      }
    }
    if (!HTMLHint) HTMLHint = window.HTMLHint;
    if (!HTMLHint) {
      if (window.console) {
          window.console.error("Error: HTMLHint not found, not defined on window, or not available through define/require, CodeMirror HTML linting cannot run.");
      }
      return found;
    }
    var messages = HTMLHint.verify(text, options && options.rules || defaultRules);
    for (var i = 0; i < messages.length; i++) {
      var message = messages[i];
      var startLine = message.line - 1, endLine = message.line - 1, startCol = message.col - 1, endCol = message.col;
      found.push({
        from: CodeMirror.Pos(startLine, startCol),
        to: CodeMirror.Pos(endLine, endCol),
        message: message.message,
        severity : message.type
      });
    }
    return found;
  });
});
