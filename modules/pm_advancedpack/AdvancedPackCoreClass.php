<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class AdvancedPackCoreClass extends Module
{
    const INSTALL_SQL_FILE = 'install.sql';
    const DYN_CSS_FILE = 'views/css/dynamic-{id_shop}.css';
    public static $_module_prefix = 'AP5';
    protected $_coreClassName;
    protected $_html = '';
    protected $baseConfigUrl = '';
    protected $_file_to_check = [];
    protected $_support_link = false;
    protected $_getting_started = false;
    protected $copyrightLink = [
        'link' => '',
        'img' => '//www.presta-module.com/img/logo-module.JPG',
    ];
    protected $_defaultConfiguration;
    protected $_cssMapTable;
    public function __construct()
    {
        parent::__construct();
        $this->_coreClassName = Tools::strtolower(get_class());
        $forum_url_tab = [
            'fr' => 'http://www.prestashop.com/forums/topic/372622-module-pm-advanced-pack-5/',
            'en' => 'http://www.prestashop.com/forums/topic/372623-module-pm-advanced-pack-5/',
        ];
        $forum_url = $forum_url_tab['en'];
        if ($this->context->language->iso_code == 'fr') {
            $forum_url = $forum_url_tab['fr'];
        }
        $doc_url = '#/advanced-pack';
        $this->_support_link = [
            ['link' => $forum_url, 'target' => '_blank', 'label' => $this->l('Forum topic', $this->_coreClassName)],
            
            ['link' => 'https://addons.prestashop.com/contact-form.php?id_product=1015', 'target' => '_blank', 'label' => $this->l('Support contact', $this->_coreClassName)],
        ];
    }
    public static function _isFilledArray($array)
    {
        return $array && is_array($array) && count($array);
    }
    protected static function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', [$data]);
        } else {
            return current(array_map($type . '_encode', [$data]));
        }
    }
    protected static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', [$data]);
        } else {
            return current(array_map($type . '_decode', [$data]));
        }
    }
    public static function array_cartesian($pA)
    {
        if (count($pA) == 0) {
            return [[]];
        }
        $a = array_shift($pA);
        $c = self::array_cartesian($pA);
        $r = [];
        foreach ($a as $v) {
            foreach ($c as $p) {
                $r[] = array_merge([$v], $p);
            }
        }
        return $r;
    }
    protected function installDatabase()
    {
        if (!Tools::file_exists_cache(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_FILE)) {
            return false;
        } elseif (!$sqlFile = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_FILE)) {
            return false;
        }
        $sqlFile = preg_split("/;\s*[\r\n]+/", str_replace(['PREFIX_', 'MYSQL_ENGINE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sqlFile));
        foreach ($sqlFile as $sqlQuery) {
            if (empty(trim($sqlQuery))) {
                continue;
            }
            if (!Db::getInstance()->Execute(trim($sqlQuery))) {
                return false;
            }
        }
        return true;
    }
    protected function _updateDb()
    {
        $columnsToAdd = [
            ['pm_advancedpack', 'allow_remove_product', 'tinyint(3) unsigned DEFAULT 0', 'fixed_price'],
            ['pm_advancedpack_cart_products', 'customization_infos', 'text', 'id_order'],
            ['pm_advancedpack_products_attributes', 'reduction_amount', 'decimal(20,6) unsigned DEFAULT "0.000000"', 'id_product_attribute'],
            ['pm_advancedpack_products_attributes', 'reduction_type', 'enum("amount","percentage") DEFAULT NULL', 'reduction_amount'],
            ['pm_advancedpack_cart_products', 'cleaned', 'tinyint(3) unsigned DEFAULT 0', 'customization_infos'],
            ['pm_advancedpack', 'is_bundle', 'tinyint(1) unsigned DEFAULT 0'],
            ['pm_advancedpack', 'bundle_datas', 'text'],
        ];
        foreach ($columnsToAdd as $columnInfos) {
            $this->_columnExists($columnInfos[0], $columnInfos[1], true, $columnInfos[2], isset($columnInfos[3]) ? $columnInfos[3] : false);
        }
        $this->installDatabase();
        $orderPackIndex = Db::getInstance()->executeS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedpack_cart_products` WHERE `Key_name` = "order_pack"');
        if (!empty($orderPackIndex)) {
            Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedpack_cart_products` DROP INDEX `order_pack`, ADD INDEX `order_pack_2` (`id_order`, `id_pack`, `cleaned`)');
        }
        $packIpaIndex = Db::getInstance()->executeS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedpack_cart_products` WHERE `Key_name` = "pack_ipa"');
        if (empty($packIpaIndex)) {
            Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedpack_cart_products` ADD INDEX `pack_ipa` (`id_pack`, `id_product_pack`, `id_product_attribute_pack`)');
        }
    }
    public function _checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true, $firstInstall = false)
    {
        if (!$updateDb && $this->version != Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false)) {
            return false;
        }
        if ($firstInstall) {
        }
        if ($updateDb) {
            if (!$firstInstall) {
                try {
                    $this->installOverrides();
                } catch (Exception $e) {
                    $this->context->controller->errors[] = sprintf('Unable to install override: %s', $e->getMessage());
                    $this->uninstallOverrides();
                }
            }
            if (method_exists($this, 'registerNewHooks')) {
                $this->registerNewHooks(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), $this->version);
            }
            Configuration::updateValue('PM_' . self::$_module_prefix . '_LAST_VERSION', $this->version);
            if (!Configuration::getGlobalValue('PM_AP5_SECURE_KEY')) {
                Configuration::updateGlobalValue('PM_AP5_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
            }
            $this->_updateDb();
            $config = $this->_getModuleConfiguration();
            foreach ($this->_defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            $this->_setModuleConfiguration($config);
            AdvancedPack::clearAP5Cache();
            $this->cleanModuleDatas();
            $this->_generateCSS();
            if ($displayConfirm) {
                $this->context->controller->confirmations[] = $this->l('Module updated successfully', $this->_coreClassName);
            }
        }
        return true;
    }
    protected function _columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $columnsList = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . $table . '`', true, false);
        foreach ($columnsList as $columnRow) {
            if ($columnRow['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $type . ' ' . ($insertAfter ? ' AFTER `' . $insertAfter . '`' : '') . '')) {
            return true;
        }
        return false;
    }
    protected function _checkPermissions()
    {
        if (self::_isFilledArray($this->_file_to_check)) {
            $errors = [];
            foreach ($this->_file_to_check as $fileOrDir) {
                if (!is_writable(dirname(__FILE__) . '/' . $fileOrDir)) {
                    $errors[] = dirname(__FILE__) . '/' . $fileOrDir;
                }
            }
            if (!count($errors)) {
                return true;
            } else {
                $vars = [
                    'permission_errors' => $errors,
                ];
                $this->context->controller->errors[] = $this->fetchTemplate('core/permissions_check.tpl', $vars);
                return false;
            }
        }
        return true;
    }
    protected function showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_' . self::$_module_prefix . '_DISMISS_RATING');
        if ($show && $dismiss != 1 && $this->getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    private function getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM ' . _DB_PREFIX_ . 'configuration
                WHERE name = \'' . pSQL('PM_' . self::$_module_prefix . '_LAST_VERSION') . '\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    protected function fetchTemplate($tpl, $customVars = [], $configOptions = [])
    {
        $this->context->smarty->assign([
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'current_iso_lang' => $this->context->language->iso_code,
            'current_id_lang' => (int)$this->context->language->id,
            'options' => $configOptions,
            'base_config_url' => $this->baseConfigUrl,
        ]);
        if (count($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
    
    protected function getPMdata()
    {
        $param = [];
        $param[] = 'ver-' . _PS_VERSION_;
        $param[] = 'current-' . $this->name;
        
        $result = $this->getPMAddons();
        if ($result && is_array($result) && count($result)) {
            foreach ($result as $moduleName => $moduleVersion) {
                $param[] = $moduleName . '-' . $moduleVersion;
            }
        }
        return $this->getDataSerialized(implode('|', $param));
    }
    protected function getPMAddons()
    {
        $pmAddons = [];
        $result = Db::getInstance()->ExecuteS('SELECT DISTINCT name FROM ' . _DB_PREFIX_ . 'module WHERE name LIKE "pm_%"');
        if ($result && is_array($result) && count($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if (!empty($instance) && !empty($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    protected function doHttpRequest($data = [], $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge([
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->context->language->iso_code),
            'iso_code' => Tools::strtolower(Country::getIsoById((int)Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ], $data);
        $postData = http_build_query($data);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            ],
        ]);
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = json_decode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    protected function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_AM');
        $modules_date = (int)Configuration::get('PM_' . self::$_module_prefix . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return json_decode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return [];
        }
        $dataToStore = [];
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = [
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            ];
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AM', json_encode($dataToStore));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AMD', time());
        return json_decode(Configuration::get('PM_' . self::$_module_prefix . '_AM'), true);
    }
    protected function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_PMM');
        $modules_date = (int)Configuration::get('PM_' . self::$_module_prefix . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return json_decode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(['list' => $this->getPMAddons()], 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return [];
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMM', json_encode($jsonResponse));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMMD', time());
        return json_decode(Configuration::get('PM_' . self::$_module_prefix . '_PMM'), true);
    }
    protected function shuffleArray(&$a)
    {
        if (is_array($a) && count($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = [];
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    protected function displaySupport()
    {
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = [];
        }
        if (!is_array($pm_products)) {
            $pm_products = [];
        }
        $this->shuffleArray($pm_addons_products);
        if (is_array($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && is_array($pm_products['ignoreList']) && count($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && is_array($addonsList) && count($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = [
            'support_links' => (is_array($this->_support_link) && count($this->_support_link) ? $this->_support_link : []),
            'copyright_link' => (is_array($this->copyrightLink) && count($this->copyrightLink) ? $this->copyrightLink : false),
            'pm_module_version' => $this->version,
            'pm_data' => $this->getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
        ];
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    public function _getModuleConfiguration()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            $config = json_decode($conf, true);
            foreach ($this->_defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            return $config;
        } else {
            return $this->_defaultConfiguration;
        }
    }
    public static function getModuleConfigurationStatic()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            return json_decode($conf, true);
        } else {
            return [];
        }
    }
    protected function _setModuleConfiguration($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', json_encode($newConf));
    }
    public static function setModuleConfigurationStatic($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', json_encode($newConf));
    }
    protected function _setDefaultConfiguration()
    {
        if (!is_array($this->_getModuleConfiguration()) || !count($this->_getModuleConfiguration())) {
            Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', json_encode($this->_defaultConfiguration));
        }
        return true;
    }
    public function getContent()
    {
        $this->context->controller->addJqueryUI('ui.tabs');
        $this->context->controller->addJqueryPlugin('chosen');
        $this->context->controller->addCSS($this->_path . 'views/css/colpick.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/jquery.tiptip.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/colpick.min.js');
        $this->context->controller->addCSS($this->_path . 'views/css/admin-module.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin-module.js');
        $this->baseConfigUrl = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name;
    }
    public static function _getCssRule($selector, $rule, $value, $is_important = false, $params = false, &$css_rules = [])
    {
        $css_rule = '';
        if ((is_array($value) && count($value)) || (Tools::strlen($value) > 0 && $value != '')) {
            switch ($rule) {
                case 'keyframes_spin':
                case 'bg_gradient':
                    if (!is_array($value)) {
                        $val = explode('-', $value);
                    } else {
                        $val = $value;
                    }
                    if (isset($val[1]) && $val[1]) {
                        $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                        $color2 = htmlentities($val[1], ENT_COMPAT, 'UTF-8');
                    } elseif (isset($val[0]) && $val[0]) {
                        $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                    }
                    if (!isset($color1)) {
                        return '';
                    }
                    if ($rule == 'bg_gradient') {
                        $css_rule .= 'background:' . $color1 . ($is_important ? '!important' : '') . ';';
                        if (isset($color2)) {
                            $css_rule .= 'background: -webkit-gradient(linear, 0 0, 0 bottom, from(' . $color1 . '), to(' . $color2 . '))' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -webkit-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -moz-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -ms-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -o-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= '-pie-background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                        }
                    } elseif ($rule == 'keyframes_spin') {
                        if (!isset($color2)) {
                            $color2 = $color1;
                        }
                        $css_rule .= '@keyframes ap5loader { 0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em ' . $color2 . '; } 40% { box-shadow: 0 2.5em 0 0 ' . $color1 . '; } }';
                        $css_rule .= '@-webkit-keyframes ap5loader { 0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em ' . $color2 . '; } 40% { box-shadow: 0 2.5em 0 0 ' . $color1 . '; } } ';
                    }
                    break;
                case 'color':
                    $css_rule .= 'color:' . $value . ($is_important ? '!important' : '') . ';';
                    break;
            }
        }
        if (!isset($css_rules[$selector])) {
            $css_rules[$selector] = [];
        }
        $css_rules[$selector][] = $css_rule;
        return $css_rules;
    }
    protected function _generateCSS()
    {
        $advanced_styles = '';
        $css_rules_array = [];
        $config = $this->_getModuleConfiguration();
        foreach ($this->_cssMapTable as $var => $cssRules) {
            foreach ($cssRules as $cssRule) {
                self::_getCssRule($cssRule['selector'], $cssRule['type'], $config[$var], true, false, $css_rules_array);
            }
        }
        if (self::_isFilledArray($css_rules_array)) {
            foreach ($css_rules_array as $selector => $rules) {
                if (self::_isFilledArray($rules)) {
                    if (preg_match('/keyframes_/i', $selector)) {
                        $advanced_styles .= implode('', $rules) . "\n";
                    } else {
                        $advanced_styles .= $selector . ' {' . implode('', $rules) . '}' . "\n";
                    }
                }
            }
        }
        $dynamic_css_file = str_replace('{id_shop}', (string)$this->context->shop->id, self::DYN_CSS_FILE);
        $advanced_styles .= "\n" . $this->_getAdvancedStylesDb();
        if (is_writable(dirname(__FILE__) . '/views/css/')) {
            file_put_contents(dirname(__FILE__) . '/' . $dynamic_css_file, $advanced_styles);
        } else {
            if (!is_writable(dirname(__FILE__) . '/views/css/')) {
                $this->context->controller->errors[] = $this->l('Please set write permision to folder:', $this->_coreClassName) . ' ' . dirname(__FILE__) . '/views/css/';
            } elseif (!is_writable(dirname(__FILE__) . '/' . $dynamic_css_file)) {
                $this->context->controller->errors[] = $this->l('Please set write permision to file:', $this->_coreClassName) . ' ' . dirname(__FILE__) . '/' . $dynamic_css_file;
            }
        }
    }
    protected function _updateAdvancedStyles($css_styles)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_ADVANCED_STYLES', self::getDataSerialized(trim($css_styles)));
        $this->_generateCSS();
    }
    protected function _getAdvancedStylesDb()
    {
        $advanced_css_file_db = Configuration::get('PM_' . self::$_module_prefix . '_ADVANCED_STYLES');
        if ($advanced_css_file_db !== false) {
            return self::getDataUnserialized($advanced_css_file_db);
        }
        return false;
    }
    public static function getThumbnailImageHTML($idProduct, $idImage = null)
    {
        if (empty($idImage)) {
            $idImage = Product::getCover($idProduct);
            if (is_array($idImage) && !empty($idImage['id_image'])) {
                $idImage = (int)$idImage['id_image'];
            }
        }
        $image = new Image((int)$idImage);
        $imageType = Context::getContext()->controller->imageType;
        $imagePath = _PS_IMG_DIR_ . 'p/' . $image->getExistingImgPath() . '.' . $imageType;
        $imageManager = new PrestaShop\PrestaShop\Adapter\ImageManager(new PrestaShop\PrestaShop\Adapter\LegacyContext());
        return $imageManager->getThumbnailForListing($idImage);
    }
    protected function removeJSFromController($jsFile)
    {
        if (method_exists($this->context->controller, 'removeJS')) {
            $this->context->controller->removeJS($jsFile);
        } else {
            $jsPath = Media::getJSPath($jsFile);
            if ($jsPath && array_search($jsPath, $this->context->controller->js_files) !== false) {
                unset($this->context->controller->js_files[array_search($jsPath, $this->context->controller->js_files)]);
            }
        }
    }
    protected static $_sortArrayByKeyColumn = null;
    protected static $_sortArrayByKeyOrder = null;
    protected function sortArrayByKey($a, $b)
    {
        if ($a[self::$_sortArrayByKeyColumn] > $b[self::$_sortArrayByKeyColumn]) {
            return self::$_sortArrayByKeyOrder == 1 ? 1 : -1;
        } elseif ($a[self::$_sortArrayByKeyColumn] < $b[self::$_sortArrayByKeyColumn]) {
            return self::$_sortArrayByKeyOrder == 1 ? -1 : 1;
        }
        return 0;
    }
    public function getPrestaShopTemplateVersion()
    {
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
            return Tools::substr(_PS_VERSION_, 0, 1);
        }
        return Tools::substr(_PS_VERSION_, 0, 3);
    }
    protected function getCategoryTreeForSelect()
    {
        $categoryList = Category::getCategories((int)$this->context->language->id);
        $categorySelect = $categoryParentSelect = $alreadyAdd = [];
        $rootCategoryId = Configuration::get('PS_ROOT_CATEGORY');
        foreach ($categoryList as $shopCategory) {
            foreach ($shopCategory as $idCategory => $categoryInformations) {
                if ($rootCategoryId == $idCategory) {
                    continue;
                }
                $categoryParentSelect[$categoryInformations['infos']['id_parent']][$idCategory] = str_repeat('&#150 ', max(0, $categoryInformations['infos']['level_depth'] - 1)) . $categoryInformations['infos']['name'];
            }
        }
        foreach ($categoryList as $shopCategory) {
            foreach ($shopCategory as $idCategory => $categoryInformations) {
                if ($rootCategoryId == $idCategory || in_array($idCategory, $alreadyAdd)) {
                    continue;
                }
                $categorySelect[$idCategory] = [
                    'value' => (int)$idCategory,
                    'name' => (string)str_repeat('&#150 ', max(0, $categoryInformations['infos']['level_depth'] - 1)) . $categoryInformations['infos']['name'],
                ];
                if (isset($categoryParentSelect[$idCategory])) {
                    foreach ($categoryParentSelect[$idCategory] as $idCategoryChild => $categoryLabel) {
                        $categorySelect[$idCategoryChild] = [
                            'value' => (int)$idCategoryChild,
                            'name' => (string)$categoryLabel,
                        ];
                        $alreadyAdd[] = $idCategoryChild;
                    }
                }
            }
        }
        return $categorySelect;
    }
    protected function displayTitle($type, $text, $classes = '', $withHr = false)
    {
        $vars = [
            'title_type' => $type,
            'title_classes' => $classes,
            'title_text' => $text,
            'with_hr' => $withHr,
        ];
        return $this->fetchTemplate('core/title.tpl', $vars);
    }
}
