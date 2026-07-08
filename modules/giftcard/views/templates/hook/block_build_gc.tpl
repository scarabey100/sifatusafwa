{*
 *
 * GIFT CARD
 *
 * @category pricing_promotion
 * @author EIRL Timactive De Véra
 * @copyright TIMACTIVE 2013
 * @version 1.0.0
 *
 *************************************
 **            GIFT CARD			 *              
 **             V 1.0.0              *
 *************************************
 * +
 * + Languages: EN, FR
 * + PS version: 1.5
 *
 *}
<script type="text/javascript">
function buildGiftCardOrder() {
    $("#formGiftCardOrderBuild").submit();
}
</script>
<div class="card">
<div class="card-header"><i class="icon-gift"></i>{l s='Gift Card product detect' mod='giftcard'}</div>
	<div class="card-body">
	<a href="javascript:buildGiftCardOrder('{$giftcardorder.id_gift_card_order|intval}')" rel="giftcardorder_{$giftcardorder.id_order|intval}" class="button giftcardorder_active btn btn-default">
		<i class="icon-check"></i>
		{l s='Build gift card order' mod='giftcard'}
	</a>
	</div>
</div>
<form method="post" id ="formGiftCardOrderBuild" action="{$smarty.server.REQUEST_URI|escape:'quotes':'UTF-8'}">
    <input type="hidden" name="buildGiftCardOrder" value="1" />
</form>