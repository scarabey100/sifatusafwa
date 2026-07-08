/**

 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
 if( typeof parent.iframeLoaded == "undefined"){
 	 parent.iframeLoaded=false;
 }

 $(document).ready(function(){
 

	$(function() {
		setTimeout(() => {
			const $el = $('#barecode');
			if ($el.length && $el.is(':visible') && !$el.is(':disabled')) {
			$el.focus().click();
			console.warn('#barecode clicked');
			// put caret at end if it's an input
			if ($el.is('input, textarea')) {
				const el = $el[0];
				const val = el.value || '';
				el.setSelectionRange(val.length, val.length);
			}
			} else {
			console.warn('#barecode not ready after delay');
			}
		}, 500); // adjust 500ms as needed
	});
	$(window).keypress(function(e) {	
	  	bareCode(e);	  
	});	
	$('#selectOrder').change(function(e){
		if($(this).val()){
			getUrlByIdOrder($(this).val());
		}else{
			setPanel('');
		}
	});
	
	$("input[name^='qty'").keypress(function(e){	
		e.stopImmediatePropagation();
		e.stopPropagation();
	
	});
	$("input[id^='weight'").keypress(function(e){	
		e.stopImmediatePropagation();
		e.stopPropagation();	
	});
	$("input[id^='weight'").change(function(e){	
	  var weight = 	parseFloat($(this).val());
	  if(!isNaN(weight)){
			$.ajax({
				type : 'POST',
				url : urlOrderPacking,
				data : {
					'id_order' : id_order,
					'action' : 'changeWeight',
                    'ajax': '1',
					'weight' : weight
				},
				dataType : 'json',
				success : function($data) {
					if(!$data.result || !$data.url){
							setError();
					}
				},
				error: function($data) {
					setError();
				}
			});
	  }else{
	  	alert(errorWeight);
	  }
	});
	$("input[name^='qty'").change(function(e){	
	
		var val = parseInt( $(this).val(),10),
			max =  parseInt($(this).data('qte-max'),10)
		;
	
		if(isNaN(val)){
			val = 0;
			$(this).val(val);
		}
		if(isNaN(max)) max = 0;

		if( val < 0 ) $(this).val(0);
		if( val > max ) $(this).val(max);
		
		changeQte($(this).val(),"add",$(this).attr('id'),max);			
		
	});
	
	if (typeof popupButtonText !== 'undefined') {
		$('body').append(
			'<div>' +
				'<style type="text/css">' +
					'.order-packing-popup { position: fixed; left: 0; top: 0; right: 0; bottom: 0; z-index: 999999; }' +
					'.order-packing-popup--form { position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); transform: translate(-50%, -50%); width: 300px; margin: 0; padding: 20px 40px; border: 1px solid #999; border: 1px solid rgba(0,0,0,.2); border-radius: 6px; background-clip: padding-box; background-color: #fde1e1; color: #c05c67; -webkit-box-shadow: 0 3px 9px rgb(0 0 0 / 50%); box-shadow: 0 3px 9px rgb(0 0 0 / 50%); outline: 0; z-index: 2; }' +
					'.order-packing-popup--form span { display: block; margin-bottom: 16px; font-size: 21px; font-weight: 400; text-align: center; }' +
					'.order-packing-popup--form button { padding: 10px 20px; border: 1px solid #c05c67; border-radius: 3px; background-color: rgb(239, 239, 239); font-size: 15px; text-transform: uppercase; outline: none; }' +
				'</style>' +
				'<div id="order_packing_popup" class="order-packing-popup" style="display: none;">' +
					'<div class="order-packing-popup--form alert-warning">' +
						'<span>Scan order barcode or select it in dropdown</span>' +
						'<button type="button">' + popupButtonText + '</button>' +
					'</div>' +
				'</div>' +
			'</div>'
		);

		$(document).on('click', '#order_packing_popup button', function() {
			$('#order_packing_popup').fadeOut(100);
		});
	}
});
function bareCode(event){
	var $popup = $('#order_packing_popup');
	if ($popup.length && $popup.is(':visible')) {
		return;
	}

	reset();
	if (event.keyCode == 13) { //entrer

		if(!isIframeLoaded()){
			bareCodeOrder(window.parent.$('#barecode').val());	
		}else{
			bareCodeProduct(window.parent.$('#barecode').val());
		}
	}else{
		
		//window.parent.$('#barecode').val(window.parent.$('#barecode').val() + event.key);
	}
	
}
function bareCodeOrder(bareCode){
		getUrlByIdOrder(bareCode);		

}
function showOrderPackingPopup(message) {
	var $popup = window.parent.$('#order_packing_popup');
	if ($popup.length) {
		$popup.find('span').text(message);
		$popup.fadeIn(100);
	}
}
function bareCodeProduct(bareCode){

	var rowId,
		iframe = ( $('#orderPackingFrame').contents().length ? $('#orderPackingFrame').contents() :  $('form')),
		btn,
		oldValue=0,
		message=''
	;
		try{
			rowId = iframe.find('input[name=ean][value=' + bareCode + '],input[name=upc][value=' + bareCode + ']').data('id');
		}catch(error){
			setError();
			return;
		}	
			if(rowId){
				btn = iframe.find('#inc-' + rowId);
				oldValue = iframe.find('#' + rowId).val();
				btn.click();
				message = btn.closest('tr').find('.productName').text().trim();
				if(oldValue == iframe.find('#' + rowId).val()){
					message += ' : already scanned';
                    setError(message);

					if (typeof window.parent.maxQuantityErrorMessage !== 'undefined') {
						showOrderPackingPopup(window.parent.maxQuantityErrorMessage);
					}
				}else{
					message += ' : scanned';
                    setok(message);
				}

			}else{
				setError();

				if (typeof window.parent.unknownBarcodeErrorMessage !== 'undefined') {
					showOrderPackingPopup(window.parent.unknownBarcodeErrorMessage);
				}
			}
		

}

function getUrlByIdOrder(id_order){
	$.ajax({
			type : 'POST',
			url : urlOrderPacking,
			data : {
				'id_order' : id_order,
				'action' : 'selectPanel',
                'ajax': '1'
			},
			dataType : 'json',
			success : function($data) {
				if(!$data.result || !$data.url){
						setError();
				}else{
					setPanel($data.url);
					setok();
				}
			},
			error: function($data) {
				setError();
			}
		});
}
function setPanel(url){

	window.parent.$('#orderPackingFrame').attr('src',url);
	if(url) parent.iframeLoaded=true;
}
function isIframeLoaded(){
	
	return parent.iframeLoaded;
	try{
		if(window.parent.$('#orderPackingFrame').attr('src')){
			
			return true;
		}
	}catch(e){
		return false	
	}
	return false	
}
function setError(message){
	window.parent.$('#barecode').css('color','rgb(255, 0, 0)'); //rouge
    if (!message)
    	window.parent.$('#barecode').val(errorMessage +  ' ' + window.parent.$('#barecode').val());
    else
        window.parent.$('#barecode').val(message +  ' ' + window.parent.$('#barecode').val());

    playNokSound();
}
function setok(message){
	if(message){
		window.parent.$('#barecode').val(message);
	}
	window.parent.$('#barecode').css('color','blue');
    playOkSound();
}
function reset(){

		if( window.parent.$('#barecode').css('color') != "rgb(85, 85, 85)"){ //si pas vert
			 window.parent.$('#barecode').css('color','rgb(85, 85, 85)');
			 window.parent.$('#barecode').val('');
		}
	
}
function changeQte(qte,sens,id,max){
	qte = parseInt(qte,10);
	max = parseInt(max,10);
	

	var val = parseInt( $("#"+id).val(),10);
	
	if(isNaN(val)) val = 0;
	if(isNaN(max)) max = 0;
	
	if(isNaN(qte)) return false;
	
	if(qte == 1){
		switch(sens){
			case "inc":
				if( val < max) $("#"+id).val( val+1 );
			break;
			case "dec":
				if( val > 0) $("#"+id).val(val-1);
			break;
		}
	}else{
		$("#"+id).val(qte);
	}
	//$("#"+id).trigger("change")

	var newVal = parseInt($("#"+id).val());

	if(newVal >= max && max>0){
		$("#status_"+id + ".status").html(packedMessage).attr("class", "status bg-success" );
	}else if(newVal <= 0){
		$("#status_"+id + ".status").html(noPackedMessage).attr("class", "status bg-danger" );			

	}else{
		$("#status_"+id + ".status").html((max - $("#"+id).val()) + ' ' + missingMessage).attr("class", "status bg-warning" );

	}
}

function dec(qte,id,max){
	changeQte(qte,'dec',id,max);

}
function inc(qte,id,max){
	changeQte(qte,'inc',id,max);

}
function verifForm(){
	var totalChecked=0,
		fields = $("input[name^=qty]"),
		tmp=0
	;
	fields.each(function(){
		tmp = parseInt($(this).val(),10);
		totalChecked += (isNaN(tmp) ? 0 : tmp);
	});
	
	
	if(totalToShip == totalChecked){
		window.location.replace(urlSubmit);
	}else{
		alert(errorSubmit);
	}
}

function playOkSound()
{
	if ($('#orderPackingFrame').contents().length) {
		var $audioOk = $('#orderPackingFrame').contents().find('#audio_ok');
		if ($audioOk.length) {
			$audioOk.get(0).play();
		}
	} else {
		var $audioOk = $('#audio_ok');
		if ($audioOk.length) {
			$audioOk.get(0).play();
		}
	}
}

function playNokSound()
{
	if ($('#orderPackingFrame').contents().length) {
		var $audioNok = $('#orderPackingFrame').contents().find('#audio_nok');
		if ($audioNok.length) {
			$audioNok.get(0).play();
		}
	} else {
        var $audioNok = $('#audio_nok');
		if ($audioNok.length) {
			$audioNok.get(0).play();
		}
	}
}
