<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

require_once(dirname(__FILE__).'/classes/ets_pa_defines.php');
if (version_compare(_PS_VERSION_, '1.7', '>=')) {
    interface WidgetCaptcha extends PrestaShop\PrestaShop\Core\Module\WidgetInterface
    {
    }
} else {
    interface WidgetCaptcha
    {
    }
}
if (!defined('_PS_ETS_CAPTCHA_LOG_DIR_')) {
    if (file_exists(_PS_ROOT_DIR_ . '/var/logs')) {
        define('_PS_ETS_CAPTCHA_LOG_DIR_', _PS_ROOT_DIR_ . '/var/logs/');
        define('_PS_ETS_CAPTCHA_LOG_', __PS_BASE_URI__ . 'var/logs/');
    } else {
        define('_PS_ETS_CAPTCHA_LOG_DIR_', _PS_ROOT_DIR_ . '/log/');
        define('_PS_ETS_CAPTCHA_LOG_', __PS_BASE_URI__ . '/log/');
    }
}

class Ets_advancedcaptcha extends Module implements WidgetCaptcha
{
    const SEND_CONFIRMATION_EMAIL = 'CONTACTFORM_SEND_CONFIRMATION_EMAIL';
    const SEND_NOTIFICATION_EMAIL = 'CONTACTFORM_SEND_NOTIFICATION_EMAIL';
    const PREFIX_CODE = 'pa_';
    const CACERT_LOCATION = 'https://curl.haxx.se/ca/cacert.pem';
    public static $log_file = 'ets_advancecaptcha_install.log';
    public $_html = '';
    public $is17;
    public $is16;
    public $_errors = array();
    protected $contact;
    protected $customer_thread;
    public $trans;
    public $captchaType = array();
    public $configs = array();
    public $overrideDir = array();
    public $newsletter = true;
    public $out_of_stock = true;
    public $fields_form = [];
    public function __construct()
    {
        $this->name = 'ets_advancedcaptcha';
        $this->tab = 'front_office_features';
        $this->version = '1.4.8';
        $this->author = 'PrestaHero';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '23c752bc1234e5c89322801b5d4a9117';
        parent::__construct();
        $this->displayName = $this->l('CAPTCHA - reCAPTCHA');
        $this->description = $this->l('Protect your store from spam messages and spam user accounts');
        if (version_compare(_PS_VERSION_, '1.5.6.0', '>') || version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => _PS_VERSION_);
        }
        $this->is17 = version_compare(_PS_VERSION_, '1.7.0', '>=');
        $this->is16 = version_compare(_PS_VERSION_, '1.6.0', '>=');
        if ($this->is17) {
            $this->overrideDir = array('form', 'ps_emailsubscription', 'ps_emailalerts');
        } elseif (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
            $this->overrideDir = array('front', 'blocknewsletter', 'mailalerts');
        } else
            $this->overrideDir = array('front');

