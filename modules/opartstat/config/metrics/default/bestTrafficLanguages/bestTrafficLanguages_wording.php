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

$metricType['bestTrafficLanguages'] = "classic";
$metricTitle = $this->module->l('List of languages sort by traffic','adminopartstatglobal');
$helpText['bestTrafficLanguages'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestTrafficLanguages/bestTrafficLanguages_help.tpl');
$idCat['bestTrafficLanguages'] = 4;

$listCols = array(
    array(
        'label' => $this->module->l('Languages','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Views','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);