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

$metricType['googleAdsBestAdsPerCostPerConversion'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Ads sort by cost per conversion','adminopartstatglobal');
$helpText['googleAdsBestAdsPerCostPerConversion'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestAdsPerCostPerConversion/googleAdsBestAdsPerCostPerConversion_help.tpl');
$idCat['googleAdsBestAdsPerCostPerConversion'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Ads','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CPC','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



