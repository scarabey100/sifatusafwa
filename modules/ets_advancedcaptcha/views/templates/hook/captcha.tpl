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
<{if $is16}div{else}p{/if} class="captcha_{$posTo|escape:'html':'UTF-8'} {if $is16}form-group row required{else}text{/if} {if isset($posTo) && $posTo}{$posTo|escape:'quotes'}{/if} page_{$captcha_page|escape:'html':'UTF-8'} ver1{if $is17}7{elseif $is16}6{else}5{/if}">
    {if $PA_CAPTCHA_TYPE != 'google_v3'}
        <label {if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE == 'google'}data-typ-google="google"{/if} for="pa_captcha" {if $is16}class="col-md-3"{/if}>
            {if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}
                {l s='Enter security code' mod='ets_advancedcaptcha'}
            {else}
                {if isset($PA_GOOGLE_CAPTCHA_LABEL) && $PA_GOOGLE_CAPTCHA_LABEL}{$PA_GOOGLE_CAPTCHA_LABEL|escape:'html':'UTF-8'}{/if}
            {/if} {if $captcha_page=='registration'}<sub>*</sub>{/if}
        </label>
    {else}
        <label {if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE == 'google'}data-typ-google="google"{/if} for="pa_captcha" class="col-md-3"></label>
    {/if}
    <{if $is16}div{else}span{/if} class="pa-captcha-inf {if $is16}col-md-6{else}pa-captcha-field-cell{/if}">
        {if $PA_CAPTCHA_TYPE != 'google' && $PA_CAPTCHA_TYPE != 'google_v3'}
            <input type="hidden" name="posTo" value="{$posTo|escape:'html':'UTF-8'}">
            <span class="pa_captcha_img">
                <img class="pa_captcha_img_data {$posTo|escape:'html':'UTF-8'}" src="{$captcha_image|escape:'html':'UTF-8'}" data-rand="{$rand|escape:'html':'UTF-8'}" alt="{l s='Security code' mod='ets_advancedcaptcha'}" title="{l s='Security code' mod='ets_advancedcaptcha'}" />
                <span id="pa_captcha_refesh">
                    <img title="{l s='Refresh the code' mod='ets_advancedcaptcha'}" alt="{l s='Refresh the code' mod='ets_advancedcaptcha'}" src="{$modules_dir|escape:'html':'UTF-8'}ets_advancedcaptcha/views/img/refresh.png" />
                </span>
            </span>
            <input type="text" name="pa_captcha" {if $is16}class="form-control grey"{/if}/>
        {elseif $PA_CAPTCHA_TYPE == 'google_v3'}
            <div id="g-recaptcha-response-{rand()|intval}"></div>
        {elseif $PA_CAPTCHA_TYPE == 'google'}
            <div class="g-recaptcha"></div>
        {/if}
    </{if $is16}div{else}span{/if}>
    {if $is16}<div class="col-md-3 form-control-comment"></div>{/if}
</{if $is16}div{else}p{/if}>