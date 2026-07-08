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

$metricType['visitorsTrend'] = "classic";
$metricTitle = $this->module->l('Visitors trend','adminopartstatglobal');
$helpText['visitorsTrend'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/visitorsTrend/visitorsTrend_help.tpl');
$idCat['visitorsTrend'] = 4;





