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

var svg = null;
$( document ).ready(function() {
	$('.gift_card_tab').hide();
	$('.tab-page').removeClass('selected');
	$('#gift_card_' + currentFormTab).show();
	$('#gift_card_link_' + currentFormTab).addClass('selected');
}
);
function displayGiftCardTab(tab)
{
	$('.gift_card_tab').hide();
	$('.tab-page').removeClass('selected');
	$('#gift_card_' + tab).show();
	$('#gift_card_link_' + tab).addClass('selected');
	$('#currentFormTab').val(tab);
}

