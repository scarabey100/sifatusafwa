<table id="payment-tab" width="100%" cellpadding="2">
	<tr>
		<td class="center border-bottom" width="60%">{l s='Payment method' d='Shop.Pdf' pdf='true'}</td>
		<td class="left border-left" width="40%">
			{assign var='orderPaymentCollection' value=$order_invoice->getOrderPaymentCollection()}
			{if count($orderPaymentCollection) === 1}
				{foreach from=$orderPaymentCollection item=payment}
					{$payment->payment_method}
				{/foreach}
			{else}
				<table width="100%" cellspacing="0" cellpadding="0">
					{foreach from=$orderPaymentCollection item=payment}
						<tr>
							<td>{$payment->payment_method}</td>
							<td class="right">{displayPrice currency=$payment->id_currency price=$payment->amount}</td>
						</tr>
					{/foreach}
				</table>
			{/if}
		</td>
	</tr>
</table>
