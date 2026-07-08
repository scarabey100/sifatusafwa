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

class AdminOpartStatSettingsIpsController extends ModuleAdminController
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

    if (Tools::isSubmit('deleteopartstat_ips_blocking'))
      $this->deleteIp();

    if (Tools::isSubmit('submitOpartAddIp'))
      $this->addIp();

    /* $this->_postProcess(); */

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('ips');
    $this->context->smarty->assign($adminLinksArray);

    Media::addJsDef([
      'currentUserIp' => Tools::getRemoteAddr(),
    ]);

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

    $this->context->controller->addJS(_MODULE_DIR_ . $this->moduleName . '/views/js/ipsBlocking.js');

    $output .= $this->displayIps();
    $output .= $this->renderAddIpForm();

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
    //return $output;
  }

  protected function renderAddIpForm()
  {
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitOpartAddIp';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsIps', true);

    $helper->tpl_vars = [
      'fields_value' =>  ["IP" => ''],
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    return $helper->generateForm(
      [array(
        'form' => array(
          'legend' => array(
            'title' => $this->module->l('Add IP to exclude','adminopartstatsettingsips'),
            'icon' => 'icon-cogs',
          ),
          'input' => array(
            array(
              'type' => 'text',
              'label' => $this->module->l('IP','adminopartstatsettingsips'),
              'desc' => $this->module->l('Add the IP you want to exclude from your statistic','adminopartstatsettingsips').' (<a href="#" id="addYourOwnIpBtn">'.$this->module->l('Click here to add your own IP','adminopartstatsettingsips').'</a>)',
              'name' => 'IP',
            ),
          ),
          'submit' => array(
            'title' => $this->module->l('Add','adminopartstatsettingsips'),
          ),
        ),
      )]
    );
  }

  public function displayIps()
  {
    $ips = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartstat_ips_blocking` ORDER BY ip asc');
    $fields_list = array(
      'ip' => array(
        'title' => $this->module->l('IP','adminopartstatsettingsips'),
        'type' => 'text',
        'search' => false,
        'orderby' => true
      )
    );

    $helper_list = new HelperList();
    $helper_list->shopLinkType = '';
    $helper_list->simple_header = false;
    $helper_list->actions = array('delete');
    $helper_list->show_toolbar = true;
    $helper_list->identifier = 'ipId';
    $helper_list->table = 'opartstat_ips_blocking';
    $helper_list->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsIps', false);
    $helper_list->token = Tools::getAdminTokenLite('AdminOpartStatSettingsIps');
    $helper_list->title = $this->module->l('IPs','adminopartstatsettingsips');
    $helper_list->_defaultOrderBy = 'ip';
    $helper_list->_defaultOrderWay = 'ASC';

    $helper_list->list_total = count($ips);
    $helper_list->pagination = array(
      'total' => count($ips),
      'page' => 1,
      'limit' => count($ips)
    );

    $fields_value = array(
      'delete' => array(
        'title' => $this->module->l('Delete','adminopartstatsettingsips'),
        'icon' => 'process-icon-delete'
      )
    );

    return $helper_list->generateList($ips, $fields_list, $fields_value);
  }

  public function deleteIp()
  {
    if(!OpartStatTools::isGranted(get_class($this),'delete')) {
      $this->postError[] = $this->module->l('Your not allowed to delete ips','adminopartstatsettingsips');
      return;
    }
    $ipId = (int)Tools::getValue('ipId');
    if (Db::getInstance()->delete('opartstat_ips_blocking', '`ipId` = ' . (int)$ipId))
      $this->postConf[] = sprintf($this->module->l('The IP has been deleted.','adminopartstatsettingsips'));
    else
      $this->postError[] = $this->module->l('An error occurred while deleting the IP.','adminopartstatsettingsips');
  }

  public function addIp()
  {
    if(!OpartStatTools::isGranted(get_class($this),'add')) {
      $this->postError[] = $this->module->l('Your not allowed to add ips','adminopartstatsettingsips');
      return;
    }
    $ip = trim(Tools::getValue('IP'));

    if ($ip == '') 
      $this->postError[] = $this->module->l('The IP can not be empty','adminopartstatsettingsips');

    if(!OpartSession::isIp($ip)) 
      $this->postError[] = $this->module->l('The IP is not valid','adminopartstatsettingsips');

    if(OpartSession::isBlockedIp($ip)) 
      $this->postError[] = sprintf($this->module->l('The %s IP is already blocked'),$ip,'adminopartstatsettingsips');

    if(count($this->postError)>0)
      return false;

    if (Db::getInstance()->insert('opartstat_ips_blocking', ['ip' => pSQL($ip)]))
      $this->postConf[] = sprintf($this->module->l('The %s ips has been added.'), $ip,'adminopartstatsettingsips');
    else
      $this->postError[] = $this->module->l('An error occurred while adding the ip.','adminopartstatsettingsips');
  }
}
