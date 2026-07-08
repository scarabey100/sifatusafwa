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

class HiFAQFeedback extends ObjectModel
{
    public $id_feedback;
    public $id_faq;
    public $id_customer;
    public $id_guest;
    public $ip_address;
    public $feedback;
    public $comment;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'hifaqfeedback',
        'primary' => 'id_feedback',
        'multilang' => false,
        'fields' => [
            'id_faq' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_guest' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'ip_address' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'feedback' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'comment' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getFeedbackByCustomer($idFaq, $ipAddress, $idCustomer = 0, $idGuest = 0)
    {
        // find feedback by ip address
        $query = new DbQuery();
        $feedback = Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->from('hifaqfeedback', 'f')
                ->where('f.id_faq = ' . (int) $idFaq)
                ->where('f.ip_address = \'' . pSQL($ipAddress) . '\'')
        );

        if ($feedback) {
            return $feedback;
        }

        // find feedback by id customer
        $query = new DbQuery();
        $feedback = Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->from('hifaqfeedback', 'f')
                ->where('f.id_faq = ' . (int) $idFaq)
                ->where('f.`id_customer` = ' . (int) $idCustomer)
        );

        if ($feedback) {
            return $feedback;
        }

        // find feedback by id guest
        $query = new DbQuery();
        $feedback = Db::getInstance()->getRow(
            $query
                ->select('f.*')
                ->from('hifaqfeedback', 'f')
                ->where('f.id_faq = ' . (int) $idFaq)
                ->where('f.`id_guest` = ' . (int) $idGuest)
        );

        return $feedback;
    }

    public static function filterFeedbacks($filter, $pageNumber, $pageItems)
    {
        $searchStatus = false;
        $searchTitle = false;

        if (isset($filter['hifaqfeedbackFilter_faqFeedbackStatus'])) {
            $searchStatus = $filter['hifaqfeedbackFilter_faqFeedbackStatus'];
        }

        if (isset($filter['hifaqfeedbackFilter_title'])) {
            $searchTitle = $filter['hifaqfeedbackFilter_title'];
        }

        $idLang = (int) Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('f.*, fl.`title`');
        $query->from('hifaqfeedback', 'f');
        $query->leftJoin('hifaq_lang', 'fl', 'f.`id_faq` = fl.`id_faq`');
        $query->where('fl.`id_lang` =' . (int) $idLang);

        if ($searchStatus !== false) {
            $query->where('f.feedback = ' . (int) $searchStatus);
        }

        if ($searchTitle) {
            $query->where('fl.`title` like "%' . pSQL($searchTitle) . '%"');
        }

        $res = Db::getInstance()->executeS($query);
        $total = 0;
        if ($res) {
            $total = count($res);
        }

        $query->limit((int) $pageItems, (int) (($pageNumber - 1) * $pageItems));
        $query->orderBy('f.date_add DESC');

        return [
            'total' => $total,
            'result' => Db::getInstance()->executeS($query),
        ];
    }

    public static function getLatestFeedbackId()
    {
        return (int) Db::getInstance()->getValue('SELECT MAX(id_feedback) FROM ' . _DB_PREFIX_ . 'hifaqfeedback');
    }

    public static function getNewFeedbacksCount($lastSeenIdFeedback)
    {
        return (int) Db::getInstance()->getValue('SELECT count(id_feedback) FROM ' . _DB_PREFIX_ . 'hifaqfeedback WHERE `id_feedback` > ' . (int) $lastSeenIdFeedback);
    }

    public static function getTotalFeedbacksCount()
    {
        return (int) Db::getInstance()->getValue('SELECT count(id_feedback) FROM ' . _DB_PREFIX_ . 'hifaqfeedback');
    }

    public static function getFeedbacksCountByIdFaq($idFaq, $feedback = 1)
    {
        return (int) Db::getInstance()->getValue('
            SELECT count(id_feedback)
            FROM ' . _DB_PREFIX_ . 'hifaqfeedback
            WHERE `id_faq` = ' . (int) $idFaq . '
            AND feedback = ' . (int) $feedback);
    }
}
