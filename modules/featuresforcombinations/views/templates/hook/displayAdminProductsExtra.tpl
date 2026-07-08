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

<div class="panel">
    <div class="ffc-features2 mb-3 panel-body">
        <h1 style="margin-bottom: 2em;">{l s='Features' mod='featuresforcombinations'}</h1>

        {foreach from=$ffc_product_attributes item=product_attribute}
            <div class="ffc-features border">
                <h2>{$product_attribute.name|escape:'htmlall':'UTF-8'}</h2>
                <div
                        class="ffc-features-content content"
                        data-prototype="{$ffc_prototypes[$product_attribute.id_product_attribute]|escape:'htmlall':'UTF-8'}"
                        data-iter="{$ffc_features[$product_attribute.id_product_attribute]|count|intval}"
                >
                    <div class="ffc-feature-collection nostyle">
                        {foreach
                        from=$ffc_features[$product_attribute.id_product_attribute]
                        item=ffc_feature
                        key=ffc_key
                        }
                            {include
                            file='./product_feature171.tpl'
                            ffc_value=$ffc_feature
                            ffc_custom_values=$ffc_custom_values
                            ffc_iter=$ffc_key|intval
                            id_product_attribute=$product_attribute.id_product_attribute|intval}
                        {/foreach}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <button type="button"
                                class="btn btn-outline-primary btn-primary-outline sensitive ffc-add-feature ffc_add_feature_button">
                            <i class="material-icons">add_circle</i> {l s='Add a feature' mod='featuresforcombinations'}
                        </button>
                    </div>
                </div>
                <hr/>
            </div>
        {/foreach}
    </div>
</div>

<style>
    .product-feature > div {
        padding-right: 1em !important;
        padding-left: 1em !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .pl-0 {
        padding-left: 0 !important;
    }

    .pr-0 {
        padding-right: 0 !important;
    }
</style>
