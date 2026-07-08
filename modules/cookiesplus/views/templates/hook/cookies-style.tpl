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
<style>
    {if $C_P_BACKGROUND_COLOR}
        div#cookiesplus-modal,
        #cookiesplus-modal > div,
        #cookiesplus-modal p {
            background-color: {$C_P_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'} !important;
        }
    {/if}

    {if $C_P_FONT_COLOR}
        #cookiesplus-modal > div,
        #cookiesplus-modal p {
            color: {$C_P_FONT_COLOR|escape:'htmlall':'UTF-8'} !important;
        }
    {/if}

    {if $C_P_ACCEPT_BACKGROUND_COLOR}
        #cookiesplus-modal button.cookiesplus-accept,
        #cookiesplus-modal button.cookiesplus-accept-encourage {
            background-color: {$C_P_ACCEPT_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_ACCEPT_BORDER_COLOR}
        #cookiesplus-modal button.cookiesplus-accept,
        #cookiesplus-modal button.cookiesplus-accept-encourage {
            border: 1px solid {$C_P_ACCEPT_BORDER_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}


    {if $C_P_ACCEPT_FONT_COLOR}
        #cookiesplus-modal button.cookiesplus-accept,
        #cookiesplus-modal button.cookiesplus-accept-encourage {
            color: {$C_P_ACCEPT_FONT_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_ACCEPT_FONT_SIZE}
        #cookiesplus-modal button.cookiesplus-accept,
        #cookiesplus-modal button.cookiesplus-accept-encourage {
            font-size: {$C_P_ACCEPT_FONT_SIZE|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_ACCEPT_PADDING}
        #cookiesplus-modal button.cookiesplus-accept,
        #cookiesplus-modal button.cookiesplus-accept-encourage {
            padding: {$C_P_ACCEPT_FONT_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_MORE_INFO_BACKGROUND_COLOR}
        #cookiesplus-modal button.cookiesplus-more-information {
            background-color: {$C_P_MORE_INFO_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_MORE_INFO_BORDER_COLOR}
        #cookiesplus-modal button.cookiesplus-more-information {
            border: 1px solid {$C_P_MORE_INFO_BORDER_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_MORE_INFO_FONT_COLOR}
        #cookiesplus-modal button.cookiesplus-more-information {
            color: {$C_P_MORE_INFO_FONT_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_MORE_INFO_FONT_SIZE}
        #cookiesplus-modal button.cookiesplus-more-information {
            font-size: {$C_P_MORE_INFO_FONT_SIZE|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_MORE_INFO_PADDING}
        #cookiesplus-modal button.cookiesplus-more-information {
            padding: {$C_P_MORE_INFO_PADDING|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_REJECT_BACKGROUND_COLOR}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            background-color: {$C_P_REJECT_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_REJECT_BORDER_COLOR}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            border: 1px solid {$C_P_REJECT_BORDER_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_REJECT_FONT_COLOR}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            color: {$C_P_REJECT_FONT_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_REJECT_FONT_SIZE}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            font-size: {$C_P_REJECT_FONT_SIZE|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_REJECT_PADDING}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            padding: {$C_P_REJECT_PADDING|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_SAVE_BACKGROUND_COLOR}
        #cookiesplus-modal button.cookiesplus-save:not([disabled]) {
            background-color: {$C_P_SAVE_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_SAVE_BORDER_COLOR}
        #cookiesplus-modal button.cookiesplus-save:not([disabled]) {
            border: 1px solid {$C_P_SAVE_BORDER_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_SAVE_FONT_COLOR}
        #cookiesplus-modal button.cookiesplus-save:not([disabled]) {
            color: {$C_P_SAVE_FONT_COLOR|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_SAVE_FONT_SIZE}
        #cookiesplus-modal button.cookiesplus-save {
            font-size: {$C_P_SAVE_FONT_SIZE|escape:'htmlall':'UTF-8'};
        }
    {/if}

    {if $C_P_SAVE_PADDING}
        #cookiesplus-modal button.cookiesplus-reject,
        #cookiesplus-modal button.cookiesplus-reject-encourage {
            padding: {$C_P_SAVE_PADDING|escape:'htmlall':'UTF-8'};
        }
    {/if}

    #cookiesplus-tab {
        {if isset($C_P_TAB_POSITION)}
            {if $C_P_TAB_POSITION == 'center-left'}
                bottom: 55%;
                left: 0;
                -webkit-transform: rotate(-90deg) translateX(-100%);
                -moz-transform: rotate(-90deg) translateX(-100%);
                -ms-transform: rotate(-90deg) translateX(-100%);
                -o-transform: rotate(-90deg) translateX(-100%);
                transform: rotate(-90deg) translateX(-100%);
                transform-origin: 0 0;
            {elseif $C_P_TAB_POSITION == 'center-right'}
                bottom: 55%;
                right: 0;
                -webkit-transform: rotate(-90deg);
                -moz-transform: rotate(-90deg) translateX(-100%);
                -ms-transform: rotate(-90deg) translateX(-100%);
                -o-transform: rotate(-90deg) translateX(-100%);
                transform: rotate(-90deg);
                transform-origin: 100% 100%;
            {elseif $C_P_TAB_POSITION == 'bottom-left'}
                bottom: 0;
                left: 0;
            {elseif $C_P_TAB_POSITION == 'bottom-right'}
                bottom: 0;
                right: 0;
            {else}
                bottom: 0;
                left: 0;
            {/if}
        {else}
            bottom: 0;
            left: 0;
        {/if}

        {if $C_P_TAB_BACKGROUND_COLOR}
            background-color: {$C_P_TAB_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};
        {/if}

        {if $C_P_TAB_FONT_COLOR}
            color: {$C_P_TAB_FONT_COLOR|escape:'htmlall':'UTF-8'};
        {/if}
    }
</style>

{if isset($C_P_CSS) && $C_P_CSS}
    {$C_P_CSS nofilter}
{/if}
