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

$metricType['bestProductsCategoriesRevenues'] = "classic";
$metricTitle = $this->module->l('Best products by revenues for each categories','adminopartstatglobal');
$helpText['bestProductsCategoriesRevenues'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestProductsCategoriesRevenues/bestProductsCategoriesRevenues_help.tpl');
$idCat['bestProductsCategoriesRevenues'] = 2;


$listCols = array(
    array(
        'label' => $this->module->l('Categories','adminopartstatglobal'),
        'varName' => 'catName',
        'varNameForLink' => 'catLink',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Products','adminopartstatglobal'),
        'varName' => 'prodName',
        'varNameForLink' => 'prodLink',
        'cssClassName' => 'alignLeft'
    ),
    array(
        'label' => $this->module->l('Revenues','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);