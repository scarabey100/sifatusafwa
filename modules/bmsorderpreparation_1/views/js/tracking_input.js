/**
 * 2007-2022 Boostmyshop
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
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function()
{		
	$('.tracking_number').keypress(function(e){
	    if(e.keyCode == 13 || e.keyCode == 10)
	    {
			return false;
	    }
	});
	$('.tracking_number').focusout(function(e){
	    return updateBtnSave(this);
	});
});

function updateBtnSave(self){
	if($(self).val() != $(self).attr("initial-data")){
		updateAjax(self);
	}
}
function updateAjax(self){
	if(!$(self).attr('order-id')) return;
	var order_id = $(self).attr('order-id');
	var value = $(self).val();

	$.ajax({
		url: ajaxUrl,
		data: {
			id_order : order_id,
            'ajax': '1',
	  		tracking_number : value
		}
	}).done(function( data ) {
		data = jQuery.parseJSON(data);
		if (data.message == 'error') {
	    	windonError(data, self);
	    }else{
	    	onSuccess(data, self);
	    }
  	});
  	
  	return false;
}

function onError(data, self){
	window.parent.showErrorMessage('The update has not been successfully completed, an error occurred');
	$(self).val($(self).attr("initial-data"));		
}

function onSuccess(data, self){
	window.parent.showSuccessMessage('Tracking number successfully updated');
	$(self).attr("initial-data", data.value);
}