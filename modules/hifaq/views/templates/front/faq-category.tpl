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
    {if $psv < 1.7}
        <a href="{$main_page_url nofilter}">{l s='FAQs' mod='hifaq'}</a>
        <span class="navigation-pipe">{$navigationPipe nofilter}</span>
        {if $faqCategory}
            <span class="navigation_page">{$faqCategory->name|escape:'html':'UTF-8'}</span>
        {/if}
    {/if}
{/capture}

<div class="hi-faq-category-page">
    <div class="hi-faq-page-description">
        <h1>{$faqCategory->name|escape:'html':'UTF-8'}</h1>
        {if $faqCategory->description}
            {$faqCategory->description nofilter}
        {/if}
    </div>

    {if $faqs}
        <div class="hi-faq-category-items">
            {foreach from=$faqs item=faq}
                <div class="hi-faq-item">
                    <div class="hi-faq-question">
                        <a href="{$faq.url|escape:'html':'UTF-8'}" class="hi-faq-question-link" data-id="{$faq.id_faq|intval}">
                            {if $icons == 'material'}
                                <i class="material-icons hi-faq-item-plus-icon">add</i>
                                <i class="material-icons hi-faq-item-minus-icon">remove</i>
                            {else}
                                <i class="fa fa-plus hi-faq-item-plus-icon"></i>
                                <i class="fa fa-minus hi-faq-item-minus-icon"></i>
                            {/if}
                            {$faq.question|escape:'html':'UTF-8'}
                        </a>
                        <a href="{$faq.url|escape:'html':'UTF-8'}" class="hi-faq-link">
                            {if $icons == 'material'}
                                <i class="material-icons">insert_link</i>
                            {else}
                                <i class="fa fa-link"></i>
                            {/if}
                        </a>
                    </div>
                    <div class="hi-faq-answer hi-faq-answer-{$faq.id_faq|intval}">
                        {$faq.answer nofilter}

                        {if $feedbackAccordion}
                            {if $psv >= 1.7}
                                {include file='module:hifaq/views/templates/front/_partials/feedback.tpl' idFaq=$faq.id_faq feedbackPosition=2 faq=$faq}
                            {else}
                                {include file="$modTplDir/front/_partials/feedback.tpl" idFaq=$faq.id_faq feedbackPosition=2 faq=$faq}
                            {/if}
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <p class="alert alert-warning">{l s='There are no any FAQs in this category.' mod='hifaq'}</p>
    {/if}
</div>
