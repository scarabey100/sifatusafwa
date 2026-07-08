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

$metricType['bestNumberOrderPaymentMethods'] = "classic";
$metricTitle = $this->module->l('List of payment methods sorted by number of orders','adminopartstatglobal');
$helpText['bestNumberOrderPaymentMethods'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestNumberOrderPaymentMethods/bestNumberOrderPaymentMethods_help.tpl');
$idCat['bestNumberOrderPaymentMethods'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Payment Method','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Orders','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);