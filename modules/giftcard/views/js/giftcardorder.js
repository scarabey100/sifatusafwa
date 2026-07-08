/**
 * GIFTCARD
*
*    @category pricing_promotion
*    @author    EIRL Timactive De Véra<support@timactive.com>
*    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
*    @version 1.0.0
*    @license   Commercial license
*
*************************************
**         GIFTCARD                 *
**          V 1.0.0                 *
*************************************
* +
* + Languages: EN, FR
* + PS version: 1.5,1.6
*/

function show_custom(token,id_gift_card_order)
{
	var current_element = $('#viewgifcardcustom_'+id_gift_card_order);
		var ajax_params = {
			'id_gift_card_order':id_gift_card_order,
			'controller': 'AdminGiftCardOrder',
			'token': token,
			'action': 'show_custom',
			'ajax': true
		};
		if(current_element.hasClass("open"))
		{
			$('#viewgifcardcustomdetail_'+id_gift_card_order).hide(200);
			current_element.removeClass('open');
			current_element.closest('td').removeClass('viewgifcardcustomopen');
			
		}
		else
		{
			$.ajax({
				url: 'index.php',
				data: ajax_params,
				dataType: 'html',
				cache: false,
				async: false,
				success: function(data) {
					current_element.closest('tr').after(data);
					current_element.closest('td').addClass('viewgifcardcustomopen');
					current_element.addClass("open");
				}
			});
		}
		/*
		if (current_element.data('opened'))
		{
			current_element.find('img').attr('src', '../img/admin/more.png');
			current_element.parent().parent().parent().find('.details_'+id).hide('fast');
			current_element.data('opened', false);
		}
		else
		{
			current_element.find('img').attr('src', '../img/admin/less.png');
			current_element.parent().parent().parent().find('.details_'+id).show('fast');
			current_element.data('opened', true);
		}*/
	
}