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

include(dirname(__FILE__) . '/../../classes/opartStatTools.php');
include(dirname(__FILE__) . '/../../opartstat.php');
class AdminOpartStatSettingsModulesController extends ModuleAdminController
{

  protected $postError = array();
  protected $postConf = array();
  protected $parentModuleName = 'opartstat';
  protected $tab = 'analytics_stats';

  public function __construct()
  {
    // Set variables
    $this->context = Context::getContext();
    $this->bootstrap = true;
    $this->name = 'opartstat';
    $this->ssl = Tools::usingSecureMode();
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

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('modules');
    $this->context->smarty->assign($adminLinksArray);

    if (Tools::isSubmit('linkModule'))
      $this->linkModule(Tools::getValue('linkModule'));

    $output = '';


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
      _PS_MODULE_DIR_ . $this->parentModuleName . '/views/templates/admin/settings/partial/header.tpl'
    );

    $partnerModules = $this->getAllPartnerModules();

    $this->context->smarty->assign(array(
      'partnerModules' => $partnerModules
    ));

    $output .=  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->parentModuleName . '/views/templates/admin/settings/partial/partnerModules.tpl'
    );


    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
  }

  public function getAllPartnerModules()
  {
    $logo_path = ($this->ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_) . _MODULE_DIR_ . $this->parentModuleName . '/config/metrics/partnersModules/';

    $directory = _PS_MODULE_DIR_ . $this->parentModuleName . '/config/metrics/partnersModules/';

    $subDirectories = scandir($directory);

    $partnerModules = [];
    foreach ($subDirectories as $subDirectory) {
      $dirInfos = pathinfo($subDirectory);

      if ($dirInfos['filename'] == 'index' || $dirInfos['basename'] == '.' || $dirInfos['basename'] == '..')
        continue;

      $moduleName = $dirInfos['basename'];
      include_once($directory . $moduleName . '/install/' . 'wording.php');

      $moduleTitle = $this->trans($moduleTitle, [], 'Modules.Opartstat.' . str_replace(".php", "", 'wording.php'));
      $moduleDecription = $this->trans($moduleDecription, [], 'Modules.Opartstat.' . str_replace(".php", "", 'wording.php'));
      $moduleLink = $this->trans($moduleLink, [], 'Modules.Opartstat.' . str_replace(".php", "", 'wording.php'));

      $isAvailable = false;
      $alreadyLinked = OpartStatTools::moduleIsLinked($moduleName);
      if (Module::isEnabled($moduleName) && !$alreadyLinked)
        $isAvailable = true;

      $partnerModules[$moduleName] = [
        'title' => $moduleTitle,
        'description' => $moduleDecription,
        'link' => $moduleLink,
        'logoUrl' => $logo_path . $moduleName . '/install/logo.png',
        'isAvailable' => $isAvailable,
        'name' => $moduleName,
        'alreadyLinked' => $alreadyLinked
      ];
    }
    return $partnerModules;
  }

  private function linkModule($moduleName)
  {
    if(!OpartStatTools::isGranted(get_class($this),'edit')) {
      $this->postError[] = $this->module->l('Your not allowed to link modules','adminopartstatsettingsmodules');
      return;
    }

    if (!validate::isModuleName(Tools::getValue('linkModule'))) {
      $this->postError[] = $this->module->l('Module name is not valid','adminopartstatsettingsmodules');
      return false;
    }

    if (!Module::isEnabled($moduleName)) {
      $this->postError[] = $this->module->l('This module is not enabled. Please enable it before link it to OpartStat','adminopartstatsettingsmodules');
      return false;
    }

    if (OpartStatTools::moduleIsLinked($moduleName)) {
      $this->postError[] = $this->module->l('This module is already linked.','adminopartstatsettingsmodules');
      return false;
    }

    $directory = _PS_MODULE_DIR_ . $this->parentModuleName . '/config/metrics/partnersModules/';
    include_once($directory . $moduleName . '/install/' . 'install.php');

    $className = 'opartStat_'.$moduleName;
    $installModuleClass = new $className();  

    if ($installModuleClass->install()) {
      $modulesAlreadyLinkedString = Configuration::get('OPARTSTAT_PARTNERMODULES_LINKED');
      $modulesAlreadyLinkedString = ($modulesAlreadyLinkedString == '') ? $moduleName : $modulesAlreadyLinkedString . '|' . $moduleName;
      Configuration::updateValue('OPARTSTAT_PARTNERMODULES_LINKED', $modulesAlreadyLinkedString);

      $this->postConf[] = sprintf($this->module->l('The %s module has been correctly linked'), $moduleName,'adminopartstatsettingsmodules');
      return true;
    } else {
      $this->postError[] = sprintf($this->module->l('An error occured during the linking with %s module'), $moduleName,'adminopartstatsettingsmodules');
      return false;
    }
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
}
