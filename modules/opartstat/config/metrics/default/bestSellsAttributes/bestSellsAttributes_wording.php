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

$metricType['bestSellsAttributes'] = "classic";
$metricTitle = $this->module->l('List of attributes sorted by quantities sold','adminopartstatglobal');
$helpText['bestSellsAttributes'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestSellsAttributes/bestSellsAttributes_help.tpl');
$idCat['bestSellsAttributes'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Attributes','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Quantities sold','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);