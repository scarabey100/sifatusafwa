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

function getUnsoldProducts($vars)
{
    return OpartStatTools::getBestMetricResult('unsoldProducts',$vars);
}

function getUnsoldProductsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;
    
    $shopConstraints = OpartStatTools::getShopConstraints();
    $result['conf']['total'] = '';

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = "";
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail','product_lang']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    if (is_array($filtersArray) && count($filtersArray) > 0) {
        foreach ($filtersArray as $excludeInclude => $array) {
            foreach ($array as $filterName => $filterValue) {
                if (!array_key_exists('values', $filterValue))
                    continue;

                if ($filterName == 'products') {
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'product');
                }
                else {
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false);
                }   
            }
        }
    }

    $sql = "SELECT SQL_NO_CACHE 
            product.`id_product` as product_id, 
            product_lang.`name` AS product_name,
            0 as qty
        FROM 
            `" . _DB_PREFIX_ . "product` product                       
        LEFT JOIN 
            `" . _DB_PREFIX_ . "product_lang` product_lang 
        ON
            product.`id_product` = product_lang.`id_product`
        ".$sqlJoins."
        WHERE
            product_lang.id_lang = ".(int)$idLang."
        AND
            product.`id_product` NOT IN (
                SELECT  
                    order_detail.`product_id`
                FROM 
                    `" . _DB_PREFIX_ . "order_detail` order_detail
                INNER JOIN 
                    `" . _DB_PREFIX_ . "orders` orders 
                ON
                    orders.id_order = order_detail.id_order
                AND
                    ".$shopConstraints."                
                WHERE
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
                AND
                    " . $orderStateCondition . "
                ".$excludeFreeOrder."
                
            )            
            ".$sqlFilters." 
        LIMIT 
            " . (int)$start . ", " . (int)$limit . "
        ";


    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0) {
        $result['value'] = [];
        $result['conf']['allDataLoaded'] = true;
        return $result;
    }
    
    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['product_id']])) {
            (int)$mergedProductList[$p['product_id']]['total'] += (int)$p['qty'];
        } else {
            $productName = ($p['product_name'] == null)?'Unknow':$p['product_name'];
            $productLink = 'index.php?controller=AdminProducts&id_product='.(int)$p['product_id'].'&updateproduct&token='.$token;
            $mergedProductList[$p['product_id']] = [
                'total' => (int)$p['qty'],
                'name' => '('.$p['product_id'].') '.$productName,
                'link' => $productLink,
                'id' => $p['product_id']
            ];
        }
    }

    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
