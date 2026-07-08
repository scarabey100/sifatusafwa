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

function getBestProductsCategoriesSells($vars)
{
    return OpartStatTools::getBestMetricResult('bestProductsCategoriesSells',$vars);
}

function getBestProductsCategoriesSellsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail','product_lang']);
    
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                order_detail.`product_id`, 
                product_lang.`name` AS product_name,
                SUM(order_detail.`product_quantity` - order_detail.`product_quantity_refunded`) as qty,
                product.id_category_default as id_category,
                category_lang.name as category_name
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail         
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders 
            ON
                orders.id_order = order_detail.id_order              
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product_lang` product_lang 
            ON
                order_detail.`product_id` = product_lang.`id_product`
            AND 
                product_lang.id_shop = orders.id_shop
            AND
                product_lang.id_lang = ".(int)$idLang."
            LEFT JOIN
            `" . _DB_PREFIX_ . "product` product
            ON
                order_detail.`product_id` =  product.`id_product`
            LEFT JOIN 
                `" . _DB_PREFIX_ . "category_lang` category_lang 
            ON
                category_lang.id_category = product.id_category_default
            AND 
                category_lang.id_shop = orders.id_shop
            AND
                category_lang.id_lang = ".(int)$idLang."
            ".$sqlJoins."
            WHERE
                ".$shopConstraints."            
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "' 
            AND 
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            ".$sqlFilters."    
            GROUP BY
                order_detail.`product_id`
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $prodToken = Tools::getAdminTokenLite('AdminProducts');
    $catToken = Tools::getAdminTokenLite('AdminCategories');

    if (count($productList) == 0) 
        return $result;    

    $mergedProductList = [];
    foreach ($productList as $p) {
        $idProd = $p['product_id'];
        $idCat = $p['id_category'];
        if (isset($mergedProductList[$idCat.'_'.$idProd])) {
            (int)$mergedProductList[$idCat.'_'.$idProd]['total'] += (int)$p['qty'];
        } else {
            $catName = ($p['category_name'] == null)?'Unknow':$p['category_name'];
            $prodName = ($p['product_name'] == null)?'Unknow':$p['product_name'];
            $productLink = 'index.php?controller=AdminProducts&id_product='.(int)$idProd.'&updateproduct&token='.$prodToken;
            $categoryLink = 'index.php?controller=AdminCategories&id_category='.(int)$idCat.'&updatecategory&token='.$catToken;
            $mergedProductList[$idCat.'_'.$idProd] = [
                'id' => $idCat.'_'.$idProd,
                'idCat'  => $idCat,
                'prodName' => $prodName,
                'prodLink' => $productLink,
                'catName' => $catName,
                'catLink' => $categoryLink,
                'total' => (int)$p['qty'],
            ];
        }
    }

    //var_dump($mergedProductList);
    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
