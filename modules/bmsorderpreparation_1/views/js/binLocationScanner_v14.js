$( document ).ready(function() {
    history.replaceState(null, null, ' ');
    var barcodeinput = '<div class="panel col-lg-12"><div class="row"><div class="col-md-12"><label>Barcode</label><input id="barecode" autofocus style="border:0px;width:100%;font-size:21px"  placeholder="Scan product reference" value=""/></div></div></div>';
    $("#form-product").prepend(barcodeinput);

    $(window).keypress(function(e) {    
        bareCode(e);      
    });


    $( "#barecode" ).click();

});

function saveStock(stock) {
	
	var value = $(stock).val();
	var id_product = $(stock).data('product-id');
	var id_product_attribute = $(stock).data('attribute-id');

    $('#img_stock_' + id_product + '_' + id_product_attribute).attr('src', pendingImgUrl);
    $('#img_stock_' + id_product + '_' + id_product_attribute).show();

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
            'field': ( $(stock).attr("data-field") ? $(stock).attr("data-field") : '')
        },
        dataType: 'json',
        success: function(data) {
            $('#img_stock_' + id_product + '_' + id_product_attribute).show();
            $('#img_stock_' + id_product + '_' + id_product_attribute).attr('src', doneImgUrl);
            console.log(data);
        },
        error: function(data)
        {
            alert('An error occured saving bin location');
        }
    });
}



function bareCode(event){

        // window.parent.$('#barecode').val(window.parent.$('#barecode').val() + event.key);
        //probably needs some extra work
    if (event.keyCode == 13) { //entrer
        $('[name="productFilter_ean13"]').val(window.parent.$('#barecode').val());
        $('[id="submitFilterButtonproduct"]').click();
        reset();
    }else{
        // window.parent.$('#barecode').val(window.parent.$('#barecode').val() + event.key);

        // $('[id="submitFilterButtonproduct"]').click();
    }
}

function reset(){
    if( window.parent.$('#barecode').css('color') != "rgb(85, 85, 85)"){ //si pas vert
         window.parent.$('#barecode').css('color','rgb(85, 85, 85)');
         window.parent.$('#barecode').val('');
    }
}

function cl(message) {
	console.log(message);
}