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

$metricType['googleAdsBestCampaignsPerImpressions'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Campaign sort by impressions','adminopartstatglobal');
$helpText['googleAdsBestCampaignsPerImpressions'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestCampaignsPerImpressions/googleAdsBestCampaignsPerImpressions_help.tpl');
$idCat['googleAdsBestCampaignsPerImpressions'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Campaigns','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Impressions','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);



