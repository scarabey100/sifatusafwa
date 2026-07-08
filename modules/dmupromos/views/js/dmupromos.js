/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this Module to 
* newer versions in the future. If you wish to customize the Module for 
* your needs please use "themes/" or/and "override/modules" directories 
* or refer to http://www.prestashop.com for more information.
*
*  .--.
*  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*       w w w . d r e a m - m e - u p . f r       '
*
* @author    Dream me up <prestashop@dream-me-up.fr>
* @copyright 2007 - 2024 Dream me up
* @license   All Rights Reserved
*/

function dmu_boLoading()
{
    $('body').append('<div id="dmu_loading" style="display:none;z-index:666999;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5) url(/modules/dmupromos/views/img/ajax-loader-big.gif) center center no-repeat;cursor:wait;"></div>');
    $('#dmu_loading').fadeIn('fast');
}
function dmu_boUnloading()
{
    $('#dmu_loading').remove();
}

function dmup_getCombinations(id_product)
{
    dmu_boLoading();
	$.ajax({
		type: 'POST',
		url: document.location,
		async: true,
		cache: false,
		dataType : 'json',
		data: 'ajax=1&action=getCombinations&id_product='+id_product,
		success: function(json)
		{
            if (json.success) {
                combinations = $('table.table tbody', json.html).html();
                $('#dmup_reloadable table.table .tr_'+json.id_product+'_0').after(combinations);
            }
            dmu_boUnloading();
        }
    });
}

function dmup_search(page, limit)
{
    // console.log('test');
    dmu_boLoading();
    filters = {
        'show_active' : $('#show_active').prop('checked') ? 1 : 0,
        'show_campaign' : $('#show_campaign').prop('checked') ? 1 : 0,
        'filter_newage' : parseInt($('#filter_newage').val()),
        'filter_lastrestock' : parseInt($('#filter_lastrestock').val()),
        'filter_id_category' : $('#filter_id_category').val(),
        'filter_id_manufacturer' : $('#filter_id_manufacturer').val(),
        'filter_keywords' : $('#filter_keywords').val(),
        'filter_orderway' : $('#filter_orderway').val(),
        'filter_orderby' : $('#filter_orderby').val(),
        'filter_stock' : $('#filter_stock').val(),
    }
    extended_data = '';
    if (typeof limit !== 'undefined') {
        extended_data += '&productslist_pagination='+limit+'&pagination='+limit;
    }
    if (typeof page !== 'undefined') {
        extended_data += '&submitFilterproductslist='+page+'&submitFilter='+page;
    }

	$.ajax({
		type: 'POST',
		url: document.location,
		async: true,
		cache: false,
		dataType : 'json',
		data: 'ajax=1&action=getProducts&filters='+JSON.stringify(filters)+extended_data,
		success: function(json)
		{
            if (json.success) {
                $('#dmup_reloadable').html(json.html);
                dmup_updatePaginationEngine();
                if ($('.bootstrap').length) {
                    $('.label-tooltip').tooltip();
                }
            }
            dmu_boUnloading();
        }
    });
}
function dmup_setOrderFilter(orderby, orderway, paginationPage, perPage)
{
    setUrlParam('filter_orderway', orderway);
    setUrlParam('filter_orderby', orderby);

    setUrlParam('paginationPage', parseInt($('.pagination.pull-right li.active a').attr('data-page')));
    setUrlParam('perPage', parseInt($('.pagination button').text()));

    $('.pagination.pull-right li a').attr('data-page')
    $('#filter_orderby').val(orderby);
    $('#filter_orderway').val(orderway);
    dmup_search();
}

