<!--  TAX DETAILS -->
{if $tax_exempt}
	{l s='Exempt of VAT according to section 259B of the General Tax Code.' d='Shop.Pdf' pdf='true'}
{elseif (isset($tax_breakdowns) && $tax_breakdowns)}
	<table id="tax-tab" width="100%" cellpadding="2" style="border: 1px solid #000;">
		<thead>
			<tr>
				<th class="tax border-top border-left bold">{l s='Tax details' d='Shop.Pdf' pdf='true'}</th>
				<th class="tax border-top bold">{l s='Tax rates' d='Shop.Pdf' pdf='true'}</th>
				{if $display_tax_bases_in_breakdowns}
					<th class="tax border-top bold">{l s='Base price' d='Shop.Pdf' pdf='true'}</th>
				{/if}
				<th class="tax border-top border-right bold">{l s='Tax Total' d='Shop.Pdf' pdf='true'}</th>
			</tr>
		</thead>
		<tbody>
			{assign var=has_line value=false}
			{foreach $tax_breakdowns as $label => $bd}
				{assign var=label_printed value=false}
				{foreach $bd as $line}
					{if $line.rate == 0}
						{continue}
					{/if}
					{assign var=has_line value=true}
					<tr>
						<td>
							{if !$label_printed}
								{if $label == 'product_tax'}
									{l s='Products' d='Shop.Pdf' pdf='true'}
								{elseif $label == 'shipping_tax'}
									{l s='Shipping' d='Shop.Pdf' pdf='true'}
								{elseif $label == 'ecotax_tax'}
									{l s='Ecotax' d='Shop.Pdf' pdf='true'}
								{elseif $label == 'wrapping_tax'}
									{l s='Wrapping' d='Shop.Pdf' pdf='true'}
								{/if}
								{assign var=label_printed value=true}
							{/if}
						</td>
						<td class="center">
							{$line.rate} %
						</td>
						{if $display_tax_bases_in_breakdowns}
							<td class="right">
								{if isset($is_order_slip) && $is_order_slip}- {/if}
								{displayPrice currency=$order->id_currency price=$line.total_tax_excl}
							</td>
						{/if}
						<td class="right">
							{if isset($is_order_slip) && $is_order_slip}- {/if}
							{displayPrice currency=$order->id_currency price=$line.total_amount}
						</td>
					</tr>
				{/foreach}
			{/foreach}
			{if !$has_line}
				<tr>
					<td class="center" colspan="{if $display_tax_bases_in_breakdowns}4{else}3{/if}">
						{l s='Zero VAT rate' d='Shop.Pdf' pdf='true'}
					</td>
				</tr>
			{/if}
		</tbody>
	</table>
{/if}
<!--  / TAX DETAILS -->
