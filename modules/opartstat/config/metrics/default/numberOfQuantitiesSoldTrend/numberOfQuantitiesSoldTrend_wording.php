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

$metricType['numberOfQuantitiesSoldTrend'] = "classic";
$metricTitle = $this->module->l('Number of quantities sold trend','adminopartstatglobal');
$helpText['numberOfQuantitiesSoldTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/numberOfQuantitiesSoldTrend/numberOfQuantitiesSoldTrend_help.tpl');
$idCat['numberOfQuantitiesSoldTrend'] = 1;




