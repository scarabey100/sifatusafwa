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

include(dirname(__FILE__) . '/../../classes/opartSession.php');
include(dirname(__FILE__) . '/../../opartstat.php');

class AdminOpartStatGlobalController extends ModuleAdminController
{

  public $translationJsObject = [];
  public $idEmployee = null;
  public $premiumIsActive = "0";

  public function __construct()
  {
    // Set variables
    $this->context = Context::getContext();
    $this->bootstrap = true;
    parent::__construct();
    $context = Context::getContext();
    $this->idEmployee = $context->employee->id;
    $this->premiumIsActive = Configuration::get('OPARTSTAT_PREMIUM_IS_ACTIVE');
  }

  public function InitContent()
  {
    parent::initContent();


    $tz = @new DateTimeZone(Configuration::get('PS_TIMEZONE') ?: 'Europe/Paris');
    $now = new DateTime('now', $tz);
    $currentYearMonth = $now->format('Y-m');

    $lastPurgedYearMonth = Configuration::get('OPARTSTAT_CACHE_LAST_MONTH_PURGED');

    if ($lastPurgedYearMonth !== $currentYearMonth) {
        OpartStatTools::purgeCacheFiles(true);
        Configuration::updateValue('OPARTSTAT_CACHE_LAST_MONTH_PURGED', $currentYearMonth);
    }


    if (Tools::isSubmit('deleteReport'))
      $this->deleteReport();

    if (Tools::isSubmit('addNewReport'))
      $this->addNewReport();

    if (Tools::isSubmit('duplicateReport'))
      $this->duplicateReport();

    OpartStatTools::purgeCacheFiles();

    //$topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);

    $topMenuConfig = $this->getTopMenuConfig();

    $reportName = (Tools::getIsset('reportName')) ? Tools::getValue('reportName') : $topMenuConfig['reports'][1]['name'];


    if (Tools::isSubmit('confirmAddNewReport') && Tools::getValue('confirmAddNewReport') == true)
      $this->confirmations[] = $this->module->l('New report added','adminopartstatglobal');

    if (!Validate::isConfigName($reportName)) {
      $this->errors[] = $this->module->l('The report name is not valid','adminopartstatglobal');
      return false;
    }

    $idLang = $this->context->language->id;

    if (!OpartStatTools::isValidDem()) {
      $this->context->smarty->assign(array(
        'opartAfId' => OpartRemoteTools::$opartAfId
      ));
      $output =  $this->context->smarty->fetch(
        _PS_MODULE_DIR_ . 'opartstat/views/templates/admin/purchaseModule.tpl'
      );

      $this->context->smarty->assign(array(
        'content' => $this->content . $output
      ));
      return;
    }

    /* $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/'.$this->idEmployee); */
    foreach ($topMenuConfig['reports'] as $report) {
      $reportList[] = [
        'displayTitle' => (isset($report['title'][$idLang])) ? $report['title'][$idLang] : $report['title'][2],
        'name' => $report['name']
      ];
    }

    $this->context->smarty->assign(array(
      'reportList' => $reportList
    ));

    $reportConfig = $this->getReportConfig($reportName);

    if ($reportConfig == false) {
      $this->errors[] =  sprintf($this->module->l('Unable to find the "%s" configuration file'), $reportName,'adminopartstatglobal');
      return false;
    }

    //$this->populateTranslationArray();
    $reportType = $reportConfig['type'];
    $liveTime = Configuration::get('OPARTSTAT_LIVE_TIME');
    $pageTitle = (isset($reportConfig['title'][$idLang])) ? $reportConfig['title'][$idLang] : $reportConfig['title'][2];

    if ($reportType == "live")
      $pageTitle = sprintf($this->module->l('%s report (last %d minutes)'), $pageTitle, $liveTime,'adminopartstatglobal');
    else
      $pageTitle = sprintf($this->module->l('%s'), $pageTitle,'adminopartstatglobal');

    $adminControllerLink = 'index.php?controller=AdminOpartStatGlobal&token=' . Tools::getAdminTokenLite('AdminOpartStatGlobal');
    $pdfGeneratorControllerLink = 'index.php?controller=AdminOpartStatExportToPdf&token=' . Tools::getAdminTokenLite('AdminOpartStatExportToPdf');

    /* Media::addJsDef([
      'ajaxUrl' => $adminControllerLink . "&ajax=1"
    ]); */

    $smartyDateFormat = (OpartStatTools::getDateFormat() == 'DD/MM/YYYY') ? '%d/%m/%Y' : '%m/%d/%Y';
    $jsDateFormat = (OpartStatTools::getDateFormat() == 'DD/MM/YYYY') ? 'dd/mm/yy' : 'mm/dd/yy';

    $lastStatDate = opartSession::getLastStatDate();

    $reportSettings = $this->getReportSettings($reportName);

    $filtersDatas = $this->getFiltersDatas($reportSettings);

    Media::addJsDef([
      'filtersDatas' => $filtersDatas,
      'ajaxUrl' => $adminControllerLink . "&ajax=1",
      'pdfGeneratorAjaxUrl' => $pdfGeneratorControllerLink . "&ajax=1",
      'allTranslatedTxt' => $this->module->l('Select all','adminopartstatglobal')
    ]);

    $demTimeLeft = OpartStatTools::getDemTimeLeft();

    $adminOpartStatSettingsSubscriptionLink = 'index.php?controller=AdminOpartStatSettingsSubscription&token=' . Tools::getAdminTokenLite('AdminOpartStatSettingsSubscription');

    $this->context->smarty->assign(array(
      //'ajaxUrl' => $ajaxUrl,
      'defaultPreselectedPeriodFirst' => $reportSettings['initial']['date'],
      'defaultPreselectedPeriodCompare' => $reportSettings['compare']['date'],
      'reportSettings' => $reportSettings,
      'smartyDateFormat' => $smartyDateFormat,
      'jsDateFormat' => $jsDateFormat,
      'lastStatDate' => $lastStatDate,
      'adminControllerLink' => $adminControllerLink,
      'title' => $pageTitle,
      'reportName' => $reportName,
      'reportType' => $reportType,
      'opartStatDir' => _MODULE_DIR_ . "opartstat/",
      'liveTime' => $liveTime,
      'demTimeLeft' => $demTimeLeft,
      'useRemote' => OpartRemoteTools::$useRemote,
      'opartAfId' => OpartRemoteTools::$opartAfId,
      'adminOpartStatSettingsSubscriptionLink' => $adminOpartStatSettingsSubscriptionLink,
      'displayShareReportBtn' => $this->isReportOwner($reportName),
      'isAuthorizedToEdit' => $this->isAuthorizedToEdit($reportName),
      'premiumIsActive' => $this->premiumIsActive
    ));

    if (Tools::getIsset('selectedItemsId')) {
      $selectedItemsId = (int)Tools::getValue('selectedItemsId');

      //change sql regard to the type of item
      $sql =
        'SELECT  product.`id_product`,  product.`reference`,  product.`price`, product_lang.`name`
        FROM `' . _DB_PREFIX_ . 'product` product
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` product_lang ON (
            product_lang.id_product = product.id_product
            AND product_lang.id_lang = ' . (int)$this->context->language->id . '
            ' . Shop::addSqlRestrictionOnLang('product_lang') . '
        )
        ' . Shop::addSqlAssociation('product', 'product') . '
        WHERE 
           product.`id_product` = ' . (int)$selectedItemsId;

      $selectedItems = Db::getInstance()->executeS($sql);
      if (count($selectedItems) > 0)
        $this->context->smarty->assign(array('selectedItem' => $selectedItems[0]));
    }

    //ps 1.6 compatibility
    //if (_PS_VERSION_ < "1.7.7.0") {
    if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
      $currency = Tools::setCurrency($this->context->cookie);
      $this->context->smarty->assign(array(
        'currencySign'        => $currency->sign,
        'currencyFormat'      => $currency->format,
        'currencyBlank'       => $currency->blank,
        'ps_version'          => "1.6"
      ));
    } else {
      $this->context->smarty->assign(array(
        'ps_version'          => "1.7"
      ));
    }

  $opartStatConfigUrl = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . urlencode('opartstat');
    $opartStatConfigHtmlLink = '<a href="' . $opartStatConfigUrl . '">' . $this->module->l('the module configuration','adminopartstatglobal') . '</a>';

    if (Configuration::get('OPARTSTAT_STATUS_VALID_ORDER') == '') {
      $msg = $this->module->l('You have to go to % and choose the statuses of the orders that should be taken into account in the revenues calculation','adminopartstatglobal');
      $msg =  sprintf($msg, $opartStatConfigHtmlLink);
      $this->context->smarty->assign('error_raw', $msg);
    }
    if (Configuration::get('OPARTSTAT_STATUS_REFUNDED_ORDER') == '') {
      $msg = $this->module->l('You have to go to %s and choose the statuses of the orders that should be taken into account in the refund calculation','adminopartstatglobal');
      $msg =  sprintf($msg, $opartStatConfigHtmlLink);
      $this->context->smarty->assign('error_raw', $msg);
    }

    


    //if (_PS_VERSION_ < '1.7.5.0') {
    if (version_compare(_PS_VERSION_, '1.7.5.0', '<')) {
      $url = $this->context->shop->getBaseURL(true) . '/translations/cldr/datas/main/fr-FR/numbers.json';
      $headers = get_headers($url);
      if (strpos($headers[0], '403') !== false)
        $this->errors[] = $this->module->l('Some metrics cannot be displayed because currency values formatting files are not accessible. You have to edit the .htacess file in the translations/cldr folder or contact us to do so.','adminopartstatglobal');
      //solution to fix this problem is here : https://www.store-opart.fr/content/19-bug-translations-cldr-datas-403-forbidden
    }

    $module = Module::getInstanceByName('opartstat');

    $humanLastStatDate = OpartStatTools::mysqlToHumanDate($lastStatDate, $jsDateFormat);
    $inactivCustomerDays = Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS');

    $this->context->smarty->assign(array(
      'humanLastStatDate'          => $humanLastStatDate,
      'inactivCustomerDays'          => $inactivCustomerDays,
    ));

    $classicMetrics = $this->getAllMetrics($humanLastStatDate, $inactivCustomerDays, 'default');
    $partnerModulesMetrics = $this->getAllMetrics($humanLastStatDate, $inactivCustomerDays, 'partnersModules');
    $customMetrics = $this->getAllMetrics($humanLastStatDate, $inactivCustomerDays, 'customs');
    $allMetrics = array_merge($classicMetrics, $partnerModulesMetrics, $customMetrics);

    if ($reportConfig == false)
      $this->errors[] =  $this->module->l('Unable to read the config file','adminopartstatglobal');

    $reportContent = [];
    $jsFileToAdd = [];

    $metrics = $reportConfig['metrics'];

    if (Configuration::get('OPARTSTAT_ACTIVE_DEBUG_MODE')) {
      array_unshift($metrics, array("name" => "debug", "position" => 0));
    }

    $allActiveMetrics = [];

    foreach ($metrics as $position => $askedMetric) {
      $r = OpartStatTools::getJsonConfig($askedMetric['name'], 'metrics', $allMetrics[$askedMetric['name']]['dir']);
      $metricTitle = $allMetrics[$askedMetric['name']]['title'];
      $metricHelp = $allMetrics[$askedMetric['name']]['help'];
      //$listLabels = array_key_exists('labels', $allMetrics[$askedMetric['name']]) ? $allMetrics[$askedMetric['name']]['labels'] : '';      
      $listCols = array_key_exists('listCols', $allMetrics[$askedMetric['name']]) ? $allMetrics[$askedMetric['name']]['listCols'] : '';

      $selectedPeriod = (!empty($askedMetric['selectedPeriod'])) ? $askedMetric['selectedPeriod'] : 'perMonth';

      //generate InitContent
      $tempoContext = Context::getContext();
      $tempoContext->smarty->assign(array(
        'metricName' => $r['name'],
        'metricitle' => $metricTitle,
        'selectedPeriod' => $selectedPeriod,
        'cols' => $r['cols'],
        'rows' => $r['rows'],
        'dir' => $r['dir'],
        'useLink' => (!array_key_exists('useLink', $r['template'])) ? false : $r['template']['useLink'],
        'help' => $metricHelp,
        //'listLabels' => $listLabels,
        'listCols' => $listCols,
        'position' => $position
      ));

      $tplFileName = $r['template']['name'];
      $tplDir = ($r['template']['isCustom'] == true) ? 'config/metrics/' . $allMetrics[$askedMetric['name']]['dir'] : 'views/templates/admin/metrics';
      $tplFileLocation = _PS_MODULE_DIR_ . $this->module->name . '/' . $tplDir . '/' . $tplFileName . '.tpl';
      $tempoContent = $tempoContext->smarty->fetch($tplFileLocation);

      $reportContent[] = array(
        'name' => $r['name'],
        'title' => $metricTitle,
        'position' => $askedMetric['position'],
        'callBackJsfunction' => $r['callBackJsfunction']['name'][0],
        'dir' => $r['dir'],
        'ajaxCallBack' => $r['ajaxCallBack']['name'],
        'content' =>  $tempoContent
      );

      foreach ($r['callBackJsfunction']['name'] as $jsFctName) {
        $jsFileName = $jsFctName;
        $jsDir = ($r['callBackJsfunction']['isCustom'] == true) ? 'config/metrics/' . $allMetrics[$askedMetric['name']]['dir'] : 'views/js/callback';
        $jsFileLocation = _MODULE_DIR_ . $this->module->name . '/' . $jsDir . '/' . $jsFileName . '.js';
        if (!in_array($jsFileLocation, $jsFileToAdd))
          $jsFileToAdd[] = $jsFileLocation;
      }

      //create array with all metric (active or not)
      $allActiveMetrics[] = array(
        'name' => $r['name'],
        'title' => $metricTitle,
        'position' => $askedMetric['position'],
        'help' => $metricHelp
      );
    }

    foreach ($allMetrics as $r) {
      if ($reportType != "product" && $r['type'] == 'product') {
        $allMetrics[$r['name']]['active'] = "disabled";
      }

      if ($reportType != "category" && $r['type'] == 'category') {
        $allMetrics[$r['name']]['active'] = "disabled";
      }

      if ($r['type'] == 'partnerModule' && !OpartStatTools::moduleIsLinked($r['subDirName']))
        $allMetrics[$r['name']]['active'] = "disabled";

      if ($r['type'] == 'googleAds' && $this->premiumIsActive != '1') {
        $allMetrics[$r['name']]['addCssClass'] = "needOpartStatPremium";
        //$allMetrics[$r['name']]['active'] = "disabled";
        //unset($allMetrics[$r['name']]);
      }
    }

    $adminOpartStatSubscriptionSellsPageUrl = $this->context->link->getAdminLink('AdminOpartStatSubscriptionSellsPage', true);
    $this->context->smarty->assign('adminOpartStatSubscriptionSellsPageUrl', $adminOpartStatSubscriptionSellsPageUrl);
    /* if ($this->useSaas != 1) {
      $adminOpartStatSubscriptionSellsPageUrl = $this->context->link->getAdminLink('AdminOpartStatSubscriptionSellsPage', true);
      $this->context->smarty->assign('adminOpartStatSubscriptionSellsPageUrl', $adminOpartStatSubscriptionSellsPageUrl);
    } */

    foreach ($allActiveMetrics as $r)
      $allMetrics[$r['name']]['active'] = "yes";

    $allMetricsSortByTitle = $allMetrics;
    usort($allMetricsSortByTitle, function ($a, $b) {
      return strcmp($a['title'], $b['title']);
    });

    $allMetricsWithCat = [];
    foreach ($allMetricsSortByTitle as $metric)
      $allMetricsWithCat[$metric['idCat']][] = $metric;

    ksort($allMetricsWithCat);

    $this->context->smarty->assign(array(
      'allMetrics' => $allMetricsWithCat,
      'categoriesName' => $this->getMetricCategoriesName()
    ));

    //add needed $jsFile
    foreach ($jsFileToAdd as $jsFile)
      $this->context->controller->addJS($jsFile);

    //sort by position
    $reportContent = OpartStatTools::sortMultipleArray($reportContent, 'position', false);
    $allActiveMetrics = OpartStatTools::sortMultipleArray($allActiveMetrics, 'position', false);

    $currentLocaleIsoCode = $this->context->language->iso_code;
    if ($currentLocaleIsoCode == 'gb')
      $currentLocaleIsoCode = 'en';

    $excludeShipping = Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING');
    $excludeFreeOrder = Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER');

    
    $discoverOpartModuleLink = ($currentLocaleIsoCode == 'fr') ? 
        'https://prestashop.pxf.io/y21BPD' :
        'https://prestashop.pxf.io/qz0V1Y';

    $this->context->smarty->assign(array(
      'currentLocaleIsoCode' => $currentLocaleIsoCode,
      'reportContent' => $reportContent,
      'allActiveMetrics' => $allActiveMetrics,
      'excludeShipping' => $excludeShipping,
      'excludeFreeOrder' => $excludeFreeOrder,
      'translationJsArray' => $this->translationJsObject,
      'discoverOpartModuleLink' => $discoverOpartModuleLink,
    ));

    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . 'opartstat/views/templates/admin/global.tpl'
    );

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
  }

  public function getFiltersDatas($reportSettings)
  {
    $filtersDatas = [];
    if (
      array_key_exists('filters', $reportSettings['initial'])
      && is_array($reportSettings['initial']['filters'])
      && count($reportSettings['initial']['filters']) > 0
    ) {
      $filtersDatas = array_merge($filtersDatas, $this->getFiltersValue($reportSettings, 'initial'));
    }
    if (
      array_key_exists('filters', $reportSettings['compare'])
      && is_array($reportSettings['compare']['filters'])
      && count($reportSettings['compare']['filters']) > 0
    ) {
      $filtersDatas = array_merge($filtersDatas, $this->getFiltersValue($reportSettings, 'compare'));
    }
    return $filtersDatas;
  }

  public function getFiltersValue($reportSettings, $type)
  {
    $filtersDatas = [];
    foreach ($reportSettings[$type]['filters'] as $excludeInclude => $array) {
      foreach ($array as $filterName => $datas) {
        if ($filterName == "device") {
          $filtersDatas[$type][$excludeInclude][$filterName][] = $datas['values'];
        } else if (is_string($datas['values']) && strpos($datas['values'], '%') !== false) {
          $filtersDatas[$type][$excludeInclude][$filterName][] = $datas['values'];
        } else {
          $filtersDatas[$type][$excludeInclude][$filterName] = $this->searchItemDatas($filterName, $datas['values']);
          if ($filterName == 'categories')
            $filtersDatas[$type][$excludeInclude][$filterName]['getAllChildren'] = $datas['getAllChildren'];
          if ($filterName == 'attributes')
            $filtersDatas[$type][$excludeInclude][$filterName]['useAnd'] = $datas['useAnd'];
        }
      }
    }
    return $filtersDatas;
  }

  public function setMedia($isNewTheme = false)
  {
    $v = $this->module->version;
    $viewUrl = $this->context->shop->getBaseURL(true) . 'modules/' . $this->module->name . '/views/';
    $this->addCSS($viewUrl.'css/admin.css?v='.$v);
    $this->addCSS($viewUrl.'css/loader.css?v='.$v);

    if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
      $this->addJS("https://code.jquery.com/jquery-3.5.1.min.js");
      $this->addJS("https://cdnjs.cloudflare.com/ajax/libs/jquery-browser/0.1.0/jquery.browser.min.js");
      $this->addJS($viewUrl.'js/before-1770.js?v='.$v);
    } else {
      $this->addJS($viewUrl.'js/since-1770.js?v='.$v);
    }

    if (version_compare(_PS_VERSION_, '1.7', '<')) {
      $this->addCSS($viewUrl.'css/16.css?v='.$v);
    }

    parent::setMedia($isNewTheme);

    $this->addJqueryUI('ui.datepicker');
    $this->addJqueryUI('ui.sortable');
    $this->addJqueryPlugin(array('autocomplete'));

    $this->addJS($viewUrl.'js/admin.js?v='.$v);

    if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
      $this->removeJS(_PS_ROOT_DIR_ . '/js/jquery/jquery-1.11.0.min.js');
      $this->removeJS(_PS_ROOT_DIR_ . '/js/jquery/jquery-migrate-1.2.1.min.js');
    }
  }

  //humanLastDate and inactivCustomerDays are used in php include files, so don't delete them
  public function getAllMetrics($humanLastStatDate, $inactiVCustomerDays, $dir)
  {
    $directory = _PS_MODULE_DIR_ . 'opartstat/config/metrics/' . $dir;
    /* if ($isCustom)
      $directory = $directory . 'customs/'; */

    $subDirs = scandir($directory);

    $allMetrics = [];
    foreach ($subDirs as $subDir) {
      $dirInfos = pathinfo($subDir);
      if ($dirInfos['filename'] == 'index' || $dirInfos['basename'] == '.' || $dirInfos['basename'] == '..')
        continue;

      $subDirName = $dirInfos['filename'];
      $subDirPath =  $directory . '/' . $subDirName;

      if (!is_dir($subDirPath))
        continue;

      //var_dump($dirInfos);
      $files = scandir($subDirPath);
      foreach ($files as $file) {
        $fileInfos = pathinfo($file);

        if (
          $fileInfos['filename'] !== 'index' &&
          $fileInfos['filename'] !== 'install' &&
          $fileInfos['extension'] === 'php' &&
          strpos(
            $fileInfos['filename'],
            '_wording'
          )
        ) {
          /* $labels = [];
          $cols = []; */
          include_once($subDirPath . '/' . $file);
          $keys = array_keys($helpText);
          $metricName = end($keys);
          $allMetrics[$metricName]['name'] = $metricName;
          $allMetrics[$metricName]['type'] = (array_key_exists($metricName, $metricType)) ? $metricType[$metricName] : '';
          $allMetrics[$metricName]['title'] = $this->trans($metricTitle, [], 'Modules.Opartstat.' . str_replace(".php", "", $file));
          $allMetrics[$metricName]['active'] = "no";
          $allMetrics[$metricName]['subDirName'] = $subDirName;
          $allMetrics[$metricName]['dir'] = $dir . '/' . $subDirName;
          $allMetrics[$metricName]['help'] = $helpText[$metricName];

          $allMetrics[$metricName]['idCat'] = isset($idCat[$metricName]) ? $idCat[$metricName] : 99;

          if (isset($listCols)) {
            foreach ($listCols as $key => $listCol) {
              if (isset($listCol['label'])) {
                $listCols[$key]['label'] = $this->trans($listCol['label'], [], 'Modules.Opartstat.' . str_replace(".php", "", $file));
              }
            }
            $allMetrics[$metricName]['listCols'] = $listCols;
          }



          /* if (isset($labels)) {
            foreach ($labels as $key => $label) {
              $allMetrics[$metricName]['labels'][$key] = $this->trans($labels[$key], [], 'Modules.Opartstat.' . str_replace(".php", "", $file));
            }
          }
          
          $allMetrics[$metricName]['cols'] = $cols; */
        }
      }
    }

    return $allMetrics;
  }

  public function trans($id, array $parameters = [], $domain = null, $locale = null)
  {
    //if (_PS_VERSION_ >= '1.7.7.0') {
    if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
      return parent::trans($id, $parameters, $domain);
    } else {
      $domain = str_replace("Modules.Opartstat.", "", $domain);
      $opartStatModule = new OpartStat;
      return Translate::getModuleTranslation($opartStatModule, $id, $domain, $parameters, false, null, true, false);
    }
  }

  public function ajaxProcessSaveConfig()
  {
    $metricsToAdd = (Tools::getValue('metrics') == false) ? [] : Tools::getValue('metrics');

    $removeAllOldMetric = Tools::getValue('removeAllOldMetric');

    $reportName = Tools::getValue('reportName');

    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);

    if (!$this->isAuthorizedToEdit($reportName, $datas)) {
      $this->errors[] = $this->module->l('You\'r not allowed to edit this report','adminopartstatglobal');
      return false;
    }
    /*
    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);
    $newReport = $this->getNewReportName($topMenuConfig);

    $idLang = $this->context->language->id;

    //$newReportConfig = OpartStatTools::getJsonConfig($reportToCopyName, 'reports', 'customs/' . $this->idEmployee);
    $newReportConfig = OpartStatTools::getJsonConfig($datas['reportName'], 'reports', 'customs/' . $datas['ownerUserId']); */

    if (!Validate::isConfigName($reportName))
      die('Report name is not valid');

    //$reportConfig = OpartStatTools::getJsonConfig($reportName, 'reports', 'customs/' . $this->idEmployee);
    $reportConfig = OpartStatTools::getJsonConfig($datas['reportName'], 'reports', 'customs/' . $datas['ownerUserId']);

    if ($reportConfig == false)
      die('Unable to read the config file');

    $newMetricConfig = [];
    $lastPos = 0;
    $alreadyAddedMetric = [];
    $oldMetrics = [];
    /* if($removeAllOldMetric == 'false') { */
    foreach ($reportConfig['metrics'] as $key => $r) {
      $oldMetrics[$r['name']] = $r;
    }

    $lastPos = 0;
    if ($removeAllOldMetric == 'false') {
      foreach ($oldMetrics as $metricName => $oldMetric) {
        $add = [];
        if (in_array($metricName, $metricsToAdd)) {
          $lastPos++;
          $add['name'] = $metricName;
          $add['position'] = $lastPos;
          if (isset($oldMetric['selectedPeriod']))
            $add['selectedPeriod'] = $oldMetric['selectedPeriod'];

          $newMetricConfig[] = $add;
          $alreadyAddedMetric[] = $metricName;
        }
      }
    }

    foreach ($metricsToAdd as $metricName) {
      $add = [];
      if (!in_array($metricName, $alreadyAddedMetric)) {
        $lastPos++;
        $add['name'] = $metricName;
        $add['position'] = $lastPos;
        if (isset($oldMetrics[$metricName]['selectedPeriod']))
          $add['selectedPeriod'] = $oldMetrics[$metricName]['selectedPeriod'];

        $newMetricConfig[] = $add;
      }
    }

    $reportConfig['metrics'] = $newMetricConfig;

    //if (OpartStatTools::setReportConfig($reportConfig, $reportName)) {
    if (OpartStatTools::setReportConfig($reportConfig, $datas['reportName'], $datas['ownerUserId'])) {
      $result = true;
    } else {
      $result = false;
    }
    echo json_encode($result);
    die();
  }


  public function ajaxProcessSaveMetricSelectedPeriod()
  {
    $metric = Tools::getValue('metric');
    $reportName = Tools::getValue('reportName');
    if (!Validate::isConfigName($reportName))
      die('Report name is not valid');

    $reportConfig = OpartStatTools::getJsonConfig($reportName, 'reports', 'customs/' . $this->idEmployee);
    foreach ($reportConfig['metrics'] as $key => $oldMetric) {
      if ($oldMetric['name'] == $metric['name']) {
        $reportConfig['metrics'][$key]['selectedPeriod'] = $metric['selectedPeriod'];
      }
    }

    if (OpartStatTools::setReportConfig($reportConfig, $reportName)) {
      $result = $metric['name'];
    } else {
      $result = false;
    }
    echo json_encode($result);
    die();
  }

  public function ajaxProcessLoadStat()
  {
    $result = $this->getStatsDatas();
    die(json_encode($result));
  }


  public function ajaxProcessGetCsv()
  {
    $result = $this->getStatsDatas();
    $file = _PS_MODULE_DIR_ . 'opartstat/csvTemp/export.csv';
    if (!file_exists($file))
      touch($file);
    if (count($result['initial']['value']) > 0) {
      $fp = fopen($file, 'w');
      fputcsv($fp, array_keys(reset($result['initial']['value'])));
      foreach ($result['initial']['value'] as $fields) {
        fputcsv($fp, $fields);
      }
    }

    die(json_encode($result));
  }

  private function getStatsDatas()
  {
    $metricName = Tools::getValue('metricName');
    if ($metricName == null || $metricName == '')
      die('no stat to load');

    $dir = Tools::getValue('dir');

    $ajaxCallBack = Tools::getValue('ajaxCallBack');

    include(_PS_MODULE_DIR_ . 'opartstat/config/metrics/' . $dir . '/' . $ajaxCallBack . '.php');

    $dateFormat = Tools::getValue('dateFormat');
    $dateFrom = Tools::getValue('dateFrom');
    $dateTo = (!Tools::getIsset('dateTo') || Tools::getValue('dateTo') == '') ? $dateFrom : Tools::getValue('dateTo');
    $dateTo = OpartStatTools::humanToMysqlDate($dateTo, true, $dateFormat);

    if ($dateFrom == -1)
      $dateFrom = OpartStatTools::humanToMysqlDate($dateFrom, false, $dateTo, true);
    else
      $dateFrom = OpartStatTools::humanToMysqlDate($dateFrom, false, $dateFormat);

    $otherVars = Tools::getValue('otherVars');

    $initialFilters = Tools::getValue('initialFilters');
    $compareFilters = Tools::getValue('compareFilters');

    //create callBack function name
    $fctName = "get" . ucfirst($ajaxCallBack);

    $vars = [
      'dateFrom' => $dateFrom,
      'dateTo' => $dateTo,
      'initialFilters' => opartStatTools::addChildrenCategoriesToFiltersArray($initialFilters),
      'compareFilters' => opartStatTools::addChildrenCategoriesToFiltersArray($compareFilters),
      'otherVars' => $otherVars,
    ];

    if (Tools::getIsset('selectedItemsId') && Tools::getValue('selectedItemsId') != null)
      $vars['selectedItemsId'] = Tools::getValue('selectedItemsId');

    $dateFromCompare = Tools::getValue('dateFromCompare');
    $dateToCompare = (!Tools::getIsset('dateToCompare') || Tools::getValue('dateToCompare') == '') ? $dateFromCompare : Tools::getValue('dateToCompare');

    $result['compare']['value'] = null;
    if ($dateFromCompare != null &&  $dateToCompare) {
      $dateFromCompare = OpartStatTools::humanToMysqlDate($dateFromCompare, false, $dateFormat);
      $dateToCompare = OpartStatTools::humanToMysqlDate($dateToCompare, true, $dateFormat);

      $vars['dateFromCompare'] = $dateFromCompare;
      $vars['dateToCompare'] = $dateToCompare;
    }

    $result['initialFilters'] = $initialFilters;
    $result['compareFilters'] = $compareFilters;

    $result = $fctName($vars);
    return $result;
  }

  public function ajaxProcessSaveSettings()
  {
    $preset1 = Tools::getValue('preset1');
    $preset2 = Tools::getValue('preset2');
    $initialFilters  = Tools::getValue('initialFilters');
    $compareFilters = Tools::getValue('compareFilters');

    $reportName = Tools::getValue('reportName');

    $result['errorMsg'] = '';

    $acceptedValues = ["custom", "today", "yesterday", "last7", "last30", "last90", "last365", "lastWeek", "lastMonth", "weekToDate", "monthToDate", "lastYear", "yearToDate", "samePeriod", "previousPeriod"];

    if (!in_array($preset1, $acceptedValues)) {
      $result['errorMsg'] = $this->module->l('The period is not valid','adminopartstatglobal');
      die(json_encode($result));
    }

    if (!in_array($preset2, $acceptedValues)) {
      $result['errorMsg'] = $this->module->l('The comparison period is not valid','adminopartstatglobal');
      die(json_encode($result));
    }

    if (!Validate::isGenericName($reportName)) {
      $result['errorMsg'] = $this->module->l('The reportName is not valid','adminopartstatglobal');
      die(json_encode($result));
    }

    $settings['initial']['date'] = $preset1;
    $settings['initial']['filters'] = $initialFilters;

    if ($preset1 == "custom") {
      $from = Tools::getValue('dateFrom');
      $to = Tools::getValue('dateTo');

      if (!OpartStatTools::isDateFormat($from) || !OpartStatTools::isDateFormat($to)) {
        $result['errorMsg'] = $this->module->l('Date format are not valid','adminopartstatglobal');
        die(json_encode($result));
      }
      $settings['initial']['from'] = $from;
      $settings['initial']['to'] = $to;
    }

    if ($compareFilters != false && $preset2 == "")
      $preset2 = "today";

    $settings['compare']['date'] = $preset2;
    $settings['compare']['filters'] = $compareFilters;

    if ($preset2 == "custom") {
      $fromCompare = Tools::getValue('dateFromCompare');
      $toCompare = Tools::getValue('dateToCompare');
      if ($fromCompare != "" && $toCompare != "") {
        if (!OpartStatTools::isDateFormat($fromCompare) || !OpartStatTools::isDateFormat($toCompare)) {
          $result['errorMsg'] = $this->module->l('Compare date format are not valid','adminopartstatglobal');
          die(json_encode($result));
        }
        $settings['compare']['from'] = $fromCompare;
        $settings['compare']['to'] = $toCompare;
      }
    }

    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);

    if (!$this->isAuthorizedToEdit($reportName, $datas)) {
      $this->errors[] = $this->module->l('You\'r not allowed to edit this report','adminopartstatglobal');
      return false;
    }

    if (!Validate::isConfigName($reportName))
      die('Report name is not valid');

    //if (!OpartStatTools::setReportConfig($settings, $reportName . '_settings'))
    if (!OpartStatTools::setReportConfig($settings, $datas['reportName'] . '_settings', $datas['ownerUserId']))
      $result['errorMsg'] = $this->modue->l('Can\'t update settings','adminopartstatglobal');

    die(json_encode($result));
  }

  public function ajaxProcessSearchItem()
  {
    $query = Tools::getValue('q', false);
    $type = Tools::getValue('searchItemType');

    if ($type == 'products') {
      $sql =
        'SELECT 
             product.`id_product` as id,
             product.`reference`,
             product.`price`,
            product_lang.`name`
        FROM `' . _DB_PREFIX_ . 'product` product
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` product_lang ON (
            product_lang.id_product = product.id_product
            AND product_lang.id_lang = ' . (int)$this->context->language->id . '
            ' . Shop::addSqlRestrictionOnLang('product_lang') . '
        )
        ' . Shop::addSqlAssociation('product', 'product') . '
        WHERE 
            product_lang.name LIKE "%' . pSQL($query) . '%"
            OR product.reference LIKE "%' . pSQL($query) . '%"
        GROUP BY product.id_product
        LIMIT 0,3000';
    } 
    else if ($type == 'attributes') {
      $sql =
        'SELECT 
             attribute.`id_attribute` as id,
             CONCAT(attribute_group_lang.`name`," : ",attribute_lang.`name`) as name
        FROM `' . _DB_PREFIX_ . 'attribute` attribute  
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` attribute_lang ON (
            attribute_lang.id_attribute = attribute.id_attribute
          AND
            attribute_lang.id_lang = ' . (int)$this->context->language->id . '
        )      
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` attribute_group_lang ON (
            attribute_group_lang.id_attribute_group = attribute.id_attribute_group
          AND 
            attribute_group_lang.id_lang = ' . (int)$this->context->language->id . '
        )
        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_shop` attribute_shop ON (
          attribute.`id_attribute` = attribute_shop.id_attribute
        )  
        WHERE 
          (
              attribute_lang.name LIKE "%' . pSQL($query) . '%"
            OR
              attribute_group_lang.name LIKE "%' . pSQL($query) . '%"
          )
          ' . Shop::addSqlRestrictionOnLang('attribute_shop') . '
        GROUP BY 
          attribute.id_attribute
        LIMIT 0,3000';
    } 
    else if ($type == 'features') {
      $sql =
        'SELECT 
             feature_value.`id_feature_value` as id,
             CONCAT(feature_lang.`name`," : ",feature_value_lang.`value`) as name
        FROM `' . _DB_PREFIX_ . 'feature_value` feature_value  
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` feature_value_lang ON (
            feature_value_lang.id_feature_value = feature_value.id_feature_value
          AND
            feature_value_lang.id_lang = ' . (int)$this->context->language->id . '
        )      
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` feature_lang ON (
            feature_lang.id_feature = feature_value.id_feature
          AND 
            feature_lang.id_lang = ' . (int)$this->context->language->id . '
        )
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_shop` feature_shop ON (
          feature_lang.`id_feature` = feature_shop.id_feature
        )  
        WHERE 
          (
              feature_value_lang.value LIKE "%' . pSQL($query) . '%"
            OR
              feature_lang.name LIKE "%' . pSQL($query) . '%"
          )
          ' . Shop::addSqlRestrictionOnLang('feature_shop') . '
        GROUP BY 
          feature_value.id_feature_value,feature_lang.id_feature
        LIMIT 0,3000';
    }
    else if ($type == 'categories') {
      $sql =
        'SELECT 
         category_lang.id_category as id,
         category_lang.`name`
      FROM 
        `' . _DB_PREFIX_ . 'category_lang` category_lang      
      WHERE 
          category_lang.name LIKE "%' . pSQL($query) . '%"
      AND 
        category_lang.id_lang = ' . (int)$this->context->language->id . '
      GROUP BY 
        category_lang.id_category      
      LIMIT 
        0,3000';
    } else if ($type == 'brands') {
      $sql =
        'SELECT 
          manufacturer.id_manufacturer as id,
          manufacturer.name
      FROM `' . _DB_PREFIX_ . 'manufacturer` manufacturer
      ' . Shop::addSqlAssociation('manufacturer', 'manufacturer') . '
      WHERE 
          manufacturer.name LIKE "%' . pSQL($query) . '%"
      GROUP BY manufacturer.id_manufacturer
      LIMIT 0,3000';
    } else if ($type == 'customerGroups') {
      $sql =
        'SELECT 
          group_lang.`id_group` as id,
          group_lang.`name`
      FROM `' . _DB_PREFIX_ . 'group_lang` group_lang    
      WHERE 
      group_lang.name LIKE "%' . pSQL($query) . '%"
          AND group_lang.id_lang = ' . (int)$this->context->language->id . '
      GROUP BY group_lang.id_group
      LIMIT 0,3000';
    } else if ($type == 'countries') {
      $sql =
        'SELECT 
          country_lang.`id_country` as id,
          country_lang.`name`
      FROM `' . _DB_PREFIX_ . 'country_lang` country_lang  
      WHERE 
        country_lang.name LIKE "%' . pSQL($query) . '%"
          AND country_lang.id_lang = ' . (int)$this->context->language->id . '
      GROUP BY country_lang.id_country';
    } else if ($type == 'paymentMethods') {
      $sql =
        'SELECT 
          orders.payment as id,
          orders.payment as name
        FROM `' . _DB_PREFIX_ . 'orders` orders
        WHERE 
            orders.payment LIKE "%' . pSQL($query) . '%"
        GROUP BY orders.payment
        LIMIT 0,3000';
    } else
      die('Item type is invalid');

    $items = Db::getInstance()->executeS($sql);

    die(json_encode(
      $items
    ));
  }

