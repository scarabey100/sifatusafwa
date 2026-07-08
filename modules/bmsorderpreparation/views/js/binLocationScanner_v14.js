$( document ).ready(function() {
    history.replaceState(null, null, ' ');
    var barcodeinput = '<div class="panel col-lg-12"><div class="row"><div class="col-md-12"><label>Barcode</label><input id="barecode" name="productFilter_ean13" autofocus style="border:0px;width:100%;font-size:21px"  placeholder="Scan product reference" value=""/></div></div></div>';
    $("#form-product").prepend(barcodeinput);

    $(window).keypress(function(e) {    
        bareCode(e);      
    });


    $( "#barecode" ).click();

});

 



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