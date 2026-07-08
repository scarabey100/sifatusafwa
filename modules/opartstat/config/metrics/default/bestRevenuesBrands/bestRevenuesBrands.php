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

function getBestRevenuesBrands($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesBrands',$vars);
}

function getBestRevenuesBrandsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail']);
    $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine();    
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

        $sql = "SELECT SQL_NO_CACHE 
                order_detail.`product_id`, 
                 product.`id_manufacturer`,
                manufacturer.name as manufacturer_name,
                (".$fields.") as total
            FROM
                " . _DB_PREFIX_ . "orders orders            
            LEFT JOIN 
            " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            INNER JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            LEFT JOIN 
                " . _DB_PREFIX_ . "product product
            ON
                order_detail.product_id = product.id_product
            LEFT JOIN 
                `" . _DB_PREFIX_ . "manufacturer` manufacturer 
            ON 
                manufacturer.id_manufacturer = product.id_manufacturer
            ".$sqlJoins."
            WHERE
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            ".$sqlFilters."
            AND
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            ORDER BY
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0)
        return $result;    
    
    $token = Tools::getAdminTokenLite('AdminManufacturers');
    
    $mergedManufacturerList = [];
    foreach ($productList as $p) {
        $manId = ($p['manufacturer_name'] == null) ? 0 : $p['id_manufacturer'];
        $total = ($p['total'] == null) ? 0 : $p['total'];
        if (isset($mergedManufacturerList[$manId])) {
            $mergedManufacturerList[$manId]['total'] += (float)$total;
        } else {
            $manufacturerName = ($p['manufacturer_name'] == null) ? "Unknow" : $p['manufacturer_name'];
            $manufacturerLink = 'index.php?controller=AdminManufacturers&id_manufacturer='.(int)$p['id_manufacturer'].'&updatemanufacturer&token='.$token;
            $mergedManufacturerList[$manId] = [
                'total' => $total,
                'name' => '(' . $p['id_manufacturer'] . ') ' . $manufacturerName,
                'link' => $manufacturerLink,
                'id' => $manId
            ];
        }
    }

    $result['value'] = $mergedManufacturerList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
