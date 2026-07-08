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

$metricType['repurchaseRate'] = "classic";
$metricTitle = $this->module->l('Repurchase rate','adminopartstatglobal');
$helpText['repurchaseRate'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/repurchaseRate/repurchaseRate_help.tpl');
$idCat['repurchaseRate'] = 1;




