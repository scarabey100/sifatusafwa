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

class AdminCookiesPlusConfigurationController extends ModuleAdminController
{
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->tabClassName = 'AdminCookiesPlusConfiguration';

        parent::__construct();

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        $this->shopLinkType = 'shop';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        // CodeMirror
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/theme/monokai.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/addon/display/autorefresh.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/css/css.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/javascript/javascript.js');

        // Shepherd
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            Configuration::updateValue('C_P_SHEPHERD', 1);
        }

        if (!Configuration::get('C_P_SHEPHERD')) {
            Configuration::updateValue('C_P_SHEPHERD', 1);

            $this->context->smarty->assign([
                'C_P_SHEPHERD_STEP1_1' => $this->l('A new menu has been added to configure the Cookies module.') . '\n\r' . $this->l('Please check all the options before enable it.'),
            ]);

            echo $this->context->smarty->fetch($this->module->getLocalPath() . 'lib/shepherd/shepherd.tpl');

            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/shepherd/shepherd.css');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/shepherd/shepherd.js');
        }

        // Tabs
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/tabs.css');
        }
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js');

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/cookiesplus-back.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/cookiesplus-back.js');
    }

    public function initContent()
    {
        if (!$this->isShopSelected && !$this->display) {
            $this->errors[] = $this->l('You have to select a shop.');

            parent::initContent();

            return;
        }

        if (version_compare($this->module->version, $this->module->getDatabaseVersion(), '>')) {
            return $this->errors[] = $this->l('Upgrade available');
        }

        if ($this->isShopSelected
            && (
                (version_compare(_PS_VERSION_, '1.5.0.13', '<')
                    && !Module::isInstalled($this->module->name))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=')
                    && !Module::isEnabled($this->module->name))
            )
        ) {
            $this->warnings[] = $this->l('This module is currently disabled for this shop. To use its features, please go to the \'Modules\' section and enable it for this shop.');
        }

        if (Tools::isSubmit('submitReinstallTabs')) {
            $this->module->installTabs();
        }

        if ($warnings = $this->module->getWarnings(true)) {
            foreach ($warnings as $warning) {
                $this->warnings[] = $warning;
            }
        }

        if (((bool) Tools::isSubmit('submitCookiesPlusModuleUpdate')) == true) {
            Configuration::deleteByName('C_P_UPDATE');
        }

        if (((bool) Tools::isSubmit('submitCookiesPlusModule')) == true) {
            $fields = $this->getConfigFormValues();

            if (!Tools::getValue('C_P_EXPIRY')) {
                $this->errors[] = $this->l('You have to introduce the cookie expiry time');
            } elseif (Tools::getValue('C_P_EXPIRY') <= 0
                || !Validate::isUnsignedInt(Tools::getValue('C_P_EXPIRY'))) {
                $this->errors[] = $this->l('You have to introduce a correct value for cookie expiry time');
            }

            $ips = explode('|', Tools::getValue('C_P_IPS'));
            $ips = array_map('trim', $ips);
            foreach ($ips as $ip) {
                if ($ip && !filter_var($ip, FILTER_VALIDATE_IP)) {
                    $this->errors[] = sprintf($this->l('Property %s is not valid'), $ip);
                }
            }
            $_POST['C_P_IPS'] = implode('|', $ips);

            $ips = explode('|', Tools::getValue('C_P_IPS_DEBUG'));
            $ips = array_map('trim', $ips);
            foreach ($ips as $ip) {
                if ($ip && !filter_var($ip, FILTER_VALIDATE_IP)) {
                    $this->errors[] = sprintf($this->l('Property %s is not valid'), $ip);
                }
            }
            $_POST['C_P_IPS_DEBUG'] = implode('|', $ips);

            if ($field_value = Tools::getValue('C_P_CSS')) {
                // Define a regular expression pattern to match <style> tags
                $pattern = '/<style\b[^>]*>(.*?)<\/style>/is';

                // Check if the field contains any <style> tags
                if (preg_match_all($pattern, $field_value, $matches)) {
                    // Count the number of opening and closing <style> tags
                    $opening_tags = substr_count($field_value, '<style');
                    $closing_tags = substr_count($field_value, '</style>');

                    // Check if the number of opening and closing <style> tags match
                    if ($opening_tags !== $closing_tags) {
                        $this->errors[] = $this->l('Field contains mismatched style tags. Make sure all opening tags have corresponding closing tags.');
                    }
                } else {
                    // Check for the presence of <style> and </style> tags independently
                    $has_opening_tag = strpos($field_value, '<style') !== false;
                    $has_closing_tag = strpos($field_value, '</style>') !== false;

                    if (!$has_opening_tag && !$has_closing_tag) {
                        $this->errors[] = $this->l('Field doesn\'t contain any style tag.');
                    } else {
                        $this->errors[] = $this->l('Field contains mismatched style tags. Make sure all opening tags have corresponding closing tags.');
                    }
                }
            }

            if ($field_value = Tools::getValue('C_P_JS')) {
                // Define a regular expression pattern to match <style> tags
                $pattern = '/<script\b[^>]*>(.*?)<\/script>/is';

                // Check if the field contains any <script> tags
                if (preg_match_all($pattern, $field_value, $matches)) {
                    // Count the number of opening and closing <script> tags
                    $opening_tags = substr_count($field_value, '<script');
                    $closing_tags = substr_count($field_value, '</script>');

                    // Check if the number of opening and closing <script> tags match
                    if ($opening_tags !== $closing_tags) {
                        $this->errors[] = $this->l('Field contains mismatched script tags. Make sure all opening tags have corresponding closing tags.');
                    }
                } else {
                    // Check for the presence of <script> and </script> tags independently
                    $has_opening_tag = strpos($field_value, '<script') !== false;
                    $has_closing_tag = strpos($field_value, '</script>') !== false;

                    if (!$has_opening_tag && !$has_closing_tag) {
                        $this->errors[] = $this->l('Field doesn\'t contain any script tag.');
                    } else {
                        $this->errors[] = $this->l('Field contains mismatched script tags. Make sure all opening tags have corresponding closing tags.');
                    }
                }
            }

            $useHtmlPurifier = Configuration::get('PS_USE_HTMLPURIFIER');
            if (!count($this->errors)) {
                foreach (array_keys($fields) as $key) {
                    if ($key === 'C_P_BOTS'
                        || $key === 'C_P_IPS') {
                        $fields[$key] = trim(preg_replace('/\|+/', '|', $fields[$key]), '|');
                        Configuration::updateValue($key, $fields[$key]);
                    } else {
                        Configuration::updateValue('PS_USE_HTMLPURIFIER', false);
                        Configuration::updateValue($key, $fields[$key], true);
                        Configuration::updateValue('PS_USE_HTMLPURIFIER', $useHtmlPurifier);
                    }
                }

                $this->confirmations[] = $this->l('Settings saved successfully');
            }
        }

        $this->content .= $this->renderGlobalConfigForm();
        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/disclaimer.tpl');

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $module = $this->module;

            $default_iso_code = 'en';
            $local_path = $module->getLocalPath();

            $readme = null;
            if (file_exists($local_path . '/readme_' . $this->context->language->iso_code . '.pdf')) {
                $readme = 'readme_' . $this->context->language->iso_code . '.pdf';
            } elseif (file_exists($local_path . '/readme_' . $default_iso_code . '.pdf')) {
                $readme = 'readme_' . $default_iso_code . '.pdf';
            }

            $this->context->smarty->assign([
                'support_id' => $module->addons_id_product,
                'readme' => $readme,
                'this_path' => $module->getPathUri(),
            ]);

            if (file_exists($local_path . '/views/templates/admin/company/information_' . $this->context->language->iso_code . '.tpl')) {
                $this->content .= $this->context->smarty->fetch($local_path . '/views/templates/admin/company/information_' . $this->context->language->iso_code . '.tpl');
            } elseif (file_exists($local_path . '/views/templates/admin/company/information_' . $default_iso_code . '.tpl')) {
                $this->content .= $this->context->smarty->fetch($local_path . '/views/templates/admin/company/information_' . $default_iso_code . '.tpl');
            }
        }

        parent::initContent();
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
            /*$this->page_header_toolbar_btn['desc-module-new'] = array(
                'href' => 'index.php?controller='.$this->tabClassName.'&add'.$this->table.'&token='.Tools::getAdminTokenLite($this->tabClassName),
                'desc' => $this->l('New'),
                'icon' => 'process-icon-new'
            );*/
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

        if (!$this->isShopSelected) {
            unset($this->page_header_toolbar_btn['desc-module-new']);
        }

        $this->context->smarty->clearAssign('help_link', '');
    }

    public function initModal()
    {
        parent::initModal();

        $languages = Language::getLanguages(false);
        $translateLinks = [];

        if (version_compare(_PS_VERSION_, '1.7.2.1', '>=')) {
            $module = Module::getInstanceByName($this->module->name);
            $isNewTranslateSystem = $module->isUsingNewTranslationSystem();
            $link = Context::getContext()->link;
            foreach ($languages as $lang) {
                if ($isNewTranslateSystem) {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, [
                        'lang' => $lang['iso_code'],
                        'type' => 'modules',
                        'selected' => $module->name,
                        'locale' => $lang['locale'],
                    ]);
                } else {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, [], [
                        'type' => 'modules',
                        'module' => $module->name,
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

        $modal_content = $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/modal_translation.tpl');

        $this->modals[] = [
            'modal_id' => 'moduleTradLangSelect',
            'modal_class' => 'modal-sm',
            'modal_title' => $this->l('Translate this module'),
            'modal_content' => $modal_content,
        ];
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
        $helper->submit_action = 'submitCookiesPlusModule';
        $helper->token = Tools::getAdminTokenLite($this->tabClassName);

        $helper->tpl_vars = [
            'fields_value' => array_merge($this->getConfigFormValues(), $this->getConfigFormTPLs()),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $form = $this->getConfigForm();

        $fieldsValue = [];
        if (version_compare(_PS_VERSION_, '1.6.0.7', '<')) {
            foreach ($form as &$fieldForm) {
                foreach ($fieldForm['form']['input'] as &$fieldFormInput) {
                    if ($fieldFormInput['type'] === 'html') {
                        // Generate a new random name for the field
                        $randomString = CookiesPlus::generateRandomString(12);

                        // Save the field value
                        $fieldsValue[$randomString] = $fieldFormInput['name'];

                        // Change the field type and match the name with the value
                        $fieldFormInput['type'] = 'free';
                        $fieldFormInput['name'] = $randomString;
                    }
                }
                unset($fieldFormInput);
            }
            unset($fieldForm);
        }

        $helper->tpl_vars = [
            'fields_value' => array_merge($helper->tpl_vars['fields_value'], $fieldsValue),
        ];

        return $helper->generateForm($form);
    }

    protected function getConfigForm()
    {
        $fields_form = [];
        $fieldsFormIndex = 0;

        if (Configuration::get('C_P_UPDATE')) {
            $fields_form[$fieldsFormIndex]['form'] = [
                'legend' => [
                    'title' => $this->l('Warning'),
                    'icon' => 'icon-warning',
                ],
                'input' => [
                    [
                        'col' => 12,
                        'type' => 'html',
                        'label' => '',
                        'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_UPDATE_MSG.tpl'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Understood'),
                    'type' => 'submit',
                    'name' => 'submitCookiesPlusModuleUpdate',
                ],
            ];

            return $fields_form;
        }

        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Module settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable module'),
                    'name' => 'C_P_ENABLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_ENABLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_ENABLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('Enable module only for the specified IPs (DEBUG mode)'),
                    'desc' => $this->l('Display the banner only for the specified IPs') . '<br />' . $this->l('Cookies (and modules) will only be blocked and cookies notice will only be displayed for these IPs') . '<br />' . $this->l('Separate each IP with a "|" (pipe) character'),
                    'name' => 'C_P_IPS_DEBUG',
                    'class' => 't',
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('DEBUG mode'),
                    'name' => 'C_P_DEBUG',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DEBUG_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'C_P_DEBUG_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'desc' => $this->l('Display DEBUG information in the browser console'),
                ],
                [
                    'col' => 6,
                    'type' => 'text',
                    'label' => $this->l('Cookie lifetime'),
                    'desc' => $this->l('The cookie consent will be stored during this time (or until customer delete cookies)') . '<br />' . $this->l('Once the consent is expired, the banner will be displayed again to ask for the consent.'),
                    'suffix' => $this->l('days'),
                    'name' => 'C_P_EXPIRY',
                    'class' => 't fixed-width-xl',
                    'required' => true,
                ],
                [
                    'col' => 6,
                    'type' => 'text',
                    'label' => $this->l('Display the banner again after X days if the user has not accepted all the finalities'),
                    'desc' => $this->l('If the user has not accepted all the finalities, the banner will be displayed again after the days configured'),
                    'suffix' => $this->l('days'),
                    'name' => 'C_P_DISPLAY_AGAIN',
                    'class' => 't fixed-width-xl',
                    'required' => true,
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Reload page when user gives consent'),
                    'desc' => $this->l('If disabled, the consent will be sent dinamically and the page will not be reloaded.') . '<br />' . $this->l('The blocked scripts are loaded "on the fly", without losing referrers nor bounces.'),
                    'name' => 'C_P_REFRESH',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_REFRESH_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_REFRESH_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display notice to customers outside the EU'),
                    'desc' => Configuration::get('PS_GEOLOCATION_ENABLED') ? '' : $this->l('Geolocation must be enabled to enable this option'),
                    'name' => 'C_P_GEO',
                    'class' => 't',
                    'disabled' => !Configuration::get('PS_GEOLOCATION_ENABLED'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_GEO_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_GEO_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('Don\'t apply restrictions for these user agents (SEO)'),
                    'desc' => $this->l('Cookies (and modules) will not be blocked and cookies notice will not be displayed for these user agents') . '<br />' . $this->l('Separate each user agent with a "|" (pipe) character'),
                    'name' => 'C_P_BOTS',
                    'class' => 't',
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('Don\'t apply restrictions for these IPs'),
                    'desc' => $this->l('Cookies (and modules) will not be blocked for these IPs') . '<br />' . $this->l('Separate each IP with a "|" (pipe) character'),
                    'name' => 'C_P_IPS',
                    'class' => 't',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        /*
        $fieldsFormIndex++;
        $fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Appearance'),
                'icon' => 'icon-pencil',
            ),
            'input' => array(
                array(
                    'col' => 12,
                    'type' => 'free',
                    'label' => '',
                    'name' => 'C_P_MENU',
                    'class' => 't',
                    'lang' => true,
                ),
            ),
        );

        $fieldsFormIndex++;
        $fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Cookie finalities'),
                'icon' => 'icon-pencil',
            ),
            'input' => array(
                array(
                    'col' => 12,
                    'type' => 'free',
                    'label' => '',
                    'name' => 'C_P_MENU',
                    'class' => 't',
                    'lang' => true,
                ),
            ),
        );

        $fieldsFormIndex++;
        $fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Cookies'),
                'icon' => 'icon-pencil',
            ),
            'input' => array(
                array(
                    'col' => 12,
                    'type' => 'free',
                    'label' => '',
                    'name' => 'C_P_MENU',
                    'class' => 't',
                    'lang' => true,
                ),
            ),
        );
        */

        $this->context->smarty->assign([
            'checkerLink' => Context::getContext()->link->getPageLink('index', true) . '?debugCookies',
        ]);

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('How to configure the module'),
                'icon' => 'icon-ambulance',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/configuration.tpl'),
                ],
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Checker'),
                'icon' => 'icon-check',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/checker-button.tpl'),
                ],
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Advanced'),
                'icon' => 'icon-magic',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_WARNING.tpl'),
                ],
                /*array(
                    'type' => 'text',
                    'label' => 'C_P_HOOK_EXECUTED',
                    'name' => 'C_P_HOOK_EXECUTED',
                    'col' => 1
                ),
                array(
                    'type' => 'text',
                    'label' => 'C_P_HOOK_POSITION',
                    'name' => 'C_P_HOOK_POSITION',
                    'col' => 1
                ),*/
                [
                    'cols' => 100,
                    'rows' => 10,
                    'type' => 'textarea',
                    'label' => 'CSS',
                    'name' => 'C_P_CSS',
                    'class' => 't codemirror codemirror-css',
                    'desc' => sprintf($this->l('Enclose the script between the %s%s tags'), htmlspecialchars('<style>'), htmlspecialchars('</style>')),
                ],
                [
                    'type' => 'textarea',
                    'label' => 'JS',
                    'name' => 'C_P_JS',
                    'cols' => 100,
                    'rows' => 10,
                    'class' => 't codemirror codemirror-js',
                    'desc' => sprintf($this->l('Enclose the script between the %s%s tags'), htmlspecialchars('<script>'), htmlspecialchars('</script>')),
                ],
                [
                    'type' => 'select',
                    'label' => 'Icons library',
                    'name' => 'C_P_MATERIAL_ICONS_LIBRARY',
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
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => 'Add Material Icons CSS library',
                    'name' => 'C_P_MATERIAL_ICONS',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_MATERIAL_ICONS_on',
                            'value' => 1,
                            'label' => 'Yes',
                        ],
                        [
                            'id' => 'C_P_MATERIAL_ICONS_off',
                            'value' => 0,
                            'label' => 'No',
                        ],
                    ],
                ],
                [
                    'type' => 'html',
                    'label' => $this->l('Reinstall tabs'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/reinstall-tabs.tpl'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        return $fields_form;
    }

    protected function getConfigFormValues()
    {
        $fields = [];

        $configFields = [
            'C_P_HOOK_EXECUTED',
            'C_P_HOOK_POSITION',
            'C_P_CSS',
            'C_P_JS',
            'C_P_MATERIAL_ICONS',
            'C_P_MATERIAL_ICONS_LIBRARY',
            'C_P_ENABLE',
            'C_P_REFRESH',
            'C_P_GEO',
            'C_P_DEBUG',
            'C_P_EXPIRY',
            'C_P_DISPLAY_AGAIN',
            'C_P_BOTS',
            'C_P_IPS',
            'C_P_IPS_DEBUG',
        ];

        foreach ($configFields as $field) {
            $fields[$field] = Tools::getValue($field, Configuration::get($field));
        }

        return $fields;
    }

    protected function getConfigFormTPLs()
    {
        $fields = [];

        $this->context->smarty->assign([
            'this_path' => $this->module->getPathUri(),
            'support_id' => $this->module->addons_id_product,
        ]);

        /*$fields['C_P_MENU'] =
            $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_MENU.tpl');*/

        return $fields;
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (is_null($class)) {
            $class = $this->tabClassName . 'Controller';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $module = $this->module;
            if (!$module) {
                $module = Module::getInstanceByName('cookiesplus');
            }
            return $module->l($string, $class);
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }
}
