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

class AdminCookiesPlusFinalitiesController extends ModuleAdminController
{
    protected $isShopSelected = true;
    protected $_defaultOrderBy = 'position';
    private $tabClassName;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cookiesplus_finality';
        $this->className = 'CookiesPlusFinality';
        $this->tabClassName = 'AdminCookiesPlusFinalities';
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
        $this->position_identifier = 'position';

        $this->fields_list = [
            'id_cookiesplus_finality' => [
                'title' => $this->l('ID'),
                'search' => false,
            ],
            'active' => [
                'title' => $this->l('Enabled'),
                'active' => 'active',
                'type' => 'bool',
                'search' => false,
            ],
            'name' => [
                'title' => $this->l('Cookie finality name'),
                'filter_key' => 'b!name',
                'search' => false,
            ],
            'description' => [
                'title' => $this->l('Cookie finality description'),
                'callback' => 'getFinalityDescriptionCallback',
                'callback_object' => 'CookiesPlusFinality',
                'search' => false,
            ],
            'modules' => [
                'title' => $this->l('Modules blocked'),
                'callback' => 'getModulesBlockedCallback',
                'callback_object' => 'CookiesPlusFinality',
                'search' => false,
            ],
            'technical' => [
                'title' => $this->l('Technical'),
                'align' => 'text-center',
                'active' => 'technical',
                'type' => 'bool',
                'search' => false,
            ],
            'position' => [
                'title' => $this->l('Position'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'position' => 'position',
                'search' => false,
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

        $this->addJqueryPlugin(['typewatch', 'fancybox', 'autocomplete']);
        $this->addJqueryUI(['ui.datepicker', 'ui.button', 'ui.sortable', 'ui.droppable']);

        // Tabs
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/tabs.css');
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/bootstrap-15.css');
        }

        // $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js'); // Before enabling this JS, check PS 1.5

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

        parent::initContent();

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/disclaimer.tpl');

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $default_iso_code = 'en';
                $local_path = $this->module->getLocalPath();

                $readme = null;
                if (file_exists($local_path . '/readme_' . $this->context->language->iso_code . '.pdf')) {
                    $readme = 'readme_' . $this->context->language->iso_code . '.pdf';
                } elseif (file_exists($local_path . '/readme_' . $default_iso_code . '.pdf')) {
                    $readme = 'readme_' . $default_iso_code . '.pdf';
                }

                $this->context->smarty->assign([
                    'support_id' => $this->module->addons_id_product,
                    'readme' => $readme,
                    'this_path' => $this->module->getPathUri(),
                ]);

                if (file_exists($local_path . '/views/templates/admin/company/information_' . $this->context->language->iso_code . '.tpl')) {
                    $this->content .= $this->context->smarty->fetch($local_path . '/views/templates/admin/company/information_' . $this->context->language->iso_code . '.tpl');
                } elseif (file_exists($local_path . '/views/templates/admin/company/information_' . $default_iso_code . '.tpl')) {
                    $this->content .= $this->context->smarty->fetch($local_path . '/views/templates/admin/company/information_' . $default_iso_code . '.tpl');
                }
            }

            $this->context->smarty->assign([
                'content' => $this->content,
            ]);
        }
    }

    public function initProcess()
    {
        parent::initProcess();

        if (Tools::isSubmit('activecookiesplus_finality') && $this->id_object) {
            $this->toggleProperty('active', $this->id_object);
            if (!$this->errors) {
                // Remove toggle from URL
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink($this->tabClassName));
            }
        }

        if (Tools::isSubmit('technicalcookiesplus_finality') && $this->id_object) {
            $this->toggleProperty('technical', $this->id_object);
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
        // Redirect if no object created
        /*if ($this->isShopSelected && !LoyaltyEditPointsAssignment::getNbObjects()) {
            $this->redirect_after = 'index.php?controller='.$this->tabClassName.'&add'.$this->table.'&token='.Tools::getAdminTokenLite($this->tabClassName);
            $this->redirect();
        }*/
        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cookie-finalities.tpl');

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

        $object = $this->loadObject(true);

        $this->multiple_fieldsets = true;
        $this->default_form_language = $this->context->language->id;

        // Modules blocked
        $modules = $this->module->getModuleList();
        $selectedModules = (array) json_decode($object->modules);
        foreach ($modules as &$module) {
            if (in_array($module['id_module'], $selectedModules)) {
                $module['checked'] = true;
            } else {
                $module['checked'] = false;
            }

            $module['sortDisplayName'] = strtolower($module['displayName']);
        }
        unset($module);

        array_multisort(
            array_column(
                $modules,
                'checked'
            ),
            SORT_DESC,
            array_column(
                $modules,
                'sortDisplayName'
            ),
            SORT_ASC,
            $modules
        );

        $this->context->smarty->assign([
            'allModules' => $modules,
            'fieldName' => 'modules',
        ]);

        $fieldsFormIndex = 0;
        $this->fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Cookie finality'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'id_cookiesplus_finality',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'position',
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
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('This is a Technical finality'),
                    'name' => 'technical',
                    'class' => 't',
                    'col' => 8,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'technical_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'technical_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'desc' => $this->l('Technical cookies can not be disabled. They are installed automatically.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Cookie finality name'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Cookie finality description'),
                    'name' => 'description',
                    'required' => true,
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 100,
                    'rows' => 30,
                ],
                [
                    'type' => 'html',
                    'col' => 8,
                    'label' => $this->l('Cookies from this finality'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cookies-list.tpl') . $this->module->getCookiesPlusCookiesList(),
                ],
                [
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => (Module::isInstalled('litespeedcache') || Module::isInstalled('stadvancedcache') || Module::isInstalled('jprestaspeedpack') || Module::isInstalled('pagecache')) ? '<div class="alert alert-warning">' . $this->l('If you are using a cache module please clear cache when you make any modification.') . '</div>' : '',
                ],
                [
                    'type' => 'html',
                    'col' => 8,
                    'label' => $this->l('Modules blocked'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/configure_modules.tpl'),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Execute this JS script when this cookie finality is accepted'),
                    'name' => 'js_script',
                    'cols' => 100,
                    'rows' => 10,
                    'class' => 't codemirror codemirror-js',
                    'desc' => sprintf($this->l('Enclose the script between the %s%s tags'), htmlspecialchars('<script>'), htmlspecialchars('</script>')),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Add this code to the BODY when this cookie finality is accepted'),
                    'name' => 'body_code',
                    'cols' => 100,
                    'rows' => 10,
                    'class' => 't codemirror codemirror-js',
                    'desc' => sprintf($this->l('Enclose the script between the %s%s tags'), htmlspecialchars('<script>'), htmlspecialchars('</script>')),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Execute this JS script when this cookie finality is rejected'),
                    'name' => 'js_not_script',
                    'cols' => 100,
                    'rows' => 10,
                    'class' => 't codemirror codemirror-js',
                    'desc' => sprintf($this->l('Enclose the script between the %s%s tags'), htmlspecialchars('<script>'), htmlspecialchars('</script>')),
                ],
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_SCRIPT_BLOCK.tpl'),
                ],
                /* Used to override token field from previous helperlist */
                [
                    'type' => 'hidden',
                    'name' => 'token',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusFinality',
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

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            foreach ($this->fields_form as &$fieldForm) {
                foreach ($fieldForm['form']['input'] as &$fieldFormInput) {
                    if ($fieldFormInput['type'] === 'html') {
                        // Generate a new random name for the field
                        $randomString = CookiesPlus::generateRandomString(12);

                        // Save the field value
                        $this->fields_value[$randomString] = $fieldFormInput['name'];

                        // Change the field type and match the name with the value
                        $fieldFormInput['type'] = 'free';
                        $fieldFormInput['name'] = $randomString;
                    }
                }
                unset($fieldFormInput);
            }
            unset($fieldForm);
        }

        // $this->content .= parent::renderForm();
        return parent::renderForm();
    }

    public function processSave()
    {
        if (Tools::isSubmit('submitCookiesPlusFinality')
            || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $fields_to_check = ['js_script', 'js_not_script'];

            // Define a regular expression pattern to match <script> tags
            $pattern = '/<script\b[^>]*>(.*?)<\/script>/s';

            // Check if the field contains <script> tags
            foreach ($fields_to_check as $field_name) {
                if ($field_value = Tools::getValue($field_name)) {
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
                            $this->errors[] = $this->l("Field doesn't contain any script tag.");
                        } else {
                            $this->errors[] = $this->l('Field contains mismatched script tags. Make sure all opening tags have corresponding closing tags.');
                        }
                    }
                }
            }
        }

        $_POST['modules'] = json_encode(Tools::getValue('modules'));
        $_POST['js_script'] = str_replace(['{literal}', '{/literal}'], '', Tools::getValue('js_script'));
        $_POST['js_not_script'] = str_replace(['{literal}', '{/literal}'], '', Tools::getValue('js_not_script'));

        return parent::processSave();
    }

    protected function afterAdd($object)
    {
        // Warning: Declaration of AdminCookiesPlusFinalitiesController::afterAdd() should be compatible with AdminControllerCore::afterAdd($object)

        $cookiesPlusFinality = new CookiesPlusFinality((int) Tools::getValue('id_cookiesplus_finality'));
        $cookiesPlusFinality->position = $cookiesPlusFinality->getHigherPosition() + 1;
        $cookiesPlusFinality->save();

        return true;
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

    public function ajaxProcessUpdatePositions()
    {
        $way = (int) Tools::getValue('way');
        $id = (int) Tools::getValue('id');
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int) $pos[2] === $id) {
                if ($conf = new CookiesPlusFinality((int) $pos[2])) {
                    if (isset($position) && $conf->updatePosition($way, $position)) {
                        echo 'ok position ' . (int) $position . ' for config ' . (int) $pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update config ' . (int) $id . ' to position ' . (int) $position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This config (' . (int) $id . ') can not be loaded"}';
                }

                break;
            }
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
