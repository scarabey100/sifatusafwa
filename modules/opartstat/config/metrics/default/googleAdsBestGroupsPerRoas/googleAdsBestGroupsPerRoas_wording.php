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

$metricType['googleAdsBestGroupsPerRoas'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Groups sort by ROAS','adminopartstatglobal');
$helpText['googleAdsBestGroupsPerRoas'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsBestGroupsPerRoas/googleAdsBestGroupsPerRoas_help.tpl');
$idCat['googleAdsBestGroupsPerRoas'] = 7;

$listCols = array(
    array(
        'label' => $this->module->l('Groups','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('ROAS','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);



