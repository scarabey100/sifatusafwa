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

$metricType['averageQuantitiesSoldPerOrder'] = "classic";
$metricTitle = $this->module->l('Average quantities sold per order','adminopartstatglobal');
$helpText['averageQuantitiesSoldPerOrder'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageQuantitiesSoldPerOrder/averageQuantitiesSoldPerOrder_help.tpl');
$idCat['averageQuantitiesSoldPerOrder'] = 1;




