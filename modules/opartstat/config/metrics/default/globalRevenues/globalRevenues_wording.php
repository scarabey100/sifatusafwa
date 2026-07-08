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

$metricType['globalRevenues'] = "classic";
$metricTitle = $this->module->l('Global revenues','adminopartstatglobal');
$helpText['globalRevenues'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/globalRevenues/globalRevenues_help.tpl');
$idCat['globalRevenues'] = 2;




