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

$metricType['customerLifetimeValue'] = "classic";
$metricTitle = $this->module->l('Customer lifetime value','adminopartstatglobal');
$helpText['customerLifetimeValue'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/customerLifetimeValue/customerLifetimeValue_help.tpl');
$idCat['customerLifetimeValue'] = 6;



