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
<div id="giftcardstatus" class="row hidden-xs hidden-sm">
	<div {if $ps_version >= "1.6" }class="col-lg-3"{/if}>
		<img src="../modules/giftcard/views/img/giftcardorder.png" {if $ps_version < "1.6" }style="float:left"{/if}/>
	</div>
	<div  class="block {if $ps_version >= "1.6" }col-lg-9 panel{/if}">
	<h3 style="margin:0px 0px 0px 0px;">&nbsp;{l s='Meaning of status' mod='giftcard'}</h3>
	<table class="table" style="width:100%">
		<thead>
			<th>{l s='Status' mod='giftcard'}</th>
			<th>{l s='Description' mod='giftcard'}</th>
		</thead>
		<tr>
			<td style="background-color:DarkOrange;text-align:center;color:#fff" class="label">{l s='Wait order accept' mod='giftcard'}</td>
			<td>{l s='The order is awaiting payment, so the card is not sent and the corresponding discount code is not generated' mod='giftcard'}</td>
		</tr>
		<tr>
			<td style="background-color:BlueViolet;text-align:center;color:#fff" class="label">{l s='Not used' mod='giftcard'}</td>
			<td>{l s='The gift card has been create, however it is not yet used.' mod='giftcard'}</td>
		</tr>
		<tr>
			<td style="background-color:LimeGreen;text-align:center;color:#fff" class="label">{l s='Used' mod='giftcard'}</td>
			<td>{l s='The gift card has been used, you can view the associated command.' mod='giftcard'}</td>
		</tr>
		<tr>
			<td style="background-color:DarkGrey;color:#fff" class="label">{l s='Cancel' mod='giftcard'}</td>
			<td>{l s='The gift card has been canceled, for example related to an order cancellation or refund.' mod='giftcard'}</td>
		</tr>
	</table>
	</div>
</div>
{if $ps_version < "1.6" }
	<br style="clear:both"/>
{/if}




