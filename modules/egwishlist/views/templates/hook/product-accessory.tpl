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

{if isset($idProduct)}
    <div class="col col-sm-auto">
        <button type="button" data-toggle="tooltip" data-placement="top"  title="{l s='Add to wishlist' mod="egwishlist"}"
           class="btn btn-secondary btn-lg btn-iconic btn-egwishlist-add js-egwishlist-add" data-animation="false" id="eg-wishlist-product-btn"
           data-id-product="{$idProduct}"
           data-id-product-attribute="{$idProductAttribute}"
           data-url="{url entity='module' name='egwishlist' controller='actions'}">
            <i class="fa fa-heart-o not-added" aria-hidden="true"></i> <i class="fa fa-heart added" aria-hidden="true"></i>
            <span class="text">
                {l s='Ajouter à mes préférés' mod="egwishlist"}
            </span>
        </button>
    </div>
{/if}