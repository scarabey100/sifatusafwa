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
<h2>{l s='Cookie consent information' mod='cookiesplus'}</h2>
<br >
{l s='Cookie information last updated on:' mod='cookiesplus'} {$info['last_update']|escape:'htmlall':'UTF-8'}
<br />
<br />
<table class="border" width="100%">
    <thead>
        <tr>
            <th class="header" valign="middle">{l s='Field' mod='cookiesplus'}</th>
            <th class="header" valign="middle">{l s='Value' mod='cookiesplus'}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {l s='Consent ID' mod='cookiesplus'}
            </td>
            <td>
                {$info['consent_hash']|escape:'htmlall':'UTF-8'}
            </td>
        </tr>
        <tr>
            <td>
                {l s='Consent date' mod='cookiesplus'}
            </td>
            <td>
                {$info['consent_date']|escape:'htmlall':'UTF-8'}
            </td>
        </tr>
        <tr>
            <td>
                {l s='Consent IP' mod='cookiesplus'}
            </td>
            <td>
                {$info['consent_ip']|escape:'htmlall':'UTF-8'}
            </td>
        </tr>
    </tbody>
</table>
