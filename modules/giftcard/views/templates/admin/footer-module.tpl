{*
 * Cart Reminder
 * 
 *    @category advertising_marketing
 *    @author    Timactive - Romain DE VERA
 *    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 *    @version 1.0.0
 *    @license   Commercial license
 *
 *************************************
 **         CART REMINDER            *
 **          V 1.0.0                 *
 *************************************
 *  _____ _            ___       _   _           
 * |_   _(_)          / _ \     | | (_)          
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____ 
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *                                              
 * +
 * + Languages: EN, FR
 * + PS version: 1.5,1.6
 *}
<div class="row" id="ta-footer-module">
<h3>{l s='Read before use' mod='giftcard'}</h3>
<div class="col-lg-12">
	<div class="col-xs-5 col-lg-2 col-sm-2 block-logo">
		<img src="../modules/giftcard/logo.png"/>
	</div>
	<div class="col-xs-7 col-lg-10 col-sm-10">
		<a href="../modules/giftcard/doc/en/index.html" class="btn btn-default" target="_blank"><img src="../modules/giftcard/views/img/doc_en.png"/></a>
		<a href="../modules/giftcard/doc/es/index.html" class="btn btn-default" target="_blank"><img src="../modules/giftcard/views/img/doc_es.png"/></a>
		<a href="../modules/giftcard/doc/fr/index.html" class="btn btn-default" target="_blank"><img src="../modules/giftcard/views/img/doc_fr.png"/></a>
	</div>
</div>
</div>
<script type="text/javascript">
$('.ta-help-fancy').click(function() {
		var url = $(this).data('fancybox-href');
		$.fancybox({
	        autoSize: true,
	        autoDimensions: true,
	        href: url,
	        beforeShow: function(){
	        	  /*$(".fancybox-skin").css("backgroundColor","#700227");*/
	        },
	        type: 'ajax'
	    });
	});
</script>