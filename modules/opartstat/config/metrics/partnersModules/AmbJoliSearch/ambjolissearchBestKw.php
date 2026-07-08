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

function getAmbjolissearchBestKw($vars)
{
    return OpartStatTools::getBestMetricResult('AmbjolissearchBestKw',$vars);
}

function getAmbjolissearchBestKwValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars)
{
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;
    
    $useCache = true;
    

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT 
                opartstat_ambjolisearch.keyword
            FROM 
                `" . _DB_PREFIX_ . "opartstat_ambjolisearch` opartstat_ambjolisearch         
            WHERE
                opartstat_ambjolisearch.createdAt >= '" . pSQL($dateFrom) . "'
            AND 
                opartstat_ambjolisearch.createdAt <= '" . pSQL($dateTo) . "'
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $kws = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($kws) == 0) {
        $result['value'] = [];
        $result['conf']['allDataLoaded'] = true;
        return $result;
    }

    $mergedProductList = [];
    foreach ($kws as $k) {
        if (isset($mergedProductList[$k['keyword']])) {
            (int)$mergedProductList[$k['keyword']]['total'] += 1;
        } else {
            $productName = ($k['keyword'] == null)?'Unknow':$k['keyword'];
            $mergedProductList[$k['keyword']] = [
                'total' => 1,
                'name' => $productName,
                'id' => $k['keyword']
            ];
        }
    }

    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
