{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{extends file="helpers/form/form.tpl"}
{block name="defaultForm"}
    {if $old_version && !(isset($ik))}{assign var="ik" value=0}{/if}
    {$smarty.block.parent}
{/block}
{block name="legend"}
    {if $field.title}
        <div class="form-heading-wrapper">
            {$smarty.block.parent}
        </div>
    {/if}
{/block}
{block name="label"}
    {if $is15}{if isset($ik) && $ik == 0}<div class="form-wrapper">{/if}
    <div class="form-group-wrapper row_{$input.name|lower|escape:'html':'UTF-8'}" {if isset($configTabs) && isset($input.tab)} data-tab-id="{$input.tab|escape:'html':'UTF-8'}"{/if}>
        {/if}
        {if $input.type == 'pa_help'}{$input.html nofilter}{else}{$smarty.block.parent}{/if}
        {/block}
        {block name="field"}
        {if $input.type != 'pa_help'}{$smarty.block.parent}{/if}
        {if $is15}</div>{if isset($ik)}{assign var="ik" value=$ik+1}{if $ik == $field|count}</div>{/if}{/if}{/if}
{/block}
{block name="input"}
    {if $input.type == 'pa_img_radio'}
        {if isset($input.values) && $input.values}
            <ul class="ets_pa_options">
                {foreach from=$input.values item='option'}
                    <li class="ets_pa_item">
                        <div class="radio">
                            <label for="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}">
                                <input type="radio" style="outline: none;" id="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{$option.id_option|escape:'quotes':'UTF-8'}" {if !empty($fields_value[$input.name]) && $fields_value[$input.name] == $option.id_option}checked{/if}>
                                <img src="{$path|cat: 'views/img/'|cat: $option.img|escape:'html':'UTF-8'}">
                                {$option.name|escape:'html':'UTF-8'}
                            </label>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {/if}
    {elseif $input.type == 'pa_checkbox'}
        {if isset($input.values) && $input.values}
            <ul class="ets_pa_options">
                {foreach from=$input.values item='option'}
                    <li class="ets_pa_item">
                        <div class="checkbox">
                            <label for="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}">
                                <input type="checkbox" id="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}[]" value="{$option.id_option|escape:'quotes':'UTF-8'}" {if !empty($fields_value[$input.name]) && ( $fields_value[$input.name] == 'ALL' || (is_array($fields_value[$input.name]) && in_array($option.id_option, $fields_value[$input.name])) )}checked{/if} {if !empty($fields_value[$input.name]) && $fields_value[$input.name] == 'ALL' && $option.id_option != 'ALL'}disabled{/if}>
                                {$option.name nofilter}
                            </label>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {/if}
    {elseif $input.type == 'switch'}
        {if $is15}
            <span class="switch prestashop-switch fixed-width-lg">
                {foreach $input.values as $value}
                    <input type="radio" name="{$input.name|escape:'quotes':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'quotes':'UTF-8'}_on"{else} id="{$input.name|escape:'quotes':'UTF-8'}_off"{/if} value="{$value.value|intval}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}/>
                    {strip}
                    <label {if $value.value == 1} for="{$input.name|escape:'quotes':'UTF-8'}_on"{else} for="{$input.name|escape:'quotes':'UTF-8'}_off"{/if}>
                        {if $value.value == 1}{l s='Yes' mod='ets_advancedcaptcha'}{else}{l s='No' mod='ets_advancedcaptcha'}{/if}
                    </label>
                {/strip}
                {/foreach}
                <a class="slide-button btn"></a>
            </span>
        {else}
            {$smarty.block.parent}
        {/if}
    {elseif $input.name == 'PA_GOOGLE_CAPTCHA_SECRET_KEY'}
        {$smarty.block.parent}
        <p class="help-block">
            <a target="_blank" href="https://prestahero.com/help-center/general-configuration/290-create-recaptcha-keys-google-cloud" title="{l s='How to Create reCAPTCHA Keys in Google Cloud Console' mod='ets_advancedcaptcha'}">{l s='How to Create reCAPTCHA Keys in Google Cloud Console' mod='ets_advancedcaptcha'}</a>
        </p>
    {elseif $input.name == 'PA_GOOGLE_V3_CAPTCHA_SECRET_KEY'}
        {$smarty.block.parent}
        <p class="help-block">
            <a target="_blank" href="https://prestahero.com/help-center/general-configuration/290-create-recaptcha-keys-google-cloud" title="{l s='How to Create reCAPTCHA Keys in Google Cloud Console' mod='ets_advancedcaptcha'}">{l s='How to Create reCAPTCHA Keys in Google Cloud Console' mod='ets_advancedcaptcha'}</a>
        </p>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="input_row"}
    {if $old_version && isset($ik) && $ik == 0}<div class="form-wrapper">{/if}
    <div class="form-group-wrapper row_{$input.name|lower|escape:'html':'UTF-8'}" {if isset($configTabs) && isset($input.tab)} data-tab-id="{$input.tab|escape:'html':'UTF-8'}"{/if}>
        {if $input.type != 'pa_help'}{$smarty.block.parent}{else}{$input.html nofilter}{/if}
    </div>
    {if $old_version && isset($ik)}{assign var="ik" value=$ik+1}{if $ik == $field|count}</div>{/if}{/if}
{/block}
{block name="footer"}
    {if isset($log_install) && $log_install}{$log_install nofilter}{/if}
    {$smarty.block.parent}
{/block}