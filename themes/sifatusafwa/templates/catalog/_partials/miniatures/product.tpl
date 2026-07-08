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
{block name='product_miniature_item'}
    <div class="js-product product{if !empty($productClasses)} {$productClasses}{/if}">
        <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
            <div class="thumbnail-container">

                <div class="thumbnail-top">

                    {include file='catalog/_partials/product-flags.tpl'}

                    {block name='product_thumbnail'}
                        <a href="{$product.url}" class="thumbnail product-thumbnail">
                            {if $product.cover}
                                <picture>
                                    {if !empty($product.cover.bySize.home_default.sources.avif)}<source srcset="{$product.cover.bySize.home_default.sources.avif}" type="image/avif">{/if}
                                    {if !empty($product.cover.bySize.home_default.sources.webp)}<source srcset="{$product.cover.bySize.home_default.sources.webp}" type="image/webp">{/if}
                                    <img
                                            src="{$product.cover.bySize.home_default.url}"
                                            alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                                            loading="lazy"
                                            data-full-size-image-url="{$product.cover.large.url}"
                                            width="{$product.cover.bySize.home_default.width}"
                                            height="{$product.cover.bySize.home_default.height}"
                                    />
                                </picture>
                                {if isset($product.images[1])}
                                    <picture class="img-hover">
                                        <img
                                                itemprop="image"
                                                loading="lazy"
                                                src="{$product.images[1].bySize.home_default.url}"
                                                alt="{if !empty($product.images[1].legend)}{$product.images[1].legend}{else}{$product.name}{/if}"
                                        />
                                    </picture>
                                {/if}
                            {else}
                                <picture>
                                    {if !empty($urls.no_picture_image.bySize.home_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.avif}" type="image/avif">{/if}
                                    {if !empty($urls.no_picture_image.bySize.home_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.webp}" type="image/webp">{/if}
                                    <img
                                            src="{$urls.no_picture_image.bySize.home_default.url}"
                                            alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                                            width="{$urls.no_picture_image.bySize.home_default.width}"
                                            height="{$urls.no_picture_image.bySize.home_default.height}"
                                            loading="lazy"
                                    />
                                </picture>
                            {/if}
                        </a>
                    {/block}

                    <div class="highlighted-informations{if !$product.main_variants} no-variants{/if}">
                        {block name='quick_view'}
                            <a class="quick-view js-quick-view" href="#" data-link-action="quickview">{l s='Quick view' d='Shop.Theme.Actions'}</a>
                        {/block}
                        {if isset($readOnly)}
                            <div class="wishlist-remove">
                                <a href="#" class="js-egwishlist-remove"
                                   data-id-product="{$product->id_product|intval}"
                                   data-url="{url entity='module' name='egwishlist' controller='actions'}">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </a>
                            </div>
                        {else}
                            <div class="wishlist-icon">
                                {hook h='displayProductListFunctionalButtons' product=$product}
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="product-description">
                    {block name='product_name'}
                        {if $page.page_name == 'index'}
                            <h3 class="h3 product-title"><a href="{$product.url}" content="{$product.url}">{$product.name}</a></h3>
                        {else}
                            <h2 class="h3 product-title"><a href="{$product.url}" content="{$product.url}">{$product.name}</a></h2>
                        {/if}
                    {/block}

                    <div class="product-subtitle">{Product::getProductNameAr($product.id)}</div>

                    {block name='product_price_and_shipping'}
                        {if $product.show_price}
                            <div class="product-price-and-shipping">

                                <span class="price" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">
                                    {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
                                    {if '' !== $smarty.capture.custom_price}
                                        {$smarty.capture.custom_price nofilter}
                                    {else}
                                        {$product.price}
                                    {/if}
                                </span>

                                {if $product.has_discount}
                                    {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                    <span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>
                                {/if}

                                {hook h='displayProductPriceBlock' product=$product type="before_price"}

                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                            </div>
                        {/if}
                    {/block}
                </div>
                {if !$configuration.is_catalog && $page.page_name == "module-egwishlist-view"}
                    <div class="product-addToCart">
                        {if $product.add_to_cart_url && !$product.customizable}
                            <form type="post" action="{$product.add_to_cart_url|escape:'html':'UTF-8'}" class="product-item-buttons">
                                {if $product.minimal_quantity !== NULL}
                                    <input type="hidden" name="qty"
                                            value="{$product.minimal_quantity|escape:'html':'UTF-8'}">
                                {/if}
                                <div class="add">
                                    <button
                                            class="btn btn-primary add-to-cart"
                                            data-button-action="add-to-cart"
                                            type="submit"
                                            {if !$product.add_to_cart_url}
                                                disabled
                                            {/if}
                                    >
                                        {l s='Add to cart' d='Shop.Theme.Actions'}
                                    </button>
                                </div>
                            </form>
                        {else}
                            <a class="btn btn-secondary" role="button"
                                href="{$product.url|escape:'html':'UTF-8'}"
                                class="thumbnail product-thumbnail">{l s='Personalize' mod='rkrselledplus' }</a>
                        {/if}
                    </div>
                {/if}
            </div>
        </article>
    </div>
{/block}
