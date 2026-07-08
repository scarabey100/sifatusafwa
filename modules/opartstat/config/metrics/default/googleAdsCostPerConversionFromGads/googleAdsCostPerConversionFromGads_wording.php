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

$metricType['googleAdsCostPerConversionFromGads'] = "googleAds";
$metricTitle = $this->module->l('[GADS] Cost per conversion (Attr. Google Ads)','adminopartstatglobal');
$helpText['googleAdsCostPerConversionFromGads'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCostPerConversionFromGads/googleAdsCostPerConversionFromGads_help.tpl');
$idCat['googleAdsCostPerConversionFromGads'] = 7;




