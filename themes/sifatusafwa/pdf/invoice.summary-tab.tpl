<table id="summary-tab" cellspacing="0" cellpadding="1" style="width: {if $addresses.invoice->vat_number}75{else}50{/if}%;">
	<tr>
		<th class="center" style="border-top: 1px solid #7C96D2; border-bottom: 1px solid #7C96D2; border-left: 1px solid #7C96D2; background-color: #D0D9EF;">{l s='Invoice Nb' d='Shop.Pdf' pdf='true'}</th>
		<th class="center" style="border-top: 1px solid #7C96D2; border-bottom: 1px solid #7C96D2; background-color: #D0D9EF;">{l s='Order ref.' d='Shop.Pdf' pdf='true'}</th>
		<th class="center" style="border-top: 1px solid #7C96D2; border-bottom: 1px solid #7C96D2;{if !$addresses.invoice->vat_number} border-right: 1px solid #7C96D2; {/if}background-color: #D0D9EF;">{l s='Date' d='Shop.Pdf' pdf='true'}</th>
		{* <th valign="middle">{l s='Invoice date' d='Shop.Pdf' pdf='true'}</th> *}
		{if $addresses.invoice->vat_number}
			<th class="center" style="border-top: 1px solid #7C96D2; border-bottom: 1px solid #7C96D2; border-right: 1px solid #7C96D2; background-color: #D0D9EF;">{l s='VAT Number' d='Shop.Pdf' pdf='true'}</th>
		{/if}
	</tr>
	<tr>
		<td>{$title|escape:'html':'UTF-8'}</td>
		<td>{$order->getUniqReference()}</td>
		<td>{dateFormat date=$order->date_add full=0}</td>
		{* <td class="center small white">{dateFormat date=$order->invoice_date full=0}</td> *}
		{if $addresses.invoice->vat_number}
			<td>{$addresses.invoice->vat_number}</td>
		{/if}
	</tr>
</table>
