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
<div class="panel col-lg-12 info clearfix">
    <div class="panel-heading">
        {l s='Revoke consent' mod='cookiesplus'}
    </div>
    <div>
        {l s='If you modify the cookie configuration you need to ask the customer\'s consent again. When you press this button, the customers will be asked to give cookie consent again.' mod='cookiesplus'}
        <div class="text-center">
            <form method="POST" action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}">
                <button class="btn btn-default row-margin-top" name="submitCookiesPlusRevokeCookies">
                    <i class="icon-refresh"></i>
                    {l s='Revoke consent' mod='cookiesplus'}
                </button>
            </form>
            <br />
            <p>{l s='Cookie declaration last updated on:' mod='cookiesplus'} {$revokeConsentDate|escape:'htmlall':'UTF-8'}</p>
        </div>
    </div>
</div>
<div class="clearfix"></div>
