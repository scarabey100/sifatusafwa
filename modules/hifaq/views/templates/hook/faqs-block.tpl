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
    {if $accordion}
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
                                {include file='module:hifaq/views/templates/front/_partials/feedback.tpl' idFaq=$faq.id_faq feedbackPosition=2}
                            {else}
                                {include file="$modTplDir/front/_partials/feedback.tpl" idFaq=$faq.id_faq feedbackPosition=2}
                            {/if}
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <ul>
            {foreach from=$faqs item='faq'}
                <li><a href="{$faq.url|escape:'html':'UTF-8'}">{$faq.question|escape:'htmlall':'UTF-8'}</a></li>
            {/foreach}
        </ul>
    {/if}

    {if $structured_data}
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "FAQPage",
                "mainEntity": [
                    {foreach from=$faqs item=faq name=faqs}
                        {
                            "@type": "Question",
                            "name": "{$faq.question|escape:'html':'UTF-8'}",
                            "acceptedAnswer": {
                                "@type": "Answer",
                                "text": "{$faq.answer|strip_tags|escape:'html':'UTF-8'}"
                            }
                        }{if !$smarty.foreach.faqs.last},{/if}
                    {/foreach}
                ]
            }
        </script>
    {/if}
</div>