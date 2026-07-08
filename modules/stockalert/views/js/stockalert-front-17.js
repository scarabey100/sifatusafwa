/*
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
$('document').ready(function () {
    document.dispatchEvent(new Event('idnovateStockAlertEvent'));

    $('body').on('click', '.stockalert-add button, .stockalert-list-add-popup button', function(event) {
        event.preventDefault();

        if (typeof stockalert_url_add == 'undefined') {
            console.error('stockalert_url_add var is not defined');
            return;
        }

        let id = event.target.getAttribute('data-id');
        let form = $('#'+event.target.getAttribute('form'));

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize() + '&ajax=1',
            success: function (response) {
                response = JSON.parse(response);
                if (response.error) {
                    /*$(event.target).closest('form').find*/$('.stockalert_result_'+id).html('<article class="alert alert-warning" role="alert" data-alert="warning">' + response.message + '</article>').show();
                } else {
                    /*$(event.target).closest('form').find*/$('.stockalert_result_'+id).html('<article class="alert alert-success" role="alert" data-alert="success">' + response.message + '</article>').show();
                    /*$(event.target).closest('form').find*/$('.stockalert_button_container_'+id).hide();
                    /*$(event.target).closest('form').find*/$('#stockalert-list-add-popup-'+id+' input[name=stockalert_customer_email]').hide();
                    /*$(event.target).closest('form').find*/$('.stockalert_gdpr_'+id).hide();
                    $('.stockalert_captcha_'+id).hide();
                }

                //Update result in product list
                /*$(event.target).closest('form').find*/$('#stockalert-list-add-'+id).html($('#stockalert-list-add-popup-'+id+' .stockalert_result_'+id).html());
            }
        });

        return true;
    });

    $('body').on('click', '.stockalert-remove button, .stockalert-list-remove button', function(event) {
        event.preventDefault();

        if (typeof stockalert_url_remove == 'undefined') {
            return;
        }

        let id = event.target.getAttribute('data-id');
        let form = $('#'+event.target.getAttribute('form'));
        let stockalert_id_stockalert_subscriber = $('input[name=stockalert_id_stockalert_subscriber_'+id+']').val();

        if (stockalert_id_stockalert_subscriber < 1) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize() + '&ajax=1',
            success: function (response) {
                response = JSON.parse(response);
                if (response.error) {
                    $('.stockalert_result_'+id).html('<article class="alert alert-warning" role="alert" data-alert="warning">' + response.message + '</article>').show();
                } else {
                    $('.stockalert_result_'+id).html('<article class="alert alert-success" role="alert" data-alert="success">' + response.message + '</article>').show();
                    $('#stockalert-list-remove-'+id+' .stockalert_result').html('<article class="alert alert-success" role="alert" data-alert="success">' + response.message + '</article>').show();
                    $('.stockalert_button_container_'+id).hide();
                    $('input[name=stockalert_id_stockalert_subscriber_'+id+']').hide();
                    $('.stockalert_disclaimer_'+id).hide().prev('div').hide();
                    $('.stockalert_gdpr_'+id).hide();
                }
            }
        });

        return true;
    });

    // Display the popup when the combinations is changed on load
    prestashop.on('updatedProduct', function (data) {
        $.fancybox.close();
        const interval = setInterval(function() {
            if ($('.stockalert-popup').length === 0) {
                clearInterval(interval);

                // Your function to execute after fancybox is removed
                if (data.product_out_of_stock) {
                    $('.stockalert-add-container').replaceWith(data.product_out_of_stock);
                    document.dispatchEvent(new Event('idnovateStockAlertEvent'));
                } else {
                    console.log('ERROR - Data not received');
                }
            }
        }, 100);
    });
});
