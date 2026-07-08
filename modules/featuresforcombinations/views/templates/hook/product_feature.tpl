{*
* 2007-2025 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row product-feature">
    <div class="col-lg-12 col-xl-4">
        <fieldset class="form-group mb-0">
            <label class="form-control-label">{l s='Feature' mod='featuresforcombinations'}</label>
            <select
                    id="ffc_{$id_product_attribute|intval}_{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature"
                    name="ffc[{$id_product_attribute|intval}][{$ffc_iter|escape:'htmlall':'UTF-8'}][id_feature]"
                    class="ffc-feature-selector custom-select form-control"
            >
                <option value="">{l s='Choose a feature' mod='featuresforcombinations'}</option>
                {foreach from=$ffcFeaturesProduct item=featureProduct}
                    <option value="{$featureProduct.id_feature|escape:'quotes':'UTF-8'}"{if $featureProduct.id_feature == $ffc_value.id_feature} selected{/if}>
                        {$featureProduct.name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </fieldset>
    </div>

    <div class="col-lg-12 col-xl-4">
        <fieldset class="form-group mb-0">
            <label class="form-control-label">{l s='Predefined value' mod='featuresforcombinations'}</label>

            <select
                    id="ffc_{$id_product_attribute|intval}_{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature_value"
                    name="ffc[{$id_product_attribute|intval}][{$ffc_iter|escape:'htmlall':'UTF-8'}][id_feature_value]"
                    {if $ffc_value.id_feature_value == 0}disabled="disabled"{/if}
                    class="ffc-feature-value-selector custom-select form-control"
            >
                <option value="">{l s='Choose a value' mod='featuresforcombinations'}</option>
                {foreach from=$ffc_value.predefined_values item=predefined_value}
                    <option value="{$predefined_value.id_feature_value|escape:'quotes':'UTF-8'}"{if !$ffc_value.custom && $predefined_value.id_feature_value == $ffc_value.id_feature_value} selected{/if}>{$predefined_value.value|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>

        </fieldset>
    </div>

    <div class="col-lg-11 col-xl-3">
        <fieldset class="form-group mb-0">
            <label class="form-control-label">{l s='OR custom value' mod='featuresforcombinations'}</label>
            <div class="translations tabbable"
                 id="ffc_{$id_product_attribute|intval}_{$ffc_iter|escape:'htmlall':'UTF-8'}_custom_value">
                <div class="translationsFields tab-content">
                    {foreach from=$ffc_languages item=ffc_language}
                        <div data-locale="{$ffc_language.iso_code|escape:'htmlall':'UTF-8'}"
                             class="translationsFields-form_modules_ffc_{$id_product_attribute|intval}_custom_value tab-pane translation-field{if $ffc_default_language == {$ffc_language.id_lang|intval}} show active{/if} translation-label-{$ffc_language.iso_code|escape:'htmlall':'UTF-8'}">
                            <input
                                    type="text"
                                    id="ffc_{$id_product_attribute|intval}_{$ffc_iter|escape:'htmlall':'UTF-8'}_custom_value_{$ffc_language.id_lang|intval}"
                                    name="ffc[{$id_product_attribute|intval}][{$ffc_iter|escape:'htmlall':'UTF-8'}][custom_value][{$ffc_language.id_lang|intval}]"
                                    class="form-control form-control"
                                    {if $ffc_value.custom}value="{$ffc_custom_values[$ffc_value.id_feature_value][$ffc_language.id_lang]|escape:'quotes':'UTF-8'}"{/if} />
                        </div>
                    {/foreach}
                </div>
            </div>
        </fieldset>
    </div>
    <div class="col-lg-1 col-xl-1">
        <a class="btn btn-invisible tooltip-link delete pl-0 pr-0"><i class="material-icons">delete</i></a>
    </div>

</div>
<hr class="mb-2 d-xl-none"/>
