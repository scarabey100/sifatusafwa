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

$metricType['stockValuePerProduct'] = "classic";
$metricTitle = $this->module->l('Stock value per product','adminopartstatglobal');
$helpText['stockValuePerProduct'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/stockValuePerProduct/stockValuePerProduct_help.tpl');

$listCols = array(
    array(
        'label' => $this->module->l('Id','adminopartstatglobal'),
        'varName' => 'idProd'
    ),
    array(
        'label' => $this->module->l('Ref','adminopartstatglobal'),
        'varName' => 'ref'
    ),
    array(
        'label' => $this->module->l('Prod','adminopartstatglobal'),
        'varName' => 'prod',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Stock','adminopartstatglobal'),
        'varName' => 'stock',
    ),
    array(
        'label' => $this->module->l('Purchase price','adminopartstatglobal'),
        'varName' => 'price'
    ),
    array(
        'label' => $this->module->l('Value','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    )
);

