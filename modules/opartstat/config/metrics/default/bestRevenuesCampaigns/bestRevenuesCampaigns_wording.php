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

$metricType['bestRevenuesCampaigns'] = "classic";
$metricTitle = $this->module->l('List of campaigns sorted by revenues','adminopartstatglobal');
$helpText['bestRevenuesCampaigns'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestRevenuesCampaigns/bestRevenuesCampaigns_help.tpl');
$idCat['bestRevenuesCampaigns'] = 2;

$listCols = array(
    array(
        'label' => $this->module->l('Campaigns','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Revenues','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);