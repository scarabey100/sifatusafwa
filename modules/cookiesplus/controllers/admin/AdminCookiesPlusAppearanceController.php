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

class AdminCookiesPlusAppearanceController extends ModuleAdminController
{
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->tabClassName = 'AdminCookiesPlusAppearance';

        parent::__construct();

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        $this->shopLinkType = 'shop';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        // Codemirror
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/theme/monokai.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/addon/display/autorefresh.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/css/css.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/javascript/javascript.js');

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
            $this->warnings[] = '<strong>' . $this->l('This module is currently disabled for this shop. To use its features, please go to the \'Modules\' section and enable it for this shop.');
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

            $colorFields = [
                'C_P_BACKGROUND_COLOR',
                'C_P_FONT_COLOR',
                'C_P_ACCEPT_BACKGROUND_COLOR',
                'C_P_ACCEPT_BORDER_COLOR',
                'C_P_ACCEPT_FONT_COLOR',
                'C_P_MORE_INFO_BACKGROUND_COLOR',
                'C_P_MORE_INFO_BORDER_COLOR',
                'C_P_MORE_INFO_FONT_COLOR',
                'C_P_REJECT_BACKGROUND_COLOR',
                'C_P_REJECT_BORDER_COLOR',
                'C_P_REJECT_FONT_COLOR',
                'C_P_SAVE_BACKGROUND_COLOR',
                'C_P_SAVE_BORDER_COLOR',
                'C_P_SAVE_FONT_COLOR',
            ];

            foreach ($colorFields as $colorField) {
                if ($fields[$colorField]) {
                    $_POST[$colorField] = $fields[$colorField] = strpos($fields[$colorField], '#') !== 0 ? '#' . $fields[$colorField] : $fields[$colorField];
                    if (!preg_match('/#([a-f0-9]{3}){1,2}\b/i', $fields[$colorField])) {
                        $this->errors[] = sprintf($this->l('Property %s is not valid'), $fields[$colorField]);
                    }
                }
            }

