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
{if $faqs}
    <ul class="hi-faq-search-elemenets">
        {foreach $faqs as $faq}
            <li>
                <a href="{$faq.url|escape:'html':'UTF-8'}">
                    {if $faq.category_name}
                        <div class="hi-faq-search-item-category">{$faq.category_name|escape:'html':'UTF-8'}</div>
                    {/if}
                    <div class="hi-faq-search-item-question">{$faq.question|escape:'html':'UTF-8'}</div>
                    <div class="hi-faq-search-item-answer">{$faq.answer|strip_tags|truncate:270:"..."|escape:'html':'UTF-8'}</div>
                </a>
            </li>
        {/foreach}
    </ul>
{/if}