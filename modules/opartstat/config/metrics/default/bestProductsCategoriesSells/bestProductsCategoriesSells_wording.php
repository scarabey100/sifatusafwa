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

$metricType['bestProductsCategoriesSells'] = "classic";
$metricTitle = $this->module->l('Best products by sells for each categories','adminopartstatglobal');
$helpText['bestProductsCategoriesSells'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestProductsCategoriesSells/bestProductsCategoriesSells_help.tpl');
$idCat['bestProductsCategoriesSells'] = 1;

$listCols = array(
    array(
        'label' => $this->module->l('Categories','adminopartstatglobal'),
        'varName' => 'catName',
        'varNameForLink' => 'catLink',
        'cssClassName' => 'largeColumn'        
    ),
    array(
        'label' => $this->module->l('Products','adminopartstatglobal'),
        'varName' => 'prodName',
        'varNameForLink' => 'prodLink',
        'cssClassName' => 'alignLeft'
    ),
    array(
        'label' => $this->module->l('Sells','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);