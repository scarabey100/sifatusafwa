{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
<div class="panel faq-related-products">
	<h3><i class="icon-list"></i> {l s='Related Products' mod='hifaq'}</h3>
	{if $relatedProducts}
		<ul class="related-products-sortable list-unstyled clearfix" data-id-faq="{$id_faq|intval}">
			{foreach from=$relatedProducts item=product}
				<li class="product-pack-item media-product-pack" style="width: 125px;cursor: move;" data-id-product="{$product.id_product|intval}">
					<img class="media-product-pack-img" src="{$product.img_link|escape:'htmlall':'UTF-8'}" style="max-width: 100%">
					<span class="media-product-pack-title-custom">
						{l s='Name' mod='hifaq'}: {$product.name|escape:'html':'UTF-8'}
					</span>
					<span class="media-product-pack-ref">
						{if $product.reference}
							{l s='REF' mod='hifaq'}: {$product.reference|escape:'html':'UTF-8'}
						{else}
							&nbsp;
						{/if}
					</span>
					<span class="media-product-pack-ref">
                        {l s='ID' mod='hifaq'}: {$product.id_product|intval}
                    </span>
					<a href="#" class="btn btn-default btn-primary media-product-pack-action delete-related-product" data-id-product="{$product.id_product|intval}" data-id-faq="{$id_faq|intval}">
						<i class="icon-trash"></i>
					</a>
					<a href="{$product.link|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default btn-primary hifaq-view-product-btn">
                        {l s='View Product' mod='hifaq'}
                    </a>
				</li>
			{/foreach}
		</ul>
	{else}
		<div class="list-empty">
			<div class="list-empty-msg">
				<i class="icon-warning-sign list-empty-icon"></i>
				{l s='No records found' mod='hifaq'}
			</div>
		</div>
	{/if}
</div>