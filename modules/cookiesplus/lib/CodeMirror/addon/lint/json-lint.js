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

// Depends on jsonlint.js from https://github.com/zaach/jsonlint

// declare global: jsonlint

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
"use strict";

CodeMirror.registerHelper("lint", "json", function(text) {
  var found = [];
  if (!window.jsonlint) {
    if (window.console) {
      window.console.error("Error: window.jsonlint not defined, CodeMirror JSON linting cannot run.");
    }
    return found;
  }
  // for jsonlint's web dist jsonlint is exported as an object with a single property parser, of which parseError
  // is a subproperty
  var jsonlint = window.jsonlint.parser || window.jsonlint
  jsonlint.parseError = function(str, hash) {
    var loc = hash.loc;
    found.push({from: CodeMirror.Pos(loc.first_line - 1, loc.first_column),
                to: CodeMirror.Pos(loc.last_line - 1, loc.last_column),
                message: str});
  };
  try { jsonlint.parse(text); }
  catch(e) {}
  return found;
});

});