var dmup_rebound_cache = '';
function dmup_setDiscount(id_product, id_product_attribute, val)
{
    rebound_this = id_product+'_'+id_product_attribute+'_'+val;
    if (dmup_rebound_cache == rebound_this) { return; }
    dmup_rebound_cache = rebound_this;

    $('.tr_'+id_product+'_'+id_product_attribute+' td.td_discount').html('<img src="../img/admin/ajax-loader.gif" border=0 />');
    $.ajax({
        type: 'POST',
        url: document.location,
        async: true,
        cache: false,
        dataType : 'json',
        data: 'ajax=1&action=setDiscount&id_product='+id_product+'&id_product_attribute='+id_product_attribute+'&val='+encodeURI(val),
        success: function(json)
        {
            if (json.success) {
                sel_tr_parent = '.tr_'+json.id_product+'_0';
                sel_tr = '.tr_'+json.id_product+'_'+json.id_product_attribute;
                if (json.on_sale == 2) {
                    $(sel_tr_parent + '.product .td_on_sale').html('<span></span>');                        
                } else if (json.on_sale == 1) {
                    $(sel_tr_parent + '.product .td_on_sale').html('<a class="list-action-enable action-enabled" href="javascript:;" title="disabled"><i class="icon-check"></i></a>');                        
                } else {
                    $(sel_tr_parent + '.product .td_on_sale').html('<a class="list-action-enable action-disabled" href="javascript:;" title="disabled"><i class="icon-remove"></i></a>');                        
                }
                if (json.val) {
                    $(sel_tr + ' .td_discount').html('<span rel="'+json.val+'"><b>'+json.val+'</b><div class="delete">X</div></span>');
                } else {
                    if (json.val_temp) {
                        $(sel_tr + ' .td_discount').html('<span rel="0"><b style="opacity:.3;">'+json.val_temp+'</b></span>');                        
                    } else {
                        $(sel_tr + ' .td_discount').html('<span rel="0"><b>&nbsp;</b></span>');                        
                    }
                }
                if (json.final_price_ht != json.price_ht) {
                    $(sel_tr + ' .td_final_price').html(json.final_price_ht+' &nbsp;-&nbsp; <b>'+json.final_price_ttc+'</b>');
                } else {
                    $(sel_tr + ' .td_final_price').html('<span style="opacity:.3;">'+json.final_price_ht+' &nbsp;-&nbsp; <b>'+json.final_price_ttc+'</b></span>');
                }
                if (json.margin) {
                    if (json.margin < 50) {
                        if (json.margin < 25) {
                            $(sel_tr + ' .td_margin').html('<span class="badge badge-danger"> '+json.margin+' % </span>');
                        } else {
                            $(sel_tr + ' .td_margin').html('<span class="badge badge-warning"> '+json.margin+' % </span>');
                        }
                    } else {
                        $(sel_tr + ' .td_margin').html('<span class="badge badge-success"> '+json.margin+' % </span>');
                    }
                } else {
                    /*
                    if (json.price_ht) {
                        $(sel_tr + ' .td_margin').html('<span class="badge badge-warning"> '+json.price_ht+' </span>');
                    } else {
                    */
                        $(sel_tr + ' .td_margin').html('');
                    /* } */
                }
                if (json.id_product_attribute === 0) {
                    $('.tr_'+json.id_product).each(function() {
                        val = $(this).find('.td_discount span').attr('rel');
                        if (val == 0 || val =='0') {
                            id_product_attribute = $(this).attr('class').trim().split(' ')[0].split('_')[2];
                            dmup_setDiscount(id_product, id_product_attribute, '');
                        }
                    });
                }
            }
        }
    });
}

var dmup_rebound_cache2 = '';
function dmup_setOnSale(id_product, onsale)
{
    rebound_this = id_product+'_'+onsale;
    if (dmup_rebound_cache2 == rebound_this) { return; }
    dmup_rebound_cache2 = rebound_this;

    $('.tr_'+id_product+'_0 td.td_on_sale').html('<img src="../img/admin/ajax-loader.gif" border=0 />');
    $.ajax({
        type: 'POST',
        url: document.location,
        async: true,
        cache: false,
        dataType : 'json',
        data: 'ajax=1&action=setOnSale&id_product='+id_product+'&onsale='+onsale,
        success: function(json)
        {
            if (json.success) {
                if (json.onsale) {
                    $('.tr_'+json.id_product+'_0 .td_on_sale').html('<a class="list-action-enable action-enabled" href="javascript:;" title="enabled"><i class="icon-check"></i></a>');
                } else {
                    $('.tr_'+json.id_product+'_0 .td_on_sale').html('<a class="list-action-enable action-disabled" href="javascript:;" title="disabled"><i class="icon-remove"></i></a>');
                }
            }
        }
    });            
}

