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
	/*$('.tab-page').removeClass('selected');
	/*Bootstrap*/
	$('.tab-row.active').removeClass('active');
	$('#gift_card_' + currentFormTab).show();
	$('#gift_card_link_' + currentFormTab).parent().addClass('active');
}
);
function displayGiftCardTab(tab)
{
	$('.gift_card_tab').hide();
	$('.tab-row.active').removeClass('active');
	/*Bootstrap*/
	$('#gift_card_' + tab).show();
	$('#gift_card_link_' + tab).parent().addClass('active');
	$('#currentFormTab').val(tab);
}
function previewTemplate(token)
{
	var data = $("#giftcardtemplate_form").serialize();
	var d = new Date();
	var n = d.getTime();
	url = 'index.php?controller=AdminGiftCardTemplate&token='+token+
	'&submitAction=generateImg&'+n+'&'+data;
	$('#giftcardtemplateselect_img').html('');
	var img = $('<img id="gifcardcreate">')
	$(img).attr('width','100%');
	$(img).attr('height','auto');
	$(img).attr('src', url).appendTo($('#giftcardtemplateselect_img')).fadeIn();
	$('#giftcardtemplates').hide();
	$('#giftcardtemplateselect').show();
}

