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

$metricType['bestConversionRateProducts'] = "classic";
$metricTitle = $this->module->l('List of products sorted by conversion rate','adminopartstatglobal');
$helpText['bestConversionRateProducts'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'opartstat/config/metrics/default/bestConversionRateProducts/bestConversionRateProducts_help.tpl');
$idCat['bestConversionRateProducts'] = 5;

$listCols = array(
    array(
        'label' => $this->module->l('Products','adminopartstatglobal'),
        'varName' => 'name',
        'varNameForLink' => 'link',
        'cssClassName' => 'largeColumn'
    ),
    array(
        'label' => $this->module->l('Conversion rate','adminopartstatglobal'),
        'varName' => 'total',
        'cssClassName' => 'osInitTotalCa'
    ),
);