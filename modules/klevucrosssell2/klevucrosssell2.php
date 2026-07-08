<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class KlevuCrossSell2 extends Module
{
    public function __construct()
    {
        $this->name = 'klevucrosssell2';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Klevu Cross Sell Products 2');
        $this->description = $this->l('Displays a cross sell text field in displayFooterProduct.');
    }

    public function install()
    {
        $this->installTabs();
        return parent::install() &&
            $this->registerHook('displayFooterProduct') &&
            Configuration::updateValue('KLEVU_CROSSSELL2_TEXT', '');
    }

    public function uninstall()
    {
        $this->uninstallTabs(); 
        return parent::uninstall() &&
            Configuration::deleteByName('KLEVU_CROSSSELL2_TEXT');
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
                'className' => 'AdminKlevuCrossSell2',
                'parent' => 'CONFIGURE',
                'name' => $this->l('Klevu Cross Sell Products 1'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
        ];
        
    }
    public function getContent()
    {
        if (Tools::isSubmit('submitAddconfiguration')) { 
            // Handle form submission
            $title = [];
            foreach (Language::getLanguages(false) as $lang) {
                $title[$lang['id_lang']] = Tools::getValue('KLEVU_CROSSSELL2_TEXT_' . $lang['id_lang']);
            }
            foreach ($title as $id_lang => $value) { 
                Configuration::updateValue('KLEVU_CROSSSELL2_TEXT_'.$id_lang, $value, true); // Save multilingual title
            }
            $this->context->controller->confirmations[] = $this->l('Settings updated');
        }
        return $this->renderForm();
    }

    protected function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Cross Sell Text'),
                        'name' => 'KLEVU_CROSSSELL2_TEXT',
                        'lang' => true, // Enable multilingual input
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        // Set multilingual values for the title field
        foreach (Language::getLanguages(false) as $lang) {
            $title = Configuration::get('KLEVU_CROSSSELL2_TEXT_'.$lang['id_lang']);
            $helper->fields_value['KLEVU_CROSSSELL2_TEXT'][$lang['id_lang']] = $title ?? '';
        }
        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayFooterProduct($params)
    {
        $id_lang = (int)$this->context->language->id;
        $text = Configuration::get('KLEVU_CROSSSELL2_TEXT_'.$id_lang);
        if ($text) {
            return '<div class="klevu-crosssell2">'.$text.'</div>';
        }
        return '';
    }
}
