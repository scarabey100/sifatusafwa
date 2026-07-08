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

$metricType['googleAdsBestAdsPerCosts'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Ads sort by costs','adminopartstatglobal');
$helpText['googleAdsBestAdsPerCosts'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestAdsPerCosts/googleAdsBestAdsPerCosts_help.tpl');
$idCat['googleAdsBestAdsPerCosts'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Ads','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Costs','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);



