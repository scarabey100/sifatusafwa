<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CpHelper
{
    const TAB = '&ensp;&ensp;';

    const PRODUCT_LIMIT = 3000;

    private static $products = [];

    private static function getProducts($id_lang, $id_category)
    {
        if (empty(self::$products[$id_lang])) {
            self::$products[$id_lang] = [];
            $products = &self::$products[$id_lang];
            $id_shop = Context::getContext()->shop->id;
            $rows = Db::getInstance()->executeS('
                SELECT p.`id_product`, p.`id_category_default`, pl.`name` FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.`id_product` = pl.`id_product`
                WHERE pl.`id_lang` = ' . (int) $id_lang . ' AND pl.`id_shop` = ' . (int) $id_shop . '
                ORDER BY p.`id_category_default`, pl.`name`
            ') ?: [];

            foreach ($rows as &$row) {
                $cat = $row['id_category_default'];

                if (!isset($products[$cat])) {
                    $products[$cat] = [];
                }
                $products[$cat][] = &$row;
            }
        }

        return isset(self::$products[$id_lang][$id_category]) ? self::$products[$id_lang][$id_category] : [];
    }

    public static function getNestedCategories($root_category = null, $id_lang = false, $active = true)
    {
        if (isset($root_category) && !Validate::isInt($root_category) || !Validate::isBool($active)) {
            exit(Tools::displayError());
        }

        $id_shop = Context::getContext()->shop->id;
        $rows = Db::getInstance()->executeS('
            SELECT c.*, cl.* FROM ' . _DB_PREFIX_ . 'category c
            INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs ON cs.`id_category` = c.`id_category` AND cs.`id_shop` = 1
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON c.`id_category` = cl.`id_category` AND cl.`id_shop` = ' . (int) $id_shop . ($root_category ? '
            RIGHT JOIN ' . _DB_PREFIX_ . 'category c2 ON c2.`id_category` = ' . (int) $root_category . ' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`' : '') . '
            WHERE 1 ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '') . ($active ? ' AND c.`active` = 1' : '') . ($id_lang ? '' : '
            GROUP BY c.`id_category`') . '
            ORDER BY c.`level_depth`, cs.`position`
        ') ?: [];
        $categories = [];
        $buff = [];
        $root = (array) Category::getRootCategory();
        array_unshift($rows, $root);
        $root_category || $root_category = $root['id'];

        foreach ($rows as $row) {
            $current = &$buff[$row['id_category']];
            $current = $row;

            if ($row['id_category'] == $root_category) {
                $categories[$row['id_category']] = &$current;
            } else {
                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
            }
        }

        return $categories;
    }

    private static function generateCategories(&$cats, &$a, $tabs = '')
    {
        foreach ($cats as &$cat) {
            if (empty($a)) {
                $a[] = ['value' => 'all', 'option' => '- All -'];
                $a[] = ['value' => '', 'option' => '- None -'];
                $a[] = ['value' => 'index', 'option' => $tabs . '▾ ' . $cat['name']];
            } else {
                $a[] = ['value' => 'cat-' . $cat['id_category'], 'option' => $tabs . '▾ ' . $cat['name']];
            }

            if (isset($cat['children'])) {
                self::generateCategories($cat['children'], $a, $tabs . self::TAB);
            }
            if (self::$products !== null) {
                $id_lang = Context::getContext()->language->id;
                $prods = self::getProducts($id_lang, $cat['id_category']);
                foreach ($prods as &$prod) {
                    $a[] = ['value' => 'prod-' . $prod['id_product'], 'option' => $tabs . self::TAB . '▸ ' . $prod['name']];
                }
            }
        }
    }

    private static function generateCategoryList(&$cats, &$a, $tabs = '')
    {
        foreach ($cats as &$cat) {
            $a[] = (object) ['term_id' => $cat['id_category'], 'name' => $tabs . $cat['name']];

            if (isset($cat['children'])) {
                self::generateCategoryList($cat['children'], $a, $tabs . self::TAB);
            }
        }
    }

    private static function generateCMSCategories(&$e, &$a, $tabs = '')
    {
        if (empty($a)) {
            $a[] = ['value' => 'all', 'option' => '- All -'];
            $a[] = ['value' => '', 'option' => '- None -'];
            if (file_exists(__DIR__ . '/../../psblog')) {
                $a[] = ['value' => 'psblogpostsmodulefront', 'option' => $tabs . '▸ Blog home'];
            }
            $a[] = ['value' => 'newproducts', 'option' => $tabs . '▸ New products'];
            $a[] = ['value' => 'bestsales', 'option' => $tabs . '▸ Best sellers'];
            $a[] = ['value' => 'pricesdrop', 'option' => $tabs . '▸ Price drop'];
            $a[] = ['value' => 'manufacturer-0', 'option' => $tabs . '▾ Brands'];
            $manufacturers = Manufacturer::getManufacturers();
            foreach ($manufacturers as &$manufacturer) {
                $a[] = [
                    'value' => 'manufacturer-' . $manufacturer['id_manufacturer'],
                    'option' => self::TAB . $tabs . '▸ ' . $manufacturer['name'],
                ];
            }
            $a[] = ['value' => 'myaccount', 'option' => $tabs . '▾ Account'];
            $a[] = ['value' => 'identity', 'option' => self::TAB . $tabs . '▸ Personal information'];
            $a[] = ['value' => 'addresses', 'option' => self::TAB . $tabs . '▾ Addresses'];
            $a[] = ['value' => 'address', 'option' => self::TAB . self::TAB . $tabs . '▸ Address'];
            $a[] = ['value' => 'history', 'option' => self::TAB . $tabs . '▾ Order history'];
            $a[] = ['value' => 'orderdetail', 'option' => self::TAB . self::TAB . $tabs . '▸ Order details'];
            $a[] = ['value' => 'orderslip', 'option' => self::TAB . $tabs . '▸ Credit slips'];
            $a[] = ['value' => 'discount', 'option' => self::TAB . $tabs . '▸ Vouchers'];
            $a[] = ['value' => 'auth', 'option' => $tabs . '▸ Authentication'];
            $a[] = ['value' => 'cart', 'option' => $tabs . '▸ Cart'];
            $a[] = ['value' => 'order', 'option' => $tabs . '▾ Order'];
            $a[] = ['value' => 'order-confirmation', 'option' => self::TAB . $tabs . '▸ Order confirmation'];
            $a[] = ['value' => 'contact', 'option' => $tabs . '▸ Contact'];
            $a[] = ['value' => 'supplier', 'option' => $tabs . '▸ Suppliers'];
            $a[] = ['value' => 'cart', 'option' => $tabs . '▾ Cart'];
            $a[] = ['value' => 'order', 'option' => self::TAB . $tabs . '▸ Order'];
            $a[] = ['value' => 'contact', 'option' => $tabs . '▸ Contact us'];
            $a[] = ['value' => 'search', 'option' => $tabs . '▸ Search'];
            $a[] = ['value' => 'sitemap', 'option' => $tabs . '▸ Sitemap'];
            $a[] = ['value' => 'stores', 'option' => $tabs . '▸ Stores'];
            $a[] = ['value' => 'cms-' . $e['id_cms_category'], 'option' => $tabs . '▾ Pages'];
        } else {
            $a[] = ['value' => 'cms-' . $e['id_cms_category'], 'option' => $tabs . '▾ ' . $e['name']];
        }

        if (isset($e['children'])) {
            foreach ($e['children'] as &$child) {
                self::generateCMSCategories($child, $a, $tabs . self::TAB);
            }
        }

        foreach ($e['cms'] as &$c) {
            $a[] = ['value' => 'page-' . $c['id_cms'], 'option' => $tabs . self::TAB . '▸ ' . $c['meta_title']];
        }
    }

    private static function generatePBCategories(&$cats, &$news, &$a, $tabs = '')
    {
        foreach ($cats as &$e) {
            $a[] = ['value' => 'bc-' . $e['id'], 'option' => $tabs . '▾ ' . $e['title']];

            if (isset($e['children'])) {
                self::generatePBCategories($e['children'], $news, $a, $tabs . self::TAB);
            }

            foreach ($news as &$n) {
                foreach ($n['categories'] as $cid => &$c) {
                    if ($e['id'] == $cid && $c) {
                        $a[] = ['value' => 'bn-' . $n['id'], 'option' => $tabs . self::TAB . '▸ ' . $n['title']];
                    }
                }
            }
        }
    }

    public static function getCategoryList()
    {
        $opts = [];
        $cats = method_exists('Category', 'getNestedCategories')
            ? Category::getNestedCategories()
            : self::getNestedCategories()
        ;
        if (empty($cats)) {
            $cats = self::getNestedCategories();
        }
        self::generateCategoryList($cats, $opts);

        return $opts;
    }

    public static function getCategories()
    {
        $opts = [];
        $cats = method_exists('Category', 'getNestedCategories')
            ? Category::getNestedCategories()
            : self::getNestedCategories()
        ;
        if (empty($cats)) {
            $cats = self::getNestedCategories();
        }

        $id_shop = Context::getContext()->shop->id;
        $count = (int) Db::getInstance()->getValue(
            'SELECT COUNT(`id_product`) FROM ' . _DB_PREFIX_ . 'product_shop WHERE `active` = 1 AND `id_shop` = ' . (int) $id_shop
        );
        if (!$count || $count > self::PRODUCT_LIMIT) {
            self::$products = null;
        }

        self::generateCategories($cats, $opts);

        return $opts;
    }

    public static function getCMSCategories()
    {
        $opts = [];
        $cats = CMSCategory::getRecurseCategory();
        self::generateCMSCategories($cats, $opts);

        if (class_exists('CategoriesClass')) {
            $opts[] = ['value' => 'bc-0', 'option' => '▾ PrestaBlog'];
            $blogcats = CategoriesClass::getListe(null, true);
            $blognews = NewsClass::getListe(null, true, 0, 0, null, 'NULL', 'ASC');
            self::generatePBCategories($blogcats, $blognews, $opts, self::TAB);
        }

        return $opts;
    }

    public static function getTagList()
    {
        $context = Context::getContext();
        $id_lang = $context->language->id;
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        if (version_compare(_PS_VERSION_, '1.6.1', '<')) {
            return $db->executeS(
                'SELECT `name`, `id_tag` AS term_id FROM ' . _DB_PREFIX_ . 'tag WHERE `id_lang` = ' . (int) $id_lang . ' ORDER BY `name`'
            );
        }
        $groups = Group::isFeatureActive() ? FrontController::getCurrentCustomerGroups() : [0];

        return $db->executeS('
            SELECT t.`name`, t.`id_tag` AS term_id FROM ' . _DB_PREFIX_ . 'tag_count pt
            LEFT JOIN ' . _DB_PREFIX_ . 'tag t ON t.`id_tag` = pt.`id_tag`
            WHERE pt.`id_group` ' . ($groups ? 'IN (' . implode(',', array_map('intval', $groups)) . ')' : '= 1') . '
            AND pt.`id_lang` = ' . (int) $id_lang . ' AND pt.`id_shop` = ' . (int) $context->shop->id . '
            ORDER BY t.`name`
        ');
    }

    public static function getProductImgTypes()
    {
        $types = ['' => cp__('original size')];

        foreach (ImageType::getImagesTypes('products') as $type) {
            $types[$type['name']] = $type['name'] . ' (' . $type['width'] . ' x ' . $type['height'] . ')';
        }

        return $types;
    }
}
