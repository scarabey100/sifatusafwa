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

$metricType['googleAdsNumberOfNewCustomers'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Number of new customers','adminopartstatglobal');
$helpText['googleAdsNumberOfNewCustomers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsNumberOfNewCustomers/googleAdsNumberOfNewCustomers_help.tpl');
$idCat['googleAdsNumberOfNewCustomers'] = 7;



