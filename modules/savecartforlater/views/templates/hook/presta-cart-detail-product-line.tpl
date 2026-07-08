{**
* 2008-2024 Prestaworld
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one website.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
*
* DISCLAIMER
*
* Do not alter or add/update to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    prestaworld
* @copyright 2008-2024 Prestaworld
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* International Registered Trademark & Property of prestaworld
*}

<div class="presta-product-line-grid">
    <form method="post" accept="" id="presta-add-cart-form" name="presta-add-cart-form">
        <!--  product left content: image-->
        <div class="presta-product-line-grid-left col-md-3 col-xs-4">
            <span class="product-image media-middle">
                {foreach $product.images as $image}
                    {foreach $image.bySize as $img}
                            <img src="{$img.url|escape:'html':'UTF-8'}" alt="Product Image" height="100px" width="100px">
                            {break}
                    {/foreach}
                    {break}
                {/foreach}
            </span>
        </div>

        <!--  product left body: description -->
        <div class="presta-product-line-grid-body col-md-4 col-xs-8">
            <input type="hidden" value="{$static_token|escape:'htmlall':'UTF-8'}" name="token">
            <input id="product_page_product_id" type="hidden" value="{$product.id_product|escape:'htmlall':'UTF-8'}" name="id_product">
            <input id="product_customization_id" type="hidden" value="0" name="id_customization">
            <input id="product_qty" type="hidden" value="{$product.cust_qty|escape:'htmlall':'UTF-8'}" name="qty">
            <div class="product-line-info">
                <a
                    class="label"
                    href="{$product.url|escape:'htmlall':'UTF-8'}"
                    data-id_customization="{$product.id_customization|intval}">{$product.name|escape:'htmlall':'UTF-8'}</a>
            </div>
            {if Configuration::get('PRESTA_SAVE_CART_PRICE_CHANGE')}
                {block name='product_discount'}
                    {if $product.has_discount}
                    <div class="product-discount">
                        <span class="regular-price">{$product.regular_price|escape:'htmlall':'UTF-8'}</span>
                    </div>
                    {/if}
                {/block}
            {/if}
            <div class="product-price h5 has-discount current-price">
                <span
                    itemprop="price"
                    content="{$product.price_amount|escape:'htmlall':'UTF-8'}">
                    {$product.price|escape:'htmlall':'UTF-8'}
                </span>
                {if Configuration::get('PRESTA_SAVE_CART_PRICE_CHANGE')}
                    {if $product.has_discount}
                        {if $product.discount_type === 'percentage'}
                            <span class="discount discount-percentage">
                                {l s='Save %percentage%' mod='savecartforlater' sprintf=['%percentage%' => $product.discount_percentage_absolute]|escape:'htmlall':'UTF-8'}
                            </span>
                        {else}
                            <span class="discount discount-amount">
                                {l s='Save %amount%' mod='savecartforlater' sprintf=['%amount%' => $product.discount_to_display]|escape:'htmlall':'UTF-8'}
                            </span>
                        {/if}
                    {/if}
                {/if}
            </div>

            <br/>
            {foreach from=$product.attributes key="attribute" item="value"}
                <div class="product-line-info">
                    <span class="label">{$value.group|escape:'htmlall':'UTF-8'}:</span>
                    <span class="value">{$value.name|escape:'htmlall':'UTF-8'}</span>
                    <input
                        type="hidden"
                        name="group[{$attribute|escape:'htmlall':'UTF-8'}]"
                        value="{$value.id_attribute|escape:'htmlall':'UTF-8'}">
                </div>
            {/foreach}
        </div>

        <!--  product left body: description -->
        <div class="presta-product-line-grid-right product-line-actions col-md-5 col-xs-12">
            <div class="row">
                <div class="col-xs-4 hidden-md-up"></div>
                <div class="col-md-10 col-xs-6 rm-padding">
                    <div class="row">
                        <div class="col-md-6 col-xs-6 qty">
                            <button
                                type="button"
                                id="presta-add-cart"
                                href="javascript:void(0);"
                                class="btn btn-primary"
                                data-id-product             = "{$product.id_product|escape:'htmlall':'UTF-8'}"
                                data-id-product-attribute   = "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                                data-id-customization   	  = "{$product.id_customization|escape:'htmlall':'UTF-8'}">
                                {l s='Add to cart' mod='savecartforlater'}
                            </button>
                        </div>
                    </div>
                    {if Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY')}
                        {if $product.quantity <= Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY')}
                        <p class="label presta-notify">
                            <i class="material-icons pull-xs-left">warning</i>
                            <span>
                                {$product.quantity|escape:'htmlall':'UTF-8'} {l s='Product left in the stock' mod='savecartforlater'}
                            </span>
                        </p>
                        {/if}
                    {/if}
                </div>
                <div class="col-md-2 col-xs-2 text-xs-right">
                    <div class="cart-line-product-actions">
                        <a
                            class                       = "remove-from-cart"
                            rel                         = "nofollow"
                            href                        = "javascript:void(0);"
                            data-controller				= "cart-controller"
                            data-id-product             = "{$product.id_product|escape:'htmlall':'UTF-8'}"
                            data-id-product-attribute   = "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                            data-id-customization   	  = "{$product.id_customization|escape:'htmlall':'UTF-8'}">
                            <i class="material-icons pull-xs-left">delete</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </form>
</div>

