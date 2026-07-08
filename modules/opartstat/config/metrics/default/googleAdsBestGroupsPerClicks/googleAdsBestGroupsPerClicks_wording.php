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

$metricType['googleAdsBestGroupsPerClicks'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Groups sort by clicks','adminopartstatglobal');
$helpText['googleAdsBestGroupsPerClicks'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestGroupsPerClicks/googleAdsBestGroupsPerClicks_help.tpl');
$idCat['googleAdsBestGroupsPerClicks'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Groups','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Clicks','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);



