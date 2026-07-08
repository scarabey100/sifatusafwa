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

function lineIndent(cm, lineNo) {
  var text = cm.getLine(lineNo)
  var spaceTo = text.search(/\S/)
  if (spaceTo == -1 || /\bcomment\b/.test(cm.getTokenTypeAt(CodeMirror.Pos(lineNo, spaceTo + 1))))
    return -1
  return CodeMirror.countColumn(text, null, cm.getOption("tabSize"))
}

CodeMirror.registerHelper("fold", "indent", function(cm, start) {
  var myIndent = lineIndent(cm, start.line)
  if (myIndent < 0) return
  var lastLineInFold = null

  // Go through lines until we find a line that definitely doesn't belong in
  // the block we're folding, or to the end.
  for (var i = start.line + 1, end = cm.lastLine(); i <= end; ++i) {
    var indent = lineIndent(cm, i)
    if (indent == -1) {
    } else if (indent > myIndent) {
      // Lines with a greater indent are considered part of the block.
      lastLineInFold = i;
    } else {
      // If this line has non-space, non-comment content, and is
      // indented less or equal to the start line, it is the start of
      // another block.
      break;
    }
  }
  if (lastLineInFold) return {
    from: CodeMirror.Pos(start.line, cm.getLine(start.line).length),
    to: CodeMirror.Pos(lastLineInFold, cm.getLine(lastLineInFold).length)
  };
});

});
