<table class="product" width="100%" cellspacing="0" cellpadding="2">
	<thead>
		<tr>
			<th class="product center" width="15%">{l s='Reference' d='Shop.Pdf' pdf='true'}</th>
			<th class="product center" width="15%">{l s='Description' d='Shop.Pdf' pdf='true'}</th>
			<th class="product center" width="14%">{l s='Hs Code' d='Shop.Pdf' pdf='true'}</th>
			<th class="product center" width="30%">{l s='Title' d='Shop.Pdf' pdf='true'}</th>
			{* <th class="product center">{l s='Tax Rate' d='Shop.Pdf' pdf='true'}</th> *}
			{* {if isset($layout.before_discount)}
				<th class="product center">{l s='Base price' d='Shop.Pdf' pdf='true'} <br /> {l s='(Tax excl.)' d='Shop.Pdf' pdf='true'}</th>
			{/if} *}
			{* <th class="product center">{l s='Unit Price' d='Shop.Pdf' pdf='true'}<br />{l s='(Tax excl.)' d='Shop.Pdf' pdf='true'}</th> *}
			<th class="product center" width="10%">{l s='Unit Price' d='Shop.Pdf' pdf='true'}</th>
			<th class="product center" width="6%">{l s='Qty' d='Shop.Pdf' pdf='true'}</th>
			{* <th class="product center">{l s='Total' d='Shop.Pdf' pdf='true'}<br />{l s='(Tax excl.)' d='Shop.Pdf' pdf='true'}</th> *}
			<th class="product center" width="10%">{l s='Total' d='Shop.Pdf' pdf='true'}</th>
		</tr>
	</thead>
	<tbody>
		<!-- PRODUCTS -->
		{foreach $order_details as $order_detail}
			{cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
			<tr class="product {$bgcolor_class}">
				<td class="product left">
					{$order_detail.product_reference}
				</td>
				<td class="product center">
					Arabic book
				</td>
				<td class="product center">
					{if isset($order_detail.hs_code)}{$order_detail.hs_code}{else}49019900{/if}
				</td>
				<td class="product left">
					{* {if $display_product_images}
						<table width="100%">
							<tr>
								<td width="15%">
									{if isset($order_detail.image) && $order_detail.image->id}
										{$order_detail.image_tag}
									{/if}
								</td>
								<td width="5%">&nbsp;</td>
								<td width="80%">
									{$order_detail.product_name}
								</td>
							</tr>
						</table>
					{else}
						{$order_detail.product_name}
					{/if} *}
					{$order_detail.product_name}
				</td>
				{* <td class="product center">
					{$order_detail.order_detail_tax_label}
				</td> *}
				{* {if isset($layout.before_discount)}
					<td class="product center">
						{if isset($order_detail.unit_price_tax_excl_before_specific_price)}
							{displayPrice currency=$order->id_currency price=$order_detail.unit_price_tax_excl_before_specific_price}
						{else}
							--
						{/if}
					</td>
				{/if} *}
				<td class="product right">
					{displayPrice currency=$order->id_currency price=$order_detail.unit_price_tax_excl_including_ecotax}
					{if $order_detail.ecotax_tax_excl > 0}
						<br>
						<small>{{displayPrice currency=$order->id_currency price=$order_detail.ecotax_tax_excl}|string_format:{l s='ecotax: %s' d='Shop.Pdf' pdf='true'}}</small>
					{/if}
				</td>
				<td class="product center">
					{$order_detail.product_quantity}
				</td>
				<td  class="product right">
					{displayPrice currency=$order->id_currency price=$order_detail.total_price_tax_excl_including_ecotax}
				</td>
			</tr>
			{* {foreach $order_detail.customizedDatas as $customizationPerAddress}
				{foreach $customizationPerAddress as $customizationId => $customization}
					<tr class="customization_data {$bgcolor_class}">
						<td class="center"> &nbsp;</td>

						<td>
							{if isset($customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_]) && count($customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_]) > 0}
								<table style="width: 100%;">
									{foreach $customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_] as $customization_infos}
										<tr>
											<td style="width: 30%;">
												{$customization_infos.name|string_format:{l s='%s:' d='Shop.Pdf' pdf='true'}}
											</td>
											<td>{if (int)$customization_infos.id_module}{$customization_infos.value nofilter}{else}{$customization_infos.value}{/if}</td>
										</tr>
									{/foreach}
								</table>
							{/if}

							{if isset($customization.datas[$smarty.const._CUSTOMIZE_FILE_]) && count($customization.datas[$smarty.const._CUSTOMIZE_FILE_]) > 0}
								<table style="width: 100%;">
									<tr>
										<td style="width: 70%;">{l s='image(s):' d='Shop.Pdf' pdf='true'}</td>
										<td>{count($customization.datas[$smarty.const._CUSTOMIZE_FILE_])}</td>
									</tr>
								</table>
							{/if}
						</td>

						<td class="center">
							({if $customization.quantity == 0}1{else}{$customization.quantity}{/if})
						</td>

						{assign var=end value=($layout._colCount-3)}
						{for $var=0 to $end}
							<td class="center">
								--
							</td>
						{/for}

					</tr>
					<!--if !$smarty.foreach.custo_foreach.last-->
				{/foreach}
			{/foreach} *}
		{/foreach}
		<!-- END PRODUCTS -->
		<!-- CART RULES -->
		{* {assign var="shipping_discount_tax_incl" value="0"}
		{foreach from=$cart_rules item=cart_rule name="cart_rules_loop"}
			{if $smarty.foreach.cart_rules_loop.first}
			<tr class="discount">
				<th class="header" colspan="{$layout._colCount}">
					{l s='Discounts' d='Shop.Pdf' pdf='true'}
				</th>
			</tr>
			{/if}
			<tr class="discount">
				<td class="white right" colspan="{$layout._colCount - 1}">
					{$cart_rule.name}
				</td>
				<td class="right white">
					- {displayPrice currency=$order->id_currency price=$cart_rule.value_tax_excl}
				</td>
			</tr>
		{/foreach} *}
	</tbody>
</table>
