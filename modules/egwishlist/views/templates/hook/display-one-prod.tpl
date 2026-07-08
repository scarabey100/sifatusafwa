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
{if isset($product) && $product}  
<div id="egwishlist-product-{$id_wishlisted|intval}" class="product__card">
    <article class="egwishlist-product-{$product->id|intval}">
            <div class="delete-icon">
                <a href="#" class="js-egwishlist-remove" data-id-product="{$id_wishlisted|intval}" data-url="{url entity='module' name='egwishlist' controller='actions'}">
                    <i class="icon-garbage" aria-hidden="true"></i>
                </a>
            </div>
            <div class="product__card--img">
                <a href="{$product->url}">
                        <img class="img-fluid" itemprop="image" src="{$product->cover}" alt="{$product->name}">
                </a> 
            </div>
        <div class="product__card--body">
            <div class="product__card--title">
                <h2 class="h3 product-title">
                    <a href="{$product->url}">{$product->name}</a>
                </h2>
            </div>
        </div>
    </article>
</div>
{/if}