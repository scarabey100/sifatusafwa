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
class GiftCardProduct extends Product
{
    public $period_val;

    public $id_currency;

    public $id_customization_field_mailto;

    public $id_customization_field_lastname;

    public $id_customization_field_from;

    public $id_customization_field_message;

    public $id_customization_field_deliverydate;

    public $id_customization_field_template;

    public $id_customization_field_image;

    public $amount;

    public $virtualcard = true;

    public $cr_free_shipping = 0;

    public $cr_partial_use = 1;

    public $isdefaultgiftcard;

    protected $identifier = 'id_product';

    protected static $gift_cards_cache = [];

    public function __construct($id = null, $full = true)
    {
        parent::__construct($id, $full);
        if ($id) {
            $result = Db::getInstance()->getRow('
				SELECT g.*
				FROM `' . _DB_PREFIX_ . 'giftcardproduct` g
				WHERE g.id_product=' . (int) $id);
            if (!$result) {
                return false;
            }
            foreach ($result as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = Tools::stripslashes($value);
                }
            }
        }
    }

    public function changeToDefault()
    {
        $result = Db::getInstance()->update('giftcardproduct', [
            'isdefaultgiftcard' => 0,
        ]);
        if ($result) {
            $result &= Db::getInstance()->update('giftcardproduct', [
                'isdefaultgiftcard' => 1,
            ], '`id_product` = ' . (int) $this->id);
        }

        return $result;
    }

    public static function updateAllCustomFields()
    {
        $sql = 'SELECT g.*
		FROM `' . _DB_PREFIX_ . 'giftcardproduct` g';
        $results = Db::getInstance()->ExecuteS($sql);
        foreach ($results as $result) {
            $id_product = (int) $result['id_product'];
            $giftcard = new GiftCardProduct($id_product);
            $giftcard->updateCustomizationFields();
        }
    }

