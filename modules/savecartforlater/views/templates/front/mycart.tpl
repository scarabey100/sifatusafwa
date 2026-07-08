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

{extends file=$layout}

{block name='content'}
    <section id="main">
        <header class="page-header">
            <h1>{l s='My Saved Cart' mod='savecartforlater'}</h1>
        </header>
        {if isset($smarty.get.success) && $smarty.get.success == 1}
            <div class="alert alert-success">{l s='Successfully deleted' mod='savecartforlater'}</div>
        {/if}
        <section class="page-content" id="content">
            <div class="table-responsive">
                <table class="table table-bordered table-labeled prsta-table">
                    <thead class="thead-default">
                        <tr>
                            <th>{l s='ID' mod='savecartforlater'}</th>
                            <th>{l s='Product Name' mod='savecartforlater'}</th>
                            <th>{l s='Image' mod='savecartforlater'}</th>
                            <th>{l s='Price' mod='savecartforlater'}</th>
                            <th>{l s='Available Quantity' mod='savecartforlater'}</th>
                            <th>{l s='Status' mod='savecartforlater'}</th>
                            <th>{l s='Action' mod='savecartforlater'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if isset($products)}
                            {foreach $products as $key => $prod}
                                <tr>
                                    <th>{$prod.id|intval}</th>
                                    <td>
                                        <a class="presta-link" href="{$prod.url|escape:'htmlall':'UTF-8'}">
                                            {$prod.name|escape:'htmlall':'UTF-8'}<br/>{if $prod.attr}{$prod.attr|escape:'htmlall':'UTF-8'}{/if}
                                        </a>
                                    </td>
                                    <td>
                                        {foreach $prod.images as $image}
                                            {foreach $image.bySize as $img}
                                                    <img
                                                        src="{$img.url|escape:'html':'UTF-8'}"
                                                        alt="Product Image"
                                                        height="100px"
                                                        width="100px">
                                                    {break}
                                            {/foreach}
                                            {break}
                                        {/foreach}
                                    </td>
                                    <td>
                                        <div class="product-price h5 has-discount">
                                        {if Configuration::get('PRESTA_SAVE_CART_PRICE_CHANGE')}
                                            {if $prod.has_discount}
                                                <div class="product-discount">
                                                    <span class="regular-price">{$prod.regular_price|escape:'htmlall':'UTF-8'}</span>
                                                </div>
                                            {/if}
                                        {/if}
                                        {$prod.price|escape:'htmlall':'UTF-8'}
                                        {if Configuration::get('PRESTA_SAVE_CART_PRICE_CHANGE')}
                                            {if $prod.has_discount}
                                                {if $prod.discount_type === 'percentage'}
                                                    <span class="discount discount-percentage">{l s='Save %percentage%' mod='savecartforlater' sprintf=['%percentage%' => $prod.discount_percentage_absolute]|escape:'htmlall':'UTF-8'}</span>
                                                {else}
                                                    <span class="discount discount-amount">
                                                        {l s='Save %amount%' mod='savecartforlater' sprintf=['%amount%' => $prod.discount_to_display]|escape:'htmlall':'UTF-8'}
                                                    </span>
                                                {/if}
                                            {/if}
                                        {/if}
                                        </div>
                                    </td>
                                    <td>
                                    {if Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY')}
                                        {if $prod.quantity <= Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY')}
                                        <p class="label presta-notify">
                                            <i class="material-icons pull-xs-left">warning</i>
                                            <span>
                                                {$prod.quantity|escape:'htmlall':'UTF-8'} {l s='Product left in the stock' mod='savecartforlater'}
                                            </span>
                                        </p>
                                        {else}
                                            {$prod.quantity|escape:'htmlall':'UTF-8'}
                                        {/if}
                                    {else}
                                        {$prod.quantity|escape:'htmlall':'UTF-8'}
                                    {/if}
                                    </td>
                                    <td>
                                        {if $prod.active}
                                            {if $prod.quantity > 0}
                                                {l s='In Stock' mod='savecartforlater'}
                                            {else}
                                                {l s='Out of Stock' mod='savecartforlater'}
                                            {/if}
                                        {else}
                                            {l s='Out of Stock' mod='savecartforlater'}
                                        {/if}
                                    </td>
                                    <td>
                                        <form method="post" id="presta-form-{$prod.id_product|escape:'htmlall':'UTF-8'}-{$key+1|escape:'htmlall':'UTF-8'}" name="presta-form-{$prod.id_product|escape:'htmlall':'UTF-8'}-{$key+1|escape:'htmlall':'UTF-8'}">
                                            <div class="product-line-info">
                                                {foreach from=$prod.attributes key="attribute" item="value"}
                                                    <input
                                                        type="hidden"
                                                        name="group[{$attribute|escape:'htmlall':'UTF-8'}]"
                                                        value="{$value.id_attribute|escape:'htmlall':'UTF-8'}">
                                                {/foreach}
                                                <input type="hidden" value="{$static_token|escape:'htmlall':'UTF-8'}" name="token">
                                                <input id="product_page_product_id" type="hidden" value="{$prod.id|escape:'htmlall':'UTF-8'}" name="id_product">
                                                <input id="product_customization_id" type="hidden" value="0" name="id_customization">
                                                <input id="product_qty" type="hidden" value="{$prod.cust_qty|escape:'htmlall':'UTF-8'}" name="qty">
                                            </div>
                                        <a
                                            class="presta-cart-form"
                                            data-key="{$key+1|escape:'htmlall':'UTF-8'}"
                                            title="{l s='Add to cart' mod='savecartforlater'}"
                                            data-id-product= "{$prod.id|escape:'htmlall':'UTF-8'}"
                                            data-id-product-attribute="{$prod.id_product_attribute|escape:'htmlall':'UTF-8'}"
                                            href="javascript:void(0);">
                                            <i class="material-icons">restore</i>
                                        </a>
                                        <a
                                            id="presta-data-delete"
                                            data-controller="mycart-controller"
                                            data-id-product= "{$prod.id_product|escape:'htmlall':'UTF-8'}"
                                            data-id-product-attribute="{$prod.id_product_attribute|escape:'htmlall':'UTF-8'}"
                                            title="{l s='Delete' mod='savecartforlater'}"
                                            href="javascript:void(0);">
                                            <i class="material-icons pull-xs-left">delete</i>
                                        </a>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                        {/if}
                    </tbody>
                </table>
            </div>
            {if !isset($products)}
                <p>{l s='No product found!' mod='savecartforlater'}</p>
            {/if}
        </section>
        <div class="presta-loader hidecontent">
            <img src="{$modules_dir|escape:'htmlall':'UTF-8'}savecartforlater/views/img/loading.gif" width="20px;"/>
        </div>
    </section>
{/block}
