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

{if isset($id_product_attribute)}
    <div class="wishlist-button-add">
        {assign var="added" value=EgWishListProduct::checkProductInShortList($id_product)}

        <a href="#" class="btn-egwishlist-add js-egwishlist-add {if $added} egwishlist-added{/if}"  data-id-product="{$id_product|intval}" data-id-product-attribute="{$id_product_attribute|intval}"
           data-url="{url entity='module' name='egwishlist' controller='actions'}" data-toggle="tooltip" title="{l s='Add to wishlist' mod="egwishlist"}">
            <i class="fa fa-heart-o not-added" aria-hidden="true"></i>
            <i class="fa fa-heart added" aria-hidden="true"></i>
            {l s='Ajouter à mes favoris' mod="egwishlist"}
        </a>
    </div>
{/if}
