{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{block name='product_flags'}
    <ul class="product-flags js-product-flags">
        {foreach from=$product.flags item=flag}
            {* EG Stickers replaces native discount values below; keep independent flags such as on-sale. *}
            {if Module::isEnabled('egstickers') && in_array($flag.type, ['discount', 'discount-percentage', 'discount-amount'])}
                {continue}
            {/if}
            {if Module::isEnabled('egstickers')}
                {hook h='displayNativeStickers' flag=$flag.type}
                {assign var="nativeFlag" value=EgStickersFlags::NativeFlag($flag.type)}
            {/if}
            {if isset($nativeFlag) &&  !empty($nativeFlag)}
                {if $nativeFlag.active}
                    <li class="product-flag ss-ribbon {if $nativeFlag.sticker_position} {if $nativeFlag.sticker_position == 1}sticker_top what{else}sticker_bottom{/if}{/if}" {if $nativeFlag.color}style="background-color: {$nativeFlag.color}; color: {$nativeFlag.color};"{/if}>
                        <span>{$nativeFlag.parallel_value|escape:'html':'UTF-8'}</span>
                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 36" width="22" height="36">
                            <g id="Group 5212">
                                <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="M20 17.05 L0 0 H-107 Q-111 0 -111 4 V30.1 Q-111 34.1 -107 34.1 H0 Z"></path>
                            </g>
                        </svg>
                    </li>
                {/if}
            {else}
                <li class="product-flag ss-ribbon {$flag.type}">
                    <span>{$flag.label|escape:'html':'UTF-8'}</span>
                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 36" width="22" height="36">
                        <g id="Group 5212">
                            <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="M20 17.05 L0 0 H-107 Q-111 0 -111 4 V30.1 Q-111 34.1 -107 34.1 H0 Z"></path>
                        </g>
                    </svg>
                </li>
            {/if}
        {/foreach}
        {hook h='displayProductFlags' id_product=$product.id_product id_product_attribute=$product.id_product_attribute}
    </ul>
{/block}
