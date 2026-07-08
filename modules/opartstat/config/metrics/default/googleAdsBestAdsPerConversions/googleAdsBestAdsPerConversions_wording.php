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

$metricType['googleAdsBestAdsPerConversions'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Ads sort by conversions','adminopartstatglobal');
$helpText['googleAdsBestAdsPerConversions'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestAdsPerConversions/googleAdsBestAdsPerConversions_help.tpl');
$idCat['googleAdsBestAdsPerConversions'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Ads','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Conversions','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);



