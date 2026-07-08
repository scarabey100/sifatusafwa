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

$metricType['pageViewsTrend'] = "classic";
$metricTitle = $this->module->l('Page views trend','adminopartstatglobal');
$helpText['pageViewsTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/pageViewsTrend/pageViewsTrend_help.tpl');
$idCat['pageViewsTrend'] = 4;




