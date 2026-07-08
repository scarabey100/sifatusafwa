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

$metricType['bestTrafficSources'] = "classic";
$metricTitle = $this->module->l('List of traffic sources sort by traffic','adminopartstatglobal');
$helpText['bestTrafficSources'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestTrafficSources/bestTrafficSources_help.tpl');
$idCat['bestTrafficSources'] = 4;

$listCols = array(
    array(
        'label' => $this->module->l('Sources','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Traffic','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);