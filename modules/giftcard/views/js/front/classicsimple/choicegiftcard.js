/**
 * GIFTCARD
 *
 *    @category pricing_promotion
 *    @author   Timactive
 *    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 *    @version 1.0.0
 *    @license   Commercial license
 *
 *************************************
 **         GIFTCARD                 *
 *************************************
 */
var mailpreview_maxwidth = 500;

$( document ).ready(function() {
        handleGCForm();
        //default check element on page
        $("#choicegiftcard input:radio[name=receptmode]:first").click();

        if ($("#choicegiftcard li.template_item.js-template-default").length) {
            $("#choicegiftcard li.template_item.js-template-default .link_template").click();
        } else {
            $("#choicegiftcard li.template_item:first .link_template").click();
        }

    }
);
function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
};
/*Handle event */
function handleGCForm() {
    /*Show div depending receptmode*/
    $("#choicegiftcard input[name='receptmode']").change(function() {
        $("#choicegiftcard input[name='id_gift_card_template']").val(0);
        if ($("#choicegiftcard li.template_item.js-template-default").length) {
            $("#choicegiftcard li.template_item.js-template-default .link_template").click();
        } else {
            $("#choicegiftcard li.template_item:first .link_template").click();
        }
        $("#recepmode-mail-additional-information").slideUp();
        if ($("input[name='receptmode']:checked").val() == '1') {
            $("#recepmode-mail-additional-information").slideDown();
        }
        taGCTriggerTemplates();
        handleGCValidationStep();
    });
    /*Listen keyup/change/paste event to call validation*/
    $("#choicegiftcard input[name='mailto']").on('change keyup paste', function() {
        handleGCValidationStep();
    });
    $("#choicegiftcard input[name='from']").on('change keyup paste', function() {
        handleGCValidationStep();
    });
    $("#choicegiftcard input[name='lastname']").on('change keyup paste', function() {
        handleGCValidationStep();
    });
    $("#choicegiftcard textarea[name='message']").on('change keyup paste', function() {
        handleGCValidationStep();
    });

    $("#choicegiftcard .link_template").click(function(){
        var link_rel = $(this).attr('rel');
        var template_id = link_rel.replace("link_template","");
        $("#choicegiftcard input[name='id_gift_card_template']").val(template_id);
        $('#choicegiftcard .template_item').removeClass('selected');
        $('#choicegiftcard .template_item'+template_id).addClass('selected');
        handleGCValidationStep();
    });
    $('#choicegiftcard .tab_template').click(function(){
        $('#choicegiftcard .tab_template').removeClass('selected');
        var datatab = $(this).attr("data-tab");
        $(this).addClass('selected');
        $('#choicegiftcard .gctab_content').removeClass('selected');
        $('#' + datatab).addClass('selected');
    });
    $('#choicegiftcard button[data-ta-action="preview"]').click(function(){
            $("#formgiftcard input[name='action']").val('preview');
            $("#formgiftcard").submit();
        }
    );
}

/* checking validation */
function handleGCValidationStep() {

    var step_1_val = 0;
    var step_2_val = 0;
    $("#gc-step-information button[data-ta-action]").prop("disabled", true);

    /* STEP 1 : validation check */
    if($("#choicegiftcard input[name='receptmode']").is(':checked')) {
        var receptMode = $("#choicegiftcard input[name='receptmode']:checked").val();
        if (receptMode == "1") {
            var mailTo = $("#choicegiftcard input[name='mailto']").val();
            if (isValidEmailAddress(mailTo)) {
                step_1_val = 1;
            }
        }
        else {
            step_1_val = 1;
        }
    }
    /* STEP 2 : validation check */
    if (step_1_val == 1) {
        var id_gift_card_template = $("#choicegiftcard input[name='id_gift_card_template']").val();
        if (id_gift_card_template > 0) {
            step_2_val = 1;
        }
    }
    /* STEP 3 : validation check */
    if (step_2_val == 1) {
        if ($("#choicegiftcard input[name='from']").val().trim() != '' &&
            $("#choicegiftcard input[name='lastname']").val().trim() != '' &&
            $("#choicegiftcard textarea[name='message']").val().trim() != ''){
                $("#gc-step-information button[data-ta-action]").prop("disabled", false);
        }
    }
}

function cleanGCForm(){
    $("#choicegiftcard li.template_item.selected").removeClass("selected");
    $("#choicegiftcard input[name='mailto']").val('');
    $("#choicegiftcard input[name='from']").val('');
    $("#choicegiftcard input[name='lastname']").val('');
    $("#choicegiftcard textarea[name='message']").val('');
    $('#gc-step-receptmode .step-title').click();
}