function dmup_ajaxBulkActionOnSale()
{
    console.log('dmup_ajaxBulkActionOnSale()');
    if ($('.td_discount img').length) {
        setTimeout(dmup_ajaxBulkActionOnSale, 100);
    } else {
        $('#dmup_reloadable table.table input[type=checkbox]').each(function() {
            if ($(this).prop('checked')) {
                id_product = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
                onsale = dmup_onsale_checkbox ? 1 : 0;
                dmup_setOnSale(id_product, onsale);
                $(this).removeAttr('checked');
            }
        });
        dmu_boUnloading();
    }
}

var dmup_discount_input;
var dmup_discount_select;
var dmup_onsale_checkbox;
function dmup_ajaxBulkActionDiscount(is_confirm)
{
    dmup_discount_input = $('#dmup_bulkpanel #discount_input').val();
    dmup_discount_select = $('#dmup_bulkpanel #discount_select').val();
    dmup_onsale_checkbox = false;
    if ($('#dmup_bulkpanel #onsale_checkbox').prop('checked'))
        dmup_onsale_checkbox = true;
    $('#dmup_bulkpanel').remove();
    
    if (is_confirm) {
        val = dmup_discount_input + dmup_discount_select;
        $('#dmup_reloadable table.table input[type=checkbox]').each(function() {
            if ($(this).prop('checked')) {
                id_product = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
                id_product_attribute = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[2];
                dmup_setDiscount(id_product, id_product_attribute, val);
                //if (!dmup_onsale_checkbox) {
                //  $(this).removeAttr('checked');
                //}
            }
        });
        //if (dmup_onsale_checkbox) {
            dmup_ajaxBulkActionOnSale();
        //} else {
        //  dmu_boUnloading();
        //}
    } else {
        dmu_boUnloading();
    }
}

function dmup_ajaxBulkAction(form, action)
{
    if (typeof dmup_txt_discount == 'undefined')
        dmup_txt_discount = 'Discount';
    if (typeof dmup_txt_currency == 'undefined')
        dmup_txt_currency = '&euro;';
    if (typeof dmup_txt_onsale == 'undefined')
        dmup_txt_onsale = 'Show « On sale ! »';
    if (typeof dmup_txt_cancel == 'undefined')
        dmup_txt_cancel = 'Cancel';
    if (typeof dmup_txt_save == 'undefined')
        dmup_txt_save = 'Save';
    // Renseigner les promotions sélectionnées
    if (action == 'submitBulksetDiscountsconfiguration' || action == 'submitBulksetDiscountsproductslist') {
        dmu_boLoading();
        html = '<div class="panel">';
            html += '<form action="" method="post" class="bulk_update_form">';
                html += '<div class="panel-heading">'+dmup_txt_discount+'</div>';
                html += '<div class="row">';
                    html += '<div class="cols col-xs-12 col-sm-8">';
                        html += '<input type="text" id="discount_input" name="discount_input" />';
                    html += '</div>';
                    html += '<div class="cols col-xs-12 col-sm-4">';
                        html += '<select id="discount_select" name="discount_select">';
                            html += '<option value="%">%</option>';
                            html += '<option value="'+dmup_txt_currency+'">'+dmup_txt_currency+'</option>';
                        html += '</select>';
                    html += '</div>';
                    html += '<div class="cols col-xs-12">';
                        html += '<input type="checkbox" id="onsale_checkbox" name="onsale_checkbox" checked="checked" /> <label for="onsale_checkbox">'+dmup_txt_onsale+'</label>';
                    html += '</div>';
                html += '</div>';
                html += '<div class="panel-footer">';
                    html += '<a href="javascript:;" onClick="dmup_ajaxBulkActionDiscount(false)" class="btn btn-default"><i class="process-icon-cancel"></i> '+dmup_txt_cancel+'</a>';
                    html += '<button type="submit" name="submit_bulk_update_form" class="btn btn-default pull-right"><i class="process-icon-save"></i> '+dmup_txt_save+'</button>';
                html += '</div>';
            html += '</form>';
        html += '</div>';
        w = 300; h = 150;
        l = ($(window).width() - w) / 2;
        t = ($(window).height() - h) / 2;
        $('body #content').append('<div id="dmup_bulkpanel" class="bootstrap ps15_bootstrap"></div>');
        $('#dmup_bulkpanel').css({
            'position' : 'fixed',
            'left' : l+'px',
            'top' : t+'px',
            'width' : '100%',
            'height' : '100%',
            'max-width' : w+'px',
            'max-height' : h+'px',
            'z-index' : '777888'
        }).append(html).find('#discount_input').focus();

        $('.panel .bulk_update_form').on('submit', function() {
            // dmu_boLoading();
            $('#dmup_bulkpanel').hide();
            console.log('test');
            var products = [];
            $.each($("input[name='productslistBox[]']:checked"), function(){
                products.push($(this).val());
            });
            $(this).append('<input type="hidden" name="products" value="'+products+'"/>');

            let orderBWay = $('#table-productslist thead a.active').attr('class').split(/\s+/)[0].split('-sort-column')[0];
            $('<input>').attr({ type: 'hidden', name: 'filter_orderway', value: orderBWay }).appendTo($(this));

            let orderBy = $('#table-productslist thead a.active').attr('class').split(/\s+/)[0].split('column-')[1].split('-link')[0];
            $('<input>').attr({ type: 'hidden', name: 'filter_orderby', value: orderBy }).appendTo($(this));

            let paginationPage = parseInt($('.pagination.pull-right li.active a').attr('data-page'));
            $('<input>').attr({ type: 'hidden', name: 'submitFilterproductslist', value: paginationPage }).appendTo($(this));
            $('<input>').attr({ type: 'hidden', name: 'submitFilter', value: paginationPage }).appendTo($(this));

            let perPage = parseInt($('.pagination button').text());
            $('<input>').attr({ type: 'hidden', name: 'productslist_pagination', value: perPage }).appendTo($(this));
            $('<input>').attr({ type: 'hidden', name: 'pagination', value: perPage }).appendTo($(this));

            setTimeout(function() {
                console.log("This runs after 0.5 seconds");
            }, 500);

            return true;
        });

        //dmu_boUnloading();
    }
    // Supprimer les promotions sélectionnées
    if (action == 'submitBulkdeleteDiscountsconfiguration' || action == 'submitBulkdeleteDiscountsproductslist') {
        dmu_boLoading();
        $('#dmup_reloadable table.table input[type=checkbox]').each(function() {
            if ($(this).prop('checked')) {
                id_product = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
                id_product_attribute = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[2];
                dmup_setDiscount(id_product, id_product_attribute, '');
                $(this).removeAttr('checked');
            }
        });
        dmu_boUnloading();
    }
}

