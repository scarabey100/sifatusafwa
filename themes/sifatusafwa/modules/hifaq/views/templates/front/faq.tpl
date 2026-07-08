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
{capture name=path}
    {l s='FAQs' mod='hifaq'}
{/capture}
<div class="hi-faq-main-page">
    {if isset($main_page_description) && $main_page_description}
        <div class="hi-faq-page-description row">
            <div class="col-lg-12">
                {$main_page_description nofilter}
            </div>
        </div>
    {/if}
    <div class="faq-container faq_category">
        {if $categories}
            {if $faqs_layout == 1}
                {assign var="layout_cols" value="12"}
            {else if ($faqs_layout == 2)}
                {assign var="layout_cols" value="6"}
            {else}
                {assign var="layout_cols" value="4"}
            {/if}
            <div class="hi-faq-category-row">
                {foreach from=$categories item=category name=cat}
                    {if $category.faqs}
                        <div class="hi-faq-category-item col-lg-{$layout_cols|intval}">
                            <h2><a href="{$category.url|escape:'html':'UTF-8'}">{$category.name|escape:'html':'UTF-8'}</a></h2>
                            <div class="hi-faq-category-faqs">
                                <ul>
                                    {foreach $category.faqs as $faq}
                                        <li>
                                            <a href="{$faq.url|escape:'html':'UTF-8'}">{$faq.question|escape:'html':'UTF-8'}</a>
                                        </li>
                                    {/foreach}
                                    {if $category.faqs_count > $faqs_count}
                                        <li class="hi-faq-view-all-questions"><a href="{$category.url|escape:'html':'UTF-8'}">{l s='View all questions' mod='hifaq'}</a></li>
                                    {/if}
                                </ul>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        {/if}
    </div>
</div>
