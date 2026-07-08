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

class Featuresforcombinations extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'featuresforcombinations';
        $this->tab = 'content_management';
        $this->version = '1.5.2';
        $this->author = 'Ciren';
        $this->need_instance = 1;
        $this->module_key = '6efac87003778261fb044bd92a10016d';

        $this->bootstrap = true;

        // $this->l('Features for the combinations');
        // $this->l('Import features for the combinations');
        $this->tabs = [
            [
                'class_name' => 'AdminFeaturesForCombinations',
                'visible' => false,
                'name' => $this->trans('Features for the combinations', [], 'Modules.Featuresforcombinations.Global'),
                'parent_class_name' => 'IMPROVE',
            ],
            [
                'class_name' => 'AdminImportFeaturesForCombinations',
                'visible' => false,
                'name' => $this->trans('Import features for the combinations', [], 'Modules.Featuresforcombinations.Global'),
                'parent_class_name' => 'IMPROVE',
            ],
        ];

        parent::__construct();

        $this->displayName = $this->trans('Features for the combinations', [], 'Modules.Featuresforcombinations.Global');
        $this->description = $this->trans('Features for the combinations', [], 'Modules.Featuresforcombinations.Global');

        // $this->l('Are you sure you want to uninstall this module?');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Featuresforcombinations.Global');

        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => '8.2.99'];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $this->addTab();

        include dirname(__FILE__) . '/sql/install.php';

        if (version_compare(_PS_VERSION_, '1.7.2.0', '>=')) {
            $this->registerHook('displayAdminProductsCombinationBottom');
        } else {
            $this->registerHook('displayAdminProductsExtra');
        }

        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
            $this->registerHook('actionCombinationFormFormBuilderModifier');
            $this->registerHook('actionAfterUpdateCombinationFormFormHandler');
        }

        Configuration::updateValue('FFC_separator', ';');

        return $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionGetProductPropertiesAfter')
            && $this->registerHook('actionProductSave');
    }

    public function addTab()
    {
        if (Tab::getIdFromClassName('AdminFeaturesForCombinations') === false) {
            $tab = new Tab();
            $tab->class_name = 'AdminFeaturesForCombinations';
            $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
            $tab->module = $this->name;
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->trans('Features for the combinations', [], 'Modules.Featuresforcombinations.Global');
            $tab->active = false;
            $tab->add();
        }
        if (Tab::getIdFromClassName('AdminImportFeaturesForCombinations') === false) {
            $tab = new Tab();
            $tab->class_name = 'AdminImportFeaturesForCombinations';
            $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
            $tab->module = $this->name;
            $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->trans('Import features for the combinations', [], 'Modules.Featuresforcombinations.Global');
            $tab->active = false;
            $tab->add();
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return $this->removeTab();
    }

    private function removeTab()
    {
        $ids_tab = [
            (int) Tab::getIdFromClassName('AdminFeaturesForCombinations'),
            (int) Tab::getIdFromClassName('AdminImportFeaturesForCombinations'),
        ];
        foreach ($ids_tab as $id_tab) {
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }

        return true;
    }

    public function getContent()
    {
        $link = $this->context->link->getAdminLink('AdminFeaturesForCombinations');
        Tools::redirectAdmin($link);
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == 'AdminProducts') {
            $this->context->controller->addJS($this->getLocalPath() . 'views/js/ffc_features.js');
            $this->context->controller->addJS($this->getLocalPath() . 'views/js/ffc_features_new.js');

            Media::addJsDef([
                'admin_ffc_ajax_url' => pSQL($this->context->link->getAdminLink('AdminFeaturesForCombinations')),
            ]);
        }
    }

    public function hookDisplayAdminProductsCombinationBottom($params)
    {
        $id_product = $params['id_product'];
        $id_product_attribute = $params['id_product_attribute'];

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
            'ffc_prototype' => $this->context->smarty->fetch(
                $this->getLocalPath() . 'views/templates/hook/product_feature.tpl'
            ),
        ]);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/displayAdminProductsCombinationBottom.tpl'
        );
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = array_key_exists('id_product', $params) ?
            (int) $params['id_product'] :
            (int) Tools::getValue('id_product');
        $product_attributes = Db::getInstance()->executeS(
            'SELECT pa.`id_product_attribute`, GROUP_CONCAT(al.`name` SEPARATOR ", ") AS "name"
    		FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                ON pa.`id_product_attribute` = pac.`id_product_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON pac.`id_attribute` = al.`id_attribute`
                    AND al.`id_lang` = ' . (int) $this->context->language->id . '
    		WHERE pa.`id_product` = ' . (int) $id_product . '
            GROUP BY pa.`id_product_attribute`'
        );

        $ffc_features = [];
        $custom_values = [];
        foreach ($product_attributes as $product_attribute) {
            $ffc_tmp = FFC::getAllForProductAttribute(
                $id_product,
                $product_attribute['id_product_attribute']
            );

            $ffc_features[$product_attribute['id_product_attribute']] = $ffc_tmp['features'];
            foreach ($ffc_tmp['custom_values'] as $key => $val) {
                if (!array_key_exists($key, $custom_values)) {
                    $custom_values[$key] = $val;
                }
            }
        }

        $this->context->smarty->assign([
            'ffc_features' => $ffc_features,
            'ffc_value' => [
                'id_feature' => 0,
                'predefined_values' => [],
                'id_feature_value' => 0,
                'custom' => false,
            ],
            'ffc_product_attributes' => $product_attributes,
            'ffc_languages' => Language::getLanguages(),
            'ffc_default_language' => $this->context->language->id,
            'ffcFeaturesProduct' => Feature::getFeatures($this->context->language->id),
            'ffc_iter' => '__iter__',
            'ffc_custom_values' => $custom_values,
        ]);

        $ffc_prototypes = [];
        foreach ($product_attributes as $product_attribute) {
            $this->context->smarty->assign([
                'id_product_attribute' => (int) $product_attribute['id_product_attribute'],
            ]);
            $ffc_prototypes[$product_attribute['id_product_attribute']] = $this->context->smarty->fetch(
                $this->getLocalPath() . 'views/templates/hook/product_feature171.tpl'
            );
        }
        $this->context->smarty->assign([
            'ffc_prototypes' => $ffc_prototypes,
        ]);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/displayAdminProductsExtra.tpl'
        );
    }

    public function hookActionProductSave($params)
    {
        if (!Tools::getValue('ffc_submitted')) {
            return true;
        }

        $ffc_form = Tools::getValue('ffc', []);
        $id_product = (int) $params['id_product'];
        $product_attribute_ids = Product::getProductAttributesIds($id_product);
        if (empty($product_attribute_ids)) {
            return true;
        }
        $ffc = new FFC($id_product);

        $languages = Language::getLanguages();

        foreach ($product_attribute_ids as $product_attribute) {
            $id_product_attribute = $product_attribute['id_product_attribute'];
            $ffc->deleteCombinationsFeatures($id_product_attribute);
            if (array_key_exists($id_product_attribute, $ffc_form)) {
                foreach ($ffc_form[$id_product_attribute] as $feature) {
                    if (!empty($feature['id_feature_value'])) {
                        $ffc->addCombinationsFeaturesToDB(
                            $id_product_attribute,
                            $feature['id_feature'],
                            $feature['id_feature_value']
                        );
                    } elseif ($defaultValue = $this->checkFeatures($languages, $feature['custom_value'], $feature['id_feature'])) {
                        $idValue = $ffc->addCombinationsFeaturesToDB(
                            $id_product_attribute,
                            $feature['id_feature'],
                            0,
                            1
                        );
                        foreach ($languages as $language) {
                            $valueToAdd = (isset($feature['custom_value'][$language['id_lang']])
                                && !empty($feature['custom_value'][$language['id_lang']]))
                                ? $feature['custom_value'][$language['id_lang']]
                                : $defaultValue;

                            $ffc->addFeaturesCustomToDB($idValue, (int) $language['id_lang'], $valueToAdd);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param mixed $params
     */
    public function hookActionGetProductPropertiesAfter($params)
    {
        $product = &$params['product'];

        $id_lang = (int) $params['id_lang'];

        $id_product = (int) $product['id_product'];
        $id_product_attribute = (int) $product['id_product_attribute'];

        $ffc = FFC::getCombinationsFeatures($id_product, $id_product_attribute, $id_lang);
        if (isset($params['product']['features']) && !empty($params['product']['features'])) {
            foreach ($params['product']['features'] as $f) {
                foreach ($ffc as $f2key => $f2) {
                    if ($f['value'] == $f2['value'] && $f['name'] == $f2['name']) {
                        unset($ffc[$f2key]);
                    }
                }
            }
            $params['product']['features'] = array_merge($params['product']['features'], $ffc);
        } else {
            $params['product']['features'] = $ffc;
        }
    }

    protected function getFeaturesForForm($id_product, $id_product_attribute)
    {
        return FFC::getAllForProductAttributeNew(
            $id_product,
            $id_product_attribute
        );
    }

    protected function checkFeatures($languages, $customValue, $featureId)
    {
        $rules = call_user_func(['FeatureValue', 'getValidationRules'], 'FeatureValue');
        $feature = Feature::getFeature((int) Configuration::get('PS_LANG_DEFAULT'), $featureId);

        foreach ($languages as $language) {
            if (isset($customValue[$language['id_lang']])
                && !empty($customValue[$language['id_lang']])) {
                $val = $customValue[$language['id_lang']];
                $current_language = new Language($language['id_lang']);
                if (Tools::strlen($val) > $rules['sizeLang']['value']) {
                    $this->context->controller->errors[] = $this->trans(
                        'The name for feature %1$s is too long in %2$s.',
                        [
                            ' <b>' . $feature['name'] . '</b>',
                            $current_language->name,
                        ],
                        'Admin.Catalog.Notification'
                    );
                } elseif (!call_user_func(['Validate', $rules['validateLang']['value']], $val)) {
                    $this->context->controller->errors[] = $this->trans(
                        'A valid name required for feature. %1$s in %2$s.',
                        [
                            ' <b>' . $feature['name'] . '</b>',
                            $current_language->name,
                        ],
                        'Admin.Catalog.Notification'
                    );
                }
                if (count($this->context->controller->errors)) {
                    return 0;
                }
                // Getting default language
                if ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT')) {
                    return $val;
                }
            }
        }

        return 0;
    }

    public function hookActionCombinationFormFormBuilderModifier($params)
    {
        if (Feature::isFeatureActive()) {
            $params['form_builder']->add('ffc', PrestaShopBundle\Form\Admin\Sell\Product\Details\FeaturesType::class);
            $params['data']['ffc'] = ['feature_values' => $this->getFeaturesForForm($params['data']['product_id'], $params['data']['id'])];
            $params['form_builder']->setData($params['data']);
        }
    }

    public function hookActionAfterUpdateCombinationFormFormHandler($params)
    {
        $ffc_form = $params['form_data']['ffc'];
        $id_product_attribute = (int) $params['form_data']['id'];
        $id_product = (int) $params['form_data']['product_id'];
        $ffc = new FFC($id_product);

        $languages = Language::getLanguages();

        $ffc->deleteCombinationsFeatures($id_product_attribute);
        if (array_key_exists('feature_values', $ffc_form)) {
            foreach ($ffc_form['feature_values'] as $feature) {
                if ($defaultValue = $this->checkFeatures($languages, $feature['custom_value'], $feature['feature_id'])) {
                    $idValue = $ffc->addCombinationsFeaturesToDB(
                        $id_product_attribute,
                        $feature['feature_id'],
                        0,
                        1
                    );
                    foreach ($languages as $language) {
                        $valueToAdd = (isset($feature['custom_value'][$language['id_lang']])
                            && !empty($feature['custom_value'][$language['id_lang']]))
                            ? $feature['custom_value'][$language['id_lang']]
                            : $defaultValue;

                        $ffc->addFeaturesCustomToDB($idValue, (int) $language['id_lang'], $valueToAdd);
                    }
                } elseif (!empty($feature['feature_value_id'])) {
                    $ffc->addCombinationsFeaturesToDB(
                        $id_product_attribute,
                        $feature['feature_id'],
                        $feature['feature_value_id']
                    );
                }
            }
        }

        return true;
    }

    public function isUsingNewTranslationSystem()
    {
        return version_compare(_PS_VERSION_, '1.7.8', '>=');
    }

    protected function trans($id, array $parameters = [], $domain = 'Modules.Featuresforcombinations.Config', $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            return parent::trans($id, $parameters, $domain, $locale);
        } else {
            return $this->l($id);
        }
    }
}
