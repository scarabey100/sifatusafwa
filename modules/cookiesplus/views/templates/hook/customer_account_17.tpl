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
{if $C_P_MATERIAL_ICONS_LIBRARY == '1'}
    <a href="" class="col-lg-4 col-md-6 col-sm-6 col-xs-12" onclick="cookieGdpr.displayModalAdvanced(false); return false;"
       style="cursor:pointer" title="{l s='Your cookie settings' mod='cookiesplus'}">
        <span class="link-item">
            <i class="material-icons">info</i> {l s='Your cookie settings' mod='cookiesplus'}
        </span>
    </a>
{elseif $C_P_MATERIAL_ICONS_LIBRARY == '2'}
    <div class="list-group-item">
        <a href="" onclick="cookieGdpr.displayModalAdvanced(false); return false;" style="cursor:pointer" title="{l s='Your cookie settings' mod='cookiesplus'}">
            <i class="fto-chart-pie mar_r4 fs_lg"></i></i> {l s='Your cookie settings' mod='cookiesplus'}
        </a>
    </div>
{else}
    <a href="" class="col-lg-4 col-md-6 col-sm-6 col-xs-12" onclick="cookieGdpr.displayModalAdvanced(false); return false;" style="cursor:pointer" title="{l s='Your cookie settings' mod='cookiesplus'}">
        <span class="link-item">
            <i class="fa fa-star fa-cookie fa-fw" aria-hidden="true"></i> {l s='Your cookie settings' mod='cookiesplus'}
        </span>
    </a>
{/if}
