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

$metricType['googleAdsBestGroupsPerCpc'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Groups sort by CPC','adminopartstatglobal');
$helpText['googleAdsBestGroupsPerCpc'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestGroupsPerCpc/googleAdsBestGroupsPerCpc_help.tpl');
$idCat['googleAdsBestGroupsPerCpc'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Groups','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CPC','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