function dmup_updatePaginationEngine()
{
    $('#dmup_reloadable form').live('submit', function() {
        return false;
    });
    // *** override ps 1.6 *** ----------------------
    // pagination ---
    $('#dmup_reloadable .pagination-items-page').unbind().on('click',function(e){
        e.preventDefault();
        dmup_search(1, $(this).data("items"));
    });
    $('#dmup_reloadable .pagination-link').unbind().on('click',function(e){
        e.preventDefault();
        if (!$(this).parent().hasClass('disabled')) {
            dmup_search($(this).data("page"));
        }
    });
    // actions groupées ---
    $('#dmup_reloadable .bulk-actions ul.dropdown-menu li a').each(function() {
        action = $(this).attr('onclick').replace('sendBulkAction', 'dmup_ajaxBulkAction');
        $(this).attr('onclick', action).attr('href', 'javascript:;');
    });
    // boutons de tris ---
    $('#dmup_reloadable table.table thead a').each(function() {
        href = $(this).attr('href');
        orderby = href.split('rderby=')[1].split('&')[0].toLowerCase();
        orderway = href.split('rderway=')[1].split('&')[0].toUpperCase();
        $(this).attr('href', "javascript:dmup_setOrderFilter('"+orderby+"', '"+orderway+"');");
    });
    // *** override ps 1.5 *** ------------------------
    // pagination
    $('#dmup_reloadable select[name=pagination]').attr('onchange', '').on('change', function(e){
        e.preventDefault();
        dmup_search(1, $(this).val());
    });
    $('#dmup_reloadable input[type=image]').unbind().on('click',function(e){
        e.preventDefault();
        dmup_search($('#submitFilter').val());
    });
    // actions groupées
    $('#dmup_reloadable input[name=submitBulksetDiscounts]').unbind().on('click',function(e){
        e.preventDefault();
        dmup_ajaxBulkAction(0, 'submitBulksetDiscountsconfiguration');
    });
    $('#dmup_reloadable input[name=submitBulkdeleteDiscounts]').unbind().on('click',function(e){
        e.preventDefault();
        dmup_ajaxBulkAction(0, 'submitBulkdeleteDiscountsconfiguration');
    });
}

