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

function getStockValue($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('StockValue', $vars, '', true);
}

function getStockValueValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints('product_shop');
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, []);
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

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

    $sql = "SELECT
            stock_available.id_product as product_id,
            stock_available.id_product_attribute,
            CONCAT(stock_available.id_product,'_',stock_available.id_product_attribute) as concat_product_id, 
            product.reference as reference,
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
            `" . _DB_PREFIX_ . "product_supplier` product_supplier
            ON 
                product_supplier.id_product = stock_available.id_product 
            AND 
                product_supplier.id_product_attribute = stock_available.id_product_attribute
            LEFT JOIN `" . _DB_PREFIX_ . "product_attribute` product_attribute
            ON stock_available.id_product_attribute = product_attribute.id_product_attribute

            LEFT JOIN `" . _DB_PREFIX_ . "product_attribute_shop` product_attribute_shop
            ON product_attribute_shop.id_product_attribute = stock_available.id_product_attribute
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

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['concat_product_id']])) {
            (int)$mergedProductList[$p['concat_product_id']]['stock'] += (int)$p['stock'];
            $mergedProductList[$p['concat_product_id']]['total'] = $mergedProductList[$p['concat_product_id']]['price'] * $mergedProductList[$p['concat_product_id']]['stock'];
        } else {
            $mergedProductList[$p['concat_product_id']] = [
                'stock' => (int)$p['stock'],
                'price' => $p['price'],
                'total' => $p['price'] * $p['stock']
            ];
        }
    }

    $total = 0;

    foreach ($mergedProductList as $p) {
        $total += $p['total'];
    }

    $result['value'] = $total;
    $result['conf'] = [
        'total' => 'price'
    ];
    return $result;
}
