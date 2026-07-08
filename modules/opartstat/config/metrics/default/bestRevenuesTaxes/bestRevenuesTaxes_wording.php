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

$metricType['bestRevenuesTaxes'] = "classic";
$metricTitle = $this->module->l('List of taxes sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesTaxes'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesTaxes/bestRevenuesTaxes_help.tpl');
$idCat['bestRevenuesTaxes'] = 99;

$listCols = array(
    array(
        'label' => $this->module->l('Taxes','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Total tax exc.','adminopartstatglobal'),
        'varName' => 'totalHt',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('Total tax','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('Total Tax inc.','adminopartstatglobal'),
        'varName' => 'totalTtc',
        'calcTotal' => true
    ),
);
