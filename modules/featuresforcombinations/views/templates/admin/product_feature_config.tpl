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

<div class="product-feature">
    <div class="row">
        <div class="col-lg-11 row">
            <div class="col-lg-5">
                <fieldset class="form-group mb-0">
                    <label class="form-control-label" for="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature">{l s='Feature' mod='featuresforcombinations'}</label>
                    <select
                            id="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature"
                            name="ffc[__PROTOTYPE_PA_ID__][{$ffc_iter|escape:'htmlall':'UTF-8'}][id_feature]"
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

            <div class="col-lg-6">
                <fieldset class="form-group mb-0">
                    <label class="form-control-label" for="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature_value">{l s='Predefined value' mod='featuresforcombinations'}</label>

                    <select
                            id="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_id_feature_value"
                            name="ffc[__PROTOTYPE_PA_ID__][{$ffc_iter|escape:'htmlall':'UTF-8'}][id_feature_value]"
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


{*            {foreach from=$ffc_languages item=ffc_language}*}
{*                <div class="col-lg-3">*}
{*                    <fieldset class="form-group mb-0">*}
{*                        <label class="form-control-label" for="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_custom_value">{l s='OR custom value' mod='featuresforcombinations'} ({$ffc_language.name})</label>*}
{*                        <div class="translations tabbable"*}
{*                             id="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_custom_value">*}
{*                            <div class="translationsFields tab-content">*}
{*                                <input*}
{*                                        disabled*}
{*                                        type="text"*}
{*                                        id="ffc___PROTOTYPE_PA_ID___{$ffc_iter|escape:'htmlall':'UTF-8'}_custom_value_{$ffc_language.id_lang|intval}"*}
{*                                        name="ffc[__PROTOTYPE_PA_ID__][{$ffc_iter|escape:'htmlall':'UTF-8'}][custom_value][{$ffc_language.id_lang|intval}]"*}
{*                                        class="custom-values form-control form-control"*}
{*                                        {if $ffc_value.custom}value="{$ffc_custom_values[$ffc_value.id_feature_value][$ffc_language.id_lang]|escape:'quotes':'UTF-8'}"{/if} />*}
{*                            </div>*}
{*                        </div>*}
{*                    </fieldset>*}
{*                </div>*}
{*            {/foreach}*}
        </div>

        <div class="col-lg-1">
            <button class="btn btn-invisible tooltip-link delete pl-0 pr-0 delete-feature-value"><i class="material-icons">delete</i></button>
        </div>
    </div>

    <hr class="mb-2 d-lg-none"/>
</div>

