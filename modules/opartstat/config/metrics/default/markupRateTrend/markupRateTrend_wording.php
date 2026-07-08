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

$metricType['markupRateTrend'] = "classic";
$metricTitle = $this->module->l('Markup rate trend','adminopartstatglobal');
$helpText['markupRateTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/markupRateTrend/markupRateTrend_help.tpl');
$idCat['markupRateTrend'] = 99;


