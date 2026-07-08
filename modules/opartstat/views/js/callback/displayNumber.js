/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
function displayNumber(result, metricName) {
  var target = metricName + "Container";
  var loader = metricName + "Loader";

  var val = displayDataToHuman(result.initial.value,result.initial["conf"]['total']);

  $("#" + target).text(val);

  if (result.initial.helpValues && Object.keys(result.initial.helpValues).length) {
    //console.log('dedans');
    var helpBoxEl = $("#" + target).parents(".panel").find(".help-box");
    var helpBoxText = helpBoxEl.attr("data-original-title");      
    //console.log(helpBoxText);
    var newHelpBoxText = replaceToolWords(helpBoxText,result.initial.helpValues);
    helpBoxEl.attr("data-original-title",newHelpBoxText);
    //console.log(helpBoxEl.attr("data-original-title"));
  }

  if (typeof result.compare !== "undefined") {
    /* var compareValue = result.compare["value"]; */

    var compareGlobalValueContainer = metricName + "CompareGlobalValue";
    var GlobalPercentVariationContainer =
      metricName + "GlobalPercentVariationContainer";

    var percentGlobal = calcPercentVariation(
      result.initial.value,
      result.compare["value"]
    );
    var compareValue = displayDataToHuman(result.compare.value,result.compare["conf"]['total']);

    $("#" + GlobalPercentVariationContainer).removeClass('osUpColor')
    $("#" + GlobalPercentVariationContainer).removeClass('osDownColor')
    $("#" + GlobalPercentVariationContainer).removeClass('osFlatColor')

    if(percentGlobal == 0) {
      $("#" + GlobalPercentVariationContainer).addClass('osFlatColor')
      var trendIco = "trending_flat"
    }
    else if(percentGlobal>0 && result.conf.superiorIsBetter == true) {
      $("#" + GlobalPercentVariationContainer).addClass('osUpColor')
      var trendIco = "trending_up"
    }
    else if(percentGlobal<0 && result.conf.superiorIsBetter == false) {
      $("#" + GlobalPercentVariationContainer).addClass('osUpColor')
      var trendIco = "trending_down"
    }
    else {
      $("#" + GlobalPercentVariationContainer).addClass('osDownColor')
      var trendIco = "trending_down"
    }

    $("#" + compareGlobalValueContainer).text(compareValue);

    //$("#" + GlobalPercentVariationContainer).text("(" + percentGlobal + "%)");
    $("#" + GlobalPercentVariationContainer).html(" (<i class='material-icons "+trendIco+"'><span>"+trendIco+"</span></i>"+percentGlobal+"%)");
  }
  $("#" + loader).fadeOut("fast", function () {
    $("#" + target).fadeIn("slow");
    if (compareValue != null) {
      $("#" + compareGlobalValueContainer).fadeIn("slow");
      $("#" + GlobalPercentVariationContainer).fadeIn("slow");
    }
  });
}

function replaceToolWords(texte, tableau) {
  for (let motOutil in tableau) {
      let regex = new RegExp('#' + motOutil + '#', 'g');
      texte = texte.replace(regex, tableau[motOutil]);
  }
  return texte;
}