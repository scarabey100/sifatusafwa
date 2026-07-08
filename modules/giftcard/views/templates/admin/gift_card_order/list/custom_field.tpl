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
<tr id="viewgifcardcustomdetail_{$giftcardorder->id|intval}">
<td colspan="11"  class="line_giftcard_customfields">
<div class="giftcard_custom_fields">
	<table style="min-width:350px">
		<tr><td colspan="2" class="title_header">{l s='Buyer' mod='giftcard'}</td></tr>
		<tr><td class="label">{l s='From' mod='giftcard'}</td><td>{$giftcardorder->from|escape:'html':'UTF-8'}</td></tr>
		<tr><td class="label">{l s='Reception' mod='giftcard'}</td><td>{if $giftcardorder->receptmode==2}{l s='Send by post' mod='giftcard'}{elseif $giftcardorder->receptmode==1}{l s='Email to a specific date' mod='giftcard'}{else}{l s='Print at home' mod='giftcard'}{/if}</td></tr>
		
		<tr><td colspan="2" class="title_header">{l s='Person who Receive card' mod='giftcard'}</td></tr>
		{if $giftcardorder->receptmode==1}
		<tr><td  class="label">{l s='Delivery date' mod='giftcard'}</td><td>{$giftcardorder->delivery_date|escape:'html':'UTF-8'}</td></tr>
		<tr><td  class="label">{l s='Mail' mod='giftcard'}</td><td>{$giftcardorder->to_mail|escape:'html':'UTF-8'}</td></tr>
		{/if}
		<tr><td  class="label">{l s='Lastname' mod='giftcard'}</td><td>{$giftcardorder->lastname|escape:'html':'UTF-8'}</td></tr>
		<tr><td  class="label">{l s='message' mod='giftcard'}</td><td>{$giftcardorder->message|escape:'html':'UTF-8'}</td></tr>
	</table>
</div>
</td>
</tr>
