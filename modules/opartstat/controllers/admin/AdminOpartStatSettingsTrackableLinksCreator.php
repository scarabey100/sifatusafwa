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

class AdminOpartStatSettingsTrackableLinksCreatorController extends ModuleAdminController
{
  protected $postError = array();
  protected $postConf = array();
  protected $moduleName = 'opartstat';
  protected $tab = 'analytics_stats';
  protected $parentModuleName = 'opartstat';

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

    $adminLinksArray = OpartStatTools::getAdminMenuLinks('linksCreator');
    $this->context->smarty->assign($adminLinksArray);

    $this->context->smarty->assign([
      'formLink' => $this->context->link->getAdminLink('AdminOpartStatSettingsTrackableLinksCreator')
    ]);

    $output =  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->moduleName . '/views/templates/admin/settings/partial/header.tpl'
    );

    if (Tools::isSubmit('deleteopartstat_trackable_links_preset'))
      $this->deletePreset();

    if (Tools::isSubmit('submitSavePreset'))
      $this->_postProcess();

    if (count($this->postError) > 0) {
      foreach ($this->postError as $err) {

        $output .= $this->module->displayError($err);
      }
    } elseif (count($this->postConf) > 0) {
      foreach ($this->postConf as $conf) {
        $output .= $this->module->displayConfirmation($conf);
      }
    }

    Media::addJsDef([
      'trackableLink_jsErrorMsg_url' => $this->module->l('The url is not valid','adminopartstatsettingtrackablelinkscreator'),
      'trackableLink_jsErrorMsg_source' => $this->module->l('The source is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator'),
      'trackableLink_jsErrorMsg_medium' => $this->module->l('The medium is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator'),
      'trackableLink_jsErrorMsg_campaign' => $this->module->l('The campaign is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator'),
      'trackableLink_jsValidMsg' => $this->module->l('The link has been copied to your clipboad 👍','adminopartstatsettingtrackablelinkscreator'),
    ]);

    $this->context->controller->addJS(_MODULE_DIR_ . $this->moduleName . '/views/js/trackableLinksCreator.js');

    $savedPresets = $this->getSavedPresetsList();
    $this->context->smarty->assign([
      'savedPresets' => $savedPresets,
    ]);

    $output .=  $this->context->smarty->fetch(
      _PS_MODULE_DIR_ . $this->parentModuleName . '/views/templates/admin/settings/partial/trackableLinksCreator.tpl'
    );

    $output .= $this->displaySavedPreset();

    $this->context->smarty->assign(array(
      'content' => $this->content . $output
    ));
  }

  private function getSavedPresetsList()
  {
    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'opartstat_trackable_links_preset ORDER BY utmSource DESC';
    return  Db::getInstance()->executeS($sql);
  }

  public function displaySavedPreset()
  {
    $savedPresets = $this->getSavedPresetsList();

    $fields_list = array(
      'utmSource' => array(
        'title' => $this->module->l('Source','adminopartstatsettingtrackablelinkscreator'),
        'type' => 'text',
        'search' => true,
        'orderby' => true
      ),
      'utmMedium' => array(
        'title' => $this->module->l('Medium','adminopartstatsettingtrackablelinkscreator'),
        'type' => 'text',
        'search' => true,
        'orderby' => true
      ),
      'utmCampaign' => array(
        'title' => $this->module->l('Campaign','adminopartstatsettingtrackablelinkscreator'),
        'type' => 'text',
        'search' => true,
        'orderby' => true
      ),
    );

    $helper_list = new HelperList();
    $helper_list->shopLinkType = '';
    $helper_list->simple_header = false;
    $helper_list->actions = array('delete');
    $helper_list->show_toolbar = true;
    $helper_list->identifier = 'presetId';
    $helper_list->table = 'opartstat_trackable_links_preset';
    $helper_list->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsTrackableLinksCreator', false);
    $helper_list->token = Tools::getAdminTokenLite('AdminOpartStatSettingsTrackableLinksCreator');
    $helper_list->title = $this->module->l('Saved presets','adminopartstatsettingtrackablelinkscreator');
    $helper_list->_defaultOrderBy = 'utmSource';
    $helper_list->_defaultOrderWay = 'ASC';

    $helper_list->list_total = count($savedPresets);
    $helper_list->pagination = array(
      'total' => count($savedPresets),
      'page' => 1,
      'limit' => count($savedPresets)
    );

    $fields_value = array(
      'delete' => array(
        'title' => $this->module->l('Delete','adminopartstatsettingtrackablelinkscreator'),
        'icon' => 'process-icon-delete'
      )
    );

    return $helper_list->generateList($savedPresets, $fields_list, $fields_value);
  }

  protected function _postProcess()
  {

    if(!OpartStatTools::isGranted(get_class($this),'add')) {
      $this->postError[] = $this->module->l('Your not allowed to add tracked links','adminopartstatsettingtrackablelinkscreator');
      return;
    }

    $url = Tools::getValue('url');
    $source = Tools::getValue('source');
    $medium = Tools::getValue('medium');
    $campaign = Tools::getValue('campaign');

    $this->context->smarty->assign(array(
      'trackableLinkUrl' => $url,
      'trackableLinkSource' => $source,
      'trackableLinkMedium' => $medium,
      'trackableLinkCampaign' => $campaign
    ));

    if (!preg_match('/^([a-zA-Z0-9_\-\.]*)$/', $source))
      $this->postError[] = $this->module->l('The source is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator');

    if (!preg_match('/^([a-zA-Z0-9_\-\.]*)$/', $medium))
      $this->postError[] = $this->module->l('The medium is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator');

    if (!preg_match('/^([a-zA-Z0-9_\-\.]*)$/', $campaign))
      $this->postError[] = $this->module->l('The campaign is not valid. Only letter, numbers, -, _ and . are allowed','adminopartstatsettingtrackablelinkscreator');

    if (count($this->postError) > 0)
      return false;

    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'opartstat_trackable_links_preset (utmSource,utmMedium,utmCampaign) 
      VALUES ("' . pSQl($source) . '", "' . pSQl($medium) . '", "' . pSQl($campaign) . '")';

    if (!Db::getInstance()->execute($sql)) {
      $this->postError[] = $this->module->l('An error occured during the save process','adminopartstatsettingtrackablelinkscreator');
      return false;
    }

    $this->postConf[] = $this->module->l('The preset have been saved.','adminopartstatsettingtrackablelinkscreator');
  }

  public function deletePreset()
  {
    if(!OpartStatTools::isGranted(get_class($this),'delete')) {
      $this->postError[] = $this->module->l('Your not allowed to delete tracked links','adminopartstatsettingtrackablelinkscreator');
      return;
    }

    $savedPresetId = (int)Tools::getValue('presetId');
    if (Db::getInstance()->delete('opartstat_trackable_links_preset', '`presetId` = ' . (int)$savedPresetId))
      $this->postConf[] = sprintf($this->module->l('The preset have been deleted.','adminopartstatsettingtrackablelinkscreator'));
    else
      $this->postError[] = $this->module->l('An error occurred while deleting the preset.','adminopartstatsettingtrackablelinkscreator');
  }
}
