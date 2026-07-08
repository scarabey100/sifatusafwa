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
        <svg xmlns="http://www.w3.org/2000/svg" width="24.99" height="24.106" viewBox="0 0 24.99 24.106">
            <path id="Icon_ionic-ios-heart-empty" data-name="Icon ionic-ios-heart-empty" d="M21.271,3.938h-.059A6.7,6.7,0,0,0,15.62,7a6.7,6.7,0,0,0-5.593-3.061H9.968A6.656,6.656,0,0,0,3.375,10.59,14.33,14.33,0,0,0,6.189,18.4a49.314,49.314,0,0,0,9.431,9.084A49.314,49.314,0,0,0,25.051,18.4a14.33,14.33,0,0,0,2.814-7.812A6.656,6.656,0,0,0,21.271,3.938ZM23.72,17.431a45.153,45.153,0,0,1-8.1,7.983,45.22,45.22,0,0,1-8.1-7.989,12.7,12.7,0,0,1-2.5-6.835,4.995,4.995,0,0,1,4.957-5h.053a4.936,4.936,0,0,1,2.42.636,5.144,5.144,0,0,1,1.79,1.678,1.654,1.654,0,0,0,2.767,0A5.2,5.2,0,0,1,18.8,6.228a4.936,4.936,0,0,1,2.42-.636h.053a4.995,4.995,0,0,1,4.957,5A12.861,12.861,0,0,1,23.72,17.431Z" transform="translate(-3.125 -3.688)" fill="#212121" stroke="#212121" stroke-width="0.5"/>
        </svg>
        <span class="counter egwishlist-nb"></span>
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
                    <svg focusable="false" aria-hidden="true" viewBox="0 0 24 24" title="Close">
                        <path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>