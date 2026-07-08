{foreach from=$packContent item='productPack' key='idProductPackKey'}
    {assign var=imageIds value="`$productPack.id_product`-`$productPack.image.id_image`"}
    {assign var=imageRewrite value=$productPack.presentation.link_rewrite}
    {if empty($imageRewrite)}
        {assign var=imageRewrite value=$productPack.id_product}
    {/if}
    {if !empty($productPack.image.legend)}
        {assign var=imageTitle value=$productPack.image.legend}
    {else}
        {assign var=imageTitle value=$productPack.presentation.name}
    {/if}
    {assign var=idProductPack value=$productPack.id_product_pack}
    <div class="col-3">
        <div id="ap5_packRow-{$idProductPack}"
            data-id-product-pack="{$idProductPack}" class="card">
            <div class="card-header">
                <a title="{$imageTitle}" href="{$productPack.presentation.url}" target="_blank">
                    <img class="img-fluid d-block mx-auto" id="thumb_{$productPack.image.id_image|intval}"
                        src="{$link->getImageLink($imageRewrite, $imageIds, $imageFormatProductCover)}"
                        alt="{$imageTitle}" title="{$imageTitle}" height="{$imageFormatProductCoverHeight}"
                        width="{$imageFormatProductCoverWidth}" itemprop="image" />
                </a>
            </div>
            <div class="card-body">
                <a target="_blank" href="{$productPack.presentation.url}" title="{$productPack.presentation.name}"
                    itemprop="url">
                    {$productPack.quantity|intval} x {$productPack.presentation.name}
                </a>
                <br />
                {l s='Ref:' mod='pm_advancedpack'} {$productPack['productObj']->reference}
            </div>
            {if !empty($productPack.productCombinations)}
                <div class="card-footer">
                    {l s='Combination:' mod='pm_advancedpack'}
                    <select id="ap5-pack-ipa-{$idProductPack}" name="ap5-pack-ipa-{$idProductPack}" data-id-product-pack="{$idProductPack}" class="ap5-product-combination form-control custom-select">
                    {foreach from=$productPack.productCombinations item='productCombination'}
                        {assign var=ap5_isDefaultCombination value=((isset($productPack.default_id_product_attribute) && (int)$productPack.default_id_product_attribute && (int)$productPack.default_id_product_attribute == (int)$productCombination.id_product_attribute) || (!isset($productPack.default_id_product_attribute) || !(int)$productPack.default_id_product_attribute) && (int)$productCombination.id_product_attribute == (int)Product::getDefaultAttribute($productPack.productObj->id))}
                        {assign var=ap5_combinationAvailableQuantity value=StockAvailable::getQuantityAvailableByProduct($productPack.productObj->id, $productCombination.id_product_attribute)|intval}
                        {assign var=ap5_idProductAttribute value=$productCombination.id_product_attribute}

                        <option value="{$ap5_idProductAttribute|intval}"{if $ap5_isDefaultCombination} selected="selected"{/if}>{l s='%s - Stock: %d' mod='pm_advancedpack' sprintf=[$productCombination.attribute_designation, $ap5_combinationAvailableQuantity|intval]}</option>
                    {/foreach}
                    </select>
                </div>
            {/if}
        </div>
    </div>
{/foreach}
