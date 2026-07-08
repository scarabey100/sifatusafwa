<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from SARL DREAM ME UP
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL DREAM ME UP is strictly forbidden.
 *
 *   .--.
 *   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
 *   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
 *   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
 *        w w w . d r e a m - m e - u p . f r       '
 *
 *  @author    Dream me up <prestashop@dream-me-up.fr>
 *  @copyright 2007 - 2025 Dream me up
 *  @license   All Rights Reserved
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class DmuAssocProdCat extends Module
{
    protected $menu_parent;
    protected $menu_controller;
    protected $menu_name;
    protected $menu_name_fr;
    protected $description_complete;
    protected $comment_acceder;
    protected $config_tabs;

    public function __construct()
    {
        $this->name = 'dmuassocprodcat';
        $this->tab = 'administration';
        $this->version = '4.0.0';
        $this->author = 'Dream me up';
        $this->module_key = '13f1a6b1b6d40f6a7bd1a868982d7471';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Products / Categories Association');
        $this->description = $this->l('This module allows you to manage the association products/categories.');
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->menu_parent = 'AdminCatalog';
        $this->menu_controller = 'AdminDmuAssocProdCat';
        $this->menu_name = 'Categories associations';
        $this->menu_name_fr = 'Associations catégories';

        // Description détaillée du module pour l'onglet configuration
        $this->description_complete = $this->l('This module allows you to manage the association products/categories.');

        // Comment accéder au module ?
        $this->comment_acceder = $this->l('To use this addon, menu Catalog > Categories assocations');

        // Onglets à afficher dans la configuration (mettre un tableau vide si pas de config)
        $this->config_tabs = [
            'Configuration1' => [
                'name' => $this->l('Configuration'),
                'is_helper' => true,
            ],
        ];
    }

    /* Configuration du module */
    public function getContent()
    {
        // Permet d'externaliser les traitements dans une autre fonction
        $this->postProcess();

        $documentation_lang = 'en';
        if ('fr' == $this->context->language->iso_code) {
            $documentation_lang = $this->context->language->iso_code;
        }

        // Liste de onglets
        $tabs = [];
        foreach (array_keys($this->config_tabs) as $key) {
            $tabs[$key] = $this->getConfigurationForm($key);
        }

        $this->context->smarty->assign(
            [
                'config_tabs' => $this->config_tabs,
                'version_prestashop' => _PS_VERSION_,
                'version_module' => $this->version,
                'nom_module' => $this->displayName,
                'description_complete' => $this->description_complete,
                'comment_acceder' => $this->comment_acceder,
                'path_module' => Context::getContext()->shop->getBaseURL(true) . '/modules/' . $this->name,

                'form_id' => (('' != Tools::getValue('form_id')) ? Tools::getValue('form_id') : ''),

                'path_documentation' => 'readme_' . $documentation_lang . '.pdf',

                // Textes traductibles à ne pas modifier
                'txt_module_version' => $this->l('Module version'),
                'txt_howto' => $this->l('How to use this module ?'),
                'txt_click_here' => $this->l('Click here to access the menu'),
                'txt_qui' => $this->l('Who are we ?'),
                'txt_dmu' => $this->l('Dream me up...'),
                'txt_notre_site' => $this->l('Our website'),
                'txt_notre' => $this->l('Our'),
                'txt_page' => $this->l('Prestashop Partner dedicated page'),
                'txt_decouvrez' => $this->l('Discover all our modules on our'),
                'txt_addons_page' => $this->l('Prestashop Addons dedicated page'),
                'txt_support' => $this->l('Support and Documentation'),
                'txt_open_doc' => $this->l('Click Here to open the module documentation'),
                'txt_support_only' => $this->l('The support of our modules is done exclusively'),
                'txt_rdv' => $this->l('Visit the module\'s page concerned and use the link "Contact the Developer"'),
                'txt_interm' => $this->l('through Prestashop Addons'),
                'txt_mention' => $this->l('You must mention'),
                'txt_desc_problem' => $this->l('A detailed description of the problem'),
                'txt_version_presta' => $this->l('Your Prestashop Version'),
                'txt_version_module' => $this->l('Your Module Version'),
                'txt_follow' => $this->l('Follow us'),
                'txt_follow_our' => $this->l('Follow our'),
                'txt_on' => $this->l('on'),
                'txt_and' => $this->l('and'),
                'txt_know_actu' => $this->l('to know all the news around our Addons'),
                'txt_to_have_details' => $this->l('to have all details on our new Addons versions and for every new launch of Addon'),

                // Liens
                'lnk_page_prestashop' => $this->l('http://addons.prestashop.com/en/9_dream-me-up'),

                'content_html' => $tabs,
            ]
        );

        $tpl_name = 'configure.tpl';

        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/' . $tpl_name);
    }

    /* Fonction à personnaliser en fonction des besoins en configuration */
    public function getConfigurationForm($form_id = '')
    {
        $data = [];
        $data['form_id'] = $form_id;
        $data['DMUASSOCPRODCAT_INACTIVE_PRODUCTS'] = Configuration::get('DMUASSOCPRODCAT_INACTIVE_PRODUCTS');

        $helper = new HelperForm();

        $fields_form = [];
        $fields_form['form'] = [];

        // Champs du formulaire
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $fields_form['form']['legend'] = [
                'title' => $this->l('Module configuration'),
                'icon' => 'icon-cogs',
            ];
        }

        $fields_form['form']['input'] = [
            [
                'type' => 'hidden',
                'name' => 'form_id',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('All products'),
                'name' => 'DMUASSOCPRODCAT_INACTIVE_PRODUCTS',
                'required' => false,
                'is_bool' => true,
                'values' => [
                    ['id' => 'DMUASSOCPRODCAT_INACTIVE_PRODUCTS', 'value' => 1, 'label' => ''],
                    ['id' => 'DMUASSOCPRODCAT_INACTIVE_PRODUCTS', 'value' => 0, 'label' => ''],
                ],
                'desc' => $this->l('Considers inactive products'),
            ],
        ];

        $fields_form['form']['submit'] = [
            'title' => $this->l('Save'),
            'name' => 'submitConfig',
            'class' => (version_compare(_PS_VERSION_, '1.6', '>=') ? 'btn btn-default pull-right' : 'button'),
        ];

        $helper = $this->createHelperForm($data);

        return $this->postProcess() . $helper->generateForm([$fields_form]);
    }

    public function createHelperForm($data)
    {
        $helper_form = new HelperForm();
        $helper_form->show_toolbar = false;
        $helper_form->table = $this->table;

        $helper_form->identifier = $this->identifier;
        $helper_form->submit_action = $data['form_id'];
        $helper_form->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
        '&configure=' . $this->name .
        '&tab_module=' . $this->tab .
        '&module_name=' . $this->name;
        $helper_form->token = Tools::getAdminTokenLite('AdminModules');
        $helper_form->tpl_vars = [
            'fields_value' => $data,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper_form;
    }

    /*  Gestion des processus en post */
    public function postProcess()
    {
        if (Tools::isSubmit('submitConfig')) {
            Configuration::updateValue(
                'DMUASSOCPRODCAT_INACTIVE_PRODUCTS',
                Tools::getValue('DMUASSOCPRODCAT_INACTIVE_PRODUCTS')
            );

            return $this->displayConfirmation($this->l('Configuration updated'));
        } else {
            return '';
        }
    }

    public function install()
    {
        // Installation du module
        if (!parent::install()) {
            return false;
        }

        $id_lang = Language::getIdByIso('en');
        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }
        $id_lang_fr = Language::getIdByIso('fr');
        $id_tab = self::getIdTab($this->menu_parent);

        // Initialisation de la variable de configuration
        Configuration::updateValue('DMUASSOCPRODCAT_INACTIVE_PRODUCTS', '0');

        // Installation d'un onglet admin
        if (!$this->installModuleTab(
            $this->menu_controller,
            [$id_lang => $this->menu_name, $id_lang_fr => $this->menu_name_fr],
            $id_tab
        )) {
            return false;
        }

        if (!$this->registerHook('displayBackOfficeHeader')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        // Désinstallation d'un onglet en admin
        $id_parent = $this->getIdTab($this->menu_parent);
        $this->uninstallModuleTab($this->menu_controller, $id_parent);

        // Suppression de la variable de configuration
        Configuration::deleteByName('DMUASSOCPRODCAT_INACTIVE_PRODUCTS');

        // Désinstallation du module
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function getIdTab($tab_class)
    {
        return Db::getInstance()->getValue('
            SELECT id_tab 
            FROM ' . _DB_PREFIX_ . "tab 
            WHERE class_name = '" . pSQL($tab_class) . "'");
    }

    private function installModuleTab($tab_class, $tab_name, $id_tab_parent)
    {
        $tab = new Tab();

        // Supprime l'ancienne tab (fix bug maj)
        // $this->uninstallModuleTab('admindmuassocprodcat', $id_tab_parent);

        $id_lang = Language::getIdByIso('en');
        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }
        $langues = Language::getLanguages(false);
        foreach ($langues as $langue) {
            if (!isset($tab_name[$langue['id_lang']])) {
                $tab_name[$langue['id_lang']] = $tab_name[$id_lang];
            }
        }

        $tab->name = $tab_name;
        $tab->class_name = $tab_class;
        $tab->module = $this->name;
        $tab->id_parent = $id_tab_parent;
        $id_tab = $tab->save();
        if (!$id_tab) {
            return false;
        }

        $this->installcleanPositions($tab->id, $id_tab_parent);

        return true;
    }

    private function uninstallModuleTab($tab_class, $id_tab_parent)
    {
        $id_tab = Tab::getIdFromClassName($tab_class);
        if (0 != $id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
            $this->uninstallcleanPositions($id_tab_parent);

            return true;
        }

        return false;
    }

    public function installcleanPositions($id, $id_parent)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT `id_tab`,`position`
        FROM `' . _DB_PREFIX_ . 'tab`
        WHERE `id_parent` = ' . (int) $id_parent . '
        AND `id_tab` != ' . (int) $id . '
        ORDER BY `position`');
        $sizeof = count($result);
        for ($i = 0; $i < $sizeof; ++$i) {
            Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'tab`
            SET `position` = ' . ((int) $result[$i]['position'] + 1) . '
            WHERE `id_tab` = ' . (int) $result[$i]['id_tab']);
        }

        return true;
    }

    public function uninstallcleanPositions($id_parent)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT `id_tab`
        FROM `' . _DB_PREFIX_ . 'tab`
        WHERE `id_parent` = ' . (int) $id_parent . '
        ORDER BY `position`');
        $sizeof = count($result);
        for ($i = 0; $i < $sizeof; ++$i) {
            Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'tab`
            SET `position` = ' . ($i + 1) . '
            WHERE `id_tab` = ' . (int) $result[$i]['id_tab']);
        }

        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == $this->menu_controller) {
            $this->context->controller->addJqueryUI(['ui.tooltip']);
            $this->context->controller->addJqueryPlugin('tablednd');
            $this->context->controller->addCss($this->_path . 'views/css/tablednd.css');
        }
    }
}
