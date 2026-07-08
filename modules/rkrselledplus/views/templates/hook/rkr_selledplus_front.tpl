{**
 *  @author    Rekire <info@rekire.com>
 *  @copyright Rekire
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<section class="rkr_selled_plus-container {$class|escape:'html':'UTF-8'} clearfix">
    {foreach $multipleSliders as $key => $slider}
        <div class="rkr_selled_plus-slider">
            <h2 class="rkr_title"{if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_title']}
            style="font-size: {$slider['configRow']['rem_size_title']|floatval}rem"{/if}>
                {$slider.title|escape:'htmlall':'UTF-8'}
            </h2>
            <div style="--swiper-navigation-color: gray; --swiper-pagination-color: gray"
                 class="swiper mySwiper-{$key|escape:'html':'UTF-8'}">
                <div class="swiper-wrapper">
                    {foreach $slider.products as $product}
                        <div class="swiper-slide" style="box-sizing:border-box">
                            <div class="js-product product">
                                <article class="product-miniature js-product-miniature"
                                         data-id-product="{$product.id_product|escape:'html':'UTF-8'}"
                                         data-id-product-attribute="{$product.id_product_attribute|escape:'html':'UTF-8'}">
                                    <div class="thumbnail-container">
                                        <div class="thumbnail-top">
                                            <a href="{$product.url|escape:'htmlall':'UTF-8'}"
                                               class="thumbnail product-thumbnail">
                                                {if $product.default_image}
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
                                                    {if $slider['configRow']['lazy'] == true}
                                                        <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                                                    {/if}
                                                {elseif $product.cover}
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
                                                    />
                                                    {if $slider['configRow']['lazy'] == true}
                                                        <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                                                    {/if}
                                                {else}
                                                   <img
                                                            src="{$urls.no_picture_image.bySize.{$slider['imageType']}.url|escape:'html':'UTF-8'}"
                                                            width="{$urls.no_picture_image.bySize.{$slider['imageType']}.width|escape:'html':'UTF-8'}"
                                                            height="{$urls.no_picture_image.bySize.{$slider['imageType']}.height|escape:'html':'UTF-8'}"
                                                    />
                                                {/if}
                                            </a>
                                            {if $slider['configRow']['show_product_variants'] || $slider['configRow']['show_product_quick_view']}
                                                <div class="highlighted-informations{if !$product.main_variants || !$slider['configRow']['show_product_variants']} no-variants{/if}">
                                                    {if $slider['configRow']['show_product_quick_view']}
                                                        <a class="quick-view js-quick-view" href="#"
                                                           data-link-action="quickview">
                                                            {l s='Quick view' d='Shop.Theme.Actions'}
                                                        </a>
                                                    {/if}
                                                    {if $slider['configRow']['show_product_variants'] && $product.main_variants}
                                                        <div class="variant-links">
                                                            {foreach from=$product.main_variants item=variant}
                                                                <a href="{$variant.url|escape:'htmlall':'UTF-8'}"
                                                                   class="{$variant.type|escape:'html':'UTF-8'}"
                                                                   title="{$variant.name|escape:'html':'UTF-8'}"
                                                                   aria-label="{$variant.name|escape:'html':'UTF-8'}"
                                                                        {if $variant.texture} style="background-image: url({$variant.texture|escape:'html':'UTF-8'})"
                                                                        {elseif $variant.html_color_code} style="background-color: {$variant.html_color_code|escape:'html':'UTF-8'}" {/if}
                                                                ></a>
                                                            {/foreach}
                                                            <span class="js-count count"></span>
                                                        </div>
                                                    {/if}
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="product-description">
                                            {if $slider['configRow']['show_product_name']}
                                                {if isset($page) && $page.page_name == 'index'}
                                                    <h3 class="h3 product-title">
                                                        <a {if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_product_name']}
                                                                style="font-size: {$slider['configRow']['rem_size_product_name']|floatval}rem"{/if}
                                                                href="{$product.url|escape:'htmlall':'UTF-8'}"
                                                                content="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name|truncate:30:'...'}</a>
                                                    </h3>
                                                {else}
                                                    <h2 class="h2 product-title">
                                                        <a {if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_product_name']}
                                                                style="font-size: {$slider['configRow']['rem_size_product_name']|floatval}rem"{/if}
                                                                href="{$product.url|escape:'htmlall':'UTF-8'}"
                                                                content="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name|truncate:30:'...'}</a>
                                                    </h2>
                                                {/if}
                                            {/if}
                                            {if $slider['configRow']['show_product_category']}
                                                {if isset($page) && $page.page_name == 'index'}
                                                    <h4 class="category-title">
                                                        <a {if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_product_category']}
                                                            style="font-size: {$slider['configRow']['rem_size_product_category']|floatval}rem"{/if}
                                                                href="{$link->getCategoryLink($product->id_category_default,$product->category,$product->id_lang)|escape:'htmlall':'UTF-8'}"
                                                                content="{$product.link|escape:'htmlall':'UTF-8'}">{$product.category_name|truncate:20:'...'}</a>
                                                    </h4>
                                                {else}
                                                    <h3 class="category-title">
                                                        <a {if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_product_category']}
                                                            style="font-size: {$slider['configRow']['rem_size_product_category']|floatval}rem"{/if}
                                                                href="{$link->getCategoryLink($product->id_category_default,$product->category,$product->id_lang)|escape:'htmlall':'UTF-8'}"
                                                                content="{$product.link|escape:'htmlall':'UTF-8'}">{$product.category_name|truncate:20:'...'}</a>
                                                    </h3>
                                                {/if}
                                            {/if}

                                            {if $slider['configRow']['show_price'] && $product.show_price}
                                                <div class="product-price-and-shipping">
                                                    {if $product.has_discount}
                                                        {if $slider['configRow']['show_displayProductPriceBlock']}
                                                            {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                                        {/if}
                                                        <span class="regular-price"
                                                              aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price|escape:'html':'UTF-8'}</span>
                                                        {if $product.discount_type === 'percentage'}
                                                            <span class="discount-percentage discount-product">{$product.discount_percentage|escape:'htmlall':'UTF-8'}</span>
                                                        {elseif $product.discount_type === 'amount'}
                                                            <span class="discount-amount discount-product">{$product.discount_amount_to_display|escape:'htmlall':'UTF-8'}</span>
                                                        {/if}
                                                    {/if}
                                                    {if $slider['configRow']['show_displayProductPriceBlock']}
                                                        {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                                    {/if}
                                                    <span class="price"
                                                          aria-label="{l s='Price' d='Shop.Theme.Catalog'}"
                                                          {if !$slider['configRow']['default_style'] && $slider['configRow']['rem_size_product_price']}
                                                            style="font-size: {$slider['configRow']['rem_size_product_price']|floatval}rem"{/if}>
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
                                                    {if $slider['configRow']['show_displayProductPriceBlock']}
                                                        {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                                                        {hook h='displayProductPriceBlock' product=$product type='weight'}
                                                    {/if}
                                                </div>
                                            {/if}
                                        </div>

                                        {* flags / stickers *}
                                        {if $slider['configRow']['show_product_flags']}
                                            <ul class="product-flags js-product-flags">
                                                {foreach from=$product.flags item=flag}
                                                    <li class="product-flag {$flag.type|escape:'html':'UTF-8'}">
                                                        {$flag.label|escape:'html':'UTF-8'}
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        {/if}

                                        {if !$configuration.is_catalog && $slider['configRow']['show_add_to_cart'] && $enable_add_to_cart}
                                            <div class="rkr_product-action">
                                                {if $product.add_to_cart_url && !$product.customizable}
                                                    <form type="post"
                                                          action="{$product.add_to_cart_url|escape:'html':'UTF-8'}"
                                                          class="product-item-buttons" style="padding-bottom: 1em;">
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
                                </article>
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    {/foreach}
</section>

<script>

    var ready = (callback) => {
        if (document.readyState != "loading") callback();
        else document.addEventListener("DOMContentLoaded", callback);
    }
    ready(() => {
        {foreach $multipleSliders as $key => $slider}
        <!-- Initialize Swiper -->
        var swiper_{$key|escape:'html':'UTF-8'} = new Swiper(".mySwiper-{$key|escape:'html':'UTF-8'}", {
            {if $slider['configRow']['autoplay']}autoplay: true,{/if}
            {* {if $slider['configRow']['lazy']}lazy: true,{/if} *}
            {if $slider['configRow']['loop']}loop: true,{/if}
            {if $slider['configRow']['lazy']}
            lazy: {
                loadPrevNext: true,
            },
            {/if}
            loopFillGroupWithBlank: false,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                576: {
                    {if $slider['nProducts'] < $slider['configRow']['columns_md']}
                    slidesPerView: {$slider['nProducts']|intval},
                    slidesPerGroup: {$slider['nProducts']|intval},
                    {else}
                    slidesPerView: {$slider['configRow']['columns_md']|intval},
                    slidesPerGroup: {$slider['configRow']['columns_md']|intval},
                    {/if}
                },
                768: {
                    {if $slider['nProducts'] < $slider['configRow']['columns_md']}
                    slidesPerView: {$slider['nProducts']|intval},
                    slidesPerGroup: {$slider['nProducts']|intval},
                    {else}
                    slidesPerView: {$slider['configRow']['columns_md']|intval},
                    slidesPerGroup: {$slider['configRow']['columns_md']|intval},
                    {/if}
                },
                992: {
                    {if $slider['nProducts'] < $slider['configRow']['columns_lg']}
                    slidesPerView: {$slider['nProducts']|intval},
                    slidesPerGroup: {$slider['nProducts']|intval},
                    {else}
                    slidesPerView: {$slider['configRow']['columns_lg']|intval},
                    slidesPerGroup: {$slider['configRow']['columns_lg']|intval},
                    {/if}
                },
                1200: {
                    {if $slider['nProducts'] < $slider['configRow']['columns_xl']}
                    slidesPerView: {$slider['nProducts']|intval},
                    slidesPerGroup: {$slider['nProducts']|intval},
                    {else}
                    slidesPerView: {$slider['configRow']['columns_xl']|intval},
                    slidesPerGroup: {$slider['configRow']['columns_xl']|intval},
                    {/if}
                },
            },
        });
        {/foreach}
    });
</script>
