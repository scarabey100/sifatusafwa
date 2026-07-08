$(document).ready(function() {
	if ($('#form_content').length > 0) {
		// PS < 8.1 - Product v1
		var ap5_newTabContent = $('#form_content .form-contenttab:eq(0)').clone().attr('id', 'pm_advancedpack').html('<div class="col-xs-12 col-12"><div class="spinner"></div></div>').removeClass('active');
		ap5_newTabContent.insertAfter('#form_content .form-contenttab:eq(0)');

		var ap5_newTabNavLink = $('#form-loading ul.js-nav-tabs li:eq(0)').clone().attr('id', 'tab_pm_advancedpack');
		$('a', ap5_newTabNavLink).removeClass('active').html(pm_advancedpack.tabName).attr('href', '#pm_advancedpack');
		ap5_newTabNavLink.insertBefore('#form-loading ul.js-nav-tabs li:last');
		// Recalculate width of nav tabs container
		var navWidth = 50;
		$('#form-loading ul.js-nav-tabs li').each((index, item) => {
			navWidth += $(item).width();
		});
		$('#form-loading ul.js-nav-tabs').width(navWidth);
        ap5_getAdminRelatedPackOutput();
	}
});

function ap5_getAdminRelatedPackOutput() {
	// Retrieve product identifier depending on PrestaShop version
	let idProduct = 0;
	if ($('#form_id_product').length) {
		idProduct = $('#form_id_product').val();
	} else {
		idProduct = $('form[name=product]').data('product-id');
	}
	$.ajax({
		type: "POST",
		dataType: "json",
		ajax: 1,
		url: pm_advancedpack.moduleConfigureURL,
		data: {
			action: 'GetAdminRelatedPackOutput',
			idProduct: parseInt(idProduct),
		},
		cache: false,
		success: function (jsonData, textStatus, jqXHR) {
			if (typeof (jsonData.tabContent) !== 'undefined') {
				$('#pm_advancedpack').html(jsonData.tabContent);
			}
		}
	});
}
