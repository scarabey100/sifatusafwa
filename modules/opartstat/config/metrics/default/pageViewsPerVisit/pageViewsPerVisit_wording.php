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

$metricType['pageViewsPerVisit'] = "classic";
$metricTitle = $this->module->l('Page per visit','adminopartstatglobal');
$helpText['pageViewsPerVisit'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/pageViewsPerVisit/pageViewsPerVisit_help.tpl');
$idCat['pageViewsPerVisit'] = 4;



