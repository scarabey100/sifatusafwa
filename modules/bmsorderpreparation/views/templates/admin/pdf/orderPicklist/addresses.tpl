{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<table id="addresses-tab" cellspacing="0" cellpadding="10" style="width:100%; border:1px solid black; padding:1px;">
	<tr>
		<td width="50%">{if $delivery_address}<span style="font-weight: bold; color: #444;">{l s='Delivery Address'  mod='bmsorderpreparation' pdf='true'}</span><br/><br/>
				{$delivery_address}
			{/if}
		</td>
		<td width="50%"><span style="font-weight: bold; color: #444;">{l s='Billing Address' mod='bmsorderpreparation' pdf='true'}</span><br/><br/>
				{$invoice_address}
		</td>
	</tr>
</table>