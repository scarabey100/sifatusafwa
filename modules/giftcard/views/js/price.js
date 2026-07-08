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

function getTax()
{
	if (noTax)
		return 0;
	var selectedTax = document.getElementById('id_tax_rules_group');
	var taxId = selectedTax.options[selectedTax.selectedIndex].value;
	return taxesArray[taxId];
}

function getEcotaxTaxIncluded()
{
	return ps_round(ecotax_tax_excl * (1 + ecotaxTaxRate), 2);
}

function getEcotaxTaxExcluded()
{
	return ecotax_tax_excl;
}

function formatPrice(price)
{
	var fixedToSix = (Math.round(price * 1000000) / 1000000);
	return (Math.round(fixedToSix) == fixedToSix + 0.000001 ? fixedToSix + 0.000001 : fixedToSix);
}

function calcPrice()
{
	var priceType = $('#priceType').val();
	if (priceType == 'TE')
		calcPriceTI();
	else
		calcPriceTE();
}

function calcPriceTI()
{
	var tax = getTax();
	var priceTE = parseFloat(document.getElementById('price').value.replace(/,/g, '.'));
	var newPrice = priceTE * ((tax / 100) + 1);
	document.getElementById('priceTI').value = (isNaN(newPrice) == true || newPrice < 0) ? '' :
		ps_round(newPrice, 2);
	document.getElementById('finalPrice').innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '' :
		ps_round(newPrice, 2).toFixed(2);
	document.getElementById('finalPriceWithoutTax').innerHTML = (isNaN(priceTE) == true || priceTE < 0) ? '' :
		(ps_round(priceTE, 2) + getEcotaxTaxExcluded()).toFixed(2);
	calcReduction();
	$('#priceTI').val((parseFloat($('#priceTI').val()) + getEcotaxTaxIncluded()).toFixed(2));
	$('#finalPrice').html(parseFloat($('#priceTI').val()).toFixed(2));
}

function calcPriceTE()
{
	ecotax_tax_excl =  $('#ecotax').val() / (1 + ecotaxTaxRate);
	var tax = getTax();
	var priceTI = parseFloat(document.getElementById('priceTI').value.replace(/,/g, '.'));
	var newPrice = ps_round(priceTI - getEcotaxTaxIncluded(), 2) / ((tax / 100) + 1);
	document.getElementById('priceTE').value = (isNaN(newPrice) == true || newPrice < 0) ? '' :
		ps_round(newPrice.toFixed(6), 6);
	document.getElementById('price').value = (isNaN(newPrice) == true || newPrice < 0) ? 0 : ps_round(newPrice, 9);
	document.getElementById('finalPrice').innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '' :
		ps_round(priceTI.toFixed(6), 6);
	document.getElementById('finalPriceWithoutTax').innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '' :
		ps_round(newPrice.toFixed(6), 6) + getEcotaxTaxExcluded();
	calcReduction();
}



function decimalTruncate(source, decimals)
{
	if (typeof(decimals) == 'undefined')
		decimals = 6;
	source = source.toString();
	var pos = source.indexOf('.');
	return parseFloat(source.substr(0, pos + decimals + 1));
}

function unitPriceWithTax(type)
{
	var tax = getTax();
	var priceWithTax = parseFloat(document.getElementById(type+'_price').value.replace(/,/g, '.'));
	var newPrice = priceWithTax * ((tax / 100) + 1);
	$('#'+type+'_price_with_tax').html((isNaN(newPrice) == true || newPrice < 0) ? '0.00' : ps_round(newPrice, 2).toFixed(2));
}


function changeCurrencySpecificPrice(index)
{
	var id_currency = $('#spm_currency_' + index).val();
	if (currencies[id_currency]["format"] == 2 || currencies[id_currency]["format"] == 4)
	{
		$('#spm_currency_sign_pre_' + index).html('');
		$('#spm_currency_sign_post_' + index).html(' ' + currencies[id_currency]["sign"]);
	}
	else if (currencies[id_currency]["format"] == 1 || currencies[id_currency]["format"] == 3)
	{
		$('#spm_currency_sign_post_' + index).html('');
		$('#spm_currency_sign_pre_' + index).html(currencies[id_currency]["sign"] + ' ');
	}
}

