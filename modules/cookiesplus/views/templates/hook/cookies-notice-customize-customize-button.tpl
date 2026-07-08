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
<div class="col-xs-12 col-md-5">
    <button type="submit"
        {*disabled="disabled"*}
        name="saveCookiesPlusPreferences"
        onclick="return cookieGdpr.save();"
        class="cookiesplus-btn cookiesplus-save cookiesplus-accept-selected">
        {if $C_P_ICONS}
            {if $C_P_MATERIAL_ICONS_LIBRARY == '1'}
                <i class="material-icons">playlist_add_check</i>
            {elseif $C_P_MATERIAL_ICONS_LIBRARY == '2'}
                <i class="fto-ok-1 fs_xl"></i>
            {else}
                <i class="fa fa-check fa-fw" aria-hidden="true"></i>
            {/if}
        {/if}
        {l s='Accept only selected cookies' mod='cookiesplus'}
    </button>
</div>
