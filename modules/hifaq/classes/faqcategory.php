<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HiFAQCategory extends ObjectModel
{
    public $id;
    public $active;
    public $position;
    public $name;
    public $description;
    public $meta_title;
    public $meta_description;
    public $friendly_url;

    public static $definition = [
        'table' => 'hifaqcategory',
        'primary' => 'id',
        'multilang' => true,
        'fields' => [
            'active' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 100, 'required' => true],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true],
            'meta_title' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 100],
            'meta_description' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 100],
            'friendly_url' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255],
        ],
    ];

    public function delete()
    {
        $res = parent::delete();
        $res &= Db::getInstance()->delete('hifaqcategory_shop', '`id` = ' . (int) $this->id);

        return $res;
    }

    public static function getCategories($active = false, $limit = false, $offset = 0)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('cb.*')
            ->select('cb_l.*')
            ->from('hifaqcategory', 'cb')
            ->leftJoin('hifaqcategory_lang', 'cb_l', 'cb_l.`id` = cb.`id`')
            ->leftJoin('hifaqcategory_shop', 'cb_s', 'cb_s.`id` = cb.`id`')
            ->where('cb_l.`id_lang` = ' . (int) $id_lang)
            ->where('cb_s.`id_shop` = ' . (int) $id_shop);

        if ($active) {
            $query->where('cb.active = 1');
        }

        $query->orderBy('cb.position ASC');

        if ($limit) {
            $query->limit((int) $limit, (int) $offset);
        }

        return Db::getInstance()->executeS($query);
    }

    public static function filterCategories($filter = [], $pageNumber = 1, $pageItems = 50)
    {
        $searchName = false;
        $searchStatus = false;
        if (isset($filter['hifaqcategoryFilter_name'])) {
            $searchName = $filter['hifaqcategoryFilter_name'];
        }
        if (isset($filter['hifaqcategoryFilter_faqCategoryStatus'])) {
            $searchStatus = $filter['hifaqcategoryFilter_faqCategoryStatus'];
        }

        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('c.*')
            ->select('c_l.*')
            ->from('hifaqcategory', 'c')
            ->leftJoin('hifaqcategory_lang', 'c_l', 'c_l.`id` = c.`id`')
            ->leftJoin('hifaqcategory_shop', 'c_s', 'c_s.`id` = c.`id`')
            ->where('c_l.`id_lang` = ' . (int) $id_lang)
            ->where('c_s.`id_shop` = ' . (int) $id_shop);

        if ($searchName) {
            $query->where('c_l.`name` like "%' . pSQL($searchName) . '%"');
        }
        if ($searchStatus !== false) {
            $query->where('c.active = ' . (int) $searchStatus);
        }

        $res = Db::getInstance()->executeS($query);
        $total = 0;
        if ($res) {
            $total = count($res);
        }

        $query->limit((int) $pageItems, (int) (($pageNumber - 1) * $pageItems));
        $query->orderBy('c.position ASC');

        return [
            'total' => $total,
            'result' => Db::getInstance()->executeS($query),
        ];
    }

    public static function getCategoryByFriendlyURL($link_rewrite, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('cb.*')
            ->select('cb_l.*')
            ->from('hifaqcategory', 'cb')
            ->leftJoin('hifaqcategory_lang', 'cb_l', 'cb_l.`id` = cb.`id`')
            ->leftJoin('hifaqcategory_shop', 'cb_s', 'cb_s.`id` = cb.`id`')
            ->where('cb_l.`id_lang` = ' . (int) $id_lang)
            ->where('cb_s.`id_shop` = ' . (int) $id_shop)
            ->where('cb_l.`friendly_url` = \'' . pSQL($link_rewrite) . '\'');

        $res = Db::getInstance()->getRow(
            $query->build()
        );

        if (!is_array($res) || !$res) {
            return [];
        }

        return $res;
    }

    public static function getCategoryById($idCategory, $idLang = null)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $query = new DbQuery();

        return Db::getInstance()->getRow(
            $query
                ->select('c.*')
                ->select('cl.*')
                ->from('hifaqcategory', 'c')
                ->leftJoin('hifaqcategory_lang', 'cl', 'cl.`id` = c.`id`')
                ->where('cl.`id_lang` = ' . (int) $idLang)
                ->where('c.id =' . (int) $idCategory)
                ->build()
        );
    }

    public static function getLinkRewriteByID($id_category, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        return Db::getInstance()->getValue('
            SELECT friendly_url FROM ' . _DB_PREFIX_ . 'hifaqcategory_lang
            WHERE id = ' . (int) $id_category . '
            AND id_lang = ' . (int) $id_lang);
    }

    public static function getIdByLinkRewrite($link_rewrite, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $id_category = (int) Db::getInstance()->getValue('
            SELECT id FROM ' . _DB_PREFIX_ . 'hifaqcategory_lang
            WHERE friendly_url = \'' . pSQL($link_rewrite) . '\'
            AND id_lang = ' . (int) $id_lang);

        if (!$id_category) {
            // check if category exists for another lang with the same friendly_url
            $id_category = (int) Db::getInstance()->getValue('
                SELECT id FROM ' . _DB_PREFIX_ . 'hifaqcategory_lang
                WHERE friendly_url = \'' . pSQL($link_rewrite) . '\'
                AND id_lang <> ' . (int) $id_lang);
        }

        return $id_category;
    }

    public static function getPosition()
    {
        return (int) Db::getInstance()->getValue('SELECT MAX(position) FROM ' . _DB_PREFIX_ . 'hifaqcategory') + 1;
    }
}
