{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<script type="text/javascript">
$(document).ready(function(){
    $( "#accordion" ).accordion({
	  collapsible: true,
	  heightStyle: "content"
	});
});
</script>
<div class="panel">
	<div class="panel-heading">
		{l s='Availables codes' mod='bmsorderpreparation'}
	</div>
	<div id="accordion">
		<h2>{l s='Codes for orders' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.order item=element key=k}
				<tr>
					<td>order.{$k}</td><td>{$element}</td>
				</tr>
				{/foreach}
			</table>
		</div>

		<h2>{l s='Codes for order carrier' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.order_carrier item=element key=k}
					<tr>
						<td>order_carrier.{$k}</td><td>{$element}</td>
					</tr>
				{/foreach}
			</table>
		</div>

		<h2>{l s='Codes for order products' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.order_details item=element key=k}
					{if !is_array($element) && !is_object($element)}
					<tr>
						<td>order_details.{$k}</td><td>{$element}</td>
					</tr>
					{/if}
				{/foreach}
			</table>
		</div>

		<h2>{l s='Codes customer' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.customer item=element key=k}
					<tr>
						<td>customer.{$k}</td><td>{$element}</td>
					</tr>
				{/foreach}
			</table>
		</div>

		<h2>{l s='Codes for invoice address' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.invoice_address item=element key=k}
					<tr>
						<td>invoice_address.{$k}</td><td>{$element}</td>
					</tr>
				{/foreach}
			</table>
		</div>

		<h2>{l s='Codes delivery address' mod='bmsorderpreparation'}</h2>
		<div>
			<table style="border-spacing: 10px;">
				{foreach from=$tags.delivery_address item=element key=k}
					<tr>
						<td>delivery_address.{$k}</td><td>{$element}</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>
