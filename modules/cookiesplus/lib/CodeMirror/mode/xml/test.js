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
(function () {
    var mode = CodeMirror.getMode({indentUnit: 2}, "xml"), mname = "xml";

    function MT(name) {
        test.mode(name, mode, Array.prototype.slice.call(arguments, 1), mname);
    }

    MT("matching",
        "[tag&bracket <][tag top][tag&bracket >]",
        "  text",
        "  [tag&bracket <][tag inner][tag&bracket />]",
        "[tag&bracket </][tag top][tag&bracket >]");

    MT("nonmatching",
        "[tag&bracket <][tag top][tag&bracket >]",
        "  [tag&bracket <][tag inner][tag&bracket />]",
        "  [tag&bracket </][tag&error tip][tag&bracket&error >]");

    MT("doctype",
        "[meta <!doctype foobar>]",
        "[tag&bracket <][tag top][tag&bracket />]");

    MT("cdata",
        "[tag&bracket <][tag top][tag&bracket >]",
        "  [atom <![CDATA[foo]",
        "[atom barbazguh]]]]>]",
        "[tag&bracket </][tag top][tag&bracket >]");

    // HTML tests
    mode = CodeMirror.getMode({indentUnit: 2}, "text/html");

    MT("selfclose",
        "[tag&bracket <][tag html][tag&bracket >]",
        "  [tag&bracket <][tag link] [attribute rel]=[string stylesheet] [attribute href]=[string \"/foobar\"][tag&bracket >]",
        "[tag&bracket </][tag html][tag&bracket >]");

    MT("list",
        "[tag&bracket <][tag ol][tag&bracket >]",
        "  [tag&bracket <][tag li][tag&bracket >]one",
        "  [tag&bracket <][tag li][tag&bracket >]two",
        "[tag&bracket </][tag ol][tag&bracket >]");

    MT("valueless",
        "[tag&bracket <][tag input] [attribute type]=[string checkbox] [attribute checked][tag&bracket />]");

    MT("pThenArticle",
        "[tag&bracket <][tag p][tag&bracket >]",
        "  foo",
        "[tag&bracket <][tag article][tag&bracket >]bar");

})();
