{*
 * 2019 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */
*}

{extends file='customer/page.tpl'}

{block name='page_header_container'}
    {if $readOnly}
        <div class="category__view">
            <h1>{$page.meta.title}</h1>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name='page_title'}
    {if !$readOnly}
        {l s='Wishlist' mod="egwishlist"}
    {/if}
{/block}

{block name='page_content'}
    {capture assign="productClasses"}{if !empty($productClass)}{$productClass}{else}col-xs-12 col-sm-6 col-xl-3{/if}{/capture}
    {if isset($wishlistProducts) && $wishlistProducts}
        <div id="egwishlist-user-products">
            <div class="container">
                <div class="products">
                    {foreach from=$wishlistProducts item="product" key="position"}
                        {include file="catalog/_partials/miniatures/product.tpl" readOnly=$readOnly product=$product position=$position productClasses=$productClasses}
                    {/foreach}
                </div>
            </div>
        </div>
        <p class="alert alert-warning hidden-xs-up" id="egwishlist-warning">
            {l s='Your wishlist is empty' mod="egwishlist"}
        </p>
        {if isset($crosselingProducts) && $crosselingProducts}
            <section id="egwishlist-crosseling" class="featured-products">
                <h2 class="featured-products__title">{l s='Customers who bought this product(s) also bought:' mod="egwishlist"}</h2>
                <div class="products">
                    {foreach from=$crosselingProducts item="product" key="position"} 
                        {if $product.active}
                            {include file="catalog/_partials/miniatures/product.tpl" readOnly=$readOnly product=$product position=$position productClasses=$productClasses}
                        {/if}
                    {/foreach}
                </div>
            </section>
        {/if}
    {else}
        <p class="alert alert-warning">{l s='Your wishlist is empty' mod="egwishlist"}</p>
    {/if}
{/block}

