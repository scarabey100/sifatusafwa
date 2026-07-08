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

$metricType['googleAdsOrder'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Orders','adminopartstatglobal');
$helpText['googleAdsOrder'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsOrder/googleAdsOrder_help.tpl');
$idCat['googleAdsOrder'] = 7;


$listCols = array(
    array(
        'label' => $this->module->l('ID order','adminopartstatglobal'),
        'varName' => 'idOrder',
    ),
    array(
        'label' => $this->module->l('reference','adminopartstatglobal'),
        'varName' => 'reference',
     ),
    array(
        'label' => $this->module->l('gclid','adminopartstatglobal'),
        'varName' => 'gclid',
    ),
);




