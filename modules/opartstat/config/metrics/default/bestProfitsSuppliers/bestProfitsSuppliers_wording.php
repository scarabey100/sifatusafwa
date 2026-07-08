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

$metricType['bestProfitsSuppliers'] = "classic";
$metricTitle = $this->module->l('List of suppliers sorted by profits','adminopartstatglobal');
$helpText['bestProfitsSuppliers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestProfitsSuppliers/bestProfitsSuppliers_help.tpl');
$idCat['bestProfitsSuppliers'] = 3;

$listCols = array(
    array(
        'label' => $this->module->l('Suppliers','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Profits','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);