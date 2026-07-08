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

$metricType['bestNumberOrderCountries'] = "classic";
$metricTitle = $this->module->l('List of countries sorted by number of orders','adminopartstatglobal');
$helpText['bestNumberOrderCountries'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestNumberOrderCountries/bestNumberOrderCountries_help.tpl');
$idCat['bestNumberOrderCountries'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Countries','adminopartstatglobal'),
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