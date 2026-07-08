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

$metricType['averageRevenuesPerDay'] = "classic";
$metricTitle = $this->module->l('Average revenue per day','adminopartstatglobal');
$helpText['averageRevenuesPerDay'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/averageRevenuesPerDay/averageRevenuesPerDay_help.tpl');
$idCat['averageRevenuesPerDay'] = 2;




