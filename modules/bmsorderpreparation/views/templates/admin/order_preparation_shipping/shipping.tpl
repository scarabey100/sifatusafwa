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
    $( "#bms_template_export" ).on('change', function(){
		if($("#bms_template_export option:selected").val() != 0){
			$('#export_form').submit();
		};
    });
});
</script>

<div class="panel">
	<div class="table-responsive">
		<table border="0" width="100%">
		    <tbody><tr>
		        <td width="50%">
		            <h2>{l s='Export orders for carrier' mod='bmsorderpreparation'}</h2>
  		            <form method="POST" action="{Context::getContext()->link->getAdminLink('AdminOrderPreparationShipping', true)|escape:'htmlall':'UTF-8'}&action=exportTpl" id="export_form" enctype="multipart/form-data">
	                	<input type="hidden" name="bms_process_export" value="1"></input>
            		 	<table border="0" style="margin-left: 50px;">
		                    <tbody><tr>
		                        <td width="100px;">{l s='Template' mod='bmsorderpreparation'}</td>
   		                        <td>
   		                        	<select name="bms_template_export" id="bms_template_export" style="margin-bottom: 5px;" width="200px">
			                     		<option value="0">{l s='Choose' mod='bmsorderpreparation'}</option>
					                    {foreach from=$templates item=template}
					                    	<option value="{$template.id_template}">{$template.name}</option>
					                    {/foreach}
				                     </select>
   		                        </td>
  		                    </tr></tbody>
 		                </table>
		            </form>
		        </td>
		        <td width="50%">
		            <h2>{l s='Import tracking file' mod='bmsorderpreparation'}</h2>
		            <form method="POST" action="{Context::getContext()->link->getAdminLink('AdminOrderPreparationShipping', true)|escape:'htmlall':'UTF-8'}&action=import" id="import_form" enctype="multipart/form-data">
		                <input type="hidden" name="bms_process_import" value="1"></input>
		                <table border="0" style="margin-left: 50px;">
		                    <tbody><tr>
		                        <td width="100px;">{l s='Template' mod='bmsorderpreparation'}</td>
		                        <td>
		                            <select name="bms_template" style="margin-bottom: 5px;">
			                            {foreach from=$templates item=template}
			                            	<option value="{$template.id_template}">{$template.name}</option>
			                            {/foreach}
		                            </select>
		                        </td>
		                    </tr>
		                    <tr>
		                        <td>{l s='File' mod='bmsorderpreparation'}</td>
		                        <td><input type="file" name="bms_file" style="margin-bottom: 5px;"></td>
		                    </tr>
		                    <tr>
		                        <td>{l s='Import' mod='bmsorderpreparation'}</td>
		                        <td><input type="submit" value="Import" style="margin-bottom: 5px;"></td>
		                    </tr>
		                </tbody></table>
		
		            </form>
		
		        </td>
		    </tr>
		</tbody></table>
	</div>
</div>
<div class="panel">
	<div id="inProgress" data-tab-id="inProgress active" class="tab-pane"><iframe id="inProgressFrame" frameborder= '0' scrolling= 'no' width= '100%' onload="resizeIframe(this)"  src="{Context::getContext()->link->getAdminLink('AdminOrderTab')|escape:'htmlall':'UTF-8'}&filter=inProgress&shippingStep=1"></iframe> </div> 
</div>