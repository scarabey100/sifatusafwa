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

$metricType['googleAdsCpc'] = "googleAds";
$metricTitle = $this->module->l('[GADS] CPC','adminopartstatglobal');
$helpText['googleAdsCpc'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/googleAdsCpc/googleAdsCpc_help.tpl');
$idCat['googleAdsCpc'] = 7;




