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
    <div class="container text-center">
    	<div class="row">
			<span style='padding:10px 10px 0 0'>{l s='Total weight' mod='bmsorderpreparation'} : </span><input id="weight-{$id_order}" style='width:100px;display:inline' type="text" value="{$weight|escape:'htmlall':'UTF-8'}"/>
     	</div>
     	<div class="row text-center" style="margin-top:10px;">
     		<p><button onclick="verifForm()" class="btn btn-primary btn-lg">{l s='Commit packing' mod='bmsorderpreparation'}</button></p>
     	</div>
    </div>
</div>