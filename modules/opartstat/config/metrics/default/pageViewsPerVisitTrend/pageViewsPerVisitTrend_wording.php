<?php
/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$metricType['pageViewsPerVisitTrend'] = "classic";
$metricTitle = $this->module->l('Page views per visit trend','adminopartstatglobal');
$helpText['pageViewsPerVisitTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/pageViewsPerVisitTrend/pageViewsPerVisitTrend_help.tpl');
$idCat['pageViewsPerVisitTrend'] = 4;




