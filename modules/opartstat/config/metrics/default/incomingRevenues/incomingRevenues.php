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

function getIncomingRevenues($vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_INCOMING_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";    

    if ($orderStateCondition == false)
        return 0;

    $fields = OpartStatTools::getTotalRevenueFields();

    $sql = "SELECT
        SUM(revenues) AS 'totalRevenues'
        FROM
            (
            SELECT
                orders.id_order,
                (".$fields.") AS 'revenues'
                FROM
                    " . _DB_PREFIX_ . "orders orders
                LEFT JOIN 
                    " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                ON
                    orders.id_order = order_cart_rule.id_order
                AND
                    order_cart_rule.free_shipping = 1
                LEFT JOIN 
                    " . _DB_PREFIX_ . "order_detail order_detail 
                ON
                    orders.id_order = order_detail.id_order
                WHERE 
                    " . $orderStateCondition."
                    ".$excludeFreeOrder."
                AND
                    ".$shopConstraints."
                GROUP BY
                    orders.id_order
            ) AS t
    ";

    $result['initial'] = OpartStatTools::getSingleNumberJsonResult($sql,0,false,'price');
    return $result;
}