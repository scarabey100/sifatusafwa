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

$metricType['googleAdsRoas'] = "googleAds";
$metricTitle = $this->module->l('[GADS] ROAS','adminopartstatglobal');
$helpText['googleAdsRoas'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsRoas/googleAdsRoas_help.tpl');
$idCat['googleAdsRoas'] = 7;




