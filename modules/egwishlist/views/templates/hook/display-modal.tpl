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

{if isset($login_form)}
<div id="egwishlist-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">{l s='You need to login or create account' mod="egwishlist"}</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <section class="login-form">
                   <p> {l s='Save products on your wishlist to buy them later or share with your friends.' mod="egwishlist"}</p>
                    {render file='customer/_partials/login-form.tpl' idForm='login-form-modal' ui=$login_form wishlistModal=true}
                </section>
                <hr/>
                {block name='display_after_login_form'}
                    {hook h='displayCustomerLoginFormAfter'}
                {/block}
                <div class="no-account">
                    <a href="{$urls.pages.register}" data-link-action="display-register-form">
                        {l s='No account? Create one here' mod="egwishlist"}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
