/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
$(document).ready(function () {
    let sliderForm = $('#hpproductsliders_form');
    let page = $(sliderForm).find('#page').val();

    changeView(sliderForm, page);

    let productsNumber = $('#hpproductsliders_form #products_number').val();

    if (!productsNumber) {
        $('#hpproductsliders_form #products_number').val(14);
    }

    $('#hpproductsliders_form').on('change', '#page', function () {
        var value = $(this).val();

        let sliderForm = $('#hpproductsliders_form');

        changeView(sliderForm, value);
        $('#hpproductsliders_form #productListTable #sortable').empty();
        if (value !== 'category' && value !== 'brands') {
            getProducts();
        }
    });

    $('.form-group.id_category').on('click', '#categories-tree [name="id_category"]', function () {
        getProducts();
    });

    $('.form-group.id_manufacturer').on('change', '#id_manufacturer', function () {
        getProducts();
    });

    $('#productIds_choose').on('change', function () {
        let value = $(this).val();
        let name = $(this).find(':selected').text();

        addProductRow(value, name);
    });

    $('#hpproductsliders_form #productListTable').on('click',' td.delete', function () {
        $(this).parent().remove();
    });
});

$(function () {

    $("#hpproductsliders_form #sortable").sortable({
        handle: ".drag-handle",
        helper: function (e, tr) {
            var originals = tr.children();
            var helper = tr.clone();

            helper.children().each(function (index) {
                $(this).width(originals.eq(index).width());
            });

            return helper;
        },

        update: function () {
            let order = [];

            $('#hpproductsliders_form #sortable tr').each(function (index) {
                order.push({
                    id: $(this).data('id'),
                    position: index + 1
                });
            });

            updateProductIDs();
        }
    });
});

function changeView(sliderForm, value) {
    if (value == 'category') {
        $(sliderForm).find('.id_manufacturer').hide();
        $(sliderForm).find('.id_category').show();
    } else if (value == 'brands') {
        $(sliderForm).find('.id_category').hide();
        $(sliderForm).find('.id_manufacturer').show();
    } else if (value == 'new' || value == 'sales') {
        $(sliderForm).find('.id_category').hide();
        $(sliderForm).find('.id_manufacturer').hide();
    }
}

function addProductRow(value, name) {
    let row = `<tr data-id="${value}">
                <td class="drag-handle">☰</td>
                <td>${name}</td>
                <td class="delete"><i class="material-icons">delete</i></td>
            </tr>`;

    $('#hpproductsliders_form #sortable').prepend(row);
    
    updateProductIDs();
}

function updateProductIDs() {
    let ids = [];
    
    $('#hpproductsliders_form #sortable tr').each(function() {
        ids.push(parseInt($(this).attr('data-id'), 10));
    });
    
    $('#hpproductsliders_form #id_products').val(JSON.stringify(ids));
    
    console.log($('#hpproductsliders_form #id_products').val());
}

function getProducts() {

    $.ajax({
        url: hproductslidersajax,
        type: 'POST',
        data: {
            idCategory: $('#hpproductsliders_form .tree-selected input[name="id_category"]').val(),
            idManufacturer: $('#hpproductsliders_form #id_manufacturer').val(),
            secret: productsliderssecret,
            numberOfProducts: $('#hpproductsliders_form #products_number').val(),
            page: $('#hpproductsliders_form #page').val(),
        },
        success: function (response) {
            $('#hpproductsliders_form #productListTable #sortable').empty();
            if (!response.products || response.products.length === 0) {
                alert('no products available for this selection');
            }
            response.products.forEach(function (item) {
                addProductRow(item.id_product, item.name);
            });
        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

