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
<div class="cookiesplus-finalities-cms">
    <div id="cookiesplus-finalities-container-cms">
        {foreach from=$C_P_FINALITIES item=finality}
            <div class="cookieplus-finality-container">
                <div class="cookiesplus-finality-header">
                    <h5 class="cookiesplus-finality-title-cms" tabindex="0">
                        <strong><span>{$finality['name'] nofilter}</span></strong>
                    </h5>
                </div>
                <div class="clearfix"></div>
                <div class="cookiesplus-finality-content-cms">
                    <div class="cookiesplus-finality-body-cms">
                        <p>{$finality['description'] nofilter}</p>
                        {if $finality['cookies']|count > 0}
                            <table class="cookies-detail-list-cms table table-bordered table-striped table-labeled">
                                <thead class="thead-default">
                                    <tr>
                                        <th>{l s='Cookie name' mod='cookiesplus'}</th>
                                        <th>{l s='Provider' mod='cookiesplus'}</th>
                                        <th>{l s='Purpose' mod='cookiesplus'}</th>
                                        <th>{l s='Expiry' mod='cookiesplus'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$finality['cookies'] item=finalityCookie}
                                        <tr>
                                            <td data-label="{l s='Cookie name' mod='cookiesplus'}">
                                                <span>{$finalityCookie['name']|escape:'htmlall':'UTF-8'}</span>
                                            </td>
                                            <td data-label="{l s='Provider' mod='cookiesplus'}">
                                                <span>
                                                    {if isset($finalityCookie['provider_url']) && $finalityCookie['provider_url']}
                                                        <a
                                                            target="_blank"
                                                            rel="nofollow noopener noreferrer"
                                                            href="{$finalityCookie['provider_url']|escape:'htmlall':'UTF-8'}">
                                                    {/if}
                                                    {if isset($finalityCookie['provider']) && $finalityCookie['provider']}
                                                        {$finalityCookie['provider']|escape:'htmlall':'UTF-8'}
                                                    {/if}
                                                    {if isset($finalityCookie['provider_url']) && $finalityCookie['provider_url']}
                                                        </a>
                                                    {/if}
                                                </span>
                                            </td>
                                            <td data-label="{l s='Purpose' mod='cookiesplus'}">
                                                {if isset($finalityCookie['purpose']) && $finalityCookie['purpose']}
                                                    <span>{$finalityCookie['purpose']|escape:'htmlall':'UTF-8'}</span>
                                                {/if}
                                            </td>
                                            <td data-label="{l s='Expiry' mod='cookiesplus'}">
                                                {if isset($finalityCookie['expiry']) && $finalityCookie['expiry']}
                                                    <span>{$finalityCookie['expiry']|escape:'htmlall':'UTF-8'}</span>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        {/foreach}
    </div>
</div>
