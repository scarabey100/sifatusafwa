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

{if isset($product.id_product_attribute)}
    <div class="col col-sm-auto">
        {assign var="added" value=EgWishListProduct::checkProductInShortList($product.id_product_attribute)}

        <button type="button" data-toggle="tooltip" data-placement="top"  title="{l s='Add to wishlist' mod="egwishlist"}"
           class="btn btn-secondary btn-lg btn-iconic btn-egwishlist-add js-egwishlist-add {if $added} egwishlist-added {/if}" data-animation="false" id="eg-wishlist-product-btn"
           data-id-product="{$product.id_product|intval}"
           data-id-product-attribute="{$product.id_product_attribute|intval}"
           data-url="{url entity='module' name='egwishlist' controller='actions'}">
            <i class="fa fa-heart-o not-added" aria-hidden="true"></i> <i class="fa fa-heart added" aria-hidden="true"></i>
            <span class="text">
                {l s='Ajouter à ma liste' mod="egwishlist"}
            </span>
        </button>
    </div>
{/if}