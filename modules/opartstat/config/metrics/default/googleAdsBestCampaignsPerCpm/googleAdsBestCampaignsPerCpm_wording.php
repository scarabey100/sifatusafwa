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

$metricType['googleAdsBestCampaignsPerCpm'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Campaign sort by CPM','adminopartstatglobal');
$helpText['googleAdsBestCampaignsPerCpm'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestCampaignsPerCpm/googleAdsBestCampaignsPerCpm_help.tpl');
$idCat['googleAdsBestCampaignsPerCpm'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Campaigns','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CPM','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



