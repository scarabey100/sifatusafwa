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
   <div id="egwishlist-product-{$product->id_egwishlist_product|intval}" class="product egwishlist-product">
       <div class="product-miniature">
           <div class="thumbnail-container">
               <div class="thumbnail-top">
                   <div class="miniature-slick swiper">
                       <div class="swiper-wrapper">
                           <div class="swiper-slide">
                               <div class="miniature-slick-item">
                                    <a href="{$product->url}"> <img
                                        class="img-fluid"
                                        {if !is_array($product->cover)}
                                            src="{$product->cover}"
                                            alt="{$product->cover}"
                                        {else}
                                            src="{$product->cover.bySize.cart_default.url}"
                                            alt="{$product->cover.bySize.cart_default.url}"
                                        {/if}
                                ></a>
                               </div>
                           </div>
                           <div class="swiper-button-next"></div>
                           <div class="swiper-button-prev"></div>
                           <div class="swiper-pagination"></div>
                       </div>
                   </div>
                    {if !$readOnly}
                        <a href="#" class="js-egwishlist-remove"
                        data-id-product="{$product->id_egwishlist_product|intval}"
                        data-url="{url entity='module' name='egwishlist' controller='actions'}">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </a>
                    {/if}
               </div>
           </div>
       </div>
   </div>