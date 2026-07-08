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

<div class="product-line-grid">
    <form method="post" id="presta-add-cart-form" name="presta-add-cart-form">

        <div class="product-line-grid-left">
            <span class="product-image media-middle">
                {foreach $product.images as $image}
                    {foreach $image.bySize as $img}
                        <picture>
                            <img
                                    src="{$img.url|escape:'html':'UTF-8'}"
                                    alt="Product Image"
                                    height="88"
                                    width="88"
                                    loading="lazy"
                            />
                            {break}
                        </picture>
                    {/foreach}
                    {break}
                {/foreach}
            </span>
        </div>

        <div class="product-line-grid-body">
            <input type="hidden" value="{$static_token|escape:'htmlall':'UTF-8'}" name="token">
            <input id="product_page_product_id" type="hidden" value="{$product.id_product|escape:'htmlall':'UTF-8'}" name="id_product">
            <input id="product_customization_id" type="hidden" value="0" name="id_customization">
            <input id="product_qty" type="hidden" value="{$product.cust_qty|escape:'htmlall':'UTF-8'}" name="qty">

            <div class="product-line-info">
                <a class="label" href="{$product.url|escape:'htmlall':'UTF-8'}" data-id_customization="{$product.id_customization|intval}">{$product.name|escape:'htmlall':'UTF-8'}</a>
            </div>
            {if $product.add_to_cart_url > 0}
                <div class="product-line-info">
                    <span class="in-stock">{l s='In stock' d='Shop.Theme.Catalog'}</span>
                </div>
            {else}
                <div class="product-line-info">
                    <span class="out-of-stock">{l s='Out of stock' d='Shop.Theme.Catalog'}</span>
                </div>
            {/if}

            {foreach from=$product.attributes key="attribute" item="value"}
                <div class="product-line-info edition">
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
        <div class="product-line-grid-right product-line-actions">
            <div class="product-line-grid-right--left">
                <div class="price">
                    <span class="product-price">
                        <strong>{$product.price|escape:'htmlall':'UTF-8'}</strong>
                    </span>
                </div>
                <div class="qty">
                    <button
                            type="button"
                            id="presta-add-cart"
                            href="javascript:void(0);"
                            class="btn btn-primary"
                            {if !$product.add_to_cart_url}
                                disabled
                            {/if}
                            data-id-product             = "{$product.id_product|escape:'htmlall':'UTF-8'}"
                            data-id-product-attribute   = "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                            data-id-customization   	  = "{$product.id_customization|escape:'htmlall':'UTF-8'}">
                        {l s='Add to cart' mod='savecartforlater'}
                    </button>
                </div>
            </div>
            <div class="product-line-grid-right--right">
                <div class="cart-line-product-actions">
                    <a
                            class                       = "remove-from-cart"
                            rel                         = "nofollow"
                            href                        = "javascript:void(0);"
                            data-controller				= "cart-controller"
                            data-id-product             = "{$product.id_product|escape:'htmlall':'UTF-8'}"
                            data-id-product-attribute   = "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
                            data-id-customization   	  = "{$product.id_customization|escape:'htmlall':'UTF-8'}">
                        <i class="material-icons pull-xs-left">close</i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

