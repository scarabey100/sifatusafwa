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
<form accept="/" method="post" class="hifaq-related-products-form" id="relatedProductForm">
	<div class="panel">
		<h3><i class="icon-cogs"></i> {l s='Add related product' mod='hifaq'}</h3>
		<div class="form-wrapper" style="max-width:95%;">
			<div class="form-group">
				<label>
					{l s='Search Product' mod='hifaq'}
					<div class="hi-module-whats-this">
						<a href="#" data-doc="productSearch">{l s='What\'s this?' mod='hifaq'}</a>
					</div>
				</label>
				<input class="form-control" type="text" name="related_product" id="related_product" placeholder="{l s='Start typing product name' mod='hifaq'}">
			</div>
		</div>
		<div class="panel-footer">
			<button type="button" class="btn btn-secondary pull-left" name="closeModalButton" id="closeModalButton"><i class="process-icon-cancel"></i> {l s='Close' mod='hifaq'}</button>
			<button type="submit" class="btn btn-default pull-right" name="submit_related_product" id="submit_related_product" data-id-faq="{$id_faq|intval}"><i class="icon-save"></i> {l s='Add' mod='hifaq'}</button>
		</div>
	</div>
</form>