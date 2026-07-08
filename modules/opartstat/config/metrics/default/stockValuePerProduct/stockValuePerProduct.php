<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function getStockValuePerProduct($vars)
{
    return OpartStatTools::getBestMetricResult('StockValuePerProduct', $vars);
}

function getStockValuePerProductValues($dateFrom, $dateTo, $filtersArray, $start, $limit)
{
    $result['conf']['total'] = 'price';
    $result['conf']['price'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $stockInfos = OpartStatTools::getShopGroupIdSharingStockAndShopIdNotSharingStock();

    $whereClause = "(";
    $conditions = [];

    if (!empty($stockInfos['shared_stock_groups'])) {
        $sharedStockGroups = implode(',', $stockInfos['shared_stock_groups']);
        $conditions[] = "stock_available.id_shop_group IN ($sharedStockGroups)";
    }

    if (!empty($stockInfos['non_shared_stock_shops'])) {
        $nonSharedStockShops = implode(',', $stockInfos['non_shared_stock_shops']);
        $conditions[] = "stock_available.id_shop IN ($nonSharedStockShops)";
    }

    $whereClause .= implode(' OR ', $conditions);
    $whereClause .= ")";


    $shopConstraints = OpartStatTools::getShopConstraints('product_shop');
    //$shopConstraints2 = OpartStatTools::getShopConstraints('stock_available');

    //$orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    //$excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    //$dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    //$sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail', 'product_lang', 'product']);

    $idLang = Context::getContext()->language->id;
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);


$sql = "SELECT
            stock_available.id_product as product_id,
            stock_available.id_product_attribute,
            CONCAT(stock_available.id_product,'_',stock_available.id_product_attribute) as concat_product_id, 
            product.reference as reference,
            product_lang.name as product_name,
            stock_available.quantity as stock,
             CASE 
            WHEN stock_available.id_product_attribute > 0 THEN
                COALESCE(
                    MAX(CASE WHEN product_supplier.product_supplier_price_te != 0 THEN product_supplier.product_supplier_price_te END),
                    MAX(CASE WHEN product_attribute_shop.wholesale_price != 0 THEN product_attribute_shop.wholesale_price END),
                    MAX(CASE WHEN product_attribute.wholesale_price != 0 THEN product_attribute.wholesale_price END),
                    0
                )
            ELSE
                COALESCE(
                    MAX(CASE WHEN product_supplier.product_supplier_price_te != 0 THEN product_supplier.product_supplier_price_te END),
                    MAX(CASE WHEN product_shop.wholesale_price != 0 THEN product_shop.wholesale_price END),
                    MAX(CASE WHEN product.wholesale_price != 0 THEN product.wholesale_price END),
                    0
                )
        END as price,
        CASE 
            WHEN stock_available.id_shop = 0 THEN stock_available.id_shop_group
            ELSE stock_available.id_shop 
        END as shop_identifier
            FROM 
            `" . _DB_PREFIX_ . "stock_available` stock_available
            LEFT JOIN 
            `" . _DB_PREFIX_ . "product_shop` product_shop 
            ON 
                stock_available.id_product = product_shop.id_product
            AND (
                (stock_available.id_shop != 0 AND product_shop.id_shop = stock_available.id_shop)
                OR (stock_available.id_shop = 0 AND product_shop.id_shop IN (" . 
                implode(',', array_map('intval', $stockInfos['first_shop_ids'])) . "))
            )
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product` product
            ON 
                stock_available.id_product = product.id_product  
            LEFT JOIN 
            `" . _DB_PREFIX_ . "product_lang` product_lang
            ON 
                stock_available.id_product = product_lang.id_product
            AND 
                product_lang.id_lang = " . (int)$idLang . "
            LEFT JOIN 
            `" . _DB_PREFIX_ . "product_supplier` product_supplier
            ON 
                product_supplier.id_product = stock_available.id_product 
            AND 
                product_supplier.id_product_attribute = stock_available.id_product_attribute
            LEFT JOIN `" . _DB_PREFIX_ . "product_attribute` product_attribute
            ON stock_available.id_product_attribute = product_attribute.id_product_attribute

            LEFT JOIN `" . _DB_PREFIX_ . "product_attribute_shop` product_attribute_shop
            ON product_attribute_shop.id_product_attribute = stock_available.id_product_attribute
            AND (
                (stock_available.id_shop != 0 AND product_attribute_shop.id_shop = stock_available.id_shop)
                OR (stock_available.id_shop = 0 AND product_attribute_shop.id_shop IN (" . 
                implode(',', array_map('intval', $stockInfos['first_shop_ids'])) . "))
            )
            WHERE 
                " . $whereClause . "
                " . $sqlFilters . "
            AND 
                stock_available.quantity > 0     
            AND (
                stock_available.id_product_attribute > 0
                OR NOT EXISTS (
                    SELECT 1
                    FROM `" . _DB_PREFIX_ . "stock_available` sa_check
                    WHERE sa_check.id_product = stock_available.id_product
                    AND sa_check.id_product_attribute > 0
                )
            )
            GROUP BY
                stock_available.id_product, stock_available.id_product_attribute, shop_identifier
            ORDER BY
                product.reference";

    $productList = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    if (count($productList) == 0)
        return $result;


    $sql = "SELECT SQL_NO_CACHE 
                pac.id_product_attribute,
                CONCAT(' ', GROUP_CONCAT(CONCAT(agl.name, ': ', al.name) SEPARATOR ', ')) as attribute_names
            FROM `" . _DB_PREFIX_ . "product_attribute_combination` pac
            JOIN `" . _DB_PREFIX_ . "attribute` a ON pac.id_attribute = a.id_attribute
            JOIN `" . _DB_PREFIX_ . "attribute_lang` al ON a.id_attribute = al.id_attribute AND al.id_lang = " . (int)$idLang . "
            JOIN `" . _DB_PREFIX_ . "attribute_group` ag ON a.id_attribute_group = ag.id_attribute_group
            JOIN `" . _DB_PREFIX_ . "attribute_group_lang` agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = " . (int)$idLang . "
            WHERE pac.id_product_attribute != 0
            GROUP BY pac.id_product_attribute";

    $attributsList = OpartStatTools::getAssociativeArrayFromQuery($sql, $useCache);

    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['concat_product_id']])) {
            (int)$mergedProductList[$p['concat_product_id']]['stock'] += (int)$p['stock'];
            $mergedProductList[$p['concat_product_id']]['total'] = $mergedProductList[$p['concat_product_id']]['price'] * $mergedProductList[$p['concat_product_id']]['stock'];
        } else {
            $productName = ($p['product_name'] == null) ? 'Unknow' : $p['product_name'];
            if(isset($attributsList[$p['id_product_attribute']]))
                $productName .= " - ".$attributsList[$p['id_product_attribute']];

            $productLink = 'index.php?controller=AdminProducts&id_product=' . (int)$p['product_id'] . '&updateproduct&token=' . $token;
            $mergedProductList[$p['concat_product_id']] = [
                'id' => $p['concat_product_id'],
                'idProd' => $p['concat_product_id'],
                'ref' => $p['reference'],
                'prod' => $productName,
                'link' => $productLink,
                'stock' => (int)$p['stock'],
                'price' => $p['price'],
                'total' => $p['price'] * $p['stock']
            ];
        }
    }
    /* var_dump($mergedProductList);
    die(); */
    $result['value'] = $mergedProductList;

    $result['conf']['allDataLoaded'] = true;

    return $result;
}
