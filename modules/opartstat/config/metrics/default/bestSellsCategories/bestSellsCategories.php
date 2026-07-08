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

function getBestSellsCategories($vars)
{
    return OpartStatTools::getBestMetricResult('bestSellsCategories',$vars);
}

function getbestSellsCategoriesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $idLang = Context::getContext()->language->id;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                order_detail.`product_id`, 
                order_detail.`product_name`, 
                order_detail.`product_quantity` - order_detail.`product_quantity_refunded` as qty,                
                IFNULL(product.`id_category_default`, 0) as id_category_default,
                IFNULL(category_lang.`name`, 'unknown') as category_name
            FROM 
                `" . _DB_PREFIX_ . "orders` orders
            INNER JOIN                 
                `" . _DB_PREFIX_ . "order_detail` order_detail
            ON
                orders.id_order = order_detail.id_order
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product` product 
            ON 
                order_detail.product_id = product.id_product  
            LEFT JOIN 
                `" . _DB_PREFIX_ . "category_lang` category_lang 
            ON 
                category_lang.id_category = product.id_category_default
            AND 
                category_lang.id_lang=".(int)$idLang." 
            AND 
                category_lang.id_shop = orders.id_shop
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
            GROUP BY 
                order_detail.id_order_detail
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0)
        return $result;    

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['product_id']])) {
            (float)$mergedProductList[$p['product_id']]['total'] += (int)$p['qty'];
        } else {
            $mergedProductList[$p['product_id']] = [
                'total' => (int)$p['qty'],
                'name' => $p['product_name'],
                'product_id' => $p['product_id'],
                'category_name' => $p['category_name'],
                'id_category_default' => $p['id_category_default']
            ];
        }
    }
    
    $token = Tools::getAdminTokenLite('AdminCategories');

    $mergedCategoryList = [];
    foreach($mergedProductList as $p) {
        $catId = $p['id_category_default'];
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedCategoryList[$catId])) {
            $mergedCategoryList[$catId]['total'] += (int)$total;
        }
        else {
            $categoryLink = 'index.php?controller=AdminCategories&id_category='.(int)$catId.'&updatecategory&token='.$token;
            $mergedCategoryList[$catId] = [
                'id' => $catId,
                'name' => '('.$p['id_category_default'].') '.$p['category_name'],
                'link' => $categoryLink,
                'total' => $total,
            ];
        }
    }

    $result['value'] = $mergedCategoryList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
