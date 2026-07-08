{**
 *  @author    Rekire <info@rekire.com>
 *  @copyright Rekire
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{foreach $multipleSliders as $key => $slider}
    <div class="featured-products featured-products__sellerplus--slider">
        <div class="container">
            <h2 class="featured-products__title">{$slider.title|escape:'htmlall':'UTF-8'}</h2>
        </div>
        <div class="products">
            {foreach $slider.products as $product}
                {if $product.add_to_cart_url}
                <div class="product">
                    <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product|escape:'html':'UTF-8'}" data-id-product-attribute="{$product.id_product_attribute|escape:'html':'UTF-8'}">
                        <div class="thumbnail-container">
                            <div class="thumbnail-top">
                                {if $slider['configRow']['show_product_flags']}
                                    <ul class="product-flags js-product-flags">
                                        {foreach from=$product.flags item=flag}
                                            {if Module::isEnabled('egstickers')}
                                                {hook h='displayNativeStickers' flag=$flag.type}
                                                {assign var="nativeFlag" value=EgStickersFlags::NativeFlag($flag.type)}
                                            {/if}
                                            {if isset($nativeFlag) &&  !empty($nativeFlag)}
                                                {if $nativeFlag.active}
                                                <li class="product-flag {if $nativeFlag.sticker_position} {if $nativeFlag.sticker_position == 1}sticker_top what{else}sticker_bottom{/if}{/if}" {if $nativeFlag.color}style="background-color: {$nativeFlag.color}; color: {$nativeFlag.color};"{/if}>
                                                    <span>{$nativeFlag.parallel_value}</span>
                                                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 36" width="22" height="36">
                                                        <g id="Group 5212">
                                                            <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="M20 17.05 L0 0 H-107 Q-111 0 -111 4 V30.1 Q-111 34.1 -107 34.1 H0 Z"></path>
                                                        </g>
                                                    </svg>
                                                </li>
                                                {/if}
                                            {else}
                                                <li class="product-flag {$flag.type}">
                                                    <span>{$flag.label}</span>
                                                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 36" width="22" height="36">
                                                        <g id="Group 5212">
                                                            <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="M20 17.05 L0 0 H-107 Q-111 0 -111 4 V30.1 Q-111 34.1 -107 34.1 H0 Z"></path>
                                                        </g>
                                                    </svg>
                                                </li>
                                            {/if}
                                        {/foreach}
                                    </ul>
                                {/if}
                                <a href="{$product.url|escape:'htmlall':'UTF-8'}" class="thumbnail product-thumbnail">
                                    {if $product.default_image}
                                        <picture>
                                            <img
                                                    {if $slider['configRow']['lazy'] == true}
                                                        data-src="{$product.default_image.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                        class="swiper-lazy"
                                                    {else}
                                                        src="{$product.default_image.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                    {/if}
                                                    alt="{if !empty($product.default_image.legend)}{$product.default_image.legend|escape:'html':'UTF-8'}{else}{$product.name|truncate:30:'...'}{/if}"
                                                    data-full-size-image-url="{$product.default_image.large.url|escape:'html':'UTF-8'}"
                                                    width="{$product.default_image.bySize.{$slider['imageType']}.width|escape:'html':'UTF-8'}"
                                                    height="{$product.default_image.bySize.{$slider['imageType']}.height|escape:'html':'UTF-8'}"
                                            />
                                        </picture>
                                        {if isset($product.images[1])}
                                            <picture class="img-hover">
                                                <img
                                                        itemprop="image"
                                                        loading="lazy"
                                                        src="{$product.images[1].bySize.home_default.url}"
                                                        alt="{if !empty($product.images[1].legend)}{$product.images[1].legend}{else}{$product.name}{/if}"
                                                />
                                            </picture>
                                        {/if}
                                    {elseif $product.cover}
                                        <picture>
                                            <img
                                                    {if $slider['configRow']['lazy'] == true}
                                                        data-src="{$product.cover.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                        class="swiper-lazy"
                                                    {else}
                                                        src="{$product.cover.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                    {/if}
                                                    alt="{if !empty($product.cover.legend)}{$product.cover.legend|escape:'html':'UTF-8'}{else}{$product.name|truncate:30:'...'}{/if}"
                                                    data-full-size-image-url="{$product.cover.large.url|escape:'html':'UTF-8'}"
                                                    width="{$product.cover.bySize.{$slider['imageType']}.width|escape:'html':'UTF-8'}"
                                                    height="{$product.cover.bySize.{$slider['imageType']}.height|escape:'html':'UTF-8'}"
                                                    loading="lazy"
                                            />
                                        </picture>
                                        {if isset($product.images[1])}
                                            <picture class="img-hover">
                                                <img
                                                        itemprop="image"
                                                        loading="lazy"
                                                        src="{$product.images[1].bySize.home_default.url}"
                                                        alt="{if !empty($product.images[1].legend)}{$product.images[1].legend}{else}{$product.name}{/if}"
                                                />
                                            </picture>
                                        {/if}
                                    {else}
                                        <picture>
                                            <img
                                                    src="{$urls.no_picture_image.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                    width="{$urls.no_picture_image.bySize.{$slider['imageType']}.width|escape:'html':'UTF-8'}"
                                                    height="{$urls.no_picture_image.bySize.{$slider['imageType']}.height|escape:'html':'UTF-8'}"
                                                    loading="lazy"
                                            />
                                        </picture>
                                    {/if}
                                </a>
                                {if $slider['configRow']['show_product_variants'] || $slider['configRow']['show_product_quick_view']}
                                    <div class="highlighted-informations{if !$product.main_variants || !$slider['configRow']['show_product_variants']} no-variants{/if}">
                                        {if $slider['configRow']['show_product_quick_view']}
                                            <a class="quick-view js-quick-view" href="#" data-link-action="quickview">{l s='Quick view' d='Shop.Theme.Actions'}</a>
                                        {/if}
                                        {if isset($readOnly)}
                                            <div class="wishlist-remove">
                                                <a href="#" class="js-egwishlist-remove"
                                                   data-id-product="{$product->id_product|intval}"
                                                   data-url="{url entity='module' name='egwishlist' controller='actions'}">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        {else}
                                            <div class="wishlist-icon">
                                                {hook h='displayProductListFunctionalButtons' product=$product}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                            <div class="product-description">
                                {if $slider['configRow']['show_product_name']}
                                    {if isset($page) && $page.page_name == 'index'}
                                        <h3 class="h3 product-title">
                                            <a href="{$product.url|escape:'htmlall':'UTF-8'}"content="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name}</a>
                                        </h3>
                                    {else}
                                        <h2 class="h2 product-title">
                                            <a href="{$product.url|escape:'htmlall':'UTF-8'}" content="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name}</a>
                                        </h2>
                                    {/if}
                                {/if}

                                <div class="product-subtitle">{Product::getProductNameAr($product.id)}</div>

                                {if $slider['configRow']['show_price'] && $product.show_price}
                                    <div class="product-price-and-shipping">
                                        <span class="price" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">
                                            {if $slider['configRow']['show_displayProductPriceBlock']}
                                                {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
                                                {if '' !== $smarty.capture.custom_price}
                                                    {$smarty.capture.custom_price|cleanHtml nofilter}
                                                {else}
                                                    {$product.price|escape:'html':'UTF-8'}
                                                {/if}
                                            {else}
                                                {$product.price|escape:'html':'UTF-8'}
                                            {/if}
                                        </span>
                                        {if $product.has_discount}
                                            {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                            <span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price|escape:'html':'UTF-8'}</span>
                                        {/if}
                                        {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                                        {hook h='displayProductPriceBlock' product=$product type='weight'}
                                    </div>
                                {/if}

                                {if !$configuration.is_catalog && $slider['configRow']['show_add_to_cart'] && $enable_add_to_cart}
                                    <div class="product-addToCart">
                                        {if $product.add_to_cart_url && !$product.customizable}
                                            <form type="post" action="{$product.add_to_cart_url|escape:'html':'UTF-8'}" class="product-item-buttons">
                                                {if $product.pa_minimal_quantity !== NULL}
                                                    <input type="hidden" name="qty"
                                                           value="{$product.pa_minimal_quantity|escape:'html':'UTF-8'}">
                                                {/if}
                                                <div class="add">
                                                    <button
                                                            class="btn btn-primary add-to-cart"
                                                            data-button-action="add-to-cart"
                                                            type="submit"
                                                            {if !$product.add_to_cart_url}
                                                                disabled
                                                            {/if}
                                                    >
                                                        {l s='Add to cart' d='Shop.Theme.Actions'}
                                                    </button>
                                                </div>
                                            </form>
                                        {else}
                                            <a class="btn btn-secondary" role="button"
                                               href="{$product.url|escape:'html':'UTF-8'}"
                                               class="thumbnail product-thumbnail">{l s='Personalize' mod='rkrselledplus' }</a>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </article>
                </div>
                {/if}
            {/foreach}
        </div>
    </div>
{/foreach}
