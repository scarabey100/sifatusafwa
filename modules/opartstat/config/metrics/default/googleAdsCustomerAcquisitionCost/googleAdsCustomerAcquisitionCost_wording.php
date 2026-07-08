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

$metricType['googleAdsCustomerAcquisitionCost'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Customer acquisition cost (CAC)','adminopartstatglobal');
$helpText['googleAdsCustomerAcquisitionCost'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCustomerAcquisitionCost/googleAdsCustomerAcquisitionCost_help.tpl');
$idCat['googleAdsCustomerAcquisitionCost'] = 7;



