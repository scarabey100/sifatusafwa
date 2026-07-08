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

$metricType['googleAdsRoasTrend'] = "googleAds";
$metricTitle = $this->module->l('[GADS] ROAS trend','adminopartstatglobal');
$helpText['googleAdsRoasTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsRoasTrend/googleAdsRoasTrend_help.tpl');
$idCat['googleAdsRoasTrend'] = 7;




