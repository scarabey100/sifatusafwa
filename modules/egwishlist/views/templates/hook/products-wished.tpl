{*
* 2007-2021 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
* @author ETS-Soft <etssoft.jsc@gmail.com>
    * @copyright 2007-2021 ETS-Soft
    * @license Valid for 1 website (or project) for each purchase of license
    * International Registered Trademark & Property of ETS-Soft
    *}
    {if isset($products) && $products} 
    <section class="featured-products product__slider ">
        <h2 class="h2">{l s='Vos produits préférés' mod="egwishlist"}</h2>
        <div calss="products wished-items row slick-initialized slick-slider slick-dotted">
            <div calss="slick-track">
                <div class="products">
                    {foreach from=$products item=product name=products}
                        <div class="product__card">
                            <article class="product-miniature js-product-miniature">
                                <div class="product__card--img">
                                    <a href="{$product->url}">
                                        <img class="img-fluid"
                                            src="{$product->cover}"
                                            alt="{$product->name}" loading="lazy"
                                            data-full-size-image-url="{$product->cover}"
                                            width="456" height="120">
                                    </a>
                                </div>
                                <div class="product__card--body">
                                    <div class="product__card--title">
                                        <h2 class="h3 product-title">
                                            <a href="{$product->url}">{$product->name}</a>
                                        </h2>
                                    </div>
                                    <div class="product__card--price">
                                        <div class="product-price-and-shipping">
                                            <span class="price" aria-label="Prix">
                                                {$product->price|string_format:"%.2f"} MAD
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </section> 
    {/if}