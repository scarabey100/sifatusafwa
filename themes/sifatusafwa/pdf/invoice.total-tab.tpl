<table id="total-tab" width="100%" cellpadding="2">
	<tr>
		<td class="border-right" width="50%">
			{l s='Total products' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="right" width="50%">
			{displayPrice currency=$order->id_currency price=$footer.products_before_discounts_tax_excl}
		</td>
	</tr>
	{if $footer.product_discounts_tax_excl > 0}
		<tr>
			<td class="border-right" width="50%">
				{l s='Total discounts' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="right" width="50%">
				- {displayPrice currency=$order->id_currency price=$footer.product_discounts_tax_excl}
			</td>
		</tr>
	{/if}
	{if !$order->isVirtual()}
		<tr>
			<td class="border-right" width="50%">
				{l s='Shipping costs' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="right" width="50%">
				{if $footer.shipping_tax_excl > 0}
					{displayPrice currency=$order->id_currency price=$footer.shipping_tax_excl}
				{else}
					{l s='Free Shipping' d='Shop.Pdf' pdf='true'}
				{/if}
			</td>
		</tr>
	{/if}
	{if $footer.wrapping_tax_excl > 0}
		<tr>
			<td class="border-right" width="50%">
				{l s='Wrapping costs' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="right" width="50%">{displayPrice currency=$order->id_currency price=$footer.wrapping_tax_excl}</td>
		</tr>
	{/if}
	<tr>
		<td class="border-right" width="50%">
			{l s='Total (tax excl.)' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="right" width="50%">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_excl}
		</td>
	</tr>
	{if $footer.total_taxes > 0}
		<tr>
			<td class="border-right" width="50%">
				{l s='Total Tax' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="right" width="50%">
				{displayPrice currency=$order->id_currency price=$footer.total_taxes}
			</td>
		</tr>
	{/if}
	<tr>
		<td class="border-right" width="50%">
			{l s='Total' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="right" width="50%">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}
		</td>
	</tr>
</table>
