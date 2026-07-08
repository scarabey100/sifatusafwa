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
<div class="hi-faq-category-items">
    <div class="hi-faq-item clearfix">
        <div class="hi-faq-question col-sm-12">
            <a data-toggle="collapse" href="#collapse_{$faq.id_faq|intval}" class="collapsed col-sm-10">{$faq.question|escape:'html':'UTF-8'}</a>
            <a href="{$faq.url|escape:'html':'UTF-8'}" class="hi-faq-link col-sm-2">
                {if $icons == 'material'}
                    <i class="material-icons">insert_link</i>
                {else}
                    <i class="fa fa-link"></i>
                {/if}
            </a>
        </div>
        <div id="collapse_{$faq.id_faq|intval}" class="hi-faq-answer collapse">{$faq.answer nofilter}</div>
    </div>
</div>