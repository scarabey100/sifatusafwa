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

$metricType['ambjolissearchBestKw'] = "partnerModule";
$metricTitle = $this->module->l('[Jolis search] List of keyword sort by occurrence','adminopartstatglobal');
$helpText['ambjolissearchBestKw'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/AmbJoliSearch/ambjolissearchBestKw_help.tpl');

$listCols = array(
    array(
        'label' => $this->module->l('Keywords','adminopartstatglobal'),
        'varName' => 'name',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Occurences','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa',
        'calcTotal' => true
    ),
);


