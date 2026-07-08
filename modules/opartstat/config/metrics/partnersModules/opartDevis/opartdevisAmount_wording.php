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

$metricType['opartdevisAmount'] = "partnerModule";
$metricTitle = $this->module->l('[Op\'art Devis] Total amount of quotes','adminopartstatglobal');
$helpText['opartdevisAmount'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/opartDevis/opartdevisAmount_help.tpl');

