/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL DREAM ME UP
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL DREAM ME UP is strictly forbidden.
*
*   .--.
*   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*        w w w . d r e a m - m e - u p . f r       '
*
*  @author    Dream me up <prestashop@dream-me-up.fr>
*  @copyright 2007 - 2025 Dream me up
*  @license   All Rights Reserved
*/

var url_global = currentIndex+'&token='+token;
var interval = 0;

function filtre_produits()
{
	clearInterval(interval);
	var id_manufacturer = $('#id_manufacturer').val();
	var id_categorie = $('#id_categorie').val();
	var recherche = $('#recherche').val();
	var id_categorie_assoc = $('#id_categorie_assoc').val();
	setLoading("#resultats_produits");

	$.ajax({
		type: 'POST',
		url: url_global + "&ajax=ajaxProduits",
		async: true,
		cache: false,
		dataType : "html",
		data: 'id_manufacturer='+id_manufacturer+'&id_categorie='+id_categorie+'&id_categorie_assoc='+id_categorie_assoc+'&recherche='+escape(recherche),
		success: function(html)
		{
			$("#resultats_produits").html(html);
		}
	});
}

// Petit timer pour la recherche
function saisie_recherche()
{
	clearInterval(interval);
	interval = setInterval("filtre_produits()", 500);
}

function change_categorie(id_categorie_assoc)
{
	setLoading("#resultats_associations");
	$.ajax({
		type: 'POST',
		url: url_global + "&ajax=ajaxProduitsAssociation",
		async: true,
		cache: false,
		dataType : "html",
		data: 'id_categorie_assoc='+id_categorie_assoc,
		success: function(html)
		{
			$("#resultats_associations").html(html);
			filtre_produits();

            $("#products_assoc").tableDnD({
                onDrop: function( table, row ) {
                    ids = new Array();
                    $("#products_assoc tr").each(function(){
                        ids.push( jQuery( this ).attr('id'));
                    });
                    $.ajax( {
                        type: 'POST',
                        url: url_global + "&ajax=saveOrder",
                        async: false,
                        data: 'tableorder=' + ids.join('-') + '&id_category=' + jQuery('#id_categorie_assoc').val(),
                        success : function (html) {
                            console.log( html );
                        }
                    })
                }
            });
		}
	});


}

function click_associer()
{
	var prods = new Array();
	var some_checked = false;
	$("input[name=chk_produits]:checked").each(
		function() {
			prods.push($(this).val());
			some_checked = true;
		}
	);

	var chaine_prods = prods.join(",");

	var def = 0;
	if ($("#chk_default_yes").is(":checked"))
	{
		def = 1;
	}

	if (some_checked == true)
	{
		$.ajax({
			type: 'POST',
			url: url_global + "&ajax=ajaxDoAssociation",
			async: true,
			cache: false,
			dataType : "html",
			data: 'id_categorie_assoc='+$("#id_categorie_assoc").val()+'&prods='+chaine_prods+'&def='+def,
			success: function(html)
			{
				change_categorie($("#id_categorie_assoc").val());
			}
		});
	}
}

function click_desassocier()
{
	var prods = new Array();
	var some_checked = false;
	$("input[name=chk_assoc]:checked").each(
		function() {
			prods.push($(this).val());
			some_checked = true;
		}
	);

	var chaine_prods = prods.join(",");

	if (some_checked == true)
	{
		$.ajax({
			type: 'POST',
			url: url_global + "&ajax=ajaxRemoveAssociation",
			async: true,
			cache: false,
			dataType : "html",
			data: 'id_categorie_assoc='+$("#id_categorie_assoc").val()+'&prods='+chaine_prods,
			success: function(html)
			{
				change_categorie($("#id_categorie_assoc").val());
			}
		});
	}
}

function click_makedefault()
{
	var prods = new Array();
	var some_checked = false;
	$("input[name=chk_assoc]:checked").each(
		function() {
			prods.push($(this).val());
			some_checked = true;
		}
	);

	var chaine_prods = prods.join(",");

	if (some_checked == true)
	{
		$.ajax({
			type: 'POST',
			url: url_global + "&ajax=ajaxMakeDefault",
			async: true,
			cache: false,
			dataType : "html",
			data: 'id_categorie_assoc='+$("#id_categorie_assoc").val()+'&prods='+chaine_prods,
			success: function(html)
			{
				change_categorie($("#id_categorie_assoc").val());
			}
		});
	}
}

function chk_all(obj_chk, jname)
{
	jQuery("input[name="+jname+"]").attr("checked", jQuery(obj_chk).is(":checked"));
}

/* AJOUT PAR lucien le 21/11/2011 */
function click_deplacer()
{
	var prods = new Array();
	var some_checked = false;
	$("input[name=chk_produits]:checked").each(
		function() {
			prods.push($(this).val());
			some_checked = true;
		}
	);

	var chaine_prods = prods.join(",");

	var def = 0;
	if ($("#chk_default_yes").is(":checked"))
	{
		def = 1;
	}

	if (some_checked == true)
	{
		$.ajax({
			type: 'POST',
			url: url_global + "&ajax=ajaxDoDeplacement",
			async: true,
			cache: false,
			dataType : "html",
			data: 'id_category_to='+$("#id_categorie_assoc").val()+'&id_category_from=' + jQuery("#id_categorie").val() + '&prods='+chaine_prods+'&def='+def,
			success: function(html)
			{
				change_categorie($("#id_categorie_assoc").val());
			}
		});
	}
}
function setLoading(id_element)
{
	$(id_element).html("<img src=\"../modules/dmuassocprodcat/views/img/ajax-loader.gif\" style=\"vertical-align:middle\" />");
}

$(document).ready(function()
{
	filtre_produits();
	$(document).on("mouseover", ".productName", function(e) { $('#popup_produits_'+$(this).attr('ref')).show(); });
	$(document).on("mouseleave", ".productName", function(e) { $('#popup_produits_'+$(this).attr('ref')).hide(); });
	$(document).on("mouseover", ".assocName", function(e) { $('#popup_assoc_'+$(this).attr('ref')).show(); });
	$(document).on("mouseleave", ".assocName", function(e) { $('#popup_assoc_'+$(this).attr('ref')).hide(); });
});