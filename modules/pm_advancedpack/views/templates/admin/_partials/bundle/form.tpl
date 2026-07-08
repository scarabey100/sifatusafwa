<div class="collapse" id="advancedpack-bundle-form" data-action="{$saveAjaxURL}" data-action-refresh="{$refreshAjaxURL}">

    <div class="card card-block">
        <h4><b>{l s='Base settings' mod='pm_advancedpack'}</b></h4>

        <div class="row">
            <div class="col-md-2">
                <fieldset class="form-group">
                    <label>{l s='Quantity' d='Admin.Global'} <small class="required">*</small></label>

                    <div class="input-group">
                        <input id="bundle-input-quantity" type="text" inputmode="numeric" pattern="\d*"
                            name="bundle[quantity]" class="js-comma-transformer form-control" min="1" value="1">

                        <div class="input-group-append">
                            <span class="input-group-text">{l s='Unit(s)' mod='pm_advancedpack'}</span>
                        </div>
                    </div>

                    <span data-rel="bundle_quantity" class="hide small js-error font-secondary text-danger"></span>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="form-group" id="bundle_packaging_names">
                    <label>{l s='Packaging name' mod='pm_advancedpack'} <small class="required">*</small></label>

                    <div class="translations tabbable">
                        <div class="translationsFields tab-content">
                            {foreach from=$languages item=language}
                                <div
                                    {if $shopUsesNewProductPage}
                                        class="js-locale-input js-locale-{$language.iso_code} {if $language.iso_code eq $default_language_iso}{else} d-none{/if}">
                                    {else}
                                        class="translation-field translation-label-{$language.iso_code} {if $language.iso_code eq $default_language_iso} show active{/if}">
                                    {/if}
                                    <input id="bundle-input-packaging-{$language.iso_code}"
                                        placeholder="{l s='jars, bottles, ...' mod='pm_advancedpack'}"
                                        type="text" name="bundle[packaging][{$language.id_lang|intval}]"
                                        class="form-control">
                                </div>
                            {/foreach}
                        </div>
                    </div>

                    <span data-rel="bundle_packaging_name" class="hide small js-error font-secondary text-danger"></span>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="form-group">
                    <label>{l s='Bundle name' mod='pm_advancedpack'}</label>

                    <div class="translations tabbable">
                        <div class="translationsFields tab-content">
                            {foreach from=$languages item=language}
                                <div
                                    {if $shopUsesNewProductPage}
                                        class="js-locale-input js-locale-{$language.iso_code} {if $language.iso_code eq $default_language_iso}{else} d-none{/if}">
                                    {else}
                                        class="translation-field translation-label-{$language.iso_code} {if $language.iso_code eq $default_language_iso} show active{/if}">
                                    {/if}
                                    <input id="bundle-input-name-{$language.iso_code}" type="text"
                                        name="bundle[name][{$language.id_lang|intval}]" class="form-control">
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3">
                <fieldset class="form-group">
                    <label>{l s='Badge' mod='pm_advancedpack'} </label>

                    <select id="pack-bundle-badges" name="bundle[badge]">
                        <option value="0">{l s='N/A' mod='pm_advancedpack'}</option>
                        {foreach from=$featureValues item=featureValue}
                            <option value="{$featureValue.id_feature_value|intval}">{$featureValue.value}</option>
                        {/foreach}
                    </select>

                    <span class="small font-secondary">
                        <a href="{$link->getAdminLink('AdminFeatures', true, [], ['viewfeature' => true, 'id_feature' => $featureId])}"
                            target="_blank" class="btn sensitive px-0"><i
                                class="material-icons">open_in_new</i>{l s='Manage' mod='pm_advancedpack'}
                        </a>
                    </span>
                </fieldset>
            </div>
        </div>
        <br>
        <div class="row">
            {if is_array($productCombinations) && count($productCombinations)}
                <div class="col-md-6">
                    <h4><b>{l s='Combinations' d='Admin.Catalog.Feature'}</b></h4>

                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="form-group ">
                                <label>
                                    <small
                                        class="form-control-label">{l s='Choose the related combination of the bundle' mod='pm_advancedpack'}
                                        :</small>
                                </label>

                                <div class="combinations">
                                    <select id="pack-bundle-combinations" name="bundle[combination]">
                                        {foreach from=$productCombinations item=productCombination}
                                            <option value="{$productCombination.id_product_attribute|intval}" data-price-impact="{$productCombination.price|floatval}">{$productCombination.attribute_designation}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="bundle[combination]" value="0" />
            {/if}

            <div class="col-md-{if is_array($productCombinations) && count($productCombinations)}6{else}12{/if}">
                <h4><b>{l s='Cover' d='Admin.Catalog.Feature'}</b></h4>

                <div class="row">
                    <div class="col-md-12">
                        <fieldset class="form-group ">
                            <label>
                                <small
                                    class="form-control-label">{l s='Choose the related image of the bundle' mod='pm_advancedpack'}
                                    :</small>
                            </label>

                            <div class="images">
                                {foreach from=$productImages item=productImage}
                                    <div class="product-image {*img-highlight*}">
                                        <input type="checkbox" name="bundle[image]" id="bundle-input-image-{$productImage.id_image|intval}" value="{$productImage.id_image|intval}"
                                            {*checked="checked"*}>
                                        <img
                                            src="{$link->getImageLink('image', $productImage.id_image, ImageType::getFormattedName('small'))}">
                                    </div>
                                {/foreach}
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <h4><b>{l s='Price settings' mod='pm_advancedpack'}</b> <small class="required">*</small></h4>

        <div class="row">
            <div class="col-md-3">
                <fieldset class="form-group">
                    <label>{l s='Product price (tax excl.)' d='Admin.Catalog.Feature'}</label>

                    <div class="input-group money-type">
                        <input type="text" id="bundle-price"
                            name="bundle[price]" class="js-depending js-comma-transformer price form-control">
                        <div class="input-group-append">
                            <span class="input-group-text"> €</span>
                        </div>
                    </div>

                    <span data-rel="bundle_price" class="hide small js-error font-secondary text-danger"></span>

                    <span class="small font-secondary">{l s='Base price (tax excl.)' mod='pm_advancedpack'} : <strong><span id="bundle-base-price"></span></strong> {l s='tax excl.' d='Admin.Global'}</span>
                </fieldset>
            </div>
            <div class="col-md-1 or-clause">
                {l s='OR' mod='pm_advancedpack'}
            </div>
            <div class="col-md-3">
                <fieldset class="form-group">
                    <label>{l s='Reduction'  d='Admin.Catalog.Feature'} </label>

                    <input type="text" id="bundle-reduction-amount"
                        name="bundle[reduction][amount]"
                        class="js-depending js-comma-transformer price form-control">

                        <span data-rel="bundle_reduction_amount" class="hide small js-error font-secondary text-danger"></span>
                </fieldset>
            </div>
            <div class="col-md-2">
                <fieldset class="form-group">
                    <label>&nbsp;</label>

                    <select id="bundle-reduction-type"
                        name="bundle[reduction][type]" class="custom-select">
                        <option value="amount">{$currency->getSign()}</option>
                        <option value="percentage">%</option>
                    </select>
                </fieldset>
            </div>
            <div class="col-md-2">
                <fieldset class="form-group">
                    <label>&nbsp;</label>

                    <select id="bundle-reduction-taxes"
                        name="bundle[reduction][taxes]" class="custom-select">
                        <option value="0" selected="selected">{l s='Tax excluded' d='Admin.Global'}</option>
                        {*<option value="1">{l s='Tax included' d='Admin.Global'}</option>*}
                    </select>
                </fieldset>
            </div>
            <div class="col-md-1">
            </div>
        </div>

        <br>

        <div class="col-md-12 text-sm-right">
            <input type="hidden" id="bundle-id" name="bundle[id]" value="0" />
            <button type="button" name="bundle[cancel]" class="btn-outline-secondary js-cancel btn">
                {l s='Cancel' d='Admin.Actions'}
            </button>
            <button type="button" name="bundle[save]" class="btn-outline-primary js-save btn">
                {l s='Apply' d='Admin.Actions'}
            </button>
        </div>
        <div class="clearfix"></div>
    </div>

</div>
