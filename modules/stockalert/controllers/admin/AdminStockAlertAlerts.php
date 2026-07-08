<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminStockAlertAlertsController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'date_add';
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'stockalert_alert';
        $this->className = 'StockAlertAlert';
        $this->tabClassName = 'AdminStockAlertAlerts';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->_orderWay = $this->_defaultOrderWay;

        parent::__construct();

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $this->fields_list = [
            'id_stockalert_alert' => [
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                // 'callback' => 'printActiveIcon'
            ],
            'name' => [
                'title' => $this->l('Name'),
            ],
            'priority' => [
                'title' => $this->l('Priority'),
                'align' => 'text-center',
            ],
            'popup' => [
                'title' => $this->l('Popup'),
                'align' => 'text-center',
                'active' => 'popup',
                'type' => 'bool',
            ],
            'stock' => [
                'title' => $this->l('Stock threshold'),
                'align' => 'text-center',
            ],
            'out_of_stock' => [
                'title' => $this->l('Display if product allow "Out of stock" orders'),
                'align' => 'text-center',
                'active' => 'out_of_stock',
                'type' => 'bool',
            ],
            'send_mail' => [
                'title' => $this->l('Send email to customer to subscribers when product is in stock again'),
                'align' => 'text-center',
                'active' => 'send_mail',
                'type' => 'bool',
            ],
            'stock_over' => [
                'title' => $this->l('Send email to customer when stock is over this value'),
                'align' => 'text-center',
                'callback' => 'stockOverCallback',
            ],
        ];

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        if (!Shop::isFeatureActive()) {
            $this->shopLinkType = '';
        } else {
            $this->shopLinkType = 'shop';
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if ($this->display) {
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/stockalert-admin.js');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js');
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/tabs.css');
            }
        }

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/stockalert-admin.css');
    }

    public function initContent()
    {
        if (!$this->isShopSelected && !$this->display) {
            $this->informations[] = $this->l('You have to select a shop.');
        }

        $warningError = '';
        if ($warnings = $this->module->getWarnings(false)) {
            $warningError = $this->module->displayError($warnings);
        }

        parent::initContent();

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->smarty->assign([
                    'this_path' => $this->module->getPathUri(),
                    'support_id' => $this->module->addons_id_product,
                ]);

                $available_iso_codes = ['en', 'es'];
                $default_iso_code = 'en';
                $template_iso_suffix = in_array($this->context->language->iso_code, $available_iso_codes) ? $this->context->language->iso_code : $default_iso_code;
                $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/company/information_' . $template_iso_suffix . '.tpl');
            }
        }

        $this->context->smarty->assign([
            'content' => $warningError . $this->content,
            'token' => $this->token,
        ]);
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['desc-module-new'] = [
                'href' => 'index.php?controller=' . $this->tabClassName . '&add' . $this->table . '&token=' . Tools::getAdminTokenLite($this->tabClassName),
                'desc' => $this->l('New'),
                'icon' => 'process-icon-new',
            ];
        }

        if (!$this->isShopSelected) {
            unset($this->page_header_toolbar_btn['desc-module-new']);
        }

        $this->context->smarty->clearAssign('help_link', '');
    }

    public function initProcess()
    {
        parent::initProcess();

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (Tools::isSubmit('out_of_stock' . $this->table) && $this->id_object) {
                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'toggle_out_of_stock';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }
            } elseif (Tools::isSubmit('send_mail' . $this->table) && $this->id_object) {
                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'toggle_send_mail';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }
            } elseif (Tools::isSubmit('popup' . $this->table) && $this->id_object) {
                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'toggle_popup';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }
            }
        } else {
            if (Tools::isSubmit('out_of_stock' . $this->table) && $this->id_object) {
                if ($this->access('edit')) {
                    $this->action = 'toggle_out_of_stock';
                }
            } elseif (Tools::isSubmit('send_mail' . $this->table) && $this->id_object) {
                if ($this->access('edit')) {
                    $this->action = 'toggle_send_mail';
                }
            } elseif (Tools::isSubmit('popup' . $this->table) && $this->id_object) {
                if ($this->access('edit')) {
                    $this->action = 'toggle_popup';
                }
            }
        }
    }

    public function processToggleOutOfStock()
    {
        $object = new $this->className($this->id_object);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        $object->out_of_stock = $object->out_of_stock ? 0 : 1;
        if (!$object->update()) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processToggleSendMail()
    {
        $object = new $this->className($this->id_object);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        $object->send_mail = $object->send_mail ? 0 : 1;
        if (!$object->update()) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processTogglePopup()
    {
        $object = new $this->className($this->id_object);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        $object->popup = $object->popup ? 0 : 1;
        if (!$object->update()) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function renderList()
    {
        // Redirect if no object created
        if ($this->isShopSelected && !StockAlertAlert::getNbObjects()) {
            $this->redirect_after = 'index.php?controller=' . $this->tabClassName . '&add' . $this->table . '&token=' . Tools::getAdminTokenLite($this->tabClassName);
            $this->redirect();
        }

        return parent::renderList();
    }

    public function renderForm()
    {
        if (!$this->isShopSelected && $this->display === 'add') {
            $this->errors[] = $this->l('You have to select a shop.');

            return false;
        }

        $object = $this->loadObject(true);

        $this->multiple_fieldsets = true;

        $fieldsFormIndex = 0;
        $this->fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Stock alert'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'col' => '4',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Priority'),
                    'name' => 'priority',
                    'col' => '1',
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Custom text block'),
                    'name' => 'custom_text',
                    'lang' => true,
                    'autoload_rte' => true,
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display stock subscription form as a popup'),
                    'name' => 'popup',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'popup_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'popup_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
            ],
            'buttons' => [
                'save-and-stay' => [
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd' . $this->table . 'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            array_push(
                $this->fields_form[$fieldsFormIndex]['form']['input'],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display a button in the product list - category page'),
                    'name' => 'product_list',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'product_list_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'product_list_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display a button in the product list - product page'),
                    'name' => 'product_list_product',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'product_list_product_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'product_list_product_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ]
            );
        }

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            [
                'type' => 'text',
                'label' => $this->l('Allow subscription if product stock is in this stock or below'),
                'name' => 'stock',
                'required' => true,
                'col' => '3',
                'prefix' => '<=',
                'desc' => $this->l('Quantity for which a product is considered out of stock.'),
            ],
            [
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Allow subscription if product allows "Out of stock" orders'),
                'name' => 'out_of_stock',
                'class' => 't',
                'col' => 8,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'out_of_stock_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'out_of_stock_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Send an email to the admin when a stock alert is created'),
                'name' => 'send_mail_admin',
                'class' => 't',
                'col' => 8,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'send_mail_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'send_mail_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Send an email to these mail addresses'),
                'name' => 'send_mail_admin_addresses',
                'class' => 'send_mail_admin',
                'required' => true,
                'col' => '6',
                'desc' => $this->l('Separate the email addresses with ;'),
            ],
            [
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Send an email to the subscribers when product is in stock again'),
                'name' => 'send_mail',
                'class' => 't',
                'col' => 8,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'send_mail_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'send_mail_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Send an email to the subscribers when stock is over this value'),
                'name' => 'stock_over',
                'class' => 'send_mail',
                'required' => true,
                'col' => '3',
                'prefix' => '>',
            ]
        );

        ++$fieldsFormIndex;
        $this->fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Which products can display subscribe form'),
                'icon' => 'icon-edit',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<div class="alert alert-info">' . $this->l('If you don\'t enable any filter, the stock alert will be displayed for all the products') . '</div>',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
            ],
            'buttons' => [
                'save-and-stay' => [
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd' . $this->table . 'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        if (!Configuration::get('SA_HIDE_FILTER_CATEGORY')) {
            $categories = Category::getCategories((int) $this->context->language->id, false, false, 'AND c.`level_depth` > 1', 'ORDER BY cl.`name` ASC');
            foreach ($categories as &$category) {
                $category['display_name'] = $category['name'] . ' - ' . $category['name'] . ' (ID: ' . $category['id_category'] . ')';
            }
            unset($category);

            array_push(
                $this->fields_form[$fieldsFormIndex]['form']['input'],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Category')),
                    'name' => 'switch_categories',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'switch_categories_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'switch_categories_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ]
            );

            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                if (version_compare(_PS_VERSION_, '9.0.0', '<')) {
                    $selected_categories = [];
                    if ($object->categories != '') {
                        if (@json_decode($object->categories) !== false) {
                            $selected_categories = json_decode($object->categories);
                        } else {
                            $selected_categories = explode(',', $object->categories);
                        }
                    }

                    $selected_categories_excluded = [];
                    if ($object->categories_excluded != '') {
                        if (@json_decode($object->categories_excluded) !== false) {
                            $selected_categories_excluded = json_decode($object->categories_excluded);
                        } else {
                            $selected_categories_excluded = explode(',', $object->categories_excluded);
                        }
                    }

                    $categoriesSelectedTreeHelper = new HelperTreeCategories('switch_categories');
                    $categoriesSelectedTreeHelper->setRootCategory(Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0)
                        ->setUseCheckBox(true)
                        ->setSelectedCategories($selected_categories)
                        ->setInputName('categories');

                    $categoriesExcludedTreeHelper = new HelperTreeCategories('switch_categories_excluded');
                    $categoriesExcludedTreeHelper->setRootCategory(Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0)
                        ->setUseCheckBox(true)
                        ->setSelectedCategories($selected_categories_excluded)
                        ->setInputName('categories_excluded');

                    array_push(
                        $this->fields_form[$fieldsFormIndex]['form']['input'],
                        [
                            'type' => 'html',
                            'name' => 'free',
                            'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Categories')),
                            'html_content' => $categoriesSelectedTreeHelper->render(),
                        ],
                        [
                            'type' => 'html',
                            'name' => 'free',
                            'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Categories')),
                            'html_content' => $categoriesExcludedTreeHelper->render(),
                        ]
                    );
                }
            } else {
                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'class' => 'switch_categories',
                        'name' => 'categories[]',
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Categories')),
                        // 'sort' => 'name',
                        'search' => true,
                        'options' => [
                            'query' => $categories,
                            'id' => 'id_category',
                            'name' => 'display_name',
                        ],
                        'desc' => $this->l('Select the product categories where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                    ]
                );
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_ATTRIBUTE')) {
            if (Combination::isFeatureActive()) {
                $attributes = self::getAttributes((int) $this->context->language->id);
                if ($attributes) {
                    foreach ($attributes as &$attribute) {
                        $attribute['display_name'] = $attribute['public_name'] . ' - ' . $attribute['name'] . ' (ID: ' . $attribute['id_attribute'] . ')';
                    }
                    unset($attribute);

                    array_unshift(
                        $attributes,
                        [
                            'id_attribute' => PHP_INT_MAX,
                            'display_name' => sprintf($this->l('- Product without %1$s - '), $this->l('Attributes')),
                        ]
                    );

                    if (count($attributes)) {
                        array_push(
                            $this->fields_form[$fieldsFormIndex]['form']['input'],
                            [
                                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                                'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Attribute')),
                                'name' => 'switch_attributes',
                                'class' => 't',
                                'col' => '1',
                                'is_bool' => true,
                                'values' => [
                                    [
                                        'id' => 'switch_attribute_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ],
                                    [
                                        'id' => 'switch_attribute_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ],
                                ],
                            ],
                            [
                                'type' => 'swap-custom',
                                'col' => 8,
                                'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Attributes')),
                                'name' => 'attributes[]',
                                'class' => 'switch_attributes',
                                'search' => true,
                                'sort' => 'display_name',
                                'options' => [
                                    'query' => $attributes,
                                    'id' => 'id_attribute',
                                    'name' => 'display_name',
                                ],
                                'desc' => $this->l('Select the products where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                            ],
                            [
                                'type' => 'swap-custom',
                                'col' => 8,
                                'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Attributes')),
                                'name' => 'attributes_excluded[]',
                                'class' => 'switch_attributes',
                                'search' => true,
                                'sort' => 'display_name',
                                'options' => [
                                    'query' => $attributes,
                                    'id' => 'id_attribute',
                                    'name' => 'display_name',
                                ],
                                'desc' => $this->l('Select the products where the subscription form will not be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                            ]
                        );
                    }
                }
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_FEATURE')) {
            if (Feature::isFeatureActive()) {
                $features = [];
                $featureGroups = Feature::getFeatures((int) $this->context->language->id);
                foreach ($featureGroups as $featureGroup) {
                    $featuresValue = FeatureValue::getFeatureValuesWithLang((int) $this->context->language->id, $featureGroup['id_feature']);
                    foreach ($featuresValue as $featureValue) {
                        $featureValue['display_name'] = $featureGroup['name'] . ' - ' . $featureValue['value'] . ' (ID: ' . $featureValue['id_feature_value'] . ')';
                        array_push($features, $featureValue);
                    }
                }

                array_unshift(
                    $features,
                    [
                        'id_feature_value' => PHP_INT_MAX,
                        'display_name' => sprintf($this->l('- Product without %1$s - '), $this->l('Features')),
                    ]
                );

                if (count($features)) {
                    array_push(
                        $this->fields_form[$fieldsFormIndex]['form']['input'],
                        [
                            'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                            'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Feature')),
                            'name' => 'switch_features',
                            'class' => 't',
                            'col' => '1',
                            'is_bool' => true,
                            'values' => [
                                [
                                    'id' => 'switch_features_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled'),
                                ],
                                [
                                    'id' => 'switch_features_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled'),
                                ],
                            ],
                        ],
                        [
                            'type' => 'swap-custom',
                            'col' => 8,
                            'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Features')),
                            'name' => 'features[]',
                            'class' => 'switch_features',
                            'search' => true,
                            'sort' => 'display_name',
                            'options' => [
                                'query' => $features,
                                'id' => 'id_feature_value',
                                'name' => 'display_name',
                            ],
                            'desc' => $this->l('Select the features where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                        ],
                        [
                            'type' => 'swap-custom',
                            'col' => 8,
                            'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Features')),
                            'name' => 'features_excluded[]',
                            'class' => 'switch_features',
                            'search' => true,
                            'sort' => 'display_name',
                            'options' => [
                                'query' => $features,
                                'id' => 'id_feature_value',
                                'name' => 'display_name',
                            ],
                            'desc' => $this->l('Select the features where the subscription form will not be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                        ]
                    );
                }
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_PRODUCT')) {
            $products = $this->getProductsLite((int) $this->context->language->id, false, false, 'p.`id_product`');
            if ($products) {
                foreach ($products as &$product) {
                    $product['name'] = 'ID ' . $product['id_product'] . ' - ' . $product['name'] . ' - ' . $product['reference'];
                }
                unset($product);

                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Product')),
                        'name' => 'switch_products',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_products_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'switch_products_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Products')),
                        'name' => 'products[]',
                        'class' => 'switch_products',
                        'search' => true,
                        'sort' => 'id_product',
                        'options' => [
                            'query' => $products,
                            'id' => 'id_product',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select the products where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Products')),
                        'name' => 'products_excluded[]',
                        'class' => 'switch_products',
                        'search' => true,
                        'sort' => 'id_product',
                        'options' => [
                            'query' => $products,
                            'id' => 'id_product',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select the products where the subscription form will not be displayed. If you don\'t select any value, the subscription form will be displayed in all products'),
                    ]
                );
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_MANUFACTURER')) {
            $manufacturers = Manufacturer::getManufacturers(false, (int) $this->context->language->id, false);
            if ($manufacturers) {
                foreach ($manufacturers as &$manufacturer) {
                    $manufacturer['display_name'] = 'ID ' . $manufacturer['id_manufacturer'] . ' - ' . $manufacturer['name'];
                }
                unset($manufacturer);

                array_unshift(
                    $manufacturers,
                    [
                        'id_manufacturer' => PHP_INT_MAX,
                        'display_name' => sprintf($this->l('- Product without %1$s - '), $this->l('Manufacturer')),
                    ]
                );

                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Manufacturer')),
                        'name' => 'switch_manufacturers',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_manufacturers_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'switch_manufacturers_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Manufacturers')),
                        'name' => 'manufacturers[]',
                        'class' => 'switch_manufacturers',
                        'search' => true,
                        'sort' => 'name',
                        'options' => [
                            'query' => $manufacturers,
                            'id' => 'id_manufacturer',
                            'name' => 'display_name',
                        ],
                        'desc' => $this->l('Select the manufacturers where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all manufacturers'),
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Manufacturers')),
                        'name' => 'manufacturers_excluded[]',
                        'class' => 'switch_manufacturers',
                        'search' => true,
                        'sort' => 'name',
                        'options' => [
                            'query' => $manufacturers,
                            'id' => 'id_manufacturer',
                            'name' => 'display_name',
                        ],
                        'desc' => $this->l('Select the manufacturers where the subscription form will not be displayed. If you don\'t select any value, the subscription form will be displayed in all manufacturers'),
                    ]
                );
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_SUPPLIER')) {
            $suppliers = Supplier::getSuppliers(false, (int) $this->context->language->id, false);
            if ($suppliers) {
                foreach ($suppliers as &$supplier) {
                    $supplier['name'] = 'ID ' . $supplier['id_supplier'] . ' - ' . $supplier['name'];
                }
                unset($supplier);

                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Supplier')),
                        'name' => 'switch_suppliers',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_suppliers_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'switch_suppliers_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Suppliers')),
                        'name' => 'suppliers[]',
                        'class' => 'switch_suppliers',
                        'search' => true,
                        'sort' => 'name',
                        'options' => [
                            'query' => $suppliers,
                            'id' => 'id_supplier',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select the suppliers where the subscription form will be displayed. If you don\'t select any value, the subscription form will be displayed in all suppliers'),
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Suppliers')),
                        'name' => 'suppliers_excluded[]',
                        'class' => 'switch_suppliers',
                        'search' => true,
                        'sort' => 'name',
                        'options' => [
                            'query' => $suppliers,
                            'id' => 'id_supplier',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Select the suppliers where the subscription form will not be displayed. If you don\'t select any value, the subscription form will be displayed in all suppliers'),
                    ]
                );
            }
        }

        ++$fieldsFormIndex;
        $this->fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Who can subscribe to stock alerts'),
                'icon' => 'icon-globe',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<div class="alert alert-info">' . $this->l('If you don\'t enable any filter, the stock alert will be displayed for all the customers') . '</div>',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
            ],
            'buttons' => [
                'save-and-stay' => [
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd' . $this->table . 'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        if (!Configuration::get('SA_HIDE_FILTER_CUSTOMER_GROUP')) {
            if (Group::isFeatureActive()) {
                $groups = Group::getGroups((int) $this->context->language->id, true);
                foreach ($groups as &$group) {
                    $group['name'] = 'ID ' . $group['id_group'] . ' - ' . $group['name'];
                }
                unset($group);

                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Customer group')),
                        'name' => 'switch_groups',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_groups_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'switch_groups_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Customer groups')),
                        'name' => 'groups[]',
                        'class' => 'switch_groups',
                        'sort' => 'name',
                        'search' => true,
                        'options' => [
                            'query' => $groups,
                            'id' => 'id_group',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Customer groups')),
                        'name' => 'groups_excluded[]',
                        'class' => 'switch_groups',
                        'sort' => 'name',
                        'search' => true,
                        'options' => [
                            'query' => $groups,
                            'id' => 'id_group',
                            'name' => 'name',
                        ],
                    ]
                );
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_CUSTOMER')) {
            $customers = Customer::getCustomers();
            if ($customers) {
                foreach ($customers as &$customer) {
                    $customer['name'] = 'ID ' . $customer['id_customer'] . ' - ' . $customer['firstname'] . ' ' . $customer['lastname'] . ' - ' . $customer['email'];
                }
                unset($customer);

                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Customer')),
                        'name' => 'switch_customers',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_customers_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'switch_customers_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Customers')),
                        'name' => 'customers[]',
                        'class' => 'switch_customers',
                        'sort' => 'id_customer',
                        'search' => true,
                        'options' => [
                            'query' => $customers,
                            'id' => 'id_customer',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'swap-custom',
                        'col' => 8,
                        'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Customers')),
                        'name' => 'customers_excluded[]',
                        'class' => 'switch_customers',
                        'sort' => 'id_customer',
                        'search' => true,
                        'options' => [
                            'query' => $customers,
                            'id' => 'id_customer',
                            'name' => 'name',
                        ],
                    ]
                );
            }
        }

        if (!Configuration::get('SA_HIDE_FILTER_COUNTRY')) {
            array_push(
                $this->fields_form[$fieldsFormIndex]['form']['input'],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Country')),
                    'name' => 'switch_countries',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'switch_countries_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'switch_countries_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'swap-custom',
                    'col' => 8,
                    'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Countries')),
                    'name' => 'countries[]',
                    'class' => 'switch_countries',
                    'search' => true,
                    'options' => [
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'swap-custom',
                    'col' => 8,
                    'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Countries')),
                    'name' => 'countries_excluded[]',
                    'class' => 'switch_countries',
                    'search' => true,
                    'options' => [
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name',
                    ],
                ]
            );
        }

        if (!Configuration::get('SA_HIDE_FILTER_ZONE')) {
            array_push(
                $this->fields_form[$fieldsFormIndex]['form']['input'],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf('%1$s %2$s', $this->l('Filter by'), $this->l('Zone')),
                    'name' => 'switch_zones',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'switch_zones_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'switch_zones_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'swap-custom',
                    'col' => 8,
                    'label' => sprintf('%1$s %2$s', $this->l('Include'), $this->l('Zones')),
                    'name' => 'zones[]',
                    'class' => 'switch_zones',
                    'search' => true,
                    'options' => [
                        'query' => Zone::getZones(),
                        'id' => 'id_zone',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'swap-custom',
                    'col' => 8,
                    'label' => sprintf('%1$s %2$s', $this->l('Exclude'), $this->l('Zones')),
                    'name' => 'zones_excluded[]',
                    'class' => 'switch_zones',
                    'search' => true,
                    'options' => [
                        'query' => Zone::getZones(),
                        'id' => 'id_zone',
                        'name' => 'name',
                    ],
                ]
            );
        }

        if ($object->id) {
            $this->fields_value = [
                'customers[]' => explode(',', $object->customers),
                'customers_excluded[]' => explode(',', $object->customers_excluded),
                'groups[]' => explode(',', $object->groups),
                'groups_excluded[]' => explode(',', $object->groups_excluded),
                'countries[]' => explode(',', $object->countries),
                'countries_excluded[]' => explode(',', $object->countries_excluded),
                'zones[]' => explode(',', $object->zones),
                'zones_excluded[]' => explode(',', $object->zones_excluded),
                'manufacturers[]' => explode(',', $object->manufacturers),
                'manufacturers_excluded[]' => explode(',', $object->manufacturers_excluded),
                'suppliers[]' => explode(',', $object->suppliers),
                'suppliers_excluded[]' => explode(',', $object->suppliers_excluded),
                'products[]' => explode(',', $object->products),
                'products_excluded[]' => explode(',', $object->products_excluded),
                'categories[]' => explode(',', $object->categories),
                'categories_excluded[]' => explode(',', $object->categories_excluded),
                'attributes[]' => explode(',', $object->attributes),
                'attributes_excluded[]' => explode(',', $object->attributes_excluded),
                'features[]' => explode(',', $object->features),
                'features_excluded[]' => explode(',', $object->features_excluded),
            ];
        } else {
            // Initialize empty values
            $this->fields_value['customers[]'] = [];
            $this->fields_value['customers_excluded[]'] = [];
            $this->fields_value['groups[]'] = [];
            $this->fields_value['groups_excluded[]'] = [];
            $this->fields_value['countries[]'] = [];
            $this->fields_value['countries_excluded[]'] = [];
            $this->fields_value['zones[]'] = [];
            $this->fields_value['zones_excluded[]'] = [];
            $this->fields_value['products[]'] = [];
            $this->fields_value['products_excluded[]'] = [];
            $this->fields_value['suppliers[]'] = [];
            $this->fields_value['suppliers_excluded[]'] = [];
            $this->fields_value['manufacturers[]'] = [];
            $this->fields_value['manufacturers_excluded[]'] = [];
            $this->fields_value['categories[]'] = [];
            $this->fields_value['categories_excluded[]'] = [];
            $this->fields_value['attributes[]'] = [];
            $this->fields_value['attributes_excluded[]'] = [];
            $this->fields_value['features[]'] = [];
            $this->fields_value['features_excluded[]'] = [];
        }

        $this->content .= parent::renderForm();
    }

    public function processSave()
    {
        if (Tools::getValue('send_mail_admin_addresses')) {
            $emails = explode(';', Tools::getValue('send_mail_admin_addresses'));

            foreach ($emails as $email) {
                if (!Validate::isEmail($email)) {
                    $this->errors[] = sprintf($this->l('"%s" is not a valid email address.'), $email);
                }
            }
        }

        if ($this->errors) {
            $this->display = 'edit';

            return false;
        }

        $_POST['products'] = (!Tools::getValue('products')) ? '' : implode(',', Tools::getValue('products'));
        $_POST['products_excluded'] = (!Tools::getValue('products_excluded')) ? '' : implode(',', Tools::getValue('products_excluded'));
        $_POST['manufacturers'] = (!Tools::getValue('manufacturers')) ? '' : implode(',', Tools::getValue('manufacturers'));
        $_POST['manufacturers_excluded'] = (!Tools::getValue('manufacturers_excluded')) ? '' : implode(',', Tools::getValue('manufacturers_excluded'));
        $_POST['suppliers'] = (!Tools::getValue('suppliers')) ? '' : implode(',', Tools::getValue('suppliers'));
        $_POST['suppliers_excluded'] = (!Tools::getValue('suppliers_excluded')) ? '' : implode(',', Tools::getValue('suppliers_excluded'));
        $_POST['customers'] = (!Tools::getValue('customers')) ? '' : implode(',', Tools::getValue('customers'));
        $_POST['customers_excluded'] = (!Tools::getValue('customers_excluded')) ? '' : implode(',', Tools::getValue('customers_excluded'));
        $_POST['groups'] = (!Tools::getValue('groups')) ? '' : implode(',', Tools::getValue('groups'));
        $_POST['groups_excluded'] = (!Tools::getValue('groups_excluded')) ? '' : implode(',', Tools::getValue('groups_excluded'));
        $_POST['countries'] = (!Tools::getValue('countries')) ? '' : implode(',', Tools::getValue('countries'));
        $_POST['countries_excluded'] = (!Tools::getValue('countries_excluded')) ? '' : implode(',', Tools::getValue('countries_excluded'));
        $_POST['zones'] = (!Tools::getValue('zones')) ? '' : implode(',', Tools::getValue('zones'));
        $_POST['zones_excluded'] = (!Tools::getValue('zones_excluded')) ? '' : implode(',', Tools::getValue('zones_excluded'));
        $_POST['attributes'] = (!Tools::getValue('attributes')) ? '' : implode(',', Tools::getValue('attributes'));
        $_POST['attributes_excluded'] = (!Tools::getValue('attributes_excluded')) ? '' : implode(',', Tools::getValue('attributes_excluded'));
        $_POST['features'] = (!Tools::getValue('features')) ? '' : implode(',', Tools::getValue('features'));
        $_POST['features_excluded'] = (!Tools::getValue('features_excluded')) ? '' : implode(',', Tools::getValue('features_excluded'));

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            if (Tools::isSubmit('categories')) {
                $cats = Tools::getValue('categories');
                $_POST['categories'] = json_encode($cats);
            } else {
                $_POST['categories'] = '';
            }

            if (Tools::isSubmit('categories_excluded')) {
                $cats = Tools::getValue('categories_excluded');
                $_POST['categories_excluded'] = json_encode($cats);
            } else {
                $_POST['categories_excluded'] = '';
            }
        } else {
            $_POST['categories'] = (!Tools::isSubmit('categories')) ? '' : implode(',', Tools::getValue('categories'));
            $_POST['categories_excluded'] = (!Tools::isSubmit('categories_excluded')) ? '' : implode(',', Tools::getValue('categories_excluded'));
        }

        // Reset fields with selector to NO but selected values remain
        foreach (array_keys($_POST) as $key) {
            if ((strpos($key, 'switch_') === 0)
                && !(int) Tools::getValue($key)) {
                // Set value in $_POST, can't use Tools::getValue()
                unset($_POST[str_replace('switch_', '', $key) . '_minimum']);
                unset($_POST[str_replace('switch_', '', $key) . '_maximum']);
                unset($_POST[str_replace('switch_', '', $key)]);
                // $_POST[str_replace('switch_', '', $key).'_excluded'] = null;
            }
        }

        return parent::processSave();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (!empty($this->errors)) {
            // if we have errors, we stay on the form instead of going back to the list
            $this->display = 'edit';

            return false;
        }
    }

    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

        $tpl->assign([
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&delete' . $this->table . '&token=' . ($token != null ? $token : $this->token),
            'confirm' => $this->l('Delete the selected item?') . $name,
            'action' => $this->l('Delete'),
            'id' => $id,
        ]);

        return $tpl->fetch();
    }

    public function getCustomerGroups($ids_customer_groups)
    {
        if ($ids_customer_groups === '' || $ids_customer_groups === 'all') {
            return $this->l('All');
        }
        $groups = [];
        $groups_array = explode(',', $ids_customer_groups);
        foreach ($groups_array as $key => $group) {
            if ($key == $this->top_elements_in_list) {
                $groups[] = $this->l('... and more');
                break;
            }
            $group = new Group($group, $this->context->language->id);
            $groups[] = $group->name;
        }

        return implode('<br />', $groups);
    }

    public function getSuppliers($ids_suppliers)
    {
        if ($ids_suppliers === '' || $ids_suppliers === 'all') {
            return $this->l('All');
        }

        $suppliers = [];
        $suppliers_array = explode(',', $ids_suppliers);
        foreach ($suppliers_array as $key => $supplier) {
            if ($key == $this->top_elements_in_list) {
                $suppliers[] = $this->l('... and more');
                break;
            }
            $supplier = new Supplier($supplier);
            $suppliers[] = $supplier->name;
        }

        return implode('<br />', $suppliers);
    }

    public function getCategories($ids_categories)
    {
        if ($ids_categories === '' || $ids_categories === 'all') {
            return $this->l('All');
        }

        $categories = [];

        if (@json_decode($ids_categories) !== false) {
            $categories_array = json_decode($ids_categories);
        } else {
            $categories_array = explode(',', $ids_categories);
        }

        foreach ($categories_array as $key => $category) {
            if ($key == $this->top_elements_in_list) {
                $categories[] = $this->l('... and more');
                break;
            }
            $category = new Category($category, $this->context->language->id);
            $categories[] = $category->name;
        }

        return implode('<br />', $categories);
    }

    public function getProducts($ids_products)
    {
        if ($ids_products === '' || $ids_products === 'all') {
            return $this->l('All');
        }
        $products = [];
        $products_array = explode(',', $ids_products);
        foreach ($products_array as $key => $product) {
            if ($key == $this->top_elements_in_list) {
                $products[] = $this->l('... and more');
                break;
            }
            $product = new Product($product, $this->context->language->id);
            $products[] = $product->id . '-' . Tools::substr($product->name[$this->context->language->id], 0, 9) . '..';
        }

        return implode('<br />', $products);
    }

    public function getCustomers($ids_customers)
    {
        if ($ids_customers === '' || $ids_customers === 'all') {
            return $this->l('All');
        }
        $customers = [];
        $customers_array = explode(',', $ids_customers);
        foreach ($customers_array as $key => $customer) {
            if ($key == $this->top_elements_in_list) {
                $customers[] = $this->l('... and more');
                break;
            }
            $customer = new Customer($customer);
            $customers[] = $customer->firstname . ' ' . $customer->lastname;
        }

        return implode('<br />', $customers);
    }

    private function _createTemplate($tpl_name)
    {
        if ($this->override_folder) {
            if ($this->context->controller instanceof ModuleAdminController) {
                $override_tpl_path = $this->context->controller->getTemplatePath() . $tpl_name;
            } elseif ($this->module) {
                $override_tpl_path = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/' . $tpl_name;
            } elseif (file_exists($this->context->smarty->getTemplateDir(1) . DIRECTORY_SEPARATOR . $this->override_folder . $this->base_folder . $tpl_name)) {
                $override_tpl_path = $this->context->smarty->getTemplateDir(1) . DIRECTORY_SEPARATOR . $this->override_folder . $this->base_folder . $tpl_name;
            } elseif (file_exists($this->context->smarty->getTemplateDir(0) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $this->override_folder . $this->base_folder . $tpl_name)) {
                $override_tpl_path = $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR . $this->override_folder . $this->base_folder . $tpl_name;
            }
        } elseif ($this->module) {
            $override_tpl_path = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/' . $tpl_name;
        }
        if (isset($override_tpl_path) && file_exists($override_tpl_path)) {
            return $this->context->smarty->createTemplate($override_tpl_path, $this->context->smarty);
        } else {
            return $this->context->smarty->createTemplate($tpl_name, $this->context->smarty);
        }
    }

    protected function getProductsLite($id_lang, $only_active = false, $front = false, $sort_by = false)
    {
        $query = 'SELECT p.*, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang .
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ($only_active ? ' AND product_shop.`active` = 1' : '') .
            ($sort_by ? ' ORDER BY ' . $sort_by . ' ASC' : 'ORDER BY p.`id_product` ASC');

        return Db::getInstance()->executeS($query);
    }

    public static function getAttributes($idLang, $notNull = false)
    {
        if (!Combination::isFeatureActive()) {
            return [];
        }

        return Db::getInstance()->executeS('
            SELECT DISTINCT ag.*, agl.*, a.`id_attribute`, al.`name`, agl.`name` AS `attribute_group`
            FROM `' . _DB_PREFIX_ . 'attribute_group` ag
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $idLang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a
                ON a.`id_attribute_group` = ag.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $idLang . ')
            ' . Shop::addSqlAssociation('attribute_group', 'ag') . '
            ' . Shop::addSqlAssociation('attribute', 'a') . '
            ' . ($notNull ? 'WHERE a.`id_attribute` IS NOT NULL AND al.`name` IS NOT NULL AND agl.`id_attribute_group` IS NOT NULL' : '') . '
            ORDER BY agl.`name` ASC, a.`position` ASC
        ');
    }

    public function stockOverCallback($value, $object)
    {
        if (!$object['send_mail']) {
            return '--';
        }

        return $value;
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (is_null($class)) {
            $class = $this->tabClassName . 'Controller';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->module->l($string, $class);
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }
}
