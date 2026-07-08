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

$metricType['googleAdsConversionsTrend'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Conversions trend','adminopartstatglobal');
$helpText['googleAdsConversionsTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsConversionsTrend/googleAdsConversionsTrend_help.tpl');
$idCat['googleAdsConversionsTrend'] = 7;