function taGCTriggerTemplates()
{
    var virtualmode = true;
    if ($("input[name='receptmode']:checked").val() == '2')
        virtualmode = false;
    $.each( $( "li.template_item" ), function() {
        if((virtualmode && $(this).data('virtualuse') == 1) ||
            !virtualmode && $(this).data('physicaluse') == 1)
            $(this).show();
        else
            $(this).hide();
    });
    $.each( $( ".gctabs li a" ), function() {
        var datatab = $(this).data('tab');
        var nb = 0;
        if(virtualmode)
            nb = $('#'+datatab + ' li.template_item[data-virtualuse=\'1\']').length;
        else
            nb = $('#'+datatab + ' li.template_item[data-physicaluse=\'1\']').length;
        if(nb > 0)
            $(this).parent().show();
        else
            $(this).parent().hide();
        $(this).find('.ta-gc-number').html(nb);
    });
    if($("li.template_item.selected").length==0 ||
        ((virtualmode && !$("li.template_item.selected").data('virtualuse') == 1) ||
            !virtualmode && !$("li.template_item.selected").data('physicaluse') == 1))
    {
        $("li.template_item.selected").removeClass("selected");
    }
    $('#ta_gc_products_virtual').hide();
    $('#ta_gc_products_physical').hide();
    if(virtualmode)
    {
        $('#ta_gc_products_virtual').show();
    }
    else
    {
        $('#ta_gc_products_physical').show();
    }
}
$( document ).ready(function() {

    $('#choicegiftcard button[data-ta-action="add_to_cart"]').click(function()
    {
        var linkcgc_controller = $('#choicegiftcard').data('link-controller');
        $("#formgiftcard input[name='action']").val('addgiftcard');

        $.ajax({
            type: 'POST',
            url:    linkcgc_controller,
            async: true,
            cache: false,
            dataType: 'json',
            data: "ajax=1"+
                "&"+$('#formgiftcard').serialize() +
                "&rand=" + new Date().getTime(),
            success: function(data)
            {
                if (!data.hasError)
                {
                    cleanGCForm();
                    handleGCValidationStep();
                    $.fancybox.open({
                        content   : data.modal_content,
                        closeClick: false,
                        padding   : '0px',
                        openEffect: 'elastic',
                        closeEffect: 'fade',
                        afterShow : function() {
                            $('button[data-ta-action="dismiss"]').click(function(){
                                $.fancybox.close();
                            });
                        }
                    });
                    $('#choicegiftcard .messages').find('.error').fadeOut(function(){
                        $(this).remove();
                    });
                    $('#choicegiftcard .messages').html('');
                    if (typeof prestashop !== 'undefined') {
                        prestashop.emit('updateCart', {
                            reason: {
                                idProduct: data.id_product,
                                idProductAttribute: 0,
                                idCustomization:data.id_customization,
                                cart: data.cart,
                                linkAction:""
                            },
                            resp: {
                                hasError:false
                            }
                        });
                    }
                    else {
                        $.ajax({
                            type: 'POST',
                            headers: { "cache-control": "no-cache" },
                            url: baseUri + '?rand=' + new Date().getTime(),
                            async: true,
                            cache: false,
                            dataType : "json",
                            data: 'controller=cart&ajax=true&token=' + static_token,
                            success: function(jsonData)
                            {
                                if (typeof(ajaxCart) != "undefined")
                                    ajaxCart.updateCart(jsonData);
                                else
                                    window.location.href = window.location.href;
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                console.log("TECHNICAL ERROR: \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                            }
                        });
                    }
                }
                else
                {
                    $('#choicegiftcard .messages').find('.success').fadeOut(function(){
                        $(this).remove();
                    });
                    // display an error message
                    $('#choicegiftcard .messages').html('');
                    $('#choicegiftcard .messages').prepend("<p class='error'></p>");
                    for (var i = 0; i < data.errors.length; i++)
                        $('#choicegiftcard .error').html($('#choicegiftcard .error').html()+data.errors[i]+"<br />");

                }
            },
            error:function()
            {
                
            }
        });
        taGCTriggerTemplates();
    });
});

function countChar(val) {
    var len = val.value.length;
    if (len >= 200) {
        val.value = val.value.substring(0, 200);
    } else {
        $('#charNum').text(200 - len);
    }
};
