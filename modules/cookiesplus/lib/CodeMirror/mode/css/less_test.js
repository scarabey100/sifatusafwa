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

(function() {
  "use strict";

  var mode = CodeMirror.getMode({indentUnit: 2}, "text/x-less");
  function MT(name) { test.mode(name, mode, Array.prototype.slice.call(arguments, 1), "less"); }

  MT("variable",
     "[variable-2 @base]: [atom #f04615];",
     "[qualifier .class] {",
     "  [property width]: [variable&callee percentage]([number 0.5]); [comment // returns `50%`]",
     "  [property color]: [variable&callee saturate]([variable-2 @base], [number 5%]);",
     "}");

  MT("amp",
     "[qualifier .child], [qualifier .sibling] {",
     "  [qualifier .parent] [atom &] {",
     "    [property color]: [keyword black];",
     "  }",
     "  [atom &] + [atom &] {",
     "    [property color]: [keyword red];",
     "  }",
     "}");

  MT("mixin",
     "[qualifier .mixin] ([variable dark]; [variable-2 @color]) {",
     "  [property color]: [variable&callee darken]([variable-2 @color], [number 10%]);",
     "}",
     "[qualifier .mixin] ([variable light]; [variable-2 @color]) {",
     "  [property color]: [variable&callee lighten]([variable-2 @color], [number 10%]);",
     "}",
     "[qualifier .mixin] ([variable-2 @_]; [variable-2 @color]) {",
     "  [property display]: [atom block];",
     "}",
     "[variable-2 @switch]: [variable light];",
     "[qualifier .class] {",
     "  [qualifier .mixin]([variable-2 @switch]; [atom #888]);",
     "}");

  MT("nest",
     "[qualifier .one] {",
     "  [def @media] ([property width]: [number 400px]) {",
     "    [property font-size]: [number 1.2em];",
     "    [def @media] [attribute print] [keyword and] [property color] {",
     "      [property color]: [keyword blue];",
     "    }",
     "  }",
     "}");


  MT("interpolation", ".@{[variable foo]} { [property font-weight]: [atom bold]; }");
})();
