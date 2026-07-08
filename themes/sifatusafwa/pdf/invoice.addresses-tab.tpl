
<br>
<br>
<br>
<br>
<br>
<br>
<table id="addresses-tab" cellspacing="0" cellpadding="0">
	<tr>
		<td width="50%">
			{if $delivery_address}
				<table style="width: 75%;" cellspacing="0" cellpadding="2">
					<tr>
						<td class="bold">{l s='Shipping Address' d='Shop.Pdf' pdf='true'}</td>
					</tr>
					<tr>
						<td style="border: 1px solid #000;">{$delivery_address}</td>
					</tr>
				</table>
			{/if}
		</td>
		<td width="50%">
			<table style="width: 75%;" cellspacing="0" cellpadding="2">
				<tr>
					<td class="bold">{l s='Invoice Address' d='Shop.Pdf' pdf='true'}</td>
				</tr>
				<tr>
					<td style="border: 1px solid #000;">{$invoice_address}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
