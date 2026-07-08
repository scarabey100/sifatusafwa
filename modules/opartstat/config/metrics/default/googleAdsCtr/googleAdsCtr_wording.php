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

$metricType['googleAdsCtr'] = "googleAds";
$metricTitle = $this->module->l('[GADS] CTR','adminopartstatglobal');
$helpText['googleAdsCtr'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCtr/googleAdsCtr_help.tpl');
$idCat['googleAdsCtr'] = 7;




