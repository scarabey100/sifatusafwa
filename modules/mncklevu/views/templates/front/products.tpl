{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}

 <section id="products">
	<div id="js-product-list">
		<div class="products row">
			{foreach $products as $product}
				{block name='product_miniature_item'}
					<div class="js-product product col-xs-12 col-sm-6 col-xl-4">
						<article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
							<div class="thumbnail-container">
								<div class="thumbnail-top">
									{block name='product_flags'}
										<ul class="product-flags js-product-flags">
											{foreach from=$product.flags item=flag}
												{if Module::isEnabled('egstickers')}
													{hook h='displayNativeStickers' flag=$flag.type}
													{assign var="nativeFlag" value=EgStickersFlags::NativeFlag($flag.type)}
												{/if}
												{if isset($nativeFlag) &&  !empty($nativeFlag)}
													{if $nativeFlag.active}
													<li class="product-flag {if $nativeFlag.sticker_position} {if $nativeFlag.sticker_position == 1}sticker_top what1{else}sticker_bottom{/if}{/if}" {if $nativeFlag.color}style="background-color: {$nativeFlag.color}; color: {$nativeFlag.color}; padding-right: 0px !important; min-width: auto;"{/if}>
														<span>{$nativeFlag.parallel_value}</span>
														<svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 34" width="22" height="32" style="left: 100%;">
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
											{hook h='displayProductFlags' id_product=$product.id_product }
										</ul>
									{/block}
									{block name='product_thumbnail'}
										{if isset($product.images[0])}
											<a href="{$product.url}" class="thumbnail product-thumbnail">
												<picture>
													{if !empty($product.images[0].bySize.home_default.sources.avif)}<source srcset="{$product.images[0].bySize.home_default.sources.avif}" type="image/avif">{/if}
													{if !empty($product.images[0].bySize.home_default.sources.webp)}<source srcset="{$product.images[0].bySize.home_default.sources.webp}" type="image/webp">{/if}
													<img
															src="{$product.images[0].bySize.home_default.url}"
															alt="{if !empty($product.images[0].legend)}{$product.images[0].legend}{else}{$product.name|truncate:30:'...'}{/if}"
															loading="lazy"
															data-full-size-image-url="{$product.images[0].large.url}"
															width="{$product.images[0].bySize.home_default.width}"
															height="{$product.images[0].bySize.home_default.height}"
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
											</a>
										{else}
											<a href="{$product.url}" class="thumbnail product-thumbnail">
												<picture>
													{if !empty($urls.no_picture_image.bySize.home_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.avif}" type="image/avif">{/if}
													{if !empty($urls.no_picture_image.bySize.home_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.webp}" type="image/webp">{/if}
													<img
															src="{$urls.no_picture_image.bySize.home_default.url}"
															alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
															width="{$urls.no_picture_image.bySize.home_default.width}"
															height="{$urls.no_picture_image.bySize.home_default.height}"
															loading="lazy"
													/>
												</picture>
											</a>
										{/if}
									{/block}

									<div class="highlighted-informations{if !$product.main_variants} no-variants{/if}">
										{block name='quick_view'}
											<a class="quick-view js-quick-view" href="#" data-link-action="quickview">{l s='Quick view' d='Shop.Theme.Actions'}</a>
										{/block}
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
									
								</div>

								<div class="product-description">
									{block name='product_name'}
										{if $page.page_name == 'index'}
											<h3 class="h3 product-title"><a href="{$product.url}" content="{$product.url}">{$product.name}</a></h3>
										{else}
											<h2 class="h3 product-title"><a href="{$product.url}" content="{$product.url}">{$product.name}</a></h2>
										{/if}
									{/block}

									<div class="product-subtitle">{$product.name_ar nofilter}</div>

									{block name='product_price_and_shipping'}
										{if $product.show_price}
											<div class="product-price-and-shipping">

												<span class="price" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">
													{capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
													{if '' !== $smarty.capture.custom_price}
														{$smarty.capture.custom_price nofilter}
													{else}
														{$product.price}
													{/if}
												</span>

												{if $product.has_discount}
													{hook h='displayProductPriceBlock' product=$product type="old_price"}
													<span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>
												{/if}

												{hook h='displayProductPriceBlock' product=$product type="before_price"}

												{hook h='displayProductPriceBlock' product=$product type='unit_price'}

												{hook h='displayProductPriceBlock' product=$product type='weight'}
											</div>
										{/if}
									{/block}
								</div>
							</div>
						</article>
					</div>
				{/block}
			{/foreach}
		</div>
	</div> 
</section>
