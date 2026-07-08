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
    checkStockAlert();

    $('.stockalert-add button').on('click', function(event) {
        event.preventDefault();

        if (typeof stockalert_url_add == 'undefined') {
            console.error('stockalert_url_add var is not defined');
            return;
        }

        let stockalert_id_product = $('.stockalert-add input[name=stockalert_id_product]').val();
        let stockalert_id_product_attribute = $('.stockalert-add input[name=stockalert_id_product_attribute]').val();
        let stockalert_send_mail = $('.stockalert-add input[name=stockalert_send_mail]').val();
        let stockalert_id_stockalert_alert = $('.stockalert-add input[name=stockalert_id_stockalert_alert]').val();
        let stockalert_customer_email = $('.stockalert-add input[name=stockalert_customer_email]').val();

        $.ajax({
            type: 'POST',
            url: stockalert_url_add,
            data: {
                ajax: 1,
                stockalert_id_product: stockalert_id_product,
                stockalert_id_product_attribute: stockalert_id_product_attribute,
                stockalert_send_mail: stockalert_send_mail,
                stockalert_id_stockalert_alert: stockalert_id_stockalert_alert,
                stockalert_customer_email: stockalert_customer_email
            },
            success: function (response) {
                response = JSON.parse(response);
                if (response.error) {
                    $('.stockalert-add span.result').html('<article class="alert alert-warning" role="alert" data-alert="warning">' + response.message + '</article>').show();
                } else {
                    $('.stockalert-add span.result').html('<article class="alert alert-success" role="alert" data-alert="success">' + response.message + '</article>').show();
                    $('.stockalert-add button').hide();
                    $('.stockalert-add input[type=email]').hide();
                    $('.stockalert-add #gdpr_consent').hide();
                }
            }
        });

        return true;
    });

    $('.stockalert-remove button').on('click', function(event) {
        event.preventDefault();

        if (typeof stockalert_url_remove == 'undefined') {
            return;
        }

        let stockalert_id_stockalert_subscriber = $('.stockalert-remove input[name=stockalert_id_stockalert_subscriber]').val();
        if (stockalert_id_stockalert_subscriber < 1) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: stockalert_url_remove,
            data: {
                ajax: 1,
                stockalert_id_stockalert_subscriber: stockalert_id_stockalert_subscriber
            },
            success: function (response) {
                response = JSON.parse(response);
                if (response.error) {
                    $('.stockalert-remove span.result').html('<article class="alert alert-warning" role="alert" data-alert="warning">' + response.message + '</article>').show();
                } else {
                    $('.stockalert-remove span.result').html('<article class="alert alert-success" role="alert" data-alert="success">' + response.message + '</article>').show();
                    $('.stockalert-remove button').hide();
                    $('.stockalert-remove input[type=email]').hide();
                    $('.stockalert-remove .disclaimer').hide();
                    $('.stockalert-remove #gdpr_consent').hide();
                }
            }
        });

        return true;
    });

    if (typeof findCombination !== "undefined") {
        findCombination = (function() {
            var findCombinationCached = findCombination;

            return function(json) {
                findCombinationCached.apply(this, arguments);
                checkStockAlert();
            }
        })();
    }
});

function checkStockAlert() {
    if (typeof stockalert_url_check == 'undefined') {
        return;
    }

    if (typeof id_product == 'undefined') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: stockalert_url_check,
        data: {
            ajax: 1,
            stockalert_id_product: id_product,
            stockalert_id_product_attribute: $('#idCombination').val()
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.error) {
                $('.stockalert-add').hide();
                $('.stockalert-remove').hide();

                return;
            }

            // Product has alert and customer is not subscribed
            if (response.result === '1') {
                stockAlert = JSON.parse(response.stockAlert);

                $('#oosHook').show();

                $('.stockalert-add').show();
                $('.stockalert-add button').show();
                $('.stockalert-add input[type=email]').show();
                $('.stockalert-add #gdpr_consent').show();
                $('.stockalert-add span.result').hide();

                $('.stockalert-remove').hide();


                //Update form vars
                $('.stockalert-add input[name=stockalert_id_product]').val(id_product);
                $('.stockalert-add input[name=stockalert_id_product_attribute]').val($('#idCombination').val());
                $('.stockalert-add input[name=stockalert_send_mail]').val(stockAlert.send_mail);
                $('.stockalert-add input[name=stockalert_id_stockalert_alert]').val(stockAlert.id_stockalert_alert);

                if (stockAlert.popup == 1) {
                    $.fancybox({
                        type: 'inline',
                        autoSize: false,
                        width: '500',
                        autoHeight: true,
                        padding: 0,
                        href: $('.stockalert-add'),
                        modal: false,
                        wrapCSS: 'stockalert-popup',
                        parent: 'body',
                        helpers: {
                            overlay : {
                                closeClick : true,
                                locked: false,
                            }
                        },
                        afterClose: function() {
                            $('.stockalert-add').show();
                        }
                    });
                }

                return;
            }

            //Customer already has an alert
            if (response.result === '2') {
                stockAlert = JSON.parse(response.stockAlert);

                $('#oosHook').show();

                $('.stockalert-add').hide();

                $('.stockalert-remove').show();
                $('.stockalert-remove button').show();
                $('.stockalert-remove input[type=email]').show();
                $('.stockalert-remove .disclaimer').show();
                $('.stockalert-remove #gdpr_consent').show();
                $('.stockalert-remove span.result').hide();

                //Update form vars
                $('.stockalert-remove input[name=stockalert_id_stockalert_subscriber]').val(stockAlert.id_stockalert_subscriber);

                return;
            }

            // Product has no alert
            if (response.result === '3') {
                $('.stockalert-add').hide();
                $('.stockalert-remove').hide();

                //Update form vars
                $('.stockalert-add input[name=stockalert_id_product]').val('');
                $('.stockalert-add input[name=stockalert_id_product_attribute]').val('');
                $('.stockalert-add input[name=stockalert_send_mail]').val('');
                $('.stockalert-add input[name=stockalert_id_stockalert_alert]').val('');
                $('.stockalert-remove input[name=stockalert_id_stockalert_subscriber]').val('');

                return;
            }

            // Product has alert and is guest
            if (response.result === '4') {
                stockAlert = JSON.parse(response.stockAlert);

                $('#oosHook').show();
                $('.stockalert-add').show();
                $('.stockalert-remove').hide();

                //Update form vars
                $('.stockalert-add input[name=stockalert_id_product]').val(id_product);
                $('.stockalert-add input[name=stockalert_id_product_attribute]').val($('#idCombination').val());
                $('.stockalert-add input[name=stockalert_send_mail]').val(stockAlert.send_mail);
                $('.stockalert-add input[name=stockalert_id_stockalert_alert]').val(stockAlert.id_stockalert_alert);
                $('.stockalert-add input[type=email]').show();
                $('.stockalert-add [type=submit]').show();
                $('.stockalert-add span.result').html('').hide();

                if (stockAlert.popup == 1) {
                    $.fancybox({
                        type: 'inline',
                        autoSize: false,
                        width: '500',
                        autoHeight: true,
                        padding: 0,
                        href: $('.stockalert-add'),
                        modal: false,
                        wrapCSS: 'stockalert-popup',
                        parent: 'body',
                        helpers: {
                            overlay : {
                                closeClick : true,
                                locked: false,
                            }
                        },
                        afterClose: function() {
                            $('.stockalert-add').show();
                        }
                    });
                }

                return;
            }
        }
    });
}
