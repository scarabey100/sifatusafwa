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
<div id="cookiesplus-script-block" class="alert alert-info">
    {l s='If you need to block scripts located in a template or in a script, you can use the following:' mod='cookiesplus'}
    <br /><br />
    <strong><span>Smarty/TPL</span></strong>
    <br />
    <em>
        <span>
            {literal}
                &emsp;{if ($C_P_COOKIE_VALUE === 'undefined' || (isset($C_P_COOKIE_VALUE['consents']) && isset($C_P_COOKIE_VALUE['consents']['cookiesplus-finality-<strong>X</strong>']) && $C_P_COOKIE_VALUE['consents']['cookiesplus-finality-<strong>X</strong>'] === 'on'))}
                <br />
                &emsp;&emsp;... code ...
                <br />
                &emsp;{/if}
            {/literal}
        </span>
    </em>
    <br /><br />
    <strong><span>Javascript</span></strong>
    <br />
    <em>
        <span>
            {literal}
                &emsp;if (typeof C_P_COOKIE_VALUE === 'undefined' || (typeof C_P_COOKIE_VALUE['consents'] !== 'undefined' && typeof C_P_COOKIE_VALUE['consents']['cookiesplus-finality-X'] !== 'undefined' && C_P_COOKIE_VALUE['consents']['cookiesplus-finality-<strong>X</strong>'] === 'on')) {
                <br />
                &emsp;&emsp;... code ...
                <br />
                &emsp;}
            {/literal}
        </span>
    </em>
    <br /><br />
    {l s='Where X is the cookie finality ID' mod='cookiesplus'}
</div>
