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

function getDebug($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('Debug', $vars, '', false);
}

function getDebugValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $useCache = false;

    $sql = "SELECT SQL_NO_CACHE 
                orders.*,
                order_detail.*, 
                IFNULL(order_detail.purchase_supplier_price,order_detail.original_wholesale_price) as product_purchase_price,
                orders.".bqSQL($dateColumn)." as orderDate
            FROM `" . _DB_PREFIX_ . "orders` orders
            JOIN `" . _DB_PREFIX_ . "order_detail` order_detail ON orders.id_order = order_detail.id_order
            " . $sqlJoins . "
            WHERE 
                " . $shopConstraints . "
            AND 
                " . $orderStateCondition . "
                " . $excludeFreeOrder . "
            AND
                orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
            " . $sqlFilters ."
            GROUP BY
                order_detail.id_order_detail";

    $context = Context::getContext();
    $context->smarty->assign(array('sqlQuery' => $sql));
    $configValues = [
        'OPARTSTAT_INSTALLED' => Configuration::get('OPARTSTAT_INSTALLED'),
        'OPARTSTAT_EXCLUDE_SHIPPING' => Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING'),
        'OPARTSTAT_EXCLUDE_FREE_ORDER' => Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER'),
        'OPARTSTAT_USE_ORDER_CREATED_DATE' => Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE'),
        'OPARTSTAT_INACTIV_CUSTOMER_DAYS' => Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS'),
        'OPARTSTAT_MAX_VISITS' => Configuration::get('OPARTSTAT_MAX_VISITS'),
        'OPARTSTAT_LIVE_TIME' => Configuration::get('OPARTSTAT_LIVE_TIME'),
        'OPARTSTAT_PARTNERMODULES_LINKED' => Configuration::get('OPARTSTAT_PARTNERMODULES_LINKED'),
        'OPARTSTAT_USE_COMMISSIONS' => Configuration::get('OPARTSTAT_USE_COMMISSIONS'),
        'OPARTSTAT_STATUS_VALID_ORDER' => Configuration::get('OPARTSTAT_STATUS_VALID_ORDER'),
        'OPARTSTAT_USE_SAAS' => Configuration::get('OPARTSTAT_USE_SAAS'),
        'OPARTSTAT_PURGE_CACHE_DELAY' => Configuration::get('OPARTSTAT_PURGE_CACHE_DELAY'),
        'OPARTSTAT_CACHE_LAST_PURGE' => Configuration::get('OPARTSTAT_CACHE_LAST_PURGE'),
        'OPARTSTAT_CACHE_FILE_MAX_AGE' => Configuration::get('OPARTSTAT_CACHE_FILE_MAX_AGE'),
        'OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION' => Configuration::get('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION'),
        'OPARTSTAT_ACTIVE_DEBUG_MODE' => Configuration::get('OPARTSTAT_ACTIVE_DEBUG_MODE'),
    ];

    $context->smarty->assign(array('configValues' => $configValues));

    $datas = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    
    foreach ($datas as $data) {
        $ordersDetails[$data['id_order']][] = $data;        
    }

    $orderDetailHtml = "";
    if (!empty($ordersDetails)) {
        $orderTotal=[];
        foreach ($ordersDetails as $order) {
            $detailtotals=[];
            foreach($order as $key => $detail) {
                $detailtotals['total_price_tax_excl'] = !isset($detailtotals['total_price_tax_excl'])?$detail['total_price_tax_excl']:$detailtotals['total_price_tax_excl']+$detail['total_price_tax_excl'];
                $detailtotals['total_refunded_tax_excl'] = !isset($detailtotals['total_refunded_tax_excl'])?$detail['total_refunded_tax_excl']:$detailtotals['total_refunded_tax_excl']+$detail['total_refunded_tax_excl'];

                if($key == 0) {
                    $orderTotal['total_paid_tax_excl'] = !isset($orderTotal['total_paid_tax_excl'])?$detail['total_paid_tax_excl']:$orderTotal['total_paid_tax_excl']+$detail['total_paid_tax_excl'];
                    $orderTotal['total_shipping_tax_excl'] = !isset($orderTotal['total_shipping_tax_excl'])?$detail['total_shipping_tax_excl']:$orderTotal['total_shipping_tax_excl']+$detail['total_shipping_tax_excl'];
                }
                $order[$key]['total_product_purchase_cost'] = ($detail['product_quantity'] - $detail['product_quantity_refunded']) * $detail['product_purchase_price'];

                $detailtotals['total_product_purchase_cost'] = !isset($detailtotals['total_product_purchase_cost'])?$order[$key]['total_product_purchase_cost']:$detailtotals['total_product_purchase_cost']+$order[$key]['total_product_purchase_cost'];

                $orderTotal['total_refunded_tax_excl'] = !isset($orderTotal['total_refunded_tax_excl'])?$detail['total_refunded_tax_excl']:$orderTotal['total_refunded_tax_excl']+$detail['total_refunded_tax_excl'];
                $orderTotal['total_product_purchase_cost'] = !isset($orderTotal['total_product_purchase_cost'])?$order[$key]['total_product_purchase_cost']:$orderTotal['total_product_purchase_cost']+$order[$key]['total_product_purchase_cost'];

            }
            $context->smarty->assign(array('order' => $order));
            $context->smarty->assign(array('detailtotals' => $detailtotals));
            $orderDetailHtml .= $context->smarty->fetch(
                _PS_MODULE_DIR_ . 'opartstat/config/metrics/default/debug/orderDetail.tpl'
            );
        }
    } else {
        $orderDetailHtml .= 'No orders found.';
    }

    $context->smarty->assign(array('orderTotal' => $orderTotal));
    
    $orderTotalHtml = $context->smarty->fetch(
        _PS_MODULE_DIR_ . 'opartstat/config/metrics/default/debug/orderTotal.tpl'
    );
    
    $context->smarty->assign(array(
        'orders' => $orderDetailHtml,
        'orderTotal' => $orderTotalHtml,

    ));

    $html = $context->smarty->fetch(
        _PS_MODULE_DIR_ . 'opartstat/config/metrics/default/debug/debugContent.tpl'
    );
    $result['value'] = $html;
    return $result;
}

