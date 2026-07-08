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
 <div class="panel">
	<div class="form-wrapper">
  		<ul class="nav nav-tabs">
    		<li>
    		    <a href="#readyToShip" data-toggle="tab" data-noRefresh='false' data-href="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=readyToShip">{l s='Ready to ship' mod='bmsorderpreparation'}</a></li>
    		<li><a href="#backorders" data-toggle="tab" data-noRefresh='false' data-href="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=backorder">{l s='Backorders' mod='bmsorderpreparation'}</a></li>
    		<li><a href="#pending" data-toggle="tab" data-noRefresh='false' data-href="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=pending">{l s='Pending payment' mod='bmsorderpreparation'}</a></li>
    		<li><a href="#holded" data-toggle="tab" data-href="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=holded">{l s='On hold' mod='bmsorderpreparation'}</a></li>
    		<li ><a href="#inProgress" data-toggle="tab" data-noRefresh='false' data-href="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=inProgress">{l s='In progress' mod='bmsorderpreparation'}</a></li>
  		</ul>
		<div class="tab-content panel">
  			<div id="readyToShip" data-tab-id="readyToShip" class="tab-pane active"><iframe id="readyToShipFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src=""></iframe></div>  
  			<div id="backorders" data-tab-id="backorders" class="tab-pane"><iframe id="backordersFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src=""></iframe></div>  
  			<div id="pending" data-tab-id="pending" class="tab-pane"><iframe id="pendingFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src=""></iframe></div>  
  			<div id="holded" data-tab-id="holded" class="tab-pane"><iframe id="holdedFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src=""></iframe></div>  
  			<div id="inProgress" data-tab-id="inProgress" class="tab-pane"><iframe id="inProgressFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src=""></iframe> </div>  
		</div>
	</div>
</div>

<script>

 
</script>