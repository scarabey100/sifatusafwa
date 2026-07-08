<table style="width: 100%">
	<tr>
		<td style="width: 50%">
			{if $logo_path}
				<img src="{$logo_path}" width="{$width_logo}" height="{$height_logo}" />
			{/if}
		</td>
		<td style="width: 50%; text-align: right;">
			<table style="width: 100%">
				<tr>
					<td style="font-size: 9pt; line-height: 13pt; color: #000; width: 100%;">
						{if isset($header)}{$header|escape:'html':'UTF-8'|upper}{/if}</td>
				</tr>
				<tr>
					<td style="font-size: 9pt; line-height: 13pt; color: #000; width: 100%;">
						{$date|escape:'html':'UTF-8'}</td>
				</tr>
				<tr>
					<td style="font-size: 9pt; line-height: 13pt; color: #000; width: 100%;">
						{$title|escape:'html':'UTF-8'}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br />
<br />
<table style="width: 100%;">
	<tr>
		<td style="font-size: 8pt; line-height: 12pt; color: #000;  width:100%;">
			{l s='SifatuSafwa Corporation Ltd' d='Shop.Pdf' pdf='true'}<br />
			{l s='Unit 9 Boland Industrial Estate - Fitz\'s Boreen, Mallow Road - T23 EAC8 Cork - Ireland' d='Shop.Pdf' pdf='true'}<br />
			{if isset($shop_details)}
				{l s='N° Immatriculation: %s' sprintf=[$shop_details|escape:'html':'UTF-8'] d='Shop.Pdf' pdf='true'}<br />
			{/if}
			{l s='N° EORI: IE3384319KH' d='Shop.Pdf' pdf='true'}<br />
		</td>
	</tr>
</table>
<br />
<br />