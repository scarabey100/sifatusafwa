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

$metricType['googleAdsBestCampaignsPerCostPerConversionFromGads'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Campaign sort by cost per conversion (Attr. Google Ads)','adminopartstatglobal');
$helpText['googleAdsBestCampaignsPerCostPerConversionFromGads'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestCampaignsPerCostPerConversionFromGads/googleAdsBestCampaignsPerCostPerConversionFromGads_help.tpl');
$idCat['googleAdsBestCampaignsPerCostPerConversionFromGads'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Campaigns','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CPC','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



