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
$(function () {
  // Hide id_product and id_product_attribute filter columns (index 0 and 1)
  $('thead tr').each(function () {
    $(this).find('th:eq(0), th:eq(1)').hide();
  });

  // Also hide corresponding td in tbody rows if needed
 
});

function saveBinLocation(control)
{
    var $input = $(control);
    var value = $input.val();
    var idProduct = $input.data("product-id") || $input.attr("data-product-id") || 0;
    var idAttribute = $input.data("attribute-id") || $input.attr("data-attribute-id") || 0;

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
            'field': $input.data("field") || $input.attr("data-field") || ''
        },
        dataType: 'json',
        success: function(data) {
            $input.removeClass('saving-stock').addClass('stock-saved');
            showCustomAlert('Location saved successfully',1000);
            console.log(data)
        },
        error: function(data)
        {
            alert('An error occured saving bin location');
        }
    });
}

function saveStock(stock) {
    var $input = $(stock);
    var value = $input.val();
    var id_product = $input.data('product-id') || $input.attr("data-product-id") || 0;
    var id_product_attribute = $input.data('attribute-id') || $input.attr("data-attribute-id") || 0;

    $.ajax({
        type: 'POST',
        async: true,
        url: ajaxSaveBinLocationUrl,
        data: {
            'method': 'saveStock',
            'ajax': '1',
            'id_product': id_product,
            'id_attribute': id_product_attribute,
            'value': value,
            'field': $input.data("field") || $input.attr("data-field") || ''
        },
        dataType: 'json',
        success: function(data) {
            $input.removeClass('saving-stock').addClass('stock-saved');
            showCustomAlert('Stock level saved successfully',1000);
            console.log(data);
        },
        error: function(data)
        {
            alert('An error occured saving bin location');
        }
    });
}
function showCustomAlert(message, duration = 3000) {
    const alertBox = $('#custom-alert');
    alertBox.text(message).addClass('show');
    alertBox.show();
    setTimeout(() => {
        alertBox.removeClass('show');
        alertBox.hide();
    }, duration);
}
$(document).ready(function() {
    // Save bin location on change
    $(document).on('change', '.bin-location-input', function() {
        saveBinLocation(this);
    });

    // Save stock on change
    $(document).on('change', '.stock-level-input', function() {
        saveStock(this);
    });

    // Also save on Enter key press and remove focus
    $(document).on('keydown', '.bin-location-input, .stock-level-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            if ($(this).hasClass('bin-location-input')) {
                saveBinLocation(this);
            } else if ($(this).hasClass('stock-level-input')) {
                saveStock(this);
            }
            this.blur(); // Remove focus from the input
        }
    });
});

