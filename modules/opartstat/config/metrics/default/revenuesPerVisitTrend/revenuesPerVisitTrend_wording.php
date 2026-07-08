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

$metricType['revenuesPerVisitTrend'] = "classic";
$metricTitle = $this->module->l('Revenues per visit Trend','adminopartstatglobal');
$helpText['revenuesPerVisitTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/revenuesPerVisitTrend/revenuesPerVisitTrend_help.tpl');
$idCat['revenuesPerVisitTrend'] = 2;




