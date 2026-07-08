{*
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 *}

<div class="panel tab-pane fade {if $section == 'promotional-banners-settings'}active in{/if}" 
     id="promotional-banners-settings" 
     role="tabpanel" 
     aria-labelledby="promotional-banners-settings-tab">
    <div>
        <div class="form-wrapper">
            
            <!-- Revolut Sign-up Rewards Banner -->
            
             <div class="form-group">
                <label class="control-label col-lg-3">{l s='Enable Revolut Reward banner' mod='revolutpayment'}</label>
                <div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="REVOLUT_SIGNUP_BANNER_ENABLE" id="REVOLUT_SIGNUP_BANNER_ENABLE_on" value="1"
                               {if $REVOLUT_SIGNUP_BANNER_ENABLE|intval == 1}checked="checked"{/if} />
						<label for="REVOLUT_SIGNUP_BANNER_ENABLE_on">{l s='Yes' mod='revolutpayment'}</label>
						<input type="radio" name="REVOLUT_SIGNUP_BANNER_ENABLE" id="REVOLUT_SIGNUP_BANNER_ENABLE_off"
                               value="0" {if $REVOLUT_SIGNUP_BANNER_ENABLE|intval == 0}checked="checked"{/if} />
						<label for="REVOLUT_SIGNUP_BANNER_ENABLE_off">{l s='No' mod='revolutpayment'}</label>
						<a class="slide-button btn"></a>
					</span>
                    <p class="help-block">{l s='Offer your customers to join Revolut where they will receive Revolut funded rewards for signing up. Enabling this banner boosts payment conversion on average by 5%' mod='revolutpayment'}</p>
                </div>
            </div>
            
            <!-- Revolut Pay Benefits Banner -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Enable Revolut Pay benefits banner?' mod='revolutpayment'}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" 
                               name="REVOLUT_BENEFITS_BANNER_ENABLE" 
                               id="REVOLUT_BENEFITS_BANNER_ENABLE_on" 
                               value="1" 
                               {if $REVOLUT_BENEFITS_BANNER_ENABLE|intval == 1}checked="checked"{/if} />
                        <label for="REVOLUT_BENEFITS_BANNER_ENABLE_on">
                            {l s='Yes' mod='revolutpayment'}
                        </label>
                        <input type="radio" 
                               name="REVOLUT_BENEFITS_BANNER_ENABLE" 
                               id="REVOLUT_BENEFITS_BANNER_ENABLE_off" 
                               value="0" 
                               {if $REVOLUT_BENEFITS_BANNER_ENABLE|intval == 0}checked="checked"{/if} />
                        <label for="REVOLUT_BENEFITS_BANNER_ENABLE_off">
                            {l s='No' mod='revolutpayment'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">
                        {l s='This allows your customers to open a pop-up with more details on the payment process and available benefits.' mod='revolutpayment'}
                    </p>
                </div>
            </div>
            
            <!-- Revolut Pay Informational Icon -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Revolut Pay informational icon' mod='revolutpayment'}
                </label>
                <div class="col-lg-9">
                    <select name="REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT" class="fixed-width-xl">
                        {foreach from=$REVOLUT_PAY_INFORMATIONAL_ICON_VARIANTS item='revolut_pay_informational_icon_variant'}
                            <option value="{$revolut_pay_informational_icon_variant.id|escape:'htmlall':'UTF-8'}" 
                                    {if $REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT == $revolut_pay_informational_icon_variant.id}selected="selected"{/if}>
                                {$revolut_pay_informational_icon_variant.name|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                    <p class="help-block">
                        {l s='Displays an icon or a "Learn more" link which opens a pop-up with details on the Revolut Pay payment process and benefits.' mod='revolutpayment'}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="panel-footer">
            <button type="submit" 
                    value="1" 
                    id="configuration_form_submit_btn" 
                    name="submitrevolutpayment" 
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='revolutpayment'}
            </button>
        </div>
    </div>        
</div>
