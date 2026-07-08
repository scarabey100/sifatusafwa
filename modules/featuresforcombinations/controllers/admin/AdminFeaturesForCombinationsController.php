<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'featuresforcombinations/classes/FFC.php';

class AdminFeaturesForCombinationsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'featuresforcombinations';
        $this->identifier = 'id_featuresforcombinations';
        $this->className = 'FFC';

        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['import'] = [
            'href' => $this->context->link->getAdminLink('AdminImportFeaturesForCombinations'),
            // $this->l('Import by File')
            'desc' => $this->trans('Import by File', [], 'Modules.Featuresforcombinations.Global'),
        ];
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        // $this->l('Add a feature')`
        Media::addJsDef([
            'admin_ffc_ajax_url' => pSQL($this->context->link->getAdminLink('AdminFeaturesForCombinations')),
            'addFeatureLabel' => $this->trans('Add a feature', [], 'Modules.Featuresforcombinations.Feature'),
        ]);
        $this->addJS($this->module->getLocalPath() . 'views/js/bulk_add.js');

        $id_product = 0;
        $id_product_attribute = 0;

        $ffc_tmp = FFC::getAllForProductAttribute($id_product, $id_product_attribute);

        $this->context->smarty->assign([
            'ffc_features' => $ffc_tmp['features'],
            'ffc_value' => [
                'id_feature' => 0,
                'predefined_values' => [],
                'id_feature_value' => 0,
                'custom' => false,
            ],
            'id_product_attribute' => (int) $id_product_attribute,
            'ffc_languages' => Language::getLanguages(),
            'ffc_default_language' => $this->context->language->id,
            'ffcFeaturesProduct' => Feature::getFeatures($this->context->language->id),
            'ffc_iter' => '__iter__',
            'ffc_custom_values' => $ffc_tmp['custom_values'],
        ]);
        $this->context->smarty->assign([
            'attribute_groups' => AttributeGroup::getAttributesGroups($this->context->language->id),
            'ffc_prototype' => $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/product_feature_config.tpl'
            ),
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/bulk_add.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitImportFFC') && Tools::getValue('ffc')) {
            // recup only les features OK du POST
            $hasCustomValues = function ($value) {
                return (bool) $value;
            };
            $ffc = array_map(function ($attribute) use ($hasCustomValues) {
                return array_filter($attribute, function ($feature) use ($hasCustomValues) {
                    return !empty($feature['id_feature_value']) || isset($feature['custom_value']) && !empty(array_filter($feature['custom_value'], $hasCustomValues));
                });
            }, Tools::getValue('ffc'));

            if (!empty($ffc)) {
                foreach ($ffc as $id_attribute => $features) {
                    foreach ($features as $feature) {
                        // get and insert rows (does not work for custom values)
                        FFC::bulkAddCombinationsFeaturesToDB(
                            $id_attribute,
                            (int) $feature['id_feature'],
                            (int) $feature['id_feature_value'],
                            false
                        );
                    }
                }

                Tools::redirectAdmin($this->context->link->getAdminLink('AdminFeaturesForCombinations') . '&conf=4');
            }
        }
        return parent::postProcess();
    }

    public function ajaxProcessGetFeatureValues()
    {
        $id_feature = (int) Tools::getValue('id_feature');

        exit(json_encode(FeatureValue::getFeatureValuesWithLang(Context::getContext()->language->id, $id_feature)));
    }

    public function ajaxProcessGetAttributes()
    {
        $id_attribute_group = (int) Tools::getValue('id_attribute_group');

        exit(json_encode(AttributeGroup::getAttributes(Context::getContext()->language->id, $id_attribute_group)));
    }

    protected function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            return parent::trans($id, $parameters, $domain, $locale);
        } else {
            return $this->l($id);
        }
    }
}
