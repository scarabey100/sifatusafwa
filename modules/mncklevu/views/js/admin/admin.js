/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

$(document).ready(function() {
    var $form = $('#store_synchronization_form');
    if ($form.length) {
        var ajaxUrl = $form.find('input[name="ajax_url"]').val();
        var productCount = 0;
        var productCountPerRequest = 0;
        var start = 0;

        function displayError(message) {
            $('#store_synchronization_form_error').text(message);
            $('#store_synchronization_form_error_container').show();
            $('#store_synchronization_form_process_image').hide();
        }

        function updateProgress() {
            $('#store_synchronization_form_progress').text(
                start + '/' + productCount + ' - ' + (productCount > 0 ? Math.round((start/productCount * 100 + Number.EPSILON) * 100) / 100 : '0') + '%'
            );
        }

        function synchronizeProducts() {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    ajax: 1,
                    action: 'synchronize_products',
                    start: start,
                    limit: productCountPerRequest,
                },
                cache: false,
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error !== 'undefined') {
                        displayError(response.error);
                    } else {
                        start += productCountPerRequest;
                        if (start > productCount) {
                            start = productCount;
                        }

                        updateProgress();

                        if (start < productCount) {
                            synchronizeProducts();
                        } else {
                            setTimeout(
                                function() {
                                    $.ajax({
                                        url: ajaxUrl,
                                        type: 'POST',
                                        data: {
                                            ajax: 1,
                                            action: 'finalize_products_synchronization',
                                        },
                                        cache: false,
                                        dataType: 'json',
                                        success: function(response) {
                                            $('#store_synchronization_form_process_image').hide();
                                            $('#store_synchronization_form_progress').text('');
                                            $('#store_synchronization_form_progress_container').hide();
                                            $('#store_synchronization_form_status_label').text(response.status);

                                            setTimeout(
                                                function() {
                                                    window.location.href = $form.find('input[name="return_url"]').val();
                                                },
                                                1000
                                            );
                                        }
                                    });
                                },
                                1000
                            );
                        }
                    }
                }
            });
        }

        setTimeout(
            function() {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        ajax: 1,
                        action: 'initialize',
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(response) {
                        if (typeof response.error !== 'undefined') {
                            displayError(response.error);
                        } else {
                            productCount = parseInt(response.product_count);
                            productCountPerRequest = parseInt(response.product_count_per_request);
                            if (productCountPerRequest == 0) {
                                productCountPerRequest = productCount;
                            }

                            $('#store_synchronization_form_status_label').text(response.status);
                            $('#store_synchronization_form_progress_container').show();

                            updateProgress();
                            synchronizeProducts();
                        }
                    }
                });
            },
            1000
        );
    } else if (typeof mncklevu !== 'undefined') {
        $(document).on('click', '.btn-group-action .dropdown-menu .synchronize', function(e) {
            e.preventDefault();

            if ($(this).data('connection_status')) {
                document.location.href = $(this).attr('href');
            } else if (typeof $.growl !== 'undefined') {
                $.growl.warning({message: mncklevu.сonnection_warning_message});
            } else {
                alert(mncklevu.сonnection_warning_message);
            }
        });
    }
});
