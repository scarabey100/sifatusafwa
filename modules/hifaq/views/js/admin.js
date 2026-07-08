/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
$(function() {
    $('#form-hifaq').hiPrestaTable({
        friendlyName: 'Faq',
        secureKey: faq_secure_key,
        ajaxUrl: faq_admin_controller_dir,
        identifier: 'id_faq',
        onFormDisplay: function() {
            $('#faqCategorySelect').select2({
                multiple: true,
                theme: 'bootstrap',
                width: '90%',
                placeholder: select2Placeholder
            });
        }
    });
    
    $('#form-hifaqcategory').hiPrestaTable({
        friendlyName: 'FaqCategory',
        secureKey: faq_secure_key,
        ajaxUrl: faq_admin_controller_dir,
        identifier: 'id'
    });
    
    $('#form-hifaqblock').hiPrestaTable({
        friendlyName: 'FaqBlock',
        secureKey: faq_secure_key,
        ajaxUrl: faq_admin_controller_dir,
        identifier: 'id_block',
        onFormDisplay: function() {
            $('#customFaqsBlockSelect').select2({
                multiple: true,
                theme: 'bootstrap',
                width: '90%',
                placeholder: select2Placeholder
            });
        }
    });
    
    $('#form-hifaqfeedback').hiPrestaTable({
        friendlyName: 'FaqFeedback',
        secureKey: faq_secure_key,
        ajaxUrl: faq_admin_controller_dir,
        identifier: 'id_feedback'
    });
});

hiFaq = {
    displaySelectedBlockForm: function($element, blockType) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'displaySelectedBlockForm',
                blockType: blockType,
                secure_key: faq_secure_key
            },
            beforeSend: function() {
                $element.addClass('hi-presta-module-spinner');
            },
            success: function(response) {
                $element.removeClass('hi-presta-module-spinner');

                if (response.error) {
                    showErrorMessage(response.error);
                } else {
                    $('#hi-presta-module-modal .content').html(response.content);

                    $('#customFaqsBlockSelect').select2({
                        multiple: true,
                        theme: 'bootstrap',
                        width: '90%',
                        placeholder: select2Placeholder
                    });
                }
            },
            error: function(jqXHR, error, errorThrown) {
                $element.removeClass('hi-presta-module-spinner');

                if (jqXHR.status && jqXHR.status == 400) {
                    showErrorMessage(jqXHR.responseText);
                } else {
                    showErrorMessage(ajaxErrorMessage);
                }
            }
        });
    },

    loadFeatureValues: function(idFeature) {
        let $featureValuesElement = $('.faq-related-feature-value');
        $featureValuesElement.prop('disabled', true);
        $featureValuesElement.find('option:not(:first)').remove();
        if (!idFeature || idFeature == 0) {
            return;
        }

        let $element = $('#hifaq-add-new-feature');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'getFeatureValues',
                idFeature: idFeature,
                secure_key: faq_secure_key
            },
            beforeSend: function() {
                $element.addClass('hi-presta-module-spinner');
            },
            success: function(response) {
                $element.removeClass('hi-presta-module-spinner');

                if (response.error) {
                    showErrorMessage(response.error);
                } else {
                    $.each(response.featureValues, function(index, item) {
                        $featureValuesElement.append($('<option>', {
                            value: item.id_feature_value,
                            text: item.value
                        }));
                    });

                    $featureValuesElement.prop('disabled', false);
                }
            },
            error: function(jqXHR, error, errorThrown) {
                $element.removeClass('hi-presta-module-spinner');

                if (jqXHR.status && jqXHR.status == 400) {
                    showErrorMessage(jqXHR.responseText);
                } else {
                    showErrorMessage(ajaxErrorMessage);
                }
            }
        });
    },

    saveFeature: function() {
        let idFeature = $('.faq-related-feature').val();
        let idFeatureValue = $('.faq-related-feature-value').val();
        let $element = $('#hifaq-add-new-feature');
        let idFaq = $element.attr('data-id-faq');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'saveFeature',
                idFeature: idFeature,
                idFeatureValue: idFeatureValue,
                idFaq: idFaq,
                secure_key: faq_secure_key
            },
            beforeSend: function() {
                $element.addClass('hi-presta-module-spinner');
            },
            success: function(response) {
                $element.removeClass('hi-presta-module-spinner');

                if (response.error) {
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage(response.message);
                    $('.hi-faq-related-features').replaceWith(response.content);

                    $('[data-action-type="renderFaqRelatedProducts"][data-id-element="' + idFaq + '"] .hi-module-badge-relatedFeaturesCount').html(response.relatedFeaturesCount);
                }
            },
            error: function(jqXHR, error, errorThrown) {
                $element.removeClass('hi-presta-module-spinner');

                if (jqXHR.status && jqXHR.status == 400) {
                    showErrorMessage(jqXHR.responseText);
                } else {
                    showErrorMessage(ajaxErrorMessage);
                }
            }
        });
    },

    removeFeature: function($element) {
        let idFaqFeature = $element.attr('data-id-faq-feature');
        let idFaq = $element.attr('data-id-faq');

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'removeFeature',
                idFaqFeature: idFaqFeature,
                idFaq: idFaq,
                secure_key: faq_secure_key
            },
            beforeSend: function() {
                $element.addClass('hi-presta-module-spinner');
            },
            success: function(response) {
                $element.removeClass('hi-presta-module-spinner');

                if (response.error) {
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage(response.message);
                    $('.hi-faq-related-features').replaceWith(response.content);

                    $('[data-action-type="renderFaqRelatedProducts"][data-id-element="' + idFaq + '"] .hi-module-badge-relatedFeaturesCount').html(response.relatedFeaturesCount);
                }
            },
            error: function(jqXHR, error, errorThrown) {
                $element.removeClass('hi-presta-module-spinner');

                if (jqXHR.status && jqXHR.status == 400) {
                    showErrorMessage(jqXHR.responseText);
                } else {
                    showErrorMessage(ajaxErrorMessage);
                }
            }
        });
    },

    initEvents: function() {
        $(document)
            .on('click', 'button[name="submit_block_type_form"]', function(e) {
                e.preventDefault();
                let blockType = $('#block_type').val();

                hiFaq.displaySelectedBlockForm($(this), blockType);
            })
            .on('change', '.faq-related-feature', function() {
                let idFeature = $(this).val();
                hiFaq.loadFeatureValues(idFeature);
            })
            .on('click', '#hifaq-add-new-feature', function() {
                hiFaq.saveFeature();
            })
            .on('click', '.hi-faq-delete-related-feature', function(){
                hiFaq.removeFeature($(this));
            });
    },

    init: function() {
        this.initEvents();
    }
}

