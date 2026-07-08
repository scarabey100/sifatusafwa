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

$metricType['numberOfNewSignups'] = "classic";
$metricTitle = $this->module->l('Number of new sign-ups','adminopartstatglobal');
$helpText['numberOfNewSignups'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/numberOfNewSignups/numberOfNewSignups_help.tpl');
$idCat['numberOfNewSignups'] = 6;



