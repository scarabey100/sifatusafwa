<?php
/**
 * 2019 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/classes/EgWishListProduct.php';

class EgWishList extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->module = $this; // Assign the current instance to the module property
        $this->name = 'egwishlist';
        $this->version = '1.0.2';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->tab = 'front_office_features';
        $this->secure_key = Tools::encrypt($this->name);
        $this->controllers = array('view');

        parent::__construct();
        $this->displayName = $this->trans('EG wishlist', [], "Modules.Egwishlist.Admin");
        $this->description = $this->trans('Allow customers to create Short List which can share.', [], "Modules.Egwishlist.Admin");

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        $this->installTabs(); 
        return (parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayNav2')
            && $this->registerHook('displayTop')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionCustomerLogoutBefore')
            && $this->registerHook('actionAuthenticationBefore')
            && $this->registerHook('displayShortList')
            && $this->registerHook('displayWishList')
            && $this->registerHook('displayAfterProductAddCartBtn')
            && $this->registerHook('displayProductAccessoryInfo')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayProductListFunctionalButtons')
            && $this->registerHook('displayEgShortHover')
            && $this->registerHook('displayEgShortListButtons')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('registerGDPRConsent')
            && $this->registerHook('actionDeleteGDPRCustomer')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('displayOneProduct')
        );
    }

    public function uninstall()
    {
        //include(dirname(__FILE__).'/sql/uninstall.php');
        $this->uninstallTabs(); 
        return parent::uninstall();
    }

    public function hookActionCustomerLogoutBefore()
    {
        Context::getContext()->cookie->bcm_short_list = '';
    }
    public function getCookieProducts()
    {
        $this->context->cookie->bcm_short_list = trim($this->context->cookie->bcm_short_list);
        $this->context->cookie->bcm_short_list = trim($this->context->cookie->bcm_short_list, ',');
        if ($this->context->cookie->bcm_short_list != '') {
            $egListed = explode(',', $this->context->cookie->bcm_short_list);
        } else {
            $egListed = array();
        }
        return $egListed;
    }
    private function addCookieProductsToWishlist()
    {
        // Fetch the products from the cookie
        $products = $this->getCookieProducts();
        
        // Get customer ID and shop ID
        $customerId = (int)$this->context->customer->id;
        $idShop = (int)$this->context->shop->id; // Get current shop ID
       
        foreach ($products as $product) {
            // Ensure that both id_product and id_product_attribute are present in the cookie data
            if (isset($product)) {
                // Check if product is already in the wishlist to avoid duplicates
                if (!$this->isProductInWishlist($product, $customerId, $idShop)) {
                    // Add product to ps_egwishlist_product table
                    Db::getInstance()->insert('egwishlist_product', [
                        'id_product' => (int)$product,
                        'id_product_attribute' => 0, // Use the attribute ID from cookie
                        'id_customer' => $customerId,
                        'id_shop' => $idShop, 
                    ]);
                }
            }
        }
        
        // Clear cookie products after adding them to the customer's wishlist
        Context::getContext()->cookie->bcm_short_list = '';
    }
    
    private function isProductInWishlist($productId, $customerId, $idShop)
    {
        // Check if the product already exists in the wishlist for this customer and shop
        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'egwishlist_product 
                WHERE id_product = ' . (int)$productId . '  
                AND id_customer = ' . (int)$customerId . ' 
                AND id_shop = ' . (int)$idShop;
                
        return (bool) Db::getInstance()->getValue($sql);
    }
    public function hookActionAuthenticationBefore($params)
    {  
            $this->addCookieProductsToWishlist(); 
    }
    
    public function hookHeader()
    {
        $id_lang = Context::getContext()->language->id;
        // send guest var
        Media::addJsDef([
            'eg_wishlist_need_to_login' => 
                !EgWishListProduct::isGuestModeActive() && !Context::getContext()->customer->isLogged()
        ]);
       
        
        $this->context->controller->addJS($this->_path . 'views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');

        $idCustomer = (int) Context::getContext()->customer->id;
        Media::addJsDef([
            'wishlist_url' => $this->context->link->getModuleLink($this->name, 'view', [], true),
        ]);
        if ($this->context->customer->isLogged()) {
            $nbProducts = (int) EgWishListProduct::getWishlistProductsNb($idCustomer);
        } else {
            $nbProducts = (int) $this->countShortlistData();
        }
        Media::addJsDef(array('egwishlist' => [
            'nbProducts' => $nbProducts
        ]));

        $this->smarty->assign([
            'login_link' => Context::getContext()->link->getPageLink('authentication', true, $id_lang)
        ]);
    }
    function countShortlistData()
    {
        if ($this->context->cookie->bcm_short_list != '') {
            $shortlisted = explode(',', $this->context->cookie->bcm_short_list);
        } else {
            $shortlisted = array();
        }
        return count($shortlisted) ;
    }

    public function hookDisplayProductAccessoryInfo()
    {
        return $this->display(__FILE__, 'views/templates/hook/product-accessory.tpl');
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
        $templateFile = 'my-account.tpl';
        if (preg_match('/^displayCustomerAccount\d*$/', $hookName)) {
            $templateFile = 'my-account.tpl';
        } elseif (preg_match('/^displayNav2\d*$/', $hookName) ||
            preg_match('/^displayTop\d*$/', $hookName) ||
            preg_match('/^displayShortList\d*$/', $hookName) ||
            preg_match('/^displayNav\d*$/', $hookName)) {
            $templateFile = 'display-nav.tpl';
        } elseif (preg_match('/^displayBeforeBodyClosingTag\d*$/', $hookName)) {
            $templateFile = 'display-modal.tpl';
        } elseif (preg_match('/^displayHeaderButtons\d*$/', $hookName)) {
            $templateFile = 'display-header-buttons.tpl';
        } elseif (preg_match('/^displayHeaderButtonsMobile\d*$/', $hookName)) {
            $templateFile = 'display-header-buttons-mobile.tpl';
        }  elseif (preg_match('/^displayProductAdditionalInfo\d*$/', $hookName) ||
            preg_match('/^displayEgShortListButtons\d*$/', $hookName) ) {
            $templateFile = 'product-page.tpl';
        } elseif (preg_match('/^displayProductListFunctionalButtons\d*$/', $hookName)) {
            $templateFile = 'product-miniature.tpl';
        } elseif (preg_match('/^displayEgShortHover\d*$/', $hookName)) {
            $templateFile = 'product-miniature.tpl';
        }

        $assign = $this->getWidgetVariables($hookName, $configuration);
        $this->smarty->assign($assign);
        $this->smarty->assign('shortlistCount' , 7);
        $this->smarty->assign('shortListType' , $this->shortListType());
        return $this->fetch('module:' . $this->name . '/views/templates/hook/' . $templateFile);
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
       
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        } 
        if (preg_match('/^displayBeforeBodyClosingTag\d*$/', $hookName)) {
            if (!Context::getContext()->customer->isLogged()) {
                $form = new CustomerLoginForm(
                    $this->context->smarty,
                    $this->context,
                    $this->getTranslator(),
                    new CustomerLoginFormatter($this->getTranslator()),
                    $this->context->controller->getTemplateVarUrls()
                );
                $form->setAction('index.php?controller=authentication&back=my-account');
                return array(
                    'login_form' => $form->getProxy(),
                );
            }
        } elseif (preg_match('/^displayProductListFunctionalButtons\d*$/', $hookName)) {
            $idProduct = (int) $configuration['smarty']->tpl_vars['product']->value['id_product_attribute'];
            $idProductAttribute = (int) $configuration['smarty']->tpl_vars['product']->value['id_product'];
            if (isset($configuration['smarty'])) {
                return array(
                    'id_product_attribute' => $idProduct,
                    'id_product' => $idProductAttribute,
                    'shortListType' => $this->shortListType(),
                );
            }
        }
    }

    public function shortListType()
    {
        if (Context::getContext()->customer->isLogged()) {
            $typeRemove = 'rv_pr';
        } else {
            $typeRemove = 'rv_ck';
        }
        return $typeRemove;
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id'])) {
            EgWishListProduct::deleteGDPRCustomer($customer['id']);
        }
    }
 
    private static function getByIdCustomer_ctm($id_customer)
    {
        $shop_restriction = '';

        if (Shop::getContextShopID()) {
            $shop_restriction = 'AND id_shop = ' . (int) Shop::getContextShopID();
        } elseif (Shop::getContextShopGroupID()) {
            $shop_restriction = 'AND id_shop_group = ' . (int) Shop::getContextShopGroupID();
        }
 
            $result = Db::getInstance()->executeS('
                SELECT *
                FROM `' . _DB_PREFIX_ . 'egwishlist_product` w
                WHERE `id_customer` = ' . (int) $id_customer . ' 
                ' . $shop_restriction . '
                ORDER BY w.`id_product` ASC'
            );
            Cache::store($cache_id, $result);
  

        return Cache::retrieve($cache_id);
    }
    public function hookDisplayEgShortHover($params)
    {
        $templateFile = 'product-miniature.tpl';
        $idProduct = (int) $params['id_product_attribute'];
        $idProductAttribute = (int) $params['id_product'];
        $assign = array(
            'id_product_attribute' => $idProduct,
            'id_product' => $idProductAttribute,
            'shortListType' => $this->shortListType(),
        );
        $this->smarty->assign('shortlistCount' , 7);
        $this->smarty->assign($assign);
        return $this->fetch('module:' . $this->name . '/views/templates/hook/' . $templateFile);
    }
    public function hookDisplayWishList()
    {  
        $products = array();
        if ( $this->context->customer->id ) {
            $customer_wishlists = $this->getByIdCustomer_ctm($this->context->customer->id);
        } else {
            $customer_wishlists = EgWishListProduct::refreshShortlistData();
        }
        foreach ($customer_wishlists as $product) {
           
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT'); 
            $id_product = (int) $product["id_product"]; 
            $prod = new Product($id_product, false, $id_lang);
            $prod->url = $this->context->link->getProductLink($prod);
            $prod->id_product_attribute  =  $product["id_product_attribute"]  ;  
            $images = Product::getCover($id_product);
            $image_url = $this->context->link->getImageLink($prod->link_rewrite, $images['id_image'], ImageType::getFormattedName('home'));
            $combination = new Combination($product["id_product_attribute"]);
            $attr = $combination->getAttributesName($id_lang);
            $prod->cover  = $image_url; 
            $prod->attrs  = $attr; 
            array_push($products, $prod );

        } 
        $this->smarty->assign('products' , $products);
        return $this->display(__FILE__, 'views/templates/hook/products-wished.tpl'); 
    }
    public function hookDisplayOneProduct ( $params ) {
      
        $idProduct = $params ["id"];
        $id_wishlisted = $params ["id_wishlisted"];
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT'); 
        $id_product = (int) $params["id"]; 
        $prod = new Product($idProduct, false, $id_lang);
        $prod->url = $this->context->link->getProductLink($prod);
        
        $images = Product::getCover($idProduct);
        $image_url = $this->context->link->getImageLink($prod->link_rewrite, $images['id_image'], ImageType::getFormattedName('home'));
        if ( isset($params["id_product_attribute"]) && !empty($params["id_product_attribute"]) ) {
            $prod->id_product_attribute  =  $params["id_product_attribute"]  ;  
            $combination = new Combination($params["id_product_attribute"]);
            $attr = $combination->getAttributesName($id_lang);
            $prod->attrs  = $attr; 
        }
        $prod->cover  = $image_url; 
        $this->smarty->assign('product' , $prod);
        $this->smarty->assign('id_wishlisted' , $id_wishlisted);
        return $this->fetch('module:' . $this->name . '/views/templates/hook/' . 'display-one-prod.tpl');
        
    } 
    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['id'])) {
            if ($res = EgWishListProduct::getIdProduct($customer['id'])) {
                $arr = array();
                foreach ($res as $key => $val) {
                    $arr[] = $val['id_product'];
                }
                $productsIds = implode(",",  $arr);
                $items = EgWishListProduct::getItemsProduct($productsIds);
                return json_encode($items);
            }
        }
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
                'className' => 'AdminEgWishlistConfGeneral',
                'parent' => 'CONFIGURE',
                'name' => $this->l('Wishlist'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
            [
                'className' => 'AdminEgWishlistConf',
                'parent' => 'AdminEgWishlistConfGeneral',
                'name' => $this->l('Configure'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
                        [
                'className' => 'AdminEgWishlist',
                'parent' => 'AdminEgWishlistConf',
                'name' => $this->l('Manage wishlist'),
                'module' => $this->name,
                'active' => true,
                'icon' => 'extension',
            ],
        ];
    }
    /**
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('EG_WISHLIST_ACTIVE_NOTIFICATION', Tools::getValue('EG_WISHLIST_ACTIVE_NOTIFICATION'));
            Configuration::updateValue('EG_WISHLIST_ACTIVE_DISCONNECTED', Tools::getValue('EG_WISHLIST_ACTIVE_DISCONNECTED'));
            Configuration::updateValue('EG_WISHLIST_TITLE', Tools::getValue('EG_WISHLIST_TITLE'));
        }

        return $this->renderForm();
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
            'EG_WISHLIST_TITLE'                      => !empty(Configuration::get('EG_WISHLIST_TITLE')) ? Configuration::get('EG_WISHLIST_TITLE') : '',
            'EG_WISHLIST_ACTIVE_NOTIFICATION'        => !empty(Configuration::get('EG_WISHLIST_ACTIVE_NOTIFICATION')) ? Configuration::get('EG_WISHLIST_ACTIVE_NOTIFICATION') : 0,
            'EG_WISHLIST_ACTIVE_DISCONNECTED'        => !empty(Configuration::get('EG_WISHLIST_ACTIVE_DISCONNECTED')) ? Configuration::get('EG_WISHLIST_ACTIVE_DISCONNECTED') : 0,
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
                    'title' => $this->l('Liste de souhaits'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Titre du Wishlist'),
                        'name' => 'EG_WISHLIST_TITLE',
                        'desc' => $this->l("Par default: Wishlist")
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activer Ajout Notification'),
                        'name' => 'EG_WISHLIST_ACTIVE_NOTIFICATION',
                        'class' => 'w-100',
                        'is_bool' => true,
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'EG_WISHLIST_ACTIVE_NOTIFICATION_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global')
                            ],
                            [
                                'id' => 'EG_WISHLIST_ACTIVE_NOTIFICATION_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global')
                            ]
                        ]
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Permettre l\'Ajout Wishlist en Mode Deconnecter'),
                        'name' => 'EG_WISHLIST_ACTIVE_DISCONNECTED',
                        'class' => 'w-100',
                        'is_bool' => true,
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'EG_WISHLIST_ACTIVE_DISCONNECTED_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global')
                            ],
                            [
                                'id' => 'EG_WISHLIST_ACTIVE_DISCONNECTED_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global')
                            ]
                        ]
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
}
