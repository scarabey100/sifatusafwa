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

class SaveCartForLaterProcessModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (!$this->isTokenValid()) {
            die('Not Valid!');
        }
    }

    public function displayAjaxSaveCart()
    {
        $idCustomer = $this->context->customer->id;
        $idProduct = Tools::getValue('id_product');
        $idProductAttribute = Tools::getValue('id_product_attribute');
        $idProductCustomization = Tools::getValue('id_product_customization');
        if (!$idProductCustomization) {
            $idProductCustomization = 0;
        }
        $isExist = $this->context->cart->containsProduct(
            $idProduct,
            $idProductAttribute,
            $idProductCustomization,
            $this->context->cart->id_address_delivery
        );
        $quantity = 1;
        if ($isExist) {
            if (Configuration::get('PRESTA_SAVE_CART_QUANTITY')) {
                $quantity = $isExist['quantity'];
            }
        }
        $cartSave = new PrestCartSave();
        $isProductExist = $cartSave->isProductExist(
            $idCustomer,
            $idProduct,
            $idProductAttribute,
            $idProductCustomization
        );
        if ($isProductExist) {
            $cartSave = new PrestCartSave($isProductExist);
        }

        $cartSave->id_customer = $this->context->customer->id;
        $cartSave->id_product = $idProduct;
        $cartSave->id_product_attribute = $idProductAttribute;
        $cartSave->id_product_customization = $idProductCustomization;
        $cartSave->quantity = $quantity;
        $cartSave->is_notified = 0;
        if ($cartSave->save()) {
            die('1');
        }
        die('0');
    }

    public function displayAjaxDeleteFromCustomer()
    {
        $idCustomer = $this->context->customer->id;
        $idProduct = Tools::getValue('id_product');
        $idProductAttribute = Tools::getValue('id_product_attribute');

        $cartSave = new PrestCartSave();
        $isProductExist = $cartSave->isProductExist($idCustomer, $idProduct, $idProductAttribute);
        if ($isProductExist) {
            $cartSave = new PrestCartSave($isProductExist);
            if ($cartSave->delete()) {
                die('1');
            }
            die('0');
        }
        die('0');
    }

    public function displayAjaxSendCart()
    {
        $presta_name = Tools::getValue('presta_name');
        $presta_email = Tools::getValue('presta_email');
        $presta_textarea = Tools::getValue('presta_textarea');

        if (!$presta_name) {
            die('2');   // Name is empty
        } elseif (!Validate::isName($presta_name)) {
            die('3');   // Name is empty
        }

        if (!$presta_email) {
            die('4');   // Email is empty
        } elseif (!Validate::isEmail($presta_email)) {
            die('5');   // Email is not valid
        }

        if (!$presta_textarea) {
            die('6');   // Email is empty
        } elseif (!Validate::isCleanHtml($presta_textarea)) {
            die('7');   // Email is not valid
        }

        $cart = (new CartPresenter)->present($this->context->cart);
        $templateVars = array(
            '{name}' => $presta_name,
            '{message}' => $presta_textarea,
            '{cart}' => $this->context->link->getPageLink(
                'cart',
                null,
                $this->context->language->id,
                array(
                    'action' => 'show'
                ),
                false,
                null,
                true
            ),
            '{prod_info}' => PrestCartSave::getProductListContent($cart),
        );
        $super_admins = Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_);
        foreach ($super_admins as $super_admin) {
            $employee = new Employee((int) $super_admin['id_employee']);
            $admin_email = $employee->email;
            if ($admin_email) {
                break;
            }
        }
        if (Mail::Send(
            $this->context->language->id,
            'cart_info',
            Mail::l('Cart share with you'),
            $templateVars,
            $presta_email,
            $presta_name,
            $admin_email,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . 'savecartforlater/mails/',
            false,
            null,
            null
        )) {
            die('1');
        }
        die('0');
    }
}