  public function searchItemDatas($itemType, $ids)
  {
    $where = "";
    if ($itemType == 'products') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " product.id_product = " . (int)$id : " OR product.id_product = " . (int)$id;

      $sql =
        'SELECT 
           product.id_product as id,
           product.`reference`,
          product_lang.`name`
      FROM `' . _DB_PREFIX_ . 'product` product
      LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` product_lang ON (
          product_lang.id_product = product.id_product
          AND product_lang.id_lang = ' . (int)$this->context->language->id . '
          ' . Shop::addSqlRestrictionOnLang('product_lang') . '
      )
      ' . Shop::addSqlAssociation('product', 'product') . '
      WHERE 
        ' . $where . '
        GROUP BY product.id_product';
    } else if ($itemType == 'attributes') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " attribute.id_attribute = " . (int)$id : " OR attribute.id_attribute = " . (int)$id;

      $sql =
        'SELECT 
            attribute.id_attribute as id,
            CONCAT(attribute_group_lang.`name`," : ",attribute_lang.`name`) as name
          FROM `' . _DB_PREFIX_ . 'attribute` attribute
          LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` attribute_lang ON (
            attribute_lang.id_attribute = attribute.id_attribute
          AND
            attribute_lang.id_lang = ' . (int)$this->context->language->id . '
          )      
          LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` attribute_group_lang ON (
              attribute_group_lang.id_attribute_group = attribute.id_attribute_group
            AND 
              attribute_group_lang.id_lang = ' . (int)$this->context->language->id . '
          )
          LEFT JOIN `' . _DB_PREFIX_ . 'attribute_shop` attribute_shop ON (
            attribute.`id_attribute` = attribute_shop.id_attribute
          )  
          WHERE 
            ' . $where . '
          GROUP BY 
            attribute.id_attribute';
    } else if ($itemType == 'features') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " feature_value.id_feature_value = " . (int)$id : " OR feature_value.id_feature_value = " . (int)$id;
      $sql =
        'SELECT 
             feature_value.`id_feature_value` as id,
             CONCAT(feature_lang.`name`," : ",feature_value_lang.`value`) as name
        FROM `' . _DB_PREFIX_ . 'feature_value` feature_value  
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` feature_value_lang ON (
            feature_value_lang.id_feature_value = feature_value.id_feature_value
          AND
            feature_value_lang.id_lang = ' . (int)$this->context->language->id . '
        )      
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` feature_lang ON (
            feature_lang.id_feature = feature_value.id_feature
          AND 
            feature_lang.id_lang = ' . (int)$this->context->language->id . '
        )
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_shop` feature_shop ON (
          feature_lang.`id_feature` = feature_shop.id_feature
        )  
        WHERE 
            ' . $where . '
        GROUP BY 
          feature_value.id_feature_value
        ';
    }
    else if ($itemType == 'categories') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " category.id_category = " . (int)$id : " OR category.id_category = " . (int)$id;

