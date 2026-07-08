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

class AdminOpartStatSettingsAdvancedController extends ModuleAdminController
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

    if (Tools::isSubmit('submitOpartStatAdvanced')) {
        $this->_postProcess();
        opartStatTools::purgeCacheFiles(true);
    }
      

    if (Tools::isSubmit('submitCleanCache')) {
      if(opartStatTools::purgeCacheFiles(true))
        $this->postConf[] = $this->module->l('The cache has been cleaned.','adminopartstatsettingsadvanced');
    }

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('advanced');
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

    $this->context->smarty->assign('cacheFilesCount',$this->countCacheFiles());

    $output .= $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . 'opartstat/views/templates/admin/settings/partial/deleteCache.tpl'
    );

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
    $helper->submit_action = 'submitOpartStatAdvanced';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsAdvanced', true);

    $helper->tpl_vars = [
      'fields_value' => $this->getConfigFormValues(),
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    return $helper->generateForm([$this->getConfigForm()]);
  }

  protected function getConfigForm()
  {
    return array(
      'form' => array(
        'legend' => array(
          'title' => $this->module->l('Advanced settings','adminopartstatsettingsadvanced'),
          'icon' => 'icon-cogs',
        ),
        'input' => array(
          array(
            'type' => 'switch',
            'label' => $this->module->l('Active debug mode','adminopartstatsettingsadvanced'),
            'name' => 'OPARTSTAT_ACTIVE_DEBUG_MODE',
            'class' => 't',
            'is_bool' => true,
            'desc' => $this->module->l('Only activate this mode if you have problems with the module','adminopartstatsettingsadvanced'),
            'values' => array(
              array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled','adminopartstatsettingsadvanced')
              ),
              array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled','adminopartstatsettingsadvanced')
              )
            ),
          ),          
        ),
        'submit' => array(
          'title' => $this->module->l('Save','adminopartstatsettingsadvanced'),
        ),
      ),
    );
  }


  protected function _postProcess()
  {
    if (!OpartStatTools::isGranted(get_class($this), 'edit')) {
      $this->postError[] = $this->module->l('Your not allowed to edit those settings','adminopartstatsettingsadvanced');
      return;
    }
    $activeDebugMode = Tools::getValue('OPARTSTAT_ACTIVE_DEBUG_MODE');

    Configuration::updateValue('OPARTSTAT_ACTIVE_DEBUG_MODE', (int)$activeDebugMode);

    $this->postConf[] = $this->module->l('The settings have been updated.','adminopartstatsettingsadvanced');
  }

  protected function getConfigFormValues()
  {
    $fieldValues['OPARTSTAT_ACTIVE_DEBUG_MODE'] = Configuration::get('OPARTSTAT_ACTIVE_DEBUG_MODE');
    return $fieldValues;
  }

  protected function countCacheFiles()
  {
    $cacheDir = _PS_MODULE_DIR_ . 'opartstat/cache/';
    $files = scandir($cacheDir);
    $fileCount = 0;
    foreach ($files as $file) {
      if (is_file($cacheDir . $file)) {
        $fileCount++;
      }
    }
    $fileCount -2; //remove index and htaccess files
    return $fileCount;
  }
}


