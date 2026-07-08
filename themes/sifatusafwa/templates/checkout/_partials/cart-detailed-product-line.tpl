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
<div class="product-line-grid">

    <!--  product line left content: image-->
    <div class="product-line-grid-left">
        <span class="product-image media-middle">
            {if $product.default_image}
                <picture>
                    {if !empty($product.default_image.bySize.cart_default.sources.avif)}<source srcset="{$product.default_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
                    {if !empty($product.default_image.bySize.cart_default.sources.webp)}<source srcset="{$product.default_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
                    <img src="{$product.default_image.bySize.cart_default.url}" alt="{$product.name|escape:'quotes'}" loading="lazy">
                </picture>
            {else}
                <picture>
                    {if !empty($urls.no_picture_image.bySize.cart_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
                    {if !empty($urls.no_picture_image.bySize.cart_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
                    <img src="{$urls.no_picture_image.bySize.cart_default.url}" loading="lazy" />
                </picture>
            {/if}
        </span>
    </div>

    <!--  product line body: label, discounts, price, attributes, customizations -->
    <div class="product-line-grid-body">
        <div class="product-line-info">
            <a class="label" href="{$product.url}" data-id_customization="{$product.id_customization|intval}">{$product.name}</a>
        </div>

        {assign var='remainingStock' value=StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.id_product_attribute)}

        {if $remainingStock <= 0}
            <div class="product-line-info">
                <span class="out-of-stock" style="color:orange;">
                    {if $language.iso_code == 'fr'}
                        Rupture de stock
                    {elseif $language.iso_code == 'en'}
                        Out of stock
                    {elseif $language.iso_code == 'ar'}
                        غير متوفر
                    {else}
                        Out of stock
                    {/if}
                </span>
            </div>
        {elseif $remainingStock == 1}
            <div class="product-line-info">
                <span class="in-stock">
                    {if $language.iso_code == 'fr'}
                        Plus qu'1 en stock
                    {elseif $language.iso_code == 'en'}
                        Only 1 left in stock
                    {elseif $language.iso_code == 'ar'}
                        تبقى قطعة واحدة فقط
                    {else}
                        Only 1 left in stock
                    {/if}
                </span>
            </div>
        {elseif $remainingStock == 2}
            <div class="product-line-info">
                <span class="in-stock">
                    {if $language.iso_code == 'fr'}
                        Plus que 2 en stock
                    {elseif $language.iso_code == 'en'}
                        Only 2 left in stock
                    {elseif $language.iso_code == 'ar'}
                        تبقى قطعتان فقط
                    {else}
                        Only 2 left in stock
                    {/if}
                </span>
            </div>
        {else}
            <div class="product-line-info">
                <span class="in-stock">
                    {if $language.iso_code == 'fr'}
                        En stock
                    {elseif $language.iso_code == 'en'}
                        In stock
                    {elseif $language.iso_code == 'ar'}
                        متوفر
                    {else}
                        In stock
                    {/if}
                </span>
            </div>
        {/if}


        <div class="product-line-info product-price h5 {if $product.has_discount}has-discount{/if}">
            {if $product.has_discount}
                <div class="product-discount">
                    <span class="regular-price">{$product.regular_price}</span>
                    {if $product.discount_type === 'percentage'}
                        <span class="discount discount-percentage">-{$product.discount_percentage_absolute}</span>
                    {else}
                        <span class="discount discount-amount">-{$product.discount_to_display}</span>
                    {/if}
                </div>
            {/if}
            <div class="current-price">
                <span class="price">{$product.price}</span>
                {if $product.unit_price_full}
                    <div class="unit-price-cart">{$product.unit_price_full}</div>
                {/if}
            </div>
            {hook h='displayProductPriceBlock' product=$product type="unit_price"}
        </div>

        {foreach from=$product.attributes key="attribute" item="value"}
            <div class="product-line-info {$attribute|lower}">
                <span class="label">{$attribute}:</span>
                <span class="value">{$value}</span>
            </div>
        {/foreach} 
        <input type="hidden" class="quantity_available_in_stock" name="quantity_available_in_stock" value="{$remainingStock}">
        {if is_array($product.customizations) && $product.customizations|count}
            <br>
            {block name='cart_detailed_product_line_customization'}
                {foreach from=$product.customizations item="customization"}
                    <a href="#" data-toggle="modal" data-target="#product-customizations-modal-{$customization.id_customization}">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
                    <div class="modal fade customization-modal js-customization-modal" id="product-customizations-modal-{$customization.id_customization}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h4 class="modal-title">{l s='Product customization' d='Shop.Theme.Catalog'}</h4>
                                </div>
                                <div class="modal-body">
                                    {foreach from=$customization.fields item="field"}
                                        <div class="product-customization-line row">
                                            <div class="col-sm-3 col-xs-4 label">
                                                {$field.label}
                                            </div>
                                            <div class="col-sm-9 col-xs-8 value">
                                                {if $field.type == 'text'}
                                                    {if (int)$field.id_module}
                                                        {$field.text nofilter}
                                                    {else}
                                                        {$field.text}
                                                    {/if}
                                                {elseif $field.type == 'image'}
                                                    <img src="{$field.image.small.url}" loading="lazy">
                                                {/if}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            {/block}
        {/if}
    </div>

    <!--  product line right content: actions (quantity, delete), price -->
    <div class="product-line-grid-right product-line-actions">
        <div class="product-line-grid-right--left">
            <div class="qty">
                {if !empty($product.is_gift)}
                    <span class="gift-quantity">{$product.quantity}</span>
                {else}
                    <input
                            class="js-cart-line-product-quantity"
                            data-down-url="{$product.down_quantity_url}"
                            data-up-url="{$product.up_quantity_url}"
                            data-update-url="{$product.update_quantity_url}"
                            data-product-id="{$product.id_product}"
                            type="number"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            value="{$product.quantity}"
                            name="product-quantity-spin"
                            aria-label="{l s='%productName% product quantity field' sprintf=['%productName%' => $product.name] d='Shop.Theme.Checkout'}"
                    />
                {/if}
            </div>
            <div class="price">
                <span class="product-price">
                    <strong>
                        {if !empty($product.is_gift)}
                            <span class="gift">{l s='Gift' d='Shop.Theme.Checkout'}</span>
                        {else}
                            {$product.total}
                        {/if}
                    </strong>
                </span>
            </div>
        </div>
        <div class="product-line-grid-right--right">
            <div class="cart-line-product-actions">
                <a
                        class                       = "remove-from-cart"
                        rel                         = "nofollow"
                        href                        = "{$product.remove_from_cart_url}"
                        data-link-action            = "delete-from-cart"
                        data-id-product             = "{$product.id_product|escape:'javascript'}"
                        data-id-product-attribute   = "{$product.id_product_attribute|escape:'javascript'}"
                        data-id-customization       = "{$product.id_customization|default|escape:'javascript'}"
                >
                    {if empty($product.is_gift)}
                        <i class="material-icons float-xs-left">close</i>
                    {/if}
                </a>

                {block name='hook_cart_extra_product_actions'}
                    {hook h='displayCartExtraProductActions' product=$product}
                {/block}

            </div>
        </div>
    </div>

</div>
