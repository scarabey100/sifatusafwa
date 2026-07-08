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

$metricType['bestProfitsMediums'] = "classic";
$metricTitle = $this->module->l('List of mediums sorted by profits','adminopartstatglobal');
$helpText['bestProfitsMediums'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestProfitsMediums/bestProfitsMediums_help.tpl');
$idCat['bestProfitsMediums'] = 3;

$listCols = array(
    array(
        'label' => $this->module->l('Mediums','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Profits','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);