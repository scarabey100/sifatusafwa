<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class GiftCardTag extends ObjectModel
{
    /**
     * @var int Language id
     */
    public $id_lang;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'giftcardtag',
        'primary' => 'id_gift_card_tag',
        'fields' => [
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 32,
            ],
        ],
    ];

    public function __construct($id = null, $name = null, $id_lang = null)
    {
        $this->def = self::getDefinition($this);
        $this->setDefinitionRetrocompatibility();
        if ($id) {
            parent::__construct($id);
        } else {
            if ($name && Validate::isGenericName($name) && $id_lang && Validate::isUnsignedId($id_lang)) {
                $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `' . _DB_PREFIX_ . 'giftcardtag` t
			WHERE `name` LIKE \'' . pSQL($name) . '\' AND `id_lang` = ' . (int) $id_lang);
                if ($row) {
                    $this->id = (int) $row['id_gift_card_tag'];
                    $this->id_lang = (int) $row['id_lang'];
                    $this->name = $row['name'];
                }
            }
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)) {
            return false;
        }

        return true;
    }

    /**
     * Add several tags in database and link it to a gift_card_template
     *
     * @param int $id_lang
     *                     Language id
     * @param int $id_gift_card_template
     *                                   gift_card_template id to link tags with
     * @param string|array $tag_list
     *                               List of tags, as array or as a string with comas
     *
     * @return bool Operation success
     */
    public static function addTags($id_lang, $id_gift_card_template, $tag_list, $separator = ',')
    {
        if (!Validate::isUnsignedId($id_lang)) {
            return false;
        }
        if (!is_array($tag_list)) {
            $tag_list = array_filter(
                array_unique(
                    array_map('trim', preg_split('#\\' . $separator . '#', $tag_list, 0, PREG_SPLIT_NO_EMPTY))
                )
            );
        }
        $list = [];
        if (is_array($tag_list)) {
            foreach ($tag_list as $tag) {
                if (!Validate::isGenericName($tag)) {
                    return false;
                }
                $tag = trim(Tools::substr($tag, 0, self::$definition['fields']['name']['size']));
                $tag_obj = new GiftCardTag(null, $tag, (int) $id_lang);
                /* Tag does not exist in database */
                if (!Validate::isLoadedObject($tag_obj)) {
                    $tag_obj->name = $tag;
                    $tag_obj->id_lang = (int) $id_lang;
                    $tag_obj->add();
                }
                if (!in_array($tag_obj->id, $list)) {
                    $list[] = $tag_obj->id;
                }
            }
        }
        $data = '';
        foreach ($list as $tag) {
            $data .= '(' . (int) $tag . ',' . (int) $id_gift_card_template . '),';
        }
        $data = rtrim($data, ',');

        return Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'giftcardtemplate_tag` (`id_gift_card_tag`, `id_gift_card_template`)
		VALUES ' . $data);
    }

    public static function getTags(Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $context->language->id;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT t.`id_gift_card_tag`, t.`name`
		FROM ' . _DB_PREFIX_ . 'giftcardtag t
		WHERE t.`id_lang`=' . (int) $id_lang);

        return $result;
    }

    public static function getTemplateTags($id_gift_card_template)
    {
        if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT t.`id_lang`, t.`name`
		FROM ' . _DB_PREFIX_ . 'giftcardtag t
		LEFT JOIN ' . _DB_PREFIX_ . 'giftcardtemplate_tag tt ON (tt.id_gift_card_tag = t.id_gift_card_tag)
		WHERE tt.`id_gift_card_template`=' . (int) $id_gift_card_template)) {
            return false;
        }
        $result = [];
        foreach ($tmp as $tag) {
            $result[$tag['id_lang']][] = $tag['name'];
        }

        return $result;
    }

    public function getTemplates($associated = true, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $id_lang = $this->id_lang ? $this->id_lang : $context->language->id;
        if (!$this->id && $associated) {
            return [];
        }
        $in = $associated ? 'IN' : 'NOT IN';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT tl.name, tl.id_gift_card_template
        FROM `' . _DB_PREFIX_ . 'giftcardtemplate` t
        LEFT JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_lang` tl ON t.id_gift_card_template = tl.id_gift_card_template 
        WHERE tl.id_lang = ' . (int) $id_lang .
            ($this->id ? (' AND t.id_gift_card_template ' . $in .
                ' (SELECT tt.id_gift_card_template FROM `' . _DB_PREFIX_ . 'giftcardtemplate_tag` tt WHERE tt.id_tag = ' .
                (int) $this->id . ')') : '') . ' ORDER BY tl.name');
    }

    public static function deleteTagsForTemplate($id_gift_card_template)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'giftcardtemplate_tag` WHERE `id_gift_card_template` = ' .
            (int) $id_gift_card_template
        );
    }
}
