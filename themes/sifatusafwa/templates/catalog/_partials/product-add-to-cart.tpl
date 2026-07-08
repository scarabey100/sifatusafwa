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
<div class="product-add-to-cart js-product-add-to-cart">
    {if !$configuration.is_catalog}

        {block name='product_availability'}
            <div id="product-availability" class="product-availability js-product-availability">
                {if $product.availability == 'last_remaining_items'}
                    <i class="material-icons product-last-items">&#xE002;</i>
                    {$product.availability_message}
                {elseif $product.availability == 'unavailable'}
                    {assign var="combinationList" value=Product::getCustomCombinations($product.id_product)}
                    {if isset($combinationList) && $combinationList|@count > 0}
                        {assign var="has_stock_combination" value=false} 
                        {foreach $combinationList as $combination}
                            {if $combination.quantity > 0}
                                {assign var="has_stock_combination" value=true}
                                {break}
                            {/if}
                        {/foreach}
                        {if $has_stock_combination} 
                            {l s='Product available with other options' d='Shop.Theme.Actions'}
                        {else}
                            {l s='Out of stock' d='Shop.Theme.Actions'}
                        {/if}
                    {else}
                        {l s='Out of stock' d='Shop.Theme.Actions'}
                    {/if}
                {/if} 
            </div>
        {/block} 
        {block name='product_quantity'}
            {assign var='remainingStock' value=StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.id_product_attribute)}
            {if $product.quantity_wanted >= $remainingStock || $product.quantity_wanted >= $product.quantity}
                <script>
                setTimeout(function() {
                    $('.bootstrap-touchspin-up').prop('disabled', true);
                }, 2000);
                </script>
            {else}
                <script>
                 setTimeout(function() {
                  $('.bootstrap-touchspin-up').prop('disabled', false);
                 }, 2000);
                </script>
            {/if}
            <div class="product-quantity">
                <div class="qty">
                    <input
                            type="number"
                            name="qty"
                            id="quantity_wanted"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            {if $product.quantity_wanted}
                                value="{$product.quantity_wanted}"
                                min="{$product.minimal_quantity}"
                            {else}
                                value="1"
                                min="1"
                            {/if}
                            class="input-group"
                            aria-label="{l s='Quantity' d='Shop.Theme.Actions'}"
                    >
                </div>
              
                {assign var='remainingStock' value=StockAvailable::getQuantityAvailableByProduct($product.id_product, $product.id_product_attribute)}
                <div class="add">
                    {if $product.quantity_wanted >= $remainingStock || $product.quantity_wanted >= $product.quantity }
                        <script>
                            $('.bootstrap-touchspin-up').prop('disabled', true);
                        </script>
                    {else}
                        <script>
                            $('.bootstrap-touchspin-up').prop('disabled', false);
                        </script>
                    {/if}
                    {if $product.availability == "unavailable"}
                        {hook h='displayProductActions' product=$product}
                    {/if}
                    <button
                            class="btn btn-primary add-to-cart"
                            data-button-action="add-to-cart"
                            type="submit"  
                            {if $product.quantity_wanted >= $remainingStock+1 || $product.quantity_wanted > $product.quantity}
                                disabled
                            {elseif !$product.add_to_cart_url }
                                disabled
                            {/if}
                            >
                        {l s='Add to cart' d='Shop.Theme.Actions'}
                    </button>
                    {if $product.add_to_cart_url && $product.show_price && $product.price_amount > 0 && $product.availability != 'unavailable' && $product.quantity_wanted < $remainingStock+1 && $product.quantity_wanted <= $product.quantity}
                        <div class="product-payment-shortcut">
                            {hook h='displayProductActions' product=$product}
                        </div>
                    {/if}
                </div>

                {hook h='displayProductListFunctionalButtons' product=$product}

                <div class="product__share">
                    <a href="#" class="share-button"><i class="material-icons" aria-hidden="true">share</i></a>
                </div>
            </div>
        {/block}
        <input type="hidden" class="quantity_available_in_stock" name="quantity_available_in_stock" value="{StockAvailable::getQuantityAvailableByProduct($product.id_product,$product.id_product_attribute)}">
        <script>
            (function() {
                var productAddToCart = document.currentScript.closest('.js-product-add-to-cart');
                if (!productAddToCart) {
                    return;
                }

                var addToCartButton = productAddToCart.querySelector('.add-to-cart');
                var paypalShortcuts = productAddToCart.querySelectorAll('[data-container-express-checkout][data-paypal-source-page="product"]');

                if (addToCartButton && addToCartButton.disabled) {
                    paypalShortcuts.forEach(function(paypalShortcut) {
                        paypalShortcut.style.display = 'none';
                    });
                }
            })();
        </script>
{*        {block name='product_availability'}*}
{*            <div id="product-availability" class="product-availability js-product-availability">*}
{*                {if $product.show_availability && $product.availability_message}*}
{*                    {if $product.availability == 'available'}*}
{*                        <i class="material-icons rtl-no-flip product-available">&#xE5CA;</i>*}
{*                    {elseif $product.availability == 'last_remaining_items'}*}
{*                        <i class="material-icons product-last-items">&#xE002;</i>*}
{*                    {else}*}
{*                        <i class="material-icons product-unavailable">&#xE14B;</i>*}
{*                    {/if}*}
{*                    {$product.availability_message}*}
{*                {/if}*}
{*            </div>*}
{*        {/block}*}

        {block name='product_minimal_quantity'}
            <p class="product-minimal-quantity js-product-minimal-quantity">
                {if $product.minimal_quantity > 1}
                    {l
                    s='The minimum purchase order quantity for the product is %quantity%.'
                    d='Shop.Theme.Checkout'
                    sprintf=['%quantity%' => $product.minimal_quantity]
                    }
                {/if}
            </p>
        {/block}
    {/if}
</div>
