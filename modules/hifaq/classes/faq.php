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

class HiFAQItem extends ObjectModel
{
    public $id_faq;
    public $active;
    public $position;
    public $title;
    public $question;
    public $answer;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $friendly_url;

    public static $definition = [
        'table' => 'hifaq',
        'primary' => 'id_faq',
        'multilang' => true,
        'fields' => [
            'active' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255, 'lang' => true],
            'question' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255, 'lang' => true],
            'answer' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true],
            'meta_title' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255],
            'meta_description' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255],
            'meta_keywords' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255],
            'friendly_url' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255, 'required' => true],
        ],
    ];

    public function delete()
    {
        $res = parent::delete();

        $res &= Db::getInstance()->delete('hifaqpostcategory', '`id_faq` = ' . (int) $this->id);
        $res &= Db::getInstance()->delete('hifaqrelatedproduct', '`id_faq` = ' . (int) $this->id);
        $res &= Db::getInstance()->delete('hifaqblockfaqs', '`id_faq` = ' . (int) $this->id);
        $res &= Db::getInstance()->delete('hifaq_shop', '`id_faq` = ' . (int) $this->id);
        $res &= Db::getInstance()->delete('hifaqfeedback', '`id_faq` = ' . (int) $this->id);

        return $res;
    }

    public static function filterFaqs($filter = [], $pageNumber = 1, $pageItems = 50)
    {
        $searchCategory = 0;
        $searchTitle = false;
        $searchQuestion = false;
        $searchStatus = false;
        if (isset($filter['hifaqFilter_faqCategories'])) {
            $searchCategory = (int) $filter['hifaqFilter_faqCategories'];
        }
        if (isset($filter['hifaqFilter_title'])) {
            $searchTitle = $filter['hifaqFilter_title'];
        }
        if (isset($filter['hifaqFilter_question'])) {
            $searchQuestion = $filter['hifaqFilter_question'];
        }
        if (isset($filter['hifaqFilter_faqStatus'])) {
            $searchStatus = $filter['hifaqFilter_faqStatus'];
        }

        $idShop = (int) Context::getContext()->shop->id;
        $idLang = (int) Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('f.*, fl.*');
        $query->from('hifaq', 'f');
        $query->leftJoin('hifaq_lang', 'fl', 'f.`id_faq` = fl.`id_faq`');
        $query->leftJoin('hifaq_shop', 'fs', 'f.`id_faq` = fs.`id_faq`');
        $query->where('fl.`id_lang` =' . (int) $idLang);
        $query->where('fs.`id_shop` =' . (int) $idShop);
        if ($searchCategory) {
            $query->leftJoin('hifaqpostcategory', 'fp', 'f.`id_faq` = fp.`id_faq`');
            $query->where('fp.`id_category` =' . (int) $searchCategory);
        }
        if ($searchTitle) {
            $query->where('fl.`title` like "%' . pSQL($searchTitle) . '%"');
        }
        if ($searchQuestion) {
            $query->where('fl.`question` like "%' . pSQL($searchQuestion) . '%"');
        }
        if ($searchStatus !== false) {
            $query->where('f.active = ' . (int) $searchStatus);
        }

        $res = Db::getInstance()->executeS($query);
        $total = 0;
        if ($res) {
            $total = count($res);
        }

        $query->limit((int) $pageItems, (int) (($pageNumber - 1) * $pageItems));
        $query->orderBy('f.position ASC');

        $faqs = Db::getInstance()->executeS($query);
        if (is_array($faqs) && $faqs) {
            foreach ($faqs as $key => $faq) {
                $idFaq = $faq['id_faq'];

                // Add FAQ categories
                $query = new DbQuery();
                $query->select('fc.*, fcl.*');
                $query->from('hifaqpostcategory', 'fc');
                $query->leftJoin('hifaqcategory_lang', 'fcl', 'fc.`id_category` = fcl.`id`');
                $query->where('fcl.`id_lang` =' . (int) $idLang);
                $query->where('fc.`id_faq` =' . (int) $faq['id_faq']);
                $faqs[$key]['categories'] = Db::getInstance()->executeS($query);

                // Add PS Products count
                $faqs[$key]['relatedProductsCount'] = self::getRelatedProductsCount($idFaq);

                // Add PS Features count
                $faqs[$key]['relatedFeaturesCount'] = self::getRelatedFeaturesCount($idFaq);

                // Add PS Categories count
                $faqs[$key]['relatedCategoriesCount'] = self::getRelatedCategoriesCount($idFaq);
            }
        }

        return [
            'total' => $total,
            'result' => $faqs,
        ];
    }

    public static function getFAQsByProductID($id_product, $active = true)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('frp.id_faq')
            ->select('f.*')
            ->select('f_l.*')
            ->from('hifaqrelatedproduct', 'frp')
            ->leftJoin('hifaq', 'f', 'f.`id_faq` = frp.`id_faq`')
            ->leftJoin('hifaq_lang', 'f_l', 'f_l.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaq_shop', 'f_s', 'f.`id_faq` = f_s.`id_faq`')
            ->where('f_l.`id_lang` = ' . (int) $id_lang)
            ->where('f_s.`id_shop` = ' . (int) $id_shop)
            ->where('frp.`id_product` = ' . (int) $id_product);

        if ($active) {
            $query->where('f.active = 1');
        }

        return Db::getInstance()->executeS($query);
    }

    public static function getFAQsByCategories($categories, $active = true)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('frc.id_faq')
            ->select('f.*')
            ->select('f_l.*')
            ->from('hifaqrelatedcategory', 'frc')
            ->leftJoin('hifaq', 'f', 'f.`id_faq` = frc.`id_faq`')
            ->leftJoin('hifaq_lang', 'f_l', 'f_l.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaq_shop', 'f_s', 'f.`id_faq` = f_s.`id_faq`')
            ->where('f_l.`id_lang` = ' . (int) $id_lang)
            ->where('f_s.`id_shop` = ' . (int) $id_shop)
            ->where('frc.`id_category` IN (' . implode(',', array_map('intval', $categories)) . ')');

        if ($active) {
            $query->where('f.active = 1');
        }

        return Db::getInstance()->executeS($query);
    }

    public static function getFAQsByProductFeatures($idFeature, $idFeatureValue, $active = true)
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();

        $query
            ->select('frf.id_faq')
            ->select('f.*')
            ->select('f_l.*')
            ->from('hifaqrelatedproductfeature', 'frf')
            ->leftJoin('hifaq', 'f', 'f.`id_faq` = frf.`id_faq`')
            ->leftJoin('hifaq_lang', 'f_l', 'f_l.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaq_shop', 'f_s', 'f.`id_faq` = f_s.`id_faq`')
            ->where('f_l.`id_lang` = ' . (int) $id_lang)
            ->where('f_s.`id_shop` = ' . (int) $id_shop)
            ->where('frf.`id_feature` = ' . (int) $idFeature)
            ->where('frf.`id_feature_value` = ' . (int) $idFeatureValue);

        if ($active) {
            $query->where('f.active = 1');
        }

        return Db::getInstance()->executeS($query);
    }

    public static function getFaqById($idFaq, $idLang = null)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $query = new DbQuery();

        return Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->select('fl.*')
                ->from('hifaq', 'f')
                ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
                ->where('fl.`id_lang` = ' . (int) $idLang)
                ->where('f.id_faq =' . (int) $idFaq)
                ->build()
        );
    }

    public static function getFAQsByIdCategory($id_category, $active = true, $limit = 0, $offset = 0, $order = 'ASC', $id_lang = null, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $query = new DbQuery();

        $query
            ->select('f.*')
            ->select('fl.*')
            ->from('hifaq', 'f')
            ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaq_shop', 'fs', 'fs.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaqpostcategory', 'fpc', 'f.`id_faq` = fpc.`id_faq`')
            ->where('fl.`id_lang` = ' . (int) $id_lang)
            ->where('fs.`id_shop` = ' . (int) $id_shop)
            ->where('fpc.id_category =' . (int) $id_category);

        if ($active) {
            $query->where('f.active = 1');
        }

        if ($limit) {
            $query->limit((int) $limit, (int) $offset);
        }

        $query->orderBy('f.position ASC');

        $result = Db::getInstance()->executeS($query);

        if (!is_array($result) || !$result) {
            return [];
        }

        return $result;
    }

    public static function getFAQs($active = false, $limit = 0, $offset = 0, $orderWay = 'ASC', $orderBy = 'id_faq')
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $query = new DbQuery();
        $query
            ->select('f.*')
            ->select('fl.*')
            ->from('hifaq', 'f')
            ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaq_shop', 'fs', 'fs.`id_faq` = f.`id_faq`')
            ->where('fl.`id_lang` = ' . (int) $id_lang)
            ->where('fs.`id_shop` = ' . (int) $id_shop);

        if ($active) {
            $query->where('f.active = 1');
        }

        $query
            ->limit((int) $limit, (int) $offset)
            ->orderBy('f.' . pSQL($orderBy) . ' ' . pSQL($orderWay));

        return Db::getInstance()->executeS($query);
    }

    public static function searchFAQs($q, $active = true, $limit = false, $offset = 0, $id_lang = null, $id_shop = null)
    {
        $q = trim($q);

        if (!$q || Tools::strlen($q) < 3) {
            return [];
        }

        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $query = new DbQuery();

        // $q = '%'.$q.'%';
        $words = explode(' ', $q);
        if (!is_array($words) || !$words) {
            return [];
        }

        $query
            ->select('f.*')
            ->select('fl.*')
            ->select('cl.name category_name, cl.id id_category, cl.friendly_url category_link_rewrite')
            ->from('hifaq', 'f')
            ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
            ->leftJoin('hifaqpostcategory', 'fc', 'fc.`id_faq` = fl.`id_faq`')
            ->leftJoin('hifaqcategory_lang', 'cl', 'cl.`id` = fc.`id_category` AND cl.`id_lang` = ' . (int) $id_lang)
            ->leftJoin('hifaq_shop', 'fsh', 'fsh.`id_faq` = f.`id_faq`')
            ->where('fl.`id_lang` = ' . (int) $id_lang)
            ->where('fsh.`id_shop` = ' . (int) $id_shop);

        $titleQuery = '(';
        $questionQuery = '(';
        $answerQuery = '(';
        foreach ($words as $key => $word) {
            $word = '%' . $word . '%';
            if ($key != 0) {
                $titleQuery .= ' AND ';
                $questionQuery .= ' AND ';
                $answerQuery .= ' AND ';
            }
            $titleQuery .= 'fl.`title` LIKE \'' . pSQL($word) . '\'';
            $questionQuery .= 'fl.`question` LIKE \'' . pSQL($word) . '\'';
            $answerQuery .= 'fl.`answer` LIKE \'' . pSQL($word) . '\'';
        }
        $titleQuery .= ')';
        $questionQuery .= ')';
        $answerQuery .= ')';

        $query->where($titleQuery . ' OR ' . $questionQuery . ' OR ' . $answerQuery);

        if ($active) {
            $query->where('f.active = 1');
        }

        if ($limit) {
            $query->limit((int) $limit, (int) $offset);
        }

        $query->groupBy('f.id_faq');

        $result = Db::getInstance()->executeS($query);

        if (!is_array($result) || !$result) {
            return [];
        }

        return $result;
    }

    public static function getDetails($link_rewrite, $id_lang = null, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $query = new DbQuery();
        $faq = Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->select('fl.*')
                ->from('hifaq', 'f')
                ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
                ->leftJoin('hifaq_shop', 'fsh', 'fsh.`id_faq` = f.`id_faq`')
                ->where('fl.`id_lang` = ' . (int) $id_lang)
                ->where('fsh.`id_shop` = ' . (int) $id_shop)
                ->where('fl.`friendly_url` = \'' . pSQL($link_rewrite) . '\'')
        );

        if (!$faq) {
            // Let's check if exists FAQ with the same link_rewrite for other languages
            $query = new DbQuery();
            $faq = Db::getInstance()->getRow(
                $query
                    ->select('f.*')
                    ->select('fl.*')
                    ->from('hifaq', 'f')
                    ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
                    ->leftJoin('hifaq_shop', 'fsh', 'fsh.`id_faq` = f.`id_faq`')
                    ->where('fl.`id_lang` <> ' . (int) $id_lang)
                    ->where('fsh.`id_shop` = ' . (int) $id_shop)
                    ->where('fl.`friendly_url` = \'' . pSQL($link_rewrite) . '\'')
            );
        }

        return $faq;
    }

    public static function getDetailsByID($id_faq, $id_lang = null, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        $query = new DbQuery();
        $faq = Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->select('fl.*')
                ->from('hifaq', 'f')
                ->leftJoin('hifaq_lang', 'fl', 'fl.`id_faq` = f.`id_faq`')
                ->leftJoin('hifaq_shop', 'fsh', 'fsh.`id_faq` = f.`id_faq`')
                ->where('fl.`id_lang` = ' . (int) $id_lang)
                ->where('fsh.`id_shop` = ' . (int) $id_shop)
                ->where('f.`id_faq` = \'' . (int) $id_faq . '\'')
        );

        return $faq;
    }

    public static function getPosition()
    {
        return (int) Db::getInstance()->getValue('SELECT MAX(position) FROM ' . _DB_PREFIX_ . 'hifaq') + 1;
    }

    public static function getRelatedProductsCount($idFaq)
    {
        return (int) Db::getInstance()->getValue(
            '
            SELECT count(`id_hifaqrelatedproduct`)
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedproduct`
            WHERE `id_faq` = ' . (int) $idFaq
        );
    }

    public static function getRelatedFeaturesCount($idFaq)
    {
        return (int) Db::getInstance()->getValue(
            '
            SELECT count(`id_hifaqrelatedproductfeature`)
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedproductfeature`
            WHERE `id_faq` = ' . (int) $idFaq
        );
    }

    public static function getRelatedCategoriesCount($idFaq)
    {
        return (int) Db::getInstance()->getValue(
            '
            SELECT count(`id_hifaqrelatedcategory`)
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedcategory`
            WHERE `id_faq` = ' . (int) $idFaq
        );
    }
}
