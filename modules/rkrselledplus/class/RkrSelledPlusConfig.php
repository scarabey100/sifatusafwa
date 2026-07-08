<?php
/**
 * @author    Rekire <info@rekire.com>
 * @copyright Rekire
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class RkrSelledPlusConfig extends ObjectModel
{
    public $position;
    public $active = true;
    public $id_slider;
    public $name;
    public $title;
    public $type;
    public $hook_name;
    public $period;
    public $category_option;
    public $id_categories;
    public $manufacturer_option;
    public $id_manufacturer;
    public $max_products_show = 10;
    public $image_type;
    public $columns_xl = 4;
    public $columns_lg = 3;
    public $columns_md = 2;
    public $columns_sm = 1;
    public $autoplay;
    public $loop;
    public $lazy;
    public $show_product_name = true;
    public $show_product_category;
    public $show_add_to_cart;
    public $show_price = true;
    public $show_displayProductPriceBlock;
    public $show_product_variants;
    public $show_product_flags;
    public $show_product_quick_view;
    public $rem_size_title = 1.3125;
    public $rem_size_product_name = 0.875;
    public $rem_size_product_category = 1.125;
    public $rem_size_product_price = 1;
    public $default_style = 1;
    public $filter_page_visibility = 0;
    public $page_index = 1;
    public $page_product = 1;
    public $page_category = 1;
    public $filter_id_categories;
    public $page_addresses = 1;
    public $page_manufacturer = 1;
    public $filter_id_manufacturer;
    public $page_supplier = 1;
    public $page_cms = 1;
    public $page_cart = 1;
    public $page_my_account = 1;
    public $page_sitemap = 1;

    public static $definition = [
        'table' => 'rkr_selled_plus',
        'primary' => 'id_slider',
        'multilang' => true,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 64,
                'required' => true,
            ],
            'title' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 128,
                'required' => true,
                'lang' => true,
            ],
            'type' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'hook_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isHookName',
                'size' => 64,
                'allow_null' => true,
            ],
            'period' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 64,
                'allow_null' => true,
            ],
            'category_option' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'id_categories' => [
                'type' => self::TYPE_STRING,
                'allow_null' => true,
            ],
            'manufacturer_option' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'id_manufacturer' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'image_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isImageTypeName',
            ],
            'max_products_show' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'columns_xl' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'columns_lg' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'columns_md' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'columns_sm' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'autoplay' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'loop' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'lazy' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_product_name' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_product_category' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_add_to_cart' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_price' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_displayProductPriceBlock' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_product_variants' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_product_flags' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'show_product_quick_view' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'rem_size_title' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
            'rem_size_product_name' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
            'rem_size_product_category' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
            'rem_size_product_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
            'default_style' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'filter_page_visibility' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_index' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_product' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_category' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'filter_id_categories' => [
                'type' => self::TYPE_STRING,
                'allow_null' => true,
            ],
            'page_addresses' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_manufacturer' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'filter_id_manufacturer' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'page_supplier' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_cms' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_cart' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_my_account' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'page_sitemap' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
        ],
    ];

    public static function getRowsByHook($hookName, $idLang, $active = 1)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(self::$definition['table'], 'rkr_r');
        $query->innerJoin('rkr_selled_plus_lang', 'rkr_rl', 'rkr_r.`id_slider` = rkr_rl.`id_slider`');
        $query->where('rkr_r.type = 1');
        $query->where("rkr_r.hook_name = '$hookName'");
        $query->where('rkr_r.active = ' . (bool) $active);
        $query->where("rkr_rl.id_lang = $idLang");
        $query->orderBy('rkr_r.`position` ASC');
        
        return Db::getInstance()->ExecuteS($query);
    }

    public static function getRowById($id, $idLang, $type = 2, $active = 1)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(self::$definition['table'], 'rkr_r');
        $query->innerJoin('rkr_selled_plus_lang', 'rkr_rl', 'rkr_r.`id_slider` = rkr_rl.`id_slider`');
        $query->where("rkr_r.id_slider = $id");
        if ($type !== null) {
            $query->where('rkr_r.type = ' . (int) $type);
        }
        $query->where('rkr_r.active = ' . (bool) $active);
        $query->where("rkr_rl.id_lang = $idLang");

        return Db::getInstance()->getRow($query);
    }

    public static function isShortCodeById($id)
    {
        $query = new DbQuery();
        $query->select('rkr_r.type');
        $query->from(self::$definition['table'], 'rkr_r');
        $query->where("rkr_r.id_slider = $id");
        $query->where('rkr_r.type = 2');

        return (bool) Db::getInstance()->getRow($query);
    }

    public static function isExist($hookName)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(self::$definition['table'], 'rkr_r');
        $query->where('rkr_r.type = 1');
        $query->where("rkr_r.hook_name = '$hookName'");

        return (bool) Db::getInstance()->getRow($query);
    }

    public function add($autoDate = true, $null_values = false)
    {
        $this->position = $this->getLastPosition();

        return parent::add($autoDate, $null_values);
    }

    public function getLastPosition()
    {
        $query = new DbQuery();
        $query->select('IFNULL(MAX(position)+1,0)');
        $query->from(self::$definition['table'], 'rkrr');

        return Db::getInstance()->getValue($query);
    }

    public function updatePosition($way, $position)
    {
        $res = Db::getInstance()->executeS('SELECT c.`' . self::$definition['primary'] . '`, c.`position` FROM `'
            . _DB_PREFIX_ . self::$definition['table'] . '` c ORDER BY c.`position` ASC');

        if (!$res) {
            return false;
        }

        foreach ($res as $reg) {
            if ((int) $reg[self::$definition['primary']] == (int) $this->id) {
                $reg_movido = $reg;
            }
        }

        if (!isset($reg_movido) || !isset($position)) {
            return false;
        }

        $result = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`
				SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
				WHERE `position`
				' . ($way
                    ? '> ' . (int) $reg_movido['position'] . ' AND `position` <= ' . (int) $position
                    : '< ' . (int) $reg_movido['position'] . ' AND `position` >= ' . (int) $position))
            && Db::getInstance()->execute('
				UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`
				SET `position` = ' . (int) $position . '
				WHERE `' . self::$definition['primary'] . '` = ' . (int) $reg_movido[self::$definition['primary']]);

        return $result;
    }
}
