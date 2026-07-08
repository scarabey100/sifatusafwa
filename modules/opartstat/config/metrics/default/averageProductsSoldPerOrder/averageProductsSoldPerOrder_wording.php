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

$metricType['averageProductsSoldPerOrder'] = "classic";
$metricTitle = $this->module->l('Average number of products sold per order','adminopartstatglobal');
$helpText['averageProductsSoldPerOrder'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageProductsSoldPerOrder/averageProductsSoldPerOrder_help.tpl');
$idCat['averageProductsSoldPerOrder'] = 1;




