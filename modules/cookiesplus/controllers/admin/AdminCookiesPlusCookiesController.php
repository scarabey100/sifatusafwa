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

class AdminCookiesPlusCookiesController extends ModuleAdminController
{
    protected $isShopSelected = true;
    private $tabClassName;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cookiesplus_cookie';
        $this->className = 'CookiesPlusCookie';
        $this->tabClassName = 'AdminCookiesPlusCookies';
        $this->_defaultOrderBy = 'name';
        $this->lang = true;
        $this->addRowAction('edit');
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

        $cookiesPlusFinalitiesArray = [];
        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities($this->context->language->id);
        foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
            $cookiesPlusFinalitiesArray[$cookiesPlusFinality['id_cookiesplus_finality']] = $cookiesPlusFinality['name'];
        }

        $this->fields_list = [
            'active' => [
                'title' => $this->l('Enabled'),
                'active' => 'active',
                'type' => 'bool',
            ],
            'name' => [
                'title' => $this->l('Cookie name'),
                'filter_key' => 'a!name',
            ],
            'id_cookiesplus_finality' => [
                'title' => $this->l('Cookie finality'),
                'callback' => 'getFinalityNameCallback',
                'callback_object' => 'CookiesPlusFinality',
                'type' => 'select',
                'list' => $cookiesPlusFinalitiesArray,
                'filter_key' => 'a!id_cookiesplus_finality',
            ],
            'provider' => [
                'title' => $this->l('Provider'),
            ],
            'purpose' => [
                'title' => $this->l('Purpose'),
                'callback' => 'getCookiePurposeCallback',
                'callback_object' => 'CookiesPlusCookie',
            ],
            'expiry' => [
                'title' => $this->l('Expiry'),
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

        if ($this->display) {
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/cookiesplus-back.js');

            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.css');
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/theme/monokai.css');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.js');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/addon/display/autorefresh.js');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/css/css.js');
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/javascript/javascript.js');
        }
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

        if ($warnings = $this->module->getWarnings(true)) {
            foreach ($warnings as $warning) {
                $this->warnings[] = $warning;
            }
        }

        if (Tools::isSubmit('submitCookiesPlusRevokeCookies')) {
            Configuration::updateValue('C_P_REVOKE_CONSENT', date('Y-m-d H:i:s'));
            $this->confirmations[] = $this->l('Revoke consent updated');
        }

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cookies.tpl');

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
    }

    public function initProcess()
    {
        parent::initProcess();
        if (Tools::isSubmit('activecookiesplus_cookie') && $this->id_object) {
            $this->toggleProperty('active', $this->id_object);
            if (!$this->errors) {
                // Remove toggle from URL
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink($this->tabClassName));
            }
        }
    }

    public function toggleProperty($property, $id)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if ($this->tabAccess['edit'] !== '1') {
                $this->errors[] = $this->l('You do not have permission to edit this.');

                return;
            }
        } else {
            if (!$this->access('edit')) {
                $this->errors[] = $this->l('You do not have permission to edit this.');

                return;
            }
        }

        $object = new $this->className($id);
        if (!Validate::isLoadedObject($object)) {
            $this->errors[] = $this->l('An error occurred while updating an object.');
        }

        $object->$property = !$object->$property;
        if (!$object->save()) {
            $this->errors[] = $this->l('An error occurred while updating an object.');
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if (!$this->isShopSelected) {
            unset($this->toolbar_btn['new']);
        }
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

    public function renderList()
    {
        $this->context->smarty->assign([
            'revokeConsentDate' => Tools::displayDate(date('Y-m-d H:i:s', strtotime(Configuration::get('C_P_REVOKE_CONSENT'))), false),
        ]);

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/revoke-consent.tpl');

        return parent::renderList();
    }

    public function renderForm()
    {
        if (!$this->isShopSelected && $this->display === 'add') {
            $this->errors[] = $this->l('You have to select a shop.');

            return parent::renderForm();
        }

        // Display a message to remember to clear the cache when the configuration is saved
        if (Tools::isSubmit('conf')) {
            if (CookiesPlus::cacheModuleDetected()) {
                $this->warnings[] = $this->l('Remember to clear the chache from PrestaShop cache and from your cache module.');
            } else {
                $this->warnings[] = $this->l('Remember to clear the cache from PrestaShop.');
            }
        }

        $this->context->smarty->assign([
            'revokeConsentDate' => Tools::displayDate(date('Y-m-d H:i:s', strtotime(Configuration::get('C_P_REVOKE_CONSENT'))), false),
        ]);

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/revoke-consent.tpl');

        $this->multiple_fieldsets = true;
        $this->default_form_language = $this->context->language->id;

        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities($this->context->language->id);

        $dummyElement = [
            'id_cookiesplus_finality' => '',
            'name' => $this->l('-- Choose --'),
        ];
        array_unshift($cookiesPlusFinalities, $dummyElement);

        $fieldsFormIndex = 0;
        $this->fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Configure cookie'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'id_cookiesplus_cookie',
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enabled'),
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
                    'type' => 'select',
                    'label' => $this->l('Cookie finality'),
                    'name' => 'id_cookiesplus_finality',
                    'required' => true,
                    'options' => [
                        'query' => $cookiesPlusFinalities,
                        'id' => 'id_cookiesplus_finality',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Cookie name'),
                    'name' => 'name',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Provider'),
                    'name' => 'provider',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Provider URL'),
                    'name' => 'provider_url',
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('Purpose'),
                    'name' => 'purpose',
                    'lang' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Expiry'),
                    'name' => 'expiry',
                    'lang' => true,
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

        // $this->content .= parent::renderForm();
        return parent::renderForm();
    }

    public function processSave()
    {
        $result = parent::processSave();

        if (Tools::getValue('back') && (int) Tools::getvalue('id_cookiesplus_finality')) {
            $this->redirect_after = Context::getContext()->link->getAdminLink('AdminCookiesPlusFinalities') . '&updatecookiesplus_finality=&conf=4&id_cookiesplus_finality=' . (int) Tools::getValue('id_cookiesplus_finality');
        }

        return $result;
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
