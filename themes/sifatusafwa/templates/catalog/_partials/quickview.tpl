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
<div id="quickview-modal-{$product.id}-{$product.id_product_attribute}" class="modal fade quickview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <div class="product__view">
                    <div class="product__media">
                        {block name='product_cover_thumbnails'}
                            {include file='catalog/_partials/product-cover-thumbnails.tpl'}
                        {/block}
                    </div>
                    <div class="product__info">
                        <h1>
                            {block name='page_title'}{$product.name}{/block}
                            <strong class="font-ar">{Product::getProductNameAr($product.id)}</strong>
                        </h1>
                        <div class="product__info--intro">
                            {if $product.manufacturer_name}
                                <div class="product__author">
                                    {l s='Author' d='Shop.Theme.Catalog'}:
                                    <span>
                                        {if $product.id_manufacturer}
                                            <a href="{Product::assignManufacturerUrl($product.id_manufacturer)}">
                                        {/if}
                                            {$product.manufacturer_name}
                                        {if $product.id_manufacturer}
                                            </a>
                                        {/if}
                                    </span>
                                </div>
                            {/if}
                            {if isset($product.reference_to_display) && $product.reference_to_display neq ''}
                                <div class="product__ref product__ref_js_get">{l s='Reference:' d='Shop.Theme.Anass'} {$product.reference_to_display}</div>
                            {else}
                                <div class="product__ref">{l s='Reference:' d='Shop.Theme.Anass'} {$product.reference}</div>
                            {/if}
                        </div>
                        {block name='product_prices'}
                            {include file='catalog/_partials/product-prices.tpl'}
                        {/block}
                        {block name='product_description_short'}
                            <div class="product-description">
                                <div class="product-description__inner">
                                    <div id="product-description-short">{$product.description_short nofilter}</div>
                                </div>
                            </div>
                        {/block}
                        {block name='product_buy'}
                            <div class="product-actions js-product-actions">
                                <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                                    <input type="hidden" name="token" value="{$static_token}">
                                    <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                                    <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id" class="js-product-customization-id">
                                    {block name='product_variants'}
                                        {include file='catalog/_partials/product-variants.tpl'}
                                    {/block}

                                    {block name='product_add_to_cart'}
                                        {include file='catalog/_partials/product-add-to-cart.tpl'}
                                    {/block}

                                    {* Input to refresh product HTML removed, block kept for compatibility with themes *}
                                    {block name='product_refresh'}{/block}
                                </form>
                            </div>
                        {/block}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
