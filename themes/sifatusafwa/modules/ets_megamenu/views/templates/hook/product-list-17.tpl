{*
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
*}
{if isset($products) && $products}
    {foreach from=$products item="product"}
        <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product|intval}" data-id-product-attribute="{$product.id_product_attribute|intval}">
            <div class="thumbnail-container">

                <div class="thumbnail-top">
                    {include file='catalog/_partials/product-flags.tpl'}

                    {block name='product_thumbnail'}
                        <a href="{$product.url|escape:'html':'UTF-8'}" class="thumbnail product-thumbnail">
                            {if isset($product.image_id)}{assign var='imageLink' value=$link->getImageLink($product.link_rewrite, $product.image_id, $imageType)}{else}{assign var='imageLink' value=$link->getImageLink($product.link_rewrite, $product.id_image, $imageType)}{/if}
                            <picture>
                                <img
                                        src="{if (strpos($imageLink,'http://')===false || strpos($imageLink,'https://'))}{$protocol_link nofilter}{/if}{$imageLink|escape:'html':'UTF-8'}"
                                        alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}"
                                        data-full-size-image-url = "{if (strpos($imageLink,'http://')===false || strpos($imageLink,'https://'))}{$protocol_link nofilter}{/if}{$imageLink|escape:'html':'UTF-8'}"
                                        loading="lazy"
                                />
                            </picture>
                        </a>
                    {/block}
                    <div class="highlighted-informations{if !$product.main_variants} no-variants{/if}">
                        <a class="quick-view js-quick-view" href="#" data-link-action="quickview">{l s='Quick view' d='Shop.Theme.Actions'}</a>
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
                        <div class="h3 product-title" itemprop="name">
                            <a href="{$product.url|escape:'html':'UTF-8'}" content="{$product.url|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a>
                        </div>
                    {/block}

                    <div class="product-subtitle">{Product::getProductNameAr($product.id)}</div>

                    {block name='product_price_and_shipping'}
                        {if $product.show_price}
                            <div class="product-price-and-shipping">
                                {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                <span itemprop="price" class="price">{$product.price|escape:'html':'UTF-8'}</span>
                                {if $product.has_discount}
                                    {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                    <span class="regular-price">{$product.regular_price|escape:'html':'UTF-8'}</span>
                                {/if}
                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                            </div>
                        {/if}
                    {/block}
                </div>
            </div>
        </article>
    {/foreach}
{else}
    <span class="mm_alert alert-warning">{l s='No product available' mod='ets_megamenu'}</span>
{/if}