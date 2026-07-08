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

$metricType['googleAdsConversionsTrendFromGads'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Conversions trend (Attr. Google Ads)','adminopartstatglobal');
$helpText['googleAdsConversionsTrendFromGads'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsConversionsTrendFromGads/googleAdsConversionsTrendFromGads_help.tpl');
$idCat['googleAdsConversionsTrendFromGads'] = 7;