function dmup_MarginMessage()
{
    setTimeout(dmup_MarginMessageTempo, 1666);
}
function dmup_MarginMessageTempo()
{
    dmu_boLoading();
    $('body').append('<div id="dmu_margincalculation" style="z-index:666999;position:fixed;left:0;top:0;right:0;background:#f0f0f0;border-bottom:2px solid #ccc;box-shadow:0 0 5px #000;cursor:wait;padding:10px 15px;"><img src="../img/loadingAnimation.gif" border="0" align="right" style="margin:2px;" /><b style="color:#900;text-transform:uppercase;letter-spacing:.5px;"><i class="icon icon-tag"></i> '+dmup_txt_margincalculation+'</b></div>');
}

function setUrlParam(key, value) {
    const url = new URL(window.location);
    url.searchParams.set(key, value);
    window.history.pushState({}, '', url);
}

var default_discount = '20%';
var dmup_filter_show = false;

$(document).ready(function() {

    // *** Modification visuelle du titre ***
    $('h2.page-title, .pageTitle h3').html(function() {
        return $(this).html().replace(/\(/g,'<span style="font-size:15px;">').replace(/\)/g,'</span>');
    });
    
    // *** Initialisation des filtres ***
    $('.dmup_filters').change(dmup_search);
    $('#filter_keywords, #filter_newage, #filter_lastrestock').keyup(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            $(this).blur();
        }
    });
    $('#filter_newage, #filter_lastrestock, #filter_stock').focus(function() {
        $(this).select();
    });
    $('#filter_toggle').click(function() {
        if (dmup_filter_show) {
            dmup_filter_show = false;
            $('.dmup_advanced_filters').hide();
            $('#filter_newage').val('');
            $('#filter_lastrestock').val('');
            $('#filter_toggle i').attr('class', 'icon-chevron-down');
        } else {
            dmup_filter_show = true;
            $('.dmup_advanced_filters').show();
            $('#filter_toggle i').attr('class', 'icon-chevron-up');
        }
    });
    $('#filter_submit').click(dmup_search);
    $('#filter_reset').click(function() {
        $('#filter_keywords').val('');
        $('#filter_id_category, #filter_id_manufacturer').val(0);
        if (dmup_filter_show) {
            $('#filter_newage, #filter_lastrestock').val('');
        }
        dmup_search();
    });
    $('.help-block').fadeOut(1).fadeIn(222).fadeOut(111).fadeIn(666).fadeOut(222).fadeIn(888);

    // *** Désactivation de l'envoi auto du formulaire ***
    $('#dmup_reloadable form').live('submit', function() {
        return false;
    });

    // *** Initialisation des raccourcis de "Check"
    $('#dmup_reloadable tbody .checkable').live('click', function() {
        $(this).parent().parent().find('input[type=checkbox]').trigger('click');
    });
    
    // *** Initialisation du "bouton" Swap « déclinaisons » ***
    $('#dmup_reloadable tbody .attributes_swap_button').live('click', function() {
        if ($(this).hasClass('attributes_swap_open')) {
            $(this).parent().find('.attributes-swap')
                .removeClass('icon-minus-square')
                .addClass('icon-plus-square');
            $(this).removeClass('attributes_swap_open');
            id_product = $(this).parent().attr('class').trim().split(' ')[0].split('_')[1];
            if (id_product) {
                $('#dmup_reloadable table.table tr.tr_'+id_product).removeClass('open');
            } else console.log('Product id not found ! '.id_product);
        } else {
            $(this).parent().find('.attributes-swap')
                .removeClass('icon-plus-square')
                .addClass('icon-minus-square');
            $(this).addClass('attributes_swap_open');
            id_product = $(this).parent().attr('class').trim().split(' ')[0].split('_')[1];
            if (id_product) {
                if ($('#dmup_reloadable table.table tr.tr_'+id_product+'_0 .attributes-swap').length
                    && !$('#dmup_reloadable table.table tr.tr_'+id_product).length) {
                    dmup_getCombinations(id_product);
                }
                $('#dmup_reloadable table.table tr.tr_'+id_product).addClass('open');
            } else console.log('Product id not found ! '.id_product);
        }
    });

    // *** Cases « En solde ! » ***
    $('#dmup_reloadable table.table td.td_on_sale a').live('click', function() {
        id_product = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
        onsale = $(this).hasClass('action-enabled') ? 0 : 1;
        dmup_setOnSale(id_product, onsale);
    });

    // *** Cases Promotion ***
    $('#dmup_reloadable table.table td.td_discount span').live('click', function() {
        val = parseInt($(this).attr('rel')) ? $(this).attr('rel') : default_discount;
        $(this).parent().html('<input type="text" size="4" value="'+val+'" class="span" />').find('input').select();
    });
    $('#dmup_reloadable table.table td.td_discount span .delete').live('click', function() {
        id_product = $(this).parent().parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
        id_product_attribute = $(this).parent().parent().parent().attr('class').trim().split(' ')[0].split('_')[2];
        dmup_setDiscount(id_product, id_product_attribute, '');
    });
    $('#dmup_reloadable table.table td.td_discount input').live('blur', function() {
        id_product = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[1];
        id_product_attribute = $(this).parent().parent().attr('class').trim().split(' ')[0].split('_')[2];
        val = $(this).val() ? $(this).val() : '';
        if (val) {
            default_discount = val;
        }
        dmup_setDiscount(id_product, id_product_attribute, val);
        }).live('keyup', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) { $(this).blur(); }
	});
    
    // *** Pagination ***
    dmup_updatePaginationEngine();
    
    // *** Supression de la confirmation de Duplication des images ***
    $('table.promos_campagnes a').each(function() {
        if (click = $(this).attr('onclick')) {
            if (click.indexOf('duplicatepromos_campagnes') != -1) {
                match_id = click.match(/id_promos_campagnes=(\d+)/);
                match_token = click.match(/token=([a-z0-9A-Z]+)/);
                if (match_id.length && match_token.length) {
                    url = 'index.php?controller=AdminDmuPromos&id_promos_campagnes='+match_id[1];
                    url += '&duplicatepromos_campagnes&token='+match_token[1];
                    new_click = "document.location = '"+url+"';";
                    $(this).attr('onclick', new_click)
                }
            }
        }
    });
    
    // *** Ajout du message de Calcul des marges
    $('table.promos_campagnes tbody td.pointer').each(function() {
        _onclick = $(this).attr('onclick');
        $(this).attr('onclick', 'dmup_MarginMessage();'+_onclick);
    });
    $('table.promos_campagnes tbody a').each(function() {
        _href = $(this).attr('href');
        if (_href.indexOf('viewpromos_campagnes')!=-1) {
            $(this).attr('onclick', 'dmup_MarginMessage();');
        }
    });

    // *** Bind de setDiscount et deleteDiscount pour 1.5
    $("input[name='submitBulksetDiscountsproductslist']").on('click', function() {
        dmup_ajaxBulkAction($('#dmup_reloadable form'), 'submitBulksetDiscountsconfiguration');
    });
    $("input[name='submitBulkdeleteDiscountsproductslist']").on('click', function() {
        dmup_ajaxBulkAction($('#dmup_reloadable form'), 'submitBulkdeleteDiscountsconfiguration');
    });

    // $('.pagination ul.dropdown-menu li a').attr('data-items');


    // setUrlParam('paginationPage', parseInt($('.pagination.pull-right li.active a').attr('data-page')));

    // setTimeout(() => {
    //     console.log("This runs after 2 seconds");
    // }, 2000);
});

// $('.pagination ul.dropdown-menu li a').on('click', function() {
//     let items = parseInt($(this).attr('data-items'));
//     setUrlParam('perPage', items);
// });
//
// $('.pagination.pull-right li a').on('click', function() {
//     let items = parseInt($(this).attr('data-page'));
//     console.log(items);
//     setUrlParam('paginationPage', items);
// });

$(document).on('click', '.pagination ul.dropdown-menu li a', function() {
    const items = parseInt($(this).attr('data-items'));
    setUrlParam('perPage', items);
});

// For pagination page links
$(document).on('click', '.pagination.pull-right li a', function() {
    const items = parseInt($(this).attr('data-page'));
    console.log(items);
    setUrlParam('paginationPage', items);
});
