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

$metricType['googleAdsBestCampaignsPerCosts'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Campaign sort by costs','adminopartstatglobal');
$helpText['googleAdsBestCampaignsPerCosts'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestCampaignsPerCosts/googleAdsBestCampaignsPerCosts_help.tpl');
$idCat['googleAdsBestCampaignsPerCosts'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Campaigns','adminopartstatglobal'),
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



