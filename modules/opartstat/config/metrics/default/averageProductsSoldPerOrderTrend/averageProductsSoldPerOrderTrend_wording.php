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

$metricType['averageProductsSoldPerOrderTrend'] = "classic";
$metricTitle = $this->module->l('Average  number of products sold per order trend','adminopartstatglobal');
$helpText['averageProductsSoldPerOrderTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageProductsSoldPerOrderTrend/averageProductsSoldPerOrderTrend_help.tpl');
$idCat['averageProductsSoldPerOrderTrend'] = 1;




