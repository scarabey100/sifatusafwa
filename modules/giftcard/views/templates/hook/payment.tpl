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
<p class="payment_module">
	<a href="#" title="{l s='Pay by gift card' mod='giftcard'}" id="giftcard_sub">
		{l s='Pay by gift card' mod='giftcard'}
	</a>
</p>
<script type="text/javascript">
$( document ).ready(function() {
$('#giftcard_sub').click(function()
 {
		$.ajax({
			type: 'POST',
			url:  '{$link->getModuleLink('giftcard', 'payment')|escape:'quotes':'UTF-8'}',
			async: true,
			cache: false,
			dataType: 'json',
			data: "ajax=1"+
				  "&"+$('#formgiftcard').serialize() + 
				  "&rand=" + new Date().getTime(),
			success: function(data)
			{
				alert(data);
			},
			error:function()
			{
				alert("error");
			}
			});
	});
});
</script>