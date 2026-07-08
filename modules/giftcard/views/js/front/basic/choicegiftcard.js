/**
 * GIFTCARD
*
*    @category pricing_promotion
*    @author    EIRL Timactive De Véra<support@timactive.com>
*    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
*    @version 1.0.0
*    @license   Commercial license
*
*************************************
**         GIFTCARD                 *
**          V 1.0.0                 *
*************************************
* +
* + Languages: EN, FR
* + PS version: 1.5,1.6
*/
var mailpreview_maxwidth = 500;
var itemscarousel = [];
//fix nb item if width superior 
itemscarousel[0] = {width:980,nb:6};
itemscarousel[1] = {width:800,nb:5};
itemscarousel[2] = {width:768,nb:4};
itemscarousel[2] = {width:450,nb:3};
itemscarousel[4] = {width:320,nb:2};
itemscarousel[5] = {width:0,nb:1};
itemscarousel.sort(function(a, b){
		return b.width-a.width;
});
$( document ).ready(function() {
	initJCarousel('jcarouselcardtemplates-all');
	handleGCForm();
}
);
function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
};
/*Handle event */
function handleGCForm() {
	let currentStepClass = 'js-current-step';
	/*Step click handle*/
	$('#choicegiftcard section .step-title').click(function(event){
		let stepEnable = $(this).closest( "section" ).attr('data-gcstep-enable');
		if (stepEnable == '1') {
			$('#choicegiftcard section').removeClass(currentStepClass);
			$(this).parent().addClass(currentStepClass);
		}
	});
	/*Button click*/
	$('#choicegiftcard section button[data-rel-gcstep]').click(function(event){
		let target_step = $(this).attr('data-rel-gcstep');
		$('#'+ target_step + ' .step-title').click();
	});
	/*Show div depending receptmode*/
	$("#choicegiftcard input[name='receptmode']").change(function() {
		$("#choicegiftcard li.template_item.selected").removeClass("selected");
		$("#choicegiftcard input[name='id_gift_card_template']").val(0);
		$("#recepmode-mail-additional-information").hide(400);
		if ($("input[name='receptmode']:checked").val() == '1') {
            $("#recepmode-mail-additional-information").show(400);
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
    	var jcarouselid = 'jcarouselcardtemplates-all';
    	if(datatab != 'block_templates_all')
    	{
    		var strid = $(this).attr('id');
    		var tag_id = strid.replace("tab_template_tag","");
    		jcarouselid = 'jcarouselcardtemplates-tag'+tag_id;
    	}
    	initJCarousel(jcarouselid);
	});
	$('#choicegiftcard .thickbox-giftcard').fancybox({
		'hideOnContentClick': true,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic'
	});
	$('#choicegiftcard button[data-ta-action="preview"]').click(function(){
		//$('#choicegiftcard .ui-loader-background').show();
		$("#formgiftcard input[name='action']").val('preview');
		$("#formgiftcard").submit();
	}
	);
}
/* checking validation and update UI depending validation*/
function handleGCValidationStep() {
	
	let step_1_enable = 1;
	let step_1_val = 0;
	let step_2_val = 0;
	let step_2_enable = 0;
	let step_3_val = 0;
	let step_3_enable = 0;
	/*STEP 1 : validation check*/
	if($("#choicegiftcard input[name='receptmode']").is(':checked')) { 
		
		let receptMode = $("#choicegiftcard input[name='receptmode']:checked").val();
		if (receptMode == "1") {
			var mailTo = $("#choicegiftcard input[name='mailto'").val();
			if (isValidEmailAddress(mailTo)) {
				step_1_val = 1;
			}
		}
		else {
			step_1_val = 1;
		}
	}
	/*STEP 2 : enable/validation check*/
	if (step_1_val == 1) {
		step_2_enable = 1;
		var id_gift_card_template = $("#choicegiftcard input[name='id_gift_card_template']").val();
		if (id_gift_card_template > 0) {
			step_2_val = 1;
		}
	}
	if (step_2_val == 1) {
		/*STEP 3 : enable/validation check*/
		step_3_enable = 1;
		if ($("#choicegiftcard input[name='from']").val().trim() != '' &&
			$("#choicegiftcard input[name='lastname']").val().trim() != '' &&
			$("#choicegiftcard textarea[name='message']").val().trim() != ''){
			step_3_val = 1;
		}
	}
	$("#gc-step-receptmode button[data-rel-gcstep]").prop("disabled", true);
	$("#gc-step-template button[data-rel-gcstep]").prop("disabled", true);
	if (step_1_val==1) {
		$("#gc-step-receptmode button[data-rel-gcstep]").prop("disabled", false);
	}
	if (step_2_val==1) {
		$("#gc-step-template button[data-rel-gcstep]").prop("disabled", false);
	}
	if (step_3_val==1) {
		$("#gc-step-information button[data-ta-action]").prop("disabled", false);
	}
	$('#gc-step-receptmode').attr("data-gcstep-valid", step_1_val);
	$('#gc-step-receptmode').attr("data-gcstep-enable", step_1_enable);
	$('#gc-step-template').attr("data-gcstep-valid", step_2_val);
	$('#gc-step-template').attr("data-gcstep-enable", step_2_enable);
	$('#gc-step-information').attr("data-gcstep-enable", step_3_enable);
	$('#gc-step-information').attr("data-gcstep-valid", step_3_val);
}
function cleanGCForm(){
	$("#choicegiftcard li.template_item.selected").removeClass("selected");
	$("#choicegiftcard input[name='id_gift_card_template']").val(0);
	$("#choicegiftcard input[name='mailto'").val('');
	$("#choicegiftcard input[name='from']").val('');
	$("#choicegiftcard input[name='lastname']").val('');
	$("#choicegiftcard textarea[name='message']").val('');
	$("#choicegiftcard input[name='receptmode']").prop('checked', false);
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
	initJCarousel('jcarouselcardtemplates-all');
	if($("li.template_item.selected").length==0 ||
			((virtualmode && !$("li.template_item.selected").data('virtualuse') == 1) || 
			!virtualmode && !$("li.template_item.selected").data('physicaluse') == 1))
	{
		$("li.template_item.selected").removeClass("selected");
		var datatab =  $( ".gctabs li a.selected" ).data('tab');
		//$('#'+datatab + ' .link_template:first').click();
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
		$('#choicegiftcard .ui-loader-background').show();
		$.ajax({
			type: 'POST',
			url:	linkcgc_controller,
			async: true,
			cache: false,
			dataType: 'json',
			data: "ajax=1"+
			  "&"+$('#formgiftcard').serialize() + 
			  "&rand=" + new Date().getTime(),
			success: function(data)
			{
				$('#choicegiftcard .ui-loader-background').hide(200);
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
						// display a confirmation message
						$('#choicegiftcard .messages').html('');
						//$('#choicegiftcard .messages').prepend("<p class='success'>"+data.message+"</p>");
						if (typeof prestashop !== 'undefined') {
							prestashop.emit('updateCart', {
						        reason: null
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
									ajaxCart.updateCart(jsonData);
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
			$('#choicegiftcard .ui-loader-background').hide(200);
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
function initJCarousel(idcarousel)
{
	/*count total of template giftcard element*/
	var nbitems = $( '#'+idcarousel + ' .jcarousel ul > li:visible' ).size();
	var showprevnext = false;
	var jcarousel = $('#'+idcarousel + ' .jcarousel');
        jcarousel
            .on('jcarousel:reload jcarousel:create', function () {
                var width = jcarousel.innerWidth();
           		itemscarouselsort = itemscarousel.sort();
           		for (var key in itemscarouselsort){
  					 if(width >= itemscarouselsort[key].width)
  					 {
  					 	width = width / itemscarouselsort[key].nb;
  					 	break;
  					 }
				}
                
                /**/
                if(nbitems * width > jcarousel.innerWidth())
                {
                	showprevnext = true;
                }
                if(!showprevnext)
                {
	               	 $('#'+idcarousel + ' .jcarousel-control-prev').hide();
	               	 $('#'+idcarousel + ' .jcarousel-control-next').hide();
	               	 $('#'+idcarousel + ' .jcarousel-pagination').hide();
               	}
               	else
               	{
               		$('#'+idcarousel + ' .jcarousel-control-prev').show();
               	 	$('#'+idcarousel + ' .jcarousel-control-next').show();
               	 	$('#'+idcarousel + ' .jcarousel-pagination').show();	
               	}
                //jcarousel.jcarousel('items').css('width', width + 'px');

            })
            .jcarousel({
                wrap: 'circular',
                visible: 3,
                size:3
            });

        $('#'+idcarousel + ' .jcarousel-control-prev')
            .jcarouselControl({
                target: '-=1'
            });

        $('#'+idcarousel + ' .jcarousel-control-next')
            .jcarouselControl({
                target: '+=1'
            });
        var virtualmode = true;
    	if ($("input[name='receptmode']:checked").val() == '2')
    		virtualmode = false;
        $('#'+idcarousel + ' .jcarousel-pagination')
            .on('jcarouselpagination:active', 'a', function() {
                $(this).addClass('active');
            })
            .on('jcarouselpagination:inactive', 'a', function() {
                $(this).removeClass('active');
            })
            .on('click', function(e) {
                e.preventDefault();
            })
            .jcarouselPagination({
                perPage: 1,
                item: function(page, carouselItems) {
                	if((virtualmode && carouselItems.data('virtualuse') == 1) || 
        					!virtualmode && carouselItems.data('physicaluse') == 1)
                		return '<a href="#' + page + '" class="ta-gc-page">' + page + '</a>';
        			else
        				return '';
                    
                }
            });
}

