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

$metricType['bestRevenuesDayOfTheWeek'] = "classic";
$metricTitle = $this->module->l('List of day of the week sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesDayOfTheWeek'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesDayOfTheWeek/bestRevenuesDayOfTheWeek_help.tpl');
$idCat['bestRevenuesDayOfTheWeek'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Day','adminopartstatglobal'),
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