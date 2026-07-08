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

include_once(dirname(__FILE__).'/classes/EgBannerTwoClass.php');

class EgBannerTwo extends Module {

    protected $_html = '';
    protected $templateFile;
    protected $domain;

    public function __construct()
    {
        $this->name = 'egbannertwo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->domain = 'Modules.Egbannertwo.Egbannertwo';
        $this->displayName = $this->trans('Multiple banners 2', array(), $this->domain);
        $this->description = $this->trans('Display banners category in home page With Two Links', array(), $this->domain);

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', array(), $this->domain);
        $this->img_path = $this->_path.'views/img/';
        $this->templateFile = 'module:egbannertwo/views/templates/hook/egbanner.tpl';
    }
        
    /**
     * Create Tab 
    */

    public function installTabs()
    {
        $installTabCompleted = true;

        foreach ($this->getTabs() as $tab) {
            try {
                $installTabCompleted = $installTabCompleted && $this->installTab(
                    $tab['className'],
                    $tab['parent'],
                    $tab['name'],
                    $tab['module'],
                    $tab['active'],
                    $tab['icon']
                );
            } catch (Exception $e) {  
                return false;
            }
        }

        return $installTabCompleted;
    }
    public function uninstallTabs()
    {
        $uninstallTabCompleted = true;

        foreach ($this->getTabs() as $tab) {
            try {
                $uninstallTabCompleted = $installTabCompleted && $this->uninstallTab(
                    $tab['className']
                );
            } catch (Exception $e) {  
                return false;
            }
        }

        return $uninstallTabCompleted;
    }
    public function installTab($className, $parent, $name, $module, $active, $icon)
    {
        if (Tab::getIdFromClassName($className)) {
            return true;
        }

        $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

        $moduleTab = new Tab();
        $moduleTab->class_name = $className;
        $moduleTab->id_parent = $idParent;
        $moduleTab->module = $module;
        $moduleTab->active = $active;
        if (property_exists($moduleTab, 'icon')) {
            $moduleTab->icon = $icon;
        }

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $moduleTab->name[$language['id_lang']] = $name;
        }

        return $moduleTab->add();
    }
    /**
     * Remove Tabs module in Dashboard
    * @param $class_name string name Tab
    * @return bool
    * @throws
    * @throws
    */
    public function uninstallTab($class_name)
    {
        if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            return $tab->delete();
        } else {
            return false;
        } 
    }
    public function getTabs()
    {
        return [
            [
                'className' => 'AdminEgBannerTwoGeneral',
                'parent' => 'CONFIGURE',
                'name' => $this->l('HP banners 2'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
            [
                'className' => 'AdminEgConfBannerTwo',
                'parent' => 'AdminEgBannerTwoGeneral',
                'name' => $this->l('Configure'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
            [
                'className' => 'AdminEgBannerTwo',
                'parent' => 'AdminEgBannerTwoGeneral',
                'name' => $this->l('Manage banners'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
        ];
        
    }

    /**
     * @see Module::install()
    */

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        $this->installTabs();
        return parent::install()
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
        $this->uninstallTabs();
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
                'linkConfigBanner' => $this->context->link->getAdminLink('AdminEgConfBannerTwo'),
                'linkManageBanner' => $this->context->link->getAdminLink('AdminEgBannerTwo'),
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
            $banners = EgBannerTwoClass::getBannerFromHook($limit);
            // Skip cache-write when there is nothing to display.
            // Smarty's per-module cache_lifetime is 1 year — caching an empty render
            // (no active banner, or status disabled, or expired date filter) would freeze
            // an empty homepage block for the whole TTL even after data becomes valid again.
            if (empty($banners) || (int)$status !== 1) {
                return '';
            }
            foreach ($banners as &$banner) {
                $banner['category_name'] = EgBannerTwoClass::getNameCategoryById($banner['id_category']);
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
                    'title' => $this->trans('Configurer la bannière', array(), $this->domain),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Nombre de bannières à afficher', array(), $this->domain),
                        'name' => 'EG_COUNT_BANNER',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Bloquer le titre des bannières', array(), $this->domain),
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
