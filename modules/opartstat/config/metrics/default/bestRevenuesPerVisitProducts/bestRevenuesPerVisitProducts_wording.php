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

$metricType['bestRevenuesPerVisitProducts'] = "classic";
$metricTitle = $this->module->l('List of products sorted by revenues per visit','adminopartstatglobal');
$helpText['bestRevenuesPerVisitProducts'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesPerVisitProducts/bestRevenuesPerVisitProducts_help.tpl');
$idCat['bestRevenuesPerVisitProducts'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Products','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('RevenuesPerVisit','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);