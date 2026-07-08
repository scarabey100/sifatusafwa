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

$metricType['bestProfitsVariationsAndFeatures'] = "classic";
$metricTitle = $this->module->l('List of variations with features sorted by profits','adminopartstatglobal');
$helpText['bestProfitsVariationsAndFeatures'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestProfitsVariationsAndFeatures/bestProfitsVariationsAndFeatures_help.tpl');
$idCat['bestProfitsVariationsAndFeatures'] = 3;

$listCols = array(
    array(
        'label' => $this->module->l('Variations','adminopartstatglobal'),
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
