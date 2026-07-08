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

class StockAlertSubscriber extends ObjectModel
{
    public $id_customer;
    public $customer_email;
    public $id_product;
    public $id_product_attribute;
    public $send_mail;
    public $id_shop;
    public $id_lang;
    public $id_stockalert_alert;
    public $date_send;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'stockalert_subscriber',
        'primary' => 'id_stockalert_subscriber',
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'customer_email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'send_mail' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_lang' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_stockalert_alert' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'date_send' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        $this->id_shop = ($this->id_shop) ?: Context::getContext()->shop->id;

        return parent::add($autodate, $null_values);
    }

    public static function customerHasNotification($id_customer, $id_product, $id_product_attribute, $id_shop = null, $id_lang = null, $guest_email = '')
    {
        if ($id_shop == null) {
            $id_shop = Context::getContext()->shop->id;
        }

        if ($id_lang == null) {
            $id_lang = Context::getContext()->language->id;
        }

        $customer = new Customer($id_customer);
        $customer_email = $customer->email;
        $guest_email = pSQL($guest_email);

        $id_customer = (int) $id_customer;
        $customer_email = pSQL($customer_email);
        $where = $id_customer == 0 ? "customer_email = '$guest_email'" : "(id_customer=$id_customer OR customer_email='$customer_email')";
        $query = '
            SELECT *
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE ' . $where . '
                AND `id_product` = ' . (int) $id_product . '
                AND `id_product_attribute` = ' . (int) $id_product_attribute . '
                AND (`date_send` IS NULL OR `date_send` = "' . date('Y-m-d H:i:s', 0) . '")
                AND `id_shop` = ' . (int) $id_shop;

        return Db::getInstance()->getRow($query);
    }

    public static function deleteAlert($id_stockalert_subscriber)
    {
        $context = Context::getContext();

        $query = '
            DELETE FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE (`customer_email` = \'' . pSQL($context->customer->email) . '\' OR `id_customer` = ' . (int) $context->customer->id . ')
            AND `id_stockalert_subscriber` = ' . (int) $id_stockalert_subscriber . '
            AND `id_shop` = ' . (int) Context::getContext()->shop->id;

        return Db::getInstance()->execute($query);
    }

    public static function getStockAlerts()
    {
        $query = '
            SELECT *
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`;';

        return Db::getInstance()->executeS($query);
    }

    /*
     * Get objects that will be viewed on "My stock alerts" page
     */
    public static function getStockAlertsByCustomer($id_customer, $id_lang, $shop = null)
    {
        if (!Validate::isUnsignedId($id_customer) || !Validate::isUnsignedId($id_lang)) {
            echo Tools::displayError();
            exit;
        }

        if (!$shop) {
            $shop = Context::getContext()->shop;
        }

        $customer = new Customer($id_customer);

        $query = '
            SELECT sas.`id_stockalert_subscriber`, sas.`date_add`, sas.`id_product`, sas.`id_product_attribute`, pl.`name`
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` sas
            JOIN `' . _DB_PREFIX_ . 'product` p ON (p.`id_product` = sas.`id_product`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.id_shop IN (' . $shop->id . '))
            WHERE product_shop.`active` = 1
                AND (`date_send` IS NULL OR `date_send` = "' . date('Y-m-d H:i:s', 0) . '")
            AND (sas.`id_customer` = ' . (int) $customer->id . ' OR sas.`customer_email` = \'' . pSQL($customer->email) . '\')
            AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestriction(false, 'sas');

        $products = Db::getInstance()->executeS($query);

        if (empty($products) === true) {
            return [];
        }

        foreach ($products as &$product) {
            $obj = new Product((int) $product['id_product'], false, (int) $id_lang);
            if (!Validate::isLoadedObject($obj)) {
                continue;
            }

            if (isset($product['id_product_attribute'])
                && Validate::isUnsignedInt($product['id_product_attribute'])) {
                $attributes = self::getProductAttributeCombination($product['id_product_attribute'], $id_lang);
                $product['attributes_small'] = '';

                if ($attributes) {
                    foreach ($attributes as $row) {
                        $product['attributes_small'] .= $row['attribute_name'] . ', ';
                    }
                }

                $product['attributes_small'] = rtrim($product['attributes_small'], ', ');
                $product['id_shop'] = $shop->id;

                /* Get cover */
                $attrgrps = $obj->getAttributesGroups((int) $id_lang);
                foreach ($attrgrps as $attrgrp) {
                    if ($attrgrp['id_product_attribute'] == (int) $product['id_product_attribute']
                        && $images = Product::_getAttributeImageAssociations((int) $attrgrp['id_product_attribute'])) {
                        $product['cover'] = $obj->id . '-' . array_pop($images);
                        break;
                    }
                }
            }

            if (!isset($product['cover']) || !$product['cover']) {
                $images = $obj->getImages((int) $id_lang);
                foreach ($images as $image) {
                    if ($image['cover']) {
                        $product['cover'] = $obj->id . '-' . $image['id_image'];
                        break;
                    }
                }
            }

            if (!isset($product['cover'])) {
                $product['cover'] = Language::getIsoById($id_lang) . '-default';
            }
            $product['link'] = $obj->getLink();
            $context = Context::getContext();
            $product['cover_url'] = $context->link->getImageLink($obj->link_rewrite, $product['cover'], version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small'));
        }
        unset($product);

        return $products;
    }

    public static function sendStockAlertSubscriberAlerts($id_product, $id_product_attribute, $quantity)
    {
        $context = Context::getContext()->cloneContext();
        $stockAlertSubscribers = self::getStockAlertSubscribers($id_product, $id_product_attribute);

        foreach ($stockAlertSubscribers as $stockAlertSubscriber) {
            $stockAlertAlert = new StockAlertAlert((int) $stockAlertSubscriber['id_stockalert_alert']);

            if ($quantity <= $stockAlertAlert->stock_over) {
                continue;
            }

            $stockAlertSubscriber = new StockAlertSubscriber((int) $stockAlertSubscriber['id_stockalert_subscriber']);
            $id_shop = (int) $stockAlertSubscriber->id_shop;
            $id_lang = (int) $stockAlertSubscriber->id_lang;
            $context->shop->id = $id_shop;
            $context->language->id = $id_lang;

            $product = new Product((int) $id_product, false, $id_lang, $id_shop);

            if (!$product->active) {
                continue;
            }

            $img = null;
            if ($id_product_attribute) {
                $img = Product::getCombinationImageById($id_product_attribute, $id_lang);
            }

            if (!$img) {
                $img = Product::getCover($id_product);
            }

            $template_vars = [
                '{product}' => Product::getProductName($id_product, $id_product_attribute, $id_lang),
                '{product_name}' => Product::getProductName($id_product, $id_product_attribute, $id_lang),
                '{product_link}' => $context->link->getProductLink($product, $product->link_rewrite, null, null, $id_lang, $id_shop, $id_product_attribute),
                '{product_img}' => $context->link->getImageLink($product->link_rewrite, $img['id_image'], version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small')),
            ];

            if ($stockAlertSubscriber->id_customer) {
                $customer = new Customer((int) $stockAlertSubscriber->id_customer);
                $customer_email = $customer->email;
                $template_vars = array_merge($template_vars, [
                    '{customer_name}' => $customer->firstname,
                ]);
            } else {
                $customer_email = $stockAlertSubscriber->customer_email;
                $template_vars = array_merge($template_vars, [
                    '{customer_name}' => '',
                ]);
            }

            $iso = Language::getIsoById((int) $id_lang);

            $module = Module::getInstanceByName('stockalert');

            $template = 'stockalert';
            $isoTemplate = $iso . '/' . $template;
            $templatePath = $module::getTemplateBasePath($isoTemplate, $module->name, Context::getContext()->shop->theme);

            // English template is required always
            if (!file_exists($templatePath . 'en/' . $template . '.txt')
                || !file_exists($templatePath . 'en/' . $template . '.html')) {
                // Copy emails
                $module->copyMailsFolder($templatePath);
            }

            if (!file_exists($templatePath . $iso . '/' . $template . '.txt')
                || !file_exists($templatePath . $iso . '/' . $template . '.html')) {
                // Copy emails
                $module->copyMailsFolder($templatePath);
            }

            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $subject = Translate::getModuleTranslation($module->name, 'Product available', static::class, null, false, Language::getLocaleByIso($iso));
            } else {
                $context->shop->id = $id_shop;
                $context->language->id = $id_lang;
                $subject = Translate::getModuleTranslation($module->name, 'Product available', static::class);
            }

            try {
                $result = Mail::Send(
                    $id_lang,
                    $template,
                    $subject,
                    $template_vars,
                    (string) trim($customer_email),
                    null,
                    (string) Configuration::get('PS_SHOP_EMAIL', null, null, $id_shop),
                    (string) Configuration::get('PS_SHOP_NAME', null, null, $id_shop),
                    null,
                    null,
                    $templatePath,
                    false,
                    $id_shop,
                    Configuration::get('SA_BCC') ? explode(';', Configuration::get('SA_BCC')) : null
                );

                if ($result) {
                    $stockAlertSubscriber->date_send = date('Y-m-d H:i:s');
                    $stockAlertSubscriber->customer_email = trim($stockAlertSubscriber->customer_email);
                    $stockAlertSubscriber->save();
                }
            } catch (Exception $e) {
                /*
                 * Something went wrong but don't care we need to continue.
                 * This can be caused by an invalid e-mail address.
                 */
                PrestaShopLogger::addLog(
                    sprintf(
                        'Stockalert error: Could not send email to address [%s] because %s',
                        $customer_email,
                        $e->getMessage()
                    ),
                    3
                );
            }
        }

        return true;
    }

    /*
     * Get product combinations
     */
    public static function getProductAttributeCombination($id_product_attribute, $id_lang)
    {
        $query = '
            SELECT al.`name` AS attribute_name
            FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            WHERE pac.`id_product_attribute` = ' . (int) $id_product_attribute;

        return Db::getInstance()->executeS($query);
    }

    /*
     * Get customers waiting for alert on the specified product/product attribute
     */
    public static function getStockAlertSubscribers($id_product, $id_product_attribute)
    {
        $query = '
            SELECT `id_stockalert_subscriber`, `id_stockalert_alert`
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE `id_product` = ' . (int) $id_product . '
                AND `id_product_attribute` = ' . (int) $id_product_attribute . '
                AND (`date_send` IS NULL OR `date_send` = "' . date('Y-m-d H:i:s', 0) . '")';

        return Db::getInstance()->executeS($query);
    }

    // Translations in backoffice
    public function translations()
    {
        $this->module->l('Product available'); // Email subject
    }

    public static function getManufacturersWithAlerts()
    {
        $query = 'SELECT DISTINCT(`id_manufacturer`)
                FROM `' . _DB_PREFIX_ . 'product` p
                LEFT JOIN `' . _DB_PREFIX_ . 'stockalert_subscriber` ss ON (ss.`id_product` = p.`id_product`)
                WHERE `id_shop` = ' . (int) Context::getContext()->shop->id .
                    ' AND (`date_send` IS NULL OR `date_send` = "' . date('Y-m-d H:i:s', 0) . '")';

        return Db::getInstance()->executeS($query);
    }
}
