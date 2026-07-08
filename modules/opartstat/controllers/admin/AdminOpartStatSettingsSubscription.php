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

require_once(dirname(__FILE__) . '/../../classes/opartStatTools.php');

use Prestashop\ModuleLibMboInstaller\DependencyBuilder;

//psaccount
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class AdminOpartStatSettingsSubscriptionController extends ModuleAdminController
{
    protected $postError = array();
    protected $postConf = array();
    protected $tab = 'analytics_stats';
    public $name = 'opartstat';
    public $module;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->module = \Module::getInstanceByName('opartstat');        
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
        $adminLinksArray = OpartStatTools::getAdminMenuLinks('subscription');
        $this->context->smarty->assign($adminLinksArray);

        if (Tools::isSubmit('submitOpartShopToken'))
            $this->saveShopToken();

        if (Tools::isSubmit('submitMaxLinesPerTables')) 
            $this->saveMaxLinesPerTables();        

        $this->context->smarty->assign('langIsoCode', $this->context->language->iso_code);

        $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/subscription.js');

        $adminControllerLink = 'index.php?controller=AdminOpartStatSettingsSubscription&token=' . Tools::getAdminTokenLite('AdminOpartStatSettingsSubscription');

        $saasDomain = OpartStatTools::getSaasDomain();

        Media::addJsDef([
            'remoteAjaxUrl' => $saasDomain . 'controllers/createUser.php',
            'ajaxUrl' => $adminControllerLink . "&ajax=1"
        ]);

        $shopTokenIsValid = $this->shopTokenIsValid();
    
        $this->context->smarty->assign(array(
            'shopTokenIsValid' => $shopTokenIsValid,
            'saasDomain' => $saasDomain,
            'currentStep' => '',
            'shopUrl' => $this->getShopUrl(),
            'isLinkedToPsAccount' => false,
            'adminControllerLink' => $adminControllerLink
        ));

        if (!$shopTokenIsValid) {
            $this->context->smarty->assign(array(
                'shopTokenForm' => $this->renderShopTokenForm(),
                'currentStep' => 'getShopToken'
            ));
            $this->displayPage();
            return;
        }

        $shopHasActiveSubscription = opartStatTools::shopHasActiveSubscription();
        $this->context->smarty->assign(array(
            'shopHasActiveSubscription' => $shopHasActiveSubscription,
        ));

        $displayManageSubscriptionAndInformationslink = false;
        if (!$shopHasActiveSubscription) {    
            Configuration::updateValue('OPARTSTAT_PREMIUM_IS_ACTIVE', '0');        
            $this->context->smarty->assign([
                'currentStep' => 'chooseSubscriptionPlan',              
                'displaySubscriptionBtn' => true,
            ]);

            $this->displayPage();
            return;
        }
        else {
            Configuration::updateValue('OPARTSTAT_PREMIUM_IS_ACTIVE', '1');
            $displayManageSubscriptionAndInformationslink = true;
        }           
        
        $this->context->smarty->assign(array(
            'displayManageSubscriptionAndInformationslink' => $displayManageSubscriptionAndInformationslink
        ));

        $currentGoogleAdsAccount = $this->getCurrentGoogleAdsAccount();
        $this->context->smarty->assign(array(
            'currentGoogleAdsAccount' => $currentGoogleAdsAccount,
            'currentStep' => 'linkGoogleAdsAccount'
        ));

        if (!$currentGoogleAdsAccount) {
            $this->displayPage();
            return;
        }

        if(!$this->googleAdsTablesAlreadyExist()) 
            $this->createGoogleAdsTables();        

        $maxLinesPerTables = $this->getMaxLinesPerTables();
        $opartStatPremiumDatas = $this->getOpArtStatPremiumDatas();
        
        $this->context->smarty->assign(array(
            'allIsConfiguredCorrectly' => true,
            'opartStatPremiumDatas' => $opartStatPremiumDatas,
            'maxLinesPerTables' => $maxLinesPerTables,
            'successConfetti' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/settings/partial/successConfetti.tpl')
        ));
        
        $this->displayPage();
        return;
    }

    private function displayPage()
    {
        $output =  $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/settings/partial/header.tpl'
        );
        if (count($this->postError) > 0) {
            foreach ($this->postError as $err) {
                $output .= $this->module->displayError($err);
            }            
            $this->context->smarty->assign(array(
                'allIsConfiguredCorrectly' => false,
            ));            
        } 
        elseif (count($this->postConf) > 0) {
            foreach ($this->postConf as $conf) {
                $output .= $this->module->displayConfirmation($conf);
            }
        }
        $output .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/settings/subscription.tpl'
        );
        $this->context->smarty->assign(array(
            'content' => $this->content . $output,
        ));
    }

    private function getMaxLinesPerTables()
    {
        $response = OpartStatTools::getSaasResponse("controllers/getMaxLinesPerTables.php");
        if ($response['success'] == true)
            return $response['limits'];
        else
            return false;
    }

    protected function renderShopTokenForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOpartShopToken';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminOpartStatSettingsSubscription', true);

        $helper->tpl_vars = [
            'fields_value' =>  [
                "opartSaasShopToken" => '',
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm(
            [array(
                'form' => array(
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->l('Security shop token'),
                            'desc' => $this->l('Paste your security shop token here'),
                            'name' => 'opartSaasShopToken',
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                ),
            )]
        );
    }

    public function shopTokenIsValid()
    {
        $response = OpartStatTools::getSaasResponse("controllers/checkShopToken.php");

        if (!is_array($response) || !array_key_exists('success', $response))
            return false;

        return $response['success'];
    }

    public function saveShopToken()
    {
        if (!Tools::getIsset('opartSaasShopToken')) {
            $this->postError[] = $this->l('No token found');
            return false;
        }

        $token = Tools::getValue('opartSaasShopToken');

        if (trim($token) == "") {
            $this->postError[] = $this->l('The security token can not be empty');
            return false;
        }
        $resultSaveShopToken = OpartStatTools::saveShopToken($token);

        if ($resultSaveShopToken === true) 
            $this->postConf[] = sprintf($this->l('The shop token has been saved.'));
        else 
            $this->postError[] = $this->l('Error while saving token : ').$resultSaveShopToken['message'];        
    }

    public function displayAjaxError($msg)
    {
        return json_encode(['success' => false, 'message' => $msg]);
    }

    public function getCurrentGoogleAdsAccount()
    {
        $response = OpartStatTools::getSaasResponse("controllers/getCurrentGoogleAdsAccount.php");
        if ($response['success'] == true && $response['datas'] != "")
            return $response['datas'];
        else
            return false;
    }

    public function getShopUrl()
    {
        $shop_url = $this->context->shop->getBaseURL(true);
        $url_components = parse_url($shop_url);
        $shop_url_without_protocol = $url_components['host'] . (isset($url_components['path']) ? $url_components['path'] : '');
        $shop_url_without_protocol = rtrim($shop_url_without_protocol, '/');
        return $shop_url_without_protocol;
    }

    public function getOpArtStatPremiumDatas() {
        $langIsoCode = $this->context->language->iso_code;
        $response = OpartStatTools::getSaasResponse("controllers/getOpartStatPremiumDatas.php?isoLang=".$langIsoCode);

        if (!is_array($response))
            return false;

        return $response;
    }

    public function savePrestashopShopIdToSaas($prestashopShopId) {
        $datas = [
            'prestashopShopId' => $prestashopShopId,
        ];

        $response = OpartStatTools::getSaasResponse("controllers/savePrestashopShopId.php", $datas);

        if (!is_array($response))
            return false;

        return $response;
    }

    public function ajaxProcessCheckIfShopIsActive() {
        $res = opartStatTools::shopHasActiveSubscription();
        $res = $res ? 'true' : 'false';
        die($res);
    }

    public function saveMaxLinesPerTables() {
        $datas['maxOpartStatSession'] = str_replace(' ', '', trim(Tools::getValue('maxOpartStatSession')));
        $datas['maxGoogleAdsClicks'] = str_replace(' ', '', trim(Tools::getValue('maxGoogleAdsClicks')));

        $res = OpartStatTools::getSaasResponse("controllers/saveMaxLinesPerTables.php", $datas);

        if ($res == true) 
            $this->postConf[] = sprintf($this->l('The limits have been saved.'));
        else 
            $this->postError[] = $this->l('Error while saving limits.');        
    }

    public function createGoogleAdsTables() {
        $db = Db::getInstance();
        $sqlCampaigns = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."opartstat_googleAdsCampaigns (
            id VARCHAR(255) PRIMARY KEY,
            name VARCHAR(255),
            advertisingChannelType INT(2),
            lastUploadDate DATE DEFAULT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        
        if(!$db->execute($sqlCampaigns)) {
            $this->postError[] = $this->l('Error while creating googleAdsCampaigns table');
            return false;
        }
        
        $sqlGroups = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."opartstat_googleAdsGroups (
            id VARCHAR(255) PRIMARY KEY,
            name VARCHAR(255),
            campaignId VARCHAR(255),
            lastUploadDate DATE DEFAULT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        if(!$db->execute($sqlGroups)) {
            $this->postError[] = $this->l('Error while creating googleAdsGroups table');
            return false;
        }        

        $sqlAds = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."opartstat_googleAdsAds (
            id VARCHAR(255) PRIMARY KEY,
            name VARCHAR(255),
            groupId VARCHAR(255),
            campaignId VARCHAR(255),
            lastUploadDate DATE DEFAULT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        if(!$db->execute($sqlAds)) {
            $this->postError[] = $this->l('Error while creating googleAdsAds table');
            return false;
        }

        $sqlClicks = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."opartstat_googleAdsClicks (
            gclid VARCHAR(255) PRIMARY KEY,
            createdAt date NOT NULL,
            campaignId varchar(255) NOT NULL,
            groupId varchar(255) DEFAULT NULL,
            adId varchar(255) DEFAULT NULL,
            kw varchar(255) DEFAULT NULL,
            lastUploadDate DATE DEFAULT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        if(!$db->execute($sqlClicks)) {
            $this->postError[] = $this->l('Error while creating googleAdsClicks table');
            return false;
        }

        $sqlDailyDatas = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."opartstat_googleAdsDailyDatas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            createdAt DATE,
            updatedAt DATE,
            campaignId VARCHAR(255) NOT NULL,
            groupId VARCHAR(255),
            adId VARCHAR(255),
            costMicros BIGINT,
            clicks BIGINT,
            impressions BIGINT,
            ctr DOUBLE,
            costPerConversion DOUBLE,
            conversions DOUBLE,
            averageCpc DOUBLE,
            cpm DOUBLE,
            lastUploadDate DATE DEFAULT NULL
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8";
        if(!$db->execute($sqlDailyDatas)) {
            $this->postError[] = $this->l('Error while creating googleAdsDailyDatas table');
            return false;
        }

        return true;
    }

    public function googleAdsTablesAlreadyExist() {
        $tablesToCheck = [
            _DB_PREFIX_."opartstat_googleAdsCampaigns",
            _DB_PREFIX_."opartstat_googleAdsGroups",
            _DB_PREFIX_."opartstat_googleAdsAds",            
            _DB_PREFIX_."opartstat_googleAdsClicks",
            _DB_PREFIX_."opartstat_googleAdsDailyDatas"
        ];
        foreach ($tablesToCheck as $table) {
            $sql = "SHOW TABLES LIKE '" . $table . "'";
            $res = Db::getInstance()->executeS($sql);
            if (empty($res)) {
                return false;
            }
        }
        return true;
    }
}
