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
import buble from '@rollup/plugin-buble';

export default [
  {
    input: "src/codemirror.js",
    output: {
      banner: `// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: https://codemirror.net/LICENSE

// This is CodeMirror (https://codemirror.net), a code editor
// implemented in JavaScript on top of the browser's DOM.
//
// You can find some technical background for some of the code below
// at http://marijnhaverbeke.nl/blog/#cm-internals .
`,
      format: "umd",
      file: "lib/codemirror.js",
      name: "CodeMirror"
    },
    plugins: [ buble({namedFunctionExpressions: false}) ]
  },
  {
    input: ["src/addon/runmode/runmode-standalone.js"],
    output: {
      format: "iife",
      file: "addon/runmode/runmode-standalone.js",
      name: "CodeMirror",
      freeze: false, // IE8 doesn't support Object.freeze.
    },
    plugins: [ buble({namedFunctionExpressions: false}) ]
  },
  {
    input: ["src/addon/runmode/runmode.node.js"],
    output: {
      format: "cjs",
      file: "addon/runmode/runmode.node.js",
      name: "CodeMirror",
      freeze: false, // IE8 doesn't support Object.freeze.
    },
    plugins: [ buble({namedFunctionExpressions: false}) ]
  },
];
