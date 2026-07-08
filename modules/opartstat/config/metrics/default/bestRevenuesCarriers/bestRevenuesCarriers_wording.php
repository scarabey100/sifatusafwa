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

$metricType['bestRevenuesCarriers'] = "classic";
$metricTitle = $this->module->l('List of carriers sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesCarriers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesCarriers/bestRevenuesCarriers_help.tpl');
$idCat['bestRevenuesCarriers'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Carriers','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Total revenues','adminopartstatglobal'),
        'varName' => 'totalRevenues'
    ),
    array(
        'label' => $this->module->l('Shipping revenues','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);
