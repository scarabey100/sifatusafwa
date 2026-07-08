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

function getBestSellsBrands($vars)
{
    return OpartStatTools::getBestMetricResult('bestSellsBrands',$vars);
}

function getbestSellsBrandsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
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
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                order_detail.`product_id`, 
                order_detail.`product_name`, 
                order_detail.`product_quantity` - order_detail.`product_quantity_refunded` as qty,
                manufacturer.name as manufacturer_name,
                product.`id_manufacturer`
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product` product
            ON 
                order_detail.product_id = product.id_product
            LEFT JOIN 
                `" . _DB_PREFIX_ . "manufacturer` manufacturer 
            ON 
                manufacturer.id_manufacturer = product.id_manufacturer                        
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders 
            ON
                orders.id_order = order_detail.id_order
            ".$sqlJoins."
            WHERE
                ".$shopConstraints."
            AND 
                " . $orderStateCondition . "
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            ".$excludeFreeOrder."
            ".$sqlFilters."
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0) 
        return $result;    

    // use this array to calculate the discount we will to apply to each product
    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['product_id']])) {
            (float)$mergedProductList[$p['product_id']]['total'] += (int)$p['qty'];
        } else {
            $mergedProductList[$p['product_id']] = [
                'total' => (int)$p['qty'],
                'name' => $p['product_name'],
                'product_id' => $p['product_id'],
                'manufacturer_name' => $p['manufacturer_name'],
                'id_manufacturer' => $p['id_manufacturer']
            ];
        }
    }    
    
    $token = Tools::getAdminTokenLite('AdminManufacturers');

    $mergedList = [];
    foreach($mergedProductList as $p) {
        $manId = ($p['manufacturer_name'] == null)?0:$p['id_manufacturer'];
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedList[$manId])) {
            $mergedList[$manId]['total'] += (int)$total;
        }
        else {
            $manufacturerName = ($p['manufacturer_name']==null)?"Unknow":$p['manufacturer_name'];
            $manufacturerLink = 'index.php?controller=AdminManufacturers&id_manufacturer='.(int)$p['id_manufacturer'].'&updatemanufacturer&token='.$token;
            $mergedList[$manId] = [
                'total' => $total,
                'name' => '('.$p['id_manufacturer'].') '.$manufacturerName,
                'link' => $manufacturerLink,
                'id' => $manId
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
