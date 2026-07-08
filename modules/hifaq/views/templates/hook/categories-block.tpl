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
<div class="hi-faq-block {if $psv < 1.7}block{else}hi-faq-block-17{/if}">
    {if $title_active}
        {if $psv >= 1.7}
            <p class="text-uppercase h6">{$title|escape:'htmlall':'UTF-8'}</p>
        {else}
            <h2 class="title_block">{$title|escape:'html':'UTF-8'}</h2>
        {/if}
    {/if}
    <ul>
        {foreach from=$faqCategories item='faqCategory'}
            <li><a href="{$faqCategory.url|escape:'html':'UTF-8'}">{$faqCategory.name|escape:'htmlall':'UTF-8'} ({$faqCategory.faqs_count|intval})</a></li>
        {/foreach}
    </ul>
</div>
