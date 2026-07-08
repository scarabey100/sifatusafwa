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

$metricType['bestCouponsPerRevenues'] = "classic";
$metricTitle = $this->module->l('List of coupons sorted by revenues','adminopartstatglobal');
$helpText['bestCouponsPerRevenues'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestCouponsPerRevenues/bestCouponsPerRevenues_help.tpl');
$idCat['bestCouponsPerRevenues'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Coupons','adminopartstatglobal'),
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