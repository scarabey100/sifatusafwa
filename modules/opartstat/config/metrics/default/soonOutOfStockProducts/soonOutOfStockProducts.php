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

function getSoonOutOfStockProducts($vars)
{
    return OpartStatTools::getBestMetricResult('SoonOutOfStockProducts', $vars);
}

function getSoonOutOfStockProductsValues($dateFrom, $dateTo, $filtersArray, $start, $limit)
{
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    //$shopConstraints2 = OpartStatTools::getShopConstraints('stock_available');
    $result['conf']['total'] = '';

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail', 'product_lang', 'product']);

    $sql = "SELECT SQL_NO_CACHE 
                CONCAT(order_detail.`product_id`,'_',order_detail.`product_attribute_id`) as concat_product_id, 
                order_detail.product_reference as reference,
                order_detail.product_name AS product_name,
                manufacturer.name as manufacturerName,
                stock_available.quantity as stock,            
                order_detail.`product_id`, 
                SUM(order_detail.`product_quantity` - order_detail.`product_quantity_refunded`) as qty
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail 
            LEFT JOIN
                `" . _DB_PREFIX_ . "product` product
            ON
                product.id_product = order_detail.product_id
            LEFT JOIN
                `" . _DB_PREFIX_ . "manufacturer` manufacturer
            ON
                manufacturer.id_manufacturer = product.id_manufacturer
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders 
            ON
                orders.id_order = order_detail.id_order            
            AND
                " . $shopConstraints . "
            AND
                orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
            INNER JOIN 
                `" . _DB_PREFIX_ . "shop` shop 
            ON
                shop.id_shop = orders.id_shop     
            INNER JOIN 
                `" . _DB_PREFIX_ . "stock_available` stock_available 
            ON
                stock_available.id_product = order_detail.product_id 
            AND 
                stock_available.id_product_attribute = order_detail.product_attribute_id 
            AND (
                    stock_available.`id_shop` = order_detail.id_shop 
                OR 
                    stock_available.`id_shop_group` = shop.id_shop_group
                )          
            " . $sqlJoins . "
            WHERE
                " . $orderStateCondition . "
                " . $excludeFreeOrder . " 
                " . $sqlFilters . " 
            GROUP BY
                    concat_product_id, order_detail.product_reference, order_detail.product_name, manufacturer.name, stock_available.quantity, order_detail.`product_id`
            HAVING
                SUM(order_detail.`product_quantity` - order_detail.`product_quantity_refunded`) > stock_available.quantity
            ORDER BY 
                manufacturer.name ASC, 
                order_detail.product_reference ASC
            ";

    $productList = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql);

    if (count($productList) == 0) {
        $result['value'] = [];
        $result['conf']['allDataLoaded'] = true;
        return $result;
    }

    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['concat_product_id']])) {
            (int)$mergedProductList[$p['concat_product_id']]['total'] += (int)$p['qty'];
             (int)$mergedProductList[$p['concat_product_id']]['stock'] += (int)$p['stock'];
        } else {
            $productName = ($p['product_name'] == null) ? 'Unknow' : $p['product_name'];
            $productLink = 'index.php?controller=AdminProducts&id_product=' . (int)$p['product_id'] . '&updateproduct&token=' . $token;
            $mergedProductList[$p['concat_product_id']] = [
                'id' => $p['concat_product_id'],
                'idProd' => $p['concat_product_id'],
                'ref' => $p['reference'],
                'brand' => $p['manufacturerName'],
                'prodName' => $productName,
                'link' => $productLink,
                'total' => (int)$p['qty'],
                'stock' => (int)$p['stock'],
            ];
        }
    }

    $result['value'] = $mergedProductList;
    //$result['conf']['allDataLoaded'] = false;
    $result['conf']['allDataLoaded'] = true;

    return $result;
}
