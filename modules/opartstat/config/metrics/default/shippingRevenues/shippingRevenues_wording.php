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

$metricType['shippingRevenues'] = "classic";
$metricTitle = $this->module->l('Shipping revenues','adminopartstatglobal');
$helpText['shippingRevenues'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/shippingRevenues/shippingRevenues_help.tpl');
$idCat['shippingRevenues'] = 99;




