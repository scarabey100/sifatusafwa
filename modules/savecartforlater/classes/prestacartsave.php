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

class PrestCartSave extends ObjectModel
{
    public $id_customer;
    public $id_product;
    public $id_product_attribute;
    public $id_product_customization;
    public $quantity;
    public $is_notified;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'presta_cart_save',
        'primary' => 'id',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'required' => true),
            'id_product_customization' => array('type' => self::TYPE_INT),
            'quantity' => array('type' => self::TYPE_INT, 'required' => true),
            'is_notified' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false)
        ),
    );

    public function isProductExist($idCustomer, $idProduct, $idProductAttribute, $idProductCustomization = false)
    {
        $sql = 'SELECT `id` FROM ' . _DB_PREFIX_ . 'presta_cart_save WHERE
                    `id_customer` = ' . (int) $idCustomer . ' AND
                    `id_product` = ' . (int) $idProduct . ' AND
                    `id_product_attribute` = ' . (int) $idProductAttribute;

        if ($idProductCustomization) {
            $sql .= ' AND `id_product_customization` = ' . (int) $idProductCustomization;
        }
        return Db::getInstance()->getValue($sql);
    }

    public function getCustomerSavedCart($idCustomer)
    {
        return Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'presta_cart_save WHERE
            `id_customer` = ' . (int) $idCustomer);
    }

    public static function getProductListContent($cart)
    {
        $path = _PS_MODULE_DIR_ . 'savecartforlater/views/templates/hook/presta-cart-product-list.tpl';

        if (Tools::file_exists_cache($path)) {
            Context::getContext()->smarty->assign('cart', $cart);

            return Context::getContext()->smarty->fetch($path);
        }
        return '';
    }
}
