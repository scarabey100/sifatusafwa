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

<div class="product__line">
    <div class="product__line--img">
        {if $product.default_image}
            <img width="78" height="78" src="{$product.default_image.medium.url}" alt="{$product.default_image.legend}" loading="lazy" />
        {else}
            <img width="78" height="78" src="{$urls.no_picture_image.bySize.medium_default.url}" alt="{$product.default_image.legend}" loading="lazy" />
        {/if}
    </div>
    <div class="product__line--details">
        <div class="product__line--name">{$product.name}</div>
        <div class="product__line--ref">{l s='Réf : ' d='Shop.Theme.Catalog'}{$product.reference}</div>
        <div class="product__line--price">{$product.price}</div>
    </div>
</div>

{if $product.customizations|count}
    <div class="customizations">
        <ul>
            {foreach from=$product.customizations item='customization'}
                <li>
                    <span class="product-quantity">{$customization.quantity}</span>
                    <a href="{$customization.remove_from_cart_url}" title="{l s='remove from cart' d='Shop.Theme.Actions'}" class="remove-from-cart" rel="nofollow">{l s='Remove' d='Shop.Theme.Actions'}</a>
                    <ul>
                        {foreach from=$customization.fields item='field'}
                            <li>
                                <span>{$field.label}</span>
                                {if $field.type == 'text'}
                                    <span>{$field.text nofilter}</span>
                                {elseif $field.type == 'image'}
                                    <img src="{$field.image.small.url}" loading="lazy">
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
