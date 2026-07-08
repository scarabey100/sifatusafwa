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
            {if $sttheme.display_category_title}<h1 class="page_heading mb-3 {if $sttheme.display_category_title==2} text-2 {elseif $sttheme.display_category_title==3} text-3 {else} text-1 {/if}">{$category.name}</h1>{/if}

            {block name='product_list_header'} 
            <div id="js-product-list-header">
                
                    <div class="category__view">
                        {if !empty($category.image.large.url)}
                            <div class="category__view--img">
                                <picture>
                                    {if !empty($category.image.large.sources.avif)}
                                        <source srcset="{$category.image.large.sources.avif}" type="image/avif">
                                    {/if}
                                    {if !empty($category.image.large.sources.webp)}
                                        <source srcset="{$category.image.large.sources.webp}" type="image/webp">
                                    {/if}
                                    <img src="{$category.image.large.url}" alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}" loading="lazy" width="1664" height="256" />
                                </picture>
                            </div>
                        {/if}
                        <h1>{$category.name}</h1>
                        {if $category.description}
                            <div id="category-description" class="category__view--desc">
                                <div class="category__view--desc--wrapper">
                                    <div class="category__view--desc--inner">{$category.description nofilter}</div>
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

            {if $sttheme.display_category_image && $category.image.bySize.category_default.url}
                <div class="category-cover mb-3">
                    <img src="{$category.image.bySize.category_default.url}" {if $sttheme.retina && isset($category.image.bySize.category_default_2x.url)} srcset="{$category.image.bySize.category_default_2x.url} 2x" {/if} alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}">
                </div>
            {/if}

            {if $sttheme.display_cate_desc_full==1 && $category.description}
                <div id="category-description" class="style_content mb-3">{$category.description nofilter}</div>
            {/if}

            {if $sttheme.display_subcate && $subcategories}
                <div id="subcategories">
                    <h3 class="page_heading mb-3 hidden">{l s='Subcategories' d='Shop.Theme.Panda'}</h3>
                    <ul class="inline_list {if $sttheme.display_subcate==1 || $sttheme.display_subcate==3} subcate_grid_view row {else} subcate_list_view {/if}">
                        {foreach $subcategories as $subcategory}
                            <li class="clearfix {if $sttheme.display_subcate==1 || $sttheme.display_subcate==3} {if $sttheme.categories_per_fw} col-fw-{(12/$sttheme.categories_per_fw)|replace:'.':'-'}{/if} {if $sttheme.categories_per_xxl} col-xxl-{(12/$sttheme.categories_per_xxl)|replace:'.':'-'}{/if} {if $sttheme.categories_per_xl} col-xl-{(12/$sttheme.categories_per_xl)|replace:'.':'-'}{/if} col-lg-{(12/$sttheme.categories_per_lg)|replace:'.':'-'} col-md-{(12/$sttheme.categories_per_md)|replace:'.':'-'} col-sm-{(12/$sttheme.categories_per_sm)|replace:'.':'-'} col-{(12/$sttheme.categories_per_xs)|replace:'.':'-'} {if $sttheme.categories_per_fw && $subcategory@iteration%$sttheme.categories_per_fw == 1} first-item-of-screen-line{/if}{if $sttheme.categories_per_xxl &&  $subcategory@iteration%$sttheme.categories_per_xxl == 1} first-item-of-large-line{/if}{if $sttheme.categories_per_xl && $subcategory@iteration%$sttheme.categories_per_xl == 1} first-item-of-desktop-line{/if}{if $subcategory@iteration%$sttheme.categories_per_lg == 1} first-item-of-line{/if}{if $subcategory@iteration%$sttheme.categories_per_md == 1} first-item-of-tablet-line{/if}{if $subcategory@iteration%$sttheme.categories_per_sm == 1} first-item-of-mobile-line{/if}{if $subcategory@iteration%$sttheme.categories_per_xs == 1} first-item-of-portrait-line{/if} {/if}">
                                <h5 class="s_title_block {if $sttheme.display_subcate==3} nohidden {/if}"><a class="subcategory-name" href="{$subcategory.url}" title="{$subcategory.name}">{$subcategory.name}</a></h5>
                                {if $subcategory.description}
                                    <div class="subcat_desc">{$subcategory.description}</div>
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        {/block}

        {block name='klevu_landing'}
            <div class="klevuLanding"></div>
        {/block}
    </section>
{/block}
