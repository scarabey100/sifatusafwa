<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class StockAlertAlert extends ObjectModel
{
    public $id_shop;
    public $active = 1;
    public $name;
    public $stock = 0;
    public $out_of_stock;
    public $send_mail = 1;
    public $send_mail_admin;
    public $send_mail_admin_addresses;
    public $stock_over = 0;
    public $groups;
    public $groups_excluded;
    public $customers;
    public $customers_excluded;
    public $countries;
    public $countries_excluded;
    public $zones;
    public $zones_excluded;
    public $categories;
    public $categories_excluded;
    public $products;
    public $products_excluded;
    public $attributes;
    public $attributes_excluded;
    public $features;
    public $features_excluded;
    public $manufacturers;
    public $manufacturers_excluded;
    public $suppliers;
    public $suppliers_excluded;
    public $priority;
    public $popup;
    public $custom_text;
    public $product_list;
    public $product_list_product;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'stockalert_alert',
        'primary' => 'id_stockalert_alert',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'name' => ['type' => self::TYPE_STRING],
            'stock' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'out_of_stock' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'send_mail' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'send_mail_admin' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'send_mail_admin_addresses' => ['type' => self::TYPE_STRING],
            'stock_over' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'groups' => ['type' => self::TYPE_STRING],
            'groups_excluded' => ['type' => self::TYPE_STRING],
            'customers' => ['type' => self::TYPE_STRING],
            'customers_excluded' => ['type' => self::TYPE_STRING],
            'countries' => ['type' => self::TYPE_STRING],
            'countries_excluded' => ['type' => self::TYPE_STRING],
            'zones' => ['type' => self::TYPE_STRING],
            'zones_excluded' => ['type' => self::TYPE_STRING],
            'categories' => ['type' => self::TYPE_STRING],
            'categories_excluded' => ['type' => self::TYPE_STRING],
            'products' => ['type' => self::TYPE_STRING],
            'products_excluded' => ['type' => self::TYPE_STRING],
            'manufacturers' => ['type' => self::TYPE_STRING],
            'manufacturers_excluded' => ['type' => self::TYPE_STRING],
            'suppliers' => ['type' => self::TYPE_STRING],
            'suppliers_excluded' => ['type' => self::TYPE_STRING],
            'attributes' => ['type' => self::TYPE_STRING],
            'attributes_excluded' => ['type' => self::TYPE_STRING],
            'features' => ['type' => self::TYPE_STRING],
            'features_excluded' => ['type' => self::TYPE_STRING],
            'priority' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'copy_post' => false],
            'popup' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'custom_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'product_list' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'product_list_product' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ],
    ];

    public function __construct($id = null, $id_lang = null)
    {
        $this->context = Context::getContext();

        parent::__construct($id, $id_lang);

        if (!$id) {
            $this->send_mail_admin_addresses = Configuration::get('PS_SHOP_EMAIL');
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        $this->id_shop = ($this->id_shop) ?: Context::getContext()->shop->id;

        return parent::add($autodate, $null_values);
    }

    public function delete()
    {
        $result = parent::delete();

        $query = 'DELETE FROM `' . _DB_PREFIX_ . 'stockalert_subscriber`
            WHERE `id_stockalert_alert` = ' . (int) $this->id;

        $result &= Db::getInstance()->execute($query);

        return $result;
    }

    public static function getStockAlertByProduct($id_product, $id_product_attribute = null)
    {
        if (!(int) $id_product) {
            return false;
        }

        $context = Context::getContext();

        $cache_key = 'StockAlertAlert::getStockAlertByProduct' . (int) $id_product . '_' . $id_product_attribute . '_' . $context->shop->id;

        if (Cache::isStored($cache_key)) {
            return Cache::retrieve($cache_key);
        }

        $query = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'stockalert_alert` saa
            LEFT JOIN `' . _DB_PREFIX_ . 'stockalert_alert_lang` saal ON (saa.id_stockalert_alert = saal.id_stockalert_alert 
                 AND id_lang = ' . (int) $context->language->id . ')
            WHERE
                `id_shop` = ' . (int) $context->shop->id .
                ' AND `active` = 1';

        // Filter by product
        if (!Configuration::get('SA_HIDE_FILTER_PRODUCT')) {
            $query .=
                ' AND (
                    (`products` = "" AND `products_excluded` = "")
                    OR (
                        (`products` = "" OR FIND_IN_SET("' . (int) $id_product . '", `products`))
                        AND NOT FIND_IN_SET("' . (int) $id_product . '", `products_excluded`)
                    )
                )';
        }

        // Filter by product attribute
        /*if (!Configuration::get('SA_HIDE_FILTER_ATTRIBUTE')) {
            if ((int)$id_product_attribute) {
                $product = new Product($id_product);
                $productAttributeCombinations = $product->getAttributeCombinationsById((int)$id_product_attribute, Context::getContext()->language->id);

                $query .= '
                AND (
                    (`attributes` = "" AND `attributes_excluded` = "")';
                foreach ($productAttributeCombinations as $productAttributeCombination) {
                    $query .= ' OR FIND_IN_SET(' . (int)$productAttributeCombination['id_attribute'] . ', `attributes`)
                    OR NOT FIND_IN_SET("' . (int)$productAttributeCombination['id_attribute'] . '", `attributes_excluded`)';
                }
                $query .= ')';
            }
        }*/
        // Filter by manufacturer
        if (!Configuration::get('SA_HIDE_FILTER_MANUFACTURER')) {
            $id_manufacturer = self::getManufacturerbyIdProduct($id_product);
            if ($id_manufacturer) {
                $query .=
                    ' AND (
                        (`manufacturers` = "" AND `manufacturers_excluded` = "")
                        OR (
                            (`manufacturers` = "" OR FIND_IN_SET("' . (int) $id_manufacturer . '", `manufacturers`))
                            AND NOT FIND_IN_SET("' . (int) $id_manufacturer . '", `manufacturers_excluded`)
                        )
                    )';
            } else {
                $query .=
                    ' AND (
                        (`manufacturers` = "" AND `manufacturers_excluded` = "")
                        OR (
                            (`manufacturers` = "" OR FIND_IN_SET("' . PHP_INT_MAX . '", `manufacturers`))
                            AND NOT FIND_IN_SET("' . PHP_INT_MAX . '", `manufacturers_excluded`)
                        )
                    )';
            }
        }

        // Filter by supplier
        if (!Configuration::get('SA_HIDE_FILTER_SUPPLIER')) {
            $productSuppliers = ProductSupplier::getSupplierCollection($id_product);
            $query .= '
                AND (
                    (`suppliers` = "" AND `suppliers_excluded` = "")';
            foreach ($productSuppliers as $productSupplier) {
                $query .= '
                OR (
                    (`suppliers` = "" OR FIND_IN_SET(' . (int) $productSupplier->id_supplier . ', `suppliers`))
                    AND NOT FIND_IN_SET("' . (int) $productSupplier->id_supplier . '", `suppliers_excluded`)
                )';
            }
            $query .= ')';
        }

        // DESTINATION FILTERS (TARGET)
        // Filter by customer
        if (!Configuration::get('SA_HIDE_FILTER_CUSTOMER')) {
            if ((int) $context->customer->id) {
                $query .=
                    ' AND (
                        (`customers` = "" AND `customers_excluded` = "")
                        OR
                        (
                            (`customers` = "" OR FIND_IN_SET("' . (int) $context->customer->id . '", `customers`))
                            AND NOT FIND_IN_SET("' . (int) $context->customer->id . '", `customers_excluded`)
                        )
                    )';
            } else {
                $query .= ' AND (`customers` = ""
                    OR `customers` IS NULL)';
            }
        }

        // Filter by customer group
        if (!Configuration::get('SA_HIDE_FILTER_CUSTOMER_GROUP')) {
            $customerGroups = Customer::getGroupsStatic((int) $context->customer->id);
            $query .= '
                AND (
                    (`groups` = "" AND `groups_excluded` = "")';
            foreach ($customerGroups as $customerGroup) {
                $query .= '
                OR (
                    (`groups` = "" OR FIND_IN_SET(' . (int) $customerGroup . ', `groups`))
                    AND NOT FIND_IN_SET("' . (int) $customerGroup . '", `groups_excluded`)
                )';
            }
            $query .= ')';
        }

        $query .= ' ORDER BY `priority`;';

        $stockAlerts = Db::getInstance()->executeS($query);

        foreach ($stockAlerts as $stockAlert) {
            // Filter by category
            if (!Configuration::get('SA_HIDE_FILTER_CATEGORY')) {
                $productCategories = Product::getProductCategories($id_product);

                if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                    $productPointCategories = json_decode($stockAlert['categories']);
                } else {
                    $productPointCategories = explode(',', $stockAlert['categories']);
                }

                if ($productPointCategories) {
                    $isInCategory = false;
                    foreach ($productCategories as $category) {
                        if (in_array($category, $productPointCategories)) {
                            $isInCategory = true;
                            break;
                        }
                    }
                } else {
                    $isInCategory = true;
                }

                if ($isInCategory === false) {
                    continue;
                }

                // Exclude by category
                if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                    $productPointCategoriesExcluded = json_decode($stockAlert['categories_excluded']);
                } else {
                    $productPointCategoriesExcluded = explode(',', $stockAlert['categories_excluded']);
                }

                $isInCategoryExcluded = false;
                if ($productPointCategoriesExcluded) {
                    foreach ($productCategories as $category) {
                        if (in_array($category, $productPointCategoriesExcluded)) {
                            $isInCategoryExcluded = true;
                            break;
                        }
                    }
                }

                if ($isInCategoryExcluded === true) {
                    continue;
                }
            }

            // Filter by attribute
            if (!Configuration::get('SA_HIDE_FILTER_ATTRIBUTE')) {
                $attributesSelected = explode(',', $stockAlert['attributes']);
                if (array_filter($attributesSelected)) {
                    $product = new Product($id_product, $id_product_attribute);
                    $productAttributeCombinations = $product->getAttributeCombinationsById((int) $id_product_attribute, Context::getContext()->language->id);

                    if (in_array(PHP_INT_MAX, $attributesSelected)) {
                        // No attributes
                        if ($productAttributeCombinations) {
                            continue;
                        }
                    } else {
                        // Get attributes group
                        $attributeGroups = [];
                        foreach ($attributesSelected as $attributeSelected) {
                            $attribute = new Attribute((int) $attributeSelected);
                            $attributeGroups[$attribute->id_attribute_group] = true;
                        }

                        $hasAttributeGroups = [];

                        foreach ($productAttributeCombinations as $productAttributeCombination) {
                            if (in_array((int) $productAttributeCombination['id_attribute'], $attributesSelected)) {
                                $hasAttributeGroups[(int) $productAttributeCombination['id_attribute_group']] = true;
                            }
                        }

                        if ($hasAttributeGroups !== $attributeGroups) {
                            continue;
                        }
                    }
                }

                $attributesExcluded = explode(',', $stockAlert['attributes_excluded']);
                if (array_filter($attributesExcluded)) {
                    $product = new Product($id_product, $id_product_attribute);
                    $productAttributeCombinations = $product->getAttributeCombinationsById((int) $id_product_attribute, Context::getContext()->language->id);

                    if (in_array(PHP_INT_MAX, $attributesExcluded)) {
                        // No attributes
                        if (!$productAttributeCombinations) {
                            continue;
                        }
                    } else {
                        // Get attributes group
                        $attributeGroups = [];
                        foreach ($attributesExcluded as $attributeExcluded) {
                            $attribute = new Attribute((int) $attributeExcluded);
                            $attributeGroups[$attribute->id_attribute_group] = true;
                        }

                        $hasAttributeGroups = [];
                        $hasAttributeValues = [];
                        foreach ($productAttributeCombinations as $productAttributeCombination) {
                            // Save attribute groups
                            if (in_array((int) $productAttributeCombination['id_attribute_group'], array_keys($attributeGroups))) {
                                $hasAttributeGroups[(int) $productAttributeCombination['id_attribute_group']] = true;
                            }

                            // Save attribute values
                            if (in_array((int) $productAttributeCombination['id_attribute'], $attributesExcluded)) {
                                $hasAttributeValues[(int) $productAttributeCombination['id_attribute_group']] = true;
                            }
                        }

                        // Check if product has attribute group
                        if ($hasAttributeGroups !== $attributeGroups) {
                            continue;
                        }

                        // Check if product has attribute value
                        if ($hasAttributeValues === $attributeGroups) {
                            continue;
                        }
                    }
                }
            }

            // Filter by feature
            if (!Configuration::get('SA_HIDE_FILTER_FEATURE')) {
                $featuresSelected = explode(',', $stockAlert['features']);
                if (array_filter($featuresSelected)) {
                    $productFeatures = Product::getFeaturesStatic((int) $id_product);

                    if (in_array(PHP_INT_MAX, $featuresSelected)) {
                        // No attributes
                        if ($productFeatures) {
                            continue;
                        }
                    } else {
                        // Get features group
                        $featureGroups = [];
                        foreach ($featuresSelected as $featureSelected) {
                            $feature = new FeatureValue((int) $featureSelected);
                            $featureGroups[$feature->id_feature] = true;
                        }

                        $hasFeatureGroups = [];

                        foreach ($productFeatures as $productFeature) {
                            if (in_array($productFeature['id_feature_value'], $featuresSelected)) {
                                $hasFeatureGroups[(int) $productFeature['id_feature']] = true;
                            }
                        }

                        if ($hasFeatureGroups !== $featureGroups) {
                            continue;
                        }
                    }
                }

                $featuresExcluded = explode(',', $stockAlert['features_excluded']);
                if (array_filter($featuresExcluded)) {
                    $productFeatures = Product::getFeaturesStatic((int) $id_product);
                    if (!in_array(PHP_INT_MAX, $featuresExcluded)) {
                        // No features
                        if (!$productFeatures) {
                            continue;
                        }
                    } else {
                        // Get features group
                        $featureGroups = [];
                        foreach ($featuresExcluded as $featureExcluded) {
                            $feature = new FeatureValue((int) $featureExcluded);
                            $featureGroups[$feature->id_feature] = true;
                        }

                        $hasFeatureGroups = [];
                        foreach ($productFeatures as $productFeature) {
                            if (in_array($productFeature['id_feature_value'], $featuresExcluded)) {
                                $hasFeatureGroups[(int) $productFeature['id_feature']] = true;
                            }
                        }

                        if ($hasFeatureGroups === $featureGroups) {
                            continue;
                        }
                    }
                }
            }

            $id_country = null;
            $id_state = null;

            if (isset($context->cart) && !empty($context->cart)) {
                $id_address_delivery = $context->cart->id_address_delivery;
                $address = new Address($id_address_delivery);
                $id_country = $address->id_country;

                if (!$id_country) {
                    $id_country = $context->country->id;
                }

                $id_state = $address->id_state;
            }

            // Filter by country
            if (!Configuration::get('SA_HIDE_FILTER_COUNTRY')) {
                if ($stockAlert['countries'] !== '') {
                    $countries = explode(';', $stockAlert['countries']);

                    if (!in_array($id_country, $countries)) {
                        continue;
                    }
                }

                if ($stockAlert['countries_excluded'] !== '') {
                    $countries = explode(';', $stockAlert['countries_excluded']);

                    if (in_array($id_country, $countries)) {
                        continue;
                    }
                }
            }

            // Filter by zones
            if (!Configuration::get('SA_HIDE_FILTER_ZONE')) {
                if ($stockAlert['zones'] !== '') {
                    if ($id_state) {
                        $zone = State::getIdZone($id_state);
                    } elseif ($id_country) {
                        $country = new Country($id_country);
                        $zone = $country->getIdZone($id_country);
                    }

                    $zones = explode(';', $stockAlert['zones']);
                    if (!in_array($zone, $zones)) {
                        continue;
                    }
                }

                if ($stockAlert['zones_excluded'] !== '') {
                    if ($id_state) {
                        $zone = State::getIdZone($id_state);
                    } elseif ($id_country) {
                        $country = new Country($id_country);
                        $zone = $country->getIdZone($id_country);
                    }

                    $zones = explode(';', $stockAlert['zones_excluded']);
                    if (in_array($zone, $zones)) {
                        continue;
                    }
                }
            }

            Cache::store($cache_key, $stockAlert);

            return $stockAlert;
        }

        Cache::store($cache_key, []);

        return [];
    }

    public static function getManufacturerbyIdProduct($id_product)
    {
        $query = '
            SELECT `id_manufacturer`
            FROM `' . _DB_PREFIX_ . 'product`
            WHERE id_product = ' . (int) $id_product;

        return Db::getInstance()->getValue($query);
    }

    public static function getNbObjects()
    {
        $query = 'SELECT COUNT(*) AS nb
                FROM `' . _DB_PREFIX_ . 'stockalert_alert`
                WHERE `id_shop` = ' . (int) Context::getContext()->shop->id;

        return (int) Db::getInstance()->getValue($query);
    }
}
