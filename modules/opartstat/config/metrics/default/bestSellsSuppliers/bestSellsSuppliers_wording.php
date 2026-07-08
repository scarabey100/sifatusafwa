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

$metricType['bestSellsSuppliers'] = "classic";
$metricTitle = $this->module->l('List of suppliers sorted by quantities sold','adminopartstatglobal');
$helpText['bestSellsSuppliers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestSellsSuppliers/bestSellsSuppliers_help.tpl');
$idCat['bestSellsSuppliers'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Suppliers','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Quantities sold','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);