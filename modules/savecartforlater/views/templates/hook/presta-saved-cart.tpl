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

{block name='content'}
{if isset($nocartproduct)}
    {if Configuration::get('PRESTA_SAVE_CART_SHARE')}
        <div class="presta-share">
            <a class="p-share btn btn-primary" href="javascript:void(0);" id="presta-share-cart">
                {l s='Share Cart' mod='savecartforlater'}
            </a>
        </div>
    {/if}
{/if}
<div class="presta-share-div">
    {block name='cart_detailed_product_line'}
        {include file='module:savecartforlater/views/templates/hook/presta-share.tpl'}
    {/block}
</div>
<div class="clearfix"></div>
{if isset($products) && $products}
<section id="main">
    <div class="cart-grid row">
        <div class="cart-grid-body col-xs-12 col-lg-12">
            <div class="card cart-container">
                <div class="card-block clearfix">
                    <h3 class="h3">{l s='Saved for later' mod='savecartforlater'}</h3>
                </div>
                <hr>
                <div class="presta-cart-overview js-cart">
                    <ul class="cart-items">
                        {foreach from=$products item=product}
                            <li class="cart-item">
                            {block name='cart_detailed_product_line'}
                                {include file='module:savecartforlater/views/templates/hook/presta-cart-detail-product-line.tpl' product=$product}
                            {/block}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="presta-loader hidecontent">
        <img src="{$modules_dir|escape:'htmlall':'UTF-8'}savecartforlater/views/img/loading.gif" width="20px;"/>
    </div>
</section>
{/if}
{/block}
