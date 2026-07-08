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

$metricType['googleAdsCostPerConversion'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Cost per conversion','adminopartstatglobal');
$helpText['googleAdsCostPerConversion'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCostPerConversion/googleAdsCostPerConversion_help.tpl');
$idCat['googleAdsCostPerConversion'] = 7;




