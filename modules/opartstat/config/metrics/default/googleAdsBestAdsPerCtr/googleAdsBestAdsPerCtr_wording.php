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

$metricType['googleAdsBestAdsPerCtr'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Ads sort by CTR','adminopartstatglobal');
$helpText['googleAdsBestAdsPerCtr'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestAdsPerCtr/googleAdsBestAdsPerCtr_help.tpl');
$idCat['googleAdsBestAdsPerCtr'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Ads','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CTR','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



