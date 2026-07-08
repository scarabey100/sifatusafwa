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

$metricType['googleAdsCpmTrend'] = "googleAds";
$metricTitle = $this->module->l('[GADS] CPM trend','adminopartstatglobal');
$helpText['googleAdsCpmTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCpmTrend/googleAdsCpmTrend_help.tpl');
$idCat['googleAdsCpmTrend'] = 7;




