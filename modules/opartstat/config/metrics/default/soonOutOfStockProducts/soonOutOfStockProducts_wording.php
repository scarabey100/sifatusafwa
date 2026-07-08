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

$metricType['soonOutOfStockProducts'] = "classic";
$metricTitle = $this->module->l('List of products soon out of stock','adminopartstatglobal');
$helpText['soonOutOfStockProducts'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/soonOutOfStockProducts/soonOutOfStockProducts_help.tpl');

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
        'label' => $this->module->l('Brand','adminopartstatglobal'),
        'varName' => 'brand'
    ),
    array(
        'label' => $this->module->l('Prod','adminopartstatglobal'),
        'varName' => 'prodName',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Sells','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
    array(
        'label' => $this->module->l('Stock','adminopartstatglobal'),
        'varName' => 'stock'
    ),
);


