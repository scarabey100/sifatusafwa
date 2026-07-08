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
            <svg class="not-added" xmlns="http://www.w3.org/2000/svg" width="20.366" height="19.737" viewBox="0 0 20.366 19.737">
                <path id="Icon_ionic-ios-heart" data-name="Icon ionic-ios-heart" d="M17.527,3.937H17.48a5.3,5.3,0,0,0-4.422,2.421A5.3,5.3,0,0,0,8.635,3.937H8.589A5.263,5.263,0,0,0,3.375,9.2,11.332,11.332,0,0,0,5.6,15.375a39,39,0,0,0,7.458,7.183,39,39,0,0,0,7.458-7.183A11.332,11.332,0,0,0,22.741,9.2,5.263,5.263,0,0,0,17.527,3.937Z" transform="translate(-2.875 -3.438)" fill="none" stroke="#666d6d" stroke-width="1"/>
            </svg>
            <svg class="added" xmlns="http://www.w3.org/2000/svg" width="20.366" height="19.737" viewBox="0 0 20.366 19.737">
                <path id="Icon_ionic-ios-heart" data-name="Icon ionic-ios-heart" d="M17.527,3.937H17.48a5.3,5.3,0,0,0-4.422,2.421A5.3,5.3,0,0,0,8.635,3.937H8.589A5.263,5.263,0,0,0,3.375,9.2,11.332,11.332,0,0,0,5.6,15.375a39,39,0,0,0,7.458,7.183,39,39,0,0,0,7.458-7.183A11.332,11.332,0,0,0,22.741,9.2,5.263,5.263,0,0,0,17.527,3.937Z" transform="translate(-2.875 -3.438)" fill="none" stroke="#666d6d" stroke-width="1"/>
            </svg>
        </a>
    </div>
{/if}
