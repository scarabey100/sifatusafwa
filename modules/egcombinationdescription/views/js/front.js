$(document).ready(function () {
    
    $(document).on("ajaxComplete", function (event, request, settings) {
        if (request.responseJSON) { 
            if (request.responseJSON.is_quick_view == false) {
                if ($(".product__ref_js").length) {
                    $(".product__ref_js_get").text($(".product__ref_js").text());
                }
            }
        }
    });

    jQuery(document).on("click", ".change-edition", function (ev) {
        ev.preventDefault();
        var dataId = jQuery(this).data("id").toString(); // Ensure string 
        jQuery(".product-variants-item select option").removeAttr("selected"); 
        jQuery(`.product-variants-item select option[value="${dataId}"]`).attr("selected", "selected");
        setTimeout(function () {
           jQuery(".product-variants-item select").val(dataId).trigger('change');
        }, 1000);
        $('html, body').animate({
            scrollTop: $('.product-information').offset().top
        }, 1000);
    
    });
	$(document).on("input change", ".js-cart-line-product-quantity", function () {
         
            updateButtonState($(this),0);
     
	});
	$(document).on("input change", "#quantity_wanted", function () {
         
            updateButtonState($(this),0);
     
	});
    $(document).on('click', '.bootstrap-touchspin-up', function () {
             
                updateButtonState($('.js-cart-line-product-quantity'));
                updateButtonState($('#quantity_wanted'));
          
    });
 
	function updateButtonState($quantityInput) {
        if ($quantityInput.parents(".js-product-add-to-cart").length) {
                    var maxStockQty = $(this).parents(".js-product-add-to-cart").find('.quantity_available_in_stock').val();
                    var maxStockQty = parseFloat(maxStockQty);
                
                    var currentQty = parseFloat($(this).val());
        
                    var $touchspinContainer_up = $(this).parents(".js-product-add-to-cart").find(".bootstrap-touchspin-up"); // Assuming .bootstrap-touchspin is the container element
                
                    if (currentQty >= maxStockQty) {
                        $touchspinContainer_up.prop("disabled", true);
                        $touchspinContainer_up.attr("disabled", true); 
                        $(this).parents(".js-product-add-to-cart").find('.js-cart-line-product-quantity').val(maxStockQty);
                    } else {
                        $touchspinContainer_up.prop("disabled", false);
        
                    }
        } else {
            $quantityInput.each(function () {
            if ($(this).parents(".product-line-grid").length) {
                    var maxStockQty = $(this).parents(".product-line-grid").find('.quantity_available_in_stock').val();
                    var maxStockQty = parseFloat(maxStockQty);
                
                    var currentQty = parseFloat($(this).val());
        
                    var $touchspinContainer_up = $(this).parents(".product-line-grid").find(".bootstrap-touchspin-up"); // Assuming .bootstrap-touchspin is the container element
                
                    if (currentQty >= maxStockQty) {
                        $touchspinContainer_up.prop("disabled", true);
                        $touchspinContainer_up.attr("disabled", true); 
                        $(this).parents(".product-line-grid").find('.js-cart-line-product-quantity').val(maxStockQty);
                    } else {
                        $touchspinContainer_up.prop("disabled", false);
        
                    }
            } 
           });
        }            
 
	}

    updateButtonState($(".js-cart-line-product-quantity"));
    updateButtonState($("#quantity_wanted"));
   
});