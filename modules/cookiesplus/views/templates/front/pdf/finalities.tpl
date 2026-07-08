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
<h2>{l s='Cookie finalities' mod='cookiesplus'}</h2>

{foreach from=$finalities item=finality}
    <h3>{$finality['name'] nofilter}</h3>
    <br/>
    {l s='Enabled' mod='cookiesplus'}:
    <strong>
        {if $finality['technical']}
            <span class="finality-enabled"> {l s='Always enabled' mod='cookiesplus'} </span>
        {else}
            {if !isset($finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"])
                || (isset($finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"])
                    && ($finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"] == 'off'
                        || $finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"] == false))}
                <span class="finality-disabled"> {l s='No' mod='cookiesplus'} </span>
            {/if}
            {if isset($finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"])
            && $finality["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"] == 'on'}
                <span class="finality-enabled"> {l s='Yes' mod='cookiesplus'} </span>
            {/if}
        {/if}
    </strong>
    <br/>
    <br/>
    {$finality['description'] nofilter}
    <br/>
    <br/>
    {if $finality['cookies']|count > 0}
        <table class="border" width="100%">
            <thead>
            <tr>
                <th width="20%" class="header" valign="middle">{l s='Cookie name' mod='cookiesplus'}</th>
                <th width="20%" class="header" valign="middle">{l s='Provider' mod='cookiesplus'}</th>
                <th width="40%" class="header" valign="middle">{l s='Purpose' mod='cookiesplus'}</th>
                <th width="20%" class="header" valign="middle">{l s='Expiry' mod='cookiesplus'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$finality['cookies'] item=finalityCookie}
                <tr>
                    <td width="20%"
                        label="{l s='Cookie name' mod='cookiesplus'}">{$finalityCookie['name']|escape:'htmlall':'UTF-8'}</td>
                    <td width="20%"
                        label="{l s='Provider' mod='cookiesplus'}">{if $finalityCookie['provider_url']}{$finalityCookie['provider']|escape:'htmlall':'UTF-8'}{/if}</td>
                    <td width="40%"
                        label="{l s='Purpose' mod='cookiesplus'}">{$finalityCookie['purpose']|escape:'htmlall':'UTF-8'}</td>
                    <td width="20%"
                        label="{l s='Expiry' mod='cookiesplus'}">{$finalityCookie['expiry']|escape:'htmlall':'UTF-8'}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <br/>
        <br/>
    {/if}
{/foreach}
