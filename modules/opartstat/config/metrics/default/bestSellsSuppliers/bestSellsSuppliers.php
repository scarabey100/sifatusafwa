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

function getBestSellsSuppliers($vars)
{
    return OpartStatTools::getBestMetricResult('bestSellsSuppliers',$vars);
}

function getbestSellsSuppliersValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
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
                supplier.name as supplier_name,
                 product.`id_supplier`
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product` product
            ON 
                order_detail.product_id = product.id_product
            LEFT JOIN 
                `" . _DB_PREFIX_ . "supplier` supplier 
            ON 
                supplier.id_supplier = product.id_supplier                        
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
                'supplier_name' => $p['supplier_name'],
                'id_supplier' => $p['id_supplier']
            ];
        }
    }
    
    $token = Tools::getAdminTokenLite('AdminSuppliers');

    $mergedList = [];
    foreach($mergedProductList as $p) {
        $manId = ($p['supplier_name'] == null)?0:$p['id_supplier'];
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedList[$manId])) {
            $mergedList[$manId]['total'] += (int)$total;
        }
        else {
            $supplierName = ($p['supplier_name']==null)?"Unknow":$p['supplier_name'];            
            $supplierLink = 'index.php?controller=AdminSuppliers&id_supplier='.(int)$p['id_supplier'].'&updatesupplier&token='.$token;
            $mergedList[$manId] = [
                'total' => $total,
                'name' => '('.$p['id_supplier'].') '.$supplierName,
                'link' => $supplierLink,
                'id' => $manId
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
