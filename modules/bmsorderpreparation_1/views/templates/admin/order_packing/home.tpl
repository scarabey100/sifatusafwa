{**
 * 2007-2022 Boostmyshop
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
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div style="display: none">
	<div id="header_notifs_icon_wrapper"></div>
</div>

<div class="panel">
	<div class="row">
		<div class="col-md-12">
			<label>{l s='Barcode' mod='bmsorderpreparation'}</label>
			<input id="barecode" style="border:0px;width:100%;font-size:21px"
				   placeholder="{l s='Scan order barcode or select it in dropdown' mod='bmsorderpreparation'}" value=""/>
		</div>
	</div>	
	<div class="row">
		<select id="selectOrder">
			<option value="0">-- {l s='select'  mod='bmsorderpreparation'} --</option>
			{foreach from=$ordersInprogress item=order}
				<option value="{$order.id_order|string_format:"%.2f"}">{$order.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="panel">
	<div id="" data-tab-id="" class="tab-pane active">
		<iframe id="orderPackingFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)" src=""></iframe>
	</div>
</div>

