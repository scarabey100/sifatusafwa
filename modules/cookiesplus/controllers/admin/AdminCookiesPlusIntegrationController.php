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

class AdminCookiesPlusIntegrationController extends ModuleAdminController
{
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->tabClassName = 'AdminCookiesPlusIntegration';

        parent::__construct();

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

        // Tabs
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/tabs.css');
        }
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js');

        // CodeMirror
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.css');
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/theme/monokai.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/lib/codemirror.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/addon/display/autorefresh.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/css/css.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/lib/CodeMirror/mode/javascript/javascript.js');

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/cookiesplus-back.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/cookiesplus-back.js');
    }

    public function initContent()
    {
        if (!$this->isShopSelected && !$this->display) {
            $this->errors[] = $this->l('You have to select a shop.');

            parent::initContent();

            return true;
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

        if (((bool) Tools::isSubmit('submitCookiesPlusModule')) == true) {
            /*if (Tools::getValue('C_P_GTM_ENABLE')
                && !Tools::getValue('gtmFireConfiguration')) {
                $this->errors[] = $this->l('You have to define the needed consents to fire the Google Tag Manager script.');
            }*/

            if (Tools::getValue('C_P_GTM_ENABLE')) {
                // Check if the keys inside the "gtmFinality" subarrays are unique across all elements in your array
                // Array to store unique keys
                $uniqueKeys = [];

                // Flag to indicate if keys are unique
                $keysAreUnique = true;
                $atLeastOneValue = false;

                foreach (Tools::getValue('gtmConfiguration') as $item) {
                    if (isset($item['gtmFinality']) && is_array($item['gtmFinality'])) {
                        foreach ($item['gtmFinality'] as $key => $value) {
                            $atLeastOneValue = true;

                            // Check if the key is already in the unique keys array
                            if (in_array($key, $uniqueKeys)) {
                                $keysAreUnique = false;
                            } else {
                                // Add the key to the unique keys array
                                $uniqueKeys[] = $key;
                            }
                        }
                    }
                }

                if (!$keysAreUnique) {
                    $this->errors[] = $this->l('You can not enable the same consent tag in more than one finality.');
                }

                if (!$atLeastOneValue) {
                    $this->errors[] = $this->l('If you enable the integration with Google Consent Mode you need to define at least 1 consent type');
                }
            }

            if (Tools::getValue('C_P_MUET_ENABLE')) {
                // Check if the keys inside the "muetFinality" subarrays are unique across all elements in your array
                // Array to store unique keys
                $uniqueKeys = [];

                // Flag to indicate if keys are unique
                $keysAreUnique = true;
                $atLeastOneValue = false;

                foreach (Tools::getValue('muetConfiguration') as $item) {
                    if (isset($item['muetFinality']) && is_array($item['muetFinality'])) {
                        foreach ($item['muetFinality'] as $key => $value) {
                            $atLeastOneValue = true;

                            // Check if the key is already in the unique keys array
                            if (in_array($key, $uniqueKeys)) {
                                $keysAreUnique = false;
                            } else {
                                // Add the key to the unique keys array
                                $uniqueKeys[] = $key;
                            }
                        }
                    }
                }

                if (!$keysAreUnique) {
                    $this->errors[] = $this->l('You can not enable the same consent tag in more than one finality.');
                }

                if (!$atLeastOneValue) {
                    $this->errors[] = $this->l('If you enable the integration with Microsoft Universal Event Tracking (UET) Consent Mode you need to define at least 1 consent type');
                }
            }
            if (Tools::getValue('C_P_FB_ENABLE') && !Tools::getValue('fbConfiguration')) {
                $this->errors[] = $this->l('You have to define the needed consents to fire the Facebook Pixel.');
            }

            if (Tools::getValue('C_P_YT_ENABLE') && !Tools::getValue('ytConfiguration')) {
                $this->errors[] = $this->l('You have to define the needed consents to use YouTube cookies.');
            }

            $useHtmlPurifier = Configuration::get('PS_USE_HTMLPURIFIER');
            if (!count($this->errors)) {
                $fields = $this->getConfigFormValues();
                foreach (array_keys($fields) as $key) {
                    if ($key === 'C_P_GTM_CONSENT') {
                        $value = json_encode(Tools::getValue('gtmConfiguration'));
                        Configuration::updateValue($key, $value);
                    } elseif ($key === 'C_P_GTM_FIRE_CONSENT') {
                        $value = json_encode(Tools::getValue('gtmFireConfiguration'));
                        Configuration::updateValue($key, $value);
                    } elseif ($key === 'C_P_MUET_CONSENT') {
                        $value = json_encode(Tools::getValue('muetConfiguration'));
                        Configuration::updateValue($key, $value);
                    } elseif ($key === 'C_P_FB_CONSENT') {
                        $value = json_encode(Tools::getValue('fbConfiguration'));
                        Configuration::updateValue($key, $value);
                    } elseif ($key === 'C_P_YT_CONSENT') {
                        $value = json_encode(Tools::getValue('ytConfiguration'));
                        Configuration::updateValue($key, $value);
                    } else {
                        // Workaround to avoid the modification of the HTML code
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
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('Google (Consent Mode V2/GTM/GA)'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM.tpl'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM_SCRIPT.tpl'),
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('HEAD code'),
                    'name' => 'C_P_GTM_HEAD',
                    'class' => 't codemirror codemirror-js',
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('BODY code'),
                    'name' => 'C_P_GTM_BODY',
                    'class' => 't codemirror codemirror-js',
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM_ENABLE.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable the integration with Google Consent Mode'),
                    'name' => 'C_P_GTM_ENABLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_GTM_ENABLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_GTM_ENABLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Cookie finalities needed to include the GTM script in the page'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM_FIRE_CONSENT.tpl'),
                    'desc' => $this->l('If you don\'t select any cookie finality the script will be added without waiting for the user consent'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM_TEMPLATE.tpl'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Cookie finality <> Google consent type'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_GTM_CONSENT.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('url_passthrough value'),
                    'name' => 'C_P_GTM_URL_PASSTHROUGH',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_GTM_URL_PASSTHROUGH_on',
                            'value' => 1,
                            'label' => 'true',
                        ],
                        [
                            'id' => 'C_P_GTM_URL_PASSTHROUGH_off',
                            'value' => 0,
                            'label' => 'false',
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('ads_data_redaction value'),
                    'name' => 'C_P_GTM_ADS_DATA_REDACTION',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_GTM_ADS_DATA_REDACTION_on',
                            'value' => 1,
                            'label' => 'true',
                        ],
                        [
                            'id' => 'C_P_GTM_ADS_DATA_REDACTION_off',
                            'value' => 0,
                            'label' => 'false',
                        ],
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
                'title' => $this->l('Microsoft Universal Event Tracking (UET) Consent Mode'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_MUET.tpl'),
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable integration with the Microsoft Universal Event Tracking (UET) Consent Mode'),
                    'name' => 'C_P_MUET_ENABLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_MUET_ENABLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_MUET_ENABLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'cols' => 100,
                    'rows' => 4,
                    'type' => 'textarea',
                    'label' => $this->l('HEAD code'),
                    'name' => 'C_P_MUET_HEAD',
                    'class' => 't codemirror codemirror-js',
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => $this->l('Cookie finality <> Microsoft Universal Event Tracking (UET) Consent Mode'),
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_MUET_CONSENT.tpl'),
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
                'title' => $this->l('Facebook'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_FB.tpl'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        if (Module::isInstalled('fabfacebookpixel')) {
            $fabfacebookpixel = Module::getInstanceByName('fabfacebookpixel');
            if ($fabfacebookpixel
                && version_compare($fabfacebookpixel->version, '3.6.2', '<')) {
                $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
                    $fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        [
                            'col' => 12,
                            'type' => 'html',
                            'label' => '',
                            'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_FB_FABFACEBOOKPIXEL.tpl'),
                        ],
                    ]
                );
            }
        }

        if (Module::isInstalled('facebookconversiontrackingplus')) {
            $facebookconversiontrackingplus = Module::getInstanceByName('facebookconversiontrackingplus');
            if ($facebookconversiontrackingplus
                && version_compare($facebookconversiontrackingplus->version, '3.6.2', '<')) {
                $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
                    $fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        [
                            'col' => 12,
                            'type' => 'html',
                            'label' => '',
                            'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_FB_FACEBOOKCONVERSIONTRACKINGPLUS.tpl'),
                        ],
                    ]
                );
            }
        }

        if (Module::isInstalled('facebookproductad')) {
            $facebookproductad = Module::getInstanceByName('facebookproductad');
            if ($facebookproductad
                && version_compare($facebookproductad->version, '1.5.8', '<')) {
                $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
                    $fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        [
                            'col' => 12,
                            'type' => 'html',
                            'label' => '',
                            'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_FB_FACEBOOKPRODUCTAD.tpl'),
                        ],
                    ]
                );
            }
        }

        $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
            $fields_form[$fieldsFormIndex]['form']['input'],
            [
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable integration with the Facebook Pixel'),
                    'name' => 'C_P_FB_ENABLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_FB_ENABLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_FB_ENABLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => 'Cookie finalities needed to include the Facebook Pixel in the page',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_FB_CONSENT.tpl'),
                ],
            ]
        );

        ++$fieldsFormIndex;
        $fields_form[$fieldsFormIndex]['form'] = [
            'legend' => [
                'title' => $this->l('YouTube'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'col' => 12,
                    'type' => 'html',
                    'label' => '',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_YT.tpl'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'type' => 'submit',
                'name' => 'submitCookiesPlusModule',
            ],
        ];

        if (Module::isInstalled('stprovideos')) {
            $stprovideos = Module::getInstanceByName('stprovideos');
            if ($stprovideos
                && version_compare($stprovideos->version, '2.0.0', '<')) {
                $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
                    $fields_form[$fieldsFormIndex]['form']['input'],
                    [
                        [
                            'col' => 12,
                            'type' => 'html',
                            'label' => '',
                            'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_YT_STPROVIDEOS.tpl'),
                        ],
                    ]
                );
            }
        }

        $fields_form[$fieldsFormIndex]['form']['input'] = array_merge(
            $fields_form[$fieldsFormIndex]['form']['input'],
            [
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Replace youtube.com domain with youtube-nocookie.com'),
                    'name' => 'C_P_YT_ENABLE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_YT_ENABLE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_YT_ENABLE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Force replacing the domain in the DOM'),
                    'name' => 'C_P_YT_ENABLE_FORCE',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'C_P_YT_ENABLE_FORCE_on',
                            'value' => 1,
                            'label' => $this->l('Yes'), ],
                        [
                            'id' => 'C_P_YT_ENABLE_FORCE_off',
                            'value' => 0,
                            'label' => $this->l('No'), ],
                    ],
                    'desc' => $this->l('⚠️ Contact us before enabling this option'),
                ],
                [
                    'col' => 8,
                    'type' => 'html',
                    'label' => 'Cookie finalities needed to use the youtube.com domain',
                    'name' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/C_P_YT_CONSENT.tpl'),
                ],
            ]
        );

        return $fields_form;
    }

    protected function getConfigFormValues()
    {
        $fields = [];

        $configFields = [
            'C_P_GTM_ENABLE',
            'C_P_GTM_HEAD',
            'C_P_GTM_BODY',
            'C_P_GTM_FIRE_CONSENT',
            'C_P_GTM_CONSENT',
            'C_P_GTM_URL_PASSTHROUGH',
            'C_P_GTM_ADS_DATA_REDACTION',
            'C_P_MUET_HEAD',
            'C_P_MUET_ENABLE',
            'C_P_MUET_CONSENT',
            'C_P_FB_ENABLE',
            'C_P_FB_CONSENT',
            'C_P_YT_ENABLE',
            'C_P_YT_ENABLE_FORCE',
            'C_P_YT_CONSENT',
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
            'cookiesPlusFinalities' => CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, false, true),
            'gtmFinalities' => [
                'ad_storage',
                'ad_user_data',
                'ad_personalization',
                'analytics_storage',
                'functionality_storage',
                'personalization_storage',
                'security_storage',
            ],
            'fieldNameGtm' => 'gtmConfiguration',
            'valuesGtm' => json_decode(Configuration::get('C_P_GTM_CONSENT'), true),
            'fieldNameGtmFire' => 'gtmFireConfiguration',
            'valuesGtmFire' => json_decode(Configuration::get('C_P_GTM_FIRE_CONSENT'), true),
            'muetFinalities' => [
                'ad_storage',
                'analytics_storage',
            ],
            'fieldNameMuet' => 'muetConfiguration',
            'valuesMuet' => json_decode(Configuration::get('C_P_MUET_CONSENT'), true),
            'fieldNameFb' => 'fbConfiguration',
            'valuesFb' => json_decode(Configuration::get('C_P_FB_CONSENT'), true),
            'fieldNameYt' => 'ytConfiguration',
            'valuesYt' => json_decode(Configuration::get('C_P_YT_CONSENT'), true),
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
