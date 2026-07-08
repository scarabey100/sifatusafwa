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

function getMarkupRateTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('MarkupRateTrend', $vars, 'percent');
}

function getMarkupRateTrendValues($dateFrom, $dateTo, $filtersArray)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    //$getTotalRefundedValue = (_PS_VERSION_ < "1.7.7.0") ? '(order_detail.product_quantity_refunded*order_detail.unit_price_tax_excl)' : 'order_detail.total_refunded_tax_excl';
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    //$sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);   

    //$profitsFields = opartStatTools::getFields($filtersArray, 'profits', 1);
    $profitsFields = opartStatTools::getProfitFieldsForOrderDetailLine(1);

    $profitSqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail'], [], true);

    //$profitsGroupBy = opartStatTools::getGroupBy($filtersArray, true);
    $profitsGroupBy = opartStatTools::getGroupBy($filtersArray, true, true);

    //$revenuesFields = opartStatTools::getFields($filtersArray, 'revenue',1);
    $revenuesFields = opartStatTools::getRevenueFieldsForOrderDetailLine(false, 1);
    
    $revenuesSqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    
    //$RevenuesGroupBy = opartStatTools::getGroupBy($filtersArray);
    $RevenuesGroupBy = opartStatTools::getGroupBy($filtersArray, false, true);

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql1 = "
    SELECT SQL_NO_CACHE 
                    orders.id_order, 
                    (" . $profitsFields . ") as 'orderMargin',
                    orders.`" . bqSQL($dateColumn) . "` as date_add
                FROM 
                    " . _DB_PREFIX_ . "orders orders                     
                INNER JOIN 
                    " . _DB_PREFIX_ . "order_detail order_detail 
                ON 
                    orders.id_order = order_detail.id_order                     
                LEFT JOIN 
                    " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                ON
                    orders.id_order = order_cart_rule.id_order
                AND
                    order_cart_rule.free_shipping = 1
                " . $profitSqlJoins . "
                WHERE 
                    " . $shopConstraints . "
                AND
                    (
                            order_detail.purchase_supplier_price IS NOT NULL 
                        OR 
                            order_detail.original_wholesale_price IS NOT NULL
                    )
                AND
                   orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                AND 
                   orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
                AND 
                    " . $orderStateCondition . "
                    " . $excludeFreeOrder . "
                " . $sqlFilters . "
                " . $profitsGroupBy;

    $margins = OpartStatTools::getValueFromCacheIfExists($sql1, $dateTo, $useCache);

    $totalMargin = 0;
    $marginsPerIdOrder = [];
    foreach ($margins as $margin) {
        $id = $margin['id_order'];
        $marginsPerIdOrder[$id] = $margin;

        $totalMargin += $margin['orderMargin'];
    }

    $sql2 = "SELECT SQL_NO_CACHE 
                orders.id_order, 
                (" . $revenuesFields . ") as 'revenues'
            FROM 
                " . _DB_PREFIX_ . "orders orders                     
            INNER JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON 
                orders.id_order = order_detail.id_order                     
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            " . $revenuesSqlJoins . "
            WHERE 
                " . $shopConstraints . "
            AND
                (
                        order_detail.purchase_supplier_price IS NOT NULL 
                    OR 
                        order_detail.original_wholesale_price IS NOT NULL
                )
            AND 
                " . $orderStateCondition . "
                " . $excludeFreeOrder . "
            AND
                orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
            " . $sqlFilters . "
            " . $RevenuesGroupBy;

    $revenues = OpartStatTools::getValueFromCacheIfExists($sql2, $dateTo, $useCache);

    $totalRevenue = 0;
    $revenuesPerIdOrder = [];
    foreach ($revenues as $revenue) {
        $id = $revenue['id_order'];
        $revenuesPerIdOrder[$id] = $revenue;

        $totalRevenue += $revenue['revenues'];
    }

    $datas=[];
    
    foreach($marginsPerIdOrder as $id => $marginArray) {
        if(isset($revenuesPerIdOrder[$id]) && $revenuesPerIdOrder[$id]['revenues'] > 0) {
            $datas[] = array(
                'id_order' => $id,
                'date_add' => $marginArray['date_add'],
                'total' => (string)(($marginArray['orderMargin'] / $revenuesPerIdOrder[$id]['revenues'])*100)
            );
        }        
    }

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $datas, 'date_add', 'total');

    if($totalRevenue != 0) 
        $r['totalGlobal'] = ($totalMargin / $totalRevenue)*100;
    else
        $r['totalGlobal'] = 0;

    //$r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $orders, 'date_add', 'total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => 'percent'
    ];
    return $result;
}
