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

$metricType['opartdevisnumberOfQuotation'] = "classic";
$metricTitle = $this->module->l('Number of quotes','adminopartstatglobal');
$helpText['opartdevisnumberOfQuotation'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/partnersModules/opartDevis/opartdevisnumberOfQuotation_help.tpl');
$idCat['opartdevisnumberOfQuotation'] = 8;




