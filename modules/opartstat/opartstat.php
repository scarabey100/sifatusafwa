<?php

/**

 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
} 

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

include_once('classes/opartStatTools.php');
include_once('classes/opartSession.php');
class OpartStat extends Module
{
    protected $postError = array();
    protected $postConf = array();
    //protected $container;

    public function __construct()
    {

        $this->name = 'opartstat';
        $this->tab = 'analytics_stats';
        $this->version = '4.5.1';
        $this->author = 'Op\'art';
        $this->module_key = '6db1ecb06aca6da9aae14c8a41856065';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];

        $this->bootstrap = true;

        parent::__construct();

        /* if ($this->container === null) {
            $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        } */

        $this->displayName = $this->l('Opart stat : true statistic for prestashop merchant');
        $this->description = $this->l('Get reliable and usefull statistics ');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        if (!Configuration::get('OPARTSTAT_INSTALLED')) {
            $this->warning = $this->l('Opart stat module is not installed');
        }
    }

    public function install()
    {
        /* $composerPlatformPhpVersion = $this->getComposerPlatformPhpVersion();
        if($composerPlatformPhpVersion == false) {
            $this->context->controller->errors[] = $this->l('Can\'t get the composer platform php version, please contact us to fix this problem.');
            return false;
        }

        if (version_compare(PHP_VERSION, $composerPlatformPhpVersion,'<')) {
            $this->context->controller->errors[] = sprintf($this->l('Your php version is lower than %s, please contact us to get the version of the module compatible with your php version.'), $composerPlatformPhpVersion);
            return false;
        } */

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Install SQL
        $sql = include(dirname(__FILE__) . '/sql/install.php');
        $db = Db::getInstance();
        foreach ($sql as $s) {
            if (!$db->execute($s)) {
                return false;
            }
        }

        $sql = "SELECT id_order_state FROM "._DB_PREFIX_."order_state WHERE paid = 1";
        $validStatus = $db->executeS($sql);
        $validStatusString = "";
        foreach($validStatus as $validStatu) {
            $validStatusString .= ($validStatusString == "")?$validStatu['id_order_state']:",".$validStatu['id_order_state'];
        }        

        if (
            !parent::install() ||
            !Configuration::updateValue('OPARTSTAT_INSTALLED', 'true') ||
            !Configuration::updateValue('OPARTSTAT_EXCLUDE_SHIPPING', 1) ||
            !Configuration::updateValue('OPARTSTAT_EXCLUDE_FREE_ORDER', 0) ||
            !Configuration::updateValue('OPARTSTAT_USE_ORDER_CREATED_DATE', 0) ||
            !Configuration::updateValue('OPARTSTAT_INACTIV_CUSTOMER_DAYS', 365) ||
            !Configuration::updateValue('OPARTSTAT_MAX_VISITS', 100000) ||
            !Configuration::updateValue('OPARTSTAT_MAX_GADS_CLICKS', 100000) ||
            !Configuration::updateValue('OPARTSTAT_LIVE_TIME', 5) ||
            !Configuration::updateValue('OPARTSTAT_PARTNERMODULES_LINKED', '') ||
            !Configuration::updateValue('OPARTSTAT_USE_COMMISSIONS', '0') ||
            !Configuration::updateValue('OPARTSTAT_STATUS_VALID_ORDER', pSQL($validStatusString)) ||
            !Configuration::updateValue('OPARTSTAT_PREMIUM_IS_ACTIVE', '0') ||
            !Configuration::updateValue('OPARTSTAT_PURGE_CACHE_DELAY', '7') ||
            !Configuration::updateValue('OPARTSTAT_CACHE_LAST_PURGE', '0000-00-00 00:00:00') ||
            !Configuration::updateValue('OPARTSTAT_CACHE_FILE_MAX_AGE', '90') ||
            !Configuration::updateValue('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION', '72') ||
            !Configuration::updateValue('OPARTSTAT_ACTIVE_DEBUG_MODE', '0') ||
            !$this->registerHook('displayHeader') ||
            //!$this->registerHook('ActionCartSave') ||
            !$this->installTab()
        ) {
            
            return false;
        }

        //if (_PS_VERSION_ >= 1.7){
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if(!$this->registerHook('displayAdminCustomers'))
                return false;
            if(!$this->registerHook('displayAdminOrderMainBottom'))
                return false;
        }        

        if(!OpartStatTools::createSecretTokenKey())
            return false;

        //$this->getService('opartstat.ps_accounts_installer')->install();

        return true;
    }

    public function uninstall()
    {
        $modulesLinked = OpartStatTools::getAllModulesLinked();

        $directory = _PS_MODULE_DIR_ . $this->name . '/config/metrics/partnersModules/';
        foreach ($modulesLinked as $moduleName) {
            if($moduleName=="")
                continue;
            include_once($directory . $moduleName . '/install/' . 'install.php');

            $className = 'opartStat_' . $moduleName;
            $installModuleClass = new $className();

            if (!$installModuleClass->uninstall()) {
                return false;      
            }
        }

        $success = $this->uninstallTab();
        $success = Configuration::deleteByName('OPARTSTAT_INSTALLED');
        $success = Configuration::deleteByName('OPARTSTAT_STATUS_VALID_ORDER');
        $success = Configuration::deleteByName('OPARTSTAT_STATUS_REFUNDED_ORDER');
        $success = Configuration::deleteByName('OPARTSTAT_STATUS_INCOMING_ORDER');
        $success = Configuration::deleteByName('OPARTSTAT_EXCLUDE_SHIPPING');
        $success = Configuration::deleteByName('OPARTSTAT_EXCLUDE_FREE_ORDER');
        $success = Configuration::deleteByName('OPARTSTAT_USE_ORDER_CREATED_DATE');
        $success = Configuration::deleteByName('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
        $success = Configuration::deleteByName('OPARTSTAT_MAX_VISITS');
        $success = Configuration::deleteByName('OPARTSTAT_MAX_GADS_CLICKS');        
        $success = Configuration::deleteByName('OPARTSTAT_PARTNERMODULES_LINKED');
        $success = Configuration::deleteByName('OPARTSTAT_USE_COMMISSIONS');
        $success = Configuration::deleteByName('OPARTSTAT_PREMIUM_IS_ACTIVE');
        $success = Configuration::deleteByName('OPARTSTAT_PURGE_CACHE_DELAY');
        $success = Configuration::deleteByName('OPARTSTAT_CACHE_LAST_PURGE');
        $success = Configuration::deleteByName('OPARTSTAT_CACHE_FILE_MAX_AGE');
        $success = Configuration::deleteByName('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');
        $success = Configuration::deleteByName('OPARTSTAT_ACTIVE_DEBUG_MODE');

        $reportNameList = array('global', 'financial', 'trafic', 'product', 'catalog', 'customers');

        foreach ($reportNameList as $reportName) {
            $success = Configuration::deleteByName('OPARTSTAT_PRESELECTEDPERIOD_' . $reportName . '_FIRST');
            $success = Configuration::deleteByName('OPARTSTAT_PRESELECTEDPERIOD_' . $reportName . '_COMPARE');
        }

        if ($success == false)
            return false;

        $sql = include(dirname(__FILE__) . '/sql/uninstall.php');
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }
        return parent::uninstall();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        $sql = "SELECT moduleName FROM " . _DB_PREFIX_ . "opartstat_partnermodules_hook WHERE hookName = 'displayBeforeBodyClosingTag'";
        $db = Db::getInstance();
        $partnerModules = $db->executeS($sql);

        $directory = _PS_MODULE_DIR_ . $this->name . '/config/metrics/partnersModules/';
        $output = '';

        foreach ($partnerModules as $partnerModule) {
            if (!Module::isEnabled($partnerModule['moduleName']))
                continue;

            include_once($directory . $partnerModule['moduleName'] . '/install/' . 'install.php');
            $className = 'opartStat_' . $partnerModule['moduleName'];
            $installModuleClass = new $className();
            $output .= $installModuleClass->displayBeforeBodyClosingTag();
        }
        return $output;
    }

    public function hookDisplayHeader($params)
    {
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $sql = 'SELECT name from `' . _DB_PREFIX_ . 'opartstat_robots`';
            $robots = Db::getInstance()->executeS($sql);
            if (count($robots) > 0) {
                $pregString = "";
                foreach ($robots as $robot) {
                    $search = ['-', '.', '/'];
                    $replace = ['\-', '\.', '\/'];
                    $name = str_replace($search, $replace, $robot['name']);
                    $pregString .= ($pregString != "") ? "|" . $name : $name;
                }
                $pregString = '/' . $pregString . '/i';
    
                if (isset($userAgent) && preg_match($pregString, $userAgent))
                    return false;
            }
        }
        else
            $userAgent = null;

        $saveSessionUrl = $this->context->link->getModuleLink('opartstat', 'saveSession', array('ajax' => true));
        
        /* $currentUrl = $this->context->shop->getBaseURL(true, false) . $_SERVER['REQUEST_URI']; */
        $shopId = $this->context->shop->id;
        $controllerName = get_class($this->context->controller);

        $elementId = null;
        if ($controllerName == "CategoryController")
            $elementId = (int)Tools::getValue('id_category');
        if ($controllerName == "ProductController")
            $elementId = (int)Tools::getValue('id_product');
        if ($controllerName == "CmsController")
            $elementId = (int)Tools::getValue('id_cms');

        $this->context->smarty->assign(array(
            'saveSessionUrl' => $saveSessionUrl,
            'controllerName' => $controllerName,
            'elementId' => $elementId,
            'shopId' => $shopId,
            'MODULE_DIR' =>  _MODULE_DIR_,
            'userAgent' => $userAgent
        ));
        $str =  $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'opartstat/views/templates/front/saveSession.tpl'
        );        
        return $str;
    }

    /* public function hookActionCartSave($params)
    {
        
        if (defined('_PS_ADMIN_DIR_')) //if hook is called from backoffice we don't save the idCart
            return false;

        $cart = $params['cart'];
        $userIp = Tools::getRemoteAddr();
        OpartSession::addCartIdToLastVisit($userIp, $cart->id);
    } */

    public function installTab()
    {
        $res = true;

        if(!Tab::getIdFromClassName('AdminMetricsLegacyStatsController')) {
            //add old stat
            $tab1 = new Tab();
            $tab1->module = $this->name;
            $tab1->active = 1;
            $tab1->class_name = 'AdminOpartRedirectToOldStat';
            $tab1->id_parent = (int)Tab::getIdFromClassName('AdminStats');
            $tab1->position = Tab::getNewLastPosition($tab1->id_parent);
            foreach (Language::getLanguages(false) as $lang) {
                if ($lang['iso_code'] == "fr") {
                    $tab1->name[(int)$lang['id_lang']] = 'Statistiques';
                } else {
                    $tab1->name[(int)$lang['id_lang']] = 'Statistics';
                }
            }
            $res &= $tab1->add();
        }

        //add opartstat tab
        $tab2 = new Tab();
        $tab2->module = $this->name;
        $tab2->active = 1;
        $tab2->class_name = 'AdminOpartStatGlobal';
        $tab2->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab2->position = Tab::getNewLastPosition($tab2->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab2->name[(int)$lang['id_lang']] = 'OpartStat';
            } else {
                $tab2->name[(int)$lang['id_lang']] = 'OpartStat';
            }
        }
        $res &= $tab2->add();

        //add tab but hide it
        $tab3 = new Tab();
        $tab3->module = $this->name;
        $tab3->active = 0;
        $tab3->class_name = 'AdminOpartStatSettingsGlobal';
        $tab3->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab3->position = Tab::getNewLastPosition($tab3->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab3->name[(int)$lang['id_lang']] = 'OpartStat réglage généraux';
            } else {
                $tab3->name[(int)$lang['id_lang']] = 'OpartStat global settings';
            }
        }
        $res &= $tab3->add();

        //add tab but hide it
        $tab4 = new Tab();
        $tab4->module = $this->name;
        $tab4->active = 0;
        $tab4->class_name = 'AdminOpartStatSettingsRobots';
        $tab4->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab4->position = Tab::getNewLastPosition($tab4->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab4->name[(int)$lang['id_lang']] = 'OpartStat réglage des robots';
            } else {
                $tab4->name[(int)$lang['id_lang']] = 'OpartStat bots settings';
            }
        }
        $res &= $tab4->add();

        //add tab but hide it
        $tab5 = new Tab();
        $tab5->module = $this->name;
        $tab5->active = 0;
        $tab5->class_name = 'AdminOpartStatSettingsModules';
        $tab5->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab5->position = Tab::getNewLastPosition($tab5->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab5->name[(int)$lang['id_lang']] = 'OpartStat réglage des modules partenaires';
            } else {
                $tab5->name[(int)$lang['id_lang']] = 'OpartStat partners modules settings';
            }
        }
        $res &= $tab5->add();

        //add tab but hide it
        $tab6 = new Tab();
        $tab6->module = $this->name;
        $tab6->active = 0;
        $tab6->class_name = 'AdminOpartStatSettingsTrackableLinksCreator';
        $tab6->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab6->position = Tab::getNewLastPosition($tab6->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab6->name[(int)$lang['id_lang']] = 'OpartStat Création de liens trackés';
            } else {
                $tab6->name[(int)$lang['id_lang']] = 'OpartStat Trackable links creator';
            }
        }
        $res &= $tab6->add();

        //add tab but hide it
        $tab7 = new Tab();
        $tab7->module = $this->name;
        $tab7->active = 0;
        $tab7->class_name = 'AdminOpartStatSettingsIps';
        $tab7->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab7->position = Tab::getNewLastPosition($tab7->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab7->name[(int)$lang['id_lang']] = 'OpartStat Blocage d\'IP';
            } else {
                $tab7->name[(int)$lang['id_lang']] = 'OpartStat IP Blocking';
            }
        }
        $res &= $tab7->add();

        //add tab but hide it
        $tab8 = new Tab();
        $tab8->module = $this->name;
        $tab8->active = 0;
        $tab8->class_name = 'AdminOpartStatSettingsCommissions';
        $tab8->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab8->position = Tab::getNewLastPosition($tab8->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab8->name[(int)$lang['id_lang']] = 'OpartStat Frais et commissions';
            } else {
                $tab8->name[(int)$lang['id_lang']] = 'OpartStat Fees and Commissions';
            }
        }
        $res &= $tab8->add();

        //add tab but hide it
        $tab9 = new Tab();
        $tab9->module = $this->name;
        $tab9->active = 0;
        $tab9->class_name = 'AdminOpartStatSettingsAdvanced';
        $tab9->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab9->position = Tab::getNewLastPosition($tab9->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab9->name[(int)$lang['id_lang']] = 'OpartStat réglages avancés';
            } else {
                $tab9->name[(int)$lang['id_lang']] = 'OpartStat advanced settings';
            }
        }
        $res &= $tab9->add();

        //add tab but hide it
        $tab10 = new Tab();
        $tab10->module = $this->name;
        $tab10->active = 0;
        $tab10->class_name = 'AdminOpartStatSettingsSubscription';
        $tab10->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab10->position = Tab::getNewLastPosition($tab10->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab10->name[(int)$lang['id_lang']] = 'OpartStat abonnement';
            } else {
                $tab10->name[(int)$lang['id_lang']] = 'OpartStat subscription';
            }
        }
        $res &= $tab10->add();

        //add tab but hide it
        $tab11 = new Tab();
        $tab11->module = $this->name;
        $tab11->active = 0;
        $tab11->class_name = 'AdminOpartStatSubscriptionSellsPage';
        $tab11->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab11->position = Tab::getNewLastPosition($tab11->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab11->name[(int)$lang['id_lang']] = 'OpartStat s\'abonner';
            } else {
                $tab11->name[(int)$lang['id_lang']] = 'OpartStat subscribe';
            }
        }
        $res &= $tab11->add();

        return $res;
    }

    public function uninstallTab()
    {
        $res = true;

        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartRedirectToOldStat'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatGlobal'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsGlobal'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsRobots'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsModules'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsTrackableLinksCreator'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsIps'));
        $tabs[] = new Tab((int)Tab::getIdFromClassName('AdminOpartStatSettingsCommissions'));

        foreach ($tabs as $tab) {
            $res &= $tab->delete();
        }
        return $res;
    }

    public function getContent()
    {
        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOpartStatSettingsGlobal', true));
    }

    /* public function getService($serviceName) {
        $autoloadPath = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
        if ($this->container === null) {
            $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
        return $this->container->getService($serviceName);
    } */

    public function hookDisplayAdminCustomers($params)
    {
        $userId = $params['id_customer'];
        $adminControllerLink = $this->context->link->getAdminLink('AdminOpartStatGlobal', false);
        $adminControllerLink .= "&token=".Tools::getAdminTokenLite('AdminOpartStatGlobal')."&ajax=1&action=getCustomerPagesViewed";

        $this->context->smarty->assign(
            array(
                'userId' => $userId,
                'ajaxUrl' => $adminControllerLink
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/customerJourney.tpl');
    }

    public function hookDisplayAdminOrderMainBottom($params)
    {
        $orderId = $params['id_order'];
        $cartId = Order::getCartIdStatic($orderId);

        $adminControllerLink = $this->context->link->getAdminLink('AdminOpartStatGlobal', false);
        $adminControllerLink .= "&token=".Tools::getAdminTokenLite('AdminOpartStatGlobal')."&ajax=1&action=getCustomerPagesViewedBeforeOrder";
        $this->context->smarty->assign(
            array(
                'cartId' => $cartId,
                'ajaxUrl' => $adminControllerLink
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/customerJourney.tpl');
    }

    /* public function getComposerPlatformPhpVersion()
    {
        $composerJsonPath = __DIR__ . '/composer.json';
        $composerConfig = json_decode(file_get_contents($composerJsonPath), true);

        if (isset($composerConfig['config']['platform']['php'])) 
            return $composerConfig['config']['platform']['php'];            
        else 
            return false;        
    } */
}
