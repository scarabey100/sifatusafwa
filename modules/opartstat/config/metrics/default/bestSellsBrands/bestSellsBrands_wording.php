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

$metricType['bestSellsBrands'] = "classic";
$metricTitle = $this->module->l('List of brands sorted by quantities sold','adminopartstatglobal');
$helpText['bestSellsBrands'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestSellsBrands/bestSellsBrands_help.tpl');
$idCat['bestSellsBrands'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Brands','adminopartstatglobal'),
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