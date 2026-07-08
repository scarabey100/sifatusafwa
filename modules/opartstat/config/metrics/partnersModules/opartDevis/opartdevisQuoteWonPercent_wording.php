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

$metricType['opartdevisQuoteWonPercent'] = "partnerModule";
$metricTitle = $this->module->l('[Op\'art Devis] Percentage of quotes won','adminopartstatglobal');
$helpText['opartdevisQuoteWonPercent'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/opartDevis/opartdevisQuoteWonPercent_help.tpl');

