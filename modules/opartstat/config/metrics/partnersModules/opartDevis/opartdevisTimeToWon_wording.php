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

$metricType['opartdevisTimeToWon'] = "partnerModule";
$metricTitle = $this->module->l('[Op\'art Devis] Average time to win a quote (in days)','adminopartstatglobal');
$helpText['opartdevisTimeToWon'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/opartDevis/opartdevisTimeToWon_help.tpl');

