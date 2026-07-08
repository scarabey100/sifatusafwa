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

$metricType['bestCountriesPerTaxesRevenues'] = "classic";
$metricTitle = $this->module->l('List of countries sorted by taxes revenues','adminopartstatglobal');
$helpText['bestCountriesPerTaxesRevenues'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestCountriesPerTaxesRevenues/bestCountriesPerTaxesRevenues_help.tpl');
$idCat['bestCountriesPerTaxesRevenues'] = 99;

$listCols = array(
    array(
        'label' => $this->module->l('Countries','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Prod. taxes','adminopartstatglobal'),
        'varName' => 'totalProductTaxe',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('Ship. taxes','adminopartstatglobal'),
        'varName' => 'totalShippingTaxe',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('Total Taxes','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);