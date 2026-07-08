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

require_once(dirname(__FILE__) . '/../../../../classes/opartStatTools.php');

function getGoogleAdsConversionsTrendFromGads($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('googleAdsConversionsTrendFromGads', $vars, 'float2', false);
}

function getGoogleAdsConversionsTrendFromGadsValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => ''
    ];
    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                conversions as total,
                createdAt 
            FROM 
                " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
            WHERE 
                createdAt >= '".pSQL($dateFrom)."' 
            AND 
                createdAt <= '".pSQL($dateTo)."'
            ";

    $conversions = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //unset($conversions['totalGlobal']);

    //$r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom,$dateTo,$response['datas'],'createdAt','total');
    $r = OpartStatTools::populatePeriodArray($dateFrom, $dateTo, $conversions, 'createdAt', 'total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => 'float2',
    ];

    return $result;
}
