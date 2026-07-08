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

<div class="d-inline-block wishlist_btn">
    <a class="header__tools--link" href="{url entity='module' name='egwishlist' controller='view'}">
        <span class="icon-heart">
            <span class="counter egwishlist-nb"></span>
        </span>
    </a>
</div>

<div class="notification">
    <div class="notification__item">
        <div class="notification__wrapper">
            <div class="notification__icon">
                <span class="icon-check-mark"></span>
            </div>

            <div class="notification__content">
                <p class="add">{l s='Article ajouté à mes favoris' mod='egwishlist'}</p>
                <p class="remove">{l s='Article supprimé de mes favoris' mod='egwishlist'}</p>
            </div>

            <div class="notification__close">
                <button>
                    <span class="icon-close"></span>
                </button>
            </div>
        </div>
    </div>
</div>