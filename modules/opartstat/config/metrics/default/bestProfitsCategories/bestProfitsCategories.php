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

function getBestProfitsCategories($vars)
{
    return OpartStatTools::getBestMetricResult('bestProfitsCategories',$vars);
}

function getbestProfitsCategoriesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    $fields = OpartStatTools::getProfitFieldsForOrderDetailLine();

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail','category_lang'],[],true);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                order_detail.`product_id`, 
                IFNULL(product_shop.`id_category_default`, 0) as id_category_default,
                IFNULL(category_lang.`name`, 'unknown') as category_name,
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
                " . _DB_PREFIX_ . "product_shop product_shop
            ON
                order_detail.product_id = product_shop.id_product
            LEFT JOIN 
                `" . _DB_PREFIX_ . "category_lang` category_lang 
            ON 
                category_lang.id_category = product_shop.id_category_default 
            AND 
                category_lang.id_lang=".(int)$idLang." 
            AND 
                category_lang.id_shop = orders.id_shop
            ".$sqlJoins."
            WHERE
                " . $orderStateCondition . "
                ".$excludeFreeOrder."
            AND
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'            
            ".$sqlFilters."  
            GROUP BY 
                order_detail.id_order_detail
            ORDER BY
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

     //echo $sql."<br />";
    if (count($productList) == 0)
        return $result;    
        //var_dump($mergedProductList);

    $token = Tools::getAdminTokenLite('AdminCategories');

    $mergedCategoryList = [];
    foreach($productList as $p) {
        $catId = $p['id_category_default'];
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedCategoryList[$catId])) {
            $mergedCategoryList[$catId]['total'] += (float)$total;
        }
        else {
            $categoryLink = 'index.php?controller=AdminCategories&id_category='.(int)$catId.'&updatecategory&token='.$token;
            $mergedCategoryList[$catId] = [
                'total' => $total,
                'name' => '('.$p['id_category_default'].') '.$p['category_name'],
                'link' => $categoryLink,
                'id' => $catId
            ];
        }
    }

    $result['value'] = $mergedCategoryList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
