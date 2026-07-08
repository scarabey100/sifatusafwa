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

$metricType['revenuesForecast'] = "classic";
$metricTitle = $this->module->l('Revenues forecast','adminopartstatglobal');
$helpText['revenuesForecast'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/revenuesForecast/revenuesForecast_help.tpl');
$idCat['revenuesForecast'] = 2;




