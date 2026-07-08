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

$metricType['dailyOrderList'] = "classic";
$metricTitle = $this->module->l('Daily order list','adminopartstatglobal');
$helpText['dailyOrderList'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/dailyOrderList/dailyOrderList_help.tpl');
$idCat['dailyOrderList'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Date','adminopartstatglobal'),
        'varName' => 'date',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('number orders','adminopartstatglobal'),
        'varName' => 'orders',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('quantities sold','adminopartstatglobal'),
        'varName' => 'quantity',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
    array(
        'label' => $this->module->l('total','adminopartstatglobal'),
        'varName' => 'total_ht',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);