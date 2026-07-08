/**
* 2008-2024 Prestaworld
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one website.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
*
* DISCLAIMER
*
* Do not alter or add/update to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    prestaworld
* @copyright 2008-2024 Prestaworld
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* International Registered Trademark & Property of prestaworld
*/

// click from cart page to save the product into customer database
$(document).on('click', '.save-to-cart', function(){
    var id_product = $(this).attr('data-id-product');
    var id_product_attribute = $(this).attr('data-id-product-attribute');
    var id_product_customization = $(this).attr('data-id-customization');
    saveCartForCustomer(id_product, id_product_attribute, id_product_customization);
});

// add to cart product from the saved cart from the cart controller
$(document).on('click', '#presta-add-cart', function(event){
    var query = $(event.target).closest('form').serialize() + '&add=1&action=update';
    $('.presta-loader').show();
    $('#presta-add-cart').attr('disabled', 'disabled');
    prestaAddToCart(query);
});

// add to cart product from the saved cart from the mycart controller
$(document).on('click', '.presta-cart-form', function(){
    var id_product = $(this).attr('data-id-product');
    var key = $(this).attr('data-key');
    var query = $('#presta-form-'+id_product+'-'+key).serialize() + '&add=1&action=update';
    prestaAddToCart(query, 'mycart');
});

// removed the saved cart product from the customer database
$(document).on('click', '#presta-data-delete', function(){
    var id_product = $(this).attr('data-id-product');
    var id_product_attribute = $(this).attr('data-id-product-attribute');
    var data_controller = $(this).attr('data-controller');
    deleteFromCustomer(id_product, id_product_attribute, data_controller);
});

$(document).on('click','.remove-from-cart', function(){
    var id_product = $(this).attr('data-id-product');
    var id_product_attribute = $(this).attr('data-id-product-attribute');
    var data_controller = $(this).attr('data-controller');
    deleteFromCustomer(id_product, id_product_attribute, data_controller);
    // setTimeout(location.reload.bind(location), 500);
});

// $(document).on('click','.remove-from-cart', function(){
//     setTimeout(location.reload.bind(location), 500);
// });

$(document).on('click', '.presta-share', function() {
    $('#presta-share').modal();
    return false;
});

$(document).on('submit', '#prestaform', function(e){
    e.preventDefault();
    var presta_name = $('#presta-name').val();
    var presta_email = $('#presta-email').val();
    var presta_textarea = $('#presta-textarea').val();

    if (!presta_name) {
        $('#prestaerror').show().text(name_empty);
        return false;
    } else {
        $('#prestaerror').hide().text('');
    }

    if (!presta_email) {
        $('#prestaerror').show().text(email_empty);
        return false;
    } else if (!validateEmail(presta_email)){
        $('#prestaerror').show().text('Email is not valid');
        return false;
    } else {
        $('#prestaerror').hide().text('');
    }

    if (!presta_textarea) {
        $('#prestaerror').show().text(msg_empty);
        return false;
    } else {
        $('#prestaerror').hide().text('');
    }

    sendCart(presta_name, presta_email, presta_textarea);
});

function prestaAddToCart(query, controller)
{
    $('.presta-loader').show();
    $.post(cart_url, query, null, 'json').then(function (resp) {
        if (resp.success) {
            $('.presta-loader').hide();
            if (typeof presta_delete === 'undefined') {
                window.location.reload(true);
            } else {
                deleteFromCustomer(resp.id_product, resp.id_product_attribute, controller);
            }
        }
    });
}
function saveCartForCustomer(id_product, id_product_attribute, id_product_customization)
{
    $.ajax({
        url : presta_url,
        cache : false,
        async: false,
        type : 'POST',
        data : {
            ajax : true,
            action : 'saveCart',
            token : prestatoken,
            id_product : id_product,
            id_product_attribute : id_product_attribute,
            id_product_customization : id_product_customization,
        },
        beforeSend: function() {
           $('.presta-loader').show();
        },
        success : function(result) {
            if (result == '1') {
                deleteCartProduct(id_product, id_product_attribute, id_product_customization);
            } else {
                alert(error);
                return false;
            }
        },
        complete: function() {
            $('.presta-loader').hide();
        },
    });
}

function deleteCartProduct(id_product, id_product_attribute, id_product_customization=null)
{
    $.ajax({
        url : cart_url,
        cache : false,
        type : 'POST',
        async: false,
        dataType : 'json',
        data : {
            ajax : true,
            action : 'update',
            token : prestatoken,
            delete : 1,
            id_product : id_product,
            id_product_attribute : id_product_attribute,
            id_customization : id_product_customization,
        },
        success : function(result) {
            if (result.success) {
                window.location.reload(true);
            }
        }
    });
}

function deleteFromCustomer(id_product, id_product_attribute, controller=false)
{
    $.ajax({
        url : presta_url,
        cache : false,
        type : 'POST',
        async: false,
        dataType : 'json',
        data : {
            ajax : true,
            action : 'deleteFromCustomer',
            delete : 1,
            token : prestatoken,
            id_product : id_product,
            id_product_attribute : id_product_attribute,
        },
        beforeSend: function() {
           $('.presta-loader').show();
        },
        complete: function() {
            $('.presta-loader').hide();
        },
        success : function(result) {
            if (result == '1') {
                if (controller == 'mycart-controller') {
                    window.location.href = success;
                } else if (controller == 'mycart') {
                    window.location.href = cart_url_show;
                } else {
                    window.location.reload(true);
                }
            }
        }
    });
}

function validateEmail(email)
{
  var check = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
  return check.test(email);
}

function sendCart(presta_name, presta_email, presta_textarea)
{
    $.ajax({
        url : presta_url,
        cache : false,
        type : 'POST',
        async: false,
        data : {
            ajax : true,
            action : 'sendCart',
            delete : 1,
            token : prestatoken,
            presta_name : presta_name,
            presta_email : presta_email,
            presta_textarea : presta_textarea,
        },
        beforeSend: function() {
            $('.prestaloader').removeClass('hidecontent');
        },
        complete: function() {
            $('.prestaloader').addClass('hidecontent');
        },
        success : function(result) {
            if (result == '1') {
                $('#prestaerror').hide().text('');
                $('#prestasuccess').show().text(cart_shared);
                window.location.reload(true);
            } else if (result == '2') {
                $('#prestaerror').show().text(name_empty);
            } else if (result == '3') {
                $('#prestaerror').show().text(name_notvalid);
            } else if (result == '4') {
                $('#prestaerror').show().text(email_empty);
            } else if (result == '5') {
                $('#prestaerror').show().text(email_notvalid);
            } else if (result == '6') {
                $('#prestaerror').show().text(msg_empty);
            } else if (result == '7') {
                $('#prestaerror').show().text(msg_notvalid);
            } else if (result == '0') {
                $('#prestaerror').show().text(cart_share_err);
            }
        }
    });
}
