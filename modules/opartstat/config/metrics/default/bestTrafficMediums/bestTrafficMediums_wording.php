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

$metricType['bestTrafficMediums'] = "classic";
$metricTitle = $this->module->l('List of mediums sor by traffic','adminopartstatglobal');
$helpText['bestTrafficMediums'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestTrafficMediums/bestTrafficMediums_help.tpl');
$idCat['bestTrafficMediums'] = 4;

$listCols = array(
    array(
        'label' => $this->module->l('Mediums','adminopartstatglobal'),
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