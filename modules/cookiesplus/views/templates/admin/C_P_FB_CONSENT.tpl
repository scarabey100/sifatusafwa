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
<div class="cookiesplus-module-list-container">
    <table id="cookiesplus-fb-list" class="table">
        <thead>
            <tr class="column-headers">
                <th></th>
                <th>{l s='Cookie finality' mod='cookiesplus'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$cookiesPlusFinalities item=cookiesPlusFinality}
                <tr>
                    <td>
                        <input type="checkbox" name="{$fieldNameFb|escape:'htmlall':'UTF-8'}[{$cookiesPlusFinality['id_cookiesplus_finality']|escape:'htmlall':'UTF-8'}]" value="true" {if isset($valuesFb[$cookiesPlusFinality['id_cookiesplus_finality']])}checked{/if}>
                    </td>
                    <td>
                        <span>{$cookiesPlusFinality['name']|escape:'quotes':'UTF-8'}</span>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
