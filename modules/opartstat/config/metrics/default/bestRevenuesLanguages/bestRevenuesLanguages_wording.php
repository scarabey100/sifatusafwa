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

$metricType['bestRevenuesLanguages'] = "classic";
$metricTitle = $this->module->l('List of languages sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesLanguages'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesLanguages/bestRevenuesLanguages_help.tpl');
$idCat['bestRevenuesLanguages'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Languages','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Revenues','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);