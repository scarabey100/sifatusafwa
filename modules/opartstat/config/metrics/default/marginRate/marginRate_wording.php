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

$metricType['marginRate'] = "classic";
$metricTitle = $this->module->l('Margin rate','adminopartstatglobal');
$helpText['marginRate'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/marginRate/marginRate_help.tpl');
$idCat['marginRate'] = 99;




