<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HiFaqAdminForms
{
    public $module;
    public $name;
    public $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->name = $module->name;
        $this->context = Context::getContext();
    }

    public function renderSettingsForm()
    {
        if ($this->module->psv >= 1.7) {
            $hook = [
                [
                    'id' => 'displayProductAdditionalInfo',
                    'name' => $this->l('Product Additional Info'),
                ],
                [
                    'id' => 'displayProductExtraContent',
                    'name' => $this->l('Product Extra Content'),
                ],
            ];
        } else {
            $hook = [
                [
                    'id' => 'displayProductTab',
                    'name' => $this->l('Product Tab'),
                ],
                [
                    'id' => 'displayLeftColumnProduct',
                    'name' => $this->l('Product Left Column'),
                ],
                [
                    'id' => 'displayRightColumnProduct',
                    'name' => $this->l('Product Right Column'),
                ],
            ];
        }
        array_push($hook, [
            'id' => 'displayFooterProduct',
            'name' => $this->l('Product Footer'),
        ]);
        array_push($hook, [
            'id' => 'custom',
            'name' => $this->l('Custom'),
        ]);
        array_push($hook, [
            'id' => 'none',
            'name' => $this->l('Do Not Display'),
        ]);
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Where to display FAQs in product page?'),
                        'name' => 'product_page_hook',
                        'desc' => $this->l('Add {hook h=\'displayHiFAQProduct\'} to your product.tpl file where you want to display product FAQs'),
                        'options' => [
                            'query' => $hook,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->l('FAQs for category pages'),
                        'name' => 'category_page_faqs',
                        'html_content' => $this->l('Since PrestaShop category pages do not have built in hooks, if you want to display FAQs in category pages you\'ll need to add {hook h=\'displayHiFAQCategory\' idCategory=$category.id} to your category.tpl file where you want to display category FAQs'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display search block'),
                        'name' => 'search',
                        'is_bool' => true,
                        'desc' => $this->l('This will add a search block on top of FAQs pages.') . ' ' . sprintf($this->l('You can add the custom hook %s in your theme tpl file if your theme doesn\'t support default displayNavFullWidth hook.'), '{hook h=\'displayFAQSearch\'}'),
                        'values' => [
                            [
                                'id' => 'search_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'search_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Main page layout'),
                        'name' => 'layout',
                        'options' => [
                            'query' => [
                                [
                                    'id' => '1',
                                    'name' => $this->l('1 Column'),
                                ],
                                [
                                    'id' => '2',
                                    'name' => $this->l('2 Columns'),
                                ],
                                [
                                    'id' => '3',
                                    'name' => $this->l('3 Columns'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('FAQs count for each category in main page'),
                        'name' => 'faqs_count',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display assigned products in FAQ details page'),
                        'name' => 'related_products',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'related_products_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'related_products_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Icons Type'),
                        'name' => 'icons',
                        'options' => [
                            'query' => [
                                [
                                    'id' => 'material',
                                    'name' => $this->l('Material'),
                                ],
                                [
                                    'id' => 'fontAwesome',
                                    'name' => $this->l('Font Awesome'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'doc' => 'faqIcons',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Clean Database when module uninstalled?'),
                        'name' => 'clean_db',
                        'is_bool' => true,
                        'desc' => $this->l('Not recommended, use this only when you’re not going to use the module'),
                        'values' => [
                            [
                                'id' => 'clean_db_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'clean_db_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'doc' => 'cleanDb',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submit_settings_form',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        if (!Configuration::get('PS_REWRITING_SETTINGS')) {
            $fields_form['form']['warning'] = $this->l('The module require Friendly URL option to be enabled');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->module = $this->module;
        $helper->submit_action = 'submitBlockSettings';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=generel_settings';
        $helper->tpl_vars = [
            'fields_value' => [
                'product_page_hook' => $this->module->product_page_hook,
                'clean_db' => $this->module->clean_db,
                'search' => $this->module->search,
                'layout' => $this->module->layout,
                'faqs_count' => $this->module->faqs_count,
                'related_products' => $this->module->related_products,
                'icons' => $this->module->icons,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderSEOForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('SEO Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Google Structured Data'),
                        'name' => 'structured_data',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'structured_data_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'structured_data_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('FAQs main page URL'),
                        'name' => 'faq_url',
                        'required' => true,
                        'prefix' => $this->context->shop->getBaseURL(true),
                        'suffix' => $this->module->renderLink($this->module->getMainURL(), $this->l('Preview'), '_blank'),
                        'disabled' => Configuration::get('PS_REWRITING_SETTINGS') ? false : true,
                        'desc' => !Configuration::get('PS_REWRITING_SETTINGS') ? $this->l('Required Friendly URL') : '',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('FAQ details URL'),
                        'name' => 'details_url',
                        'required' => true,
                        'prefix' => $this->context->shop->getBaseURL(true),
                        'suffix' => '/{link_rewrite}',
                        'disabled' => Configuration::get('PS_REWRITING_SETTINGS') ? false : true,
                        'desc' => $this->l('Example: ') . $this->context->shop->getBaseURL(true) . $this->module->faq_url . '/faq/faq-link-rewrite',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category page URL'),
                        'name' => 'category_url',
                        'required' => true,
                        'prefix' => $this->context->shop->getBaseURL(true),
                        'suffix' => '/{link_rewrite}',
                        'disabled' => Configuration::get('PS_REWRITING_SETTINGS') ? false : true,
                        'desc' => $this->l('Example: ') . $this->context->shop->getBaseURL(true) . $this->module->faq_url . '/category/category-link-rewrite',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Search page URL'),
                        'name' => 'search_url',
                        'required' => true,
                        'prefix' => $this->context->shop->getBaseURL(true),
                        'suffix' => '/{query}',
                        'disabled' => Configuration::get('PS_REWRITING_SETTINGS') ? false : true,
                        'desc' => $this->l('Example: ') . $this->context->shop->getBaseURL(true) . $this->module->faq_url . '/search/query',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Main page Meta Title'),
                        'name' => 'main_page_meta_title',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Main page Meta Description'),
                        'name' => 'main_page_meta_description',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Main page Meta Keywords'),
                        'name' => 'main_page_meta_keywords',
                        'lang' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Main page description'),
                        'name' => 'main_page_description',
                        'autoload_rte' => true,
                        'lang' => true,
                        'cols' => 200,
                        'rows' => 40,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submit_seo_form',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        if (!Configuration::get('PS_REWRITING_SETTINGS')) {
            $fields_form['form']['warning'] = $this->l('The module require Friendly URL option to be enabled');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->module = $this->module;
        $helper->submit_action = 'submitSEOSettings';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=seo_settings';
        $helper->tpl_vars = [
            'fields_value' => [
                'structured_data' => $this->module->structured_data,
                'faq_url' => $this->module->faq_url,
                'category_url' => $this->module->category_url,
                'details_url' => $this->module->details_url,
                'search_url' => $this->module->search_url,
                'main_page_meta_title' => $this->module->main_page_meta_title,
                'main_page_meta_description' => $this->module->main_page_meta_description,
                'main_page_meta_keywords' => $this->module->main_page_meta_keywords,
                'main_page_description' => $this->module->main_page_description,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderSidebarSettings()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Sidebar Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Sidebar position'),
                        'name' => 'sidebar_position',
                        'options' => [
                            'query' => [
                                [
                                    'id' => 'left',
                                    'name' => $this->l('Left Sidebar'),
                                ],
                                [
                                    'id' => 'right',
                                    'name' => $this->l('Right Sidebar'),
                                ],
                                [
                                    'id' => 'no',
                                    'name' => $this->l('No Sidebar'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submit_sidebar_settings',
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=sidebar';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->tpl_vars = [
            'fields_value' => [
                'sidebar_position' => $this->module->sidebar_position,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderCategoryForm($id_category = null)
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $id_category ? $this->l('Update Category') : $this->l('Add Category'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_category',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'class' => 't',
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
                        'lang' => true,
                        'class' => 'copy2friendlyUrl',
                        'required' => true,
                        'id' => 'name',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Meta title'),
                        'name' => 'meta_title',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Meta description'),
                        'name' => 'meta_description',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Friendly URL'),
                        'name' => 'friendlyurl',
                        'lang' => true,
                        'required' => true,
                        'id' => 'link_rewrite',
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Category to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_category ? $this->l('Update') : $this->l('Add'),
                    'name' => 'submitSaveFaqCategory',
                    'class' => 'btn btn-default pull-right',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'closeFaqCategoryForm',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=category_list';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqcategory';
        $helper->identifier = 'id';
        $helper->id = $id_category;
        $helper->tpl_vars = [
            'fields_value' => $this->getCategoryAddFieldsValues($id_category),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getCategoryAddFieldsValues($id_category = null)
    {
        if ($id_category) {
            $category = new HiFAQCategory($id_category);

            return [
                'id_category' => $id_category,
                'active' => $category->active,
                'name' => $category->name,
                'description' => $category->description,
                'meta_title' => $category->meta_title,
                'meta_description' => $category->meta_description,
                'friendlyurl' => $category->friendly_url,
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_category' => 0,
                'active' => true,
                'name' => $empty_array,
                'description' => $empty_array,
                'meta_title' => $empty_array,
                'meta_description' => $empty_array,
                'friendlyurl' => $empty_array,
            ];
        }
    }

    public function renderCategoriesList($filter = [], $pageItems = 50, $pageNumber = 1)
    {
        if (!(int) $pageItems) {
            $pageItems = 50;
        }
        if (!(int) $pageNumber) {
            $pageNumber = 1;
        }

        $fields_list = [
            'sort' => [
                'title' => $this->l('Sort'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
                'disableSort' => $filter ? true : false,
            ],
            'id' => [
                'title' => $this->l('ID'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
            ],
            'name' => [
                'title' => $this->l('Name'),
                'width' => 140,
                'type' => 'text',
                'search' => true,
            ],
            'status' => [
                'title' => $this->l('Status'),
                'width' => 140,
                'type' => 'select',
                'search' => true,
                'filter_key' => 'faqCategoryStatus',
                'list' => [
                    1 => $this->l('Active'),
                    0 => $this->l('Inactive'),
                ],
            ],
        ];
        $helper = new HelperList();
        $helper->module = $this->module;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->actions = ['edit', 'delete'];
        $helper->identifier = 'id';
        $helper->show_toolbar = false;
        $helper->title = $this->l('FAQ Categories');
        $helper->table = 'hifaqcategory';
        $helper->toolbar_btn['new'] = [
            'href' => '#',
            'desc' => $this->l('Add new category'),
        ];
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&' . $this->name . '=category_list';
        $categories = HiFAQCategory::filterCategories($filter, $pageNumber, $pageItems);
        $helper->listTotal = $categories['total'];

        return $helper->generateList($categories['result'], $fields_list);
    }

    public function renderAddFAQForm($id_faq = null)
    {
        $faq_categories = HiFAQCategory::getCategories(true);
        $categories = [];
        foreach ($faq_categories as $cat) {
            array_push($categories, [
                'id' => $cat['id'],
                'name' => $cat['name'],
            ]);
        }
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $id_faq ? $this->l('Update FAQ') : $this->l('Add FAQ'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_faq',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
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
                        'label' => $this->l('Title'),
                        'name' => 'title',
                        'lang' => true,
                        'required' => true,
                        'class' => 'copy2friendlyUrl',
                        'id' => 'name',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Category'),
                        'multiple' => true,
                        'name' => 'faq_category[]',
                        'id' => 'faqCategorySelect',
                        'options' => [
                            'query' => $categories,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Question'),
                        'name' => 'question',
                        'lang' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'label' => $this->l('Answer'),
                        'name' => 'answer',
                        'lang' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Meta title'),
                        'name' => 'meta_title',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Meta description'),
                        'name' => 'meta_description',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Meta keywords'),
                        'name' => 'meta_keywords',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Friendly URL'),
                        'name' => 'friendly_url',
                        'lang' => true,
                        'required' => true,
                        'id' => 'link_rewrite',
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the FAQ to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_faq ? $this->l('Update') : $this->l('Add'),
                    'name' => 'submitSaveFaq',
                    'class' => 'btn btn-default pull-right submit_item',
                    'icon' => 'icon-save',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'closeFaqForm',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=faqs';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->table = 'hifaq';
        $helper->identifier = 'id_faq';
        $helper->id = $id_faq;
        $helper->tpl_vars = [
            'fields_value' => $this->getFAQAddFieldsValues($id_faq),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getFAQAddFieldsValues($id_faq)
    {
        if ($id_faq) {
            $category = [];
            $faq_post_category = HiFAQPostCategory::getAllFaqCategoryByIdFaq($id_faq);
            if (!empty($faq_post_category)) {
                foreach ($faq_post_category as $cat) {
                    $category[] = $cat['id_category'];
                }
            }
            $faq = new HiFAQItem($id_faq);

            return [
                'id_faq' => $id_faq,
                'active' => $faq->active,
                'faq_category[]' => $category,
                'title' => $faq->title,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'meta_title' => $faq->meta_title,
                'meta_description' => $faq->meta_description,
                'meta_keywords' => $faq->meta_keywords,
                'friendly_url' => $faq->friendly_url,
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_faq' => 0,
                'active' => true,
                'faq_category[]' => [],
                'title' => $empty_array,
                'question' => $empty_array,
                'answer' => $empty_array,
                'meta_title' => $empty_array,
                'meta_description' => $empty_array,
                'meta_keywords' => $empty_array,
                'friendly_url' => $empty_array,
            ];
        }
    }

    public function renderFAQsList($filter = [], $pageItems = 50, $pageNumber = 1)
    {
        if (!(int) $pageItems) {
            $pageItems = 50;
        }
        if (!(int) $pageNumber) {
            $pageNumber = 1;
        }

        $faqCategories = HiFAQCategory::getCategories(true);
        $sortedCategories = [];
        if (is_array($faqCategories) && $faqCategories) {
            foreach ($faqCategories as $faqCategory) {
                $sortedCategories[$faqCategory['id']] = $faqCategory['name'];
            }
        }
        $fields_list = [
            'sort' => [
                'title' => $this->l('Sort'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
                'disableSort' => $filter ? true : false,
            ],
            'id_faq' => [
                'title' => $this->l('ID'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
            ],
            'title' => [
                'title' => $this->l('Title'),
                'width' => 140,
                'type' => 'text',
                'search' => true,
            ],
            'question' => [
                'title' => $this->l('Question'),
                'width' => 140,
                'type' => 'text',
                'search' => true,
            ],
            'related_products' => [
                'title' => $this->l('Assign to Products'),
                'search' => false,
                'type' => 'multiActionButton',
                'actionType' => 'renderFaqRelatedProducts',
                'actionTitle' => $this->l('Manage products'),
                'actionIcon' => 'icon-list',
                'actionCount' => ['relatedProductsCount', 'relatedFeaturesCount'],
            ],
            'related_categories' => [
                'title' => $this->l('Assign to Categories'),
                'search' => false,
                'type' => 'actionButton',
                'actionType' => 'renderFaqRelatedCategories',
                'actionTitle' => $this->l('Manage Categories'),
                'actionIcon' => 'icon-folder-open',
                'actionCount' => 'relatedCategoriesCount',
            ],
            'faqCategories' => [
                'title' => $this->l('FAQ Categories'),
                'type' => 'select',
                'search' => true,
                'filter_key' => 'faqCategories',
                'list' => $sortedCategories,
                'align' => 'center',
                'class' => 'column-faqcategories',
            ],
            'status' => [
                'title' => $this->l('Status'),
                'width' => 140,
                'type' => 'select',
                'search' => true,
                'filter_key' => 'faqStatus',
                'list' => [
                    1 => $this->l('Active'),
                    0 => $this->l('Inactive'),
                ],
            ],
        ];

        $helper = new HelperList();
        $helper->module = $this->module;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->actions = ['edit', 'delete'];
        $helper->identifier = 'id_faq';
        $helper->show_toolbar = false;
        $helper->title = $this->l('FAQs');
        $helper->table = 'hifaq';
        $helper->toolbar_btn['new'] = [
            'href' => '#',
            'desc' => $this->l('Add'),
        ];
        $helper->toolbar_btn['reset'] = [
            'href' => '#',
            'desc' => $this->l('Reset positions to default'),
        ];
        $this->context->smarty->assign([
            'faqCategories' => HiFAQCategory::getCategories(true),
        ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&' . $this->name . '=faqs';
        $faqs = HiFAQItem::filterFaqs($filter, $pageNumber, $pageItems);
        $helper->listTotal = $faqs['total'];

        return $helper->generateList($faqs['result'], $fields_list);
    }

    public function renderBlocksList($filter = [], $pageItems = 50, $pageNumber = 1)
    {
        if (!(int) $pageItems) {
            $pageItems = 50;
        }
        if (!(int) $pageNumber) {
            $pageNumber = 1;
        }

        $fields_list = [
            'sort' => [
                'title' => $this->l('Sort'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
            ],
            'id_block' => [
                'title' => $this->l('ID'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
            ],
            'title' => [
                'title' => $this->l('Title'),
                'width' => 140,
                'type' => 'text',
                'search' => true,
            ],
            'typeName' => [
                'title' => $this->l('Block Type'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ],
            'hook' => [
                'title' => $this->l('Position'),
                'width' => 140,
                'search' => false,
            ],
            'custom_hook' => [
                'title' => $this->l('Custom hook'),
                'width' => 140,
                'search' => false,
            ],
            'status' => [
                'title' => $this->l('Status'),
                'width' => 140,
                'type' => 'select',
                'search' => true,
                'filter_key' => 'faqBlockStatus',
                'list' => [
                    1 => $this->l('Active'),
                    0 => $this->l('Inactive'),
                ],
            ],
        ];
        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->actions = ['edit', 'delete'];
        $helper->identifier = 'id_block';
        $helper->show_toolbar = false;
        $helper->title = $this->l('Blocks');
        $helper->table = 'hifaqblock';
        $helper->module = $this;
        $helper->toolbar_btn['new'] = [
            'href' => '#',
            'desc' => $this->l('Add New Block'),
        ];
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&' . $this->name . '=faq_list';
        $blocks = HiFAQBlock::filterBlocks($filter, $pageNumber, $pageItems);

        if (is_array($blocks['result']) && $blocks['result']) {
            foreach ($blocks['result'] as $key => $block) {
                $blocks['result'][$key]['typeName'] = $this->module->getBlockTypeName($block['type']);
            }
        }

        $helper->listTotal = $blocks['total'];

        return $helper->generateList($blocks['result'], $fields_list);
    }

    public function renderBlockTypeForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Select Block Type'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Block Type'),
                        'name' => 'block_type',
                        'options' => [
                            'query' => [
                                [
                                    'id' => 'renderLatestFAQsForm',
                                    'name' => $this->module->l('Latest FAQs'),
                                ],
                                [
                                    'id' => 'renderOldFAQsForm',
                                    'name' => $this->module->l('Old FAQs'),
                                ],
                                [
                                    'id' => 'renderCategoryFaqsForm',
                                    'name' => $this->module->l('Category FAQs'),
                                ],
                                [
                                    'id' => 'renderCustomFAQsForm',
                                    'name' => $this->module->l('Custom FAQs'),
                                ],
                                [
                                    'id' => 'renderCategoriesForm',
                                    'name' => $this->module->l('Categories'),
                                ],
                                [
                                    'id' => 'renderSearchForm',
                                    'name' => $this->module->l('Search'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Next'),
                    'name' => 'submit_block_type_form',
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->tpl_vars = [
            'fields_value' => [
                'block_type' => 'left',
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderBlockEditForm($id_block)
    {
        $block = new HiFAQBlock($id_block);
        switch ($block->type) {
            case 'latest':
                return $this->renderLatestFAQsForm($id_block);
            case 'old':
                return $this->renderOldFAQsForm($id_block);
            case 'categoryFaqs':
                return $this->renderCategoryFaqsForm($id_block);
            case 'custom':
                return $this->renderCustomFAQsForm($id_block);
            case 'categories':
                return $this->renderCategoriesForm($id_block);
            case 'search':
                return $this->renderSearchForm($id_block);
            default:
                return $this->module->l('Edit form not found');
        }
    }

    public function getHooksQuery()
    {
        return [
            [
                'id' => 'displayHome',
                'name' => $this->module->l('Home'),
            ],
            [
                'id' => 'displayLeftColumn',
                'name' => $this->module->l('Left Column'),
            ],
            [
                'id' => 'displayRightColumn',
                'name' => $this->module->l('Right Column'),
            ],
            [
                'id' => 'displayFooter',
                'name' => $this->module->l('Footer'),
            ],
            [
                'id' => 'custom',
                'name' => $this->module->l('Custom'),
            ],
        ];
    }

    public function renderLatestFAQsForm($id_block = 0)
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Latest FAQs Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('FAQs Count to display'),
                        'name' => 'block_count',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display FAQs in accordion'),
                        'name' => 'accordion',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'accordion_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'accordion_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitLatestfaqs';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'fields_value' => $this->getLatestFAQsAddFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getLatestFAQsAddFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);

            return [
                'id_block' => $id_block,
                'block_type' => 'latest',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_count' => $block->count,
                'accordion' => $block->accordion,
                'block_position' => $block->hook,
                'actionType' => 'submitLatestFAQs',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'latest',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_count' => 10,
                'accordion' => true,
                'block_position' => 'displayHome',
                'actionType' => 'submitLatestFAQs',
            ];
        }
    }

    public function renderOldFAQsForm($id_block = 0)
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Old FAQs Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('FAQs Count to display'),
                        'name' => 'block_count',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display FAQs in accordion'),
                        'name' => 'accordion',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'accordion_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'accordion_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitLatestfaqs';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'fields_value' => $this->getOldFAQsAddFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getOldFAQsAddFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);

            return [
                'id_block' => $id_block,
                'block_type' => 'old',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_count' => $block->count,
                'accordion' => $block->accordion,
                'block_position' => $block->hook,
                'actionType' => 'submitOldFAQs',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'old',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_count' => 10,
                'accordion' => true,
                'block_position' => 'displayHome',
                'actionType' => 'submitOldFAQs',
            ];
        }
    }

    public function renderCategoryFaqsForm($id_block = 0)
    {
        $categories = HiFAQCategory::getCategories();
        $sortedCategories = [];
        array_push($sortedCategories, [
            'id' => 0,
            'name' => $this->l('-- Choose --'),
        ]);
        foreach ($categories as $category) {
            array_push($sortedCategories, [
                'id' => $category['id'],
                'name' => $category['name'],
            ]);
        }
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Category FAQs Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Select Category'),
                        'name' => 'block_category',
                        'options' => [
                            'query' => $sortedCategories,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('FAQs Count to display'),
                        'name' => 'block_count',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display FAQs in accordion'),
                        'name' => 'accordion',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'accordion_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'accordion_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitCategoryFAQs';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'psv' => $this->module->psv,
            'fields_value' => $this->getCategoryFAQsFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getCategoryFAQsFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);

            return [
                'id_block' => $id_block,
                'block_type' => 'categoryFaqs',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_count' => $block->count,
                'block_category' => $this->module->getBlockSetting($id_block, 'HI_FAQ_BLOCK_CATEGORY'),
                'accordion' => $block->accordion,
                'block_position' => $block->hook,
                'actionType' => 'submitCategoryFaqs',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'categoryFaqs',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_count' => 10,
                'block_category' => [],
                'accordion' => true,
                'block_position' => 'displayHome',
                'actionType' => 'submitCategoryFaqs',
            ];
        }
    }

    public function renderCustomFAQsForm($id_block = 0)
    {
        $faqs = HiFAQItem::getFAQs(true);
        $sorted_faqs = [];
        foreach ($faqs as $faq) {
            array_push($sorted_faqs, [
                'id' => $faq['id_faq'],
                'name' => $faq['title'],
            ]);
        }
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Custom FAQs Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Select FAQs'),
                        'multiple' => true,
                        'name' => 'block_faqs[]',
                        'id' => 'customFaqsBlockSelect',
                        'class' => 'form-cotrol',
                        'options' => [
                            'query' => $sorted_faqs,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display FAQs in accordion'),
                        'name' => 'accordion',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'accordion_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'accordion_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitCustomFAQs';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'psv' => $this->module->psv,
            'fields_value' => $this->getCustomFAQsFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getCustomFAQsFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);
            $sorted_faqs = [];
            $faqs = HiFAQBlock::getCustomFAQs($id_block);
            if ($faqs) {
                foreach ($faqs as $faq) {
                    $sorted_faqs[] = $faq['id_faq'];
                }
            }

            return [
                'id_block' => $id_block,
                'block_type' => 'custom',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_faqs[]' => $sorted_faqs,
                'accordion' => $block->accordion,
                'block_position' => $block->hook,
                'actionType' => 'submitCustomFAQs',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'custom',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_faqs[]' => [],
                'accordion' => true,
                'block_position' => 'displayHome',
                'actionType' => 'submitCustomFAQs',
            ];
        }
    }

    public function renderCategoriesForm($id_block = 0)
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Categories Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Categories Count to display'),
                        'name' => 'block_count',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitCategoriesBlock';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'psv' => $this->module->psv,
            'fields_value' => $this->getCategoriesFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getCategoriesFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);

            return [
                'id_block' => $id_block,
                'block_type' => 'categories',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_count' => $block->count,
                'block_position' => $block->hook,
                'actionType' => 'submitCategoriesBlock',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'categories',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_count' => 10,
                'block_position' => 'displayLeftColumn',
                'actionType' => 'submitCategoriesBlock',
            ];
        }
    }

    public function renderSearchForm($id_block = 0)
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Search Block'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_block',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'block_type',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'actionType',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Active'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'placeholder' => $this->module->l('Enter block title'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display Title'),
                        'name' => 'block_title_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'block_title_active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'block_title_active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Position to display'),
                        'name' => 'block_position',
                        'options' => [
                            'query' => $this->getHooksQuery(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'shop',
                        'label' => $this->l('Assign the Block to these shops'),
                        'name' => 'checkBoxShopAsso',
                    ],
                ],
                'submit' => [
                    'title' => $id_block ? $this->module->l('Update') : $this->module->l('Add'),
                    'name' => 'submitSaveFaqBlock',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'submit_cancel_block',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitSearchBlock';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this->module;
        $helper->table = 'hifaqblock';
        $helper->identifier = 'id_block';
        $helper->id = $id_block;
        $helper->tpl_vars = [
            'psv' => $this->module->psv,
            'fields_value' => $this->getSearchFieldsValues($id_block),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getSearchFieldsValues($id_block)
    {
        if ($id_block) {
            $block = new HiFAQBlock($id_block);

            return [
                'id_block' => $id_block,
                'block_type' => 'search',
                'block_active' => $block->active,
                'block_title_active' => $block->title_active,
                'block_title' => $block->title,
                'block_position' => $block->hook,
                'actionType' => 'submitSearchBlock',
            ];
        } else {
            $empty_array = [];
            foreach (Language::getLanguages(false) as $lang) {
                $empty_array[$lang['id_lang']] = '';
            }

            return [
                'id_block' => 0,
                'block_type' => 'search',
                'block_active' => true,
                'block_title_active' => true,
                'block_title' => $empty_array,
                'block_position' => 'displayLeftColumn',
                'actionType' => 'submitSearchBlock',
            ];
        }
    }

    public function renderRelatedCategories($id_faq)
    {
        $selected_categories = $this->module->getRelatedCategories($id_faq);
        if (!is_array($selected_categories) || !$selected_categories) {
            $selected_categories = [];
        }

        $selected_categories = array_map('current', $selected_categories);

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Assign Categories'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_faq',
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->l('Select Categories'),
                        'name' => 'categories',
                        'tree' => [
                            'id' => 'faq_categories',
                            'use_checkbox' => true,
                            'selected_categories' => $selected_categories,
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save'),
                    'name' => 'submitRelatedCategoriesForm',
                    'icon' => 'icon-save',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
                        'name' => 'closeModalButton',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitRelatedCategoriesForm';
        $helper->currentIndex = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->tpl_vars = [
            'fields_value' => [
                'id_faq' => $id_faq,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderDesignSettingsForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Design Settings'),
                    'icon' => 'icon-paint-brush',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Search block background color'),
                        'name' => 'searchBgColor',
                        'class' => 'hi-color-picker',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS'),
                        'name' => 'customCss',
                        'cols' => 100,
                        'rows' => 10,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submitDesignSettingsForm',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->submit_action = 'submitDesignSettingsForm';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=designSettings';
        $helper->module = $this->module;
        $helper->tpl_vars = [
            'fields_value' => [
                'searchBgColor' => $this->module->searchBgColor,
                'customCss' => $this->module->customCss,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderFeedbackForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Feedback Settings'),
                    'icon' => 'icon-comment',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable FAQs feedback'),
                        'name' => 'feedback',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'feedback_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'feedback_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Feedback form position'),
                        'name' => 'feedbackPosition',
                        'desc' => $this->l('For FAQ details page only'),
                        'options' => [
                            'query' => [
                                [
                                    'id' => 1,
                                    'name' => $this->l('Fixed (Always Visible)'),
                                ],
                                [
                                    'id' => 2,
                                    'name' => $this->l('Embedded in page'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Feedback for accordions'),
                        'name' => 'feedbackAccordion',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'feedbackAccordion_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'feedbackAccordion_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display Feedbacks count'),
                        'name' => 'feedbacksCount',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'feedbacksCount_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'feedbacksCount_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submitFeedbackForm',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->submit_action = 'submitFeedbackForm';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        ) . '&configure=' . $this->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->name . '&' . $this->name . '=feedbackSettings';
        $helper->module = $this->module;
        $helper->tpl_vars = [
            'fields_value' => [
                'feedback' => $this->module->feedback,
                'feedbackPosition' => $this->module->feedbackPosition,
                'feedbackAccordion' => $this->module->feedbackAccordion,
                'feedbacksCount' => $this->module->feedbacksCount,
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function renderFeedbackList($filter = [], $pageItems = 50, $pageNumber = 1)
    {
        if (!(int) $pageItems) {
            $pageItems = 50;
        }
        if (!(int) $pageNumber) {
            $pageNumber = 1;
        }

        $fields_list = [
            'title' => [
                'title' => $this->l('FAQ'),
                'width' => 140,
                'type' => 'text',
                'search' => true,
            ],
            'id_customer' => [
                'title' => $this->l('ID Customer'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ],
            'ip_address' => [
                'title' => $this->l('IP Address'),
                'width' => 140,
                'search' => false,
            ],
            'feedback' => [
                'title' => $this->l('Feedback'),
                'width' => 140,
                'type' => 'select',
                'search' => true,
                'filter_key' => 'faqFeedbackStatus',
                'list' => [
                    1 => $this->l('Good'),
                    0 => $this->l('Bad'),
                ],
            ],
            'comment' => [
                'title' => $this->l('Comment'),
                'width' => 140,
                'search' => false,
            ],
            'date_add' => [
                'title' => $this->l('Date Created'),
                'type' => 'date',
                'search' => false,
            ],
        ];
        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->actions = ['delete'];
        $helper->identifier = 'id_feedback';
        $helper->show_toolbar = false;
        $helper->title = $this->l('Feedbacks');
        $helper->table = 'hifaqfeedback';
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&' . $this->name . '=feedbackList';
        $feedbacks = HiFAQFeedback::filterFeedbacks($filter, $pageNumber, $pageItems);

        $helper->listTotal = $feedbacks['total'];

        return $helper->generateList($feedbacks['result'], $fields_list);
    }

    public function l($string)
    {
        return $this->module->l($string, 'FAQadminForms');
    }
}
