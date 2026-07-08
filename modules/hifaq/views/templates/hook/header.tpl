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
<script type="text/javascript">
    {literal}
        var HiFaq = {
            psv: {/literal}{$psv|floatval}{literal},
            search_url: '{/literal}{$search_url|escape:'html':'UTF-8'}{literal}',
            key: '{/literal}{$secure_key|escape:'html':'UTF-8'}{literal}',
            mainUrl: '{/literal}{$hiFaqMainUrl|escape:'html':'UTF-8'}{literal}'
        }
    {/literal}
</script>
{if $controller_name == 'faqcategory'}
    <meta property="og:url" content="{$protocol}{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}">
    <meta property="og:title" content="{$meta_title|escape:'htmlall':'UTF-8'}">
    <meta property="og:description" content="{$meta_description|escape:'htmlall':'UTF-8'}">
{/if}

<style type="text/css">
    {literal}
        .hi-faq-top-search-container {
            background-color: {/literal}{$hiFaqSearchBgColor}{literal};
        }
    {/literal}
    {$hiFaqCustomCss nofilter}
</style>

{if isset($schema_faqs) && $schema_faqs}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {foreach from=$schema_faqs item=faq name=faqs}
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
