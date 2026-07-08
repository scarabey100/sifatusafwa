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

$metricType['bestCountriesPerShipping'] = "classic";
$metricTitle = $this->module->l('List of countries ranked by delivery costs','adminopartstatglobal');
$helpText['bestCountriesPerShipping'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestCountriesPerShipping/bestCountriesPerShipping_help.tpl');
$idCat['bestCountriesPerShipping'] = 99;

$listCols = array(
    array(
        'label' => $this->module->l('Countries','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Total Shipping','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);