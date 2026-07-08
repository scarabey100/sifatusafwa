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

$metricType['customersWithNoOrder'] = "classic";
$metricTitle = $this->module->l('List of customers with no order','adminopartstatglobal');
$helpText['customersWithNoOrder'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/customersWithNoOrder/customersWithNoOrder_help.tpl');
$idCat['customersWithNoOrder'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Customers','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Email','adminopartstatglobal'),
        'varName' => 'email'
    ),
    array(
        'label' => $this->module->l('Orders','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);