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

class Ets_pa_defines {
	protected static $instance;
	/** @var Ets_advancedcaptcha */
	private $module;
	private $context;

	public	function __construct()
	{
		$this->context= Context::getContext();
		$this->module = new Ets_advancedcaptcha();
	}
	public static function l($string)
	{
		return Translate::getModuleTranslation('ets_advancedcaptcha', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
	}
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Ets_pa_defines();
		}
		return self::$instance;
	}
	public static function getHooks() {
		return array(
			'displayPaCaptcha',
			'displayHeader',
			'displayCustomerAccountForm',
			'displayBackOfficeHeader',
			'displayOverrideTemplate',
			'displayReassurance',
		);
	}
	public static function getControllers() {
		return array(
			'contact',
			'authentication',
			'registration',
			'order',
			'order-opc',
			'orderopc',
			'product',
			'password'
		);
	}
	public function getPositions() {
		$positions = array(
			'con' => array(
				'id_option' => 'contact',
				'name' => $this->l('Contact form (Recommended)'),
			),
			'reg' => array(
				'id_option' => 'register',
				'name' => $this->l('Registration form (Recommended)'),
			),
			'log' => array(
				'id_option' => 'login',
				'name' => $this->l('Login form'),
			),
			'new' => array(
				'id_option' => 'newsletter',
				'name' => $this->l('Newsletter subscription form'),
			),
			'out' => array(
				'id_option' => 'out_of_stock',
				'name' => $this->l('Out of product alert form'),
			),
			'pwd' => array(
				'id_option' => 'pwd_recovery',
				'name' => $this->l('Forgot your password form'),
			),
		);

		$is17 = version_compare(_PS_VERSION_, '1.7.0', '>=');
		if (!$is17 && (int)Configuration::get('PS_ORDER_PROCESS_TYPE')) {
			unset($positions['log']);
		}
		return $positions;
	}

	public function getCaptchaTypes() {
		$captchaTypes =  array(
			'google' => array(
				'id_option' => 'google',
				'name' => $this->l('Google reCAPTCHA - V2'),
				'img' => 'google.png',
			),
			'google_v3' => array(
				'id_option' => 'google_v3',
				'name' => $this->l('Google reCAPTCHA - V3'),
				'img' => 'google_v3.png',
			),
			'colorful' => array(
				'id_option' => 'colorful',
				'name' => $this->l('Image captcha - Easy level'),
				'img' => 'colorful.png',
			),
			'basic' => array(
				'id_option' => 'basic',
				'name' => $this->l('Image captcha - Medium level'),
				'img' => 'basic.png',
			),
			'complex' => array(
				'id_option' => 'complex',
				'name' => $this->l('Image captcha - Difficult level'),
				'img' => 'complex.png',
			),
		);
		$is17 = version_compare(_PS_VERSION_, '1.7.0', '>=');
		if (!$is17 && (int)Configuration::get('PS_ORDER_PROCESS_TYPE')) {
			unset($captchaTypes['google']);
		}
		return $captchaTypes;
	}

	public function getConfigs() {
		$positions = self::getInstance()->getPositions();
		$captchaTypes = self::getInstance()->getCaptchaTypes();
		return array(
			'PA_CAPTCHA_POSITION' => array(
				'label' => $this->l('Select forms to enable captcha'),
				'type' => 'pa_checkbox',
				'values' => $positions,
				'default' => 'register,contact',
				'tab' => 'captcha',
			),
			'PA_CAPTCHA_TYPE' => array(
				'label' => $this->l('Captcha type'),
				'type' => 'pa_img_radio',
				'required' => true,
				'values' => $captchaTypes,
				'default' => 'colorful',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_CAPTCHA_SITE_KEY' => array(
				'label' => $this->l('Site key'),
				'type' => 'text',
				'required' => true,
				'col' => '4',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_CAPTCHA_SECRET_KEY' => array(
				'label' => $this->l('Secret key'),
				'type' => 'text',
				'required' => true,
				'col' => '4',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_CAPTCHA_LABEL' => array(
				'label' => $this->l('Label'),
				'type' => 'text',
				'lang' => true,
				'col' => '4',
				'tab' => 'captcha',
				'desc' => $this->l('Leave blank to not display the label'),
				'default' => $this->l('Security check'),
			),
			'PA_GOOGLE_V3_CAPTCHA_SITE_KEY' => array(
				'label' => $this->l('Site key'),
				'type' => 'text',
				'required' => true,
				'col' => '4',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_V3_CAPTCHA_SECRET_KEY' => array(
				'label' => $this->l('Secret key'),
				'type' => 'text',
				'required' => true,
				'col' => '4',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_V3_CAPTCHA_SCORE' => array(
				'label' => $this->l('Score'),
				'type' => 'text',
				'required' => true,
				'col' => '4',
				'tab' => 'captcha',
                'desc' => $this->l('reCAPTCHA v3 returns a score for each request without user friction (1.0 is very likely a good interaction, 0.0 is very likely a bot). Based on the score, you can take variable action in the context of your site. As reCAPTCHA v3 does not ever interrupt the user flow, you can first run reCAPTCHA without taking action and then decide on thresholds by looking at your traffic in the admin console. By default, you can use a threshold of 0.5.'),
				'default' => 0.5
			),
			'PA_GOOGLE_CAPTCHA_THEME' => array(
				'label' => $this->l('Google reCAPTCHA theme'),
				'type' => 'select',
				'options' => array(
					'query' => array(
						array(
							'id_option' => 'light',
							'name' => $this->l('Light')
						),
						array(
							'id_option' => 'dark',
							'name' => $this->l('Dark')
						),
					),
					'id' => 'id_option',
					'name' => 'name',
				),
				'default' => 'light',
				'tab' => 'captcha',
			),
			'PA_GOOGLE_V3_POSITION' => array(
				'label' => $this->l('Google reCAPTCHA - V3 position'),
				'type' => 'select',
				'options' => array(
					'query' => array(
						array(
							'id_option' => 'bottomright',
							'name' => $this->l('Bottom right')
						),
						array(
							'id_option' => 'bottomleft',
							'name' => $this->l('Bottom left')
						),
						array(
							'id_option' => 'inline',
							'name' => $this->l('Inline')
						)
					),
					'id' => 'id_option',
					'name' => 'name',
				),
				'default' => 'bottomright',
				'tab' => 'captcha',
			),
			'PA_CAPTCHA_OFF_CUSTOMER_LOGIN' => array(
				'label' => $this->l('Disable captcha for logged in customer'),
				'type' => 'switch',
				'default' => '1',
				'tab' => 'captcha',
			),
			'PA_CAPTCHA_IP_BLACKLIST' => array(
				'label' => $this->l('IP blacklist (IPs to block)'),
				'type' => 'textarea',
				'desc' => $this->l('Enter exact IP or IP pattern using "*", each IP/IP pattern on a line. For example: 69.89.31.226, 69.89.31.*, *.226, etc. '),
				'rows' => 10,
				'col' => 4,
				'tab' => 'captcha',
			),
			'PA_CAPTCHA_EMAIL_BLACKLIST' => array(
				'label' => $this->l('Email blacklist (emails to block)'),
				'type' => 'textarea',
				'desc' => $this->l('Enter exact email address or email pattern using "*", each email/email pattern on a line. For example: example@mail.ru,*@mail.ru, *@qq.com, etc.'),
				'rows' => 10,
				'col' => 4,
				'tab' => 'captcha',
			),
			'PA_CAPTCHA_ENABLE_CACHE' => array(
				'label' => $this->l('Enable smarty cache'),
				'type' => 'switch',
				'default' => (int)Configuration::get('PS_SMARTY_CACHE'),
				'tab' => 'captcha',
				'desc' => $this->l('The module uses PrestaShop Smarty Cache, so please make sure that PrestaShop Smarty Cache is enabled to use this feature'),
			)
		);
	}
    public static function checkEnableOtherShop($id_module)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $id_module . ' AND `id_shop` NOT IN(' . implode(', ', Shop::getContextListShopID()) . ')';
        return Db::getInstance()->executeS($sql);
    }
    public static function activeTab($module_name)
    {
        return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'tab` SET enabled=1 where module ="'.pSQL($module_name).'"');
    }
}