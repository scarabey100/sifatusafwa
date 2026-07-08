<?php
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Core\Domain\Product\Pack\ValueObject\PackStockType;
use PrestaShop\PrestaShop\Core\Domain\Product\ProductSettings;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\Ean13;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\Isbn;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\RedirectType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\Reference;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\Upc;
use PrestaShop\PrestaShop\Core\Util\DateTime\DateTime as DateTimeUtil;
class Product extends ProductCore
{     /**
     * @var array Contains object definition
     *
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'product',
        'primary' => 'id_product',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            /* Classic fields */
            'id_shop_default' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_manufacturer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => Reference::MAX_LENGTH],
            'supplier_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 512],
            'location' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'width' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'height' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'depth' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'quantity_discount' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => Ean13::MAX_LENGTH],
            'isbn' => ['type' => self::TYPE_STRING, 'validate' => 'isIsbn', 'size' => Isbn::MAX_LENGTH],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => Upc::MAX_LENGTH],
            'mpn' => ['type' => self::TYPE_STRING, 'validate' => 'isMpn', 'size' => ProductSettings::MAX_MPN_LENGTH],
            'cache_is_pack' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'cache_has_attachments' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_virtual' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'state' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'additional_delivery_times' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'delivery_in_stock' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'size' => 255,
            ],
            'delivery_out_stock' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'size' => 255,
            ],
            'product_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                // For now undefined value is still allowed, in 179 we should use ProductType::AVAILABLE_TYPES here
                'values' => [
                    ProductType::TYPE_STANDARD,
                    ProductType::TYPE_PACK,
                    ProductType::TYPE_VIRTUAL,
                    ProductType::TYPE_COMBINATIONS,
                    ProductType::TYPE_UNDEFINED,
                ],
                // This default value should be replaced with ProductType::TYPE_STANDARD in 179 when the v2 page is fully migrated
                'default' => ProductType::TYPE_UNDEFINED,
            ],

            /* Shop fields */
            'id_category_default' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'],
            'id_tax_rules_group' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'],
            'on_sale' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'online_only' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'ecotax' => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
            'minimal_quantity' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isPositiveInt'],
            'low_stock_threshold' => ['type' => self::TYPE_INT, 'shop' => true, 'allow_null' => true, 'validate' => 'isInt'],
            'low_stock_alert' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'price' => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'required' => true],
            'wholesale_price' => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
            'unity' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'],
            'unit_price' => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
            /*
             * Only the DB field is deprecated because unit_price is the new reference, we need to keep the class field though
             * @deprecated in 8.0 this DB column will be removed in a future version
             */
            'unit_price_ratio' => ['type' => self::TYPE_FLOAT, 'shop' => true],
            'additional_shipping_cost' => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
            'customizable' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'],
            'text_fields' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'],
            'uploadable_files' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'redirect_type' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'],
            'id_type_redirected' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'],
            'available_for_order' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'available_date' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'],
            'show_condition' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'condition' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isGenericName', 'values' => ['new', 'used', 'refurbished'], 'default' => 'new'],
            'show_price' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'indexed' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'visibility' => ['type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isProductVisibility', 'values' => ['both', 'catalog', 'search', 'none'], 'default' => 'both'],
            'cache_default_attribute' => ['type' => self::TYPE_INT, 'shop' => true],
            'advanced_stock_management' => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
            'pack_stock_type' => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'],
            'name_ar' => ['type' => self::TYPE_STRING],
            /* Lang fields */
            'meta_description' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 512],
            'meta_keywords' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'meta_title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
                'required' => false,
                'size' => 128,
                'ws_modifier' => [
                    'http_method' => WebserviceRequest::HTTP_POST,
                    'modifier' => 'modifierWsLinkRewrite',
                ],
            ],
            'name' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => false, 'size' => ProductSettings::MAX_NAME_LENGTH],
            'description' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4194303],
            'description_short' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4194303],
            'available_now' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => ProductSettings::MAX_AVAILABLE_NOW_LABEL_LENGTH],
            'available_later' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'IsGenericName', 'size' => ProductSettings::MAX_AVAILABLE_LATER_LABEL_LENGTH],
        ],
        'associations' => [
            'manufacturer' => ['type' => self::HAS_ONE],
            'supplier' => ['type' => self::HAS_ONE],
            'default_category' => ['type' => self::HAS_ONE, 'field' => 'id_category_default', 'object' => 'Category'],
            'tax_rules_group' => ['type' => self::HAS_ONE],
            'categories' => ['type' => self::HAS_MANY, 'field' => 'id_category', 'object' => 'Category', 'association' => 'category_product'],
            'stock_availables' => ['type' => self::HAS_MANY, 'field' => 'id_stock_available', 'object' => 'StockAvailable', 'association' => 'stock_availables'],
            'attachments' => ['type' => self::HAS_MANY, 'field' => 'id_attachment', 'object' => 'Attachment', 'association' => 'product_attachment'],
        ],
    ];
    public function getAccessories($id_lang, $active = true)
    {
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`link_rewrite`,
                    pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`,
                    image_shop.`id_image` id_image, il.`legend`, m.`name` as manufacturer_name, cl.`name` AS category_default, IFNULL(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
                    DATEDIFF(
                        p.`date_add`,
                        DATE_SUB(
                            "' . date('Y-m-d') . ' 00:00:00",
                            INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
                        )
                    ) > 0 AS new
                FROM `' . _DB_PREFIX_ . 'accessory`
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = `id_product_2`
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
                    ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $this->id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
                    product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $this->id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
                ' . Product::sqlStock('p', 0) . '
                LEFT JOIN '._DB_PREFIX_.'stock_available sa ON sa.id_product = p.id_product
                WHERE `id_product_1` = ' . (int) $this->id .
                ($active ? ' AND product_shop.`active` = 1 AND product_shop.`visibility` != \'none\'' : '') . '
                AND sa.quantity > 0 
                GROUP BY product_shop.id_product';
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return [];
        }
        foreach ($result as $k => &$row) {
            if (!Product::checkAccessStatic((int) $row['id_product'], false)) {
                unset($result[$k]);
                continue;
            } else {
                $row['id_product_attribute'] = Product::getDefaultAttribute((int) $row['id_product']);
            }
        }
        return $this->getProductsProperties($id_lang, $result);
    }
    public static function getProductNameAr($id_product)
    {
        $sql = 'SELECT `name_ar` FROM `' . _DB_PREFIX_ . 'product` 
                WHERE `id_product` = ' . (int)$id_product;
        return Db::getInstance()->getValue($sql);
    }
    public static function assignManufacturerUrl($manufacturer_id)
    {
        $link = Context::getContext()->link;
        $lang_id = Context::getContext()->language->id;
        $manufacturer = new Manufacturer((int) $manufacturer_id, $lang_id);
        $url = $link->getManufacturerLink($manufacturer, urlencode($manufacturer->link_rewrite), $lang_id);
        return $url;
    }
    public static function getCustomCombinations($id_product)
    {   
        $productPack = new Product((int)$id_product);
        $combinationList = $productPack->getAttributeCombinations(Context::getContext()->language->id);
        return $combinationList;
    }
    public static function getCombinationsFeatures($id_product, $id_product_attribute, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $query = 'SELECT DISTINCT fl.`name`, fvl.`value`
            FROM `' . _DB_PREFIX_ . 'featuresforcombinations` ffc
            -- CROSS JOIN `' . _DB_PREFIX_ . 'feature_product` pf
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON (ffc.`id_feature` = fl.`id_feature`
                    AND fl.`id_lang` = ' . (int) $id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                ON (ffc.`id_feature_value` = fvl.`id_feature_value`
                    AND fvl.`id_lang` = ' . (int) $id_lang . ')
            INNER JOIN `' . _DB_PREFIX_ . 'feature` f
                ON (f.`id_feature` = ffc.`id_feature`)' . Shop::addSqlAssociation('feature', 'f') . '
            WHERE ffc.`id_product` = ' . (int) $id_product . '
                AND ffc.`id_product_attribute` = ' . (int) $id_product_attribute . '
                -- AND pf.`id_product` = ' . (int) $id_product . '
            ORDER BY f.`position`';
        return Db::getInstance()->executeS($query);
    }
    public static function getCombinationDescription($id_product_attribute, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $sqlCheck = 'SELECT description FROM `' . _DB_PREFIX_ . 'product_attribute_lang` WHERE `id_product_attribute` = ' . (int)$id_product_attribute . ' AND `id_lang` = ' . (int)$id_lang;
        return Db::getInstance()->getValue($sqlCheck);
    }
    
    /*
    * module: giftcard
    * date: 2025-07-16 11:57:21
    * version: 2.1.97
    */
    public static function getPriceStatic(
        $id_product,
        $usetax = true,
        $id_product_attribute = null,
        $decimals = 6,
        $divisor = null,
        $only_reduc = false,
        $usereduc = true,
        $quantity = 1,
        $force_associated_tax = false,
        $id_customer = null,
        $id_cart = null,
        $id_address = null,
        &$specific_price_output = null,
        $with_ecotax = true,
        $use_group_reduction = true,
        Context $context = null,
        $use_customer_price = true,
        $id_customization = null
    ) {
        $giftcard = Module::getInstanceByName('giftcard');
        if ($giftcard && $giftcard->active && (int) $id_product > 0 && $giftcard->isGiftCard($id_product)) {
            return (float) GiftCardProduct::getAmount($id_product);
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<') === true) {
            return parent::getPriceStatic(
                $id_product,
                $usetax,
                $id_product_attribute,
                $decimals,
                $divisor,
                $only_reduc,
                $usereduc,
                $quantity,
                $force_associated_tax,
                $id_customer,
                $id_cart,
                $id_address,
                $specific_price_output,
                $with_ecotax,
                $use_group_reduction,
                $context,
                $use_customer_price
            );
        }
        return parent::getPriceStatic(
            $id_product,
            $usetax,
            $id_product_attribute,
            $decimals,
            $divisor,
            $only_reduc,
            $usereduc,
            $quantity,
            $force_associated_tax,
            $id_customer,
            $id_cart,
            $id_address,
            $specific_price_output,
            $with_ecotax,
            $use_group_reduction,
            $context,
            $use_customer_price,
            $id_customization
        );
    }
}