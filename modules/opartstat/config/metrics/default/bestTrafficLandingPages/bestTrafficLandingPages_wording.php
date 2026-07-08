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

$metricType['bestTrafficLandingPages'] = "classic";
$metricTitle = $this->module->l('List of landing pages sort by traffic','adminopartstatglobal');
$helpText['bestTrafficLandingPages'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestTrafficLandingPages/bestTrafficLandingPages_help.tpl');
$idCat['bestTrafficLandingPages'] = 4;

$listCols = array(
    array(
        'label' => $this->module->l('Pages','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Visits','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);