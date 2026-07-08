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

class AdminOpartStatSettingsCommissionsController extends ModuleAdminController
{

  protected $postError = array();
  protected $postConf = array();
  protected $moduleName = 'opartstat';
  protected $tab = 'analytics_stats';

  public function __construct()
  {
    // Set variables
    $this->context = Context::getContext();
    $this->bootstrap = true;
    $this->name = 'opartstat';
    parent::__construct();
  }

  public function setMedia($isNewTheme = false)
  {
    parent::setMedia($isNewTheme);
    
    $v = $this->module->version;
    $viewUrl = $this->context->shop->getBaseURL(true) . 'modules/' . $this->module->name . '/views/';
    
    $this->addCSS($viewUrl.'css/admin.css?v='.$v);
    $this->addCSS($viewUrl.'css/settings.css?v='.$v);

    if (version_compare(_PS_VERSION_, '1.7', '<')) {
      $this->addCSS($viewUrl.'css/16.css?v='.$v);
    }
  }

  public function InitContent()
  {
    parent::initContent();

    //$this->_postProcess();

    if (Tools::isSubmit('submitOpartUpdateUseCommissions')) {
        $this->updateUseCommissions();
        opartStatTools::purgeCacheFiles(true);
    }
      

    if (Tools::isSubmit('deleteopartstat_commissions')) {
      $this->deleteCommission();
      opartStatTools::purgeCacheFiles(true);
  }

    if (Tools::isSubmit('submitOpartAddCommission')) {
      $this->addCommission();
      opartStatTools::purgeCacheFiles(true);
  }

    if (Tools::isSubmit('submitOpartUpdateCommission')) {
      $this->updateCommission();
      opartStatTools::purgeCacheFiles(true);
  }

    if (Tools::isSubmit('updateopartstat_commissions'))  {
      $this->context->smarty->assign($this->getCommissionsData());
      opartStatTools::purgeCacheFiles(true);
  }

    $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin.js');

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('commissions');
    $this->context->smarty->assign($adminLinksArray);

    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName . '/views/templates/admin/settings/partial/header.tpl'
    );

    $this->context->smarty->assign(array(
      'useCommissions' => Configuration::get('OPARTSTAT_USE_COMMISSIONS')
    ));

    if (count($this->postError) > 0) {
      foreach ($this->postError as $err) {
        $output .= $this->module->displayError($err);
      }
    } elseif (count($this->postConf) > 0) {
      foreach ($this->postConf as $conf) {
        $output .= $this->module->displayConfirmation($conf);
      }
    }

