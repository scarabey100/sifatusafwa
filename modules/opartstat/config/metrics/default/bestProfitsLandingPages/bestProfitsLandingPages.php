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

function getBestProfitsLandingPages($vars)
{
    return OpartStatTools::getBestMetricResult('bestProfitsLandingPages',$vars);
}

function getBestProfitsLandingPagesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $result['conf']['thLabels'] = [
        'thLabel1' => 'sources', 
        'thLabel2' => 'profits' 
    ];

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');    
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";

    
    $fields = opartStatTools::getFields($filtersArray,'profits');
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $sessionsTable = OpartStatTools::getSessionsTableName();
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

        /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
            $datas = [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'start' => $start,
                'limit' => $limit
            ];
    
            $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/sessions.php", $useCache, $datas);
    
            if ($response['datas'] == null)
                return $result;
    
            $sessions = $response['datas'];
        } else { */
            $lastStatDate = opartSession::getLastStatDate();
            if ($lastStatDate == false) 
                return $result;        
    
            if ($dateFrom < $lastStatDate)
                $dateFrom = $lastStatDate;
    
            $lastStatDate = opartSession::getLastStatDate();
            if ($lastStatDate == false)
                return $result;
    
            if ($dateFrom < $lastStatDate)
                $dateFrom = $lastStatDate;
    
            $sql1 = "SELECT
                userIp,
                createdAt,
                idCart,
                pageUrl
            FROM
                 `" . pSQL($sessionsTable) . "`
            WHERE 
                `createdAt` >= '" . pSQL($dateFrom) . "'
            AND 
                `createdAt` <= '" . pSQL($dateTo) . "'";
    
            $sessions = OpartStatTools::executeSessionsSelect($sql1);
        /* } */
    
        if (count($sessions) == 0)
            return $result;
    
        $tempArray = [];
        foreach ($sessions as $session) {
            $day = date('Y-m-d', strtotime($session['createdAt']));
            $key = $session['userIp'] . '_' . $day;
            if (!isset($tempArray[$key])) {
                $tempArray[$key] = [
                    'pageUrl' => $session['pageUrl'],
                    'idCart' => $session['idCart']
                ];
            } else {
                if ($session['idCart'] !== null) {
                    $tempArray[$key]['idCart'] = $session['idCart'];
                }
            }
        }
        $referrersArray = [];
        $in = "";
        foreach ($tempArray as $r) {
            if ($r['idCart'] != null) {
                $referrersArray[$r['idCart']] = $r['pageUrl'];
                $in .= ($in == "") ? "" : ",";
                $in .= (int)$r['idCart'];
            }
        }
        if ($in != "")
            $in = "orders.id_cart IN (" . $in . ") AND";
        else 
            return $result;

        $sql2 = "SELECT
                        orders.id_cart,
                        ".$fields." as total
                    FROM
                        " . _DB_PREFIX_ . "orders orders
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_detail order_detail 
                    ON
                        orders.id_order = order_detail.id_order
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                    ON
                        orders.id_order = order_cart_rule.id_order
                    AND
                        order_cart_rule.free_shipping = 1
                    ".$excludeFreeOrder."
                    WHERE
                        " . $in . "
                        " . $shopConstraints . "
                    AND
                        " . $orderStateCondition . "
                        ".$excludeFreeOrder."
                        ".$sqlFilters."  
                        ".$groupBy."
                    LIMIT 
                        " . (int)$start . ", " . (int)$limit;
    
        $orders = OpartStatTools::getValueFromCacheIfExists($sql2, $dateTo, $useCache);
    
        if (count($orders) == 0)
            return $result;
    
        foreach ($orders as $order) {
            $results[$order['id_cart']] = array(
                'total' => $order['total'],
                'pageUrl' => $referrersArray[$order['id_cart']]
            );
        }
    
        if (count($results) == 0)
            return $result;    

    $mergedPageList = [];
    foreach ($results as $v) {
        $page = opartStatTools::cleanUrl($v['pageUrl']);
        $page = ($page == '') ? 'unknow' : $page;

        if (!empty($mergedPageList[$page]))
            $mergedPageList[$page] = $mergedPageList[$page] + $v['total'];
        else
            $mergedPageList[$page] = $v['total'];
    }

    $pageList = [];
    foreach($mergedPageList as $name => $total) {
        $pageList[$name] = [
            'id' => $name,
            'name' => $name,
            'total' => $total
        ];
    }

    $result['value'] = $pageList;    
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
