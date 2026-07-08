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
<div class="hi-faq-top-search-container">
    <div class="hi-faq-top-search-content">
        <h2>{l s='How can we help you?' mod='hifaq'}</h2>
        <div class="hi-faq-search-container" id="hi-faq-search-container">
            <form method="get" action="{$searchPageUrl|escape:'html':'UTF-8'}" id="hi-faq-search-bar-form">
                <div class="hi-faq-search-bar-input-group">
                    {if $icons == 'material'}
                        <i class="material-icons search"></i>
                    {else}
                        <i class="fa fa-search"></i>
                    {/if}
                    <input type="text" name="faqQuery" class="form-control hi-faq-top-search-input" id="hi_faq_top_search_input" placeholder="{l s='Type keywords to find answers' mod='hifaq'}" autocomplete="off">
                </div>
            </form>

            <div class="hi-faq-search-results"></div>
        </div>

        <div class="hi-faq-top-search-note">{l s='You can also browse the topics below to find what you are looking for.' mod='hifaq'}</div>
    </div>
</div>