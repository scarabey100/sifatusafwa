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

class AdminCookiesPlusUsersConsentController extends ModuleAdminController
{
    protected $isShopSelected = true;
    private $tabClassName;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cookiesplus_user_consent';
        $this->className = 'CookiesPlusUserConsent';
        $this->tabClassName = 'AdminCookiesPlusUsersConsent';
        $this->list_no_link = true;
        $this->addRowAction('delete');

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
            'id_cookiesplus_user_consent' => [
                'title' => $this->l('ID'),
                'align' => 'text-center center',
                'class' => 'fixed-width-xs',
            ],
            'hash' => [
                'title' => $this->l('Hash'),
                'type' => 'text',
            ],
            'date' => [
                'title' => $this->l('Date'),
                'align' => 'text-left',
                'type' => 'datetime',
            ],
            'ip' => [
                'title' => $this->l('IP'),
                'align' => 'text-center center',
            ],
            'date_add' => [
                'title' => $this->l('Download PDF consent'),
                'align' => 'text-center center',
                'type' => 'text',
                'callback' => 'getDownloadPdfLink',
            ],
        ];

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        $this->shopLinkType = 'shop';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/bootstrap-15.css');
        }

        $this->addJqueryPlugin(['typewatch', 'fancybox', 'autocomplete']);
        $this->addJqueryUI(['ui.datepicker', 'ui.button', 'ui.sortable', 'ui.droppable']);

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/cookiesplus-back.css');

        // Graph
        $admin_webpath = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);

        $this->addJS([
            _PS_JS_DIR_ . 'vendor/d3.v3.min.js',
            __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->context->employee->bo_theme . '/js/vendor/nv.d3.min.js',
        ]);
        $this->addCSS(__PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->context->employee->bo_theme . '/css/vendor/nv.d3.css');
    }

    public function initContent()
    {
        if (!$this->isShopSelected && !$this->display) {
            $this->errors[] = $this->l('You have to select a shop.');

            // parent::initContent();

            return;
        }

        if (version_compare($this->module->version, $this->module->getDatabaseVersion(), '>')) {
            return $this->errors[] = $this->l('Upgrade available');
        }

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP) && !Module::isEnabled($this->module->name)) {
            $this->warnings[] = '<strong>' . $this->l('This module is currently disabled for this shop. To use its features, please go to the \'Modules\' section and enable it for this shop.');
        }

        if ($warnings = $this->module->getWarnings(true)) {
            foreach ($warnings as $warning) {
                $this->warnings[] = $warning;
            }
        }

        if (Tools::isSubmit('submitCookiesPlusUsersConsent')) {
            Configuration::updateValue('C_P_SAVE_CONSENT', Tools::getvalue('C_P_SAVE_CONSENT'));

            $this->confirmations[] = $this->l('Settings saved successfully');
        }

        $this->content .= $this->renderGlobalConfigForm();

        parent::initContent();

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/disclaimer.tpl');

        if (!$this->display) {
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
        }

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    public function renderList()
    {
        if ($this->isShopSelected
            && (
                (version_compare(_PS_VERSION_, '1.5.0.13', '<')
                    && !Module::isInstalled($this->module->name))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=')
                    && !Module::isEnabled($this->module->name))
            )
        ) {
            $this->warnings[] = '<strong>' . $this->l('This module is currently disabled for this shop. To use its features, please go to the \'Modules\' section and enable it for this shop.');
        }

        return parent::renderList();
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
        $helper->submit_action = 'submitCookiesPlusUsersConsent';
        $helper->token = Tools::getAdminTokenLite($this->tabClassName);

        $helper->tpl_vars = [
            'fields_value' => array_merge($this->getConfigFormValues()),
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

        $this->context->smarty->assign([
            'usersConsentControllerLink' => $this->context->link->getAdminLink('AdminCookiesPlusUsersConsent'),
        ]);

        $fieldsFormIndex = 0;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Stats'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/graph.tpl'),
                ],
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Configuration'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_USERS_CONSENT.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Generate a PDF file with the consent for each user'),
                    'name' => 'C_P_SAVE_CONSENT',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_SAVE_CONSENT_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_SAVE_CONSENT_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusUsersConsent',
            ],
        ];

        return $fields_form;
    }

    protected function getConfigFormValues()
    {
        $fields = [];

        $configFields = [
            'C_P_SAVE_CONSENT',
        ];

        foreach ($configFields as $field) {
            $fields[$field] = Tools::getValue($field, Configuration::get($field));
        }

        return $fields;
    }

    public function renderForm()
    {
        return parent::renderList();
    }

    public function ajaxProcessRefreshConsents()
    {
        $query = 'SELECT DATE(date_add) AS day, action, COUNT(*) AS total_records
            FROM ' . _DB_PREFIX_ . 'cookiesplus_stats
            WHERE date_add >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE(date_add), action
            ORDER BY DATE(date_add)';

        $responseData = [];
        $responseData['stats'] = Db::getInstance()->executeS($query);

        if (Tools::getValue('type') === 'hits') {
            $responseData['chart'] = 'line';
        } else {
            $responseData['chart'] = 'stackedBar';
        }

        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $this->ajaxRender(json_encode($responseData));
        } else {
            $this->ajaxDie(json_encode($responseData));
        }

        exit;
    }

    public function ajaxProcessResetStats()
    {
        $time = time();

        $queries = [];
        $queries[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'cookiesplus_stats_' . $time . '` LIKE `' . _DB_PREFIX_ . 'cookiesplus_stats`;';
        $queries[] = 'INSERT INTO `' . _DB_PREFIX_ . 'cookiesplus_stats_' . $time . '` SELECT * FROM `' . _DB_PREFIX_ . 'cookiesplus_stats`;';
        $queries[] = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'cookiesplus_stats`;';

        foreach ($queries as $query) {
            Db::getInstance()->execute($query);
        }

        $responseData = [];
        $responseData['result'] = true;

        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $this->ajaxRender(json_encode($responseData));
        } else {
            $this->ajaxDie(json_encode($responseData));
        }

        exit;
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
        if (file_exists($override_tpl_path)) {
            return $this->context->smarty->createTemplate($override_tpl_path, $this->context->smarty);
        } else {
            return $this->context->smarty->createTemplate($tpl_name, $this->context->smarty);
        }
    }

    public function getDownloadPdfLink($value, $object)
    {
        $this->context->smarty->assign([
            'download_link' => $this->context->link->getModuleLink('cookiesplus', 'front', ['hash' => $object['hash'], 'getPdf' => true], true),
        ]);

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/download_link.tpl');
        }

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/download_link_15.tpl');
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
