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
 <div class="panel text-center">
	<h2 class="text-center">{l s='This order is packed' mod='bmsorderpreparation'}</h2></br>
    <button class="btn btn-primary" onclick=location.href="{Context::getContext()->link->getAdminLink('AdminOrderPreparation')|escape:'htmlall':'UTF-8'}&id_order={$id_order}&action=print&document=OrderPickList">{l s='Download picking list' mod='bmsorderpreparation'}</button>
    <button class="btn btn-primary" onclick=location.href="{Context::getContext()->link->getAdminLink('AdminOrderPreparation')|escape:'htmlall':'UTF-8'}&id_order={$id_order}&action=print&document=DeliverySlip">{l s='Download packing slip' mod='bmsorderpreparation'}</button>
    <button class="btn btn-primary" onclick=location.href="{Context::getContext()->link->getAdminLink('AdminOrderPreparation')|escape:'htmlall':'UTF-8'}&id_order={$id_order}&action=print&document=Invoice">{l s='Download invoice' mod='bmsorderpreparation'}</button>
    <button class="btn btn-primary" onclick=location.href="{Context::getContext()->link->getAdminLink('AdminOrderPreparationShipping')|escape:'htmlall':'UTF-8'}&id_order={$id_order}&action=exportTpl">{l s='Download shipping label' mod='bmsorderpreparation'}</button>
</div> 
<div class="panel text-center">
	<h2 class="text-center">{l s='Add tracking number' mod='bmsorderpreparation'}</h2>
	<input id="tracking" style="width:200px;display:inline;margin-right:10px" type="text" name="tracking" value="{$shippingNumber}"><button class="btn btn-primary" onclick="addTracking()" style="margin-right:10px">{l s='Add tracking' mod='bmsorderpreparation'}</button><button class="btn btn-primary" onclick="removeTracking()">{l s='Skip tracking' mod='bmsorderpreparation'}</button>
</div>