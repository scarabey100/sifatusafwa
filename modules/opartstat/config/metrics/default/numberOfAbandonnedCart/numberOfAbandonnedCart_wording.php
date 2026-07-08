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

$metricType['numberOfAbandonnedCart'] = "classic";
$metricTitle = $this->module->l('Number of abandonned cart','adminopartstatglobal');
$helpText['numberOfAbandonnedCart'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/numberOfAbandonnedCart/numberOfAbandonnedCart_help.tpl');
$idCat['numberOfAbandonnedCart'] = 1;




