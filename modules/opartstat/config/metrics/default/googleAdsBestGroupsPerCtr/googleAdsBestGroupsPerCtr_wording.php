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

$metricType['googleAdsBestGroupsPerCtr'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Groups sort by CTR','adminopartstatglobal');
$helpText['googleAdsBestGroupsPerCtr'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestGroupsPerCtr/googleAdsBestGroupsPerCtr_help.tpl');
$idCat['googleAdsBestGroupsPerCtr'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Groups','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('CTR','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



