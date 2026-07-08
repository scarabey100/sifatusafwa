{**
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
 *}
{assign var=color_header value="#F0F0F0"}
{assign var=color_border value="#F0F0F0"}
{assign var=height_header value="20px"}
{assign var=table_padding value="6px"}
{assign var=font_size_text value="9pt"}
{assign var=font_size_header value="9pt"}
{assign var=height_header value="20px"}

<style>
    table, th, td {
        vertical-align: middle;
        white-space: nowrap;
        padding: {$table_padding nofilter};
        font-size: {$font_size_text nofilter};
    }

    table {
        width: 100%;
        border-collapse: collapse;
        padding: {$table_padding nofilter};
    }

    th.header {
        height: {$height_header nofilter};
        background-color: {$color_header nofilter};
        vertical-align: middle;
        text-align: center;
        font-weight: bold;
        font-size: {$font_size_header nofilter};
        height: {$height_header nofilter};
    }

    table.border,
    table.border th,
    table.border td {
        border: 1px solid {$color_border nofilter};
    }

    .finality-enabled {
        background-color: #4ED964;
        color: white;
    }

    .finality-disabled {
        background-color: #FF3A31;
        color: white;
    }
</style>
