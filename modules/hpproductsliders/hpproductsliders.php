<?php

/**
 * 2007-2026 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Hpproductsliders extends Module {

    protected $config_form = false;

    public function __construct() {
        $this->name = 'hpproductsliders';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'sifatusafwa.com';
        $this->need_instance = 0;
        $this->table = 'hpproductsliders';
        $this->bootstrap = true;
        $this->secret = 'SDKHF2323Ds';

        parent::__construct();

        $this->displayName = $this->l('HP product sliders');
        $this->description = $this->l('Shows product sliders on homepage');

        $this->ps_versions_compliancy = array('min' => '8.2', 'max' => '9.0');
    }

    public function install() {
        Configuration::updateValue('HPPRODUCTSLIDERS_LIVE_MODE', false);

        return parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall() {
        Configuration::deleteByName('HPPRODUCTSLIDERS_LIVE_MODE');

        return parent::uninstall();
    }

    public function getContent() {

        if (((bool) Tools::isSubmit('submitHpproductslidersModule')) == true) {
            $this->postProcess();
        }

        if ((bool) Tools::isSubmit('deletehpproductsliders') == true) {
            $this->deleteHpSlider();
        }

        if ((bool) Tools::isSubmit('statushpproductsliders') == true) {
            $this->updateStatusHpSlider();
        }

        if ((int) Tools::getValue('id_hpproductslider')) {
            return $this->renderSlidersForm();
        }

        return $this->renderHpSlidersList();
    }

    protected function renderHpSlidersList() {
        $hpSliders = $this->getHpSliders();

        $fields_list = [
            'id_hpproductslider' => [
                'title' => $this->trans('ID', [], 'Modules.Hpproductsliders.Admin'),
                'type' => 'text',
                'filter_key' => 'a!id_hpproductslider',
                'class' => 'hpproductslider-id',
            ],
            'title' => [
                'title' => $this->trans('Slider title', [], 'Modules.Hpproductsliders.Admin'),
                'type' => 'text',
                'class' => 'hpproductslider-title',
            ],
            'page' => [
                'title' => $this->trans('Source of products', [], 'Modules.Hpproductsliders.Admin'),
                'type' => 'text',
                'class' => 'hpproductslider-page',
            ],
//            'position' => [
//                'title' => $this->trans('Position', [], 'Modules.Hpproductsliders.Admin'),
//                'filter_key' => 'a!position',
//                'position' => 'position',
//                'class' => 'hpproductslider-position',
//            ],
            'active' => [
                'title' => $this->trans('Status', [], 'Modules.Hpproductsliders.Admin'),
                'active' => 'status',
                'type' => 'bool',
            ],
        ];

        $lastIdHpProductSlider = (int) Db::getInstance()->getValue(
                        'SELECT COALESCE(MAX(id_hpproductslider), 0) + 1 AS next_id
                FROM ' . _DB_PREFIX_ . 'hpproductsliders;'
                );

        $helper = new HelperList();
        $helper->list_id = 'hpproductsliders-list';
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = ['edit', 'delete'];
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->identifier = 'id_hpproductslider';
        $helper->title = $this->trans('HP product sliders', [], 'Modules.Hpproductsliders.Admin');
        $helper->table = $this->name;
//        $helper->table_id = 'approved-productcomments-list';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->no_link = true;
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name, 'id_hpproductslider' => $lastIdHpProductSlider]),
            'desc' => $this->trans('Add New HP Slider', [], 'Modules.Hpproductsliders.Admin'),
        ];
        $helper->listTotal = count($hpSliders);

        return $helper->generateList($hpSliders, $fields_list);
    }

    public function getHpSliders() {

        $sql = 'SELECT hps.*, hpsl.`title` FROM `' . _DB_PREFIX_ . 'hpproductsliders` hps '
                . 'LEFT JOIN `' . _DB_PREFIX_ . 'hpproductsliders_lang` hpsl '
                . 'ON (hps.id_hpproductslider = hpsl.id_hpproductslider AND `id_lang` = ' . $this->context->language->id . ') ';

        $results = Db::getInstance()->executeS($sql);

        return $results;
    }

    protected function renderSlidersForm() {
        $helper = new HelperForm();

        $helper->show_toolbar = false;

        $helper->id_form = 'hpproductsliders-form';
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->show_cancel_button = true;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHpproductslidersModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getHpSlider(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm() {
        $selectedCategory = false;

        $sql = 'SELECT `id_category` '
                . 'FROM ' . _DB_PREFIX_ . 'hpproductsliders '
                . 'WHERE `id_hpproductslider` = ' . Tools::getValue('id_hpproductslider');

        $idCategory = Db::getInstance()->getValue($sql);

        if (!empty($idCategory)) {
            $selectedCategory = $idCategory;
        }

        $sql = 'SELECT p.id_product, 
                CONCAT(p.reference, " ", pl.name) AS product_name 
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                WHERE pl.id_lang = ' . (int) Context::getContext()->language->id . ' 
                AND p.active = 1 AND pss.active = 1
            ORDER BY p.id_product DESC';

        $products = Db::getInstance()->executeS($sql);

        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id_product' => $product['id_product'],
                'name' => $product['product_name']
            ];
        }

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('HP product slider', [], 'Modules.Hpproductsliders.Admin'),
                    'icon' => 'icon-cogs',
                ),
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_hpproductslider',
                        'required' => false,
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'name' => 'title',
                        'label' => $this->trans('Slider Title', [], 'Modules.Hpproductsliders.Admin'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Number of products', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'products_number',
                    ],
//                    [
//                        'type' => 'switch',
//                        'label' => $this->trans('Random', [], 'Modules.Hpproductsliders.Admin'),
//                        'name' => 'random',
//                        'values' => [
//                            [
//                                'id' => 'active_on',
//                                'value' => 1,
//                                'label' => $this->trans('Yes', [], 'Modules.Hpproductsliders.Admin'),
//                            ],
//                            [
//                                'id' => 'active_off',
//                                'value' => 0,
//                                'label' => $this->trans('No', [], 'Modules.Hpproductsliders.Admin'),
//                            ],
//                        ],
//                    ],
                    [
                        'label' => $this->trans('Product source', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'page',
                        'type' => 'select',
                        'options' => [
                            'query' => [
                                ['page' => 'category', 'name' => $this->trans('Category', [], 'Modules.Hpproductsliders.Admin')],
                                ['page' => 'brands', 'name' => $this->trans('Brands (Authors)', [], 'Modules.Hpproductsliders.Admin')],
                                ['page' => 'new', 'name' => $this->trans('New products', [], 'Modules.Hpproductsliders.Admin')],
                                ['page' => 'sales', 'name' => $this->trans('Sales', [], 'Modules.Hpproductsliders.Admin')],
                            ],
                            'id' => 'page',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->trans('Categories', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'id_category',
                        'form_group_class' => 'id_category',
                        'tree' => [
                            'id' => 'categories-tree',
                            'use_search' => true,
                            'use_checkbox' => false,
                            'selected_categories' => [$selectedCategory], // Pre-select category
                        ],
                    ],
                    [
                        'label' => $this->trans('Brands (Authors)', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'id_manufacturer',
                        'class' => 'chosen',
                        'form_group_class' => 'id_manufacturer',
                        'type' => 'select',
                        'options' => [
                            'query' => $this->getBrands(),
                            'id' => 'id_manufacturer',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'label' => $this->trans('Select a product', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'productIds_choose', // Change name
                        'id' => 'productIds_choose',
                        'multiple' => false,
                        'options' => [
                            'query' => $formattedProducts, // Use formatted products
                            'id' => 'id_product',
                            'name' => 'name'
                        ],
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->trans('Product list', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'products',
                        'required' => false,
                        'html_content' => $this->getProductListHtml()
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_products',
                        'required' => false,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Active', [], 'Modules.Hpproductsliders.Admin'),
                        'name' => 'active',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', [], 'Modules.Hpproductsliders.Admin'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', [], 'Modules.Hpproductsliders.Admin'),
                            ],
                        ],
                    ],
                ],
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getProductListHtml() {
        $sql = 'SELECT `id_products` FROM `' . _DB_PREFIX_ . 'hpproductsliders` '
                . 'WHERE `id_hpproductslider` = ' . (int) Tools::getValue('id_hpproductslider');

        $idProducts = Db::getInstance()->getValue($sql);
        $idProducts = json_decode($idProducts, true);

        $products = [];

        foreach ($idProducts as $id) {
            $product = new Product($id, false, $this->context->language->id);
            $products[] = [
                'id_product' => $product->id,
                'name' => $product->reference . ' ' . $product->meta_title,
            ];
        }

        $this->context->smarty->assign([
            'secret' => $this->secret,
            'hproductslidersajax' => $this->context->link->getModuleLink(
                    'hpproductsliders', 'ajax',
            ),
            'products' => $products
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
//        return '<table></table>';
    }

    protected function getBrands() {
        return Manufacturer::getManufacturers($idLang = $this->context->language->id);
    }

    public function getHpSlider() {
        $result = Db::getInstance()->getRow(
                'SELECT hps.* FROM `' . _DB_PREFIX_ . 'hpproductsliders` hps
                WHERE hps.id_hpproductslider = ' . (int) Tools::getValue('id_hpproductslider')
        );

        if (empty($result)) {

            return [
                'id_hpproductslider' => (int) Tools::getValue('id_hpproductslider'),
                'position' => '',
                'page' => 'category',
                'id_products' => '',
                'title' => [],
                'id_category' => [],
                'active' => true,
                'random' => true,
                'products' => '',
                'id_products' => '',
            ];
        }

        $title = [];

        $languages = Language::getLanguages();

        foreach ($languages as $key => $value) {

            $meta_title = Db::getInstance()->getValue(
                    'SELECT `title` FROM `' . _DB_PREFIX_ . 'hpproductsliders_lang` '
                    . 'WHERE `id_hpproductslider` = ' . (int) Tools::getValue('id_hpproductslider') . ' '
                    . 'AND `id_lang` = ' . $value['id_lang']
            );

            $title[$value['id_lang']] = $meta_title;
        }

        $result['title'] = $title;

        return $result;
    }

    protected function postProcess() {
        $sql = 'SELECT `id_hpproductslider` '
                . 'FROM ' . _DB_PREFIX_ . 'hpproductsliders '
                . 'WHERE `id_hpproductslider` = ' . Tools::getValue('id_hpproductslider');

        $id = $products = Db::getInstance()->getValue($sql);

        $data['id_hpproductslider'] = Tools::getValue('id_hpproductslider');
        $data['page'] = Tools::getValue('page');
        $data['id_manufacturer'] = Tools::getValue('id_manufacturer');
        $data['id_products'] = Tools::getValue('id_products');
        $data['active'] = Tools::getValue('active');
        $data['products_number'] = Tools::getValue('products_number');
        $data['id_category'] = Tools::getValue('id_category');

        if ($id > 0) {
            Db::getInstance()->update('hpproductsliders', $data, '`id_hpproductslider` = ' . Tools::getValue('id_hpproductslider'));

            $languages = Language::getLanguages();

            foreach ($languages as $key => $value) {

                $data = [];
                $data['id_hpproductslider'] = Tools::getValue('id_hpproductslider');
                $data['id_lang'] = $value['id_lang'];
                $data['title'] = Tools::getValue('title_' . $value['id_lang']);

                Db::getInstance()->update(
                        'hpproductsliders_lang',
                        $data,
                        '`id_hpproductslider` = ' . Tools::getValue('id_hpproductslider') . ' '
                        . 'AND `id_lang` = ' . $value['id_lang']
                );
            }
        } else {

            Db::getInstance()->insert('hpproductsliders', $data);

            $languages = Language::getLanguages();

            foreach ($languages as $key => $value) {

                $data = [];
                $data['id_hpproductslider'] = Tools::getValue('id_hpproductslider');
                $data['id_lang'] = $value['id_lang'];
                $data['title'] = Tools::getValue('title_' . $value['id_lang']);

                Db::getInstance()->insert('hpproductsliders_lang', $data);
            }
        }
    }

    public function deleteHpSlider() {
        $id = Tools::getValue('id_hpproductslider');

        $sql = [];
        $sql[] = 'DELETE FROM `' . _DB_PREFIX_ . 'hpproductsliders_lang` WHERE  `id_hpproductslider` = ' . $id;
        $sql[] = 'DELETE FROM `' . _DB_PREFIX_ . 'hpproductsliders` WHERE  `id_hpproductslider` = ' . $id;

        foreach ($sql as $s) {
            Configuration::updateValue('asd' . rand(), $sql);
            Db::getInstance()->execute($s);
        }

        $link = $this->context->link->getAdminLink('AdminModules', true)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        Tools::redirectAdmin($link);
    }

    public function updateStatusHpSlider() {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'hpproductsliders`
                SET active = 1 - active
                WHERE id_hpproductslider = ' . Tools::getValue('id_hpproductslider');
        
        Db::getInstance()->execute($sql);

        $link = $this->context->link->getAdminLink('AdminModules', true)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        Tools::redirectAdmin($link);
    }

    public function hookDisplayBackOfficeHeader() {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    public function hookHeader() {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }
}
