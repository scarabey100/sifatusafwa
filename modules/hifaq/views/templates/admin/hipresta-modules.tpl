{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
 <div class="col-lg-12 hipresta-modules-ad">
    <div class="clearfix">
        {foreach from=$modules item=module}
            {if $module.name != 'hifaq'}
                <div class="module-item module-item-grid col-md-12 col-lg-4 col-xl-3">
                    <div class="module-item-wrapper-grid">
                        <div class="module-item-heading-grid">
                            <div class="module-logo-thumb-grid">
                                <img src="{$module.image_link|escape:'html':'UTF-8'}" alt="{$module.display_name|escape:'htmlall':'UTF-8'}">
                            </div>
                            <h3 title="{$module.display_name|escape:'htmlall':'UTF-8'}" class="text-ellipsis module-name-grid">
                                <span>{$module.display_name|escape:'htmlall':'UTF-8'}</span>
                            </h3>
                        </div>
                        <div class="module-quick-description-grid no-padding mb-0">
                            {if isset($module.desc_short) && $module.desc_short}
                                <div class="module-quick-description-text">
                                    {$module.desc_short|escape:'htmlall':'UTF-8'}
                                    <span>...</span>
                                </div>
                            {/if}
                            <div class="module-read-more-grid">
                                <a href="{$module.link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Read more' mod='hifaq'}</a>
                            </div>
                        </div>
                        <div class="module-container module-quick-action-grid clearfix">
                            <div class="badges-container">
                                <div>
                                    <img src="https://hipresta.com/images/hipresta.jpg">
                                    <span>{l s='Made by HiPresta' mod='hifaq'}</span></div>
                                </div>
                            <hr>
                            <div class="float-right module-price"><span>{$module.price|escape:'htmlall':'UTF-8'}</span></div>
                            <div class="form-action-button-container">
                                <a href="{$module.link|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-primary btn-primary-reverse btn-block btn-outline-primary light-button module_action_menu_go_to_addons">
                                {l s='Discover' mod='hifaq'}</a>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
</div>
<div class="clear"></div>
