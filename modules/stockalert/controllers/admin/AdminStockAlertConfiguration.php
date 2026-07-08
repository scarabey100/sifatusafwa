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

class AdminStockAlertConfigurationController extends ModuleAdminController
{
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->tabClassName = 'AdminStockAlertConfiguration';

        parent::__construct();

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js', false);
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/addon/display/autorefresh.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/css/css.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/stockalert-admin.js');

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/tabs.css');
        }

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/theme/monokai.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/stockalert-admin.css');
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
            $this->page_header_toolbar_btn['desc-module-translate'] = [
                'href' => '#',
                'desc' => $this->l('Translate'),
                'modal_target' => '#moduleTradLangSelect',
                'icon' => 'process-icon-flag',
            ];

            $this->page_header_toolbar_btn['desc-module-hook'] = [
                'href' => 'index.php?tab=AdminModulesPositions&token=' . Tools::getAdminTokenLite('AdminModulesPositions') . '&show_modules=' . Module::getModuleIdByName($this->module->name),
                'desc' => $this->l('Manage hooks'),
                'icon' => 'process-icon-anchor',
            ];
        }

        $this->context->smarty->clearAssign('help_link', '');
    }

    public function initModal()
    {
        parent::initModal();

        $languages = Language::getLanguages(false);
        $translateLinks = [];

        if (version_compare(_PS_VERSION_, '1.7.2.1', '>=')) {
            $isNewTranslateSystem = $this->module->isUsingNewTranslationSystem();
            $link = Context::getContext()->link;
            foreach ($languages as $lang) {
                if ($isNewTranslateSystem) {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, [
                        'lang' => $lang['iso_code'],
                        'type' => 'modules',
                        'selected' => $this->module->name,
                        'locale' => $lang['locale'],
                    ]);
                } else {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, [], [
                        'type' => 'modules',
                        'module' => $this->module->name,
                        'lang' => $lang['iso_code'],
                    ]);
                }
            }
        }

        $this->context->smarty->assign([
            'trad_link' => 'index.php?tab=AdminTranslations&token=' . Tools::getAdminTokenLite('AdminTranslations') . '&type=modules&module=' . $this->module->name . '&lang=',
            'module_languages' => $languages,
            'module_name' => $this->module->name,
            'translateLinks' => $translateLinks,
        ]);

        $modal_content = $this->context->smarty->fetch('controllers/modules/modal_translation.tpl');

        $this->modals[] = [
            'modal_id' => 'moduleTradLangSelect',
            'modal_class' => 'modal-sm',
            'modal_title' => $this->l('Translate this module'),
            'modal_content' => $modal_content,
        ];
    }

    public function initContent()
    {
        if (!$this->isShopSelected && !$this->display) {
            $this->informations[] = $this->l('You have to select a shop.');
        }

        if ($warnings = $this->module->getWarnings(false)) {
            $this->content .= $this->module->displayError($warnings);
        }

        if (Tools::isSubmit('submitImportMailAlerts')) {
            $this->module->importAlertsFromMailAlertsModule();
        }

        if (Tools::isSubmit('submitImportBackInStock')) {
            $this->module->importAlertsFromBackInStockModule();
        }

        if (Tools::isSubmit('submitStockAlertConfiguration')) {
            if (Tools::getValue('SA_BCC')) {
                $emails = explode(';', Tools::getValue('SA_BCC'));

                foreach ($emails as $email) {
                    if (!Validate::isEmail($email)) {
                        $this->errors[] = sprintf($this->l('"%s" is not a valid email address.'), $email);
                    }
                }
            }

            $configurationFields = [
                'SA_CAPTCHA',
                'SA_ICONS_LIBRARY',
                'SA_BCC',
                'SA_HIDE_FILTER_CATEGORY',
                'SA_HIDE_FILTER_FEATURE',
                'SA_HIDE_FILTER_PRODUCT',
                'SA_HIDE_FILTER_MANUFACTURER',
                'SA_HIDE_FILTER_CUSTOMER_GROUP',
                'SA_HIDE_FILTER_CUSTOMER',
                'SA_HIDE_FILTER_COUNTRY',
                'SA_HIDE_FILTER_ZONE',
                'SA_CSS',
                'SA_JS',
            ];

            foreach ($configurationFields as $configurationField) {
                Configuration::updateValue($configurationField, Tools::getValue($configurationField));
            }

            $this->content .= $this->module->displayConfirmation($this->l('Configuration successfully updated.'));
        }

        // $this->content .= $this->_createTemplate('admin_translations.tpl')->fetch();

        if (!$this->display) {
            $this->content .= $this->renderGlobalConfigForm();
        }

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->smarty->assign([
                    'this_path' => $this->module->getPathUri(),
                    'support_id' => $this->module->addons_id_product,
                ]);

                $available_iso_codes = ['en', 'es'];
                $default_iso_code = 'en';
                $template_iso_suffix = in_array($this->context->language->iso_code, $available_iso_codes) ? $this->context->language->iso_code : $default_iso_code;
                $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/company/information_' . $template_iso_suffix . '.tpl');
            }
        }

        parent::initContent();
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

    protected function renderGlobalConfigForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = self::$currentIndex;
        $helper->submit_action = 'submitStockAlertConfiguration';
        $helper->token = Tools::getAdminTokenLite($this->tabClassName);
        $helper->tpl_vars = [
            'fields_value' => $this->getGlobalConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm($this->getGlobalConfigForm());
    }

    protected function getGlobalConfigForm()
    {
        $fields = [];

        $fieldsFormIndex = 0;
        $fields[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Cron Link'),
                'icon' => 'icon-link',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'free',
                    'label' => '',
                    'name' => 'SA_CRON_LINK',
                    'class' => 't',
                    'lang' => true,
                ],
            ],
        ];

        ++$fieldsFormIndex;
        $fields[$fieldsFormIndex]['form'] = [
            'id_form' => 'aaa',
            'legend' => [
                'title' => $this->l('Advanced'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 8,
                    'type' => 'html',
                    'name' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/warning.tpl')
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Include captcha'),
                    'name' => 'SA_CAPTCHA',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_CAPTCHA_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_CAPTCHA_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => 'Icons library',
                    'name' => 'SA_ICONS_LIBRARY',
                    'class' => 't',
                    'options' => [
                        'query' => [
                            [
                                'id' => '1',
                                'name' => 'Material Icons',
                            ],
                            [
                                'id' => '2',
                                'name' => 'Fontello',
                            ],
                            [
                                'id' => '3',
                                'name' => 'Font Awesome',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Email blind copy (BCC)'),
                    'name' => 'SA_BCC',
                    'class' => 't',
                    'col' => 6,
                    'desc' => $this->l('Separate the email addresses with ;'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Category')),
                    'name' => 'SA_HIDE_FILTER_CATEGORY',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_CATEGORY_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_CATEGORY_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Feature')),
                    'name' => 'SA_HIDE_FILTER_FEATURE',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_FEATURE_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_FEATURE_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Product')),
                    'name' => 'SA_HIDE_FILTER_PRODUCT',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_PRODUCT_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_PRODUCT_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Manufacturer')),
                    'name' => 'SA_HIDE_FILTER_MANUFACTURER',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_MANUFACTURER_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_MANUFACTURER_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Customer group')),
                    'name' => 'SA_HIDE_FILTER_CUSTOMER_GROUP',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_CUSTOMER_GROUP_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_CUSTOMER_GROUP_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Customer')),
                    'name' => 'SA_HIDE_FILTER_CUSTOMER',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_CUSTOMER_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_CUSTOMER_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Country')),
                    'name' => 'SA_HIDE_FILTER_COUNTRY',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_COUNTRY_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_COUNTRY_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => sprintf($this->l('Hide %s filter'), $this->l('Zone')),
                    'name' => 'SA_HIDE_FILTER_ZONE',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'SA_HIDE_FILTER_ZONE_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'SA_HIDE_FILTER_ZONE_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Import alerts from PrestaShop "Mail Alerts" module'),
                    'name' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/import_button_mailalerts.tpl')
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Import alerts from "backinstock" module'),
                    'name' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/import_button_backinstock.tpl')
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('CSS Rules'),
                    'name' => 'SA_CSS',
                    'class' => 'codemirror codemirror-css',
                    'cols' => 100,
                    'rows' => 10,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('JS script'),
                    'name' => 'SA_JS',
                    'class' => 'codemirror codemirror-js',
                    'cols' => 100,
                    'rows' => 10,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'btn btn-default pull-right' : 'button big',
                'name' => 'submitStockAlertConfiguration',
            ],
        ];

        return $fields;
    }

    protected function getGlobalConfigFormValues()
    {
        $configurationFields = [
            'SA_CAPTCHA',
            'SA_ICONS_LIBRARY',
            'SA_BCC',
            'SA_HIDE_FILTER_CATEGORY',
            'SA_HIDE_FILTER_FEATURE',
            'SA_HIDE_FILTER_PRODUCT',
            'SA_HIDE_FILTER_MANUFACTURER',
            'SA_HIDE_FILTER_CUSTOMER_GROUP',
            'SA_HIDE_FILTER_CUSTOMER',
            'SA_HIDE_FILTER_COUNTRY',
            'SA_HIDE_FILTER_ZONE',
            'SA_CSS',
            'SA_JS',
        ];

        $fields_values = [];
        foreach ($configurationFields as $configurationField) {
            $fields_values[$configurationField] = Tools::getValue($configurationField, Configuration::get($configurationField));
        }

        $this->context->smarty->assign([
            'cron_job_link' => $this->context->link->getModuleLink(
                $this->module->name,
                'cron',
                ['token' => Tools::encrypt('stockalert')], // parámetros de la URL
                Configuration::get('PS_SSL_ENABLED')
            ),
        ]);

        $fields_values['SA_CRON_LINK'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/cron_link.tpl');

        return $fields_values;
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