    $output .=  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName . '/views/templates/admin/settings/commissions.tpl'
    );

    if (Configuration::get('OPARTSTAT_USE_COMMISSIONS')) {
      $output .=  $this->renderAddCommissionForm();
      $output .=  $this->renderCommissionsList();
    }

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
  }

  protected function renderAddCommissionForm()
  {
    $this->addJqueryUI('ui.datepicker');
    $jsDateFormat = (OpartStatTools::getDateFormat() == 'DD/MM/YYYY') ? 'dd/mm/yy' : 'mm/dd/yy';

    $paymentMethods = $this->getAllPaymentMethod();

    $this->context->smarty->assign(array(
      'jsDateFormat' => $jsDateFormat,
      'paymentMethods' => $paymentMethods
    ));

    $formRendered = $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName . '/views/templates/admin/settings/partial/addCommissionForm.tpl'
    );

    return $formRendered;
  }

  protected function updateUseCommissions()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'edit')) {
      $this->postError[] = $this->module->l('Your not allowed to edit those settings','adminopartstatsettingscommissions');
      return;
    }
    $useCommission = Tools::getValue('OPARTSTAT_USE_COMMISSIONS');
    if (!validate::isBool($useCommission))
      $this->postError[] = $this->module->l('OPARTSTAT_USE_COMMISSIONS should be a boolean','adminopartstatsettingscommissions');
    else
      Configuration::updateValue('OPARTSTAT_USE_COMMISSIONS', $useCommission);
  }

  protected function _postProcess()
  {
    /* if (Tools::isSubmit('submitOpartUpdateUseCommissions')) {
      $useCommission = Tools::getValue('OPARTSTAT_USE_COMMISSIONS');
      if (!validate::isBool($useCommission))
        $this->postError[] = $this->l('OPARTSTAT_USE_COMMISSIONS should be a boolean');
      else
        Configuration::updateValue('OPARTSTAT_USE_COMMISSIONS', $useCommission);
    } */
    /* if (Configuration::get('OPARTSTAT_USE_COMMISSIONS') != 1)
      return false; */

    /* if (Tools::isSubmit('deleteopartstat_commissions'))
      $this->deleteCommission(); */

    /* if (Tools::isSubmit('updateopartstat_commissions')) {
      die('ddddd');
      $updateCommissionId = (int)Tools::getValue('commissionId');
      $dateFormat = OpartStatTools::getDateFormat();
      $commissionToEdit = $this->loadCommission($updateCommissionId);
      $startDate = OpartStatTools::mysqlToHumanDate($commissionToEdit['startDate'], $dateFormat);
      if ($commissionToEdit['endDate'] != null)
        $endDate = OpartStatTools::mysqlToHumanDate($commissionToEdit['endDate'], $dateFormat);
      else
        $endDate = null;

      if ($this->containPercentCaractere($commissionToEdit['paymentMethod'])) {
        $this->context->smarty->assign(array(
          'paymentKeyword' => $commissionToEdit['paymentMethod']
        ));
        $commissionToEdit['paymentMethod'] = "keyword";
      }

      $this->context->smarty->assign(array(
        'dateFrom' => $startDate,
        'dateTo' => $endDate,
        'fixedFees' => $commissionToEdit['fixedFees'],
        'variableFees' => $commissionToEdit['variableFees'],
        'paymentMethod' => $commissionToEdit['paymentMethod'],
        'isEdit' => true,
        'updateCommissionId' => $updateCommissionId
      ));
    }
 */
    /* if (Tools::isSubmit('submitOpartAddCommission'))
      $this->addCommission();

    if (Tools::isSubmit('submitOpartUpdateCommission'))
      $this->updateCommission(); */
  }



  protected function formatDateOrNullToSql($date)
  {
    if ($date == "")
      $dateSql = "NULL";
    else
      $dateSql = '"' . pSQL($date) . '"';

    return $dateSql;
  }

  protected function addCommission()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'add')) {
      $this->postError[] = $this->module->l('Your not allowed to add comissions','adminopartstatsettingscommissions');
      return;
    }

    if (Configuration::get('OPARTSTAT_USE_COMMISSIONS') != 1)
      return false;

    $postData = $this->validPostData();
    if ($postData == false)
      return false;

    $dateToSql = $this->formatDateOrNullToSql($postData['dateTo']);

    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartstat_commissions 
          (startDate,endDate,fixedFees,variableFees,paymentMethod)
        VALUES (
          "' . pSQL($postData['dateFrom']) . '",
          ' . $dateToSql . ',
          "' . pSQL($postData['fixedFees']) . '",
          "' . pSQL($postData['variableFees']) . '",
          "' . pSQL($postData['paymentMethod']) . '"
        )';

    $db = Db::getInstance();
    if ($db->execute($sql))
      $this->postConf[] = $this->module->l('The commission have been added.','adminopartstatsettingscommissions');
    else
      $this->postError[] = $this->module->l('Error during the saving','adminopartstatsettingscommissions');
    return;
  }

  protected function updateCommission()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'edit')) {
      $this->postError[] = $this->module->l('Your not allowed to edit comissions','adminopartstatsettingscommissions');
      return;
    }

    if (Configuration::get('OPARTSTAT_USE_COMMISSIONS') != 1)
      return false;

    $updateId = (int)Tools::getValue('updateCommissionId');

    $postData = $this->validPostData($updateId);

    if ($postData == false)
      return false;

    $dateToSql = $this->formatDateOrNullToSql($postData['dateTo']);

    $sql = '
          UPDATE ' . _DB_PREFIX_ . 'opartstat_commissions 
          SET 
            startDate = "' . pSQL($postData['dateFrom']) . '", 
            endDate = ' . $dateToSql . ',
            fixedFees = "' . pSQL($postData['fixedFees']) . '",
            variableFees = "' . pSQL($postData['variableFees']) . '",
            paymentMethod = "' . pSQL($postData['paymentMethod']) . '"
          WHERE    
            commissionId = ' . (int)$updateId;

    $db = Db::getInstance();
    if ($db->execute($sql))
      $this->postConf[] = $this->module->l('The commison have been updated.','adminopartstatsettingscommissions');
    else
      $this->postError[] = $this->module->l('Error during the update','adminopartstatsettingscommissions');
    return;
  }

  protected function validPostData($updateId = null)
  {
    $dateFormat = OpartStatTools::getDateFormat();
    $dateFromHumanFormat = Tools::getValue('dateFrom');
    $dateToHumanFormat = Tools::getValue('dateTo');
    $postData['fixedFees'] = Tools::getValue('fixedFees');
    $postData['variableFees'] = Tools::getValue('variableFees');
    $postData['paymentMethod'] = Tools::getValue('paymentMethod');
    if ($postData['paymentMethod'] == "keyword")
      $postData['paymentMethod'] = Tools::getValue('paymentKeyword');

    if (!OpartStatTools::isDateFormat($dateFromHumanFormat))
      $this->postError[] = $this->module->l('Start date is not valid','adminopartstatsettingscommissions');

    $postData['dateFrom'] = OpartStatTools::humanToMysqlDate($dateFromHumanFormat, false, $dateFormat);

    if (!OpartStatTools::isDateFormat($dateToHumanFormat) && $dateToHumanFormat != "")
      $this->postError[] = $this->module->l('End date is not valid','adminopartstatsettingscommissions');

    if ($dateToHumanFormat != "")
      $postData['dateTo'] = OpartStatTools::humanToMysqlDate($dateToHumanFormat, true, $dateFormat);
    else
      $postData['dateTo'] = "";

    $isOverLapId = $this->isOverlapPeriod($postData['dateFrom'], $postData['dateTo'], $postData['paymentMethod'], $updateId);
    if ($isOverLapId)
      $this->postError[] = sprintf($this->module->l('Dates are not valid as they overlap with commission id : %s'), $isOverLapId,'adminopartstatsettingscommissions');

    $postData['fixedFees'] = OpartStatTools::cleanPrice($postData['fixedFees']);
    if (!validate::isPrice($postData['fixedFees']))
      $this->postError[] = $this->module->l('Fixed Fees is not valid','adminopartstatsettingscommissions');

    $postData['variableFees'] = OpartStatTools::cleanPrice($postData['variableFees']);
    if (!validate::isPrice($postData['variableFees']))
      $this->postError[] = $this->module->l('Variable Fees is not valid','adminopartstatsettingscommissions');

    $allPaymentMethods = $this->getAllPaymentMethod();
    if (!in_array($postData['paymentMethod'], $allPaymentMethods) && !$this->containPercentCaractere($postData['paymentMethod']))
      $this->postError[] = $this->module->l('Payment method is not valid','adminopartstatsettingscommissions');

    if (count($this->postError) > 0) {
      $this->context->smarty->assign(array(
        'dateFrom' => $dateFromHumanFormat,
        'dateTo' => $dateToHumanFormat,
        'fixedFees' => $postData['fixedFees'],
        'variableFees' => $postData['variableFees'],
        'paymentMethod' => $postData['paymentMethod']
      ));

      if ($updateId != null) {
        $this->context->smarty->assign(array(
          'isEdit' => true,
          'updateCommissionId' => $updateId
        ));
      }

      return false;
    }

    return $postData;
  }

  protected function getAllPaymentMethod()
  {
    $sql = "SELECT DISTINCT(payment) FROM " . _DB_PREFIX_ . "orders";
    $paymentMethods = Db::getInstance()->executeS($sql);
    foreach ($paymentMethods as $paymentMethod) {
      $result[] = $paymentMethod['payment'];
    }
    return $result;
  }

  protected function renderCommissionsList()
  {
    $db = Db::getInstance();
    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'opartstat_commissions';
    $commissions = $db->executeS($sql);

    $fields_list = array(
      'commissionId' => array(
        'title' => $this->module->l('Id','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'paymentMethod' => array(
        'title' => $this->module->l('Payment Method','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'startDate' => array(
        'title' => $this->module->l('Start Date','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'endDate' => array(
        'title' => $this->module->l('End Date','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'fixedFees' => array(
        'title' => $this->module->l('Fixed Fees','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'variableFees' => array(
        'title' => $this->module->l('Variable Fees (%)','adminopartstatsettingscommissions'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      )
    );

    $helper_list = new HelperList();
    $helper_list->shopLinkType = '';
    $helper_list->simple_header = false;
    $helper_list->actions = ['edit', 'delete'];
    $helper_list->show_toolbar = true;
    $helper_list->identifier = 'commissionId';
    $helper_list->table = 'opartstat_commissions';
    $helper_list->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsCommissions', false);
    $helper_list->token = Tools::getAdminTokenLite('AdminOpartStatSettingsCommissions');
    $helper_list->title = $this->module->l('Fees','adminopartstatsettingscommissions');
    $helper_list->_defaultOrderBy = 'name';
    $helper_list->_defaultOrderWay = 'ASC';

    $helper_list->list_total = count($commissions);
    $helper_list->pagination = array(
      'total' => count($commissions),
      'page' => 1,
      'limit' => count($commissions)
    );

    $fields_value = array(
      'delete' => array(
        'title' => $this->module->l('Delete','adminopartstatsettingscommissions'),
        'icon' => 'process-icon-delete'
      )
    );

    return $helper_list->generateList($commissions, $fields_list, $fields_value);
  }


  public function deleteCommission()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'delete')) {
      $this->postError[] = $this->module->l('Your not allowed to delete comissions','adminopartstatsettingscommissions');
      return;
    }

    if (Configuration::get('OPARTSTAT_USE_COMMISSIONS') != 1)
      return false;

    $commissionId = Tools::getValue('commissionId');
    if (Db::getInstance()->delete('opartstat_commissions', '`commissionId` = ' . (int)$commissionId))
      $this->postConf[] = sprintf($this->module->l('The commission has been deleted.','adminopartstatsettingscommissions'));
    else
      $this->postError[] = $this->module->l('An error occurred while deleting the commission.','adminopartstatsettingscommissions');
  }

  public function loadCommission($commissionId)
  {
    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'opartstat_commissions WHERE commissionId = ' . (int)$commissionId;
    $result = db::getInstance()->getRow($sql);
    return $result;
  }

  private function getCommissionsData() {
    $updateCommissionId = (int)Tools::getValue('commissionId');
      $dateFormat = OpartStatTools::getDateFormat();
      $commissionToEdit = $this->loadCommission($updateCommissionId);
      $startDate = OpartStatTools::mysqlToHumanDate($commissionToEdit['startDate'], $dateFormat);
      if ($commissionToEdit['endDate'] != null)
        $endDate = OpartStatTools::mysqlToHumanDate($commissionToEdit['endDate'], $dateFormat);
      else
        $endDate = null;

      if ($this->containPercentCaractere($commissionToEdit['paymentMethod'])) {
        $this->context->smarty->assign(array(
          'paymentKeyword' => $commissionToEdit['paymentMethod']
        ));
        $commissionToEdit['paymentMethod'] = "keyword";
      }

      $commissionDatas = array(
        'dateFrom' => $startDate,
        'dateTo' => $endDate,
        'fixedFees' => $commissionToEdit['fixedFees'],
        'variableFees' => $commissionToEdit['variableFees'],
        'paymentMethod' => $commissionToEdit['paymentMethod'],
        'isEdit' => true,
        'updateCommissionId' => $updateCommissionId
      );
      return $commissionDatas;
  }

  public function isOverlapPeriod($dateFrom, $dateTo, $paymentMethod, $updateId = null)
  {
    $dateToSql = $this->formatDateOrNullToSql($dateTo);
    $ignoreUpdateId = ($updateId != null) ? "AND commissionId != " . (int)$updateId : "";

    if ($dateToSql === "NULL") {
      $sql = 'SELECT commissionId FROM ' . _DB_PREFIX_ . 'opartstat_commissions 
                WHERE paymentMethod = "' . pSQL($paymentMethod) . '"
                AND startDate <= NOW()
                AND (endDate >= "' . pSQL($dateFrom) . '" OR endDate IS NULL)'
        . $ignoreUpdateId;
    } else {

      $sql = 'SELECT commissionId FROM ' . _DB_PREFIX_ . 'opartstat_commissions 
                WHERE paymentMethod = "' . pSQL($paymentMethod) . '"
                AND startDate <= ' . $dateToSql . ' 
                AND (endDate >= "' . pSQL($dateFrom) . '" OR endDate IS NULL)'
        . $ignoreUpdateId;
    }

    return db::getInstance()->getValue($sql);
  }

  public function containPercentCaractere($str)
  {
    if (strpos($str, "%") !== false) {
      return true;
    } else {
      return false;
    }
  }
}
