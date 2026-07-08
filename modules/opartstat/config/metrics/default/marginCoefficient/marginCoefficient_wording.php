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

$metricType['marginCoefficient'] = "classic";
$metricTitle = $this->module->l('Margin coefficient','adminopartstatglobal');
$helpText['marginCoefficient'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/marginCoefficient/marginCoefficient_help.tpl');
$idCat['marginCoefficient'] = 99;




