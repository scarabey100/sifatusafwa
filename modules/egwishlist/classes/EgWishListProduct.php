<?php
/**
 * 2019 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

class EgWishListProduct extends ObjectModel
{
    public $id_egwishlist_product;
    public $id_product;
    public $id_customer;
    public $id_product_attribute;
    public $date_add;
    public $id_shop;

    public static $definition = array(
        'table' => 'egwishlist_product',
        'primary' => 'id_egwishlist_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false),
        ),
    );

    /**
     * @param $idCustomer
     * @return int ProductsNb
     */
    public static function getWishlistProductsNb($idCustomer)
    {
        if (!$idCustomer) {
            return false;
        }

        $idShop = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;
     
        $sql = 'SELECT DISTINCT p.*, product_shop.*, pl.*, image_shop.`id_image` AS id_image, il.`legend`, cl.`name` AS category_default, 
                product_shop.`id_category_default`, a.id_egwishlist_product, a.id_product, a.id_product_attribute
                FROM `' . _DB_PREFIX_ . 'egwishlist_product` a
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = a.id_product 
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON p.id_product = pl.id_product
                    AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                    ON image_shop.`id_product` = p.`id_product` AND image_shop.cover = 1 AND image_shop.id_shop = ' . (int)$idShop . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il 
                    ON image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang . '
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                WHERE product_shop.`id_shop` = ' . (int)$idShop . ' AND product_shop.`active` = 1';

        if ($idCustomer) {
            $sql .= ' AND a.`id_customer` = ' . (int)$idCustomer;
        }

        // Group by product and product attribute for distinct results
        $sql .= ' GROUP BY a.`id_product`';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
 
        return count($result);
    }

    /**
     * @param $idCustomer
     * @return int ProductsNb
     */
    public static function getWishlistProductsNb2($idCustomer)
    {
        if (!$idCustomer) {
            return false;
        }

        $idShop = Context::getContext()->shop->id;

        return Db::getInstance()->getValue('
			SELECT COUNT(*)
			FROM `' . _DB_PREFIX_ . 'wishlist_product` wp
            INNER JOIN `'._DB_PREFIX_.'wishlist` AS `w` ON `w`.`id_wishlist` = `wp`.`id_wishlist`
			LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = wp.id_product
            ' . Shop::addSqlAssociation('product', 'p') . '
			WHERE `w`.`id_customer` = ' . (int)$idCustomer . '
			AND w.`id_shop` = ' . (int) $idShop);
    }

    public static function refreshShortlistData()
    {
        $context = Context::getContext();
        $shortlisted_products = array();
        if ($context->cookie->bcm_short_list != '') {
            $shortlisted = explode(',',Context::getContext()->cookie->bcm_short_list);
        } else {
            $shortlisted = array();
        }

        if (count($shortlisted) > 0) {
            foreach ($shortlisted as $id_product) {
                if ($id_product != 0) {
                    $shortlisted_products[] = array(
                        'id_product' => $id_product,
                    );
                }

            }
        }
        return $shortlisted_products;
    }

    public static function checkProductInShortList($idProduct)
    {
        $context = Context::getContext();
        $idLang = (int) $context->language->id;
        $idCustomer = (int) $context->customer->id;
        $shortList = 0;
        if ($context->customer->isLogged()) {
            $egShortListProducts =  self::getWishlistProducts($idCustomer, $idLang);
            foreach ($egShortListProducts as $product) {
                if ($product['id_product'] == $idProduct) {
                    $shortList = 1;
                    break;
                }
            }
        } else {
            $refreshShortlistData = self::refreshShortlistData();
            foreach ($refreshShortlistData as $product) {
                if ($product['id_product'] == $idProduct) {
                    $shortList = 1;
                    break;
                }
            }
        }
        return $shortList;
    }

    /**
     * @param $idCustomer
     * @param $idProduct
     * @param $idProductAttribute
     * @return bool
     */
    public static function isCustomerWishlistProduct($idCustomer, $idProduct, $idProductAttribute)
    {
        if (!$idCustomer) {
            return false;
        }
        $idShop = Context::getContext()->shop->id;
        return (bool)Db::getInstance()->getValue('
			SELECT COUNT(*)
			FROM `' . _DB_PREFIX_ . 'egwishlist_product`
			WHERE `id_customer` = ' . (int)$idCustomer . '
			AND `id_product` = ' . (int)$idProduct . '
			AND `id_product_attribute` = ' . (int)$idProductAttribute . '
			AND `id_shop` = ' . (int) $idShop);
    }

    public static function getWishlistProducts($idCustomer, $id_lang, $full = false)
    {
        $context = Context::getContext();
        $idShop = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;
        $sql = 'SELECT DISTINCT p.*, product_shop.*, pl.*, image_shop.`id_image` AS id_image, il.`legend`, cl.`name` AS category_default, 
                product_shop.`id_category_default`, a.id_egwishlist_product, a.id_product, a.id_product_attribute
                FROM `' . _DB_PREFIX_ . 'egwishlist_product` a
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = a.id_product 
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON p.id_product = pl.id_product
                    AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                    ON image_shop.`id_product` = p.`id_product` AND image_shop.cover = 1 AND image_shop.id_shop = ' . (int)$idShop . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il 
                    ON image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang . '
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                WHERE product_shop.`id_shop` = ' . (int)$idShop . ' AND product_shop.`active` = 1';

        if ($idCustomer) {
            $sql .= ' AND a.`id_customer` = ' . (int)$idCustomer;
        }

        // Group by product and product attribute for distinct results
        $sql .= ' GROUP BY a.`id_product`';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
 
        foreach ($result as &$line) {
            if (Combination::isFeatureActive() && isset($line['id_product_attribute']) && $line['id_product_attribute']) {
                $line['cache_default_attribute'] = $line['id_product_attribute'] = $line['id_product_attribute'];
                $sql = 'SELECT agl.`name` AS group_name, al.`name` AS attribute_name,  pai.`id_image` AS id_product_attribute_image
				FROM `' . _DB_PREFIX_ . 'product_attribute` pa
				' . Shop::addSqlAssociation('product_attribute', 'pa') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = ' . $line['id_product_attribute'] . '
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)Context::getContext()->language->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)Context::getContext()->language->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON (' . $line['id_product_attribute'] . ' = pai.`id_product_attribute`)
				WHERE pa.`id_product` = ' . (int)$line['id_product'] . ' AND pa.`id_product_attribute` = ' . $line['id_product_attribute'] . '
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`';
                $attr_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                if (isset($attr_name[0]['id_product_attribute_image']) && $attr_name[0]['id_product_attribute_image']) {
                    $line['id_image'] = $attr_name[0]['id_product_attribute_image'];
                }
            }
            $line = Product::getTaxesInformations($line);
        }
        if (!$full) {
            return $result;
        }
        $array_result = array();
        foreach ($result as $prow) {
            if (!Pack::isPack($prow['id_product'])) {
                $prow['id_product_attribute'] = (int)$prow['id_product_attribute'];
                $prow['cover'] = 'aaaa';
                $array_result[] = Product::getProductProperties($id_lang, $prow);
            }
        }
        return $array_result;
    }

    public static function getWishlistProductsByIdProducts($productsIds, $id_lang, $full = false)
    {
        $context = Context::getContext();
        $sql = 'SELECT p.*, product_shop.*, pl.*, image_shop.`id_image` id_image, il.`legend`, cl.`name` AS category_default, product_shop.`id_category_default`
				FROM `' . _DB_PREFIX_ . 'product` p 
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
					ON p.id_product = pl.id_product
					AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
					ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int)$context->shop->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$id_lang . ')
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
					ON product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . '
				WHERE product_shop.`id_shop` = ' . (int)$context->shop->id . '
				AND p.`id_product` in (' .implode(',',$productsIds).  ')
				GROUP BY p.`id_product`';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($result as &$line) {
            if (Combination::isFeatureActive() && isset($line['id_product_attribute']) && $line['id_product_attribute']) {
                $line['cache_default_attribute'] = $line['id_product_attribute'] = $line['id_product_attribute'];
                $sql = 'SELECT agl.`name` AS group_name, al.`name` AS attribute_name,  pai.`id_image` AS id_product_attribute_image
				FROM `' . _DB_PREFIX_ . 'product_attribute` pa
				' . Shop::addSqlAssociation('product_attribute', 'pa') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = ' . $line['id_product_attribute'] . '
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)Context::getContext()->language->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)Context::getContext()->language->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON (' . $line['id_product_attribute'] . ' = pai.`id_product_attribute`)
				WHERE pa.`id_product` = ' . (int)$line['id_product'] . ' AND pa.`id_product_attribute` = ' . $line['id_product_attribute'] . '
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`';
                $attr_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                if (isset($attr_name[0]['id_product_attribute_image']) && $attr_name[0]['id_product_attribute_image']) {
                    $line['id_image'] = $attr_name[0]['id_product_attribute_image'];
                }
            }
            $line = Product::getTaxesInformations($line);
        }
        if (!$full) {
            return $result;
        }
        $array_result = array();
        foreach ($result as $prow) {
            if (!Pack::isPack($prow['id_product'])) {
                $prow['id_product_attribute'] = (int)$prow['id_product_attribute'];
                $prow['cover'] = 'aaaa';
                $array_result[] = Product::getProductProperties($id_lang, $prow);
            }
        }
        return $array_result;
    }
    public static function cleanShortListByIds($idProduct, $idCustomer)
    {
        $where = '`id_product` = ' . (int) $idProduct . ' AND `id_customer` = '. (int) $idCustomer;
        return Db::getInstance()->delete('egwishlist_product', $where);
    }

    public static function cleanAllShortListByIds($idProducts, $idCustomer)
    {

        $where = '`id_product` IN (' .  $idProducts . ') AND `id_customer` = '. (int) $idCustomer;
        return Db::getInstance()->delete('egwishlist_product', $where);
    }

    /**
     * @param array $productIds
     * @return array list Product
     * @throws
     */
    public static function getOrderProducts(array $productIds = array())
    {
        $context = Context::getContext();
        $order_products = array();

        $q_orders = 'SELECT o.id_order
        FROM ' . _DB_PREFIX_ . 'orders o
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON (od.id_order = o.id_order)
        WHERE o.valid = 1
        AND od.product_id IN (' . implode(',', $productIds) . ')';
        $orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($q_orders);
        if (0 < count($orders)) {
            $list = '';
            foreach ($orders as $order) {
                $list .= (int)$order['id_order'] . ',';
            }
            $list = rtrim($list, ',');
            $list_product_ids = join(',', $productIds);
            if (Group::isFeatureActive()) {
                $sql_groups_join = '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = product_shop.id_category_default AND cp.id_product = product_shop.id_product)
                LEFT JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cp.`id_category` = cg.`id_category`)';
                $groups = FrontController::getCurrentCustomerGroups();
                $sql_groups_where = 'AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',',
                            $groups) . ')' : '=' . (int)Group::getCurrent()->id);
            }
            $order_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT od.product_id
                FROM ' . _DB_PREFIX_ . 'order_detail od
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON (p.id_product = od.product_id)
                ' . Shop::addSqlAssociation('product', 'p') .
                (Combination::isFeatureActive() ? 'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
                ' . Shop::addSqlAssociation(
                        'product_attribute',
                        'pa',
                        false,
                        'product_attribute_shop.`default_on` = 1'
                    ) . '
                ' . Product::sqlStock(
                        'p',
                        'product_attribute_shop',
                        false,
                        $context->shop
                    ) : Product::sqlStock(
                    'p',
                    'product',
                    false,
                    $context->shop
                )) . '
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = od.product_id' .
                Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = product_shop.id_category_default'
                . Shop::addSqlRestrictionOnLang('cl') . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'image i ON (i.id_product = od.product_id)
                ' . (Group::isFeatureActive() ? $sql_groups_join : '') . '
                WHERE od.id_order IN (' . $list . ')
                AND pl.id_lang = ' . (int)$context->language->id . '
                AND cl.id_lang = ' . (int)$context->language->id . '
                AND od.product_id NOT IN (' . $list_product_ids . ')
                AND i.cover = 1
                AND product_shop.active = 1
                ' . (Group::isFeatureActive() ? $sql_groups_where : '') . '
                ORDER BY RAND()
                LIMIT ' . (int)Configuration::get('CROSSSELLING_NBR')
            );
        }

        return $order_products;
    }

    public static function deleteGDPRCustomer($customer)
    {
        $sql = 'DELETE FROM '._DB_PREFIX_.'egwishlist_product WHERE `id_customer` = '.(int) pSQL($customer);
        if (Db::getInstance()->execute($sql)) {
            return json_encode(true);
        }
    }

    public static function getItemsProduct($productsIds)
    {
        $sql = 'SELECT p.`id_product` as "Id", p.`reference`, pl.`name`
		        FROM `'._DB_PREFIX_.'product` p
		        '.Shop::addSqlAssociation('product', 'p').'
		        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product'.Shop::addSqlRestrictionOnLang('pl').')
		        WHERE p.id_product IN ('.$productsIds.')';

        return Db::getInstance()->executeS($sql);
    }

    public static function getIdProduct($idProducts)
    {
        $sql = 'SELECT id_product FROM '._DB_PREFIX_.'egwishlist_product WHERE id_customer = '.(int) pSQL($idProducts);
        return Db::getInstance()->executeS($sql);
    }
    public static function getIdLastProduct()
    {
        $sql = 'SELECT id_egwishlist_product FROM '._DB_PREFIX_.'egwishlist_product ORDER BY id_egwishlist_product DESC';
        return Db::getInstance()->getValue($sql);
    }

    public static function assignAttributesGroups($idProduct, $product_for_template = null)
    {
        $colors = array();
        $groups = array();
        $combinations= array();
        $context = Context::getContext();
        $product = new Product((int) $idProduct);

        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $product->getAttributesGroups((int) $context->language->id );
        if (is_array($attributes_groups) && $attributes_groups) {
            $combination_images = $product->getCombinationImages((int) $context->language->id );
            $combination_prices_set = array();
            foreach ($attributes_groups as $k => $row) {
                // Color management
                if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_ . $row['id_attribute'] . '.jpg'))) {
                    $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                    $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                    if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                        $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                    }
                    $colors[$row['id_attribute']]['attributes_quantity'] += (int) $row['quantity'];
                }
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = array(
                    'name' => $row['attribute_name'],
                    'html_color_code' => $row['attribute_color'],
                    'texture' => (@filemtime(_PS_COL_IMG_DIR_ . $row['id_attribute'] . '.jpg')) ? _THEME_COL_DIR_ . $row['id_attribute'] . '.jpg' : '',
                    'selected' => (isset($product_for_template['attributes'][$row['id_attribute_group']]['id_attribute']) && $product_for_template['attributes'][$row['id_attribute_group']]['id_attribute'] == $row['id_attribute']) ? true : false,
                );

                //$product.attributes.$id_attribute_group.id_attribute eq $id_attribute
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int) $row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int) $row['quantity'];

                $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_product_attribute']]['attributes'][] = (int) $row['id_attribute'];
                $combinations[$row['id_product_attribute']]['price'] = (float) $row['price'];

                // Call getPriceStatic in order to set $combination_specific_price
                if (!isset($combination_prices_set[(int) $row['id_product_attribute']])) {
                    $combination_specific_price = null;
                    Product::getPriceStatic((int) $product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                    $combination_prices_set[(int) $row['id_product_attribute']] = true;
                    $combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
                }
                $combinations[$row['id_product_attribute']]['ecotax'] = (float) $row['ecotax'];
                $combinations[$row['id_product_attribute']]['weight'] = (float) $row['weight'];
                $combinations[$row['id_product_attribute']]['quantity'] = (int) $row['quantity'];
                $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
                $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
                if ($row['available_date'] != '0000-00-00' && Validate::isDate($row['available_date'])) {
                    $combinations[$row['id_product_attribute']]['available_date'] = $row['available_date'];
                    $combinations[$row['id_product_attribute']]['date_formatted'] = Tools::displayDate($row['available_date']);
                } else {
                    $combinations[$row['id_product_attribute']]['available_date'] = $combinations[$row['id_product_attribute']]['date_formatted'] = '';
                }

                if (!isset($combination_images[$row['id_product_attribute']][0]['id_image'])) {
                    $combinations[$row['id_product_attribute']]['id_image'] = -1;
                }
            }

            // wash attributes list depending on available attributes depending on selected preceding attributes
            $current_selected_attributes = array();
            $count = 0;
            foreach ($groups as &$group) {
                ++$count;
                if ($count > 1) {
                    //find attributes of current group, having a possible combination with current selected
                    $id_product_attributes = array(0);
                    $query = 'SELECT pac.`id_product_attribute`
                        FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                        INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
                        WHERE id_product = ' . $product->id . ' AND id_attribute IN (' . implode(',', array_map('intval', $current_selected_attributes)) . ')
                        GROUP BY id_product_attribute
                        HAVING COUNT(id_product) = ' . count($current_selected_attributes);
                    if ($results = Db::getInstance()->executeS($query)) {
                        foreach ($results as $row) {
                            $id_product_attributes[] = $row['id_product_attribute'];
                        }
                    }
                    $id_attributes = Db::getInstance()->executeS('SELECT `id_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac2
                        WHERE `id_product_attribute` IN (' . implode(',', array_map('intval', $id_product_attributes)) . ')
                        AND id_attribute NOT IN (' . implode(',', array_map('intval', $current_selected_attributes)) . ')');
                    foreach ($id_attributes as $k => $row) {
                        $id_attributes[$k] = (int) $row['id_attribute'];
                    }
                    foreach ($group['attributes'] as $key => $attribute) {
                        if (!in_array((int) $key, $id_attributes)) {
                            unset($group['attributes'][$key]);
                            unset($group['attributes_quantity'][$key]);
                        }
                    }
                }
                //find selected attribute or first of group
                $index = 0;
                $current_selected_attribute = 0;
                foreach ($group['attributes'] as $key => $attribute) {
                    if ($index === 0) {
                        $current_selected_attribute = $key;
                    }
                    if ($attribute['selected']) {
                        $current_selected_attribute = $key;
                        break;
                    }
                    $index++;
                }
                if ($current_selected_attribute > 0) {
                    $current_selected_attributes[] = $current_selected_attribute;
                }
            }

            // wash attributes list (if some attributes are unavailables and if allowed to wash it)
            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity <= 0) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }

                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] <= 0) {
                        unset($colors[$key]);
                    }
                }
            }
            foreach ($combinations as $id_product_attribute => $comb) {
                $attribute_list = '';
                foreach ($comb['attributes'] as $id_attribute) {
                    $attribute_list .= '\'' . (int) $id_attribute . '\',';
                }
                $attribute_list = rtrim($attribute_list, ',');
                $combinations[$id_product_attribute]['list'] = $attribute_list;
            }

            $attributeVariants = [
                'groups' => $groups,
                'colors' => (count($colors)) ? $colors : false,
                'combinations' => $combinations,
                'combinationImages' => $combination_images,
            ];

        } else {
            $attributeVariants = [
                'groups' => array(),
                'colors' => false,
                'combinations' => array(),
                'combinationImages' => array(),
            ];
        }
        return $attributeVariants;
    }

    public static function isNotificationActive(){
        return (int)Configuration::get('EG_WISHLIST_ACTIVE_NOTIFICATION');
    }

    public static function isGuestModeActive(){
        return (int)Configuration::get('EG_WISHLIST_ACTIVE_DISCONNECTED');
    }
}
