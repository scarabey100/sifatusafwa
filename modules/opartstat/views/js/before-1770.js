/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
function osDisplayPriceToHuman(price) {
    price = Number(price)
    /* 1.7.5.0 convert currency format to int used by formatCurrency method */
    if(currencyFormat = '#,##0.00 ¤')
        currencyFormat = 2

    if(price < 0)
		var result = '-'+formatCurrency(price, currencyFormat, currencySign, currencyBlank)
	else 
		var result = formatCurrency(price, currencyFormat, currencySign, currencyBlank)
	return result
}