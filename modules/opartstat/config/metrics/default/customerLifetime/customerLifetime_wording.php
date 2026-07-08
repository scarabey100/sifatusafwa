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

$metricType['customerLifetime'] = "classic";
$metricTitle = $this->module->l('Customer lifetime (In days)','adminopartstatglobal');
$helpText['customerLifetime'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/customerLifetime/customerLifetime_help.tpl');
$idCat['customerLifetime'] = 6;



