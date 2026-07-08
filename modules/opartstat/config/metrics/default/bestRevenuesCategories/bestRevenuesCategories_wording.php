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

$metricType['bestRevenuesCategories'] = "classic";
$metricTitle = $this->module->l('List of categories sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesCategories'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesCategories/bestRevenuesCategories_help.tpl');
$idCat['bestRevenuesCategories'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Categories','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Revenues','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);