<?php
/**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 Boostmyshop
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';

if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
    require_once _PS_MODULE_DIR_ . 'advancedstock/classes/Model/AdvancedStockWarehousesProducts.php';

class BmsOrderPreparationProductHelper
{

    public static function getProductAttributs($id_product, $id_product_attribut)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from('product_attribute')
            ->where('id_product = ' . (int) $id_product . ' and id_product_attribute=' . (int) $id_product_attribut);
        $result = DB::getInstance()->getRow($query);

        return (object) $result;
    }

    public static function getAllProduct($allData)
    {
        $products = array();
        $allProducts = array();
        $arrAllProducts = array();
        $totalQte = 0;
        $totalWeight = 0;
        $hasLocation = false;

        foreach ($allData as $order) {
            $products = $order->getProducts();
            foreach ($products as $product) {
                $product = (object) $product;
                $attrib = BmsOrderPreparationProductHelper::getProductAttributs($product->product_id, $product->product_attribute_id);

                $totalQte += (int) $product->product_quantity;
                if (! empty($attrib->weight)) {
                    $totalWeight += (int) $product->weight += (float) $attrib->weight;
                } else {
                    $totalWeight += (int) $product->weight;
                }

                $key = $product->product_id . '-' . $product->product_attribute_id;

                if (isset($allProducts[$key])) {
                    $allProducts[$key]->product_quantity += (int) $product->product_quantity;
                } else {
                    self::injectProductLocation($product);
                    if ($product->location)
                        $hasLocation = true;
                    $allProducts[$key] = $product;
                }

                if (! empty($attrib->reference)) {
                    $allProducts[$key]->product_reference = $attrib->reference;
                }
                if (! empty($attrib->ean13)) {
                    $allProducts[$key]->ean13 = $attrib->ean13;
                }
                if (! empty($attrib->upc)) {
                    $allProducts[$key]->upc = $attrib->upc;
                }

            }
        }


        //sort products by name or location
        if (!$hasLocation)
            uasort($allProducts, array("BmsOrderPreparationProductHelper", "sortProductPerName"));
        else
            uasort($allProducts, array("BmsOrderPreparationProductHelper", "sortProductPerLocation"));

        $arrAllProducts['object'] = $allProducts;
        $arrAllProducts['nb'] = count($allProducts);
        $arrAllProducts['qte'] = $totalQte;
        $arrAllProducts['weight'] = $totalWeight;


        return $arrAllProducts;
    }

    public static function injectProductLocation(&$product, $warehouseId = null)
    {
        if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
        {
            $class = "AdvancedStockWarehousesProducts";
            $method = "findProduct";
            $wi = $class::{$method}($product->id_product, $product->product_attribute_id, $warehouseId);
            if ($wi)
                $product->location = $wi->wi_shelf_location;
        }
        else
        {
            # EGIO - Override Starts
            if ($product->product_attribute_id > 0)
            {
                $query = new DbQuery();
                $query->select('location')
                      ->from('product_attribute')
                      ->where('id_product_attribute = ' . (int) $product->product_attribute_id);
                $location = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
                $product->location = $location;
            }
            # EGIO - Override Ends
        }
    }

    public static function getProductLocation($productId, $attributeId, $warehouseId = null)
    {
        if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
        {
            $class = "AdvancedStockWarehousesProducts";
            $method = "findProduct";
            $wi = $class::{$method}($productId, $attributeId, $warehouseId);
            if ($wi)
                return $wi->wi_shelf_location;
        }
        else
        {
            if ($attributeId > 0)
            {
                // Get location directly from database since it's not loaded in the Combination class
                $query = new DbQuery();
                $query->select('location')
                      ->from('product_attribute')
                      ->where('id_product_attribute = ' . (int) $attributeId);
                return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
            }
            else
            {
                $product = new Product($productId);
                return $product->location;
            }
        }
    }

    public static function sortProductPerName($a, $b) {
        if (isset($a->name) && isset($a->name))
        {
            if ($a->name > $b->name)
                return 1;
            else
                return -1;
        }
        else
            return 1;
    }

    public static function sortProductPerLocation($a, $b) {
        if ($a->location > $b->location)
            return 1;
        else
            return -1;
    }


}
