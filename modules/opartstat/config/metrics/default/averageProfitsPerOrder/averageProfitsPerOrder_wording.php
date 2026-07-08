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

$metricType['averageProfitsPerOrder'] = "classic";
$metricTitle = $this->module->l('Average profits per order','adminopartstatglobal');
$helpText['averageProfitsPerOrder'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageProfitsPerOrder/averageProfitsPerOrder_help.tpl');
$idCat['averageProfitsPerOrder'] = 3;