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
{if isset($errors) && $errors && !$PA_CAPTCHA_ERROR_IS_FIXED}
    <div class="pa_captcha_log_install alert alert-success">
        <h4>{l s='Congratulations! You have installed CAPTCHA successfully.' mod='ets_advancedcaptcha'}</h4>
        <p>{l s='However, there are a few files/functions that could not be overridden during the installation process (they might have been already overridden by another module).' mod='ets_advancedcaptcha'}</p>
        <p>{l s='Please check the installation log below and manually copy the files/functions from directory "root/modules/ets_advancedcaptcha/override/" to "root/override"' mod='ets_advancedcaptcha'}</p>
        <p>{l s='to make sure all features of the CAPTCHA works properly' mod='ets_advancedcaptcha'}</p>
        <ul class="pa_captcha_logs alert-warning">
            {foreach from=$errors item='log'}
                {if $log}{foreach from=$log item='lg'}<li class="ybc_ins_item_log">{$lg|escape:'html':'UTF-8'}</li>{/foreach}{/if}
            {/foreach}
        </ul>
        <div class="pa_captcha_log_actions">
            <button id="pa_captcha_button_yes" class="btn btn-primary" name="pa_captcha_button_yes">{l s='Yes, I have fixed all these issues' mod='ets_advancedcaptcha'}</button>
            <button id="pa_captcha_clear_log" class="btn btn-default" name="pa_captcha_clear_log">{l s='Clear installation logs' mod='ets_advancedcaptcha'}</button>
        </div>
    </div>
{elseif $PA_CAPTCHA_ERROR_IS_FIXED}
    <div class="pa_captcha_log_install alert alert-info">
        <p>{l s='Module was successfully installed with warnings.' mod='ets_advancedcaptcha'}</p>
        <a class="pa_captcha_view_log" href="{$link|escape:'html':'UTF-8'}" target="_blank">{l s='View installation log' mod='ets_advancedcaptcha'}</a>
        <a class="pa_captcha_clear_log" href="{$smarty.server.REQUEST_URI|escape:'quotes':'UTF-8'}&pa_captcha_clear_log=1">{l s='Clear installation log' mod='ets_advancedcaptcha'}</a>
    </div>
{/if}