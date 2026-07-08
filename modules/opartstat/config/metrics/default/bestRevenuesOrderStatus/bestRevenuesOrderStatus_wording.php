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

$metricType['bestRevenuesOrderStatus'] = "classic";
$metricTitle = $this->module->l('List of order status sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesOrderStatus'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesOrderStatus/bestRevenuesOrderStatus_help.tpl');
$idCat['bestRevenuesOrderStatus'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Status','adminopartstatglobal'),
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
