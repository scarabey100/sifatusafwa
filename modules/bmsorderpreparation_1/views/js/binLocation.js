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

function saveBinLocation(control)
{
    var value = $(control).val();

    var idProduct =  ( $(control).attr("data-product-id") ? $(control).attr("data-product-id") : 0);
    var idAttribute = ( $(control).attr("data-attribute-id") ? $(control).attr("data-attribute-id") : 0 );

    $('#img_bin_location_' + idProduct + '_' + idAttribute).attr('src', pendingImgUrl);
    $('#img_bin_location_' + idProduct + '_' + idAttribute).show();

    $.ajax({
        type: 'POST',
        async: true,
        url: ajaxSaveBinLocationUrl,
        data: {
            'method': 'saveLocation',
            'ajax': '1',
            'id_product': idProduct,
            'id_attribute': idAttribute,
            'value': value,
            'field': ( $(control).attr("data-field") ? $(control).attr("data-field") : '')
        },
        dataType: 'json',
        success: function(data) {
            $('#img_bin_location_' + idProduct + '_' + idAttribute).attr('src', doneImgUrl);
            console.log(data)
        },
        error: function(data)
        {
            alert('An error occured saving bin location');
        }
    });
}