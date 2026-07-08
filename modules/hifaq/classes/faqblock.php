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

class HiFAQBlock extends ObjectModel
{
    public $id_block;
    public $active;
    public $title_active;
    public $type;
    public $count;
    public $hook;
    public $position;
    public $title;
    public $accordion;

    public static $definition = [
        'table' => 'hifaqblock',
        'primary' => 'id_block',
        'multilang' => true,
        'fields' => [
            'active' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'title_active' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'type' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 100],
            'count' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'hook' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255, 'lang' => true],
            'accordion' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        $res = parent::add($autodate, $null_values);
        if ($this->type == 'custom' && Tools::getValue('block_faqs')) {
            $faqs = Tools::getValue('block_faqs');
            foreach ($faqs as $id_faq) {
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'hifaqblockfaqs` (id_block, id_faq)
                    VALUES (' . (int) $this->id . ', ' . (int) $id_faq . ')
                ');
            }
        }

        return $res;
    }

    public function update($null_values = false)
    {
        $res = parent::update($null_values);
        $res &= Db::getInstance()->execute('
            DELETE FROM `' . _DB_PREFIX_ . 'hifaqblockfaqs`
            WHERE id_block = ' . (int) $this->id);

        if ($this->type == 'custom' && Tools::getValue('block_faqs')) {
            $faqs = Tools::getValue('block_faqs');
            foreach ($faqs as $id_faq) {
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'hifaqblockfaqs` (id_block, id_faq)
                    VALUES (' . (int) $this->id . ', ' . (int) $id_faq . ')
                ');
            }
        }

        return $res;
    }

    public function delete()
    {
        $res = parent::delete();

        $res &= Db::getInstance()->delete('hifaqblockfaqs', '`id_block` = ' . (int) $this->id);
        $res &= Db::getInstance()->delete('hifaqblock_shop', '`id_block` = ' . (int) $this->id);

        return $res;
    }

    public static function getBlocks($active = false)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;
        $query = new DbQuery();
        $query
            ->select('b.*')
            ->select('b_l.*')
            ->from('hifaqblock', 'b')
            ->leftJoin('hifaqblock_lang', 'b_l', 'b_l.`id_block` = b.`id_block`')
            ->leftJoin('hifaqblock_shop', 'b_s', 'b_s.`id_block` = b.`id_block`')
            ->where('b_l.`id_lang` = ' . (int) $id_lang)
            ->where('b_s.`id_shop` = ' . (int) $id_shop);

        if ($active) {
            $query->where('b.active = 1');
        }

        $query->orderBy('b.position ASC');

        return Db::getInstance()->executeS($query);
    }

    public static function filterBlocks($filter = [], $pageNumber = 1, $pageItems = 50)
    {
        $searchTitle = false;
        $searchStatus = false;
        if (isset($filter['hifaqblockFilter_title'])) {
            $searchTitle = $filter['hifaqblockFilter_title'];
        }
        if (isset($filter['hifaqblockFilter_faqBlockStatus'])) {
            $searchStatus = $filter['hifaqblockFilter_faqBlockStatus'];
        }

        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;
        $query = new DbQuery();
        $query
            ->select('b.*')
            ->select('b_l.*')
            ->from('hifaqblock', 'b')
            ->leftJoin('hifaqblock_lang', 'b_l', 'b_l.`id_block` = b.`id_block`')
            ->leftJoin('hifaqblock_shop', 'b_s', 'b_s.`id_block` = b.`id_block`')
            ->where('b_l.`id_lang` = ' . (int) $id_lang)
            ->where('b_s.`id_shop` = ' . (int) $id_shop);

        if ($searchTitle) {
            $query->where('b_l.`title` like "%' . pSQL($searchTitle) . '%"');
        }
        if ($searchStatus !== false) {
            $query->where('b.active = ' . (int) $searchStatus);
        }

        $res = Db::getInstance()->executeS($query);
        $total = 0;
        if ($res) {
            $total = count($res);
        }

        $query->limit((int) $pageItems, (int) (($pageNumber - 1) * $pageItems));
        $query->orderBy('b.position ASC');

        return [
            'total' => $total,
            'result' => Db::getInstance()->executeS($query),
        ];
    }

    public static function getBlocksByHook($hook, $active = true)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;
        $query = new DbQuery();

        $query
            ->select('b.*')
            ->select('bl.*')
            ->from('hifaqblock', 'b')
            ->leftJoin('hifaqblock_lang', 'bl', 'bl.`id_block` = b.`id_block`')
            ->leftJoin('hifaqblock_shop', 'bs', 'bs.`id_block` = b.`id_block`')
            ->where('bl.`id_lang` = ' . (int) $id_lang)
            ->where('bs.`id_shop` = ' . (int) $id_shop)
            ->where('b.`hook` = \'' . pSQL($hook) . '\'');

        if ($active) {
            $query->where('b.`active` = 1');
        }

        $query->orderBy('b.position ASC');

        return Db::getInstance()->executeS($query);
    }

    public static function getBlocksByID($id_block)
    {
        $id_lang = Context::getContext()->language->id;

        $query = new DbQuery();

        $query
            ->select('b.*')
            ->select('bl.*')
            ->from('hifaqblock', 'b')
            ->leftJoin('hifaqblock_lang', 'bl', 'bl.`id_block` = b.`id_block`')
            ->where('bl.`id_lang` = ' . (int) $id_lang)
            ->where('b.`id_block` = ' . (int) $id_block);

        return Db::getInstance()->getRow($query);
    }

    public static function getCustomFAQs($id_block)
    {
        $query = new DbQuery();

        return Db::getInstance()->executeS(
            $query
                ->select('bf.*')
                ->from('hifaqblockfaqs', 'bf')
                ->where('bf.id_block = ' . (int) $id_block)
                ->build()
        );
    }

    public static function getPosition()
    {
        return (int) Db::getInstance()->getValue('SELECT MAX(position) FROM ' . _DB_PREFIX_ . 'hifaqblock') + 1;
    }
}
