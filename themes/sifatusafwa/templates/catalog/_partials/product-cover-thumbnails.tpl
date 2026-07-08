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
<div class="images-container js-images-container">
    {block name='product_cover'}
        <div class="product-cover">
            {foreach from=$product.images item=image key=$key}
                <div id="product-cover__item--{$key}" class="product-cover__item">
                    <picture>
                        <source media="(min-width: 768px)" srcset="{$image.bySize.large_default.url}">
                        <source media="(max-width: 768px)" srcset="{$image.bySize.medium_default.url}">
                        <img
                                class="img-fluid"
                                src="{$image.bySize.large_default.url}"
                                {if !empty($image.legend)}
                                    alt="{$image.legend}"
                                    title="{$image.legend}"
                                {else}
                                    alt="{$product.name}"
                                {/if}
                                loading="lazy"
                                width="{$product.default_image.bySize.large_default.width}"
                                height="{$product.default_image.bySize.large_default.height}"
                        />
                    </picture>
                </div>
            {/foreach}
        </div>
    {/block}

    {block name='product_images'}
        <div class="product-nav">
            {foreach from=$product.images item=image key=$key}
                <div id="product-nav__item--{$key}" class="product-nav__item">
                    <picture>
                        <source media="(max-width: 768px)" srcset="{$image.bySize.small_default.url}">
                        <img
                                src="{$image.bySize.small_default.url}"
                                {if !empty($image.legend)}
                                    alt="{$image.legend}"
                                    title="{$image.legend}"
                                {else}
                                    alt="{$product.name}"
                                {/if}
                                loading="lazy"
                                width="{$product.default_image.bySize.small_default.width}"
                                height="{$product.default_image.bySize.small_default.height}"
                        />
                    </picture>
                </div>
            {/foreach}
        </div>
    {/block}
    {hook h='displayAfterProductThumbs' product=$product}
</div>
