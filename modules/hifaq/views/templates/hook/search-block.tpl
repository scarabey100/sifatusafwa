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
<div class="hi-faq-block hi-faq-search-block {if $psv < 1.7}block{else}hi-faq-block-17{/if}">
    {if $title_active}
        {if $psv >= 1.7}
            <p class="text-uppercase h6">{$title|escape:'html':'UTF-8'}</p>
        {else}
            <h2 class="title_block">{$title|escape:'html':'UTF-8'}</h2>
        {/if}
    {/if}
    <form method="get" action="{$search_url|escape:'html':'UTF-8'}">
        <input type="text" name="faqQuery" placeholder="{l s='Search for help' mod='hifaq'}" value="{$query|escape:'html':'UTF-8'}">
        {if !$ps_rewrite_settings}
            <input type="hidden" name="fc" value="module">
            <input type="hidden" name="module" value="hifaq">
            <input type="hidden" name="controller" value="faqsearch">
        {/if}
        <button type="submit">
            {if $icons == 'material'}
                <i class="material-icons search"></i>
            {else}
                <i class="fa fa-search"></i>
            {/if}
        </button>
    </form>
</div>
