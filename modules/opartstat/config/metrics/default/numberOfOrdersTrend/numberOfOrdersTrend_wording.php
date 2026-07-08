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

$metricType['numberOfOrdersTrend'] = "classic";
$metricTitle = $this->module->l('Number of orders trend','adminopartstatglobal');
$helpText['numberOfOrdersTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/numberOfOrdersTrend/numberOfOrdersTrend_help.tpl');
$idCat['numberOfOrdersTrend'] = 1;




