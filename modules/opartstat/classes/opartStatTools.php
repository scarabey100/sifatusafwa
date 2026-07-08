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

use Symfony\Component\Validator\Constraints\Locale;

include(dirname(__FILE__) . '/opartRemoteTools.php');
//include(dirname(__FILE__) . '/../../../src/Core/Localization/Locale.php');
//include(C:\wamp64\www\shopstats\src\Core\Localization\Locale.php

class OpartStatTools
{
    public static function humanToMysqlDate($humanDate, $endOfTheDay, $dateFormat, $isLiveDateFrom = false)
    {
        if ($humanDate == -1) {
            $phpDateFormat = "Y-m-d H:i:s";
            if ($isLiveDateFrom == true) {
                $minutes = Configuration::get('OPARTSTAT_LIVE_TIME');
                $mysqlDate = Date($phpDateFormat, strtotime('-' . $minutes . ' minutes'));
            } else
                $mysqlDate = Date($phpDateFormat);
        } else {
            $hour = ($endOfTheDay == true) ? ' 23:59:59' : ' 00:00:00';
            $dateArray = explode("/", $humanDate);
            $mysqlDate = ($dateFormat == "dd/mm/yy" || $dateFormat == "DD/MM/YYYY") ? $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0] : $dateArray[2] . '-' . $dateArray[0] . '-' . $dateArray[1];
            $mysqlDate .= $hour;
        }
        return $mysqlDate;
    }

    public static function mysqlToHumanDate($mysqlDate, $dateFormat)
    {
        $timestamp = strtotime($mysqlDate);
        $d = date('d', $timestamp);
        $m = date('m', $timestamp);
        $y = date('Y', $timestamp);
        $humanDate = ($dateFormat == "dd/mm/yy" || $dateFormat == "DD/MM/YYYY") ? $d . "/" . $m . "/" . $y : $m . "/" . $d . "/" . $y;
        return $humanDate;
    }

    public static function sortMultipleArray(&$arrayToSort, $key, $isInf, $keepArrayKey = false)
    {
        $fctName = ($keepArrayKey == true) ? "uasort" : "usort";

        $fctName($arrayToSort, function ($a, $b) use ($key, $isInf) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            if ($isInf == true)
                return ($a[$key] < $b[$key]) ? 1 : -1;
            else
                return ($a[$key] > $b[$key]) ? 1 : -1;
        });
        return $arrayToSort;
    }

    public static function formatPrice($price)
    {
        $context = Context::getContext();
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $isoCode = $currency->iso_code;
        return $context->getCurrentLocale()->formatPrice($price, $isoCode);
    }

    public static function createOrderStateCondition($configKey, $alias = 'orders', $columnName = 'current_state')
    {
        $orderStateCondition = '';

        $orderStateConditionContent = '';
        $orderStates = explode(',', Configuration::get($configKey));
        if (count($orderStates) == 0)
            return false;

        if (!validate::isTableOrIdentifier($alias))
            return false;

        for ($i = 0; $i < count($orderStates); ++$i) {
            if (($i + 1) < count($orderStates))
                $orderStateConditionContent .= $alias . ".`" . bqSQL($columnName) . "` = '" . pSQL($orderStates[$i]) . "' OR ";
            else
                $orderStateConditionContent .= $alias . ".`" . bqSQL($columnName) . "` = '" . pSQL($orderStates[$i]) . "'";
        }
        if (count($orderStates) > 0)
            $orderStateCondition = "(" . $orderStateConditionContent . ")";

        return $orderStateCondition;
    }

    public static function convertIsoToHumanLanguage($isoCode, $idLang)
    {
        $code = substr($isoCode, 0, 2);
        $langArray = [
            'af' => [1 => 'Afrikaans', 2 => 'Afrikaans'],
            'ar' => [1 => 'Arabe', 2 => 'Arabic'],
            'az' => [1 => 'Azerbaïdjanais', 2 => 'Azerbaijani'],
            'be' => [1 => 'Biélorusse', 2 => 'Belarusian'],
            'bg' => [1 => 'Bulgare', 2 => 'Bulgarian'],
            'bn' => [1 => 'Bengali', 2 => 'Bengali'],
            'bs' => [1 => 'Bosniaque', 2 => 'Bosnian'],
            'cs' => [1 => 'Tchèque', 2 => 'Czech'],
            'da' => [1 => 'Danois', 2 => 'Danish'],
            'de' => [1 => 'Allemand', 2 => 'German'],
            'el' => [1 => 'Grec', 2 => 'Greek'],
            'en' => [1 => 'Anglais', 2 => 'English'],
            'es' => [1 => 'Espagnol', 2 => 'Spanish'],
            'et' => [1 => 'Estonien', 2 => 'Estonian'],
            'eu' => [1 => 'Basque', 2 => 'Basque'],
            'fi' => [1 => 'Finnois', 2 => 'Finnish'],
            'fr' => [1 => 'Français', 2 => 'French'],
            'ga' => [1 => 'Irlandais', 2 => 'Irish'],
            'hr' => [1 => 'Croate', 2 => 'Croatian'],
            'hu' => [1 => 'Hongrois', 2 => 'Hungarian'],
            'hy' => [1 => 'Arménien', 2 => 'Armenian'],
            'is' => [1 => 'Islandais', 2 => 'Icelandic'],
            'it' => [1 => 'Italien', 2 => 'Italian'],
            'ja' => [1 => 'Japonais', 2 => 'Japanese'],
            'lv' => [1 => 'Letton', 2 => 'Latvian'],
            'lt' => [1 => 'Lituanien', 2 => 'Lithuanian'],
            'mt' => [1 => 'Maltais', 2 => 'Maltese'],
            'nl' => [1 => 'Néerlandais', 2 => 'Dutch'],
            'no' => [1 => 'Norvégien', 2 => 'Norwegian'],
            'pl' => [1 => 'Polonais', 2 => 'Polish'],
            'pt' => [1 => 'Portugais', 2 => 'Portuguese'],
            'ro' => [1 => 'Roumain', 2 => 'Romanian'],
            'ru' => [1 => 'Russe', 2 => 'Russian'],
            'sk' => [1 => 'Slovaque', 2 => 'Slovak'],
            'sl' => [1 => 'Slovène', 2 => 'Slovenian'],
            'sq' => [1 => 'Albanais', 2 => 'Albanian'],
            'sr' => [1 => 'Serbe', 2 => 'Serbian'],
            'sv' => [1 => 'Suédois', 2 => 'Swedish'],
            'tr' => [1 => 'Turc', 2 => 'Turkish'],
            'uk' => [1 => 'Ukrainien', 2 => 'Ukrainian'],
            'zh' => [1 => 'Chinois', 2 => 'Chinese']
        ];
        $langName = (empty($langArray[$code][$idLang])) ? $code : $langArray[$code][$idLang];
        return $langName;
    }

    public static function cleanUrl($url, $keepQueryString = false, $keepFragment = false)
    {
        $parsedUrl = parse_url($url);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        if ($keepQueryString) {
            $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
            if ($query != "")
                $path .= '?' . $query;
        }
        if ($keepFragment) {
            $fragment = isset($parsedUrl['fragment']) ? $parsedUrl['fragment'] : '';
            if ($fragment != "")
                $path .= '#' . $fragment;
        }
        return $path;
    }

    public static function getDomainNameFromUrl($url)
    {
        $parse = parse_url($url);
        $domain_name = $parse['host'];
        // Enlever les "www." éventuels
        if (strpos($domain_name, 'www.') === 0) {
            $domain_name = substr($domain_name, 4);
        }
        return $domain_name;
    }

    public static function getJsonConfig($configName, $type = '', $dir = '')
    {
        opartStatTools::isValidDem();

        if (!validate::isFileName($configName))
            die('invalid file name');

        if ($type == 'reports')
            $finalDir = "reports/";
        else if ($type == 'metrics')
            $finalDir = "metrics/";
        else
            $finalDir = "";

        $finalDir = ($dir != '') ? $finalDir . $dir . '/' : $finalDir;

        //$jsonFilePath = $baseDir . 'opartstat/config/' . $finalDir . $configName . '.json';              

        if ($type == 'metrics' && opartRemoteTools::$useRemote) {
            $opartStatModule = Module::getInstanceByName('opartstat');
            $moduleVersion = $opartStatModule->version;

            $baseDir = opartRemoteTools::$remoteUrl . 'getjsonfile.php?moduleVersion=' . $moduleVersion . '&privateKey=' . OpartRemoteTools::$privateKey . '&fileName=';
            $jsonFilePath = $baseDir . $configName;
            $jsonContent = file_get_contents($jsonFilePath);
        } else {
            $baseDir = _PS_MODULE_DIR_;
            $jsonFileDir = $baseDir . 'opartstat/config/' . $finalDir;
            $jsonFilePath = $jsonFileDir . $configName . '.json';

            if ($type == 'reports' && !file_exists($jsonFilePath)) {
                if (!file_exists($jsonFileDir))
                    OpartStatTools::createDirectoryAndCopyIndex($jsonFileDir);

                $defaultJsonFilePath = _PS_MODULE_DIR_ . 'opartstat/config/' . $type . '/default/' . $configName . '_default.json';
                copy($defaultJsonFilePath, $jsonFilePath);
            }

            $fileSize = filesize($jsonFilePath);
            $jsonFile = fopen($jsonFilePath, "r");

            if ($jsonFile == false) {
                return false;
            } else {
                $stat = fstat($jsonFile);
                $fileSize = $stat['size'];
                $jsonContent = fread($jsonFile, $fileSize);
                fclose($jsonFile);
            }
        }
        $config = json_decode($jsonContent, true);
        if ($type == 'metrics') {
            $config['dir'] = $dir;
        }
        return $config;
    }

    public static function setReportConfig($reportConfig, $reportName, $idEmployee = null)
    {
        $context = Context::getContext();
        if ($idEmployee === null)
            $idEmployee = $context->employee->id;
        $customDir = _PS_MODULE_DIR_ . 'opartstat/config/reports/customs/' . $idEmployee;
        if (!file_exists($customDir))
            OpartStatTools::createDirectoryAndCopyIndex($customDir);

        $jsonFilePath = $customDir . '/' . $reportName . '.json';

        //$json = json_encode($reportConfig);
        $json = json_encode($reportConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        //write json to file
        if (file_put_contents($jsonFilePath, $json))
            return true;
        else
            return false;
    }

    /* public static function resetReportConfig($reportName)
    {        
        $defaultJsonFilePath = _PS_MODULE_DIR_ . 'opartstat/config/reports/default/' . $reportName . '_default.json';
        $context = Context::getContext();
        $idEmployee = $context->employee->id;
        $customDir = _PS_MODULE_DIR_ . 'opartstat/config/reports/customs/'.$idEmployee;
        if (!file_exists($customDir)) 
            OpartStatTools::createDirectoryAndCopyIndex($customDir); 

        $jsonFilePath = $customDir.'/'.$reportName.'.json';

        if (file_exists($defaultJsonFilePath)) {
            unlink($jsonFilePath);
            OpartStatTools::getJsonConfig($reportName, 'reports');
        } else
            return false;
    } */

    public static function deleteReport($reportName, $idEmployee = null)
    {
        $context = Context::getContext();
        if ($idEmployee === null)
            $idEmployee = $context->employee->id;

        $customDir = _PS_MODULE_DIR_ . 'opartstat/config/reports/customs/' . $idEmployee;
        $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $idEmployee);

        if (count($topMenuConfig['reports']) <= 2 || $reportName == "live")
            return false;

        $jsonFilePath = $customDir . '/' . $reportName . '.json';
        $jsonFileSettingsPath = $customDir . '/' . $reportName . '_settings.json';

        if (file_exists($jsonFilePath)) {
            unlink($jsonFilePath);
            unlink($jsonFileSettingsPath);
        } else
            return false;

        foreach ($topMenuConfig['reports'] as $report) {
            if ($reportName != $report['name'])
                $newTopMenuConfig['reports'][] = $report;
        }
        OpartStatTools::setReportConfig($newTopMenuConfig, 'top-menu', $idEmployee);

        $sql = "DELETE FROM " . _DB_PREFIX_ . "opartstat_shared_reports WHERE reportName = '" . pSQL($reportName) . "' AND ownerUserId=" . (int)$idEmployee;
        db::getInstance()->execute(($sql));

        return true;
    }

    static public function extractReportNameAndOwnerIdIfExists($reportName, $idEmployee)
    {
        $result['reportName'] = $reportName;
        $result['ownerUserId'] = $idEmployee;

        if (preg_match('/^shared_(\d+)_(.*)$/', $reportName, $matches)) {
            $result['ownerUserId'] = $matches[1];
            $result['reportName'] = $matches[2];
        }
        return $result;
    }

    public static function getLastWeekOfTheYear($date)
    {
        $y = (int)$date->format('y');
        $w = (int)$date->format('W');
        if ($w == 52 || $w == 53 || $w == 1) {
            $daysToAdd = 0;
            if ($date->format('N') == 1) { //lundi
                $daysToAdd = 3;
            }
            if ($date->format('N') == 2) { //mardi
                $daysToAdd = 2;
            }
            if ($date->format('N') == 3) { //mercredi
                $daysToAdd = 1;
            }
            $futurDate = $date->modify("+{$daysToAdd} days");
            if ($futurDate->format('y') > $y) {
                return ($y + 1);
            }

            $daysToRemove = 0;
            if ($date->format('N') == 5) { //vendredi
                $daysToRemove = 1;
            }
            if ($date->format('N') == 6) { //samedi
                $daysToRemove = 2;
            }
            if ($date->format('N') == 7) { //dimanche
                $daysToRemove = 3;
            }
            $pastDate = $date->modify("-{$daysToRemove} days");
            if ($pastDate->format('y') < $y) {
                return ($y - 1);
            }
        }
        return $y;
    }

    public static function getAllDatesBeetweenTwoDate($startDate, $endDate)
    {
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($endDate)
        );

        $totalPerYear = [];
        $totalPerMonth = [];
        $totalPerWeek = [];
        $totalPerDay = [];

        foreach ($period as $value) {
            $y = (int)$value->format('y');
            $m = (int)$value->format('n');
            $w = (int)$value->format('W');
            $d = (int)$value->format('z');

            //dont use $y,$m and $d because they are int
            $dateOfThisDay = $value->format('Y') . '-' . $value->format('m') . '-' . $value->format('d');

            //year need specialement treament for week because some week can extend on 2 years (week number 53 and 1)
            $yForWeek = OpartStatTools::getLastWeekOfTheYear($value);

            if (!isset($totalPerYear[$y]['date']))
                $totalPerYear[$y][0]['date'] = $dateOfThisDay;

            if (!isset($totalPerMonth[$y][$m]['date']))
                $totalPerMonth[$y][$m]['date'] = $dateOfThisDay;

            if (!isset($totalPerWeek[$yForWeek][$w]['date']))
                $totalPerWeek[$yForWeek][$w]['date'] = $dateOfThisDay;

            if (!isset($totalPerDay[$y][$d]['date']))
                $totalPerDay[$y][$d]['date'] = $dateOfThisDay;
        }

        return [
            'y' => $totalPerYear,
            'm' => $totalPerMonth,
            'w' => $totalPerWeek,
            'd' => $totalPerDay,
        ];
    }

    public static function createTotalPerArray($dateFrom, $dateTo)
    {
        $totalPerYear = [];
        $totalPerMonth = [];
        $totalPerWeek = [];
        $totalPerDay = [];

        $periodArrays = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);
        //var_dump($periodArrays);
        $totalPerYear = $periodArrays['y'];
        $totalPerMonth = $periodArrays['m'];
        $totalPerWeek = $periodArrays['w'];
        $totalPerDay = $periodArrays['d'];

        foreach ($totalPerYear as $y => $array1)
            $totalPerYear[$y][0]['value'] = 0;

        foreach ($totalPerMonth as $y => $array1)
            foreach ($array1 as $m => $array2)
                $totalPerMonth[$y][$m]['value'] = 0;

        foreach ($totalPerWeek as $y => $array1)
            foreach ($array1 as $w => $array2)
                $totalPerWeek[$y][$w]['value'] = 0;

        foreach ($totalPerDay as $y => $array1)
            foreach ($array1 as $d => $array2)
                $totalPerDay[$y][$d]['value'] = 0;

        $result = [
            'totalPerYear' => $totalPerYear,
            'totalPerMonth' => $totalPerMonth,
            'totalPerWeek' => $totalPerWeek,
            'totalPerDay' => $totalPerDay
        ];

        return $result;
    }

     public static function getValueFromCacheIfExists($sql, $dateTo, $useCache, $singleValue = false, $sessionsSource = false)
    {
        $cacheKey = hash('sha256', $sql);
        $cacheFile = _PS_MODULE_DIR_ . "opartstat/cache/{$cacheKey}";

        $dateTo = ($dateTo != 0) ? new DateTime($dateTo) : 0;
        $today = new DateTime();
        $today->setTime(23, 59, 59);

        if ($useCache == true && file_exists($cacheFile) && $dateTo instanceof DateTime && $dateTo != $today) {
            $data = file_get_contents($cacheFile);
            $data = json_decode($data, true);
            return $data;
        }

        $useExternal = $sessionsSource && (int)Configuration::get('OPARTSTAT_USE_SEPARATE_DB') === 1;

        if ($useExternal) {
            try {
                $pdo = self::getExternalPdo();
                if ($singleValue) {
                    $data = $pdo->query($sql)->fetchColumn();
                } else {
                    $stmt = $pdo->query($sql);
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e) {
                $data = ($singleValue) ? 0 : [];
            }
        } else {
            if ($singleValue == true) {
                $data = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
            } else {
                $data = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql);
            }
        }

        if ($dateTo instanceof DateTime && $dateTo != $today) {
            file_put_contents($cacheFile, json_encode($data));
        }

        return $data;
    }

    public static function getExternalPdo()
    {
        $dbHost = (string) Configuration::get('OPARTSTAT_DB_HOST');
        $dbPort = (string) (Configuration::get('OPARTSTAT_DB_PORT') ?: '3306');
        $dbName = (string) Configuration::get('OPARTSTAT_DB_NAME');
        $dbUser = (string) Configuration::get('OPARTSTAT_DB_USER');
        $dbPass = (string) Configuration::get('OPARTSTAT_DB_PASS');

        $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';

        return new PDO($dsn, $dbUser, $dbPass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ));
    }


    public static function getSingleNumberJsonResult($sql, $dateTo, $useCache, $valueType = '', $sessionsSource = false)
    {
        $data = self::getValueFromCacheIfExists($sql, $dateTo, $useCache, true, $sessionsSource);
        $data = ($data == false) ? 0 : $data;

        $result['value'] = $data;
        $result['conf'] = [
            'total' => $valueType
        ];
        return $result;
    }


    public static function getMetricResult($fctName, $vars, $dataFormat = '', $superiorIsBetter = true)
    {
        $getValueFctName = "get" . $fctName . "Values";

        $result['initial'] = $getValueFctName($vars['dateFrom'], $vars['dateTo'], $vars['initialFilters'], $vars);

        if (isset($vars['dateFromCompare']) &&  isset($vars['dateToCompare'])) {
            $result['compare'] = $getValueFctName($vars['dateFromCompare'], $vars['dateToCompare'], $vars['compareFilters'], $vars);
        }
        $result['conf']['dataFormat'] = $dataFormat;
        $result['conf']['superiorIsBetter'] = $superiorIsBetter;
        return $result;
    }

    public static function getBestMetricResult($fctName, $vars, $superiorIsBetter = true, $isCustom = false)
    {
        (int)$start = (isset($vars['otherVars']['start'])) ? $vars['otherVars']['start'] : 0;

        $limit = 5000; //number of lines read each time
        $getValueFctName = "get" . $fctName . "Values";

        $result['conf']['otherVars'] = $vars['otherVars'];

        $result['initial'] = $getValueFctName($vars['dateFrom'], $vars['dateTo'], $vars['initialFilters'], $start, $limit, $vars);

        $vars['dateFromCompare'] = (isset($vars['dateFromCompare'])) ? $vars['dateFromCompare'] : 0;
        $vars['dateToCompare'] = (isset($vars['dateToCompare'])) ? $vars['dateToCompare'] : 0;

        $result['compare'] = $getValueFctName($vars['dateFromCompare'], $vars['dateToCompare'], $vars['compareFilters'], $start, $limit, $vars);

        $result['conf']['allDataLoaded'] = false;
        if ($result['initial']['conf']['allDataLoaded'] == true && $result['compare']['conf']['allDataLoaded'] == true) {
            $result['conf']['allDataLoaded'] = true;
        }

        if ($result['conf']['allDataLoaded'] == false) {
            $result['conf']['otherVars']['start'] = $start + $limit;
        }

        $result['conf']['dateFrom'] = $vars['dateFrom'];
        $result['conf']['dateTo'] = $vars['dateTo'];
        $result['conf']['ajaxCallBack'] = $fctName;
        $result['conf']['isCustom'] = $isCustom;
        $result['conf']['superiorIsBetter'] = $superiorIsBetter;

        if (isset($vars['dateFromCompare'])) {
            $result['conf']['dateFromCompare'] = $vars['dateFromCompare'];
            $result['conf']['dateToCompare'] = $vars['dateToCompare'];
        }

        return $result;
    }

    static function purgeCacheFiles($purgeAllFiles = false)
    {
        $dirPath = _PS_MODULE_DIR_ . 'opartstat/cache/';
        $files = scandir($dirPath);

        $lastPurge = Configuration::get('OPARTSTAT_CACHE_LAST_PURGE');
        $purgeDelay = Configuration::get('OPARTSTAT_PURGE_CACHE_DELAY');
        $now = date('Y-m-d');
        $daysSinceLastPurge = (strtotime($now) - strtotime($lastPurge)) / (60 * 60 * 24);

        if ($daysSinceLastPurge < $purgeDelay && $purgeAllFiles == false) {
            return;
        }

        $fileMaxAge = $purgeAllFiles == true ? 0 : Configuration::get('OPARTSTAT_CACHE_FILE_MAX_AGE');

        foreach ($files as $file) {
            if ($file == 'index.php' || $file == '.htaccess')
                continue;

            $filePath = $dirPath . $file;
            if (is_file($filePath)) {
                $fileTime = filemtime($filePath);
                $now = time();
                $daysOld = ($now - $fileTime) / (60 * 60 * 24);

                if ($daysOld >= $fileMaxAge) {
                    unlink($filePath);
                }
            }
        }
        return Configuration::updateValue('OPARTSTAT_CACHE_LAST_PURGE', date('Y-m-d H:i:s'));
    }

    static public function addInArrayIfNotExists($array, $value)
    {
        if (!in_array($value, $array)) {
            $array[] = $value;
        }
        return $array;
    }

    public static function getJoins($filtersArray, $tableNotToJoin = [], $unauthorizedFilters = [], $isProfit = false)
    {
        $sqlJoins = "";

        if ($isProfit ==  true && Configuration::get('OPARTSTAT_USE_COMMISSIONS')) {
            $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
            $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "opartstat_commissions` opartstat_commissions ON orders. `" . bqSQL($dateColumn) . "` BETWEEN opartstat_commissions.startDate AND IFNULL(opartstat_commissions.endDate,NOW()) AND (opartstat_commissions.paymentMethod = orders.payment OR orders.payment LIKE CONCAT (opartstat_commissions.paymentMethod))";
        }

        if (!is_array($filtersArray) || count($filtersArray) == 0)
            return $sqlJoins;

        $tablesToJoin = [];
        foreach ($filtersArray as $excludeInclude => $array) {
            /* foreach ($array as $filterName => $filterValue) { */
            foreach ($array as $filterName => $filterDatas) {
                if (!array_key_exists('values', $filterDatas))
                    continue;
                $filterValues = $filterDatas['values'];
                if (in_array($filterName, $unauthorizedFilters))
                    continue;

                $useLike = (is_string($filterValues) && strpos($filterValues, '%') !== false);

                if ($filterName == 'products' || $filterName == 'categories' || $filterName == 'brands') {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'order_detail');
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'product');
                }
                if ($filterName == 'products')
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'product_lang');

                if ($filterName == 'attributes')
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'order_detail');

                if ($filterName == 'features')
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'order_detail');

                if ($filterName == 'customerGroups') {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'customer');
                    if ($useLike)
                        $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'group_lang');
                }
                if ($filterName == 'countries') {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'orders');
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'address');
                    if ($useLike)
                        $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'country_lang');
                }
                if ($filterName == 'brands' && $useLike) {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'manufacturer');
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'product');
                }
                if ($filterName == 'categories') {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'category');
                    if ($useLike)
                        $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'category_lang');
                }
                if ($filterName == 'device') {
                    $tablesToJoin = OpartStatTools::addInArrayIfNotExists($tablesToJoin, 'opartstat_sessions');
                }
            }
        }

        foreach ($tablesToJoin as $tableName) {
            if (in_array($tableName, $tableNotToJoin))
                continue;

            $idLang = Context::getContext()->language->id;

            if ($tableName == 'order_detail')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "order_detail` order_detail ON order_detail.id_order = orders.id_order";

            if ($tableName == 'product')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "product` product ON product.id_product = order_detail.product_id";

            if ($tableName == 'product_lang') {
                $shopConstraints = self::getShopConstraints("product_lang");
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "product_lang` product_lang ON product_lang.id_product = product.id_product AND " . $shopConstraints . " AND product_lang.id_lang = " . (int)$idLang;
            }

            /* if ($tableName == 'product_attribute_combination') {
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "product_attribute_combination` product_attribute_combination ON product_attribute_combination.id_product_attribute = order_detail.product_attribute_id";
            } */

            if ($tableName == 'customer')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "customer` customer ON customer.id_customer = orders.id_customer";

            if ($tableName == 'address')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "address` address ON address.id_address = orders.id_address_invoice";

            if ($tableName == 'country_lang') {
                $shopConstraints = self::getShopConstraints("country_lang");
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "country_lang` country_lang ON country_lang.id_country = address.id_country AND " . $shopConstraints . " AND country_lang.id_lang = " . (int)$idLang;
            }

            if ($tableName == 'manufacturer')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "manufacturer` manufacturer ON manufacturer.id_manufacturer = product.id_manufacturer";

            if ($tableName == 'group_lang') {
                $shopConstraints = self::getShopConstraints("group_lang");
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "group_lang` group_lang ON group_lang.id_group = customer.id_default_group AND " . $shopConstraints . " AND group_lang.id_lang = " . (int)$idLang;
            }

            if ($tableName == 'category')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "category` category ON category.id_category = product.id_category_default";

            if ($tableName == 'category_lang') {
                $shopConstraints = self::getShopConstraints("category_lang");
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "category_lang` category_lang ON category_lang.id_category = category.id_category AND " . $shopConstraints . " AND category_lang.id_lang = " . (int)$idLang;
            }

            if ($tableName == 'opartstat_sessions')
                $sqlJoins .= " LEFT JOIN `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions ON opartstat_sessions.idOrder = orders.id_order";
        }

        return $sqlJoins;
    }

    public static function getFilters($filtersArray, $unauthorizedFilters = [])
    {
        $sqlFilters = "";

        if (!is_array($filtersArray) || count($filtersArray) == 0)
            return $sqlFilters;

        foreach ($filtersArray as $excludeInclude => $array) {
            foreach ($array as $filterName => $filterDatas) {
                if (in_array($filterName, $unauthorizedFilters))
                    continue;

                if (!array_key_exists('values', $filterDatas))
                    continue;

                $useAnd = false;
                if (array_key_exists('useAnd', $filterDatas))
                    $useAnd = self::stringToBoolean($filterDatas['useAnd']);

                $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterDatas['values'], $excludeInclude, false, 'orders', 'id_shop', $useAnd);
            }
        }
        return $sqlFilters;
    }

    public static function getselectedItemsConstraints($filterName, $filterValues, $excludeInclude, $isJson = true, $shopConstraintPrefix = 'orders', $shopConstraintId = 'id_shop', $useAnd = false)
    {
        $selectedItemIds = $filterValues;
        $useLike = (is_string($selectedItemIds) && strpos($selectedItemIds, '%') !== false);
        $addLangConstraint = false;
        $addShopConstraint = false;
        $beforeConstraint = '';
        $afterConstraint = '';
        $comparisonSymbol = ($excludeInclude == "exclude") ? '<>' : '=';
        switch ($filterName) {
            case 'brands':
                if ($useLike) {
                    $prefix = 'manufacturer';
                    $fieldName = 'name';
                } else {
                    $prefix = 'product';
                    $fieldName = 'id_manufacturer';
                }
                break;
            case 'categories':
                if ($useLike) {
                    $prefix = 'category_lang';
                    $fieldName = 'name';
                    $addLangConstraint = true;
                    $addShopConstraint = true;
                } else {
                    /* if ((bool)$filterValue['getAllChildren'] == true)
                        $selectedItemIds = OpartStatTools::getAllChildrenCategories($selectedItemIds); */
                    $prefix = 'category';
                    $fieldName = 'id_category';
                }
                break;
            case 'customerGroups':
                if ($useLike) {
                    $prefix = 'group_lang';
                    $fieldName = 'name';
                    $addLangConstraint = true;
                } else {
                    $prefix = 'customer';
                    $fieldName = 'id_default_group';
                }
                break;
            case 'countries':
                if ($useLike) {
                    $prefix = 'country_lang';
                    $fieldName = 'name';
                    $addLangConstraint = true;
                } else {
                    $prefix = 'address';
                    $fieldName = 'id_country';
                }
                break;
            case 'products':
                if ($useLike) {
                    $prefix = 'product_lang';
                    $fieldName = 'name';
                    $addLangConstraint = true;
                    $addShopConstraint = true;
                } else if ($shopConstraintPrefix == 'product') {
                    $prefix = 'product';
                    $fieldName = 'id_product';
                } else {
                    $prefix = 'order_detail';
                    $fieldName = 'product_id';
                }
                break;
                /* case 'attributes':
                $prefix = 'product_attribute_combination';
                $fieldName = 'id_attribute';
                break; */
            case 'paymentMethods':
                $prefix = 'orders';
                $fieldName = 'payment';
                break;
            case 'productsVisits':
                $prefix = 'opartstat_sessions';
                $fieldName = 'elementId';
                $comparisonSymbol = '=';
                $beforeConstraint = ($excludeInclude == "exclude") ? 'NOT (' : '(';
                $afterConstraint = 'AND opartstat_sessions.controllerName = "ProductController")';
                break;
            case 'brandsVisits':
                $prefix = 'opartstat_sessions';
                $fieldName = 'elementId';
                $comparisonSymbol = '=';
                $beforeConstraint = ($excludeInclude == "exclude") ? 'NOT (' : '(';
                $afterConstraint = 'AND opartstat_sessions.controllerName = "ManufacturerController")';
                break;
            case 'categoriesVisits':
                /* if ((bool)$filterValue['getAllChildren'] == true)
                    $selectedItemIds = OpartStatTools::getAllChildrenCategories($selectedItemIds); */
                $prefix = 'opartstat_sessions';
                $fieldName = 'elementId';
                $comparisonSymbol = '=';
                $beforeConstraint = ($excludeInclude == "exclude") ? 'NOT (' : '(';
                $afterConstraint = 'AND opartstat_sessions.controllerName = "CategoryController")';
                break;
            case 'device':
                $prefix = 'opartstat_sessions';
                $fieldName = 'device';
                $comparisonSymbol = '=';
                $beforeConstraint = ($excludeInclude == "exclude") ? 'NOT (' : '(';
                $afterConstraint = ')';
                break;
        }

        if (!validate::isTableOrIdentifier($shopConstraintPrefix))
            die('shopConstraintPrefix not valid');

        $langConstraintSql = ($addLangConstraint) ? '  AND ' . $prefix . '.id_lang = ' . (int)Context::getContext()->language->id : '';
        $addShopConstraintSql = ($addShopConstraint) ? ' AND ' . $shopConstraintPrefix . '.`' . bqSQL($shopConstraintId) . '` = ' . $prefix . '.id_shop' : '';

        if ($isJson)
            $selectedItemIds = json_decode($selectedItemIds);

        if ($filterName == "attributes") {
            $ids = implode(',', array_map('intval', $selectedItemIds));
            $notExist = ($excludeInclude == "exclude") ? 'NOT ' : '';
            if ($useAnd == true) {
                $constraint = $notExist . 'EXISTS (
                    SELECT 1 FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                    WHERE pac.id_product_attribute = order_detail.product_attribute_id
                    AND pac.id_attribute IN (' . $ids . ')
                    GROUP BY pac.id_product_attribute
                    HAVING COUNT(DISTINCT pac.id_attribute) = ' . count($selectedItemIds) . '
                )';
            } else {
                $constraint = $notExist . 'EXISTS (
                    SELECT 1
                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                    WHERE pac.id_product_attribute = order_detail.product_attribute_id
                    AND pac.id_attribute IN (' . $ids . ')
                )';
            }
        } else if ($filterName == "features") {
            $ids = implode(',', array_map('intval', $selectedItemIds));
            $notExist = ($excludeInclude == "exclude") ? 'NOT ' : '';
            if ($useAnd == true) {
                $constraint = $notExist . 'EXISTS (
                    SELECT 1 FROM ' . _DB_PREFIX_ . 'feature_product fp
                    WHERE fp.id_product = order_detail.product_id
                    AND fp.id_feature_value IN (' . $ids . ')
                    GROUP BY fp.id_product
                    HAVING COUNT(DISTINCT fp.id_feature_value) = ' . count($selectedItemIds) . '
                )';
            } else {
                $constraint = $notExist . 'EXISTS (
                    SELECT 1
                    FROM ' . _DB_PREFIX_ . 'feature_product fp
                    WHERE fp.id_product = order_detail.product_id
                    AND fp.id_feature_value IN (' . $ids . ')
                )';
            }
        } else {
            $constraint = "";
            $prefix = ($prefix == false) ? '' : $prefix . '.';

            $andOr = ($excludeInclude == "exclude") ? ' AND ' : ' OR ';
            if ($useLike) {
                $notLike = ($excludeInclude == "exclude") ? 'NOT ' : '';
                $constraint = $prefix . '`' . bqSQL($fieldName) . '` ' . $notLike . 'LIKE "' . pSQL($selectedItemIds) . '"' . $langConstraintSql . $addShopConstraintSql;
            } else {
                foreach ($selectedItemIds as $id) {
                    $adToConstraint = $beforeConstraint . $prefix . '`' . bqSQL($fieldName) . '` ' . $comparisonSymbol . ' "' . pSQL($id) . '"' . $afterConstraint;
                    $constraint .= ($constraint == "")
                        ? $adToConstraint
                        : $andOr . $adToConstraint;
                }
            }
        }
        $constraint = ' AND (' . $constraint . ')';
        return $constraint;
    }

    public static function getFiltersForOpartSessionTable($filtersArray, $unauthorizedFilters = [])
    {
        $sqlFilters = '';
        if (is_array($filtersArray) && count($filtersArray) > 0) {
            foreach ($filtersArray as $excludeInclude => $array) {
                foreach ($array as $filterName => $filterDatas) {
                    $filterValues = $filterDatas['values'];
                    if (!array_key_exists('values', $filterDatas))
                        continue;

                    if (in_array($filterName, $unauthorizedFilters))
                        continue;

                    if ($filterName == 'categories') {
                        $filterName = 'categoriesVisits';
                        $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValues, $excludeInclude, false, 'opartstat_sessions', 'shopId');
                    }

                    if ($filterName == 'brands') {
                        $filterName = 'brandsVisits';
                        $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValues, $excludeInclude, false, 'opartstat_sessions', 'shopId');
                    }

                    if ($filterName == 'products') {
                        $filterName = 'productsVisits';
                        $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValues, $excludeInclude, false, 'opartstat_sessions', 'shopId');
                    }

                    if ($filterName == 'device') {
                        $filterName = 'device';
                        $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValues, $excludeInclude, false, 'opartstat_sessions', 'shopId');
                    }
                }
            }
        }
        return $sqlFilters;
    }

    public static function createSqlCategoriesFilter($categoryIds, $getAllChildren = false)
    {
        if ($getAllChildren == true)
            $categoryIds = OpartStatTools::getAllChildrenCategories($categoryIds);

        return OpartStatTools::getselectedItemsConstraints($categoryIds, 'p', 'id_category_default', false);
    }

    public static function getAllChildrenCategories($selectedCatIds)
    {
        $db = db::getInstance();
        //$selectedCatId = json_decode($selectedCatIds);
        $allCatIds = [];
        foreach ($selectedCatIds as $catId) {
            $getSubCategoriesQuery = "SELECT
                            category.id_category
                        FROM
                            " . _DB_PREFIX_ . "category AS category
                        INNER JOIN 
                            " . _DB_PREFIX_ . "category AS category2 
                        ON 
                            category.nleft 
                        BETWEEN 
                            category2.nleft 
                        AND 
                            category2.nright
                        WHERE
                            category.nleft BETWEEN
                                (SELECT nleft FROM " . _DB_PREFIX_ . "category WHERE id_category = " . (int)$catId . " ) AND
                                (SELECT nright FROM " . _DB_PREFIX_ . "category WHERE id_category = " . (int)$catId . ")
                        GROUP BY
                            category.id_category,
                            category.nleft,
                            category.nright
                        ORDER BY
                            category.nleft";
            $tempCatIds = $db->executes($getSubCategoriesQuery);
            foreach ($tempCatIds as $tempCatId)
                $allCatIds[] = $tempCatId['id_category'];
        }
        return $allCatIds;
    }

    public static function addChildrenCategoriesToFiltersArray($filtersArray)
    {
        if (is_array($filtersArray) && count($filtersArray) > 0) {
            foreach ($filtersArray as &$array) { // Use reference to modify the original array
                foreach ($array as &$filterValue) { // Use reference to modify the original filterValue
                    if (!array_key_exists('values', $filterValue))
                        continue;
                    if (isset($filterValue['getAllChildren']) && (bool)$filterValue['getAllChildren'] === true) {
                        $filterValue['values'] = OpartStatTools::getAllChildrenCategories($filterValue['values']);
                    }
                }
            }
        }
        return $filtersArray;
    }

    /* public static function createSqlBrandsFilter($brandsId)
    {
        return OpartStatTools::getselectedItemsConstraints($brandsId, 'p', 'id_manufacturer', false);
    }

    public static function createSqlCustomerGroupsFilter($customerGroupIds)
    {
        return OpartStatTools::getselectedItemsConstraints($customerGroupIds, 'c', 'id_default_group', false);
    } */

    public static function populatePeriodArray($dateFrom, $dateTo, $values, $keyDate, $keyValue)
    {
        $totalPerArrays = OpartStatTools::createTotalPerArray($dateFrom, $dateTo);
        $totalPerYear = $totalPerArrays['totalPerYear'];
        $totalPerMonth = $totalPerArrays['totalPerMonth'];
        $totalPerWeek = $totalPerArrays['totalPerWeek'];
        $totalPerDay = $totalPerArrays['totalPerDay'];

        $totalGlobal = 0;


        if ($values != null) {
            foreach ($values as $value) {
                $valueTotal = (float)$value[$keyValue];
                $totalGlobal = (isset($totalGlobal)) ? $totalGlobal + $valueTotal : $valueTotal;

                $y = (int)date("y", strtotime($value[$keyDate]));
                $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($value[$keyDate]));
                $m = (int)date("n", strtotime($value[$keyDate]));
                $w = (int)date("W", strtotime($value[$keyDate]));
                $d = (int)date("z", strtotime($value[$keyDate]));

                (float)$totalPerYear[$y][0]['value'] = (isset($totalPerYear[$y][0]['value'])) ? $totalPerYear[$y][0]['value'] + $valueTotal : $valueTotal;
                (float)$totalPerMonth[$y][$m]['value'] = (isset($totalPerMonth[$y][$m]['value'])) ? $totalPerMonth[$y][$m]['value'] + $valueTotal : $valueTotal;
                (float)$totalPerWeek[$yForWeek][$w]['value'] = (isset($totalPerWeek[$yForWeek][$w]['value'])) ? $totalPerWeek[$yForWeek][$w]['value'] + $valueTotal : $valueTotal;
                (float)$totalPerDay[$y][$d]['value'] = (isset($totalPerDay[$y][$d]['value'])) ? $totalPerDay[$y][$d]['value'] + $valueTotal : $valueTotal;
            }
        }

        ksort($totalPerYear);
        ksort($totalPerMonth);
        ksort($totalPerWeek);
        ksort($totalPerDay);

        $res = [
            'totalGlobal' => $totalGlobal,
            'totalPerYear' => $totalPerYear,
            'totalPerMonth' => $totalPerMonth,
            'totalPerWeek' => $totalPerWeek,
            'totalPerDay' => $totalPerDay
        ];
        return $res;
    }

    public static function populatePeriodArrayUsingAverage($dateFrom, $dateTo, $values, $keyDate = 'date_add', $keyValue = 'total')
    {
        $totalGlobal = 0;

        $r = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);

        foreach ($r['y'] as $y => $array1) {
            $r['y'][$y][0]['total'] = 0;
            $r['y'][$y][0]['divider'] = 0;
        }

        foreach ($r['m'] as $y => $array1) {
            foreach ($array1 as $m => $array2) {
                $r['m'][$y][$m]['total'] = 0;
                $r['m'][$y][$m]['divider'] = 0;
            }
        }

        foreach ($r['w'] as $y => $array1) {
            foreach ($array1 as $w => $array2) {
                $r['w'][$y][$w]['total'] = 0;
                $r['w'][$y][$w]['divider'] = 0;
            }
        }

        foreach ($r['d'] as $y => $array1) {
            foreach ($array1 as $d => $array2) {
                $r['d'][$y][$d]['total'] = 0;
                $r['d'][$y][$d]['divider'] = 0;
            }
        }
        $dividerGlobal = 0;

        if ($values != null) {
            foreach ($values as $value) {
                $orderTotal = (float)$value[$keyValue];

                $totalGlobal = (isset($totalGlobal)) ? $totalGlobal + $orderTotal : $orderTotal;
                $dividerGlobal = $dividerGlobal + 1;

                $y = (int)date("y", strtotime($value[$keyDate]));
                $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($value[$keyDate]));
                $m = (int)date("m", strtotime($value[$keyDate]));
                $w = (int)date("W", strtotime($value[$keyDate]));
                $d = (int)date("z", strtotime($value[$keyDate]));

                (float)$r['y'][$y][0]['total'] = (isset($r['y'][$y][0]['total'])) ? $r['y'][$y][0]['total'] + $orderTotal : $orderTotal;
                (int)$r['y'][$y][0]['divider'] = (isset($r['y'][$y][0]['divider'])) ? $r['y'][$y][0]['divider'] + 1 : 1;

                (float)$r['m'][$y][$m]['total'] = (isset($r['m'][$y][$m]['total'])) ? $r['m'][$y][$m]['total'] + $orderTotal : $orderTotal;
                (int)$r['m'][$y][$m]['divider'] = (isset($r['m'][$y][$m]['divider'])) ? $r['m'][$y][$m]['divider'] + 1 : 1;

                (float)$r['w'][$yForWeek][$w]['total'] = (isset($r['w'][$yForWeek][$w]['total'])) ? $r['w'][$yForWeek][$w]['total'] + $orderTotal : $orderTotal;
                (int)$r['w'][$yForWeek][$w]['divider'] = (isset($r['w'][$yForWeek][$w]['divider'])) ? $r['w'][$yForWeek][$w]['divider'] + 1 : 1;

                (float)$r['d'][$y][$d]['total'] = (isset($r['d'][$y][$d]['total'])) ? $r['d'][$y][$d]['total'] + $orderTotal : $orderTotal;
                (int)$r['d'][$y][$d]['divider'] = (isset($r['d'][$y][$d]['divider'])) ? $r['d'][$y][$d]['divider'] + 1 : 1;
            }
        }
        //calc average
        foreach ($r as $key => $array1) {
            foreach ($array1 as $y => $array2) {
                foreach ($array2 as $period => $vals) {
                    if ($vals['divider'] == 0)
                        $average = 0;
                    else
                        $average = $vals['total'] / $vals['divider'];

                    $r[$key][$y][$period]['value'] = $average;
                }
            }
        }

        ksort($r['y']);
        ksort($r['m']);
        ksort($r['w']);
        ksort($r['d']);

        if ($dividerGlobal == 0)
            $dividerGlobal = 1;

        $globalAverage = $totalGlobal / $dividerGlobal;

        $result = [
            'totalGlobal' => $globalAverage,
            'totalPerYear' => $r['y'],
            'totalPerMonth' => $r['m'],
            'totalPerWeek' => $r['w'],
            'totalPerDay' => $r['d']
        ];

        return $result;
    }

    public static function moduleIsLinked($moduleName)
    {
        $modulesAlreadyLinkedString = Configuration::get('OPARTSTAT_PARTNERMODULES_LINKED');
        $modulesAlreadyLinked = explode('|', $modulesAlreadyLinkedString);
        if (in_array($moduleName, $modulesAlreadyLinked))
            return true;
        return false;
    }

    public static function getAllModulesLinked()
    {
        $modulesAlreadyLinkedString = Configuration::get('OPARTSTAT_PARTNERMODULES_LINKED');
        $modulesAlreadyLinked = explode('|', $modulesAlreadyLinkedString);
        return $modulesAlreadyLinked;
    }

    public static function getTotalRefundedFields($withSum = true)
    {
        //if (_PS_VERSION_ < "1.7.7.0")
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<'))
            $fields = '(order_detail.product_quantity_refunded*order_detail.unit_price_tax_excl)';
        else
            $fields = 'order_detail.total_refunded_tax_excl';

        if ($withSum == true)
            return 'SUM(IFNULL(' . $fields . ', 0))';
        else
            return $fields;
    }

    public static function getTotalRevenueFields($multiplyByConversionRate = true, $removeRefunds = true, $forceExcludeShipping = null)
    {
        $totalRefundedFields = ($removeRefunds == true) ? '-' . opartStatTools::getTotalRefundedFields() : '';
        //$conversionRate = ($multiplyByConversionRate == true) ? '/orders.conversion_rate' : '';
        $conversionRate = ($multiplyByConversionRate == true) ? '/COALESCE(NULLIF(orders.conversion_rate, 0), 1)' : '';

        if ($forceExcludeShipping === null)
            $excludeShipping = Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING');
        else
            $excludeShipping = $forceExcludeShipping;

        if ($excludeShipping == 0) {
            $fields = '(
                        orders.total_paid_tax_excl 
                        ' . $totalRefundedFields . '
                    )' . $conversionRate . '';
        } else {
            $fields = '(
                        (orders.total_paid_tax_excl - 
                            IF(
                                order_cart_rule.value_tax_excl IS NULL,
                                orders.total_shipping_tax_excl,
                                0
                            )
                        )
                        ' . $totalRefundedFields . ' 
                    )' . $conversionRate . '';
        }
        $fields = trim(preg_replace('/\s+/', ' ', $fields)); //remove /n from $field var
        return $fields;
    }

    public static function getTotalProfitFields($forceExcludeShipping = null)
    {
        $revenueFields = opartStatTools::getTotalRevenueFields(false, true, $forceExcludeShipping);

        $costSfields = OpartStatTools::getCostsFields();
        $fields = '(
            (' . $revenueFields . ') 
            - ' . $costSfields . '
            )/COALESCE(NULLIF(orders.conversion_rate, 0), 1)';
        $fields = trim(preg_replace('/\s+/', ' ', $fields)); //remove /n from $field var
        return $fields;
    }

    public static function getCommissionsFields()
    {
        if (Configuration::get('OPARTSTAT_USE_COMMISSIONS')) {
            $commissionField =
                '(IFNULL(opartstat_commissions.fixedFees,0) + orders.total_paid_tax_incl * IFNULL(opartstat_commissions.variableFees,0) / 100)';
        } else {
            $commissionField = '';
        }
        return $commissionField;
    }

    public static function getGroupBy($filtersArray, $isProfit = false, $forceUseOrderDetailGroup = null)
    {
        $useOrderDetailGroup = false;
        if ($forceUseOrderDetailGroup != null)
            $useOrderDetailGroup = $forceUseOrderDetailGroup;
        else
            $useOrderDetailGroup = self::needDetailLine($filtersArray);
        /* else if (is_array($filtersArray)) {
            foreach ($filtersArray as $array) {
                if (
                    is_array($array) && 
                    (
                        array_key_exists('categories', $array) || 
                        array_key_exists('brands', $array) || 
                        array_key_exists('products', $array) || 
                        array_key_exists('attributes', $array) || 
                        array_key_exists('features', $array)
                    )
                )
                    $useOrderDetailGroup = true;
            }
        }  */

        if ($useOrderDetailGroup) {
            $groupBy = 'GROUP BY order_detail.id_order_detail';
        } else {
            $groupBy = 'GROUP BY orders.id_order';
        }

        if (Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING') == 1) {
            $groupBy = $groupBy . ",order_cart_rule.id_order_cart_rule";
        }

        if ($isProfit && Configuration::get('OPARTSTAT_USE_COMMISSIONS')) {
            $groupBy = $groupBy . ",opartstat_commissions.commissionId";
        }
        return $groupBy;
    }

    public static function needDetailLine($filtersArray)
    {
        $useDetailline = false;
        if (is_array($filtersArray)) {
            foreach ($filtersArray as $array) {
                if (
                    is_array($array)
                    && (
                        array_key_exists('categories', $array) ||
                        array_key_exists('brands', $array) ||
                        array_key_exists('products', $array) ||
                        array_key_exists('attributes', $array) ||
                        array_key_exists('features', $array)
                    )
                )
                    $useDetailline = true;
            }
        }
        return $useDetailline;
    }

    public static function getFields($filtersArray, $fieldsType = 'revenue', $forceExcludeShipping = null)
    {
        $useDetailline = OpartStatTools::needDetailLine($filtersArray);

        if ($useDetailline) {
            if ($fieldsType == 'profits')
                $fields = OpartStatTools::getProfitFieldsForOrderDetailLine(true, $forceExcludeShipping);
            else
                $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine(true, $forceExcludeShipping);
        } else {
            if ($fieldsType == 'profits')
                $fields = OpartStatTools::getTotalProfitFields($forceExcludeShipping);
            else
                $fields = OpartStatTools::getTotalRevenueFields(true, true, $forceExcludeShipping);
        }
        return $fields;
    }

    public static function getRevenueFieldsForOrderDetailLine($multiplyByConversionRate = true, $forceExcludeShipping = null)
    {
        $refundedFields = opartStatTools::getTotalRefundedFields(false);
        $conversionRate = ($multiplyByConversionRate == true) ? '/COALESCE(NULLIF(orders.conversion_rate, 0), 1)' : '';
        if ($forceExcludeShipping === null)
            $excludeShipping = Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING');
        else
            $excludeShipping = $forceExcludeShipping;

        if ($excludeShipping == 1)
            $removeFreeShippingDiscountFromTotalDiscount = "-IFNULL(order_cart_rule.value_tax_excl,0)";
        else
            $removeFreeShippingDiscountFromTotalDiscount = "";
        $fields = ' (
                        order_detail.total_price_tax_excl - ' . $refundedFields . ' - (
                            (orders.total_discounts_tax_excl' . $removeFreeShippingDiscountFromTotalDiscount . ') * (order_detail.total_price_tax_excl/orders.total_products)
                        ) 
                    )' . $conversionRate . '';
        $fields = trim(preg_replace('/\s+/', ' ', $fields)); //remove /n from $field var
        return $fields;
    }

    /* public static function getCostsFields($forDetailLine = false)
    {
        $commissionField = OpartStatTools::getCommissionsFields();
        if (!$forDetailLine) {
            if ($commissionField != "")
                $commissionField = "+ " . $commissionField;

            $costsFields = '(SUM(
                (order_detail.product_quantity-order_detail.product_quantity_refunded) * IFNULL(order_detail.purchase_supplier_price,order_detail.original_wholesale_price)
            )
            ' . $commissionField . ')';
        } else {
            $commissionField = opartStatTools::getCommissionsFields();
            if ($commissionField != "") {
                $commissionField = "+ " . $commissionField . " * (order_detail.total_price_tax_excl / orders.total_products)";
            }
            $costsFields = '(
                (order_detail.product_quantity-order_detail.product_quantity_refunded) * IFNULL(order_detail.purchase_supplier_price,order_detail.original_wholesale_price)
                ' . $commissionField . '
            )';
        }

        return $costsFields;
    } */

    public static function getCostsFields($forDetailLine = false)
    {
        $commissionField = OpartStatTools::getCommissionsFields();
        /* $costsFields = '(order_detail.product_quantity-order_detail.product_quantity_refunded) * 
        COALESCE(
            CASE 
                WHEN order_detail.purchase_supplier_price > 0 THEN order_detail.purchase_supplier_price
                ELSE order_detail.original_wholesale_price
            END,
            order_detail.original_wholesale_price
        )'; */
        $costsFields = '(order_detail.product_quantity-order_detail.product_quantity_refunded) * 
        (
            IF(order_detail.purchase_supplier_price IS NOT NULL AND order_detail.purchase_supplier_price > 0, 
                order_detail.purchase_supplier_price, 
                IF(order_detail.original_wholesale_price IS NOT NULL, 
                    order_detail.original_wholesale_price, 
                    0
                )
            )
        )';
        if (!$forDetailLine) {
            if ($commissionField != "")
                $commissionField = "+ " . $commissionField;

            $costsFields = '(SUM(' . $costsFields . ')' . $commissionField . ')';
        } else {
            $commissionField = opartStatTools::getCommissionsFields();
            if ($commissionField != "") {
                $commissionField = "+ " . $commissionField . " * (order_detail.total_price_tax_excl / orders.total_products)";
            }
            $costsFields = '(
                ' . $costsFields . '
                ' . $commissionField . '
            )';
        }

        return $costsFields;
    }

    public static function getProfitFieldsForOrderDetailLine($forceExcludeShipping = null)
    {
        $costSfields = OpartStatTools::getCostsFields(true);

        $revenueFields = opartStatTools::getRevenueFieldsForOrderDetailLine(true, $forceExcludeShipping);
        $fields = '(' . $revenueFields . ' 
                        - 
                        ' . $costSfields . '
                    )';
        $fields = trim(preg_replace('/\s+/', ' ', $fields)); //remove /n from $field var
        return $fields;
    }

    public static function getShopConstraints($prefix = 'orders', $fieldName = 'id_shop')
    {
        $shops = self::getShops();

        if (!validate::isTableOrIdentifier($prefix))
            die('prefix is not valid');

        $shopConstraints = "";
        foreach ($shops as $shop) {
            $shopConstraints .= ($shopConstraints == "") ? $prefix . '.`' . bqSQL($fieldName) . '` = ' . (int)$shop : ' OR ' . $prefix . '.`' . bqSQL($fieldName) . '` = ' . (int)$shop;
        }

        return ' (' . $shopConstraints . ')';
    }

    public static function getShops()
    {
        if (Shop::getContext() == Shop::CONTEXT_ALL)
            $shops = Shop::getShops(false, null, true);

        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $idShopGroup = Shop::getContextShopGroupID();
            $shops = Shop::getShops(false, $idShopGroup, true);
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP)
            $shops[Context::getContext()->shop->id] = Context::getContext()->shop->id;

        return $shops;
    }

    public static function isValidDem()
    {
        if (opartRemoteTools::$useRemote) {
            $filePath = opartRemoteTools::$remoteUrl . 'checkvalidity.php?privateKey=' . opartRemoteTools::$privateKey;
            $content = file_get_contents($filePath);
            if ($content === 'true')
                return true;
            else
                return false;
        } else
            return true;
    }

    public static function getDemTimeLeft()
    {
        if (!OpartRemoteTools::$useRemote) {
            return false;
        }
        $filePath = opartRemoteTools::$remoteUrl . 'checkvalidity.php?privateKey=' . opartRemoteTools::$privateKey . '&getDemTimeLeft=1';
        $content = file_get_contents($filePath);
        return intval($content);
    }

    public static function isDateFormat($str)
    {
        return (bool) preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})?$/', $str);
    }

    public static function getAdminMenuLinks($currentPage)
    {
        $context = Context::getContext();

        $linksArray = [
            'moduledir' => _PS_MODULE_DIR_ . 'opartstat',
            'currentPage' => $currentPage,
            'settingsGlobalLink' => $context->link->getAdminLink('AdminOpartStatSettingsGlobal', true),
            'settingsRobotsLink' => $context->link->getAdminLink('AdminOpartStatSettingsRobots', true),
            'settingsIpsLink' => $context->link->getAdminLink('AdminOpartStatSettingsIps', true),
            'settingsModulesLink' => $context->link->getAdminLink('AdminOpartStatSettingsModules', true),
            'settingsTrackableLinksCreatorLink' => $context->link->getAdminLink('AdminOpartStatSettingsTrackableLinksCreator', true),
            'settingsCommissionsLink' => $context->link->getAdminLink('AdminOpartStatSettingsCommissions', true),
            'settingsSubscriptionLink' => $context->link->getAdminLink('AdminOpartStatSubscriptionSellsPage', true),
            'settingsAdvancedLink' => $context->link->getAdminLink('AdminOpartStatSettingsAdvanced', true),
            //'useGoogleAds' => Configuration::get('OPARTSTAT_USE_SAAS')
        ];

        return $linksArray;
    }

    public static function getDateFormat()
    {
        //if (_PS_VERSION_ >= 1.7) {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return Tools::getDateFormat();
        } else {
            $format = Context::getContext()->language->date_format_lite;
            $search = ['d', 'm', 'Y'];
            $replace = ['DD', 'MM', 'YYYY'];
            $format = str_replace($search, $replace, $format);
            return $format;
        }
    }

    public static function cleanPrice($str)
    {
        $str = str_replace(',', '.', $str);
        $str = trim($str);
        return $str;
    }

    public static function createDirectoryAndCopyIndex($dirName)
    {
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
            copy('../index.php', $dirName . '/index.php');
        }
    }

    public static function isGranted($controllerClassName, $akedPermissions)
    {
        $context = Context::getContext();
        if (!$context->employee->isLoggedBack())
            return;

        $profilId = $context->employee->id_profile;
        $className = str_replace('Controller', '', $controllerClassName);
        $idCurrentTab = (int)Tab::getIdFromClassName($className);
        $profileAccess = Profile::getProfileAccess($profilId, $idCurrentTab);
        if ($profileAccess[$akedPermissions] === '1')
            return true;
        else
            return false;
    }

    public static function getShopToken($encryptedTokenBase64 = null)
    {
        $method = "AES-256-CBC";
        if ($encryptedTokenBase64 == null) {
            $sql = "SELECT value FROM " . _DB_PREFIX_ . "opartstat_config WHERE name='shopToken'";
            $encryptedTokenBase64 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        if ($encryptedTokenBase64 == null)
            return null;

        $encryptedToken = base64_decode($encryptedTokenBase64);
        $config = self::getSecretTokenKeyAndIv();
        $token = openssl_decrypt($encryptedToken, $method, $config['secretTokenKey'], OPENSSL_RAW_DATA, $config['iv']);
        return $token;
    }

    public static function saveShopToken($token)
    {
        $config = self::getSecretTokenKeyAndIv();
        $method = "AES-256-CBC";
        $encryptedToken = openssl_encrypt($token, $method, $config['secretTokenKey'], OPENSSL_RAW_DATA, $config['iv']);
        $encryptedTokenBase64 = base64_encode($encryptedToken);
        $db = Db::getInstance();
        $sql = "INSERT INTO " . _DB_PREFIX_ . "opartstat_config (name, value) VALUES ('shopToken', '" . pSQL($encryptedTokenBase64) . "')
                    ON DUPLICATE KEY UPDATE value = VALUES(value)";
        if (!$db->execute($sql))
            return false;

        $response = self::getSaasResponse("controllers/saveToken.php", ['encryptedToken' => $encryptedTokenBase64]);

        if ($response['success'] == false)
            return $response;

        return true;
    }

    public static function createSecretTokenKey()
    {
        $method = "AES-256-CBC";
        $secretKey = base64_encode(openssl_random_pseudo_bytes(16));
        $iv = base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));


        $configFile = dirname(__FILE__) . '/../config/config.ini';

        if (!file_exists($configFile)) {
            touch($configFile);
        }

        $config = parse_ini_file($configFile);
        $config['secretTokenKey'] = $secretKey;
        $config['iv'] = $iv;
        $newConfig = '';

        foreach ($config as $key => $value) {
            $newConfig .= "$key = \"$value\"\n";
        }

        if (file_put_contents($configFile, $newConfig) === false)
            return false;

        return true;
    }

    public static function getSecretTokenKeyAndIv()
    {
        $newConfig = [];
        $configFile = dirname(__FILE__) . '/../config/config.ini';
        $config = parse_ini_file($configFile);
        foreach ($config as $key => $value) {
            $newConfig[$key] = base64_decode($value);
        }
        return $newConfig;
    }

    public static function getSaasDomain()
    {
        $sql = "SELECT value FROM " . _DB_PREFIX_ . "opartstat_config WHERE name = 'saasDomain'";
        $saasDomain = Db::getInstance()->getValue($sql);
        if ($saasDomain == null)
            $saasDomain = 'https://saas.opart-stat.com/';

        return $saasDomain;
    }

    static public function getSaasResponse($page, $datas = null, $debug = false, $timeOut = false, $customDomain = null, $encryptedTokenBase64 = null)
    {
        $token = OpartStatTools::getShopToken($encryptedTokenBase64);

        if ($token == null)
            return ['success' => false, 'message' => "Token does not exist in Op'art stat module"];

        $error = "";
        $ch = curl_init();

        if ($debug == true)
            $page = $page . "?debug=true";

        if ($customDomain == null)
            $apiUrl = self::getSaasDomain() . $page;
        else
            $apiUrl = $customDomain . $page;

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        if ($timeOut != false)
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeOut);

        if ($datas != null) {
            $jsonDatas = json_encode($datas);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatas);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonDatas),
                'Authorization: Bearer ' . $token
            ));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token
            ));
        }

        $jsonResponse = curl_exec($ch);

        if ($debug == true) {
            print_r($jsonResponse);
            //var_dump($jsonResponse);
            die('<br /> -- debug mode for getSaasDomain --<br />');
        }

        $response = json_decode($jsonResponse, true);

        $error = curl_errno($ch);
        if ($error) {
            if ($timeOut != false && $error == CURLE_OPERATION_TIMEDOUT) {
                curl_close($ch);
                return true;
            }
            echo 'Error:' . curl_error($ch);
            die();
        }
        curl_close($ch);

        return $response;
    }

    static public function getMetricsResultFromSaas($page, $useCache, $datas = null, $debug = false, $customDomain = null)
    {
        $cacheKey = hash('sha256', $page . json_encode($datas));
        $cacheFile = _PS_MODULE_DIR_ . "opartstat/cache/{$cacheKey}";

        $dateTo = (isset($datas['dateTo']) && $datas['dateTo'] != 0) ? new DateTime($datas['dateTo']) : 0;
        $today = new DateTime();
        $today->setTime(23, 59, 59);

        if ($useCache == true && file_exists($cacheFile) && $dateTo instanceof DateTime && $dateTo != $today && $debug == false) {
            $response = file_get_contents($cacheFile);
            $response = json_decode($response, true);  // Désérialisez les données
        } else {
            $response = OpartStatTools::getSaasResponse($page, $datas, $debug, false, $customDomain);

            if ($dateTo instanceof DateTime && $dateTo != $today)
                file_put_contents($cacheFile, json_encode($response));
        }

        if ($response == null || !array_key_exists('success', $response)) {
            echo '$response is null or success property is missing in response object';
            die();
        }

        if ($response['success'] == false) {
            if (array_key_exists('action', $response)) {
                $result['action'] = $response;
                return $result;
            } else {
                echo $response['message'];
                die();
            }
        } else
            return $response;
    }

    public static function shopHasActiveSubscription()
    {
        $response = OpartStatTools::getSaasResponse("controllers/shopIsActive.php");
        if ($response['success'] == true)
            return true;
        else
            return false;
    }

    public static function getOriginConversion($attributionMethod, $datas)
    {
        $ordersPlaced = $datas['ordersPlaced'];
        if ($attributionMethod == "firstClick") {
            $firstClicksByIdCart = self::getFirstClicksByIdCart($datas);
            if (count($firstClicksByIdCart) == 0)
                return [];

            $resultDatas = [];

            if (
                $datas['originType'] == "googleAdsCampaigns"
                || $datas['originType'] == "googleAdsGroups"
                || $datas['originType'] == "googleAdsAds"
            ) {
                $googleAdsElementByGclid = self::getGoogleAdsElementByGclids($firstClicksByIdCart, $datas['originType']);
                foreach ($firstClicksByIdCart as $idCart => $session) {
                    if (!isset($googleAdsElementByGclid[$session['gclid']]))
                        continue;

                    $id = $googleAdsElementByGclid[$session['gclid']]['id'];
                    $name = "(" . $id . ") " . $googleAdsElementByGclid[$session['gclid']]['name'];

                    $resultDatas = self::formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $firstClicksByIdCart, $name, $datas['totalType']);
                }
            }

            if ($datas['originType'] == "sources" || $datas['originType'] == "campaigns" || $datas['originType'] == "mediums") {
                switch ($datas['originType']) {
                    case "sources":
                        $columnName = 'referrer';
                        break;
                    case "campaigns":
                        $columnName = 'utm_campaign';
                        break;
                    case "mediums":
                        $columnName = 'utm_medium';
                        break;
                }
                foreach ($firstClicksByIdCart as $idCart => $session) {
                    if ($session[$columnName] == "") {
                        $name = "Unknow";
                        $id = "Unknow";
                    } else {
                        $name = $session[$columnName];
                        $id = $session[$columnName];
                    }

                    if ($datas['originType'] == "sources" && $session['gclid'] != "") {
                        $name = "Google Ads";
                        $id = "GoogleAds";
                    }

                    $resultDatas = self::formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $firstClicksByIdCart, $name, $datas['totalType']);
                }
                $unattributedOrders = array_diff_key($ordersPlaced, $firstClicksByIdCart);
                foreach ($unattributedOrders as $idCart => $order) {
                    $name = "Unknow";
                    $id = "Unknow";
                    $resultDatas = self::formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $unattributedOrders, $name, $datas['totalType']);
                }
            }

            /* if($datas['originType'] == "sources") {
                $resultDatas = [];
                foreach ($firstClicksByIdCart as $idCart => $session) {
                    if($session['referrer'] == "") {
                        $name = "Unknow";
                        $id = "Unknow";
                    }
                    else {
                        $name = $session['referrer'];
                        $id = $session['referrer'];
                    }                    

                    if($session['gclid'] != "") {
                        $name = "Google Ads";
                        $id = "GoogleAds";
                    }
                    $resultDatas = self::formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $firstClicksByIdCart, $name, $datas['totalType']);
                }
                $unattributedOrders = array_diff_key($ordersPlaced, $firstClicksByIdCart);
                foreach ($unattributedOrders as $idCart => $order) {
                    $name = "Unknow";
                    $id = "Unknow";
                    $resultDatas = self::formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $unattributedOrders, $name, $datas['totalType']);
                }
            } */
        }

        if (isset($_GET['debug']) && $_GET['debug'] == true) {
            ini_set('xdebug.var_display_max_depth', '5');
            ini_set('xdebug.var_display_max_children', '256');
            ini_set('xdebug.var_display_max_data', '1024');

            echo "Orders Placed with idCart as key:";
            var_dump($ordersPlaced);

            echo "resultDatas :";
            var_dump($resultDatas);
        }

        return $resultDatas;
    }

    public static function getFirstClicksByIdCart($datas)
    {
        $dateFrom = $datas['dateFrom'];
        $dateTo = $datas['dateTo'];
        $attributionDelay = $datas['attributionDelay'];
        $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
        $ordersPlaced = $datas['ordersPlaced'];

        $dateFromWithAttributionDelay = date('Y-m-d H:i:s', strtotime($dateFrom . '-' . $attributionDelay . ' hour'));

        $limit = 10000;
        $offset = 0;

        $lastUserOrder = [];
        $firstClicks = [];

        while (true) {
            $sql = "SELECT
                        visiteId,
                        createdAt,
                        userIp,
                        idCart,
                        gclid,
                        referrer,
                        utm_campaign,
                        utm_medium
                    FROM
                    " . _DB_PREFIX_ . "opartstat_sessions opartstat_sessions
                    WHERE 
                        opartstat_sessions.createdAt >= '" . pSQL($dateFromWithAttributionDelay) . "'
                    AND 
                        opartstat_sessions.createdAt <= '" . pSQL($dateTo) . "'
                    AND
                        " . pSQL($shopConstraints) . "
                    ORDER BY
                        opartstat_sessions.createdAt DESC
                    LIMIT $limit OFFSET $offset";

            $sessions = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $datas['useCache']);

            if (!is_array($sessions) || count($sessions) == 0)
                break;

            foreach ($sessions as $session) {
                $userIp = $session['userIp'];
                if (!isset($lastUserOrder[$userIp])) {
                    $lastUserOrder[$userIp] = [];
                }

                if ($session['idCart'] != null && $session['idCart'] != 0) {
                    $idCart = $session['idCart'];
                    if (isset($ordersPlaced[$idCart])) {
                        $maxAttributionDateForThisOrder = date('Y-m-d H:i:s', strtotime($ordersPlaced[$idCart]['date'] . '-' . $attributionDelay . ' hour'));
                        $lastUserOrder[$userIp] = array(
                            'idCart' => $idCart,
                            'orderDate' => $ordersPlaced[$idCart]['date'],
                            'maxAttributionDateForThisOrder' => $maxAttributionDateForThisOrder
                        );
                    }
                }
                if (
                    isset($lastUserOrder[$userIp]) &&
                    $session['createdAt'] >= $lastUserOrder[$userIp]['maxAttributionDateForThisOrder'] &&
                    $session['createdAt'] <= $lastUserOrder[$userIp]['orderDate']
                ) {
                    $firstClicks[$lastUserOrder[$userIp]['idCart']] = $session;
                }
            }
            $offset += $limit;
        }

        return $firstClicks;
    }

    public static function getGoogleAdsElementByGclids($sessions, $googleAdsElement)
    {

        $gclidIn = "";

        foreach ($sessions as $session) {
            if ($session['gclid'] == "")
                continue;

            $gclidIn .= $gclidIn == "" ? "'" . pSQL($session['gclid']) . "'" : ",'" . pSQL($session['gclid']) . "'";
        }

        if ($gclidIn == "")
            return [];

        $gclidIn = "(" . $gclidIn . ")";

        switch ($googleAdsElement) {
            case "googleAdsCampaigns":
                $idColumn = "campaignId";
                $nameColumn = "googleAdsCampaigns.name";
                $joinTable = "googleAdsCampaigns";
                $on = "googleAdsClicks.campaignId = googleAdsCampaigns.id";
                break;
            case "googleAdsGroups":
                $idColumn = "groupId";
                $nameColumn = "googleAdsGroups.name";
                $joinTable = "googleAdsGroups";
                $on = "googleAdsClicks.groupId = googleAdsGroups.id";
                break;
            case "googleAdsAds":
                $idColumn = "adId";
                $nameColumn = "googleAdsAds.name";
                $joinTable = "googleAdsAds";
                $on = "googleAdsClicks.adId = googleAdsAds.id";
                break;
        }

        $sql = "SELECT 
                    " . $idColumn . " as id,
                    gclid,
                    " . $nameColumn . " as name
                FROM 
                    " . _DB_PREFIX_ . "opartstat_googleAdsClicks googleAdsClicks 
                JOIN
                    " . _DB_PREFIX_ . "opartstat_" . $joinTable . " " . $joinTable . "
                ON
                    " . $on . "
                WHERE 
                    gclid IN " . $gclidIn;

        $elements = Db::getInstance()->executeS($sql);

        if (!is_array($elements) || count($elements) == 0)
            return [];

        foreach ($elements as $element)
            $elementsGclid[$element['gclid']] = $element;

        return $elementsGclid;
    }

    public static function formatDatasForGetOriginConversion($resultDatas, $id, $ordersPlaced, $idCart, $sessions, $name, $totalType)
    {
        switch ($totalType) {
            case "orderTotal":
                $total = (float)$ordersPlaced[$idCart]['total'];
                break;
            case "orderCount":
                $total = 1;
                break;
        }

        $userIp = null;
        $lastVisitDate = null;

        if (isset($sessions[$idCart]['userIp']))
            $userIp = $sessions[$idCart]['userIp'];


        if (isset($sessions[$idCart]['createdAt']))
            $lastVisitDate = $sessions[$idCart]['createdAt'];

        if (isset($resultDatas[$id])) {
            $resultDatas[$id]['total'] = $resultDatas[$id]['total'] + $total;
            $resultDatas[$id]['orders'][] =
                array(
                    'idCart' => $idCart,
                    'idOrder' => $ordersPlaced[$idCart]['idOrder'],
                    'total' => $ordersPlaced[$idCart]['total'],
                    'orderDate' => $ordersPlaced[$idCart]['date'],
                    'userIp' => $userIp,
                    'lastVisitDate' => $lastVisitDate
                );
        } else {
            $resultDatas[$id] = array(
                'id' => $id,
                'name' => $name,
                'total' => $total,
                'orders' => array(
                    array(
                        'idCart' => $idCart,
                        'idOrder' => $ordersPlaced[$idCart]['idOrder'],
                        'total' => $ordersPlaced[$idCart]['total'],
                        'orderDate' => $ordersPlaced[$idCart]['date'],
                        'userIp' => $userIp,
                        'lastVisitDate' => $lastVisitDate
                    )
                )
            );
        }

        return $resultDatas;
    }

    public static function getDayOfTheWeekTranslatedArray()
    {
        $idLang = Context::getContext()->language->id;
        $language = new Language($idLang);
        $isoCode = $language->iso_code;

        switch ($isoCode) {
            case 'fr':
                $daysArray = array(
                    1 => 'Dimanche',
                    2 => 'Lundi',
                    3 => 'Mardi',
                    4 => 'Mercredi',
                    5 => 'Jeudi',
                    6 => 'Vendredi',
                    7 => 'Samedi'
                );
                break;
            case 'en':
            case 'us':
                $daysArray = array(
                    1 => 'Sunday',
                    2 => 'Monday',
                    3 => 'Tuesday',
                    4 => 'Wednesday',
                    5 => 'Thursday',
                    6 => 'Friday',
                    7 => 'Saturday'
                );
                break;
            case 'es':
                $daysArray = array(
                    1 => 'Domingo',
                    2 => 'Lunes',
                    3 => 'Martes',
                    4 => 'Miércoles',
                    5 => 'Jueves',
                    6 => 'Viernes',
                    7 => 'Sábado'
                );
                break;
            case 'it':
                $daysArray = array(
                    1 => 'Domenica',
                    2 => 'Lunedì',
                    3 => 'Martedì',
                    4 => 'Mercoledì',
                    5 => 'Giovedì',
                    6 => 'Venerdì',
                    7 => 'Sabato'
                );
                break;
            case 'de':
                $daysArray = array(
                    1 => 'Sonntag',
                    2 => 'Montag',
                    3 => 'Dienstag',
                    4 => 'Mittwoch',
                    5 => 'Donnerstag',
                    6 => 'Freitag',
                    7 => 'Samstag'
                );
                break;
        }
        return $daysArray;
    }

    static public function stringToBoolean($str)
    {
        if ($str === "true") {
            return true;
        } elseif ($str === "false") {
            return false;
        } else {
            return null; // ou une autre valeur par défaut selon le contexte
        }
    }

    /*     static public function getTokenFromHeader() {
        $token = false;
        if(isset($_SERVER['Authorization'])){
            $token = trim($_SERVER['Authorization']);
        }elseif(isset($_SERVER['HTTP_AUTHORIZATION'])){
            $token = trim($_SERVER['HTTP_AUTHORIZATION']);
        }elseif(function_exists('apache_request_headers')){
            $requestHeaders = apache_request_headers();
            if(isset($requestHeaders['Authorization'])){
                $token = trim($requestHeaders['Authorization']);
            }
        }
        return $token;
    } */

    static public function getTokenFromSaas()
    {
        $token = false;
        if (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
            $token = trim(str_replace('Bearer ', '', $_SERVER['HTTP_X_AUTHORIZATION']));
        }
        return $token;
    }

    static public function getGoogleAdsElementsCostsByElementId($dateFrom, $dateTo, $googleAdsElementsWithOrders, $googleAdsElement)
    {
        $db = Db::getInstance();

        switch ($googleAdsElement) {
            case "googleAdsCampaigns":
                $idColumn = "campaignId";
                $joinTable = "googleAdsCampaigns";
                $on = "googleAdsDailyDatas.campaignId = googleAdsCampaigns.id";
                break;
            case "googleAdsGroups":
                $idColumn = "groupId";
                $joinTable = "googleAdsGroups";
                $on = "googleAdsDailyDatas.groupId = googleAdsGroups.id";
                break;
            case "googleAdsAds":
                $idColumn = "adId";
                $joinTable = "googleAdsAds";
                $on = "googleAdsDailyDatas.adId = googleAdsAds.id";
                break;
        }

        $in = "";
        foreach ($googleAdsElementsWithOrders as $googleAdsElementWithOrders) {
            $in .=  $in == "" ? "'" . pSQL($googleAdsElementWithOrders['id']) . "'" : ",'" . pSQL($googleAdsElementWithOrders['id']) . "'";
        }

        $sql = "SELECT  
            " . $idColumn . " as id,
            SUM(costMicros/1000000) as totalCosts 
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas
        JOIN
            " . _DB_PREFIX_ . "opartstat_" . $joinTable . " " . $joinTable . "
        ON
            " . $on . "
        WHERE 
            createdAt >= '" . pSQL($dateFrom) . "' 
        AND 
            createdAt <= '" . pSQL($dateTo) . "'
        AND
            " . $joinTable . ".id IN (" . $in . ")
        GROUP BY 
            " . $joinTable . ".id
        ";

        $googleAdsElementsCosts = $db->executeS($sql);

        foreach ($googleAdsElementsCosts as $googleAdsElementCosts) {
            $googleAdsElementsCostsByElementId[$googleAdsElementCosts['id']] = $googleAdsElementCosts['totalCosts'];
        }

        return $googleAdsElementsCostsByElementId;
    }

    /*     public static function getShopGroupIdSharingStockAndShopIdNotSharingStock()
    {
        $shops = self::getShops();

        $sql = "SELECT 
            s.id_shop,
            sg.id_shop_group,
            sg.share_stock
        FROM 
            `" . _DB_PREFIX_ . "shop` s
        JOIN 
            `" . _DB_PREFIX_ . "shop_group` sg ON s.id_shop_group = sg.id_shop_group
        WHERE 
            s.id_shop IN (" . implode(',', array_map('intval', $shops)) . ")
        ORDER BY 
            sg.share_stock DESC, sg.id_shop_group, s.id_shop";

        $results = Db::getInstance()->executeS($sql);

        $sharedStockGroups = [];
        $nonSharedStockShops = [];

        foreach ($results as $row) {
            if ($row['share_stock'] == 1) {
                if (!in_array($row['id_shop_group'], $sharedStockGroups))
                $sharedStockGroups[]= $row['id_shop_group'];
            } else {
                $nonSharedStockShops[] = $row['id_shop'];
            }
        }
        return [
            'shared_stock_groups' => $sharedStockGroups,
            'non_shared_stock_shops' => $nonSharedStockShops
        ];
    } */

    public static function getShopGroupIdSharingStockAndShopIdNotSharingStock()
    {
        $shops = self::getShops();

        $sql = "SELECT 
        s.id_shop,
        sg.id_shop_group,
        sg.share_stock
    FROM 
        `" . _DB_PREFIX_ . "shop` s
    JOIN 
        `" . _DB_PREFIX_ . "shop_group` sg ON s.id_shop_group = sg.id_shop_group
    WHERE 
        s.id_shop IN (" . implode(',', array_map('intval', $shops)) . ")
    ORDER BY 
        sg.share_stock DESC, sg.id_shop_group, s.id_shop";

        $results = Db::getInstance()->executeS($sql);

        $sharedStockGroups = [];
        $nonSharedStockShops = [];
        $firstShopIds = [];

        foreach ($results as $row) {
            if ($row['share_stock'] == 1) {
                if (!in_array($row['id_shop_group'], $sharedStockGroups)) {
                    $sharedStockGroups[] = $row['id_shop_group'];
                    // Stocker le premier id_shop pour chaque groupe partageant le stock
                    $firstShopIds[$row['id_shop_group']] = $row['id_shop'];
                }
            } else {
                $nonSharedStockShops[] = $row['id_shop'];
                $firstShopIds[$row['id_shop_group']] = $row['id_shop'];
            }
        }

        return [
            'shared_stock_groups' => $sharedStockGroups,
            'non_shared_stock_shops' => $nonSharedStockShops,
            'first_shop_ids' => $firstShopIds
        ];
    }

    public static function getAssociativeArrayFromQuery($sql, $useCache)
    {
        $yesterday = new DateTime();
        $yesterday->modify('-1 day');
        $yesterday->setTime(23, 59, 59);
        $yesterdayFormatted = $yesterday->format('Y-m-d H:i:s');

        $result = self::getValueFromCacheIfExists($sql, $yesterdayFormatted, $useCache);
        $associativeArray = [];

        if (is_array($result) && !empty($result)) {
            foreach ($result as $row) {
                $keys = array_keys($row);
                $associativeArray[$row[$keys[0]]] = $row[$keys[1]];
            }
        }

        return $associativeArray;
    }


    public static function useSeparateDb()
    {
        return (int)Configuration::get('OPARTSTAT_USE_SEPARATE_DB') === 1;
    }

    public static function getSessionsTableName()
    {
        return self::useSeparateDb() ? 'opartstat_sessions' : _DB_PREFIX_ . 'opartstat_sessions';
    }

    public static function executeSessionsSelect($sql)
    {
        if (!self::useSeparateDb()) {
            return Db::getInstance((bool)_PS_USE_SQL_SLAVE_)->executeS($sql);
        }

        $dbHost = (string) Configuration::get('OPARTSTAT_DB_HOST');
        $dbPort = (string) (Configuration::get('OPARTSTAT_DB_PORT') ?: '3306');
        $dbName = (string) Configuration::get('OPARTSTAT_DB_NAME');
        $dbUser = (string) Configuration::get('OPARTSTAT_DB_USER');
        $dbPass = (string) Configuration::get('OPARTSTAT_DB_PASS');

        $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';

        $pdo = new PDO($dsn, $dbUser, $dbPass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ));

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

}
