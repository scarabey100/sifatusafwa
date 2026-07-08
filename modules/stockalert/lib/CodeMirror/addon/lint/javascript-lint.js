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
(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
  "use strict";
  // declare global: JSHINT

  function validator(text, options) {
    if (!window.JSHINT) {
      if (window.console) {
        window.console.error("Error: window.JSHINT not defined, CodeMirror JavaScript linting cannot run.");
      }
      return [];
    }
    if (!options.indent) // JSHint error.character actually is a column index, this fixes underlining on lines using tabs for indentation
      options.indent = 1; // JSHint default value is 4
    JSHINT(text, options, options.globals);
    var errors = JSHINT.data().errors, result = [];
    if (errors) parseErrors(errors, result);
    return result;
  }

  CodeMirror.registerHelper("lint", "javascript", validator);

  function parseErrors(errors, output) {
    for ( var i = 0; i < errors.length; i++) {
      var error = errors[i];
      if (error) {
        if (error.line <= 0) {
          if (window.console) {
            window.console.warn("Cannot display JSHint error (invalid line " + error.line + ")", error);
          }
          continue;
        }

        var start = error.character - 1, end = start + 1;
        if (error.evidence) {
          var index = error.evidence.substring(start).search(/.\b/);
          if (index > -1) {
            end += index;
          }
        }

        // Convert to format expected by validation service
        var hint = {
          message: error.reason,
          severity: error.code ? (error.code.startsWith('W') ? "warning" : "error") : "error",
          from: CodeMirror.Pos(error.line - 1, start),
          to: CodeMirror.Pos(error.line - 1, end)
        };

        output.push(hint);
      }
    }
  }
});
