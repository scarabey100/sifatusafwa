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

// Depends on js-yaml.js from https://github.com/nodeca/js-yaml

// declare global: jsyaml

CodeMirror.registerHelper("lint", "yaml", function(text) {
  var found = [];
  if (!window.jsyaml) {
    if (window.console) {
      window.console.error("Error: window.jsyaml not defined, CodeMirror YAML linting cannot run.");
    }
    return found;
  }
  try { jsyaml.loadAll(text); }
  catch(e) {
      var loc = e.mark,
          // js-yaml YAMLException doesn't always provide an accurate lineno
          // e.g., when there are multiple yaml docs
          // ---
          // ---
          // foo:bar
          from = loc ? CodeMirror.Pos(loc.line, loc.column) : CodeMirror.Pos(0, 0),
          to = from;
      found.push({ from: from, to: to, message: e.message });
  }
  return found;
});

});
