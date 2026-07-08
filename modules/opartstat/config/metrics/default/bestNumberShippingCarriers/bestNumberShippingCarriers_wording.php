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

$metricType['bestNumberShippingCarriers'] = "classic";
$metricTitle = $this->module->l('List of carriers sorted by number of shippings','adminopartstatglobal');
$helpText['bestNumberShippingCarriers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestNumberShippingCarriers/bestNumberShippingCarriers_help.tpl');
$idCat['bestNumberShippingCarriers'] = 99;

$listCols = array(
    array(
        'label' => $this->module->l('Carriers','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Shippings','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);



