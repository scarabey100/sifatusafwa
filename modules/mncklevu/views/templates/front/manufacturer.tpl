{**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file='layouts/layout-full-width.tpl'}

{block name='head' append}
    {include file='module:mncklevu/views/templates/front/_partials/custom_templates.tpl'}
{/block}

{block name='content'}
    <section id="main">
        {block name='product_list_header'}
            <div class="flex_container flex_start">
                {if $sttheme.brand_page_image}
                    <div class="brand-img mr-3 mb-3">
                        <picture>
                            {assign var='brand_link' value=$link->getManufacturerImageLink($manufacturer.id, 'brand_default')}
                            {if isset($stwebp) && isset($stwebp.brand_default) && $stwebp.brand_default}
                                <!--[if IE 9]><video style="display: none;"><![endif]-->
                                <source srcset="{$brand_link|regex_replace:'/\.jpg$/':'.webp'}"
                                    title="{$manufacturer.name}"
                                    type="image/webp"
                                >
                                <!--[if IE 9]></video><![endif]-->
                            {/if}
                            <img src="{$brand_link}" alt="{$manufacturer.name}" class="general_border" width="{$sttheme.brand_default.width}" height="{$sttheme.brand_default.height}">
                        </picture>
                    </div>
                {/if}
                 
                {block name='product_list_header'} 
                {assign var="manufacturer_description" value="{$manufacturer.short_description } {$manufacturer.description }"}
                <div id="js-product-list-header"> 
                        <div class="category__view">  
                            <h1>{$manufacturer.name}</h1>
                            {$manufacturer.short_description nofilter}
                            {if $manufacturer.description} 
                                <div id="category-description" class="category__view--desc">
                                    <div class="category__view--desc--wrapper">
                                        <div class="category__view--desc--inner">{$manufacturer.description nofilter}</div>
                                    </div>
                                    <div class="category__view--desc--actions">
                                        <div class="show-more active">
                                            {l s='Show more' d='Shop.Theme.Global'} <i class="material-icons">expand_more</i>
                                        </div>
                                        <div class="show-less">
                                            {l s='Show less' d='Shop.Theme.Global'} <i class="material-icons">expand_less</i>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    
                </div> 
                {/block}
                 
            </div>
        {/block}
        
        {block name='klevu_landing'}
            <div class="klevuLanding"></div>
        {/block}
    </section>
{/block}
