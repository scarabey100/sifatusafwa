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
<div id="_desktop_cart">
    <div class="blockcart cart-preview" data-refresh-url="{$refresh_url}">
        <a rel="nofollow" aria-label="{l s='Shopping cart link containing %nbProducts% product(s)' sprintf=['%nbProducts%' => $cart.products_count] d='Shop.Theme.Checkout'}" href="{$cart_url}" class="header__tools--link popup__open" data-popup="popup__minicart">
            <svg xmlns="http://www.w3.org/2000/svg" width="20.037" height="23.547" viewBox="0 0 20.037 23.547">
                <path id="Path_57" data-name="Path 57" d="M14.956,3a4.546,4.546,0,0,0-4.528,4.528v.906H5.956L5.9,9.283l-.906,16.3-.057.962H24.974l-.056-.963-.906-16.3-.057-.849H19.484V7.528A4.546,4.546,0,0,0,14.956,3Zm0,1.811a2.717,2.717,0,0,1,2.717,2.717v.906H12.239V7.528A2.717,2.717,0,0,1,14.956,4.811Zm-7.3,5.434h2.774v2.717h1.811V10.245h5.434v2.717h1.811V10.245h2.774l.792,14.49H6.862Z" transform="translate(-4.937 -3)" fill="#212121"/>
            </svg>
            <span class="cart-products-count counter">{$cart.products_count}</span>
        </a>

        <div id="popup__minicart" class="popup popup__minicart">
            <div class="popup__wrapper">
                <div class="popup__header">
                    {l s="Votre panier " d='Shop.Theme.Actions'}
                    <button class="popup__close">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00045 8.145L0.908488 0.0530396L0.0476066 0.913921L8.13957 9.00588L0.0476066 17.0978L0.908488 17.9587L9.00045 9.86676L17.0922 17.9586L17.9531 17.0977L9.86133 9.00588L17.9531 0.914085L17.0922 0.0532037L9.00045 8.145Z" fill="black"/>
                        </svg>
                    </button>
                </div>
                <div class="popup__body">
                    {if $cart.products_count > 0}
                        <div class="popup__minicart--content">
                            <ul>
                                {foreach from=$cart.products item=product}
                                    <li>
                                        {include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {else}
                        <div class="popup__minicart--empty">
                            <p>{l s='There are no more items in your cart' d='Shop.Theme.Checkout'}</p>
                        </div>
                    {/if}
                </div>
                <div class="popup__footer">
                    <a href="{$cart_url}" class="btn" title="{l s='Voir mon panier' d='Shop.Theme.Actions'}">
                        {l s='Voir le panier' d='Shop.Theme.Actions'}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
