<?php
/**
 * 2008-2024 Prestaworld
 *
 * NOTICE OF LICENSE
 *
 * The source code of this module is under a commercial license.
 * Each license is unique and can be installed and used on only one website.
 * Any reproduction or representation total or partial of the module, one or more of its components,
 * by any means whatsoever, without express permission from us is prohibited.
 *
 * DISCLAIMER
 *
 * Do not alter or add/update to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @author    prestaworld
 * @copyright 2008-2024 Prestaworld
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * International Registered Trademark & Property of prestaworld
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;

include_once 'classes/prestacartsave.php';

class SaveCartForLater extends Module
{
    const INSTALL_SQL_FILE = 'db.sql';
    public function __construct()
    {
        $this->name = 'savecartforlater';
        $this->tab = 'front_office_features';
        $this->version = '7.0.2';
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        ];
        $this->author = 'presta_world';
        $this->bootstrap = true;
        $this->module_key = 'a363b68f985181b559fb9368aa1e5a88';
        $this->confirmUninstall = $this->l('Do you want to uninstall this module?');
        parent::__construct();
        $this->displayName = $this->l('Save cart for later buy');
        $this->description = $this->l('Allow customer to save cart product for later buy');
    }

    public function hookDisplayCustomerAccount()
    {
        $idCustomer = $this->context->customer->id;
        if ($idCustomer) {
            $this->context->smarty->assign(
                array(
                    'id_customer' => $idCustomer,
                    'mylink' => $this->context->link->getModuleLink($this->name, 'mycart')
                )
            );
            return $this->display(__FILE__, 'presta-my-cart.tpl');
        }
    }

    // Display save cart icon on cart summary page
    public function hookDisplayCartExtraProductActions($params)
    {
        if ($this->context->customer->id) {
            if (Configuration::get('PRESTA_SAVE_CART')) {
                $this->context->smarty->assign(array(
                    'product' => $params['product'],
                    'modules_dir' => _MODULE_DIR_
                ));

                return $this->display(__FILE__, 'presta_cart_save.tpl');
            }
        }
    }

    // Dislplay saved cart product below the cart summary to add again into cart
    public function hookDisplayShoppingCartFooter()
    {
        if ($this->context->customer->id) {
            $productsForTemplate = array();
            $objSaveCart = new PrestCartSave();
            $data = $objSaveCart->getCustomerSavedCart($this->context->customer->id);
            if ($data) {
                foreach ($data as $key => $info) {
                    $objProduct = new Product($info['id_product'], false, $this->context->language->id);
                    $allAttr = $objProduct->getAttributesResume($this->context->language->id);
                    if ($allAttr) {
                        foreach ($allAttr as $attr) {
                            if ($attr['id_product_attribute'] == $info['id_product_attribute']) {
                                $data[$key]['attr'] = $attr['attribute_designation'];
                            }
                        }
                    }
                    $data[$key]['id_customization'] = $info['id_product_customization'];
                    $data[$key]['cust_qty'] = $info['quantity'];

                    $assembler = new ProductAssembler($this->context);
                    $presenterFactory = new ProductPresenterFactory($this->context);
                    $presentationSettings = $presenterFactory->getPresentationSettings();
                    $presenter = new ProductListingPresenter(
                        new ImageRetriever(
                            $this->context->link
                        ),
                        $this->context->link,
                        new PriceFormatter(),
                        new ProductColorsRetriever(),
                        $this->context->getTranslator()
                    );

                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct($data[$key]),
                        $this->context->language
                    );
                }

                $this->context->smarty->assign(array(
                    'products' => $productsForTemplate,
                    'modules_dir' => _MODULE_DIR_
                ));
            }
        }
        if ($this->context->cart->getProducts()) {
            $this->context->smarty->assign(array(
                'nocartproduct' => 1
            ));
        }
        return $this->display(__FILE__, 'presta-saved-cart.tpl');
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ('cart' === Tools::getValue('controller')) {
            if (Configuration::get('PRESTA_SAVE_CART_DELETE_AGAIN')) {
                Media::addJsDef(array(
                    'presta_delete' => 1
                    ));
            }
            Media::addJsDef(array(
                'cart_url' => $this->context->link->getPageLink('cart'),
                'presta_url' => $this->context->link->getModuleLink($this->name, 'process'),
                'prestatoken' => Tools::getToken(false),
                'error' => $this->l('Product can not save'),
                'name_empty' => $this->l('Name is empty'),
                'name_notvalid' => $this->l('Name is not valid'),
                'email_empty' => $this->l('Email is empty'),
                'email_notvalid' => $this->l('Email is not valid'),
                'msg_empty' => $this->l('Message is empty'),
                'msg_notvalid' => $this->l('Message is not valid'),
                'cart_shared' => $this->l('Cart shared successfully'),
                'cart_share_err' => $this->l('Something went wrong!'),
            ));

            $this->context->controller->registerStyleSheet(
                'modules-savecartforlater-css',
                'modules/' . $this->name . '/views/css/savecart.css'
            );
            $this->context->controller->registerJavascript(
                'modules-savecartforlater',
                'modules/' . $this->name . '/views/js/savecart.js'
            );
        }
    }

    public function getContent()
    {
        $this->_html = '';
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (empty($this->_postErrors)) {
                $this->postProcess();
            } else {
                if ($this->_postErrors) {
                    foreach ($this->_postErrors as $err) {
                        $this->_html .= $this->displayError($err);
                    }
                }
            }
        } else {
            $this->_html .= '<br />';
        }
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $quantity = (int) Tools::getValue('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY');
            if (!$quantity) {
                $this->_postErrors[] = $this->l('Please set quantity');
            } elseif ($quantity < 0) {
                $this->_postErrors[] = $this->l('quantity is not valid');
            } elseif ($quantity && !Validate::isInt($quantity)) {
                $this->_postErrors[] = $this->l('quantity is not valid');
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PRESTA_SAVE_CART', Tools::getValue('PRESTA_SAVE_CART'));
            Configuration::updateValue('PRESTA_SAVE_CART_QUANTITY', Tools::getValue('PRESTA_SAVE_CART_QUANTITY'));
            Configuration::updateValue(
                'PRESTA_SAVE_CART_ALERT_NOTIFY',
                Tools::getValue('PRESTA_SAVE_CART_ALERT_NOTIFY')
            );
            Configuration::updateValue(
                'PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY',
                Tools::getValue('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY')
            );
            Configuration::updateValue('PRESTA_SAVE_CART_SHARE', Tools::getValue('PRESTA_SAVE_CART_SHARE'));
            Configuration::updateValue(
                'PRESTA_SAVE_CART_PRICE_CHANGE',
                Tools::getValue('PRESTA_SAVE_CART_PRICE_CHANGE')
            );
            Configuration::updateValue(
                'PRESTA_SAVE_CART_DELETE_AGAIN',
                Tools::getValue('PRESTA_SAVE_CART_DELETE_AGAIN')
            );
        }

        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminModules').
            '&configure='.
            $this->name.
            '&tab_module='.
            $this->tab.
            '&module_name='.
            $this->name.
            '&conf=4'
        );
    }

    public function renderForm()
    {
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Save Cart Product Configuration'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Allow customer to save cart product'),
                    'name' => 'PRESTA_SAVE_CART',
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Allow customer to save cart product to buy it later'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show price change if any (eg- Discount on product)'),
                    'name' => 'PRESTA_SAVE_CART_PRICE_CHANGE',
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Show price change if any (eg- Discount on product)'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Save product cart quantity'),
                    'name' => 'PRESTA_SAVE_CART_QUANTITY',
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Save product quantity as customer has added into cart'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Notify to customers, if quantity is low'),
                    'name' => 'PRESTA_SAVE_CART_ALERT_NOTIFY',
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('If product quantity is low then mail to customer to notify them'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Set quantity'),
                    'name' => 'PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY',
                    'col' => 1,
                    'hint' => $this->l('If product quantity is low then mail to customer to notify them')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Allow to customer to share their cart'),
                    'name' => 'PRESTA_SAVE_CART_SHARE',
                    'class' => 't',
                    'is_bool' => true,
                    'hint' => $this->l('Allow to customer to share their cart'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l(
                        'Delete product from save cart if customer again add to cart its saved product'
                    ),
                    'name' => 'PRESTA_SAVE_CART_DELETE_AGAIN',
                    'is_bool' => true,
                    'hint' => $this->l(
                        'If customer add its saved cart product again into the cart then delete thatproduct from their
                        saved cart'
                    ),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.
            '&configure='.
            $this->name.
            '&tab_module='.
            $this->tab.
            '&module_name='.
            $this->name;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->submit_action = 'btnSubmit';
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfiguationValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }

    public function getConfiguationValues()
    {
        $configuration = array(
            'PRESTA_SAVE_CART' => Tools::getValue('PRESTA_SAVE_CART', Configuration::get('PRESTA_SAVE_CART')),
            'PRESTA_SAVE_CART_QUANTITY' => Tools::getValue(
                'PRESTA_SAVE_CART_QUANTITY',
                Configuration::get('PRESTA_SAVE_CART_QUANTITY')
            ),
            'PRESTA_SAVE_CART_ALERT_NOTIFY' => Tools::getValue(
                'PRESTA_SAVE_CART_ALERT_NOTIFY',
                Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY')
            ),
            'PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY' => Tools::getValue(
                'PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY',
                Configuration::get('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY')
            ),
            'PRESTA_SAVE_CART_SHARE' => Tools::getValue(
                'PRESTA_SAVE_CART_SHARE',
                Configuration::get('PRESTA_SAVE_CART_SHARE')
            ),
            'PRESTA_SAVE_CART_PRICE_CHANGE' => Tools::getValue(
                'PRESTA_SAVE_CART_PRICE_CHANGE',
                Configuration::get('PRESTA_SAVE_CART_PRICE_CHANGE')
            ),
            'PRESTA_SAVE_CART_DELETE_AGAIN' => Tools::getValue(
                'PRESTA_SAVE_CART_DELETE_AGAIN',
                Configuration::get('PRESTA_SAVE_CART_DELETE_AGAIN')
            ),
        );
        return $configuration;
    }

    public function install()
    {
        if (!file_exists(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return (false);
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return (false);
        }

        $sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if ($query) {
                if (!Db::getInstance()->execute(trim($query))) {
                    return false;
                }
            }
        }
        Configuration::updateValue('PRESTA_SAVE_CART', 1);
        Configuration::updateValue('PRESTA_SAVE_CART_QUANTITY', 1);
        Configuration::updateValue('PRESTA_SAVE_CART_ALERT_NOTIFY', 1);
        Configuration::updateValue('PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY', 5);
        Configuration::updateValue('PRESTA_SAVE_CART_SHARE', 1);
        Configuration::updateValue('PRESTA_SAVE_CART_PRICE_CHANGE', 1);
        Configuration::updateValue('PRESTA_SAVE_CART_DELETE_AGAIN', 1);

        if (!parent::install()
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('displayShoppingCartFooter')
            || !$this->registerHook('displayCartExtraProductActions')
            || !$this->registerHook('actionFrontControllerSetMedia')
        ) {
            return false;
        }
        return true;
    }

    public function deleteConfiguration()
    {
        $config = array(
            'PRESTA_SAVE_CART', 'PRESTA_SAVE_CART_QUANTITY', 'PRESTA_SAVE_CART_ALERT_NOTIFY',
            'PRESTA_SAVE_CART_ALERT_NOTIFY_QUANTITY', 'PRESTA_SAVE_CART_SHARE',
            'PRESTA_SAVE_CART_PRICE_CHANGE', 'PRESTA_SAVE_CART_DELETE_AGAIN'
            );
        foreach ($config as $key) {
            Configuration::deleteByName($key);
        }
        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'presta_cart_save');
        if (!parent::uninstall()
            || !$this->deleteConfiguration()
        ) {
            return false;
        }
        return true;
    }
}
