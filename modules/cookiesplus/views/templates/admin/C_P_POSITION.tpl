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
<div id="C_P_POSITION" class="img-radio">
    <div class="row">
        <div class="col-xs-3">
            <label class="position-img">
                <input type="radio" name="C_P_POSITION" value="top" {if isset($C_P_POSITION) && $C_P_POSITION == 'top'}checked=""{/if}>
                <img src="../modules/cookiesplus/views/img/positions/top.png">
            </label>
            <label class="position-label">{l s='Top' mod='cookiesplus'}</label>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-3">
            <label class="position-img">
                <input type="radio" name="C_P_POSITION" value="center" {if isset($C_P_POSITION) && $C_P_POSITION == 'center'}checked=""{/if}>
                <img src="../modules/cookiesplus/views/img/positions/center.png">
            </label>
            <label class="position-label">{l s='Center' mod='cookiesplus'}</label>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-3">
            <label class="position-img">
                <input type="radio" name="C_P_POSITION" value="bottom-left" {if isset($C_P_POSITION) && $C_P_POSITION == 'bottom-left'}checked=""{/if}>
                <img src="../modules/cookiesplus/views/img/positions/bottomleft.png">
            </label>
            <label class="position-label">{l s='Bottom left' mod='cookiesplus'}</label>
        </div>
        <div class="col-xs-3">
            <label class="position-img">
                <input type="radio" name="C_P_POSITION" value="bottom" {if isset($C_P_POSITION) && $C_P_POSITION == 'bottom'}checked=""{/if}>
                <img src="../modules/cookiesplus/views/img/positions/bottomcenter.png">
            </label>
            <label class="position-label">{l s='Bottom' mod='cookiesplus'}</label>
        </div>
        <div class="col-xs-3">
            <label class="position-img">
                <input type="radio" name="C_P_POSITION" value="bottom-right" {if isset($C_P_POSITION) && $C_P_POSITION == 'bottom-right'}checked=""{/if}>
                <img src="../modules/cookiesplus/views/img/positions/bottomright.png">
            </label>
            <label class="position-label">{l s='Bottom right' mod='cookiesplus'}</label>
        </div>
    </div>
</div>
