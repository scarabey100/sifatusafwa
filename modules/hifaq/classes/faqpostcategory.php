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

class HiFAQPostCategory extends ObjectModel
{
    public $id;
    public $id_category;
    public $id_faq;
    public $name;

    public static $definition = [
        'table' => 'hifaqpostcategory',
        'primary' => 'id',
        'fields' => [
            'id_faq' => [
                'type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_category' => [
                'type' => self::TYPE_INT, 'validate' => 'isInt'],
        ],
    ];

    public static function getAllFaqCategoryByIdFaq($id_faq = null)
    {
        $query = new DbQuery();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('bt. *')
                ->from('hifaqpostcategory', 'bt')
                ->where('bt.id_faq = ' . (int) $id_faq)
                ->build()
        );
    }

    public static function getAllFaqByIdCategory($id_cat = null)
    {
        $query = new DbQuery();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('bt. *')
                ->from('hifaqpostcategory', 'bt')
                ->where('bt.id_category = ' . (int) $id_cat)
                ->build()
        );
    }

    public static function getAllFaqCategory()
    {
        $query = new DbQuery();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('bt.*')
                ->from('hifaqpostcategory', 'bt')
                ->groupBy('id_category')
                ->build()
        );
    }

    public static function getCategoryFAQsCount($id_category)
    {
        return Db::getInstance()->getValue('
            SELECT COUNT(f.`id_faq`)
            FROM `' . _DB_PREFIX_ . 'hifaqpostcategory` fc
            LEFT JOIN `' . _DB_PREFIX_ . 'hifaq` f
                ON f.`id_faq` = fc.`id_faq`
            WHERE fc.`id_category` = ' . (int) $id_category . ' 
            AND f.`active` = 1');
    }
}
