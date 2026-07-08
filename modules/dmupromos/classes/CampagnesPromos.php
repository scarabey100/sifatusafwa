<?php
/**
 * DISCLAIMER.
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future. If you wish to customize the Module for
 * your needs please use "themes/" or/and "override/modules" directories
 * or refer to http://www.prestashop.com for more information.
 *
 *  .--.
 *  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
 *  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
 *  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
 *       w w w . d r e a m - m e - u p . f r       '
 *
 * @author    Dream me up <prestashop@dream-me-up.fr>
 * @copyright 2007 - 2024 Dream me up
 * @license   All Rights Reserved
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CampagnesPromos extends ObjectModel
{
    public $id;

    public $name;
    public $date_debut;
    public $date_fin;
    public $search_only_active;
    public $search_new_age;
    public $search_stock_age;
    public $apply_on_stock_only;
    public $is_onsale;
    public $id_shop;
    public $id_currency;
    public $id_country;
    public $id_group;

    protected $fieldsValidate = ['name' => 'isString'];
    protected $fieldsRequired = ['name', 'date_debut', 'date_fin'];

    protected $table = 'promos_campagnes';
    protected $tables = ['promos_campagnes'];
    protected $identifier = 'id_promos_campagnes';

    public static $definition = [
        'table' => 'promos_campagnes',
        'primary' => 'id_promos_campagnes',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'date_debut' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
            'date_fin' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$this->id_shop) {
            $this->id_shop = (int) Shop::getContextShopID();
        }
    }

    public function getFields()
    {
        parent::validateFields();
        $fields = [];
        if (!empty($this->id)) {
            $fields['id_promos_campagnes'] = (int) $this->id;
        }
        $fields['date_debut'] = pSQL($this->date_debut);
        $fields['date_fin'] = pSQL($this->date_fin);
        if (!strpos(' ', $fields['date_fin'])) {
            $fields['date_fin'] .= ' 23:59:59';
        }
        $fields['name'] = pSQL($this->name);
        $fields['search_only_active'] = pSQL($this->search_only_active);
        $fields['search_new_age'] = pSQL($this->search_new_age);
        $fields['search_stock_age'] = pSQL($this->search_stock_age);
        $fields['apply_on_stock_only'] = pSQL($this->apply_on_stock_only);
        $fields['is_onsale'] = $this->is_onsale ? true : false;

        if (!$this->id_shop) {
            $this->id_shop = Shop::getContextShopID();
            if (empty($this->id_shop)) {
                $this->id_shop = Configuration::get('PS_SHOP_DEFAULT');
            }
        }
        $fields['id_shop'] = (int) $this->id_shop;
        $fields['id_currency'] = (int) $this->id_currency;
        $fields['id_country'] = (int) $this->id_country;
        $fields['id_group'] = (int) $this->id_group;

        return $fields;
    }

    public function update($null_values = false)
    {
        // Mise à jour des dates des Prix spécifiques
        if ($this->getSpecificPricesIds()) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . "specific_price`
                    SET `from` = '" . pSQL($this->date_debut) . "',
                        `to` = '" . pSQL($this->date_fin) . "',
                        `id_shop` = " . (int) $this->id_shop . ',
                        `id_currency` = ' . (int) $this->id_currency . ',
                        `id_country` = ' . (int) $this->id_country . ',
                        `id_group` = ' . (int) $this->id_group . '
                    WHERE id_specific_price IN (' . implode(',', $this->getSpecificPricesIds()) . ')';
            Db::getInstance()->execute($sql);
        }
        $this->checkIsOnSale();

        return parent::update($null_values);
    }

    public function delete()
    {
        // Suppression de l'affichage  « en solde ! » si campagne en cours
        if ($this->is_onsale) {
            $this->date_debut = '0000-00-00 00:00:00';
            $this->date_fin = '0000-00-00 00:00:01';
            $this->checkIsOnSale();
        }

        // Suppression des Prix spécifiques associés
        if ($this->getSpecificPricesIds()) {
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'specific_price`
                    WHERE id_specific_price IN (' . implode(',', $this->getSpecificPricesIds()) . ')';
            Db::getInstance()->execute($sql);
        }

        // Suppression des associations produits
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'promos_produits`
                WHERE id_promos_campagnes = ' . $this->id;
        Db::getInstance()->execute($sql);

        return parent::delete();
    }

    public function recurseCategoryNbProds(&$tree, $categories, $current, $id_category = 1, $id_selected = 1)
    {
        // Récupération du nombre de produits de cette catégorie
        $sql = 'SELECT COUNT(cp.id_product) AS nb_products
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                INNER JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = cp.id_product
                WHERE id_category = ' . (int) $id_category;
        $nb_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        // Récupération du nombre de produits en reduc pour cette catégorie
        $sql = 'SELECT DISTINCT cp.id_product
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                INNER JOIN ' . _DB_PREFIX_ . 'product p
                    ON p.id_product = cp.id_product
                INNER JOIN ' . _DB_PREFIX_ . 'promos_produits pp
                    ON p.id_product = pp.id_product AND pp.id_promos_campagnes = ' . (int) $this->id . '
                WHERE id_category = ' . (int) $id_category;
        $nb_products_reduc = count(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        $current['nb_products'] = $nb_products;
        $current['nb_products_reduc'] = $nb_products_reduc;

        if (!(0 == $current['infos']['id_parent'] && 0 == $current['infos']['is_root_category'])) {
            $tree[] = $current;
        }
        if (isset($categories[$id_category])) {
            foreach (array_keys($categories[$id_category]) as $key) {
                $this->recurseCategoryNbProds($tree, $categories, $categories[$id_category][$key], $key, $id_selected);
            }
        }
    }

    /*
    *  Prix spécifique associé au produit de cette campagne
    *  Array
    */
    public function getSpecificPrice($id_product, $id_product_attribute = 0)
    {
        $cache_key = 'specific_price_by_campagne_' . (int) $this->id . '_' . (int) $id_product . '_' . (int) $id_product_attribute;
        if (!Cache::isStored($cache_key)) {
            $result = false;

            $sql = 'SELECT sp.* FROM `' . _DB_PREFIX_ . 'specific_price` sp
                    INNER JOIN `' . _DB_PREFIX_ . 'promos_produits` pp ON sp.id_specific_price = pp.id_specific_price
                    WHERE pp.id_promos_campagnes = ' . (int) $this->id . '
                        AND pp.id_product = ' . (int) $id_product . '
                        AND pp.id_product_attribute = ' . (int) $id_product_attribute;
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    /*
    *  Liste des « id_specific_price » de la campagne
    *  Array
    */
    public function getSpecificPricesIds()
    {
        $cache_key = 'specific_prices_ids_id_promos_campagnes_' . (int) $this->id;
        if (!Cache::isStored($cache_key)) {
            $specific_prices_ids_list = [];
            $sql = 'SELECT id_specific_price FROM `' . _DB_PREFIX_ . 'promos_produits`
                    WHERE id_promos_campagnes = ' . (int) $this->id;
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $specific_price) {
                $specific_prices_ids_list[] = $specific_price['id_specific_price'];
            }
            $result = $specific_prices_ids_list;
            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    /*
    *  Mise à jour du statut « En solde ! » des produits de la campagne
    */
    public function checkIsOnSale()
    {
        $now = date('Y-m-d H:i:s');
        if ($this->is_onsale) {
            if (($this->date_debut > $now) || ($this->date_fin < $now)) {
                // Suppression de la mention « En solde ! »
                $discount_products_ids = [];
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                        WHERE id_promos_campagnes = ' . $this->id;
                foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $promos_produit) {
                    $discount_products_ids[] = $promos_produit['id_product'];
                }
                if ($discount_products_ids) { // ajouts
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'product`
                            SET on_sale = 0
                            WHERE id_product IN (' . implode(',', $discount_products_ids) . ')';
                    Db::getInstance()->execute($sql);
                    if ($this->id_shop) {
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop`
                                SET on_sale = 0
                                WHERE id_shop = ' . (int) $this->id_shop . ' AND id_product IN (' .
                                    implode(',', $discount_products_ids) . ')';
                        Db::getInstance()->execute($sql);
                    }
                }
                $this->is_onsale = false;
            }
        } else {
            if (($this->date_debut <= $now) && ($this->date_fin >= $now)) {
                // Ajout (ou suppression) de la mention « En solde ! »
                $discount_products_ids = [];
                $discount_products_ids_off = [];
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                        WHERE id_promos_campagnes = ' . $this->id;
                foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $promos_produit) {
                    if ($promos_produit['solde']) {
                        $discount_products_ids[] = $promos_produit['id_product'];
                    } else {
                        $discount_products_ids_off[] = $promos_produit['id_product'];
                    }
                }
                if ($discount_products_ids) { // ajouts
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'product`
                            SET on_sale = 1
                            WHERE id_product IN (' . implode(',', $discount_products_ids) . ')';
                    Db::getInstance()->execute($sql);
                    if ($this->id_shop) {
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop`
                                SET on_sale = 1
                                WHERE id_shop = ' . (int) $this->id_shop . ' AND id_product IN (' .
                                    implode(',', $discount_products_ids) . ')';
                        Db::getInstance()->execute($sql);
                    }
                }
                if ($discount_products_ids_off) { // suppressions
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'product`
                            SET on_sale = 0
                            WHERE id_product IN (' . implode(',', $discount_products_ids_off) . ')';
                    Db::getInstance()->execute($sql);
                    if ($this->id_shop) {
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop`
                                SET on_sale = 0
                                WHERE id_shop = ' . (int) $this->id_shop . ' AND id_product IN (' .
                                    implode(',', $discount_products_ids_off) . ')';
                        Db::getInstance()->execute($sql);
                    }
                }
                $this->is_onsale = true;
            }
        }
    }

    /*
    *  Mise à jour du statut « En solde ! » des produits de toutes les campagnes
    */
    public static function updateOnSales()
    {
        $conf_key = 'DMUPROMOS_ONSALE_CHECKED_TIME';
        // <= Délai de 5 minutes avant prochain Check
        if (!($onsale_checked_time = Configuration::getGlobalValue($conf_key))
            || ($onsale_checked_time < (time() - 300))) {
            Configuration::updateGlobalValue($conf_key, time());

            // Recherche de campagnes affichées « En solde ! » hors période de campagne
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_campagnes`
                    WHERE is_onsale = 1 AND ( NOW() < date_debut OR NOW() > date_fin )';
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $campagne_array) {
                $campagne = new CampagnesPromos($campagne_array['id_promos_campagnes']);
                $is_onsale = $campagne->is_onsale;
                $campagne->checkIsOnSale();
                if ($campagne->is_onsale != $is_onsale) {
                    $campagne->update();
                }
            }
            // Recherche de campagnes a afficher « En solde ! »
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_campagnes`
                    WHERE is_onsale = 0 AND NOW() >= date_debut AND NOW() <= date_fin';
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $campagne_array) {
                $campagne = new CampagnesPromos($campagne_array['id_promos_campagnes']);
                $is_onsale = $campagne->is_onsale;
                $campagne->checkIsOnSale();
                if ($campagne->is_onsale != $is_onsale) {
                    $campagne->update();
                }
            }
        }
    }

    public static function getProductsFromCampagne($id_product, $id_product_attribute)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'promos_produits pp
                INNER JOIN ' . _DB_PREFIX_ . 'promos_campagnes pc ON pp.id_promos_campagnes = pc.id_promos_campagnes
                WHERE pp.id_product=' . (int) $id_product . ' AND pp.id_product_attribute=' . (int) $id_product_attribute . ' ';
        $campagnes_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $campagnes_products;
    }

    public static function deleteProductFromCampagne($id_promos_campagnes, $id_product, $id_product_attribute)
    {
        $sql = 'SELECT id_specific_price FROM ' . _DB_PREFIX_ . 'promos_produits
                WHERE id_promos_campagnes=' . (int) $id_promos_campagnes . ' 
                AND id_product=' . (int) $id_product . ' AND id_product_attribute=' . (int) $id_product_attribute . ' ';
        $id_specific_price = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'specific_price`
                WHERE id_specific_price=' . (int) $id_specific_price;
        Db::getInstance()->execute($sql);

        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'promos_produits`
                WHERE id_promos_campagnes=' . (int) $id_promos_campagnes . ' 
                AND id_product=' . (int) $id_product . ' AND id_product_attribute=' . (int) $id_product_attribute . ' ';
        Db::getInstance()->execute($sql);
    }

    public static function verifyDeletedSpecificPrices()
    {
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'promos_produits 
                WHERE id_specific_price NOT IN (SELECT id_specific_price FROM ' . _DB_PREFIX_ . 'specific_price)';
        Db::getInstance()->execute($sql);
    }
}
