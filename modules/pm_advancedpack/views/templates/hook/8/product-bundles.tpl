{*
*
* Advanced Pack
*
* @author Presta-Module.com <support@presta-module.com>
* @copyright Presta-Module
*
*           ____     __  __
*          |  _ \   |  \/  |
*          | |_) |  | |\/| |
*          |  __/   | |  | |
*          |_|      |_|  |_|
*
*}
<div class="tabs">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <span class="nav-link active js-product-nav-active">{l s='Bundles' mod='pm_advancedpack'}</span>
        </li>
    </ul>

    <div class="tab-content">
        <div class=" tab-pane fade in active show">
            <div class="product-quantities">
                <section class="bundle-products">
                    {foreach from=$bundles item=bundle name=bundle}
                        <div class="bundle-wrapper">
                            <div class="bundle"
                                data-quantity="{$bundle.quantity|intval}"
                                data-product="{$bundle.id_pack|intval}"
                                data-main-product="{$productId|intval}"
                                data-add-to-cart-url={$bundle.addToCartUrl}
                                {if isset($bundle.image.link)}data-image="{$bundle.image.link}" {/if}>
                                <span class="ap5-bundle-product-icon-remove"></span>
                                <div class="bundle-detail">
                                    {if isset($bundle.datas->packaging)}<p class="contains">{$bundle.quantity|intval}
                                        {$bundle.datas->packaging->$language.id}</p>{/if}
                                    {if isset($bundle.datas->name)}<p class="title">{$bundle.datas->name->$language.id}</p>
                                    {/if}
                                    <p class="{if $bundle.quantity eq 1 || $bundle.productPriceTaxesExcluded == $bundle.bundlePriceTaxesExcluded}base {/if}price">
                                        {if !$priceDisplay || $priceDisplay == 2}
                                            <span>{$bundle.bundlePriceTaxesIncluded}</span>
                                        {elseif $priceDisplay == 1}
                                            <span>{$bundle.bundlePriceTaxesExcluded}</span>
                                        {/if}
                                    </p>
                                    {if $bundle.quantity > 1}
                                        {if $bundle.productPriceTaxesExcluded != $bundle.bundlePriceTaxesExcluded}
                                            <p class="old-price">
                                                {if !$priceDisplay || $priceDisplay == 2}
                                                    <span>{$bundle.productPriceTaxesIncluded}</span>
                                                {elseif $priceDisplay == 1}
                                                    <span>{$bundle.productPriceTaxesExcluded}</span>
                                                {/if}
                                            </p>
                                        {/if}
                                        {if $bundle.badge_name != 'N/A'}
                                            <div class="badge">{$bundle.badge_name}</div>
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </section>
            </div>
        </div>
    </div>
</div>
