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
<div id="cookiesplus-overlay" aria-modal="true" role="alertdialog" class="cookiesplus-move"></div>

<div class="container cookiesplus-move" id="cookiesplus-modal-container">
    <div class="row">
        <div id="cookiesplus-modal" style="display: none;"
             class="cookiesplus-{$C_P_POSITION|escape:'htmlall':'UTF-8'} {$C_P_CLASS|escape:'htmlall':'UTF-8'}">
            <button type="button" class="cookiesplus-close" onclick="return cookieGdpr.close();">&times;</button>
            <button type="button" style="{if !$C_P_DISPLAY_X}display: none;{/if}" class="cookiesplus-close-and-reject" onclick="return cookieGdpr.rejectAllCookies();">&times;</button>


            <div id="cookiesplus-content">
                <div class="row">
                    <form id="cookiesplus-form" method="POST" action="{$link->getModuleLink('cookiesplus', 'front', [], true)|escape:'htmlall':'UTF-8'}">
                        {if isset($C_P_TEXT_BASIC) && $C_P_TEXT_BASIC}
                            <div class="cookiesplus-info col-12 col-xs-12">
                                <div>{$C_P_TEXT_BASIC nofilter}</div>
                            </div>
                        {/if}

                        <div class="cookiesplus-finalities col-12 col-xs-12">
                            <div id="cookiesplus-finalities-container">
                                {foreach from=$C_P_FINALITIES item=finality}
                                    <div class="cookieplus-finality-container">
                                        <div class="col-12 col-xs-12">
                                            <div class="cookiesplus-finality-header">
                                                <div class="cookiesplus-finality-title"
                                                     onclick="$(this).parent().siblings('.cookiesplus-finality-content').slideToggle(); $(this).find('.cookiesplus-finality-chevron').toggleClass('bottom up');">
                                                    <span class="cookiesplus-finality-chevron bottom"></span>
                                                    <strong><span>{$finality['name'] nofilter}</span></strong></div>
                                                <div class="cookiesplus-finality-switch-container">
                                                    {if $finality['technical']}
                                                        <label class="technical">{l s='Always enabled' mod='cookiesplus'}</label>
                                                        <input class="cookiesplus-finality-checkbox not_uniform comparator"
                                                                id="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-on"
                                                                value="on"
                                                                name="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"
                                                                type="radio"
                                                                checked="checked"
                                                         >
                                                    {else}
                                                        <input class="cookiesplus-finality-checkbox not_uniform comparator"
                                                               id="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-off"
                                                               value="off"
                                                               name="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"
                                                               type="radio"
                                                               {if (!isset($C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"]) && $C_P_DEFAULT_CONSENT)
                                                                || (isset($C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"]) && $C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"] == 'off')}
                                                                    checked="checked"
                                                                {/if}
                                                        >
                                                        <input class="cookiesplus-finality-checkbox not_uniform comparator"
                                                               id="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-on"
                                                               value="on"
                                                               name="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"
                                                               type="radio"
                                                               {if (!isset($C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"]) && !$C_P_DEFAULT_CONSENT) || (isset($C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"]))
                                                                && $C_P_COOKIE_VALUE["cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}"] == 'on'}
                                                                    checked="checked"
                                                                {/if}

                                                        >
                                                        <label for="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-off">{l s='No' mod='cookiesplus'}</label>
                                                        <span
                                                                onclick="$('input[name=cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}]:checked').val() === 'on' ? $('label[for=cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-off]').click() : $('label[for=cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-on]').click(); return false;"
                                                               class="cookiesplus-finality-switch"
                                                               id="cookiesplus-finality-switch-{$finality['id_cookiesplus_finality']|intval}"></span>
                                                        <label for="cookiesplus-finality-{$finality['id_cookiesplus_finality']|intval}-on">{l s='Yes' mod='cookiesplus'}</label>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="cookiesplus-finality-content">
                                                <div class="cookiesplus-finality-body">
                                                    {$finality['description'] nofilter}
                                                    {if $finality['cookies']|count > 0}
                                                        <table class="cookies-detail-list">
                                                            <thead>
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
                                    </div>
                                    <div class="clearfix"></div>
                                {/foreach}
                            </div>
                        </div>

                        {if $C_P_FINALITIES|@count > 1}
                            <div class="cookiesplus-actions col-12 col-xs-12">
                                <div class="first-layer">
                                    <div class="row">
                                        {if $C_P_BUTTONS_LAYOUT === 1}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 2}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 3}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 4}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 5}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                        {*elseif $C_P_BUTTONS_LAYOUT === 6*}
                                        {else}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-button.tpl"}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-button.tpl"}
                                        {/if}
                                    </div>

                                    <div class="row">
                                        <div class="cookiesplus-footer-actions col-xs-12">
                                            {if isset($C_P_CMS_PAGE) && $C_P_CMS_PAGE}
                                                <div class="float-xs-right">
                                                    <a href="{$link->getCMSLink($C_P_CMS_PAGE)|escape:'htmlall':'UTF-8'}"
                                                       class="float-xs-right cookiesplus-policy"
                                                       target="_blank"
                                                       rel="nofollow noopener noreferrer">{l s='Privacy & Cookie Policy' mod='cookiesplus'}
                                                    </a>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>

                                <div class="second-layer">
                                    <div class="row">
                                        {if $C_P_BUTTONS_LAYOUT === 1}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 2}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                        {elseif $C_P_BUTTONS_LAYOUT === 3}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 4}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                        {elseif $C_P_BUTTONS_LAYOUT === 5}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                        {*elseif $C_P_BUTTONS_LAYOUT === 6*}
                                        {else}
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-customize-customize-button.tpl"}
                                            {*include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-reject-customize-button.tpl'*}
                                            <div class="col-xs-12 col-md-2"></div>
                                            {include file="$C_P_TPL_DIR/views/templates/hook/cookies-notice-accept-customize-button.tpl"}
                                        {/if}
                                    </div>

                                    <div class="row">
                                        <div class="cookiesplus-footer-actions col-xs-12">
                                            <div class="float-xs-left">
                                                <span id="cookiesplus-back" onclick="cookieGdpr.displayModal(); return false;">{l s='← Back' mod='cookiesplus'}</span>
                                            </div>

                                            {if isset($C_P_CMS_PAGE) && $C_P_CMS_PAGE}
                                                <div class="float-xs-right">
                                                    <a href="{$link->getCMSLink($C_P_CMS_PAGE)|escape:'htmlall':'UTF-8'}"
                                                       class="float-xs-right cookiesplus-policy"
                                                       target="_blank"
                                                       rel="nofollow noopener noreferrer">{l s='Privacy & Cookie Policy' mod='cookiesplus'}
                                                    </a>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {if $C_P_REVOKE_CONSENT && $C_P_DISPLAY_DATE}
                            <div class="cookiesplus-revoke text-small col-xs-12 text-xs-right">
                                <span><strong>{l s='Cookie declaration last updated on:' mod='cookiesplus'}</strong> {$C_P_REVOKE_CONSENT|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        {/if}

                        {if $C_P_SAVE_CONSENT}
                            <div class="cookiesplus-consent-hash text-small col-xs-12 text-xs-right" style="display:none;">
                                <span>
                                    <strong>{l s='Consent ID:' mod='cookiesplus'}</strong>
                                    <a target="_blank"
                                        rel="nofollow noopener noreferrer"
                                        title="{l s='Download consent' mod='cookiesplus'}">
                                    </a>
                                </span>
                            </div>
                        {/if}
                    </form>
                </div>
            </div>
        </div>

        <div id="cookiesplus-modal-not-available" style="display: none;" class="cookiesplus-center">
            <button type="button" class="cookiesplus-close" onclick="return cookieGdpr.close();">&times;</button>
            {l s='Content not available' mod='cookiesplus'}
        </div>

    </div>
</div>

{if isset($C_P_TAB_ENABLED) && $C_P_TAB_ENABLED}
    <div id="cookiesplus-tab" class="cookiesplus-move">
        <span onclick="cookieGdpr.displayModalAdvanced(true);">
            {if $C_P_ICONS}
                {if $C_P_MATERIAL_ICONS_LIBRARY == '1'}
                    <i class="material-icons">group_work</i>
                {elseif $C_P_MATERIAL_ICONS_LIBRARY == '2'}
                    <i class="fto-cog fs_xl"></i>
                {else}
                    <i class="fa fa-star fa-cookie fa-fw" aria-hidden="true"></i>
                {/if}
            {/if}
            {l s='Cookie consent' mod='cookiesplus'}
        </span>
    </div>
{/if}

<script>
    // Avoid form resubmission when page is refreshed
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

{if isset($C_P_JS) && $C_P_JS}
	{$C_P_JS nofilter}
{/if}