$.fn.rp_position_sort = function(name) {
    var sortable_item = [];
    var sortable = $(this);

    sortable.each(function(i) {
        $('li', this).each(function(e) {
            var data_id = $(this).attr('data-id-product');
            sortable_item.push(name+'['+e+'][id_product] ='+data_id);
        });
    });
    return sortable_item.join('&');
}

function hiInitRelatedProductsSortable() {
    $('.related-products-sortable').sortable({
        stop: function(event, ui) {
            var position = $('.related-products-sortable').rp_position_sort('related_products') + '&id_faq=' + $(this).attr('data-id-faq') + '&action=sortRelatedProducts&secure_key=' + faq_secure_key;
            $.post(faq_admin_controller_dir + '&ajax=1', position);
        }
    }).disableSelection();
}

function initFAQProductSearchAutocomplete() {
    $('#related_product').autocomplete(faq_admin_controller_dir + '&ajax=1', {
        minChars: 2,
        max: 50,
        width: 500,
        formatItem: function (data) {
            return data[0]+ '. '+data[2] + '-' + data[1];
        },
        scroll: false,
        multiple: false,
        extraParams: {
            action : 'productSearch',
            secure_key : faq_secure_key,
        }
    });
}

$(document).ready(function() {
    $('#fake').closest('form').hide();
    hiFaq.init();

    $('#product_page_hook').parent().find('.help-block').hide();
    if ($('#product_page_hook').val() == 'custom') {
        $('#product_page_hook').parent().find('.help-block').show();
    }
    $('#product_page_hook').change(function(){
        $(this).parent().find('.help-block').hide();
        if ($(this).val() == 'custom') {
            $(this).parent().find('.help-block').show();
        }
    });

    /*Faq custom list block*/
    $(document).on('change', '#faq_type', function(e){
        $('.hide_block').hide();
        $('.faqs_count').show();
        if ($(this).val() == 'custom_faq') {
            $('.hide_block').hide();
            $('.faqs_multiple_list').show();
        } else if ($(this).val() == 'categories') {
            $('.hide_block').hide();
            $('.faqs_multiple_categories_list').show();
        } else if ($(this).val() == 'category_faq') {
            $('.hide_block').hide();
            $('.faqs_multiple_category_list').show();
        }
    });
    $(document).on('click', '[name="submit_cancel_block"], #cancel_related_products_modal, [name="closeModalButton"]', function() {
        if (typeof tinymce != 'undefined') {
            tinymce.remove('.autoload_rte');
        }

        $('#hi-presta-module-modal').modal('hide');
        return false;
    });

    $(document).on('click', '.add-faq-related-products', function(e){
        e.preventDefault();
        var $this = $(this);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action : "renderRelatedProducts",
                id_faq : $(this).attr('data-id-faq'),
                secure_key : faq_secure_key,
            },
            beforeSend: function(){
                $this.find('i').removeClass('icon-list').addClass('icon-refresh icon-spin');
            },
            success: function(response) {
                $this.find('i').removeClass('icon-refresh icon-spin').addClass('icon-list');
                $("#hi-presta-module-modal .content").html(response.content);
                hiInitRelatedProductsSortable();
                initFAQProductSearchAutocomplete();
                $('#hi-presta-module-modal').modal('show');
            }
        });
    });

    $(document).on('hiPrestaTable.renderFaqRelatedProducts', '#form-hifaq', function() {
        hiInitRelatedProductsSortable();
        initFAQProductSearchAutocomplete();
    });

    $(document).on('click', '#submit_related_product', function(e){
        e.preventDefault();
        var $this = $(this);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'addRelatedProduct',
                id_faq: $this.attr('data-id-faq'),
                id_product: $('#related_product').val(),
                secure_key: faq_secure_key
            },
            beforeSend: function(){
                // $this.find('i').removeClass('process-icon-save').addClass('process-icon-refresh icon-spin');
                $this.addClass('hi-presta-module-spinner');
            },
            success: function(response){
                // $this.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-save');
                $this.removeClass('hi-presta-module-spinner');

                if (response.error != '') {
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage(response.message);
                    $('#hi-presta-module-modal .faq-related-products').replaceWith(response.content);
                    $('#related_product').val('');

                    $('[data-action-type="renderFaqRelatedProducts"][data-id-element="'+ $this.attr('data-id-faq') +'"] .hi-module-badge-relatedProductsCount').html(response.relatedProductsCount);
                }
                hiInitRelatedProductsSortable();

                // $('#form-hifaq').hiPrestaTable('displayList');
            }
        });
    });

    // delete related product
    $(document).on('click', '.delete-related-product', function(e){
        e.preventDefault();
        var $this = $(this);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data:{
                ajax : true,
                action: 'deleteRelatedProduct',
                id_faq: $this.attr('data-id-faq'),
                id_product: $this.attr('data-id-product'),
                secure_key: faq_secure_key
            },
            beforeSend: function(){
                $this.find('i').removeClass('icon-trash').addClass('icon-refresh icon-spin');
            },
            success: function(response){
                $this.find('i').removeClass('icon-refresh icon-spin').addClass('icon-trash');
                if (response.error) {
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage(response.message);
                    $('#hi-presta-module-modal .faq-related-products').replaceWith(response.content);

                    $('[data-action-type="renderFaqRelatedProducts"][data-id-element="'+ $this.attr('data-id-faq') +'"] .hi-module-badge').html(response.relatedProductsCount);
                }
                hiInitRelatedProductsSortable();
            }
        });
    });

    $(document).on('click', '[name="submitRelatedCategoriesForm"]', function(e){
        e.preventDefault();
        var $this = $(this);
        var idFaq = $('#id_faq').val();
        var data = $('[name="categories[]"]').serialize();
        data += '&ajax=1';
        data += '&action=addRelatedCategories';
        data += '&id_faq=' + idFaq;
        data += '&secure_key=' + faq_secure_key;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: faq_admin_controller_dir,
            data: data,
            beforeSend: function(){
                // $this.find('i').removeClass('process-icon-save').addClass('process-icon-refresh icon-spin');
                $this.addClass('hi-presta-module-spinner');
            },
            success: function(response){
                // $this.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-save');
                $this.removeClass('hi-presta-module-spinner');

                if (response.error != '') {
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage(response.message);
                    $('#hi-presta-module-modal').modal('hide');

                    $('[data-action-type="renderFaqRelatedCategories"][data-id-element="'+ idFaq +'"] .hi-module-badge').html(response.categoriesCount);
                }
            }
        });
    });

    if ($('#customCss').length > 0) {
        CodeMirror.fromTextArea(document.getElementById('customCss'), {
            lineNumbers: true,
            mode: 'css'
        });

        $('.hi-color-picker').spectrum({
            type: 'component',
            preferredFormat: 'hex'
        });
    }
});