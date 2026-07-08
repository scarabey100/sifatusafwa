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

<div id="blockcart-modal" class="popup">
    <div class="popup__wrapper">
        <div class="popup__header">
            {l s='L’ARTICLE A ÉTÉ AJOUTÉ À VOTRE PANIER' d='Shop.Theme.Checkout'}
            <button class="popup__close" data-dismiss="modal">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00045 8.145L0.908488 0.0530396L0.0476066 0.913921L8.13957 9.00588L0.0476066 17.0978L0.908488 17.9587L9.00045 9.86676L17.0922 17.9586L17.9531 17.0977L9.86133 9.00588L17.9531 0.914085L17.0922 0.0532037L9.00045 8.145Z" fill="black"/>
                </svg>
            </button>
        </div>
        <div class="popup__body">
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
                    {hook h='displayProductFeatures' product=$product style='product__line--color'}
                    <div class="product__line--ref">{l s='Réf : ' d='Shop.Theme.Catalog'}{$product.reference}</div>
                    <div class="product__line--price">{$product.price}</div>
                </div>
            </div>
            <div class="product__line--actions">
                <a href="{$cart_url}" class="btn btn-light">{l s='Voir le panier' d='Shop.Theme.Actions'}</a>
                <a href="#" class="btn__back popup__close" data-dismiss="modal">
                    <svg width="20" height="10" viewBox="0 0 20 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 5H19M5 9L1 5L5 9ZM1 5L5 1L1 5Z" stroke="#282828" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {l s='Continuer mon shopping' d='Shop.Theme.Actions'}
                </a>
            </div>
        </div>
    </div>
</div>

{*<div id="blockcart-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">*}
{*    <div class="modal-dialog" role="document">*}
{*        <div class="modal-content">*}
{*            <div class="modal-header">*}
{*                <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">*}
{*                    <span aria-hidden="true"><i class="material-icons">close</i></span>*}
{*                </button>*}
{*                <h4 class="modal-title h6 text-sm-center" id="myModalLabel"><i class="material-icons rtl-no-flip">&#xE876;</i>{l s='Product successfully added to your shopping cart' d='Shop.Theme.Checkout'}</h4>*}
{*            </div>*}
{*            <div class="modal-body">*}
{*                <div class="row">*}
{*                    <div class="col-md-5 divide-right">*}
{*                        <div class="row">*}
{*                            <div class="col-md-6">*}
{*                                {if $product.default_image}*}
{*                                    <img*}
{*                                            src="{$product.default_image.medium.url}"*}
{*                                            data-full-size-image-url="{$product.default_image.large.url}"*}
{*                                            title="{$product.default_image.legend}"*}
{*                                            alt="{$product.default_image.legend}"*}
{*                                            loading="lazy"*}
{*                                            class="product-image"*}
{*                                    >*}
{*                                {else}*}
{*                                    <img*}
{*                                            src="{$urls.no_picture_image.bySize.medium_default.url}"*}
{*                                            loading="lazy"*}
{*                                            class="product-image"*}
{*                                    />*}
{*                                {/if}*}
{*                            </div>*}
{*                            <div class="col-md-6">*}
{*                                <h6 class="h6 product-name">{$product.name}</h6>*}
{*                                <p class="product-price">{$product.price}</p>*}
{*                                {hook h='displayProductPriceBlock' product=$product type="unit_price"}*}
{*                                {foreach from=$product.attributes item="property_value" key="property"}*}
{*                                    <span class="{$property|lower}">{l s='%label%:' sprintf=['%label%' => $property] d='Shop.Theme.Global'}<strong> {$property_value}</strong></span><br>*}
{*                                {/foreach}*}
{*                                <span class="product-quantity">{l s='Quantity:' d='Shop.Theme.Checkout'}&nbsp;<strong>{$product.cart_quantity}</strong></span>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
{*                    <div class="col-md-7">*}
{*                        <div class="cart-content">*}
{*                            {if $cart.products_count > 1}*}
{*                                <p class="cart-products-count">{l s='There are %products_count% items in your cart.' sprintf=['%products_count%' => $cart.products_count] d='Shop.Theme.Checkout'}</p>*}
{*                            {else}*}
{*                                <p class="cart-products-count">{l s='There is %products_count% item in your cart.' sprintf=['%products_count%' =>$cart.products_count] d='Shop.Theme.Checkout'}</p>*}
{*                            {/if}*}
{*                            <p><span class="label">{l s='Subtotal:' d='Shop.Theme.Checkout'}</span>&nbsp;<span class="subtotal value">{$cart.subtotals.products.value}</span></p>*}
{*                            {if $cart.subtotals.shipping.value}*}
{*                                <p><span>{l s='Shipping:' d='Shop.Theme.Checkout'}</span>&nbsp;<span class="shipping value">{$cart.subtotals.shipping.value} {hook h='displayCheckoutSubtotalDetails' subtotal=$cart.subtotals.shipping}</span></p>*}
{*                            {/if}*}

{*                            {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}*}
{*                                <p><span>{$cart.totals.total.label}{if $configuration.display_taxes_label}&nbsp;{$cart.labels.tax_short}{/if}</span>&nbsp;<span>{$cart.totals.total.value}</span></p>*}
{*                                <p class="product-total"><span class="label">{$cart.totals.total_including_tax.label}</span>&nbsp;<span class="value">{$cart.totals.total_including_tax.value}</span></p>*}
{*                            {else}*}
{*                                <p class="product-total"><span class="label">{$cart.totals.total.label}&nbsp;{if $configuration.taxes_enabled && $configuration.display_taxes_label}{$cart.labels.tax_short}{/if}</span>&nbsp;<span class="value">{$cart.totals.total.value}</span></p>*}
{*                            {/if}*}

{*                            {if $cart.subtotals.tax}*}
{*                                <p class="product-tax">{l s='%label%:' sprintf=['%label%' => $cart.subtotals.tax.label] d='Shop.Theme.Global'}&nbsp;<span class="value">{$cart.subtotals.tax.value}</span></p>*}
{*                            {/if}*}
{*                            {hook h='displayCartModalContent' product=$product}*}
{*                            <div class="cart-content-btn">*}
{*                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Continue shopping' d='Shop.Theme.Actions'}</button>*}
{*                                <a href="{$cart_url}" class="btn btn-primary"><i class="material-icons rtl-no-flip">&#xE876;</i>{l s='Proceed to checkout' d='Shop.Theme.Actions'}</a>*}
{*                            </div>*}
{*                        </div>*}
{*                    </div>*}
{*                </div>*}
{*            </div>*}
{*            {hook h='displayCartModalFooter' product=$product}*}
{*        </div>*}
{*    </div>*}
{*</div>*}