            if (!count($this->errors)) {
                foreach (array_keys($fields) as $key) {
                    Configuration::updateValue($key, $fields[$key], true);
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
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->currentIndex = self::$currentIndex;
        $helper->submit_action = 'submitCookiesPlusModule';
        $helper->token = Tools::getAdminTokenLite($this->tabClassName);

        $helper->tpl_vars = [
            'fields_value' => array_merge($this->getConfigFormValues(), $this->getConfigFormTPLs()),
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
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm($form);
    }

    protected function getConfigForm()
    {
        $fields_form = [];

        if (Configuration::get('C_P_UPDATE')) {
            $fieldsFormIndex = 0;
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

        $cms = CMS::listCms($this->context->language->id);
        $dummyElement = [
            'id_cms' => 0,
            'meta_title' => $this->l('- Do not display any link -'),
        ];

        array_unshift($cms, $dummyElement);

        $fieldsFormIndex = 0;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Position and colors for the banner'),
                'icon' => 'icon-pencil',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_DISPLAY_MODAL.tpl'),
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_FONT_COLOR',
                    'size' => 20,
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Position'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_POSITION.tpl'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Width'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_WIDTH.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display overlay'),
                    'name' => 'C_P_OVERLAY',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_OVERLAY_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_OVERLAY_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Overlay opacity'),
                    'name' => 'C_P_OVERLAY_OPACITY',
                    'class' => 't',
                    'col' => '2',
                    'options' => [
                        'query' => [
                            [
                                'id' => '1',
                                'name' => '1 (darkest)',
                            ],
                            [
                                'id' => '0.9',
                                'name' => '0.9',
                            ],
                            [
                                'id' => '0.8',
                                'name' => '0.8',
                            ],
                            [
                                'id' => '0.7',
                                'name' => '0.7',
                            ],
                            [
                                'id' => '0.6',
                                'name' => '0.6',
                            ],
                            [
                                'id' => '0.5',
                                'name' => '0.5',
                            ],
                            [
                                'id' => '0.4',
                                'name' => '0.4',
                            ],
                            [
                                'id' => '0.3',
                                'name' => '0.3',
                            ],
                            [
                                'id' => '0.2',
                                'name' => '0.2',
                            ],
                            [
                                'id' => '0.1',
                                'name' => '0.1',
                            ],
                            [
                                'id' => '0',
                                'name' => '0 (lightest)',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_OVERLAY_MSG.tpl'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Banner\'s texts'),
                'icon' => 'icon-pencil',
            ],
            'input' => [
                /*[
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Banner title'),
                    'name' => 'C_P_DISPLAY_TITLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DISPLAY_TITLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_DISPLAY_TITLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'cols' => 90,
                    'rows' => 5,
                    'type' => 'textarea',
                    'label' => $this->l('Banner title'),
                    'name' => 'C_P_TITLE',
                    'lang' => true,
                    'autoload_rte' => version_compare(_PS_VERSION_, '1.6', '>=') ? '' : true,
                    'class' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'cp_tiny' : 't',
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_TITLE_MSG.tpl'),
                ],*/
                [
                    'type' => 'html',
                    'name' => 'html',
                    // 'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_DISPLAY_TEXT.tpl'),
                    'html_content' => '<div class="alert alert-info">' . $this->l('You can translate the button labels using the "Translate" button in the toolbar') . '</div>',
                ],
                [
                    'cols' => 100,
                    'rows' => 5,
                    'type' => 'textarea',
                    'label' => $this->l('Banner body text'),
                    'name' => 'C_P_TEXT_BASIC',
                    'lang' => true,
                    'required' => true,
                    'autoload_rte' => version_compare(_PS_VERSION_, '1.6', '>=') ? '' : true,
                    'class' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'cp_tiny' : 't',
                ],

                [
                    'col' => 6,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_DISPLAY_TEXT.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable cookie encouragement text'),
                    'name' => 'C_P_DISPLAY_ENCOURAGEMENT',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DISPLAY_ENCOURAGEMENT_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_DISPLAY_ENCOURAGEMENT_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                    'desc' => $this->l('If the customer doesn\'t accept the cookies, a second overlay will appear to encourage them to accept the cookies.'),
                ],
                [
                    'cols' => 100,
                    'rows' => 5,
                    'type' => 'textarea',
                    'label' => $this->l('Cookie encouragement text'),
                    'name' => 'C_P_TEXT_ENCOURAGEMENT',
                    'lang' => true,
                    'required' => true,
                    'autoload_rte' => version_compare(_PS_VERSION_, '1.6', '>=') ? '' : true,
                    'class' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'cp_tiny' : 't',
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Display a link to the cookies policy CMS'),
                    'name' => 'C_P_CMS_PAGE',
                    'class' => 't fixed-width-xxxl',
                    'options' => [
                        'query' => $cms,
                        'id' => 'id_cms',
                        'name' => 'meta_title',
                    ],
                ],
                [
                    'col' => 6,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_DISPLAY_LINK.tpl'),
                ],
                [
                    'col' => 6,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_COOKIES_POLICIES_CMS.tpl'),
                ],
                /*array(
                    'col' => 5,
                    'type' => 'text',
                    'label' => $this->l('"Accept only selected cookies" button padding'),
                    'name' => 'C_P_SAVE_PADDING',
                    'desc' => $this->l('Example').':'.'<br />10px<br />10px 5px<br />10px 5px 20px 5px<br />0.5rem 1rem'
                ),*/
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display an "X" icon to close the banner'),
                    'name' => 'C_P_DISPLAY_X',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DISPLAY_X_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_DISPLAY_X_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                    'desc' => $this->l('This button refuses all the cookies'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display the date when the cookie information was updated'),
                    'name' => 'C_P_DISPLAY_DATE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DISPLAY_DATE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_DISPLAY_DATE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'col' => 6,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_DISPLAY_DATE.tpl'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Buttons settings'),
                'icon' => 'icon-pencil',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Buttons layout'),
                    'name' => 'C_P_BUTTONS_LAYOUT',
                    'class' => 't fixed-width-xxxl',
                    'options' => [
                        'query' => [
                            [
                                'id' => '1',
                                'name' => $this->l('Accept - Reject - Customize'),
                            ],
                            [
                                'id' => '2',
                                'name' => $this->l('Accept - Customize - Reject'),
                            ],
                            [
                                'id' => '3',
                                'name' => $this->l('Reject - Accept - Customize'),
                            ],
                            [
                                'id' => '4',
                                'name' => $this->l('Reject - Customize - Accept'),
                            ],
                            [
                                'id' => '5',
                                'name' => $this->l('Customize - Accept - Reject'),
                            ],
                            [
                                'id' => '6',
                                'name' => $this->l('Customize - Reject - Accept'),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display icons in the buttons'),
                    'name' => 'C_P_ICONS',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_ICONS_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_ICONS_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display the cookies finalities disabled by default in the 2nd layer (when customizing the consent)'),
                    'name' => 'C_P_DEFAULT_CONSENT',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_DEFAULT_CONSENT_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_DEFAULT_CONSENT_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                    'desc' => $this->l('Deactivating this option may potentially be in violation of the law'),
                ],
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<hr />',
                ],
                /*[
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<span class="btn btn-default">' . $this->l('Button "Accept cookies"') . '</span>',
                ],*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<label class="control-label custom-control-label">' . $this->l('Button "Accept cookies"') . '</label>',
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_ACCEPT_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Border color'),
                    'name' => 'C_P_ACCEPT_BORDER_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_ACCEPT_FONT_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Font size'),
                    'name' => 'C_P_ACCEPT_FONT_SIZE',
                    'class' => 't fixed-width-sm',
                    'options' => [
                        'query' => [
                            [
                                'id' => '10px',
                                'name' => '10px',
                            ],
                            [
                                'id' => '12px',
                                'name' => '12px',
                            ],
                            [
                                'id' => '14px',
                                'name' => '14px',
                            ],
                            [
                                'id' => '16px',
                                'name' => '16px',
                            ],
                            [
                                'id' => '18px',
                                'name' => '18px',
                            ],
                            [
                                'id' => '20px',
                                'name' => '20px',
                            ],
                            [
                                'id' => '22px',
                                'name' => '22px',
                            ],
                            [
                                'id' => '24px',
                                'name' => '24px',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                /*array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('"Accept all cookies" button padding'),
                    'name' => 'C_P_ACCEPT_PADDING',
                    'desc' => $this->l('Example').':'.'<br />10px<br />10px 5px<br />10px 5px 20px 5px<br />0.5rem 1rem'
                ),*/
                /*array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'switch' : 'radio',
                    'label' => $this->l('Display "Configure" button'),
                    'name' => 'C_P_MORE_INFO_DISPLAY',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'C_P_MORE_INFO_DISPLAY_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'C_P_MORE_INFO_DISPLAY_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<hr />',
                ],
                /*[
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<span class="btn btn-default">' . $this->l('Button "Reject cookies"') . '</span>',
                ],*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<label class="control-label custom-control-label">' . $this->l('Button "Reject cookies"') . '</label>',
                ],
                [
                    'type' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'switch' : 'radio',
                    'label' => $this->l('Display the button in the first layer'),
                    'name' => 'C_P_REJECT_DISPLAY',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_REJECT_DISPLAY_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'C_P_REJECT_DISPLAY_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_REJECT_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Border color'),
                    'name' => 'C_P_REJECT_BORDER_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_REJECT_FONT_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Font size'),
                    'name' => 'C_P_REJECT_FONT_SIZE',
                    'class' => 't fixed-width-sm',
                    'options' => [
                        'query' => [
                            [
                                'id' => '10px',
                                'name' => '10px',
                            ],
                            [
                                'id' => '12px',
                                'name' => '12px',
                            ],
                            [
                                'id' => '14px',
                                'name' => '14px',
                            ],
                            [
                                'id' => '16px',
                                'name' => '16px',
                            ],
                            [
                                'id' => '18px',
                                'name' => '18px',
                            ],
                            [
                                'id' => '20px',
                                'name' => '20px',
                            ],
                            [
                                'id' => '22px',
                                'name' => '22px',
                            ],
                            [
                                'id' => '24px',
                                'name' => '24px',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                /*array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('"Accept only essential cookies" button padding'),
                    'name' => 'C_P_REJECT_PADDING',
                    'desc' => $this->l('Example').':'.'<br />10px<br />10px 5px<br />10px 5px 20px 5px<br />0.5rem 1rem'
                ),*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<hr />',
                ], /*
                [
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<span class="btn btn-default">' . $this->l('Button "Customize"') . '</span>',
                ],*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<label class="control-label custom-control-label">' . $this->l('Button "Customize"') . '</label>',
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_MORE_INFO_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Border color'),
                    'name' => 'C_P_MORE_INFO_BORDER_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_MORE_INFO_FONT_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Font size'),
                    'name' => 'C_P_MORE_INFO_FONT_SIZE',
                    'class' => 't fixed-width-sm',
                    'options' => [
                        'query' => [
                            [
                                'id' => '10px',
                                'name' => '10px',
                            ],
                            [
                                'id' => '12px',
                                'name' => '12px',
                            ],
                            [
                                'id' => '14px',
                                'name' => '14px',
                            ],
                            [
                                'id' => '16px',
                                'name' => '16px',
                            ],
                            [
                                'id' => '18px',
                                'name' => '18px',
                            ],
                            [
                                'id' => '20px',
                                'name' => '20px',
                            ],
                            [
                                'id' => '22px',
                                'name' => '22px',
                            ],
                            [
                                'id' => '24px',
                                'name' => '24px',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                /*array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('"Configure" button padding'),
                    'name' => 'C_P_MORE_INFO_PADDING',
                    'desc' => $this->l('Example').':'.'<br />10px<br />10px 5px<br />10px 5px 20px 5px<br />0.5rem 1rem'
                ),*/
                /*array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'switch' : 'radio',
                    'label' => $this->l('Display "Accept only essential cookies" button'),
                    'name' => 'C_P_REJECT_DISPLAY',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'C_P_REJECT_DISPLAY_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'C_P_REJECT_DISPLAY_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<hr />',
                ], /*
                [
                    'type' => 'html',
                    'name' => 'html',
                    'html_content' => '<span class="btn btn-default">' . $this->l('Button "Save my preferences" (displayed in the 2nd layer)') . '</span>',
                ],*/
                [
                    'type' => 'html',
                    'label' => '',
                    'name' => '<label class="control-label custom-control-label">' . $this->l('Button "Save my preferences" (displayed in the 2nd layer)') . '</label>',
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_SAVE_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Border color'),
                    'name' => 'C_P_SAVE_BORDER_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_SAVE_FONT_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Font size'),
                    'name' => 'C_P_SAVE_FONT_SIZE',
                    'class' => 't fixed-width-sm',
                    'options' => [
                        'query' => [
                            [
                                'id' => '10px',
                                'name' => '10px',
                            ],
                            [
                                'id' => '12px',
                                'name' => '12px',
                            ],
                            [
                                'id' => '14px',
                                'name' => '14px',
                            ],
                            [
                                'id' => '16px',
                                'name' => '16px',
                            ],
                            [
                                'id' => '18px',
                                'name' => '18px',
                            ],
                            [
                                'id' => '20px',
                                'name' => '20px',
                            ],
                            [
                                'id' => '22px',
                                'name' => '22px',
                            ],
                            [
                                'id' => '24px',
                                'name' => '24px',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Icon always visible'),
                'icon' => 'icon-pencil',
            ],
            'input' => [
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display an icon'),
                    'name' => 'C_P_TAB_ENABLED',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_TAB_ENABLED_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_TAB_ENABLED_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                    'desc' => $this->l('Display a small icon that enables your customers to check or update their given consent.'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Position'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_TAB_POSITION.tpl'),
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'C_P_TAB_BACKGROUND_COLOR',
                    'size' => 20,
                ],
                [
                    'type' => 'color',
                    'label' => $this->l('Font color'),
                    'name' => 'C_P_TAB_FONT_COLOR',
                    'size' => 20,
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
            'C_P_CMS_PAGE',
            'C_P_POSITION',
            'C_P_WIDTH',
            'C_P_MODE',
            'C_P_OVERLAY',
            'C_P_OVERLAY_OPACITY',
            'C_P_BACKGROUND_COLOR',
            'C_P_FONT_COLOR',
            'C_P_ACCEPT_DISPLAY',
            'C_P_BUTTONS_LAYOUT',
            'C_P_ACCEPT_BACKGROUND_COLOR',
            'C_P_ACCEPT_BORDER_COLOR',
            'C_P_ACCEPT_FONT_COLOR',
            'C_P_ACCEPT_FONT_SIZE',
            'C_P_ACCEPT_PADDING',
            'C_P_MORE_INFO_DISPLAY',
            'C_P_MORE_INFO_BACKGROUND_COLOR',
            'C_P_MORE_INFO_BORDER_COLOR',
            'C_P_MORE_INFO_FONT_COLOR',
            'C_P_MORE_INFO_FONT_SIZE',
            'C_P_MORE_INFO_PADDING',
            'C_P_REJECT_DISPLAY',
            'C_P_REJECT_BACKGROUND_COLOR',
            'C_P_REJECT_BORDER_COLOR',
            'C_P_REJECT_FONT_COLOR',
            'C_P_REJECT_FONT_SIZE',
            'C_P_REJECT_PADDING',
            'C_P_SAVE_BACKGROUND_COLOR',
            'C_P_SAVE_BORDER_COLOR',
            'C_P_SAVE_FONT_COLOR',
            'C_P_SAVE_FONT_SIZE',
            'C_P_SAVE_PADDING',
            // 'C_P_DISPLAY_TITLE',
            'C_P_DISPLAY_X',
            'C_P_DISPLAY_ENCOURAGEMENT',
            'C_P_DISPLAY_DATE',
            'C_P_ICONS',
            'C_P_DEFAULT_CONSENT',
            'C_P_TAB_ENABLED',
            'C_P_TAB_POSITION',
            'C_P_TAB_BACKGROUND_COLOR',
            'C_P_TAB_FONT_COLOR',
        ];

        foreach ($configFields as $field) {
            $fields[$field] = Tools::getValue($field, Configuration::get($field));
        }

        $configFields = [
            // 'C_P_TITLE',
            'C_P_TEXT_BASIC',
            'C_P_TEXT_ENCOURAGEMENT',
        ];

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            foreach ($configFields as $field) {
                $fields[$field][$lang['id_lang']] = Tools::getValue(
                    $field . '_' . $lang['id_lang'],
                    Configuration::get($field, $lang['id_lang'])
                );
            }
        }

        return $fields;
    }

    protected function getConfigFormTPLs()
    {
        $fields = [];

        $this->context->smarty->assign([
            'this_path' => $this->module->getPathUri(),
            'support_id' => $this->module->addons_id_product,
            'C_P_POSITION' => Configuration::get('C_P_POSITION'),
            'C_P_WIDTH' => Configuration::get('C_P_WIDTH'),
            'C_P_TAB_POSITION' => Configuration::get('C_P_TAB_POSITION'),
            'C_P_COOKIES_POLICIES' => Configuration::get('C_P_COOKIES_POLICIES'),
        ]);

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
