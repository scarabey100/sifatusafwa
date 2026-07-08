/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
function debug(result, metricName) {
  var target = metricName + "Container";
  var loader = metricName + "Loader";
    console.log(result);

  $("#" + target).html(result.initial.value);

  $("#" + loader).fadeOut("fast", function () {
    $("#" + target).fadeIn("slow");
  });
}