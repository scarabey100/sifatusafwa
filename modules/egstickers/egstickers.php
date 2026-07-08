<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once  _PS_MODULE_DIR_ .'/egstickers/classes/EgStickersFlags.php';
class EgStickers extends Module
{
    /**
     * @var Module Reference to the module instance
     */
    public $module;

    public function __construct()
    {
        $this->module = $this; // Assign the current instance to the module property
        $this->name = 'egstickers';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EG Stickers');
        $this->description = $this->l('Manage and display stickers for products.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    { 
        include(dirname(__FILE__).'/sql/install.php');
        $this->installTabs(); 
        return parent::install()
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayProductFlags')
            && $this->registerHook('displayNativeStickers')
            && $this->registerHook('displayBackofficeHeader')
            && $this->registerHook('displayAdminProductsExtra'); // Registering the hook
           
    } 

    public function uninstall()
    { 
        //include(dirname(__FILE__).'/sql/uninstall.php');
        $this->uninstallTabs(); 
        return parent::uninstall() ;
    }
 
    public function hookDisplayBackofficeHeader($params) 
    {   
        $this->context->controller->addJqueryPlugin('alerts');
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->addJqueryPlugin('alerts');
        $this->context->controller->addJqueryUI('ui.sortable');
         
        $this->context->controller->addJS($this->_path.'views/js/back.js');  
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
                $uninstallTabCompleted = $uninstallTabCompleted && $this->uninstallTab(
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
                'className' => 'AdminEgStickersGeneral',
                'parent' => 'CONFIGURE',
                'name' => $this->l('Stickers'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
            [
                'className' => 'AdminEgStickers',
                'parent' => 'AdminEgStickersGeneral',
                'name' => $this->l('Custom Stickers'),
                'module' => $this->module->name,
                'active' => true,
                'icon' => 'extension',
            ],
            [
                'className' => 'AdminEgStickersConfig',
                'parent' => 'AdminEgStickersGeneral',
                'name' => $this->l('Native Stickers'),
                'module' => $this->module->name,
                'active' => true,
                'icon' => 'settings',
            ],
        ];
    }
    public function hookDisplayProductFlags($params)
    {
        $id_product = (int)$params['id_product'];

        // Fetch all stickers
        $stickers = Db::getInstance()->executeS('
            SELECT s.*, sl.name, ps.id_product 
            FROM `' . _DB_PREFIX_ . 'egstickers` s
            LEFT JOIN `' . _DB_PREFIX_ . 'egstickers_lang` sl 
            ON s.id_sticker = sl.id_sticker 
            AND sl.id_lang = ' . (int)$this->context->language->id . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_sticker` ps 
            ON s.id_sticker = ps.id_sticker 
            WHERE ps.id_product = ' . (int)$id_product . '
        ');
 
        // Assign variables to the template
        $this->context->smarty->assign([
            'stickers' => $stickers, 
        ]);

        // Render the template without checkboxes
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'egstickers/views/templates/hook/product_flags.tpl');
    }

    public function hookActionProductUpdate($params)
    {
        $id_product = (int)$params['id_product'];
        $selected_stickers = Tools::getValue('stickers');

        // Remove existing stickers for the product
        Db::getInstance()->delete('product_sticker', 'id_product = ' . $id_product);

        // Add selected stickers
        if (is_array($selected_stickers)) {
            foreach ($selected_stickers as $id_sticker) {
                Db::getInstance()->insert('product_sticker', [
                    'id_product' => $id_product,
                    'id_sticker' => (int)$id_sticker,
                ]);
            }
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)$params['id_product'];

        // Fetch all stickers
        $stickers = Db::getInstance()->executeS('
            SELECT s.*, sl.name 
            FROM `' . _DB_PREFIX_ . 'egstickers` s
            LEFT JOIN `' . _DB_PREFIX_ . 'egstickers_lang` sl 
            ON s.id_sticker = sl.id_sticker 
            AND sl.id_lang = ' . (int)$this->context->language->id . '
        ');

        // Fetch selected stickers for the product
        $selected_stickers = Db::getInstance()->executeS('SELECT id_sticker FROM `' . _DB_PREFIX_ . 'product_sticker` WHERE id_product = ' . $id_product);
        $selected_sticker_ids = array_column($selected_stickers, 'id_sticker');

        // Assign variables to the template
        $this->context->smarty->assign([
            'stickers' => $stickers,
            'selected_sticker_ids' => $selected_sticker_ids,
        ]);
 
        // Render the template
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'egstickers/views/templates/admin/product_stickers.tpl');
    }

    public function hookDisplayNativeStickers($params)
    { 
    }
}