      $sql =
        'SELECT 
            category.id_category as id,
            category_lang.`name`
          FROM `' . _DB_PREFIX_ . 'category` category
          LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` category_lang ON (
              category_lang.id_category = category.id_category
              AND category_lang.id_lang = ' . (int)$this->context->language->id . '
              ' . Shop::addSqlRestrictionOnLang('category_lang') . '
          )
          ' . Shop::addSqlAssociation('category', 'category') . '
          WHERE 
            ' . $where . '
            GROUP BY category.id_category';
    } else if ($itemType == 'brands') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " manufacturer.id_manufacturer = " . (int)$id : " OR manufacturer.id_manufacturer = " . (int)$id;

      $sql =
        'SELECT 
            manufacturer.id_manufacturer as id,
            manufacturer.name
        FROM `' . _DB_PREFIX_ . 'manufacturer` manufacturer
        ' . Shop::addSqlAssociation('manufacturer', 'manufacturer') . '
        WHERE 
          ' . $where . '
          GROUP BY manufacturer.id_manufacturer';
    } else if ($itemType == 'customerGroups') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " group_lang.id_group = " . (int)$id : " OR group_lang.id_group = " . (int)$id;

      $sql =
        'SELECT 
          group_lang.`id_group` as id,
          group_lang.`name`
      FROM `' . _DB_PREFIX_ . 'group_lang` group_lang
      ' . Shop::addSqlAssociation('group_lang', 'group_lang') . '      
      WHERE 
          ' . $where . '
          AND group_lang.id_lang = ' . (int)$this->context->language->id . '
          GROUP BY group_lang.id_group';
    } else if ($itemType == 'countries') {
      foreach ($ids as $id)
        $where .= ($where == "") ? " country_lang.id_country = " . (int)$id : " OR country_lang.id_country = " . (int)$id;

      $sql =
        'SELECT 
          country_lang.`id_country` as id,
          country_lang.`name`
      FROM `' . _DB_PREFIX_ . 'country_lang` country_lang
      ' . Shop::addSqlAssociation('country_lang', 'country_lang') . '      
      WHERE 
          ' . $where . '
          AND country_lang.id_lang = ' . (int)$this->context->language->id . '
          GROUP BY country_lang.id_country';
    } else if ($itemType == 'paymentMethods') {
      foreach ($ids as $paymentName)
        $where .= ($where == "") ? " orders.payment = '" . pSQL($paymentName) . "'" : " OR orders.payment = '" . pSQL($paymentName) . "'";

      $sql =
        'SELECT 
          orders.payment as id,
          orders.payment
      FROM `' . _DB_PREFIX_ . 'orders` orders   
      WHERE 
          ' . $where . '
          GROUP BY orders.payment';
    } else {
      return [];
    }
    $items = Db::getInstance()->executeS($sql);
    return $items;
  }

  public function ajaxProcessEditReportName()
  {
    $oldName = Tools::getValue('oldName');
    $newName = Tools::getValue('newName');
    $reportKey = Tools::getValue('reportKey');

    if (!$this->isAuthorizedToEdit($reportKey)) {
      $result['errorMsg'] = $this->module->l('You\' not allowed to edit this report','adminopartstatglobal');
      die(json_encode($result));
    }

    if (!Validate::isGenericName($oldName)) {
      $result['errorMsg'] = $this->module->l('The old report name is not valid','adminopartstatglobal');
      die(json_encode($result));
    }
    if (!Validate::isGenericName($newName)) {
      $result['errorMsg'] = $this->module->l('The new report name is not valid','adminopartstatglobal');
      die(json_encode($result));
    }
    if (!Validate::isConfigName($reportKey)) {
      $result['errorMsg'] = $this->module->l('The report key is not valid','adminopartstatglobal');
      die(json_encode($result));
    }

    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportKey, $this->idEmployee);

    //$topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);
    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $datas['ownerUserId']);

    foreach ($topMenuConfig['reports'] as $position => $report) {
      if ($datas['reportName'] == $report['name']) {
        $idLang = $this->context->language->id;
        $topMenuConfig['reports'][$position]['name'] = $datas['reportName'];
        $topMenuConfig['reports'][$position]['title'][$idLang] = $newName;
        OpartStatTools::setReportConfig($topMenuConfig, 'top-menu', $datas['ownerUserId']);

        //$reportConfig = OpartStatTools::getJsonConfig($reportKey, 'reports', 'customs/' . $this->idEmployee);
        $reportConfig = OpartStatTools::getJsonConfig($datas['reportName'], 'reports', 'customs/' . $datas['ownerUserId']);
        $reportConfig['title'][$idLang] = $newName;
        OpartStatTools::setReportConfig($reportConfig, $datas['reportName'], $datas['ownerUserId']);

        die(json_encode(true));
      }
    }
    die(json_encode(false));
  }

  public function deleteReport()
  {
    $reportNameToDelete = Tools::getValue('deleteReport');

    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportNameToDelete, $this->idEmployee);

    if ($this->idEmployee != $datas['ownerUserId'] && $this->getReportRights($reportNameToDelete, $datas) != 2) {
      $this->errors[] = $this->module->l('You don\'t have permission to delete this report','adminopartstatglobal');
      return false;
    }

    $customDir = _PS_MODULE_DIR_ . 'opartstat/config/reports/customs/' . $datas['ownerUserId'];
    $jsonFilePath = $customDir . '/' . $datas['reportName'] . '.json';

    if (!file_exists($jsonFilePath)) {
      $this->errors[] = $this->module->l('Can\'t delete this report file do not exists','adminopartstatglobal');
      return false;
    } else
      $reportConfig = $this->getReportConfig($reportNameToDelete);

    $idLang = $this->context->language->id;
    $reportHumanName = $reportConfig['title'][$idLang];

    if (OpartStatTools::deleteReport($datas['reportName'], $datas['ownerUserId']))
      $this->confirmations[] = sprintf($this->module->l('The "%s" report has been deleted'), $reportHumanName,'adminopartstatglobal');
    else
      $this->errors[] = sprintf($this->module->l('Can\'t delete "%s" report'), $reportHumanName,'adminopartstatglobal');
  }

  public function addNewReport()
  {
    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);
    $idLang = $this->context->language->id;
    $newReport = $this->getNewReportName($topMenuConfig);

    $reportConfig = [
      "title" => [$idLang => $newReport['name']],
      "type" => "classic",
      "metrics" => []
    ];

    $reportSettings = [
      "initial" => ["date" => "last30"],
      "compare" => ["date" => ""]
    ];
    $reportTitle = sprintf($this->module->l('Report %s'), $newReport['number'],'adminopartstatglobal');

    if ($this->createReport($reportConfig, $reportSettings, $newReport['name'], $idLang, $reportTitle))
      Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOpartStatGlobal', true, [], ['reportName' => $newReport['name'], 'confirmAddNewReport' => true]));
  }

  public function duplicateReport()
  {
    $reportToCopyName = Tools::getValue(('duplicateReport'));

    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportToCopyName, $this->idEmployee);

    if (!Validate::isConfigName($reportToCopyName)) {
      $this->errors[] = $this->module->l('Can\'t copy this report','adminopartstatglobal');
      return false;
    }
    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);
    $newReport = $this->getNewReportName($topMenuConfig);

    $idLang = $this->context->language->id;

    //$newReportConfig = OpartStatTools::getJsonConfig($reportToCopyName, 'reports', 'customs/' . $this->idEmployee);
    $newReportConfig = OpartStatTools::getJsonConfig($datas['reportName'], 'reports', 'customs/' . $datas['ownerUserId']);

    $newReportTitle = sprintf($this->module->l('Copy of %s'), $newReportConfig['title'][$idLang],'adminopartstatglobal');

    $newReportConfig["title"][$idLang] = $newReportTitle;

    //$newReportSettings = opartStatTools::getJsonConfig($reportToCopyName . '_settings', 'reports', 'customs/' . $this->idEmployee);
    $newReportSettings = opartStatTools::getJsonConfig($datas['reportName'] . '_settings', 'reports', 'customs/' . $datas['ownerUserId']);

    if ($this->createReport($newReportConfig, $newReportSettings, $newReport['name'], $idLang, $newReportTitle))
      Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOpartStatGlobal', true, [], ['reportName' => $newReport['name'], 'confirmAddNewReport' => true]));
  }

  public function createReport($reportConfig, $reportSettings, $newReportName, $idLang, $reportTitle)
  {
    if (!Validate::isConfigName($newReportName)) {
      $this->errors[] = sprintf($this->module->l('The report %s already exist', $newReportName,'adminopartstatglobal'));
      return false;
    }

    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);

    foreach ($topMenuConfig['reports'] as $report) {
      if ($newReportName == $report['name']) {
        $this->errors[] = sprintf($this->module->l('The report %s already exist', $newReportName,'adminopartstatglobal'));
        return false;
      }
    }

    if (!OpartStatTools::setReportConfig($reportConfig, $newReportName))
      $this->errors[] = $this->module->l('Can\'t add new report','adminopartstatglobal');

    if (!OpartStatTools::setReportConfig($reportSettings, $newReportName . '_settings'))
      $this->errors[] = $this->module->l('Can\'t save new report settings','adminopartstatglobal');

    $topMenuConfig['reports'][] = [
      "name" => $newReportName,
      "title" => [$idLang => $reportTitle]
    ];

    if (!OpartStatTools::setReportConfig($topMenuConfig, 'top-menu'))
      $this->errors[] = $this->module->l('Can\'t update top menu configuration','adminopartstatglobal');

    if (count($this->errors) == 0)
      return true;

    return false;
  }

  public function getNewReportName($topMenuConfig)
  {
    $lastReport = end($topMenuConfig['reports']);
    $number = (int)preg_replace("/[^0-9]/", "", $lastReport['name']) + 1;
    $newReportName = "report" . $number;
    $res = [
      'name' => $newReportName,
      'number' => $number
    ];
    return $res;
  }

  public function getMetricCategoriesName()
  {
    return array(
      '1' => $this->module->l('Sells','adminopartstatglobal'),
      '2' => $this->module->l('Revenues','adminopartstatglobal'),
      '3' => $this->module->l('Profits','adminopartstatglobal'),
      '4' => $this->module->l('Trafic','adminopartstatglobal'),
      '5' => $this->module->l('Conversion','adminopartstatglobal'),
      '6' => $this->module->l('Customers','adminopartstatglobal'),
      '7' => $this->module->l('Google ads','adminopartstatglobal'),
      '99' => $this->module->l('Others','adminopartstatglobal'),
    );
  }

  public function getTopMenuConfig()
  {
    $topMenuConfig = OpartStatTools::getJsonConfig('top-menu', 'reports', 'customs/' . $this->idEmployee);

    $sql = "SELECT reportName, ownerUserId FROM " . _DB_PREFIX_ . "opartstat_shared_reports WHERE guestUserId = " . (int)$this->idEmployee;
    $sharedReports = Db::getInstance()->executeS($sql);

    foreach ($sharedReports as $sharedReport) {
      $reportDatas = OpartStatTools::getJsonConfig($sharedReport['reportName'], 'reports', 'customs/' . $sharedReport['ownerUserId']);
      $sharedReportName = "shared_" . $sharedReport['ownerUserId'] . "_" . $sharedReport['reportName'];
      $topMenuConfig['reports'][] = array(
        "name" => $sharedReportName,
        "title" => $reportDatas['title']
      );
      //$reportsToAddDatas[] = 
    }
    return $topMenuConfig;
  }

  public function isAuthorizedToEdit($reportName, $datas = null)
  {
    if ($datas == null)
      $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);

    if ($this->idEmployee != $datas['ownerUserId'] && $this->getReportRights($reportName, $datas) != 2)
      return false;

    return true;
  }

  public function isReportOwner($reportName)
  {
    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);
    return ($datas['ownerUserId'] == $this->idEmployee);
  }

  public function getReportConfig($reportName)
  {
    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);
    $reportConfig = OpartStatTools::getJsonConfig($datas['reportName'], 'reports', 'customs/' . $datas['ownerUserId']);
    return $reportConfig;
  }

  public function getReportSettings($reportName)
  {
    $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);
    $reportSettings = opartStatTools::getJsonConfig($datas['reportName'] . '_settings', 'reports', 'customs/' . $datas['ownerUserId']);
    return $reportSettings;
  }

  private function getReportRights($reportName, $datas = null)
  {
    if ($datas == null)
      $datas = opartStatTools::extractReportNameAndOwnerIdIfExists($reportName, $this->idEmployee);

    if ($datas['ownerUserId'] == $this->idEmployee)
      $rights = 1;
    else {
      $sql = "SELECT rights FROM " . _DB_PREFIX_ . "opartstat_shared_reports 
                WHERE 
                    guestUserId = " . (int)$this->idEmployee . "
                AND 
                    ownerUserId = " . (int)$datas['ownerUserId'] . " 
                AND
                    reportName = '" . pSQL($datas['reportName']) . "'";
      $rights = Db::getInstance()->getValue($sql);
    }
    return $rights;
  }

  public function ajaxProcessLoadEmployees()
  {
    $reportName = Tools::getValue('reportName');
    $sql = "SELECT guestUserId,rights FROM " . _DB_PREFIX_ . "opartstat_shared_reports WHERE ownerUserId = " . (int)$this->idEmployee . " AND reportName = '" . pSQL($reportName) . "'";
    $employeesWithAccess = Db::getInstance()->executeS($sql);
    $employeeRightsById = [];
    foreach ($employeesWithAccess as $employee) {
      $employeeRightsById[$employee['guestUserId']] = $employee['rights'];
    }

    $employees = Employee::getEmployees();
    $employeesList = [];
    foreach ($employees as $employee) {
      if ($employee['id_employee'] == $this->idEmployee)
        continue;
      $rights = isset($employeeRightsById[$employee['id_employee']]) ? $employeeRightsById[$employee['id_employee']] : 0;
      $employeesList[] = [
        'id' => $employee['id_employee'],
        'name' => $employee['firstname'] . ' ' . $employee['lastname'],
        'rights' => $rights
      ];
    }
    $this->context->smarty->assign(array(
      'employeesList' => $employeesList
    ));
    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . 'opartstat/views/templates/admin/partial/shareReportModalEmployeesList.tpl'
    );
    echo $output;
    die();
  }

  public function ajaxProcessUpdateEmployeeRights()
  {
    $reportName = Tools::getValue('reportName');
    $guestId = Tools::getValue('guestId');
    $rights = Tools::getValue('rights');

    if ($rights == 0) {
      $sql = "DELETE FROM " . _DB_PREFIX_ . "opartstat_shared_reports 
                WHERE 
                    guestUserId = " . (int)$guestId . " 
                AND 
                    ownerUserId = " . (int)$this->idEmployee . "
                AND 
                    reportName = '" . pSQL($reportName) . "'";
      $result = Db::getInstance()->execute($sql);
    } else {
      $sql = "INSERT INTO " . _DB_PREFIX_ . "opartstat_shared_reports 
              (reportName, ownerUserId, guestUserId, rights) 
            VALUES 
              ('" . pSQL($reportName) . "', " . (int)$this->idEmployee . ", " . (int)$guestId . ",  " . (int)$rights . ")
            ON DUPLICATE KEY UPDATE 
              rights = VALUES(rights)";

      $result = Db::getInstance()->execute($sql);
    }
    die(json_encode($result));
  }

  public function ajaxProcessGetCustomerPagesViewed()
  {
    $userId = Tools::getValue('userId');
    $shopId = Context::getContext()->shop->id;

    if (Configuration::get('OPARTSTAT_USE_SAAS')) {
      $datas = [
        'shopId' => $shopId,
        'userId' => $userId
      ];

      $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/customerPagesViewed.php", false, $datas);

      if ($response['datas'] == null)
        die(json_encode([]));

      $visits = $response['datas'];
    } else {
      $sql = "SELECT 
                pageUrl, createdAt, utm_medium, utm_campaign, referrer, idCart  
              FROM 
                " . _DB_PREFIX_ . "opartstat_sessions 
              WHERE 
                shopId=" . (int)$shopId . " 
              AND 
                userId=" . (int)$userId . " 
              ORDER BY 
                createdAt DESC";
      $visits = Db::getInstance()->executeS($sql);
    }
    foreach ($visits as $key => $visit) {
      $visits[$key]['referrer'] = ($visits[$key]['referrer'] == OpartStatTools::getDomainNameFromUrl($visits[$key]['pageUrl'])) ? "" : $visits[$key]['referrer'];
      $visits[$key]['pageUrl'] = OpartStatTools::cleanUrl($visits[$key]['pageUrl'], true, true);
      $visits[$key]['createdAt'] = Tools::formatDateStr($visits[$key]['createdAt'], true);
      $visits[$key]['idCart'] = ($visits[$key]['idCart'] == null)?"":$visits[$key]['idCart'];
    }
    die(json_encode($visits));
  }

  public function ajaxProcessGetCustomerPagesViewedBeforeOrder()
  {
    $cartId = Tools::getValue('cartId');
    $shopId = Context::getContext()->shop->id;
    $attributionDelay = Configuration::get('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');
    //$dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sql = "SELECT date_add FROM " . _DB_PREFIX_ . "orders WHERE id_cart=" . (int)$cartId . " AND id_shop=" . (int)$shopId;
    $orderDate = Db::getInstance()->getValue($sql);

    if (!$orderDate)
      die(json_encode(['error' => 'Aucune commande trouvée pour ce panier.']));

    if (Configuration::get('OPARTSTAT_USE_SAAS')) {
      $datas = [
        'shopId' => $shopId,
        'cartId' => $cartId,
        'attributionDelay' => $attributionDelay,
        'orderDate' => $orderDate
      ];

      $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/customerPagesViewedBeforeOrder.php", false, $datas);

      if ($response['datas'] == null)
        die(json_encode([]));

      $visits = $response['datas'];
    } else {
      $sql = "SELECT 
                userIp 
              FROM 
                " . _DB_PREFIX_ . "opartstat_sessions 
              WHERE 
                idCart=" . (int)$cartId;

      $userIp = Db::getInstance()->getValue($sql);

      if($userIp == "")      
        die(json_encode([]));

      $sql = "SELECT 
                pageUrl, createdAt, utm_medium, utm_campaign, referrer, idCart  
              FROM 
                " . _DB_PREFIX_ . "opartstat_sessions 
              WHERE 
                shopId=" . (int)$shopId . " 
              AND 
                userIp='" . pSQL($userIp) . "' 
              AND 
                createdAt BETWEEN DATE_SUB('" . pSQL($orderDate) . "', INTERVAL " . (int)$attributionDelay . " HOUR) AND '" . pSQL($orderDate) . "'
              ORDER BY 
                createdAt DESC";

      $visits = Db::getInstance()->executeS($sql);
    }
    foreach ($visits as $key => $visit) {
      $visits[$key]['referrer'] = ($visits[$key]['referrer'] == OpartStatTools::getDomainNameFromUrl($visits[$key]['pageUrl'])) ? "" : $visits[$key]['referrer'];
      $visits[$key]['pageUrl'] = OpartStatTools::cleanUrl($visits[$key]['pageUrl'], true, true);
      $visits[$key]['createdAt'] = Tools::formatDateStr($visits[$key]['createdAt'], true);
      $visits[$key]['idCart'] = ($visits[$key]['idCart'] == null || $visits[$key]['idCart'] == 0)?"":$visits[$key]['idCart'];
    }
    die(json_encode($visits));
  }
}
