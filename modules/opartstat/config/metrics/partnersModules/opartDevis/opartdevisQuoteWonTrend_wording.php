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

$metricType['opartdevisQuoteWonTrend'] = "partnerModule";
$metricTitle = $this->module->l('[Op\'art Devis] Number of quotes won trend','adminopartstatglobal');
$helpText['opartdevisQuoteWonTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/opartDevis/opartdevisQuoteWonTrend_help.tpl');





