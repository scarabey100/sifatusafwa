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

$metricType['profitsTrend'] = "classic";
$metricTitle = $this->module->l('Profits Trend','adminopartstatglobal');
$helpText['profitsTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/profitsTrend/profitsTrend_help.tpl');
$idCat['profitsTrend'] = 3;


