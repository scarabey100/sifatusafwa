<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__).'/classes/EgCategoryLancerClass.php');

class EgCategoryLancer extends Module {

    protected $_html = '';
    protected $templateFile;
    protected $domain;

    public function __construct()
    {
        $this->name = 'egcategorylancer';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->domain = 'Modules.EgCategorylancer.EgCategorylancer';
        $this->displayName = $this->trans('Lanceurs categories', array(), $this->domain);
        $this->description = $this->trans('Liens personalisé in home page avec multiples links', array(), $this->domain);

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', array(), $this->domain);
        $this->img_path = $this->_path.'views/img/';
        $this->templateFile = 'module:egcategorylancer/views/templates/hook/egcategorylancer.tpl';
    }

    /**
     * @see  CREATE TAB module in Dashboard
     */
    public function createTabs()
    {
        $idParent = (int) Tab::getIdFromClassName('AdminEgDigital');
        if (empty($idParent)) {
            $parent_tab = new Tab();
            $parent_tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $parent_tab->name[$lang['id_lang']] = $this->trans('Modules EGIO', array(), $this->domain);
            }
            $parent_tab->class_name = 'AdminEgDigital';
            $parent_tab->id_parent = 0;
            $parent_tab->module = $this->name;
            $parent_tab->icon = 'library_books';
            $parent_tab->add();
        }

        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Lanceurs categories', array(), $this->domain);
        }
        $tab->class_name = 'AdminEgCategoryLanceGeneral';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgDigital');
        $tab->module = $this->name;
        $tab->icon = 'library_books';
        $tab->add();

        // Menage Module
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Config', array(), $this->domain);
        }
        $tab->class_name = 'AdminEgConfCategoryLancer';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminEgCategoryLanceGeneral');
        $tab->module = $this->name;
        $tab->add();

        // Menage Banner
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Lanceurs', array(), $this->domain);
        }
        $tab->class_name = 'AdminEgCategoryLancer';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgCategoryLanceGeneral');
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

    /**
     * Remove Tabs module in Dashboard
     * @param $class_name string name Tab
     * @return bool
     * @throws
     * @throws
     */
    public function removeTabs($class_name)
    {
        if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install()
            && $this->createTabs()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayHome');
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        $this->removeTabs('AdminEgConfCategoryLancer');
        $this->removeTabs('AdminEgCategoryLanceGeneral');
        $this->removeTabs('AdminEgCategoryLancer');
        return parent::uninstall();
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/egbanner.css');
    }

    public function renderList()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminModules');
        $idEmployee = (int) $this->context->employee->id;
        $token = Tools::getAdminToken('AdminModules'.$idTab.$idEmployee);
        $this->context->smarty->assign(
            array(
                'linkConfigBanner' => $this->context->link->getAdminLink('AdminEgConfCategoryLancer'),
                'linkManageBanner' => $this->context->link->getAdminLink('AdminEgCategoryLancer'),
            )
        );
        $template = _PS_MODULE_DIR_ . $this->name .'/views/templates/admin/_configure/helpers/list/list_header.tpl';
        return $this->context->smarty->fetch($template);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    public function clearCache()
    {
        $this->_clearCache($this->templateFile);
    }

    public function hookDisplayHome()
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId($this->name))) {
            $count = (int)Configuration::get('EG_COUNT_BANNER');
            $title_banner = Configuration::get('EG_TITLE_BANNER');
            $limit = isset($count) ? $count : null;
            $status = Configuration::get('EG_BANNER_STATUS');
            $banners = EgCategoryLancerClass::getBannerFromHook($limit);
            foreach ($banners as &$banner) {
                $banner['category_name'] = EgCategoryLancerClass::getNameCategoryById($banner['id_category']);
                $banner['category_link'] = $this->context->link->getCategoryLink($banner['id_category']);
                $banner['type'] = $banner['type'];
            }
            $this->context->smarty->assign(array(
                'banners' => $banners,
                'status' => $status,
                'uri' => $this->img_path,
                'title_banner' => $title_banner
            ));
        }
        return $this->fetch($this->templateFile, $this->getCacheId($this->name));
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('EG_COUNT_BANNER', Tools::getValue('EG_COUNT_BANNER'));
            Configuration::updateValue('EG_BANNER_STATUS', Tools::getValue('EG_BANNER_STATUS'));
            Configuration::updateValue('EG_TITLE_BANNER', Tools::getValue('EG_TITLE_BANNER'));
        }

        $this->_html .= $this->renderList();
        $this->_html .= $this->renderForm();
        return $this->_html;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return array(
            'EG_COUNT_BANNER' => Configuration::get('EG_COUNT_BANNER'),
            'EG_TITLE_BANNER' => Configuration::get('EG_TITLE_BANNER'),
            'EG_BANNER_STATUS' => Configuration::get('EG_BANNER_STATUS'),
        );
    }

    /**
     * @return array
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->trans('Configuration', array(), $this->domain),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Nombre de categories à afficher', array(), $this->domain),
                        'name' => 'EG_COUNT_BANNER',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Titre du bloc', array(), $this->domain),
                        'name' => 'EG_TITLE_BANNER',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Affichage', array(), $this->domain),
                        'name' => 'EG_BANNER_STATUS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Activé', array(), $this->domain)
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Désactivé', array(), $this->domain)
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Sauvegarder', array(), $this->domain),
                ),
            ),
        );
    }
}
