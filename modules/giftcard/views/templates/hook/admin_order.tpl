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
function cancelGiftCardOrder(id_gift_card_order)
{
	if (confirm('{l s='Are you sure to cancel this gift card?' mod='giftcard'}')) {
           $("#formGiftCardOrderCancel input[name$='id_gift_card_order']").val(id_gift_card_order);
           $("#formGiftCardOrderCancel").submit();
    }
}
function activeGiftCardOrder(id_gift_card_order)
{
	if (confirm('{l s='Are you sure to active this gift card?' mod='giftcard'}')) {
           $("#formGiftCardOrderActive input[name$='id_gift_card_order']").val(id_gift_card_order);
           $("#formGiftCardOrderActive").submit();
    }
}
</script>

{if (isset($giftcardsorder) && $giftcardsorder && ($giftcardsorder|@count) > 0)}

{if $ps_version >= '1.6'}
	<div class="panel col-lg-7 card">
	<h3 class="card-header">
					<i class="icon-gift"></i>
					{l s='Gift Card purchased' mod='giftcard'}
	</h3>
{else}
<br/>
<fieldset>
	<legend><img src="{$base_url|escape:'quotes':'UTF-8'}modules/giftcard/logo.png" alt="" width="30px" height="30px"/>{l s='Gift Card purchased' mod='giftcard'}</legend>
{/if}
	{foreach from=$giftcardsorder item=giftcardorder}
	{if !$currentState->logable && ($giftcardorder.status=='CREATED')}
		{if isset($giftcardorder.used_order) && $giftcardorder.used_order>0}
			{if $ps_version >= '1.6'}
				<div class="alert alert-error">
					<ul class="list-unstyled">
						{l s='The gift card %1$s with code %2$s is used  but the order is cancel' mod='giftcard' sprintf=[$giftcardorder.cart_rule_name,$giftcardorder.discountcode]}
					</ul>
				</div>
			{else}
				<p class="error">{l s='The gift card %1$s with code %2$s is used  but the order is cancel' mod='giftcard' sprintf=[$giftcardorder.cart_rule_name,$giftcardorder.discountcode]}</p>
			{/if}
		{else}
			{if $ps_version >= '1.6'}
				<div class="alert alert-warning">
					<ul class="list-unstyled">
						{l s='The gift card %1$s with code %2$s must be cancel because the order is cancel' mod='giftcard' sprintf=[$giftcardorder.cart_rule_name,$giftcardorder.discountcode]}
					</ul>
				</div>
			{else}
				<p class="warn">{l s='The gift card %1$s with code %2$s must be cancel because the order is cancel' mod='giftcard' sprintf=[$giftcardorder.cart_rule_name,$giftcardorder.discountcode]}</p>
			{/if}
		{/if}
	{/if}
	{/foreach}
	<form method="post" id ="formGiftCardOrderCancel" action="{$smarty.server.REQUEST_URI|escape:'quotes':'UTF-8'}">
		<input type="hidden" name="id_gift_card_order" value="0" />
		<input type="hidden" name="cancelGiftCardOrder" value="1" />
	</form>
	<form method="post" id ="formGiftCardOrderActive" action="{$smarty.server.REQUEST_URI|escape:'quotes':'UTF-8'}">
		<input type="hidden" name="id_gift_card_order" value="0" />
		<input type="hidden" name="activeGiftCardOrder" value="1" />
	</form>
	{foreach from=$errors item=error}
		{if $ps_version >= '1.6'}
		<div class="alert alert-error">
					<ul class="list-unstyled">
						{$error}{* HTML CONTENT *}
					</ul>
		</div>
		{else}
			<p class="error">{$error}{* HTML CONTENT *}</p>
		{/if}
	{/foreach}
	{foreach from=$infos item=info}
		{if $ps_version >= '1.6'}
		<div class="alert alert-success">
					<ul class="list-unstyled">
						{$info}{* HTML CONTENT *}
					</ul>
		</div>
		{else}
			<p class="info">{$info}{* HTML CONTENT *}</p>
		{/if}
	{/foreach}
	<table class="table" width="100%" cellspacing="0" cellpadding="0">
				<colgroup>
					<col width="15%">
					<col width="15%">
					<col width="5%">
					<col width="5%">
					<col >
				</colgroup>
						<thead>
							<tr>
								<th>{l s='Type' mod='giftcard'}</th>
								<th>{l s='Amount reduction' mod='giftcard'}</th>
								<th>{l s='Code' mod='giftcard'}</th>
								<th>{l s='Qty' mod='giftcard'}</th>
								<th>{l s='Active' mod='giftcard'}</th>
								<th>{l s='Status' mod='giftcard'}</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						{foreach from=$giftcardsorder item=giftcardorder}
						<tr>
							<td>
								{if $giftcardorder.receptmode == 0}
									{l s='Print at home' mod='giftcard'}
								{/if}
								{if $giftcardorder.receptmode == 1}
									{l s='Send by email' mod='giftcard'}
								{/if}
								{if $giftcardorder.receptmode == 2}
									{l s='Send by post' mod='giftcard'}
								{/if}
							</td>
							<td>{displayPrice price=$giftcardorder.price currency=$giftcardorder.id_currency}</td>
							<td>{$giftcardorder.discountcode|escape:'htmlall':'UTF-8'}</td>
							<td style="text-align:center">{$giftcardorder.voucher_qty|intval}</td>
							<td>{if isset($giftcardorder.voucher_active)} 
									{if $giftcardorder.voucher_active}
										<img src="../img/admin/enabled.gif" alt="{l s='enabled' mod='giftcard'}"/>
									{else}
										<img src="../img/admin/disabled.gif" alt="{l s='disabled' mod='giftcard'}"/>
									{/if}
								{else}?
								{/if}</td>
							<td>
								{if $giftcardorder.status eq 'CREATED'}
									{if isset($giftcardorder.used_order) && (int)$giftcardorder.used_order>0 }
										<span class="color_field badge" style="background-color:RoyalBlue;color:white">
											{l s='Used in order #%s' mod='giftcard' sprintf=$giftcardorder.used_order}</span>
											<a target="_blank" href="{$link->getAdminLink('AdminOrders')|escape:'htmlall':'UTF-8'}&id_order={$giftcardorder.used_order|escape:'htmlall':'UTF-8'}&vieworder"> <img src="../img/admin/details.gif" alt="{l s='view' mod='giftcard'}"/></a>  
										
									{else}
										<span class="color_field badge" style="background-color:DarkGreen;color:white">{l s='Unused' mod='giftcard'}</span>
									{/if}
								{else}
										{if $giftcardorder.status eq 'WAIT'}
											<span class="color_field badge" style="background-color:DarkOrange;color:white">{l s='Waiting valid order' mod='giftcard'}</span>
										{elseif $giftcardorder.status eq 'CANCEL'}
											<span class="color_field badge" style="background-color:DarkGrey;color:white">{l s='Canceled' mod='giftcard'}</span>
										{elseif $giftcardorder.status eq 'ERROR'}
											<span class="color_field badge" style="background-color:DarkRed;color:white">{l s='Error' mod='giftcard'}</span>
										{/if}	
								{/if}
							</td>
							<td>
								{if $giftcardorder.status eq 'CREATED'}
									<a class="button btn btn-default" href="{$link->getAdminLink('AdminGiftCardOrder')|escape:'htmlall':'UTF-8'}&submitAction=generatePDF&id_gift_card_order={$giftcardorder.id_gift_card_order|intval}" target="_blank">
											<i class="icon-print"></i>&nbsp;{l s='View pdf' mod='giftcard'}
									</a>&nbsp;{if !($ps_version >= '1.6')}<br/>{/if}
								{/if}
								{if isset($giftcardorder.used_order) && $giftcardorder.used_order>0}
									{l s='Card used no action available' mod='giftcard'}
								{else}
									{if $giftcardorder.status eq 'CREATED' || $giftcardorder.status eq 'WAIT'}
									<a href="javascript:cancelGiftCardOrder('{$giftcardorder.id_gift_card_order|intval}')" rel="giftcardorder_{$giftcardorder.id_order|intval}" class="button giftcardorder_cancel btn btn-default">
										{if $ps_version >= '1.6'}<i class="icon-ban"></i>{/if}
										{l s='Cancel this gift card' mod='giftcard'}</a>
									{elseif $giftcardorder.status eq 'CANCEL' && $giftcardorder.voucher_qty > 0}
										<a href="javascript:activeGiftCardOrder('{$giftcardorder.id_gift_card_order|intval}')" rel="giftcardorder_{$giftcardorder.id_order|intval}" class="button giftcardorder_active btn btn-default">
											{if $ps_version >= '1.6'}<i class="icon-check"></i>{/if}
										{l s='Actived this gift card' mod='giftcard'}
										</a>
									{/if}
								{/if}
							</td>
						</tr>
						{/foreach}
						</tbody>
					</table>
{if $ps_version >= '1.6'}
	</div>
{else}
	</fieldset>
{/if}
{/if}
{if (isset($purchaseorders) && $purchaseorders && ($purchaseorders|@count) > 0)}
{if $ps_version >= '1.6'}
	<div class="panel">
	<h3>
					<i class="icon-gift"></i>
					{l s='Gift card used' mod='giftcard'}
	</h3>
{else}
<br/>
<fieldset>
	<legend><img src="{$base_url|escape:'quotes':'UTF-8'}modules/giftcard/logo.png" alt="" width="30px" height="30px"/>{l s='Gift card used' mod='giftcard'}</legend>
{/if}
	{foreach from=$purchaseorders item=purchaseorder}
	{if !$purchaseorder.status_logable}
			{if $ps_version >= '1.6'}
				<div class="alert alert-warning">
					<ul class="list-unstyled">
						{l s='The customer used card %1$s with code %2$s but the purchase order #%3$s is canceled' mod='giftcard' sprintf=[$purchaseorder.cart_rule_name,$purchaseorder.discountcode,$purchaseorder.id_order]}
					</ul>
				</div>
			{else}
				<p class="warn">{l s='The customer used card %1$s with code %2$s but the purchase order #%3$s is canceled' mod='giftcard' sprintf=[$purchaseorder.cart_rule_name,$purchaseorder.discountcode,$purchaseorder.id_order]}</p>
			{/if}
	{/if}
	{/foreach}
	<p class="alert alert-info">{l s='The customer used a gift card in this order, you can see from the list, the purchase order associated with this gift card' mod='giftcard'}</p>
	<table class="table" width="100%" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>{l s='Name' mod='giftcard'}</th>
								<th>{l s='Date order' mod='giftcard'}</th>
								<th>{l s='Id Order' mod='giftcard'}</th>
								<th>{l s='Order Status' mod='giftcard'}</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						{foreach from=$purchaseorders item=purchaseorder}
						<tr>
							<td>{$purchaseorder.cart_rule_name|escape:'htmlall':'UTF-8'}</td>
							<td>{dateFormat date=$purchaseorder.date_add full=1}</td>
							<td># {$purchaseorder.id_order|intval}
							<a target="_blank" href="{$link->getAdminLink('AdminOrders')|escape:'htmlall':'UTF-8'}&id_order={$purchaseorder.id_order|intval}&vieworder"> <img src="../img/admin/details.gif" alt="{l s='view' mod='giftcard'}"/></a></td>
							<td><span class="color_field badge" style="background-color:{$purchaseorder.status_color|escape:'quotes':'UTF-8'};color:white">{$purchaseorder.status_name|escape:'html':'UTF-8'}</span></td>
						</tr>
						{/foreach}
						</tbody>
		</table>
{if $ps_version >= '1.6'}
	</div>
{else}
	</fieldset>
{/if}
{/if}
