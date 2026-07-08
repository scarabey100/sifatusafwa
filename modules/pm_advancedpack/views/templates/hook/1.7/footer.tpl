<script>
function ap5_setPackContentData(retryCount) {
	if (typeof(retryCount) == 'undefined') {
		var retryCount = 0;
	}
	if (typeof(ap5_cartPackProducts) == 'object') {
		let ap5_packContentDataUpdated = false;
		for (ap5_uniquePackAttribute in ap5_cartPackProducts) {
            // Default cart preview
            $('.product-line-info span:contains("' + ap5_uniquePackAttribute + '"), .product-line-grid span:contains("' + ap5_uniquePackAttribute + '")').each(function (idx, elem) {
				var changed = $(elem).html().replace(ap5_uniquePackAttribute, ap5_cartPackProducts[ap5_uniquePackAttribute].cart);
				$(elem).html(changed);
				ap5_packContentDataUpdated = true;
			});
            // Default add to cart modal
            $('#blockcart-modal .modal-body span:contains("' + ap5_uniquePackAttribute + '")').each(function (idx, elem) {
				var changed = $(elem).html().replace(ap5_uniquePackAttribute, ap5_cartPackProducts[ap5_uniquePackAttribute].cart);
				$(elem).html(changed);
				ap5_packContentDataUpdated = true;
			});
            {if !empty($customPackAttributesSelectors)}
                // Custom selectors configurable in Advanced Pack
                const customPackAttributesSelectors = {$customPackAttributesSelectors|json_encode nofilter};
                const replacedCustomPackAttributesSelector = customPackAttributesSelectors.replaceAll('%ap5ReplaceSelector%', '"' + ap5_uniquePackAttribute + '"');
                $(replacedCustomPackAttributesSelector).each(function (idx, elem) {
                    var changed = $(elem).html().replace(ap5_uniquePackAttribute, ap5_cartPackProducts[ap5_uniquePackAttribute].cart);
                    $(elem).html(changed);
                    ap5_packContentDataUpdated = true;
                });
            {/if}
		}
		if (!ap5_packContentDataUpdated) {
			if (retryCount <= 5) {
				retryCount++;
				setTimeout(ap5_setPackContentData, 100, retryCount);
			}
		}
	}
}

document.addEventListener('DOMContentLoaded', function () {
	ap5_setPackContentData();
	$(document).ajaxSuccess(function() {
		ap5_setPackContentData();
	});
	$(document).on('ap5-After-AddPackToCart', function() {
		ap5_setPackContentData();
	});
});
</script>

{if isset($ap5_firstExecution) && $ap5_firstExecution}
{if is_array($product)}
	{assign var="ap5ProductID" value=$product.id}
{else}
	{assign var="ap5ProductID" value=$product->id}
{/if}
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		$('body').addClass('ap5-pack-page-simple-mode');
		ap5Plugin.changeBuyBlock('{pm_advancedpack::getPackAddCartURL($ap5ProductID)}', {$ap5_buyBlockPackPriceContainer nofilter});
	});
</script>
{/if}
