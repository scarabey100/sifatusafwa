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

$metricType['averageRevenuesPerMonth'] = "classic";
$metricTitle = $this->module->l('Average revenue per month','adminopartstatglobal');
$helpText['averageRevenuesPerMonth'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageRevenuesPerMonth/averageRevenuesPerMonth_help.tpl');
$idCat['averageRevenuesPerMonth'] = 2;




