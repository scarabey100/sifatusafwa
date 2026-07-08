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

class AdminOpartStatSettingsGlobalController extends ModuleAdminController
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
    $this->removeOpartStatDemo();

    parent::initContent();

    if (Tools::isSubmit('submitOpartStatConfig')) {
        $this->_postProcess();
        opartStatTools::purgeCacheFiles(true);
    }

    $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin.js');
      

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('global');
    $this->context->smarty->assign($adminLinksArray);

    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName . '/views/templates/admin/settings/partial/header.tpl'
    );
    if (count($this->postError) > 0) {
      foreach ($this->postError as $err) {

        $output .= $this->module->displayError($err);
      }
    } elseif (count($this->postConf) > 0) {
      foreach ($this->postConf as $conf) {
        $output .= $this->module->displayConfirmation($conf);
      }
    }

    $output .= $this->renderForm();

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
  }

  public function renderForm()
  {
    $helper = new HelperForm();
    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitOpartStatConfig';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsGlobal', true);

    $helper->tpl_vars = [
      'fields_value' => $this->getConfigFormValues(),
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    return $helper->generateForm([$this->getConfigForm()]);
  }

  protected function getConfigForm()
  {
    $orderStatus = $this->getOrderStatusList();
    $suggestedStatus = "";
    foreach ($orderStatus as $orderStatu) {
      if ($orderStatu['paid'] == 1)
        $suggestedStatus .= ($suggestedStatus == "") ? '"' . $orderStatu['name'] . '"' : ', "' . $orderStatu['name'] . '"';
    }

    return array(
      'form' => array(
        'legend' => array(
          'title' => $this->module->l('General settings','adminopartstatsettingsglobal'),
          'icon' => 'icon-cogs',
        ),
        'input' => array(
          array(
            'type' => 'checkbox',
            'label' => $this->module->l('Status of valid orders','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_STATUS_VALID_ORDER[]',
            'desc' => sprintf($this->module->l('Choose the statuses of the orders that should be taken into account in the revenues calculation. We suggest to check those status : %s and "Partial refund"'), $suggestedStatus,'adminopartstatsettingsglobal'),
            'values' => [
              'query' => $orderStatus,
              'id' => 'id',
              'val' => 'val',
              'name' => 'name',
            ],
          ),
          array(
            'type' => 'checkbox',
            'label' => $this->module->l('Status of refunded orders','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_STATUS_REFUNDED_ORDER[]',
            'desc' => $this->module->l('Choose the statuses of the orders that should be taken into account in the refund calculation. IMPORTANT : do not check "partial refund". It as to be checked as a validated order status.','adminopartstatsettingsglobal'),
            'values' => [
              'query' => $orderStatus,
              'id' => 'id',
              'val' => 'val',
              'name' => 'name',
            ]
          ),
          array(
            'type' => 'checkbox',
            'label' => $this->module->l('Status of incoming orders','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_STATUS_INCOMING_ORDER[]',
            'desc' => $this->module->l('Choose the statuses of the orders that should be taken into account in incoming payment calculation','adminopartstatsettingsglobal'),
            'values' => [
              'query' => $orderStatus,
              'id' => 'id',
              'val' => 'val',
              'name' => 'name',
            ]
          ),
          array(
            'type' => 'switch',
            'label' => $this->module->l('Exclude shipping cost','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_EXCLUDE_SHIPPING',
            'class' => 't',
            'is_bool' => true,
            'desc' => $this->module->l('Enable this option if you want shipping costs not to be taken into account when calculating revenues.','adminopartstatsettingsglobal'),
            'values' => array(
              array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled','adminopartstatsettingsglobal')
              ),
              array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled','adminopartstatsettingsglobal')
              )
            ),
          ),
          array(
            'type' => 'switch',
            'label' => $this->module->l('Exclude Free order','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_EXCLUDE_FREE_ORDER',
            'class' => 't',
            'is_bool' => true,
            'desc' => $this->module->l('Enable this option if you want to exclude free order from calculation.','adminopartstatsettingsglobal'),
            'values' => array(
              array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled','adminopartstatsettingsglobal')
              ),
              array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled','adminopartstatsettingsglobal')
              )
            ),
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('Number of days before a customer becomes inactive','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('A customer is considered inactive if their last order was placed more than X days ago, where X is the number of days specified in this field.','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_INACTIV_CUSTOMER_DAYS'
          ),
          array(
              'type' => 'switch',
              'label' => $this->module->l('Store statistics in a separate database','adminopartstatsettingsglobal'),
              'desc' => $this->module->l('If enabled, visit/session data will be stored in an external database (useful to reduce the size of the main PrestaShop database).','adminopartstatsettingsglobal'),
              'name' => 'OPARTSTAT_USE_SEPARATE_DB',
              'is_bool' => true,
              'values' => array(
                array(
                  'id' => 'active_on',
                  'value' => 1,
                  'label' => $this->module->l('Yes','adminopartstatsettingsglobal')
                ),
                array(
                  'id' => 'active_off',
                  'value' => 0,
                  'label' => $this->module->l('No','adminopartstatsettingsglobal')
                )
              ),
            ),
          array(
            'type' => 'text',
            'label' => $this->module->l('Maximum number of visits stored in the database','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('When this limit is reached, a new visit will erase the oldest one already recorded.','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_MAX_VISITS'
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('External DB host','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('Only used if "Store statistics in a separate database" is enabled. Example: 127.0.0.1 or db.myhost.tld','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_DB_HOST'
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('External DB port','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('Leave empty to use default port (3306).','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_DB_PORT'
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('External DB name','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_DB_NAME'
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('External DB user','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_DB_USER'
          ),
          array(
            'type' => 'password',
            'label' => $this->module->l('External DB password','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_DB_PASS'
          ),
         /* array(
            'type' => 'text',
            'label' => $this->module->l('Maximum number of Google Ads clicks stored in the database','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('When this limit is reached, a new clicks will erase the oldest one already recorded. (This settings is used only if Op\'art Stat Premium is activated','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_MAX_GADS_CLICKS'
          ),*/
          array(
            'type' => 'switch',
            'label' => $this->module->l('Use order creation date instead of invoice','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_USE_ORDER_CREATED_DATE',
            'class' => 't',
            'is_bool' => true,
            'desc' => $this->module->l('Only use the order creation date if the module does not display any data ','adminopartstatsettingsglobal'),
            'values' => array(
              array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled','adminopartstatsettingsglobal')
              ),
              array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled','adminopartstatsettingsglobal')
              )
            ),
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('Live time (in minutes) ','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('Live stats will be calculated on the last X minutes shown in this field.','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_LIVE_TIME'
          ),
          array(
            'type' => 'text',
            'label' => $this->module->l('Conversion attribution duration (in hours)','adminopartstatsettingsglobal'),
            'desc' => $this->module->l('"Conversion attribution duration" refers to the specific time period during which conversions are credited to a particular source','adminopartstatsettingsglobal'),
            'name' => 'OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION'
          ),
        ),
        'submit' => array(
          'title' => $this->module->l('Save','adminopartstatsettingsglobal'),
        ),
      ),
    );
  }

  protected function getOrderStatusList()
  {
    $states = new OrderState();
    $states2 = $states->getOrderStates($this->context->language->id);

    foreach ($states2 as $node) {
      $orderStatus[] = [
        'id' => $node['id_order_state'],
        'name' => $node['name'],
        'val' => $node['id_order_state'],
        'paid' => $node['paid'],
      ];
    }

    return $orderStatus;
  }

  protected function _postProcess()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'edit')) {
      $this->postError[] = $this->module->l('Your not allowed to edit those settings','adminopartstatsettingsglobal');
      return;
    }
    $validStatus = Tools::getValue('OPARTSTAT_STATUS_VALID_ORDER');
    $cancelStatus = Tools::getValue('OPARTSTAT_STATUS_REFUNDED_ORDER');
    $incomingStatus = Tools::getValue('OPARTSTAT_STATUS_INCOMING_ORDER');
    $excludeShipping = Tools::getValue('OPARTSTAT_EXCLUDE_SHIPPING');
    $excludeFreeOrder = Tools::getValue('OPARTSTAT_EXCLUDE_FREE_ORDER');
    $useOrderCreatedDate = Tools::getValue('OPARTSTAT_USE_ORDER_CREATED_DATE');
    $inactivCustomerDays = (int)Tools::getValue('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
    $maxVisits = (int)Tools::getValue('OPARTSTAT_MAX_VISITS');
    $maxGadsClicks = (int)Tools::getValue('OPARTSTAT_MAX_GADS_CLICKS');
    $liveTime = (int)Tools::getValue('OPARTSTAT_LIVE_TIME');
    $conversionAttributionDuration = Tools::getValue('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');
    $useSeparateDb = (int)Tools::getValue('OPARTSTAT_USE_SEPARATE_DB');
    $dbHost = Tools::getValue('OPARTSTAT_DB_HOST');
    $dbPort = Tools::getValue('OPARTSTAT_DB_PORT');
    $dbName = Tools::getValue('OPARTSTAT_DB_NAME');
    $dbUser = Tools::getValue('OPARTSTAT_DB_USER');
    $dbPass = Tools::getValue('OPARTSTAT_DB_PASS');


    if (!$validStatus)
      $this->postError[] = $this->module->l('You should choose at least one valid order status','adminopartstatsettingsglobal');
    else {
      $validStatusString = implode(",", $validStatus);
      Configuration::updateValue('OPARTSTAT_STATUS_VALID_ORDER', pSQL($validStatusString));
    }

    if (!$cancelStatus)
      $this->postError[] = $this->module->l('You should choose at least one cancel order status','adminopartstatsettingsglobal');
    else {
      $refundedStatusString = implode(",", $cancelStatus);
      Configuration::updateValue('OPARTSTAT_STATUS_REFUNDED_ORDER', htmlentities($refundedStatusString));
    }

    if ($incomingStatus) { //we dont impose incoming statu
      $incomingStatusString = implode(",", $incomingStatus);
      Configuration::updateValue('OPARTSTAT_STATUS_INCOMING_ORDER', htmlentities($incomingStatusString));
    }

    Configuration::updateValue('OPARTSTAT_EXCLUDE_SHIPPING', (int)$excludeShipping);
    Configuration::updateValue('OPARTSTAT_EXCLUDE_FREE_ORDER', (int)$excludeFreeOrder);
    Configuration::updateValue('OPARTSTAT_USE_ORDER_CREATED_DATE', (int)$useOrderCreatedDate);

    if ($inactivCustomerDays <= 0)
      $this->postError[] = $this->module->l('The number of days before a customer becomes inactive has to be greater than 0','adminopartstatsettingsglobal');
    else
      Configuration::updateValue('OPARTSTAT_INACTIV_CUSTOMER_DAYS', (int)$inactivCustomerDays);

    Configuration::updateValue('OPARTSTAT_USE_SEPARATE_DB', (int)$useSeparateDb);

      if ($useSeparateDb) {
        if (!$dbHost || !$dbName || !$dbUser) {
            $this->postError[] = $this->module->l(
                'External DB host, name and user are required when using a separate database',
                'adminopartstatsettingsglobal'
            );
            return;
        }

        $port = (string) ($dbPort ?: '3306');
        $dsn = 'mysql:host=' . $dbHost . ';port=' . $port . ';dbname=' . $dbName . ';charset=utf8mb4';

        try {
            $pdo = new PDO(
                $dsn,
                (string) $dbUser,
                (string) $dbPass,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                )
            );

            $pdo->query('SELECT 1');

            // Création table dans la DB externe
            $createSql = "
            CREATE TABLE IF NOT EXISTS `opartstat_sessions` (
              `visiteId` int(10) NOT NULL AUTO_INCREMENT,
              `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `userIp` varchar(64) NOT NULL,
              `pageUrl` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
              `device` int(1) DEFAULT NULL,
              `country` varchar(100) DEFAULT NULL,
              `referrer` varchar(250) NOT NULL,
              `userLanguage` varchar(20) NOT NULL,
              `idCart` int(10) DEFAULT NULL,
              `elementId` int(10) DEFAULT NULL,
              `controllerName` varchar(100) DEFAULT NULL,
              `shopId` int(10) DEFAULT NULL,
              `utm_medium` varchar(250) DEFAULT NULL,
              `utm_campaign` varchar(250) DEFAULT NULL,
              `gclid` varchar(255) DEFAULT NULL,
              `userId` int(12) DEFAULT NULL,
              `userAgent` text CHARACTER SET utf8mb4 DEFAULT NULL,
              PRIMARY KEY (`visiteId`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;
            ";

            $pdo->exec($createSql);

        } catch (Exception $e) {
            // Message différent selon que ça a pété sur connexion ou sur create
            // (ici on reste simple : message générique)
            $this->postError[] = $this->module->l(
                'Unable to connect to the external database or create the table. Please check credentials/host/port and user privileges (CREATE).',
                'adminopartstatsettingsglobal'
            );

            Configuration::updateValue('OPARTSTAT_USE_SEPARATE_DB', 0);
            return;
        }

        // OK => on enregistre
        Configuration::updateValue('OPARTSTAT_DB_HOST', pSQL($dbHost));
        Configuration::updateValue('OPARTSTAT_DB_PORT', pSQL($dbPort));
        Configuration::updateValue('OPARTSTAT_DB_NAME', pSQL($dbName));
        Configuration::updateValue('OPARTSTAT_DB_USER', pSQL($dbUser));

        if ($dbPass !== null && $dbPass !== '') {
            Configuration::updateValue('OPARTSTAT_DB_PASS', pSQL($dbPass));
        }
    }



    if ($maxVisits <= 0)
      $this->postError[] = $this->module->l('The maximum number of visits has to be greater than 0','adminopartstatsettingsglobal');
    else
      Configuration::updateValue('OPARTSTAT_MAX_VISITS', (int)$maxVisits);

    /*if ($maxGadsClicks <= 0)
      $this->postError[] = $this->module->l('The maximum number of Google Ads clicks has to be greater than 0','adminopartstatsettingsglobal');
    else
      Configuration::updateValue('OPARTSTAT_MAX_GADS_CLICKS', (int)$maxGadsClicks);*/

    if ($liveTime <= 0)
      $this->postError[] = $this->module->l('The live time has to be greater than 0','adminopartstatsettingsglobal');
    else
      Configuration::updateValue('OPARTSTAT_LIVE_TIME', (int)$liveTime);

    Configuration::updateValue('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION', (int)$conversionAttributionDuration);

    $this->postConf[] = $this->module->l('The settings have been updated.','adminopartstatsettingsglobal');
  }

  protected function getConfigFormValues()
  {
    $validStatus = explode(',', Configuration::get('OPARTSTAT_STATUS_VALID_ORDER'));
    foreach ($validStatus as $node)
      $fieldValues["OPARTSTAT_STATUS_VALID_ORDER[]_{$node}"] = true;

    $cancelStatus = explode(',', Configuration::get('OPARTSTAT_STATUS_REFUNDED_ORDER'));
    foreach ($cancelStatus as $node)
      $fieldValues["OPARTSTAT_STATUS_REFUNDED_ORDER[]_{$node}"] = true;

    $cancelStatus = explode(',', Configuration::get('OPARTSTAT_STATUS_INCOMING_ORDER'));
    foreach ($cancelStatus as $node)
      $fieldValues["OPARTSTAT_STATUS_INCOMING_ORDER[]_{$node}"] = true;

    $fieldValues['OPARTSTAT_EXCLUDE_SHIPPING'] = Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING');
    $fieldValues['OPARTSTAT_EXCLUDE_FREE_ORDER'] = Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER');
    $fieldValues['OPARTSTAT_USE_ORDER_CREATED_DATE'] = Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE');
    $fieldValues['OPARTSTAT_INACTIV_CUSTOMER_DAYS'] = Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
    $fieldValues['OPARTSTAT_MAX_VISITS'] = Configuration::get('OPARTSTAT_MAX_VISITS');
    $fieldValues['OPARTSTAT_MAX_GADS_CLICKS'] = Configuration::get('OPARTSTAT_MAX_GADS_CLICKS');
    $fieldValues['OPARTSTAT_LIVE_TIME'] = Configuration::get('OPARTSTAT_LIVE_TIME');
    $fieldValues['OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION'] = Configuration::get('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');
    $fieldValues['OPARTSTAT_USE_SEPARATE_DB'] = (int)Configuration::get('OPARTSTAT_USE_SEPARATE_DB');
    $fieldValues['OPARTSTAT_DB_HOST'] = Configuration::get('OPARTSTAT_DB_HOST');
    $fieldValues['OPARTSTAT_DB_PORT'] = Configuration::get('OPARTSTAT_DB_PORT');
    $fieldValues['OPARTSTAT_DB_NAME'] = Configuration::get('OPARTSTAT_DB_NAME');
    $fieldValues['OPARTSTAT_DB_USER'] = Configuration::get('OPARTSTAT_DB_USER');
    $fieldValues['OPARTSTAT_DB_PASS'] = Configuration::get('OPARTSTAT_DB_PASS');


    return $fieldValues;
  }

  private function removeOpartStatDemo()
  {
    $moduleName = 'opartstatdemo';
    $module = Module::getInstanceByName($moduleName);
    if ($module) {
      $isInstalled = false;
      if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
        $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();
        $isInstalled = $moduleManager->isInstalled($moduleName);
      } else
        $isInstalled = Module::isInstalled($moduleName);

      if ($isInstalled) {
        if (!$module->uninstall()) {
          $this->postError[] = sprintf($this->module->l('A problem occured during the uninstall of %s'), $moduleName,'adminopartstatsettingsglobal');
          return false;
        }
        $moduleDir = _PS_MODULE_DIR_ . 'opartstatdemo';
        $this->recursiveDeleteOnDisk($moduleDir);
      }
      return true;
    }
  }

  protected function recursiveDeleteOnDisk($dir)
  {
    if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
      return;
    }
    if (is_dir($dir)) {
      $objects = scandir($dir, SCANDIR_SORT_NONE);
      foreach ($objects as $object) {
        if ($object != '.' && $object != '..') {
          if (filetype($dir . '/' . $object) == 'dir') {
            $this->recursiveDeleteOnDisk($dir . '/' . $object);
          } else {
            unlink($dir . '/' . $object);
          }
        }
      }
      reset($objects);
      rmdir($dir);
    }
  }
}
