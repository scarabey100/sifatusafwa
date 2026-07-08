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
<div style="display: none"><div id="header_notifs_icon_wrapper"></div></div>

  <div class="panel">

 <div class="panel">

     <h1 class="text-center">{l s='Order' mod='bmsorderpreparation'}#{$id_order} ({$reference})</h1>

    <div class="">

    <div class="row">
		<div style="width:20%;float:left;">
       		<div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
        		<div class="kpi-content">
          			<i class="icon  icon-user" style="font-size: 2em; color: rgb(138,223,52);">‎</i>
           			<span class="title">{l s='Ship to ' mod='bmsorderpreparation'}</span>
    				<span class="value">{$customerName|escape:'htmlall':'UTF-8'}</span>
       			</div>
        	</div>
        </div>
		<div style="width:20%;float:left;">
       		<div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
        		<div class="kpi-content">
          			<i class="icon  icon-truck" style="font-size: 2em; color: rgb(138,223,52);">‎</i>
           			<span class="title">{l s='Shipping method' mod='bmsorderpreparation'}</span>
    				<span class="value">{$shippingMethod|escape:'htmlall':'UTF-8'}</span>
       			</div>
        	</div>
        </div>
		<div style="width:20%;float:left;">
       		<div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
        		<div class="kpi-content">
          			<i class="icon icon-flag" style="font-size: 2em; color: rgb(138,223,52);">‎</i>
           			<span class="title">{l s='Shipping country' mod='bmsorderpreparation'}</span>
    				<span class="value">{$shippingCountry|escape:'htmlall':'UTF-8'}</span>
       			</div>
        	</div>
        </div>
		<div style="width:25%;float:left;">
       		<div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
        		<div class="kpi-content">
          			<i class="icon  icon-clock-o" style="font-size: 2em; color: rgb(138,223,52);">‎</i>
           			<span class="title">{l s='Status' mod='bmsorderpreparation'}</span>
    				<span class="value">{$orderStatus|escape:'htmlall':'UTF-8'}</span>
       			</div>
        	</div>
        </div>
		<div style="width:15%;float:left;">
       		<div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
        		<div class="kpi-content">
          			<i class="icon  icon-list" style="font-size: 2em; color: rgb(138,223,52);">‎</i>
           			<span class="title">{l s='Products count' mod='bmsorderpreparation'}</span>
    				<span class="value">{$productCount|escape:'htmlall':'UTF-8'}</span>
       			</div>
        	</div>
        </div>
       
        
    </div>
    </div>
</div>


<audio id="audio_nok" src="{$nokSoundUrl|escape:'htmlall':'UTF-8'}" ></audio>
<audio id="audio_ok" src="{$okSoundUrl|escape:'htmlall':'UTF-8'}" ></audio>
