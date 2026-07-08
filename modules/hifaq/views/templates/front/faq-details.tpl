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
        {if $faq}
            <span class="navigation_page">{$faq.question|escape:'html':'UTF-8'}</span>
        {/if}
    {/if}
{/capture}

{if $faq}
    <h1>{$faq.question|escape:'html':'UTF-8'}</h1>
    {$faq.answer nofilter}

    {if $display_related_products && $related_products}
        {if $psv < 1.7}
            <div class="faq-related-products block">
                <p class="title_block">
                    {l s='Related Products' mod='hifaq'}
                </p>
                {include file="{$tpl_dir|escape:'html':'UTF-8'}./product-list.tpl" products=$related_products}
            </div>
        {else}
            <section id="products">
                <h2 class="title">
                    {l s='Related Products' mod='hifaq'}
                </h2>
                <div class="products">
                    {foreach from=$related_products item="product"}
                        {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses='col-xs-12 col-sm-6 col-xl-4'}
                    {/foreach}
                </div>
            </section>
        {/if}
    {/if}

    {if $feedback}
        {if $psv >= 1.7}
            {include file='module:hifaq/views/templates/front/_partials/feedback.tpl' idFaq=$faq.id_faq feedbackPosition=$feedbackPosition faq=$faq}
        {else}
            {include file="$modTplDir/front/_partials/feedback.tpl" idFaq=$faq.id_faq feedbackPosition=2 faq=$faq}
        {/if}
    {/if}
{else}
    <p class="alert alert-danger">{l s='FAQ Not Found' mod='hifaq'}</p>
{/if}




