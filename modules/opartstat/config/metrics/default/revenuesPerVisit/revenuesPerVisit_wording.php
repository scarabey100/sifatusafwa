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

$metricType['revenuesPerVisit'] = "classic";
$metricTitle = $this->module->l('Revenues per visit','adminopartstatglobal');
$helpText['revenuesPerVisit'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/revenuesPerVisit/revenuesPerVisit_help.tpl');
$idCat['revenuesPerVisit'] = 2;




