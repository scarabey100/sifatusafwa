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
    <a href="{$mainPageURL|escape:'html':'UTF-8'}">{l s='FAQs' mod='hifaq'}</a>
    <span class="navigation-pipe">{$navigationPipe nofilter}</span>
    <span class="navigation_page">{$query|escape:'html':'UTF-8'}</span>
{/capture}

<h1 class="page-heading">{l s='Search Results' mod='hifaq'} "{$query|escape:'html':'UTF-8'}"</h1>
<div class="hi-faq-category-items">
    {foreach from=$faqs item=faq}
        <div class="hi-faq-item">
            <div class="hi-faq-question">
                <a href="{$faq.url|escape:'html':'UTF-8'}" class="hi-faq-question-link" data-id="{$faq.id_faq|intval}">{$faq.question|escape:'html':'UTF-8'}</a>
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
                    {include file="$modTplDir/front/_partials/feedback.tpl" idFaq=$faq.id_faq feedbackPosition=2 faq=$faq}
                {/if}
            </div>
        </div>
    {/foreach}
</div>