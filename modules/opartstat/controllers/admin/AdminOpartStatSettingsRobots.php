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

class AdminOpartStatSettingsRobotsController extends ModuleAdminController
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

    if (Tools::isSubmit('deleteopartstat_robots'))
      $this->deleteRobot();

    if (Tools::isSubmit('submitOpartAddRobot'))
      $this->addRobot();

    /* $this->_postProcess(); */

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('robots');
    $this->context->smarty->assign($adminLinksArray);


    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName .'/views/templates/admin/settings/partial/header.tpl'
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

    //$orderStatus = $this->getOrderStatusList();

    $output .= $this->displayRobots();
    $output .= $this->renderAddRobotForm();
    $output .= $this->displayUserAgent();
    $output .= $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName .'/views/templates/admin/settings/partial/robotsFooter.tpl'
    );

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
    //return $output;
  }

  protected function renderAddRobotForm()
  {
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitOpartAddRobot';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsRobots', true);

    $helper->tpl_vars = [
      'fields_value' =>  ["robotName" => ''],
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    return $helper->generateForm(
      [array(
        'form' => array(
          'legend' => array(
            'title' => $this->module->l('Add robot to exclude','adminopartstatsettingsrobots'),
            'icon' => 'icon-cogs',
          ),
          'input' => array(
            array(
              'type' => 'text',
              'label' => $this->module->l('Robot name','adminopartstatsettingsrobots'),
              'desc' => $this->module->l('Add the name of the robot you want to exclude. It will still be able to visit your site, but its visits will no longer be counted in your statistics.','adminopartstatsettingsrobots'),
              'name' => 'robotName'
            ),
          ),
          'submit' => array(
            'title' => $this->module->l('Save','adminopartstatsettingsrobots'),
          ),
        ),
      )]
    );
  }

  public function displayRobots()
  {
    $robots = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartstat_robots` ORDER BY name asc');
    $fields_list = array(
      'name' => array(
        'title' => $this->module->l('Name','adminopartstatsettingsrobots'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      )
    );

    $helper_list = new HelperList();
    $helper_list->shopLinkType = '';
    $helper_list->no_link = true;
    $helper_list->simple_header = false;
    $helper_list->actions = array('delete');
    $helper_list->show_toolbar = true;
    $helper_list->identifier = 'robotId';
    $helper_list->table = 'opartstat_robots';
    $helper_list->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsRobots', false);
    $helper_list->token = Tools::getAdminTokenLite('AdminOpartStatSettingsRobots');
    $helper_list->title = $this->module->l('Robots','adminopartstatsettingsrobots');
    $helper_list->_defaultOrderBy = 'name';
    $helper_list->_defaultOrderWay = 'ASC';

    $helper_list->list_total = count($robots);
    $helper_list->pagination = array(
      'total' => count($robots),
      'page' => 1,
      'limit' => count($robots)
    );

    $fields_value = array(
      'delete' => array(
        'title' => $this->module->l('Delete','adminopartstatsettingsrobots'),
        'icon' => 'process-icon-delete'
      )
    );

    return $helper_list->generateList($robots, $fields_list, $fields_value);
  }

  public function deleteRobot()
  {
    if(!OpartStatTools::isGranted(get_class($this),'delete')) {
      $this->postError[] = $this->module->l('Your not allowed to delete bots','adminopartstatsettingsrobots');
      return;
    }

    $robotId = (int)Tools::getValue('robotId');
    if (Db::getInstance()->delete('opartstat_robots', '`robotId` = ' . (int)$robotId))
      $this->postConf[] = sprintf($this->module->l('The robot has been deleted.','adminopartstatsettingsrobots'));
    else
      $this->postError[] = $this->module->l('An error occurred while deleting the robot.','adminopartstatsettingsrobots');
  }

  public function addRobot()
  {
    if(!OpartStatTools::isGranted(get_class($this),'add')) {
      $this->postError[] = $this->module->l('Your not allowed to add bots','adminopartstatsettingsrobots');
      return;
    }

    $robotName = Tools::getValue('robotName');
    if ($robotName == '') {
      $this->postError[] = $this->module->l('The robot name can not be empty','adminopartstatsettingsrobots');
      return false;
    }
    if (Db::getInstance()->insert('opartstat_robots', ['name' => pSQL($robotName)]))
      $this->postConf[] = sprintf($this->module->l('The %s robot has been added.'), $robotName,'adminopartstatsettingsrobots');
    else
      $this->postError[] = $this->module->l('An error occurred while adding the robot.','adminopartstatsettingsrobots');
  }

  public function displayUserAgent()
  {    
    $userAgents = Db::getInstance()->executeS('SELECT userAgent, COUNT(userAgent) as visits FROM `' . _DB_PREFIX_ . 'opartstat_sessions` WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY userAgent');
    $fields_list = array(
      'userAgent' => array(
        'title' => $this->module->l('User Agent','adminopartstatsettingsrobots'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      ),
      'visits' => array(
        'title' => $this->module->l('Visits in the last 6 month','adminopartstatsettingsrobots'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      )
    );

    $helper_list = new HelperList();
    $helper_list->shopLinkType = '';
    $helper_list->no_link = true;
    $helper_list->simple_header = false;
    $helper_list->show_toolbar = true;
    $helper_list->identifier = 'visiteId';
    $helper_list->table = 'opartstat_sessions';
    $helper_list->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsRobots', false);
    $helper_list->token = Tools::getAdminTokenLite('AdminOpartStatSettingsRobots');
    $helper_list->title = $this->module->l('User Agent list','adminopartstatsettingsrobots');
    $helper_list->_defaultOrderBy = 'visits';
    $helper_list->_defaultOrderWay = 'DESC';

    $helper_list->list_total = count($userAgents);
    $helper_list->pagination = array(
      'total' => count($userAgents),
      'page' => 1,
      'limit' => count($userAgents)
    );

    $fields_value = array(
      'delete' => array(
        'title' => $this->module->l('Delete','adminopartstatsettingsrobots'),
        'icon' => 'process-icon-delete'
      )
    );

    return $helper_list->generateList($userAgents, $fields_list, $fields_value);
  }
}
