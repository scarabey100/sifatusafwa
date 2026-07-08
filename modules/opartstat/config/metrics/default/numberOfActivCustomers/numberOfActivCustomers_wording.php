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

$metricType['numberOfActivCustomers'] = "classic";
$metricTitle = $this->module->l('Number of activ customers','adminopartstatglobal');
$helpText['numberOfActivCustomers'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/numberOfActivCustomers/numberOfActivCustomers_help.tpl');
$idCat['numberOfActivCustomers'] = 6;




