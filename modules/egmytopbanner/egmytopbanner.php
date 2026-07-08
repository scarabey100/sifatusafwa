<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__) . '/classes/EgMyTopBannerObjectClass.php');

class Egmytopbanner extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'egmytopbanner';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Egio Digital';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Egio My Top Banner');
        $this->description = $this->l('this module allow you to add custom banner shows in top header');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('EGMYTOPBANNER_LIVE_MODE', false);
        include(dirname(__FILE__).'/sql/install.php');
        $this->installTabs();
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayNav1');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EGMYTOPBANNER_LIVE_MODE');
        $this->uninstallTabs();
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }
    
    public function enable($force_all = false)
    {
        
        return parent::enable($force_all)
            && $this->installTabs()  
        ;
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTabs()
        ;
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
                'className' => 'AdminEgmytopBanner',
                'parent' => 'CONFIGURE',
                'name' => $this->l('Egio top Header'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
        ];
        
    }
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'EGMYTOPBANNER_LIVE_MODE' => Configuration::get('EGMYTOPBANNER_LIVE_MODE', true),
            'EGMYTOPBANNER_ACCOUNT_EMAIL' => Configuration::get('EGMYTOPBANNER_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'EGMYTOPBANNER_ACCOUNT_PASSWORD' => Configuration::get('EGMYTOPBANNER_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayBanner()
    {
         $elements = EgMyTopBannerObjectClass::getAllBanners();
        
         $this->context->smarty->assign([
            'elements' =>  $elements , 
        ]);
      
       return $this->display(__FILE__,"egtopbanner.tpl");
     
    }

    public function hookDisplayNav1()
    {
         $elements = EgMyTopBannerObjectClass::getAllBanners();
         $this->context->smarty->assign([
            'elements' =>  $elements ,
        ]);

       return $this->display(__FILE__,"egtopbanner.tpl");

    }
}
