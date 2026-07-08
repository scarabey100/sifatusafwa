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

function getDailyOrderList($vars)
{
    return OpartStatTools::getBestMetricResult('dailyOrderList',$vars);
}

function getDailyOrderListValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total_ht'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

$sql = '
SELECT 
  cal.d AS order_date,
  COALESCE(o.cnt, 0)                      AS orders_count,
  COALESCE(o.products_qty_raw, 0)         AS products_qty_raw,
  COALESCE(o.products_qty_net, 0)         AS products_qty_net,
  /* CA produits HT (avant remises) */
  COALESCE(o.ca_products_ht_gross, 0)     AS ca_products_ht_gross,
  /* CA produits HT (après remises) = total_products - total_discounts_tax_excl */
  COALESCE(o.ca_products_ht_net, 0)       AS ca_products_ht_net,
  /* CA HT total payé (incl. frais de port & emballage, remises déduites) */
  COALESCE(o.ca_order_ht_total, 0)        AS total_ht,
  /* Détails utiles si tu veux afficher à part */
  COALESCE(o.ca_shipping_ht, 0)           AS ca_shipping_ht,
  COALESCE(o.ca_wrapping_ht, 0)           AS ca_wrapping_ht
FROM (
  /* Génère chaque jour entre $dateFrom et $dateTo sans CTE */
  SELECT DATE_ADD(\'' . pSQL($dateFrom) . '\', INTERVAL seq DAY) AS d
  FROM (
    SELECT units.u + tens.t*10 AS seq
    FROM (SELECT 0 u UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS units
    CROSS JOIN
         (SELECT 0 t UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS tens
  ) AS seqs
  WHERE seq <= DATEDIFF(\'' . pSQL($dateTo) . '\', \'' . pSQL($dateFrom) . '\')
) AS cal
LEFT JOIN (
  SELECT 
    DATE(orders.`'.bqSQL($dateColumn).'`)     AS d,
    COUNT(DISTINCT orders.id_order)           AS cnt,

    /* Quantités produits */
    SUM(order_detail.product_quantity)        AS products_qty_raw,
    SUM(order_detail.product_quantity 
        - order_detail.product_quantity_refunded 
        - order_detail.product_quantity_return
        /* - order_detail.product_quantity_reinjected */) AS products_qty_net,

    /* CA produits HT (sans remises) : champ agrégé depuis ps_orders */
    SUM(orders.total_products)                               AS ca_products_ht_gross,
    /* CA produits HT après remises (remises catalogue + panier) */
    SUM(orders.total_products - orders.total_discounts_tax_excl) AS ca_products_ht_net,

    /* CA HT total payé (incl. port/emballage, remises déduites) */
    SUM(orders.total_paid_tax_excl)                           AS ca_order_ht_total,

    /* Détails HT */
    SUM(orders.total_shipping_tax_excl)                       AS ca_shipping_ht,
    SUM(orders.total_wrapping_tax_excl)                       AS ca_wrapping_ht

  FROM `' . _DB_PREFIX_ . 'orders` orders
  INNER JOIN `' . _DB_PREFIX_ . 'order_detail` order_detail
          ON order_detail.id_order = orders.id_order
  WHERE orders.`'.bqSQL($dateColumn).'` >= \'' . pSQL($dateFrom) . ' 00:00:00\'
    AND orders.`'.bqSQL($dateColumn).'` <  DATE_ADD(\'' . pSQL($dateTo) . '\', INTERVAL 1 DAY)
    ' . ($orderStateCondition ? 'AND ' . $orderStateCondition : '') . '
    ' . $excludeFreeOrder . '
    ' . $sqlFilters . '
  GROUP BY DATE(orders.`'.bqSQL($dateColumn).'`)
) AS o ON o.d = cal.d
ORDER BY cal.d
 LIMIT ' . (int)$start . ", " . (int)$limit . '';



     $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);



    if (count($orderList) == 0)
        return $result;    

    $mergedOrderList = [];
    foreach ($orderList as $o) {
        if (isset($mergedOrderList[$o['order_date']]) && $mergedOrderList[$o['order_date']]['total'] > 0) {
            $mergedOrderList[$o['order_date']]['total_ht'] = $mergedOrderList[$o['order_date']]['total_ht']+1;
        }
        else {
            $mergedOrderList[$o['order_date']] = [
                'id' => $o['order_date'],
                'date' => (new DateTime($o['order_date']))->format('Y-m-d'),
                'orders' => $o['orders_count'],
                'quantity' => $o['products_qty_net'],
                'total_ht' => $o['total_ht'],
            ];
        }
    }



    $result['value'] = $mergedOrderList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