    public function updateCustomizationFields()
    {
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_TO', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_mailto .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_FROM', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_from .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_MESSAGE', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_message .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_DATESEND', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_deliverydate .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_TEMPLATE', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_template .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_LASTNAME', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_lastname .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
            Db::getInstance()->update('customization_field_lang', [
                'name' => pSQL(Configuration::get('GIFTCARD_CF_IMAGE', (int) $language['id_lang'])),
            ], '`id_customization_field` = ' . (int) $this->id_customization_field_image .
                ' AND `id_lang` = ' . (int) $language['id_lang']);
        }
    }

    public function addCustomizationFields()
    {
        $languages = Language::getLanguages(true);
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 1)') || !$this->id_customization_field_lastname = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 0)') || !$this->id_customization_field_mailto = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 1)') || !$this->id_customization_field_from = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 1)') || !$this->id_customization_field_message = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 0)') || !$this->id_customization_field_deliverydate = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 1, 1)') || !$this->id_customization_field_template = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'customization_field` (`id_product`, `type`, `required`)
            VALUES (' . (int) $this->id . ', 0, 0)') || !$this->id_customization_field_image = (int) Db::getInstance()->Insert_ID()) {
            return false;
        }
        foreach ($languages as $language) {
            $id_lang = (int) $language['id_lang'];
            if (version_compare(_PS_VERSION_, '1.6.0.12', '<')) {
                $this->addCustomizeFieldLang($id_lang);
            } else {
                $shops = Shop::getShops(true, null, true);
                foreach (Shop::getContextListShopID() as $id_shop) {
                    $this->addCustomizeFieldLang($id_lang, $id_shop);
                }
            }
        }

        return true;
    }

    public function addCustomizeFieldLang($id_lang, $id_shop = 0)
    {
        $data_to = [
            'id_customization_field' => (int) $this->id_customization_field_mailto,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_TO', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_to['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_to)) {
            return false;
        }
        $data_from = [
            'id_customization_field' => (int) $this->id_customization_field_from,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_FROM', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_from['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_from)) {
            return false;
        }
        $data_message = [
            'id_customization_field' => (int) $this->id_customization_field_message,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_MESSAGE', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_message['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_message)) {
            return false;
        }
        $data_datesend = [
            'id_customization_field' => (int) $this->id_customization_field_deliverydate,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_DATESEND', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_datesend['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_datesend)) {
            return false;
        }
        $data_lastname = [
            'id_customization_field' => (int) $this->id_customization_field_lastname,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_LASTNAME', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_lastname['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_lastname)) {
            return false;
        }
        $data_template = [
            'id_customization_field' => (int) $this->id_customization_field_template,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_TEMPLATE', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_template['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_template)) {
            return false;
        }
        $data_image = [
            'id_customization_field' => (int) $this->id_customization_field_image,
            'id_lang' => (int) $id_lang,
            'name' => pSQL(Configuration::get('GIFTCARD_CF_IMAGE', (int) $id_lang)),
        ];
        if ((int) $id_shop) {
            $data_image['id_shop'] = $id_shop;
        }
        if (!Db::getInstance()->insert('customization_field_lang', $data_image)) {
            return false;
        }
    }

    public static function getDefault()
    {
        $result = Db::getInstance()->getRow('
				SELECT g.id_product
				FROM `' . _DB_PREFIX_ . 'giftcardproduct` g
				WHERE g.isdefaultgiftcard=1');
        if (!$result) {
            return false;
        }
        $id_product = (int) $result['id_product'];
        $gift_card = new GiftCardProduct($id_product);

        return $gift_card;
    }

    public function add($autodate = true, $null_values = false, $dupplicate = false)
    {
        $this->id_category_default = (int) Configuration::get('GIFTCARD_CATEGORY_ID');
        if (!$dupplicate && !$this->isdefaultgiftcard && !self::getDefault()) {
            $this->isdefaultgiftcard = 1;
        }
        if (!$dupplicate) {
            $this->visibility = 'none';
            $this->customizable = 1;
            $this->uploadable_files = 1;
            if ($this->virtualcard) {
                $this->is_virtual = 1;
            }
            $this->text_fields = 6;
            $this->out_of_stock = 1;
            $languages = Language::getLanguages(false);
            $this->link_rewrite = [];
            foreach ($languages as $language) {
                $this->link_rewrite[(int) $language['id_lang']] = 'giftcard' .
                    (isset($this->name[(int) $language['id_lang']]) ? '-' .
                        Tools::link_rewrite($this->name[(int) $language['id_lang']]) : '');
            }
        } else {
            $this->isdefaultgiftcard = 0;
        }
        if (!parent::add() || !$this->addToCategories($this->id_category_default)) {
            return false;
        }
        if (!$this->addCustomizationFields()) {
            return false;
        }
        if (!Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'giftcardproduct`
            (`id_product`, `period_val`, `amount`,`cr_partial_use`, `cr_free_shipping`,
            `id_customization_field_mailto`,`id_customization_field_message`,`id_customization_field_lastname`,
            `id_customization_field_from`,`id_customization_field_deliverydate`,`id_customization_field_template`,
            `id_customization_field_image`,`id_currency`,`isdefaultgiftcard`,`virtualcard`)
            VALUES (' . $this->id . ',' . (int) $this->period_val . ',' .
            (float) $this->amount . ',' . $this->cr_partial_use . ',' . $this->cr_free_shipping . ',' .
            $this->id_customization_field_mailto . ',' . $this->id_customization_field_message . ',' .
            $this->id_customization_field_lastname . ',' . $this->id_customization_field_from . ',' .
            $this->id_customization_field_deliverydate . ',' . $this->id_customization_field_template . ',' .
            $this->id_customization_field_image . ',' . $this->id_currency . ',' .
            ($this->isdefaultgiftcard ? 1 : 0) . ',' . $this->virtualcard . '.)')) {
            return false;
        }

        return true;
    }

    public function update($autodate = true, $null_values = false)
    {
        if ($this->virtualcard) {
            $this->is_virtual = 1;
        } else {
            $this->is_virtual = 0;
        }
        $res = Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'giftcardproduct
											SET id_currency = ' . (int) $this->id_currency . ',
											    amount = ' . (float) $this->amount . ',
												virtualcard = ' . (int) $this->virtualcard . ',
												cr_partial_use = ' . (int) $this->cr_partial_use . ',
												cr_free_shipping = ' . (int) $this->cr_free_shipping . ',
												period_val = ' . (int) $this->period_val . ' WHERE id_product=' . $this->id);
        $res .= parent::update($autodate, $null_values);

        return $res;
    }

    public function delete($autodate = true, $null_values = false)
    {
        $res = parent::delete($autodate, $null_values);
        if ($res
            && Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'giftcardproduct WHERE id_product=' . $this->id
            )) {
            return true;
        }

        return false;
    }

    public static function getGiftCards(
        $id_lang = null,
        $active = false,
        $id_currency = null,
        $id_shop = null,
        $with_prop = true
    ) {
        if ($id_lang === null) {
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        $sql = 'SELECT distinct(g.id_product),g.`virtualcard`,pl.name,
        pl.link_rewrite,amount,id_customization_field_mailto,
        id_customization_field_message,id_customization_field_deliverydate,id_customization_field_template,
        id_customization_field_lastname,id_customization_field_from,id_customization_field_image,
        id_currency,isdefaultgiftcard,p.*
        FROM `' . _DB_PREFIX_ . 'giftcardproduct` g
        INNER JOIN `' . _DB_PREFIX_ . 'product` p ON (p.`id_product` = g.`id_product`)
        ' . ($id_shop ? ' INNER JOIN `' . _DB_PREFIX_ . 'product_shop` pshop ON (pshop.`id_product` = p.`id_product`
        AND pshop.id_shop=' . (int) $id_shop . ')' : ' ')
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
        ON (p.`id_product` = pl.`id_product`) and pl.id_lang = ' . $id_lang .
            ' WHERE 1=1
        ' . ($active ? ' AND p.active=1 ' : '') .
            (isset($id_currency) ? ' AND (g.id_currency=0 || g.id_currency=' . (int) $id_currency . ') ' : '') . '
        GROUP BY p.id_product
        ORDER BY `price`';
        $products = Db::getInstance()->ExecuteS($sql);
        if ($with_prop) {
            $products = Product::getProductsProperties($id_lang, $products);
        }

        return $products;
    }

    public static function getGiftCardIn($products)
    {
        $filterproducts = '';
        foreach ($products as $key => $product) {
            if (isset($filterproducts) && !empty($filterproducts)) {
                $filterproducts .= ' OR `id_product` = ' . $product['id_product'];
            } else {
                $filterproducts .= '`id_product` = ' . $product['id_product'];
            }
        }
        $result = Db::getInstance()->ExecuteS(
            'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'giftcardproduct` WHERE ' . $filterproducts
        );
        $tab = null;
        foreach ($result as $key => $row) {
            $tab[] = (int) $row['id_product'];
        }
        $tabin = null;
        if ($tab != null) {
            foreach ($products as $key => $product) {
                if (in_array((int) $product['id_product'], $tab)) {
                    $tabin[] = $product;
                }
            }
        }

        return $tabin;
    }

    public static function getGiftCardsId()
    {
        if (self::$gift_cards_cache == null) {
            $result = Db::getInstance()->ExecuteS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'giftcardproduct`');
            self::$gift_cards_cache = [];
            foreach ($result as $key => $row) {
                self::$gift_cards_cache[(int) $row['id_product']] = true;
            }
        }

        return self::$gift_cards_cache;
    }

    /**
     * Specify if a giftcard is already in database
     *
     * @param $id_product Product
     *
     * @return bool
     */
    public static function isGiftCard($id_product)
    {
        $giftcard_hash_map = self::getGiftCardsId();

        return isset($giftcard_hash_map[(int) $id_product]);
    }

    public function getFixedPrice($id_currency)
    {
        $specificprice = SpecificPrice::getSpecificPrice($this->id, 0, $id_currency, 0, 0, 0, null, 0, 0, 0);

        return (float) $specificprice['price'];
    }

    public static function duplicate($id_product_old)
    {
        $card = new GiftCardProduct($id_product_old);
        if (Validate::isLoadedObject($card = new GiftCardProduct((int) Tools::getValue('id_product')))) {
            unset($card->id);
            unset($card->id_product);
            $card->indexed = 0;
            $card->active = 0;
            if ($card->add(true, false, true)) {
                if (!Category::duplicateProductCategories($id_product_old, $card->id)) {
                    return false;
                }
                if (!Product::duplicateSpecificPrices($id_product_old, $card->id)) {
                    return false;
                }
                if (!Tools::getValue('noimage')
                    && !Image::duplicateProductImages($id_product_old, $card->id, [])) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return $card;
    }

    /* Controle */
    public static function getAmount($id_product)
    {
        $sql = new DbQuery();
        $sql->select('amount');
        $sql->from('giftcardproduct', 'g');
        $sql->where('g.id_product = ' . (int) $id_product);

        return (float) Db::getInstance()->getValue($sql);
    }

    public function validityPrice($id_currency)
    {
        $fixedprice = $this->getFixedPrice($id_currency);
        if ($fixedprice == $this->amount) {
            return true;
        }

        return false;
    }
}