        //order opc.
        if (!$this->is17 && (int)Configuration::get('PS_ORDER_PROCESS_TYPE')) {
	        if (Configuration::get('PA_CAPTCHA_TYPE') == 'google') {
		        ConfigurationCore::updateValue('PA_CAPTCHA_TYPE', 'colorful');
	        }
            $this->captchaPos('login');
        }
    }

	public function captchaPos($key, $class = '')
    {
        if ($class && $this->overrideDir) {
            $ik = 0;
            foreach ($this->overrideDir as $overrideClass) {
                if ($overrideClass == $class)
                    unset($this->overrideDir[$ik]);
                $ik++;
            }
        }

        if (!($result = Configuration::get('PA_CAPTCHA_POSITION')))
            return false;
        $positions = explode(',', $result);
        $override = _PS_OVERRIDE_DIR_ . 'modules' . DIRECTORY_SEPARATOR . $class . DIRECTORY_SEPARATOR . $class . '.php';
        $values = array();
        foreach ($positions as $position) {
            if ($key != $position || ($class && @file_exists($override)))
                $values[] = $position;
        }
        return Configuration::updateValue('PA_CAPTCHA_POSITION', ($values ? implode(',', $values) : ''), true);
    }

    public function overrideClass()
    {
        $dst = _PS_ROOT_DIR_ . '/override';
        if (!@file_exists($dst))
            return true;
        $src = dirname(__FILE__) . '/override';
        if (glob($src . DIRECTORY_SEPARATOR . '*')) {
            $this->recurseCopy($src, $dst);
        }
        $this->generateIndex();
    }

    public function recurseCopy($src, $dst)
    {
        if (!file_exists($src))
            return true;
        if (!is_dir($dst) && in_array(basename($dst, '.php'), $this->overrideDir))
            mkdir($dst);
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..') && basename($src . DIRECTORY_SEPARATOR . $file, '.php') != 'index') {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    $this->recurseCopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } elseif (!file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                    file_put_contents($dst . DIRECTORY_SEPARATOR . $file, '');
                }
            }
        }
        closedir($dir);
    }

    public function registerHooks()
    {
        $res = true;
        if ($hooks = Ets_pa_defines::getHooks()) {
            foreach ($hooks as $hook)
                $res &= $this->registerHook($hook);
        }
        return $res;
    }

    public function install()
    {
//        $this->overrideClass();

        $this->regexTemplates();

        return parent::install()
            && $this->registerHooks()
            && $this->installConfig();
    }

    public function regexTemplates()
    {
        /*
		* For: Prestashop 1.7 - 8
		* - Theme: themes/classic/modules/contactform/views/templates/widget/contacform.tpl
		* - Regex: (<\/section>\s*<footer(?:[^>]+?form-footer[^>]*?)>)/ms;
		* - Replace: {hook h='displayPaCaptcha' posTo='contact'}$1
		* For: Prestashop 1.6 & 1.5
		* - Theme: /themes/default-bootstrap/contact-form.tpl
		* - Regex: (\{if\s+\$fileupload\s*==\s*1\}(?:.+\"fileUpload\".+?)\{\/if\})/ms
		* - Replace: {hook h='displayPaCaptcha' posTo='contact'}$1
		*/
        $tpl_contact = ($this->is17 ? 'modules/contactform/views/templates/widget/contactform' : 'contact-form') . '.tpl';
        $tpl_contact = (file_exists(_PS_THEME_DIR_ . $tpl_contact) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_contact;
        if (!file_exists($tpl_contact . '.backup') && file_exists($tpl_contact)) {
            $tpl_content = Tools::file_get_contents($tpl_contact);
            if (!preg_match('/\{hook[^\}]+?displayPaCaptcha[^\}]+?\}/ms', $tpl_content) && copy($tpl_contact, $tpl_contact . '.backup')) {
                $pattern = $this->is17 ? '/(<\/section>\s*<footer(?:[^>]+?form-footer[^>]*?)>)/ms' : '/(\{if\s+\$fileupload\s*==\s*1\}(?:.+\"fileUpload\".+?)\{\/if\})/ms';
                $tpl_content = preg_replace(
                    $pattern,
                    '{hook h=\'displayPaCaptcha\' posTo=\'contact\'}$1',
                    $tpl_content
                );
                file_put_contents($tpl_contact, $tpl_content);
            }
        }
        /*
         * 2. themes/classic/templates/customer/_partials/login-form.tpl:
         * For: Prestashop 1.7 - 8
         * - Regex: (<div\s+class\s*=\s*"[^"]*?forgot-password[^"]*?">)/ms;
         * - Replace: {hook h='displayPaCaptcha' posTo='login'}
         * For: Prestashop 1.6 & 1.5
         * - Regex: (<p[^>]+?lost_password[^>]*?>(?:.+?)<\/p>)/ms
         * - Replace: {hook h='displayPaCaptcha' posTo='login'}$1
         */
        $tpl_login = ($this->is17 ? 'templates/customer/_partials/login-form' : 'authentication') . '.tpl';
        $tpl_login = (file_exists(_PS_THEME_DIR_ . $tpl_login) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_login;
        if (!file_exists($tpl_login . '.backup') && file_exists($tpl_login)) {
            $tpl_content = Tools::file_get_contents($tpl_login);
            if (!preg_match('/\{hook[^\}]+?displayPaCaptcha[^\}]+?\}/ms', $tpl_content) && copy($tpl_login, $tpl_login . '.backup')) {
                $pattern = $this->is17
                    ? '/(<div\s+class\s*=\s*"[^"]*?forgot-password[^"]*?"\s*>)/ms'
                    : '/(<p[^>]+?lost_password[^>]*?>.*?<\/p>)/ms';

                $tpl_content = preg_replace(
                    $pattern,
                    '{hook h=\'displayPaCaptcha\' posTo=\'login\'}$1',
                    $tpl_content
                );

                file_put_contents($tpl_login, $tpl_content);
            }
        }
        /*3. themes/classic/modules/ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl
        * For: Prestashop 1.7 - 8
        * - Regex: (<div[^>]+?>\s*\{if\s+\$conditions\s*\})/ms;
        * - {hook h='displayPaCaptcha' posTo='newsletter'}$1
        * For: Prestashop 1.6 & 1.5
        * - Theme: /themes/default-bootstrap/modules/blocknewsletter/blocknewsletter.tpl
        * - Regex : 1.6 - (<button[^>]+?submit[^>]+?>(?:.+?)<\/button>)/ms || 1.5 - (<input[^>]+?submitNewsletter[^>]+?\/>)/ms
        * - Replace: $1{hook h='displayPaCaptcha' posTo='newsletter'}
        */
        $tpl_newsletter = 'modules/' . ($this->is17 ? 'ps_emailsubscription/views/templates/hook/ps_emailsubscription' : 'blocknewsletter/blocknewsletter') . '.tpl';
        $tpl_newsletter = (file_exists(_PS_THEME_DIR_ . $tpl_newsletter) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_newsletter;
        if (!file_exists($tpl_newsletter . '.backup') && file_exists($tpl_newsletter)) {
            // Đọc nội dung file
            $tpl_content = Tools::file_get_contents($tpl_newsletter);

            // Kiểm tra xem hook đã tồn tại trong file chưa
            if (!preg_match('/\{hook[^\}]+?displayPaCaptcha[^\}]+?\}/ms', $tpl_content)) {
                // Tạo bản sao lưu
                if (@copy($tpl_newsletter, $tpl_newsletter . '.backup')) {
                    // Xác định pattern dựa trên phiên bản
                    if ($this->is17) {
                        $pattern = '/(<div[^>]+?>\s*\{if\s+\$conditions\s*\})/ms';
                        $before = '';
                        $after = '$1';
                    } elseif ($this->is16) {
                        $pattern = '/(<button[^>]+?submit[^>]+?>(?:.+?)<\/button>)/ms';
                        $before = '$1';
                        $after = '';
                    } else {
                        $pattern = '/(<input[^>]+?submitNewsletter[^>]+?\/>)/ms';
                        $before = '$1';
                        $after = '';
                    }

                    // Thêm hook vào nội dung
                    $tpl_content = preg_replace(
                        $pattern,
                        $before . '{hook h=\'displayPaCaptcha\' posTo=\'newsletter\'}' . $after,
                        $tpl_content
                    );

                    // Ghi nội dung mới vào file
                    file_put_contents($tpl_newsletter, $tpl_content);
                } else {
                    // Xử lý lỗi nếu không thể sao lưu file
                    error_log('Failed to create backup file: ' . $tpl_newsletter . '.backup');
                }
            }
        }
        /* 5. themes/classic/templates/customer/password-email.tpl
        * - Regex : (<\s*\/\s*section\s*>)/ms
         *
        * - using < instead of [, > instead of ]
         *
        * - Replace: [div class="form-group captcha-fields"]{hook h='displayPaCaptcha' posTo='pwd_recovery'}[/div]$1
        * 1.6 & 1.5
        * - Theme: /themes/default-bootstrap/password.tpl
        * - Regex : (<p[^>]+?submit[^>]+?>(?:.+?submit.+?)<\/p>)/ms;
        * - Replace: {hook h='displayPaCaptcha' posTo='pwd_recovery'}$1
        */
        $tpl_password = ($this->is17 ? 'templates/customer/password-email' : 'password') . '.tpl';
        $tpl_password = (file_exists(_PS_THEME_DIR_ . $tpl_password) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_password;
        if (!file_exists($tpl_password . '.backup') && file_exists($tpl_password)) {
            $tpl_content = Tools::file_get_contents($tpl_password);
            if (!preg_match('#\{hook[^\}]+?displayPaCaptcha[^\}]+?\}#ms', $tpl_content) && copy($tpl_password, $tpl_password . '.backup')) {
                $pattern = $this->is17 ? '/(<\s*\/\s*section\s*>)/ms' : '/(<p[^>]+?submit[^>]+?>(?:.+?submit.+?)<\/p>)/ms';
                $before = $this->is17 ? '<'.'d'.'i'.'v'.' class="'.'form-group captcha-fields'.'"'.'>' : '';
                $after = ($this->is17 ? '<'.'/'.'d'.'i'.'v'.'>' : '') . '$1';
                $tpl_content = preg_replace(
                    $pattern,
                    $before . '{hook h=\'displayPaCaptcha\' posTo=\'pwd_recovery\'}' . $after,
                    $tpl_content
                );
                file_put_contents($tpl_password, $tpl_content);
            }
        }
        /*
        * 4. modules/ps_emailalerts/views/templates/hook/product.tpl
        * - modules/mailalerts/views/templates/hook/product.tpl
        */
        $tpl_product = ($this->is17 ? 'ps_emailalerts' : 'mailalerts') . '/views/templates/hook/product.tpl';
        $tpl_product = file_exists(_PS_THEME_DIR_ . 'modules/' . $tpl_product) ? _PS_THEME_DIR_ . 'modules/' . $tpl_product : (file_exists(_PS_PARENT_THEME_DIR_ . 'modules/' . $tpl_product) ? _PS_PARENT_THEME_DIR_ . 'modules/' . $tpl_product : _PS_MODULE_DIR_ . $tpl_product);
        if (!file_exists($tpl_product . '.backup') && file_exists($tpl_product)) {
            $tpl_content = Tools::file_get_contents($tpl_product);
            if (!preg_match('#\{hook[^\}]+?displayPaCaptcha[^\}]+?\}#ms', $tpl_content) && @copy($tpl_product, $tpl_product . '.backup')) {
                $tpl_content = preg_replace(
                    '/(\{if[^\}]+?email[^\}]+?\}(?:.+?)\{\/if\})/ms',
                    '$1{hook h=\'displayPaCaptcha\' posTo=\'out_of_stock\'}',
                    $tpl_content
                );
                $tpl_content = preg_replace('/(onclick(?:.+?))(addNotification\((?:.*?)\))/ms', '$1(typeof func_pa !== typeof undefined && func_pa.$2 || $2)' . ($this->is17 ? '' : '16'), $tpl_content);
                $tpl_content = preg_replace('/\bjs-mailalert-add\b/', 'js-mailalert-add-custom', $tpl_content);
                file_put_contents($tpl_product, $tpl_content);
            }
        }
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallConfig() && $this->clearLogInstall();
    }

    public function installConfig($upgrade = false)
    {
        $languages = Language::getLanguages(false);
        if ($configs = Ets_pa_defines::getInstance()->getConfigs()) {
            foreach ($configs as $key => $config) {
            	$default = isset($config['default']) && $config['default'] ? $config['default'] : '';
                if (isset($config['lang']) && $config['lang']) {
                    $values = array();
                    foreach ($languages as $lang) {
                        $values[$lang['id_lang']] = $default;
                    }
                    if ($upgrade && !Configuration::hasKey($key) || !$upgrade) {
                        Configuration::updateValue($key, $values, true);
                    }
                } else if ($upgrade && !Configuration::hasKey($key) || !$upgrade) {
                    Configuration::updateValue($key, $default, true);
                }
            }
        }
        if (!$upgrade) {
            Configuration::updateValue('PS_DISABLE_OVERRIDES', 0);
        }
        return true;
    }

    protected function uninstallConfig()
    {
        if ($configs = Ets_pa_defines::getInstance()->getConfigs()) {
            foreach ($configs as $key => $config) {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCss(array(
                $this->_path . 'views/css/admin.css',
            ), 'all');
        }
    }

    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl" => array(
                    "allow_self_signed" => true,
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }

	/**
	 * @param string $controller
	 * @return bool
	 */
    public function isControllerSupportCaptcha($controller) {
    	if ($controller) {
    		return in_array($controller, Ets_pa_defines::getControllers());
	    }
    	return false;
    }

    public function hookDisplayHeader()
    {
    	$this->clearCacheWhenNeed();
        $controller = trim(Tools::getValue('controller'));
        $pos = explode(',', Configuration::get('PA_CAPTCHA_POSITION'));
        $create_account = (int)Tools::getValue('create_account');
        $module = Tools::getValue('module');
        if (!$pos)
        	return '';

        if
        (
	        (in_array('newsletter', $pos) && $this->newsletter) ||
	        ($controller === 'product' && $this->out_of_stock && in_array('out_of_stock', $pos)) ||
	        (($controller !== 'product' && $this->isControllerSupportCaptcha($controller)) &&
	        (
		        ($controller === 'password' && in_array('pwd_recovery', $pos)) ||
		        ($controller === 'authentication' || $controller == 'registration') && ($create_account != 1 && in_array('login', $pos) || ($create_account == 1 || $controller == 'registration') && in_array('register', $pos)) ||
		        $controller === 'order' && $module != 'ets_onepagecheckout' && in_array('register', $pos) ||
		        $controller === 'contact' && in_array('contact', $pos) && $module != 'ets_contactform7'
	        ))
        ){
        	if ($controller == 'authentication' && $create_account && !in_array('register', $pos))
        		return '';
            $this->context->controller->addJS($this->_path . 'views/js/front.js');
            $this->context->controller->addCSS($this->_path . 'views/css/front.css', 'all');
            if (($captcha_type = Configuration::get('PA_CAPTCHA_TYPE')) == 'google' || $captcha_type == 'google_v3') {
            	if (!$this->isCached('head.tpl', $this->_getCacheId())) {
		            $this->smarty->assign(array(
			            'PA_CAPTCHA_TYPE' => $captcha_type,
			            'PA_GOOGLE_CAPTCHA_SITE_KEY' => Configuration::get('PA_GOOGLE_CAPTCHA_SITE_KEY'),
			            'PA_GOOGLE_CAPTCHA_THEME' => trim(Configuration::get('PA_GOOGLE_CAPTCHA_THEME')),
			            'PA_GOOGLE_V3_CAPTCHA_SITE_KEY' => Configuration::get('PA_GOOGLE_V3_CAPTCHA_SITE_KEY'),
			            'PA_GOOGLE_V3_POSITION' => trim(Configuration::get('PA_GOOGLE_V3_POSITION')),
			            'hl' => $this->context->language->iso_code
		            ));
	            }
                return $this->display(__FILE__, 'head.tpl', $this->_getCacheId());
            }
        }
    }

    public function getConfigs($js = false)
    {
        $cacheID = $this->_getCacheId();
        if ($js && $this->isCached('js.tpl', $cacheID)) {
            return $this->display(__FILE__, 'js.tpl', $cacheID);
        }
        else
        {
            $configsDefines = Ets_pa_defines::getInstance()->getConfigs();
            $configs = array();
            foreach ($configsDefines as $key => $val) {
                if (isset($val['js']) && $val['js']) {
                    $configs[$key] = array(
                        'value' => Configuration::get($key, (isset($val['lang']) && $val['lang'] ? $this->context->language->id : null)),
                        'type' => $val['js']
                    );
                } else
                    $configs[$key] = Configuration::get($key, (isset($val['lang']) && $val['lang'] ? $this->context->language->id : null));
            }
            if ($js) {
                $this->smarty->assign('configs', $configs);
                return $this->display(__FILE__, 'js.tpl', $cacheID);
            }
            return $configs;
        }

    }

    public function required($key)
    {
        if (!$key)
            return false;
        $captcha_type = trim(Tools::getValue('PA_CAPTCHA_TYPE'));
        $value = trim(Tools::getValue($key, ''));
        if (!Validate::isCleanHtml($value))
            return false;
        switch ($key) {
            case 'PA_GOOGLE_CAPTCHA_SITE_KEY':
            case 'PA_GOOGLE_CAPTCHA_SECRET_KEY':
                if ($captcha_type === 'google' && $value == '')
                    return true;
                break;
            case 'PA_GOOGLE_V3_CAPTCHA_SITE_KEY':
            case 'PA_GOOGLE_V3_CAPTCHA_SECRET_KEY':
            case 'PA_GOOGLE_V3_CAPTCHA_SCORE':
                if ($captcha_type === 'google_v3' && $value == '')
                    return true;
                break;
            default:
                if ($value == '')
                    return true;
        }
        return false;
    }

    public function postConfig(&$errors)
    {
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $configs = Ets_pa_defines::getInstance()->getConfigs();
        if (Tools::isSubmit('pa_captcha_clear_log')) {
            $this->clearLogInstall();
            if (Tools::getValue('pa_captcha_clear_log'))
                Tools::redirectAdmin($this->getAdminLink());
        }
        elseif (Tools::isSubmit('pa_captcha_button_yes')) {
            Configuration::updateValue('PA_CAPTCHA_ERROR_IS_FIXED', 1);
        }
        elseif (Tools::isSubmit('saveConfig')) {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && trim(Tools::getValue($key . '_' . $id_lang_default) == '')) {
                            $errors[] = $config['label'] . ' ' . $this->l('is required');
                        }
                    } else {
                        if (isset($config['required']) && $config['required'] && $config['type'] != 'switch' && $this->required($key)) {
                            $errors[] = $config['label'] . ' ' . $this->l('is required');
                        } elseif (isset($config['validate']) && method_exists('Validate', $config['validate'])) {
                            $validate = $config['validate'];
                            if (!Validate::$validate(trim(Tools::getValue($key))))
                                $errors[] = $config['label'] . ' ' . $this->l('is invalid');
                            unset($validate);
                        } elseif (!is_array(Tools::getValue($key)) && !Validate::isCleanHtml(trim(Tools::getValue($key)))) {
                            $errors[] = $config['label'] . ' ' . $this->l('is invalid');
                        } elseif ($key == 'PA_CAPTCHA_IP_BLACKLIST' && ($ip_blacklist = trim(Tools::getValue($key))) != '' && !preg_match('/^(([0-9A-Fa-f\.\*:])+(\n|(\r\n))*)+$/', $ip_blacklist)) {
                            $errors[] = $config['label'] . ' ' . $this->l('is invalid');
                        } elseif ($key == 'PA_CAPTCHA_EMAIL_BLACKLIST' && ($email_blacklist = trim(Tools::getValue($key))) != '' && !preg_match('/^(([a-z0-9\*@\-\._])+(\n|(\r\n))*)+$/i', $email_blacklist)) {
                            $errors[] = $config['label'] . ' ' . $this->l('is invalid');
                        } elseif ($key == 'PA_GOOGLE_V3_CAPTCHA_SCORE' && trim(Tools::getValue('PA_CAPTCHA_TYPE')) == 'google_v3') {
	                        $score = (float) trim(Tools::getValue($key));
	                        if ($score <= 0 || $score > 1)
                                $errors[] = $config['label'] . ' ' . $this->l('must be a number within the range greater than 0 and less than or equal to 1.');
                        }
                    }
                }
            }
            if (!$errors) {
                if ($configs) {
                	$this->clearCacheWhenSaveConfig();
                    foreach ($configs as $key => $config) {
                        if (isset($config['lang']) && $config['lang']) {
                            $values = array();
                            foreach ($languages as $lang) {
                                if ($config['type'] == 'switch')
                                    $values[$lang['id_lang']] = (int)trim(Tools::getValue($key . '_' . $lang['id_lang'])) ? 1 : 0;
                                else
                                    $values[$lang['id_lang']] = trim(Tools::getValue($key . '_' . $lang['id_lang'])) ? trim(Tools::getValue($key . '_' . $lang['id_lang'])) : trim(Tools::getValue($key . '_' . $id_lang_default));
                            }
                            Configuration::updateValue($key, $values, true);
                        } else {
                            if ($config['type'] == 'switch') {
                                Configuration::updateValue($key, (int)trim(Tools::getValue($key)) ? 1 : 0, true);
                            } elseif ($config['type'] == 'pa_checkbox' || ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple'])) {
                                $value = implode(',', Tools::getValue($key, array()));
                                if (version_compare(_PS_VERSION_, '1.6.1', '<') && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && Shop::getContext() !== Shop::CONTEXT_ALL) {
                                    $idConfig = (int)Configuration::getIdByName($key, (int)$this->context->shop->id_shop_group, (int)$this->context->shop->id);
                                    $configuration = new Configuration($idConfig);
                                    if (!$idConfig) {
                                        $configuration->name = $key;
                                        $configuration->id_shop = (int)$this->context->shop->id;
                                        $configuration->id_shop_group = (int)$this->context->shop->id_shop_group;
                                    }
                                    $configuration->value = $value;
                                    if ($configuration->save(true, true)) {
                                        Configuration::set($key, $value, (int)$this->context->shop->id_shop_group, (int)$this->context->shop->id);
                                    }
                                } else {
                                    $res = Configuration::updateValue($key, $value, true);
                                }
                            } else {
                                Configuration::updateValue($key, trim(Tools::getValue($key)), true);
                            }
                        }
                    }
                    $moduleLink = $this->getAdminLink(4);
                    Tools::redirectAdmin($moduleLink);
                }
            }
        }
    }
    public function clearCacheWhenNeed() {
    	if (Configuration::get('PA_CAPTCHA_NEED_CLEAR_CACHE')) {
    		$this->_clearCache('*');
    		Configuration::updateValue('PA_CAPTCHA_NEED_CLEAR_CACHE', 0);
	    }
    }

    public function clearCacheWhenSaveConfig() {
	    $this->_clearCache('*');
	    Configuration::updateValue('PA_CAPTCHA_NEED_CLEAR_CACHE', 1);
    }

    public function renderForm()
    {
        $configs = Ets_pa_defines::getInstance()->getConfigs();
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Captcha settings'),
                    'icon' => 'icon-AdminAdmin'
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        if ($configs) {
            foreach ($configs as $key => $config) {
                if (!(isset($config['unset'])) || !$config['unset']) {
                    $confFields = $config;
                    $confFields['name'] = $key;
                    if (isset($config['type']) && $config['type'] == 'switch') {
                        $confFields['values'] = array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        );
                    } elseif ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple'] && stripos($confFields['name'], '[]') === false)
                        $confFields['name'] .= '[]';

                    $fields_form['form']['input'][] = $confFields;
                }
            }
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveConfig';
        $helper->currentIndex = $this->getAdminLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $fields = array();
        $languages = Language::getLanguages(false);
        $helper->override_folder = '/';
        if (Tools::isSubmit('saveConfig')) {
            if ($configs) {
                foreach ($configs as $key => $config) {
	                $default = isset($config['default']) && $config['default'] ? $config['default'] : '';
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Tools::getValue($key . '_' . $l['id_lang'], $default);
                        }
                    } elseif ($config['type'] == 'pa_checkbox' || ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple'])) {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = Tools::getValue($key, array());
                    } else
                        $fields[$key] = Tools::getValue($key, $default);
                }
            }
        } else {
            if ($configs) {
                foreach ($configs as $key => $config) {
                    if (isset($config['lang']) && $config['lang']) {
                        foreach ($languages as $l) {
                            $fields[$key][$l['id_lang']] = Configuration::get($key, $l['id_lang']);
                        }
                    } elseif ($config['type'] == 'pa_checkbox' || ($config['type'] == 'select' && isset($config['multiple']) && $config['multiple'])) {
                        $fields[$key . ($config['type'] == 'select' ? '[]' : '')] = ($result = Configuration::get($key)) != '' ? explode(',', $result) : array();
                    } else
                        $fields[$key] = Configuration::get($key);
                }
            }
        }
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $fields,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'path' => $this->_path,
            'is15' => !$this->is16,
            'old_version' => version_compare(_PS_VERSION_, '1.6.0.7', '<'),
            'log_install' => $this->displayLogInstall(),
            'refsLink' => isset($this->refs) ? $this->refs . $this->context->language->iso_code : false,
        );
        $this->_html .= $helper->generateForm(array($fields_form));
    }

    public static function getBaseModLink()
    {
        $context = Context::getContext();
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $context->shop->domain . $context->shop->getBaseURI();
    }

    public function generateHTML($key)
    {
        $this->smarty->assign(array(
            'key' => $key,
            'is17' => $this->is17,
            'path' => $this->_path,
        ));
        return $this->display(__FILE__, 'gen-html.tpl');
    }

    public function getContent()
    {
	    if(!$this->active)
	    {
		    return $this->displayWarning(sprintf($this->l('You must enable "%s" module to configure its features'),$this->displayName));
	    }
	    $this->context->smarty->assign(array(
		    'root_dir' => $this->_path,
	    ));
	    $this->postConfig($this->_errors);
	    $this->renderForm();
	    $this->smarty->assign(array(
		    'html' => $this->_html,
		    'base_dir' => $this->_path,
		    'is16' => $this->is16,
	    ));
	    if (count($this->_errors))
		    $this->context->controller->errors = $this->_errors;
        return $this->display(__FILE__, 'bo-form.tpl');
    }

    public function getAdminLink($conf = 0)
    {
        if ($this->is16)
            return $this->context->link->getAdminLink('AdminModules', true) . ($conf ? '&conf=' . $conf : '') . '&configure=' . $this->name;
        else
            return AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . ($conf ? '&conf=' . $conf : '') . '&configure=' . $this->name;
    }

    public function hookDisplayPaCaptcha($params)
    {
        $page = trim(Tools::getValue('controller'));
        if(!$params)
        {
            $params['posTo'] = $page;
        }
        if (($page) && Validate::isControllerName($page) && isset($params['posTo']) && $params['posTo'] && $this->hookVal($page, $params['posTo'])) {
            $params['page'] = $page;
            $params['rand'] = md5(rand());
            return $this->captchaPro($params);
        }
    }

    public function captchaPro($params)
    {
        if (!(isset($params['rand'])) || !$params['rand'] || !(isset($params['posTo'])) || !$params['posTo'])
            return false;
        $this->smarty->assign(array_merge(array(
            'captcha_page' => $params['page'] ?: 'index',
            'captcha_image' => $this->context->link->getModuleLink($this->name, 'captcha', array('pos' => $params['posTo'], 'rand' => $params['rand']), Tools::usingSecureMode()),
            'rand' => $params['rand'],
            'modules_dir' => _MODULE_DIR_,
            'is16' => $this->is16,
            'is17' => $this->is17,
            'hl' => $this->context->language->iso_code,
            'posTo' => $params['posTo'] ?: false,
        ), $this->getConfigs()));
        return $this->display(__FILE__, 'captcha.tpl');
    }

    public function hookDisplayCustomerAccountForm()
    {
        return $this->hookDisplayPaCaptcha(array(
            'posTo' => 'register'
        ));
    }

    public function hookDisplayReassurance()
    {
        if ($this->is17 && Tools::getValue('controller', false) === 'order') {
            return $this->hookDisplayPaCaptcha(array(
                'posTo' => 'login'
            ));
        }
    }

    public function hookDisplayOverrideTemplate($params)
    {
        if (!empty($params['template_file'])) {
            if (Tools::strpos($params['template_file'], 'contact') !== false) {
                return $this->getTemplatePath('contact.tpl');
            }
        }
    }

    public function renderWidget($hookName = null, array $configuration = array())
    {
        if ($hookName == null && ($page = Tools::getValue('controller')) && Tools::strpos($page, 'contact') !== false) {
            $this->context->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            if ($contactForm = Module::getInstanceByName('contactform')) {
                return $contactForm->display($contactForm->getLocalPath(), 'views/templates/widget/contactform.tpl');
            }
        }
    }

    protected function createNewToken()
    {
        $this->context->cookie->contactFormToken = md5(uniqid());
        $this->context->cookie->contactFormTokenTTL = time() + 600;

        return $this;
    }

    public function getWidgetVariables($hookName = null, array $configuration = array())
    {
        $contact = array();
        $notifications = false;
        if (Tools::isSubmit('submitMessage')) {
            $this->sendMessage();

            if (!empty($this->context->controller->errors)) {
                $notifications['messages'] = $this->context->controller->errors;
                $notifications['nw_error'] = true;
            } elseif (!empty($this->context->controller->success)) {
                $notifications['messages'] = $this->context->controller->success;
                $notifications['nw_error'] = false;
            }
        } elseif (empty($this->context->cookie->contactFormToken)
            || empty($this->context->cookie->contactFormTokenTTL)
            || $this->context->cookie->contactFormTokenTTL < time()
        ) {
            $this->createNewToken();
        }
        if (($id_customer_thread = (int)Tools::getValue('id_customer_thread')) && ($token = Tools::getValue('token')) && Validate::isCleanHtml($token)) {
            $cm = new CustomerThread($id_customer_thread);
            if ($cm->token === $token) {
                $this->customer_thread = $this->context->controller->objectPresenter->present($cm);
                $order = new Order((int)$this->customer_thread['id_order']);
                if (Validate::isLoadedObject($order)) {
                    $this->customer_thread['reference'] = $order->getUniqReference();
                }
            }
        }

        $contact['contacts'] = $this->getTemplateVarContact();
        $contact['message'] = html_entity_decode(Tools::getValue('message'));
        $contact['allow_file_upload'] = (bool)Configuration::get('PS_CUSTOMER_SERVICE_FILE_UPLOAD');

        if (!(bool)Configuration::isCatalogMode()) {
            $contact['orders'] = $this->getTemplateVarOrders();
        } else {
            $contact['orders'] = array();
        }

        if ($this->customer_thread && isset($this->customer_thread['email']) && $this->customer_thread['email']) {
            $contact['email'] = $this->customer_thread['email'];
        } else {
            $contact['email'] = Tools::safeOutput(Tools::getValue('from', ((isset($this->context->cookie) && isset($this->context->cookie->email) && Validate::isEmail($this->context->cookie->email)) ? $this->context->cookie->email : '')));
        }
        unset($hookName);
        unset($configuration);
        return array(
            'contact' => $contact,
            'notifications' => $notifications,
            'token' => $this->context->cookie->contactFormToken,
            'id_module' => (int)Module::getInstanceByName('contactform')->id,
        );
    }

	public function fileAttachment($file, $return_content = true) {
		$file_attachment = null;
		if (isset($file) && !empty($file['name']) && !empty($file['tmp_name'])) {
			$file_attachment['rename'] = uniqid() . Tools::strtolower(substr($file['name'], -5));
			if ($return_content) {
				$file_attachment['content'] = Tools::file_get_contents($file['tmp_name']);
			}
			$file_attachment['tmp_name'] = $file['tmp_name'];
			$file_attachment['name'] = $file['name'];
			$file_attachment['mime'] = $file['type'];
			$file_attachment['error'] = $file['error'];
			$file_attachment['size'] = $file['size'];
		}

		return $file_attachment;
	}

	public function saveCustomerMessage($id_customer, $message, $file_attachments) {
		$res = false;
		$cm = new CustomerMessage();
		$cm->id_customer_thread = $id_customer;
		$cm->message = $message;

		$file_names = '';
        if ($file_attachments) {
            for ($i = 0, $iMax = count($file_attachments); $i < $iMax; $i++) {
                if ((isset($file_attachments[$i]['rename']) && !empty($file_attachments[$i]['rename'])) && rename($file_attachments[$i]['tmp_name'], _PS_UPLOAD_DIR_ . basename($file_attachments[$i]['rename']))) {
                    $file_names .= $file_attachments[$i]['rename'] . ';';
                    @chmod(_PS_UPLOAD_DIR_ . basename($file_attachments[$i]['rename']), 0664);
                }
            }
        }
        $cm->file_name = $file_names;
		$cm->ip_address = (int)ip2long(Tools::getRemoteAddr());
		$cm->user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (!$cm->add()) {
			$res = $this->l('An error occurred while sending the message.');
		}
		return $res;
	}

    public function sendMessage()
    {
        if ($this->hookVal('contact', 'contact')) {
            $this->captchaVal($this->context->controller->errors);
        }
        if ($this->context->controller->errors)
            return false;
        $extension = array('.txt', '.rtf', '.doc', '.docx', '.pdf', '.zip', '.png', '.jpeg', '.gif', '.jpg');
	    $file_attachments = [];
        if (isset($_FILES['fileUpload'])
            && ($files = $_FILES['fileUpload'])
            && is_array($files)
            && count($files)
            && ((isset($files['name']) && is_array($files['name'])) || !isset($files['name']))) {
        	foreach ($files as $file) {
		        $file_attachments[] = $this->fileAttachment($file);
	        }
	        $file_attachment = $file_attachments[0];
        } else {
	        $file_attachment = $this->fileAttachment($_FILES['fileUpload']);
        }
        $message = Tools::getValue('message');

        if (!($from = trim(Tools::getValue('from'))) || !Validate::isEmail($from)) {
            $this->context->controller->errors[] = $this->l('Invalid email address.');
        } elseif (!$message) {
            $this->context->controller->errors[] = $this->l('The message cannot be blank.');
        } elseif (!Validate::isCleanHtml($message)) {
            $this->context->controller->errors[] = $this->l('Invalid message');
        } elseif (!($id_contact = (int)Tools::getValue('id_contact')) || !(Validate::isLoadedObject($contact = new Contact($id_contact, $this->context->language->id)))) {
            $this->context->controller->errors[] = $this->l('Please select a subject from the list provided. ');
        } elseif (!empty($file_attachment['name']) && $file_attachment['error'] != 0) {
            $this->context->controller->errors[] = $this->l('An error occurred during the file-upload process.');
        } elseif (!empty($file_attachment['name']) && !in_array(Tools::strtolower(Tools::substr($file_attachment['name'], -4)), $extension) && !in_array(Tools::strtolower(Tools::substr($file_attachment['name'], -5)), $extension)) {
            $this->context->controller->errors[] = $this->l('Bad file extension');
        } else {
            $customer = $this->context->customer;
            if (!$customer->id) {
                $customer->getByEmail($from);
            }

            $id_order = (int)Tools::getValue('id_order');

            $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($from, $id_order);

            if (!empty($contact->customer_service)) {
                if ((int)$id_customer_thread) {
                    $ct = new CustomerThread($id_customer_thread);
                    $ct->status = 'open';
                    $ct->id_lang = (int)$this->context->language->id;
                    $ct->id_contact = (int)$id_contact;
                    $ct->id_order = (int)$id_order;
                    if ($id_product = (int)Tools::getValue('id_product')) {
                        $ct->id_product = $id_product;
                    }
                    $ct->update();
                } else {
                    $ct = new CustomerThread();
                    if (isset($customer->id)) {
                        $ct->id_customer = (int)$customer->id;
                    }
                    $ct->id_shop = (int)$this->context->shop->id;
                    $ct->id_order = (int)$id_order;
                    if ($id_product = (int)Tools::getValue('id_product')) {
                        $ct->id_product = $id_product;
                    }
                    $ct->id_contact = (int)$id_contact;
                    $ct->id_lang = (int)$this->context->language->id;
                    $ct->email = $from;
                    $ct->status = 'open';
                    $ct->token = Tools::passwdGen(12);
                    $ct->add();
                }

                if ($ct->id) {

                    $lastMessage = CustomerMessage::getLastMessageForCustomerThread($ct->id);
                    if ($file_attachments && count($file_attachments)) {
	                    $testFileUpload = (isset($file_attachments[0]['rename']) && !empty($file_attachments[0]['rename']));
                    } else {
	                    $testFileUpload = (isset($file_attachment['rename']) && !empty($file_attachment['rename']));
                    }

                    // if last message is the same as new message (and no file upload), do not consider this contact
                    if ($lastMessage != $message || $testFileUpload) {
                    	$res = $this->saveCustomerMessage($ct->id, $message, $file_attachments && count($file_attachments) ? $file_attachments[0] : [$file_attachment]);
                    	if ($res)
                    		$this->context->controller->errors[] = $res;
                    } else {
                        $mailAlreadySend = true;
                    }
                } else {
                    $this->context->controller->errors[] = $this->l('An error occurred while sending the message.');
                }
            }
            $sendConfirmationEmail = Module::isEnabled('contactform') && (Configuration::hasKey(self::SEND_CONFIRMATION_EMAIL) || Configuration::hasKey(self::SEND_CONFIRMATION_EMAIL, null, $this->context->shop->id_shop_group, $this->context->shop->id)) ? (int)Configuration::get(self::SEND_CONFIRMATION_EMAIL) : 1;
            $sendNotificationEmail = Module::isEnabled('contactform') && (Configuration::hasKey(self::SEND_NOTIFICATION_EMAIL) || Configuration::hasKey(self::SEND_NOTIFICATION_EMAIL, null, $this->context->shop->id_shop_group, $this->context->shop->id)) ? (int)Configuration::get(self::SEND_NOTIFICATION_EMAIL) : 1;

            if (!count($this->context->controller->errors)
                && empty($mailAlreadySend)
                && ($sendConfirmationEmail || $sendNotificationEmail)
            ) {
                $var_list = array(
                    '{order_name}' => '-',
                    '{attached_file}' => '-',
                    '{message}' => Tools::nl2br(Tools::stripslashes($message)),
                    '{email}' => $from,
                    '{product_name}' => '',
                );

                if (isset($file_attachment['name'])) {
                    $var_list['{attached_file}'] = $file_attachment['name'];
                }

                $id_product = (int)Tools::getValue('id_product');

                if (isset($ct) && Validate::isLoadedObject($ct) && $ct->id_order) {
                    $order = new Order((int)$ct->id_order);
                    $var_list['{order_name}'] = $order->getUniqReference();
                    $var_list['{id_order}'] = (int)$order->id;
                }

                if ($id_product) {
                    $product = new Product((int)$id_product);
                    if (Validate::isLoadedObject($product) && isset($product->name[Context::getContext()->language->id])) {
                        $var_list['{product_name}'] = $product->name[Context::getContext()->language->id];
                    }
                }

                if (empty($contact->email) && $sendConfirmationEmail) {
                    Mail::Send(
                        $this->context->language->id,
                        'contact_form',
                        ((isset($ct) && Validate::isLoadedObject($ct)) ? sprintf($this->l('Your message has been correctly sent #ct%s #tc%s'), $ct->id, $ct->token) : $this->l('Your message has been correctly sent')),
                        $var_list,
                        $from,
                        null,
                        null,
                        null,
                        $file_attachment
                    );
                } elseif ( !empty($contact->email)) {
                    if ($sendNotificationEmail && !Mail::Send(
                            $this->context->language->id,
                            'contact',
                            $this->l('Message from contact form') . ' [no_sync]',
                            $var_list,
                            isset($contact->email) ? $contact->email : null,
                            isset($contact->name) ? $contact->name:null,
                            null,
                            null,
                            $file_attachment,
                            null,
                            _PS_MAIL_DIR_,
                            false,
                            null,
                            null,
                            $from
                        ) || $sendConfirmationEmail && !Mail::Send(
                            $this->context->language->id,
                            'contact_form',
                            ((isset($ct) && Validate::isLoadedObject($ct)) ? sprintf($this->l('Your message has been correctly sent #ct%s #tc%s'), $ct->id, $ct->token) : $this->l('Your message has been correctly sent')),
                            $var_list,
                            $from,
                            null,
                            null,
                            null,
                            $file_attachment,
                            null,
                            _PS_MAIL_DIR_,
                            false,
                            null,
                            null,
                            $contact->email
                        )) {
                        $this->context->controller->errors[] = $this->l('An error occurred while sending the message.');
                    }
                }
            }

            if (!count($this->context->controller->errors)) {
                $this->context->controller->success[] = $this->l('Your message has been successfully sent to our team.');
            }
        }
    }

    public function getTemplateVarContact()
    {
        $contacts = array();
        $all_contacts = Contact::getContacts($this->context->language->id);

        foreach ($all_contacts as $one_contact) {
            $contacts[$one_contact['id_contact']] = $one_contact;
        }

        if ($this->customer_thread && isset($this->customer_thread['id_contact']) && $this->customer_thread['id_contact']) {
            $contacts_arr = array();
            $contacts_arr[] = $contacts[$this->customer_thread['id_contact']];
            return $contacts_arr;
        }

        return $contacts;
    }

    public function getTemplateVarOrders()
    {
        $orders = array();

        if (!isset($this->customer_thread['id_order']) && $this->context->customer->isLogged()) {
            $customer_orders = Order::getCustomerOrders($this->context->customer->id);
            foreach ($customer_orders as $customer_order) {
                $myOrder = new Order((int)$customer_order['id_order']);
                if (Validate::isLoadedObject($myOrder)) {
                    $orders[$customer_order['id_order']] = $customer_order;
                    $orders[$customer_order['id_order']]['products'] = $myOrder->getProducts();
                }
            }
        } elseif ($this->customer_thread && isset($this->customer_thread['id_order']) && (int)$this->customer_thread['id_order'] > 0) {
            $myOrder = new Order($this->customer_thread['id_order']);
            if (Validate::isLoadedObject($myOrder)) {
                $orders[$myOrder->id] = $this->context->controller->objectPresenter->present($myOrder);
                $orders[$myOrder->id]['id_order'] = $myOrder->id;
                $orders[$myOrder->id]['products'] = $myOrder->getProducts();
            }
        }

        if ($this->customer_thread && isset($this->customer_thread) && $this->customer_thread['id_product']) {
            $id_order = 0;
            if (isset($this->customer_thread['id_order'])) {
                $id_order = (int)$this->customer_thread['id_order'];
            }
            $orders[$id_order]['products'][(int)$this->customer_thread['id_product']] = $this->context->controller->objectPresenter->present(new Product((int)$this->customer_thread['id_product']));
        }

        return $orders;
    }

    public function hookVal($page, $posTo)
    {
        if ($this->context->customer && $this->context->customer->isLogged() && Configuration::get('PA_CAPTCHA_OFF_CUSTOMER_LOGIN') || !$posTo) {
            return false;
        }
        $position = ($result = Configuration::get('PA_CAPTCHA_POSITION')) != '' ? explode(',', $result) : false;
        if (!$position)
            return false;
        if (in_array($posTo, $position)) {
            switch ($posTo) {
                case 'newsletter':
                    if ($this->newsletter)
                        return true;
                    break;
                case 'out_of_stock':
                    if ($page == 'product' && $this->out_of_stock)
                        return true;
                    break;
                case 'register':
                case 'login':
	                $module = Tools::getValue('module');
                    if (
                    	($this->is17 || !Configuration::get('PS_ORDER_PROCESS_TYPE') || Configuration::get('PA_CAPTCHA_TYPE') != 'google')
	                    && $this->isControllerSupportCaptcha($page)
                    ) {
                    	if ($module == 'ets_onepagecheckout' && Tools::getValue('fc') == 'module' && $page === 'order')
                    		return false;
	                    return true;
                    }
                    break;
                default:
                    if ($this->isControllerSupportCaptcha($page))
                        return true;
                    break;
            }
        }
        return false;
    }

    public function captchaVal(&$errors)
    {
        if (($captcha_type = Configuration::get('PA_CAPTCHA_TYPE')) == 'google' || $captcha_type == 'google_v3') {
            if (Tools::getIsset('g-recaptcha-response') && ($reCaptcha = Tools::getValue('g-recaptcha-response')) && Validate::isCleanHtml($reCaptcha)) {
                $secret = Configuration::get('PA_GOOGLE' . ($captcha_type == 'google_v3' ? '_V3' : '') . '_CAPTCHA_SECRET_KEY');
                $site_verify = "https://www.google.com/recaptcha/api/siteverify";
                $this->refreshCACertFile();
                $data = array(
                    'secret' => $secret,
                    'response' => $reCaptcha
                );
                $ch = curl_init($site_verify);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($result, true);
                if (!$response) {
	                $query_build = http_build_query(array(
		                'secret' => $secret,
		                'response' => $reCaptcha
	                ));
	                $curl_timeout = 5;
	                $this->refreshCACertFile();
	                $stream_context = stream_context_create(array(
		                'http' => array('timeout' => $curl_timeout),
		                'ssl' => array(
			                'verify_peer' => true,
			                'cafile' => $this->getBundledCaBundlePath(),
		                ),
	                ));
	                $response = Tools::file_get_contents($site_verify . '?' . $query_build, false, $stream_context, $curl_timeout);
	                $response = json_decode($response, true);
                }
                $score = (float) Configuration::get('PA_GOOGLE_V3_CAPTCHA_SCORE');
                if (!$response || !isset($response['success']) || !$response['success'] || (isset($response['score']) && $response['score'] < $score)) {
                    $errors[] = $this->l('reCaptcha is invalid.');
                }
            } else
                $errors[] = $this->l('reCaptcha error');
        } else {
            if (($posTo = Tools::getValue('posTo', false)) && Validate::isCleanHtml($posTo)) {
                $security = ($captcha = self::PREFIX_CODE . $posTo) && isset($this->context->cookie->{$captcha}) && ($cookieVal = $this->context->cookie->{$captcha}) ? $cookieVal : false;
                $pa_captcha = Tools::getIsset('pa_captcha') && ($val = Tools::getValue('pa_captcha', false)) && Validate::isCleanHtml($val) ? Tools::strtolower(trim($val)) : false;
                if (!$security || ($security !== $pa_captcha)) {
                    $errors[] = $this->l('Security code does not match');
                }
                if (!(isset($this->context->cookie->{$captcha})))
                    $errors[] = $this->l('Security code does not match');
            }
            else
                $errors[] = $this->l('Security code does not match');
        }

        if (!$errors) {
            if ($this->ipBlackList(Configuration::get('PA_CAPTCHA_IP_BLACKLIST'))) {
                $errors[] = $this->l('Your IP is blocked. Contact webmaster for more info.');
            } elseif ($this->emailBlackList(Configuration::get('PA_CAPTCHA_EMAIL_BLACKLIST'))) {
                $errors[] = $this->l('Your email is blocked. Contact webmaster for more info.');
            }
        }
    }

    public function ipBlackList($ip_blacklist)
    {
        if (!$ip_blacklist)
            return false;
        $remote_addr = Tools::getRemoteAddr();
        $ips = explode("\n", $ip_blacklist);
        if ($ips) {
            foreach ($ips as $ip) {
                if (preg_match('/^' . $this->formatPattern($ip) . '$/', $remote_addr)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function emailBlackList($email_blacklist)
    {
        if (!$email_blacklist || !($email = Tools::getValue('email', Tools::getValue('from'))) || !Validate::isEmail($email))
            return false;
        $emails = explode("\n", $email_blacklist);
        if ($emails) {
            foreach ($emails as $pattern) {
                if (preg_match('/^' . $this->formatPattern($pattern) . '$/', $email)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function formatPattern($pattern)
    {
        return str_replace('*', '(.*)', trim($pattern));
    }

    public function refreshCACertFile()
    {
        if ((time() - filemtime($this->local_path . 'cache/cacert.pem') > 0)) {
            $stream_context = stream_context_create(array(
                'http' => array('timeout' => 3),
                'ssl' => array(
                    'cafile' => $this->getBundledCaBundlePath(),
                ),
            ));
            $ca_cert_content = Tools::file_get_contents(self::CACERT_LOCATION, false, $stream_context);
            if (empty($ca_cert_content)) {
                $ca_cert_content = Tools::file_get_contents($this->getBundledCaBundlePath());
            }
            if (preg_match('/(.*-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----){50}$/Uims', $ca_cert_content) && Tools::substr(rtrim($ca_cert_content), -1) == '-') {
                file_put_contents(_PS_CACHE_CA_CERT_FILE_, $ca_cert_content);
            }
        }
    }

    public function getBundledCaBundlePath()
    {
        $caBundleFile = $this->local_path . 'cache/cacert.pem';

        if (file_exists($caBundleFile)) {
            if (0 === strpos($caBundleFile, 'phar://')) {
                $tempCaBundleFile = tempnam(sys_get_temp_dir(), 'openssl-ca-bundle-');

                $contents = Tools::file_get_contents($caBundleFile);
                if ($contents !== false) {
                    file_put_contents($tempCaBundleFile, $contents);
                }

                register_shutdown_function(function () use ($tempCaBundleFile) {
                    unlink($tempCaBundleFile);
                });

                $caBundleFile = $tempCaBundleFile;
            }
        }

        return $caBundleFile;
    }

    public function getOverrides()
    {
        if (!$this->is17) {
            if (!is_dir($this->getLocalPath() . 'override')) {
                return null;
            }
            $autoload = ($this->is16 ? 'PrestaShop' : '') . 'Autoload';
            $result = array();
            foreach (Tools::scandir($this->getLocalPath() . 'override', 'php', '', true) as $file) {
                $class = basename($file, '.php');
                if ($autoload::getInstance()->getClassPath($class . 'Core') || Module::getModuleIdByName($class)) {
                    $result[] = $class;
                }
            }
            return $result;
        } else
            return parent::getOverrides();
    }

    public function addOverride($classname)
    {
    	$res = parent::addOverride($classname);
    	if (!$res)

		    $this->logInstall($classname);
    	return $res;
    }

    public function generateIndex()
    {
        if ($this->is16) {
            Tools::generateIndex();
        } else {
            Autoload::getInstance()->generateIndex();
        }
    }

    public function removeOverride($classname)
    {
        if ($this->isLogInstall($classname))
            return true;
        $autoload = ($this->is16 ? 'PrestaShop' : '') . 'Autoload';
        $orig_path = $path = $autoload::getInstance()->getClassPath($classname . 'Core');
        if ($orig_path && !$file = $autoload::getInstance()->getClassPath($classname))
            return true;
        elseif (!$orig_path && Module::getModuleIdByName($classname))
            $path = 'modules' . DIRECTORY_SEPARATOR . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
        $override_path = $orig_path ? _PS_ROOT_DIR_ . '/' . $file : _PS_OVERRIDE_DIR_ . $path;
        if (!@is_file($override_path) || !is_writable($override_path))
            return true;
        return parent::removeOverride($classname);
    }

    public function logInstall($classname, $_errors =[])
    {
	    $this->_clearCache('bo-log.tpl', $this->_getCacheId());
        $log_file = _PS_ETS_CAPTCHA_LOG_DIR_ . self::$log_file;
        if (!is_dir(_PS_ETS_CAPTCHA_LOG_DIR_))
            mkdir(_PS_ETS_CAPTCHA_LOG_DIR_, '0755');
        $data = array();
        if (file_exists($log_file))
            $data = (array)json_decode(Tools::file_get_contents($log_file), true);
        $data[$classname] = $_errors;
        file_put_contents($log_file, json_encode($data));
    }

    public function isLogInstall($classname)
    {
        $log_file = _PS_ETS_CAPTCHA_LOG_DIR_ . self::$log_file;
        if (!@file_exists($log_file))
            return false;
        $cached = (array)json_decode(Tools::file_get_contents($log_file), true);
        if ($cached && !empty($cached[$classname]))
            return true;
        return false;
    }

    public function clearLogInstall()
    {
        $log_file = _PS_ETS_CAPTCHA_LOG_DIR_ . self::$log_file;

        if (file_exists($log_file)) {
            if (unlink($log_file))
                Configuration::deleteByName('PA_CAPTCHA_ERROR_IS_FIXED');
        }
        return true;
    }

    public function displayLogInstall()
    {
	    $log_file = _PS_ETS_CAPTCHA_LOG_DIR_ . self::$log_file;
	    if (!file_exists($log_file))
		    return false;
    	if (!$this->isCached('bo-log.tpl', $this->_getCacheId())) {
		    $errors = (array)json_decode(Tools::file_get_contents($log_file), true);
		    if ($errors) {
			    $this->smarty->assign(array(
				    'PA_CAPTCHA_ERROR_IS_FIXED' => (int)Configuration::get('PA_CAPTCHA_ERROR_IS_FIXED'),
				    'link' => _PS_ETS_CAPTCHA_LOG_ . self::$log_file,
				    'errors' => $errors,
			    ));
		    }
	    }
	    return $this->display(__FILE__, 'bo-log.tpl', $this->_getCacheId());
    }

    public function isCached($template, $cache_id = null, $compile_id = null)
    {
	    if (!(int)Configuration::get('PA_CAPTCHA_ENABLE_CACHE'))
		    return false;
	    return parent::isCached($template, $cache_id, $compile_id); // TODO: Change the autogenerated stub
    }

	public function _getCacheId($params = null)
	{
		if (!(int)Configuration::get('PA_CAPTCHA_ENABLE_CACHE'))
			return null;
		$cacheId = $this->getCacheId($this->name);
		$cacheId = str_replace($this->name, '', $cacheId);
		$suffix ='';
		if($params)
		{
			if(is_array($params))
				$suffix .= '|'.implode('|',$params);
			else
				$suffix .= '|'.$params;
		}
		return $this->name . $suffix . $cacheId;
	}

	public function _clearCache($template, $cache_id = null, $compile_id = null)
	{
		if ($cache_id === null) {
			$cache_id = $this->name;
		}
		if($template=='*')
		{
			return Tools::clearCache(Context::getContext()->smarty, null, $cache_id, $compile_id);
		}
		else
		{
			return Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath($template), $cache_id, $compile_id);
		}
	}
    private function safeMkDir($path, $permission = 0755)
    {
        if (!@mkdir($concurrentDirectory = $path, $permission) && !is_dir($concurrentDirectory)) {
            throw new \PrestaShopException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return true;
    }
    private function checkOverrideDir()
    {
        if (defined('_PS_OVERRIDE_DIR_')) {
            $psOverride = @realpath(_PS_OVERRIDE_DIR_) . DIRECTORY_SEPARATOR;
            if (!is_dir($psOverride)) {
                $this->safeMkDir($psOverride);
            }
            $base = str_replace('/', DIRECTORY_SEPARATOR, $this->getLocalPath() . 'override');
            $iterator = new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS);
            /** @var RecursiveIteratorIterator|\SplFileInfo[] $iterator */
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            $iterator->setMaxDepth(4);
            foreach ($iterator as $k => $item) {
                if (!$item->isDir()) {
                    continue;
                }
                $path = str_replace($base . DIRECTORY_SEPARATOR, '', $item->getPathname());
                if (!@file_exists($psOverride . $path)) {
                    $this->safeMkDir($psOverride . $path);
                    @touch($psOverride . $path . DIRECTORY_SEPARATOR . '_do_not_remove');
                }
            }
            if (!file_exists($psOverride . 'index.php')) {
                Tools::copy($this->getLocalPath() . 'index.php', $psOverride . 'index.php');
            }
        }
    }
    public function enable($force_all = false)
    {
        if(!$force_all && Ets_pa_defines::checkEnableOtherShop($this->id) && $this->getOverrides() != null)
        {
            try {
                $this->uninstallOverrides();
            }
            catch (Exception $e)
            {
            }
        }
        $this->checkOverrideDir();
        return parent::enable($force_all);
    }
    public function disable($force_all = false)
    {
        if(parent::disable($force_all))
        {
            if(!$force_all && Ets_pa_defines::checkEnableOtherShop($this->id))
            {
                if(property_exists('Tab','enabled') && method_exists($this, 'get') && $dispatcher = $this->get('event_dispatcher')){
                    /** @var \Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher|\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
                    $dispatcher->addListener(\PrestaShopBundle\Event\ModuleManagementEvent::DISABLE, function (\PrestaShopBundle\Event\ModuleManagementEvent $event) {
                        Ets_pa_defines::activeTab($this->name);
                    });
                }
                if($this->getOverrides() != null)
                {
                    try {
                        $this->installOverrides();
                    }
                    catch (Exception $e)
                    {
                    }
                }
            }
            return true;
        }
        return false;
    }
}