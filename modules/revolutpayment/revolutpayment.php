<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutApi.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutDatabaseHelper.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutPRBSettingsHelper.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutApplePayOnBoardingHelper.php';
require_once _PS_MODULE_DIR_ . 'revolutpayment/classes/RevolutModuleHelper.php';

class RevolutPayment extends PaymentModule
{
    use RevolutDatabaseHelper;
    use RevolutModuleHelper;

    public static $free_shipping_options = [
        [
            'id' => 'flat_rate:0',
            'amount' => '0',
            'description' => '',
            'label' => 'SHIPPING',
        ],
    ];

    /**
     * RevolutPayment constructor.
     *
     * Set the information about this module
     */
    public function __construct()
    {
        $this->name = 'revolutpayment';
        $this->tab = 'payments_gateways';
        $this->version = '2.8.17';
        $this->author = 'Revolut';
        $this->controllers = ['payment', 'validation'];
        $this->revolutpay_tittle = 'Revolut Pay';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        $this->module_key = '7161109d5988905e258ab64bcf4b092e';
        $this->description = 'Revolut Business’s payment gateway has the unique advantage of being integrated 
        directly with your Revolut Business account';
        $this->confirmUninstall = 'Are you sure you want to uninstall this module?';
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->isPs16 = version_compare(_PS_VERSION_, '1.7', '<');
        $this->isPs17 = version_compare(_PS_VERSION_, '1.7', '>');
        $this->isPs8 = version_compare(_PS_VERSION_, '8.0', '>=');
        $this->available_currency_list = ['BGN', 'AED', 'AUD', 'BHD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'ILS', 'ISK', 'JPY', 'KWD', 'MXN', 'NOK', 'NZD', 'OMR', 'PLN', 'QAR', 'RON', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'UAH', 'USD', 'ZAR'];
        $this->card_payments_currency_list = ['AED', 'AUD', 'BHD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ISK', 'JPY', 'KWD', 'NOK', 'NZD', 'OMR', 'PLN', 'QAR', 'RON', 'SAR', 'SEK', 'SGD', 'TRY', 'UAH', 'USD', 'ZAR', 'BGN', 'ILS', 'MXN'];
        $this->support_link = 'https://www.revolut.com/en-HR/business/help/merchant-accounts/';
        $this->support_link .= 'payments/in-which-currencies-can-i-accept-payments';
        $this->ps_support_link = 'https://addons.prestashop.com/en/contact-us?id_product=50148';
        $this->autoCancelTimeout = 'PT2M';
        $this->captureMode = 'manual';
        $this->default_revolut_reward_banner = 1;
        $this->default_revolut_benifits_banner = 1;
        $this->default_revolut_informational_icon = 'link';
        $this->REVOLUT_PAY_INFORMATIONAL_ICON_VARIANTS = [
            [
                'id' => 'link',
                'name' => 'Learn more',
            ],
            [
                'id' => 'icon',
                'name' => 'Icon',
            ],
            [
                'id' => 'cashback',
                'name' => 'Get cashback',
            ],
            [
                'id' => 'disabled',
                'name' => 'Disabled',
            ],
        ];
        $payWidgetIsEnabled = false;
        $cardWidgetIsEnabled = false;
        $paymentRequestIsEnabled = false;
        $checkoutWidgetDisplayType = 1;
        $paymentDescription = '';
        $authorizeOnly = false;
        $checkoutDisplayName = 'Pay with card (via Revolut)';
        $customStatus = false;
        $autoRefunds = false;
        $apiKeyLive = Configuration::get('REVOLUT_P_APIKEY_LIVE');
        $apiKeySandbox = Configuration::get('REVOLUT_P_APIKEY');
        $isSandBoxMode = Configuration::get('REVOLUT_P_SANDBOX');
        if (
            Configuration::get('REVOLUT_PAY_METHOD_ENABLE') == 1
            && (($isSandBoxMode && !empty($apiKeySandbox)) || !empty($apiKeyLive))
        ) {
            $payWidgetIsEnabled = true;
        }

        if (
            Configuration::get('REVOLUT_CARD_METHOD_ENABLE') == 1
            && (($isSandBoxMode && !empty($apiKeySandbox)) || !empty($apiKeyLive))
        ) {
            $cardWidgetIsEnabled = true;
        }

        if (Configuration::get('REVOLUT_PRB_METHOD_ENABLE') == 1 && !empty($apiKeyLive)) {
            $paymentRequestIsEnabled = true;
        }

        if (Configuration::get('REVOLUT_P_AUTHORIZE_ONLY') == 1) {
            $authorizeOnly = true;
        }

        if (Configuration::get('REVOLUT_P_WIDGET_TYPE') != null) {
            $checkoutWidgetDisplayType = Configuration::get('REVOLUT_P_WIDGET_TYPE');
        }
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS') == 1) {
            $customStatus = true;
        }
        if (Configuration::get('REVOLUT_P_AUTO_REFUNDS') == 1) {
            $autoRefunds = true;
        }
        $mode = $isSandBoxMode ? 'sandbox' : 'live';

        $this->revolutApi = new RevolutApi($mode, $apiKeyLive, $apiKeySandbox, $this->version);
        $this->payWidgetIsEnabled = $payWidgetIsEnabled;
        $this->cardWidgetIsEnabled = $cardWidgetIsEnabled;
        $this->paymentRequestIsEnabled = $paymentRequestIsEnabled;
        $this->moduleEnable = $this->payWidgetIsEnabled || $this->cardWidgetIsEnabled;
        $this->authorizeOnly = $authorizeOnly;
        $this->displayName = 'Revolut';
        $this->checkoutWidgetDisplayType = $checkoutWidgetDisplayType;
        $this->customStatus = $customStatus;
        $this->autoRefunds = $autoRefunds;
        $this->isSandBoxMode = $isSandBoxMode;

        $this->payment_widget_types = [
            1 => $this->l('Direct'),
            2 => $this->l('Payment Page'),
            3 => $this->l('Popup'),
        ];

        parent::__construct();

        if (Configuration::get('REVOLUT_P_DESCRIPTION_' . $this->context->language->id) != null) {
            $paymentDescription = Configuration::get('REVOLUT_P_DESCRIPTION_' . $this->context->language->id);
        }

        if (Configuration::get('REVOLUT_P_TITLE_' . $this->context->language->id) != null) {
            $checkoutDisplayName = Configuration::get('REVOLUT_P_TITLE_' . $this->context->language->id);
        }

        $this->checkoutDisplayName = $checkoutDisplayName;
        $this->paymentDescription = $paymentDescription;
        $this->prbSettingsHelper = new RevolutPRBSettingsHelper($this);

        // check required setups
        $this->installDb();
    }

    /**
     * Install this module and register the following Hooks:
     *
     * @return bool
     */
    public function install()
    {
        // make default configuration
        $default_revolut_title = $this->l('Pay with card (via Revolut)');
        $default_revolut_description = $this->l('Pay with your credit or debit card');
        $languages = Language::getLanguages();

        foreach ($languages as $language) {
            if (Configuration::get('REVOLUT_P_TITLE_' . $language['id_lang']) == null) {
                Configuration::updateValue('REVOLUT_P_TITLE_' . $language['id_lang'], $default_revolut_title);
            }
            if (Configuration::get('REVOLUT_P_DESCRIPTION_' . $language['id_lang']) == null) {
                Configuration::updateValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang'], $default_revolut_description);
            }
        }

        if (Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_SIGNUP_BANNER_ENABLE', $this->default_revolut_reward_banner);
        }

        if (Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_BENEFITS_BANNER_ENABLE', $this->default_revolut_benifits_banner);
        }

        if (Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT') == null) {
            Configuration::updateValue('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT', $this->default_revolut_informational_icon);
        }

        if (Configuration::get('REVOLUT_PAY_METHOD_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_PAY_METHOD_ENABLE', 1);
        }

        if (Configuration::get('REVOLUT_CARD_METHOD_ENABLE') == null) {
            Configuration::updateValue('REVOLUT_CARD_METHOD_ENABLE', 1);
        }

        $default_revolut_widget_type = 1; // stands for Direct option
        if (Configuration::get('REVOLUT_P_WIDGET_TYPE') == null) {
            Configuration::updateValue('REVOLUT_P_WIDGET_TYPE', $default_revolut_widget_type);
        }

        $default_revolut_custom_status = 1;
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS', $default_revolut_custom_status);
        }

        $default_revolut_auto_refund = 0;
        if (Configuration::get('REVOLUT_P_AUTO_REFUNDS') == null) {
            Configuration::updateValue('REVOLUT_P_AUTO_REFUNDS', $default_revolut_auto_refund);
        }
        $default_revolut_custom_status_refund = Configuration::get('PS_OS_REFUND');
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_REFUND', $default_revolut_custom_status_refund);
        }
        $default_revolut_custom_status_capture = Configuration::get('PS_OS_PAYMENT');
        if (Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE') == null) {
            Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE', $default_revolut_custom_status_capture);
        }

        $default_revolut_webhook_status_authorised = Configuration::get('PS_OS_PAYMENT');
        if (Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED') == null) {
            Configuration::updateValue(
                'REVOLUT_P_WEBHOOK_STATUS_AUTHORISED',
                $default_revolut_webhook_status_authorised
            );
        }

        return parent::install()
            && $this->installOrderState()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayPaymentReturn')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('actionOrderSlipAdd')
            && $this->registerHook('actionProductCancel')
            && $this->registerHook('actionRevolutWebhook')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('payment')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayExpressCheckout')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('actionObjectOrderCarrierUpdateAfter')
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('actionObjectColissimoOrderCarrierUpdateAfter')

            && $this->installDb();
    }

    public function initAutomaticSetup()
    {
        // init api again with updated settings
        $apiKeyLive = Configuration::get('REVOLUT_P_APIKEY_LIVE');
        $apiKeySandbox = Configuration::get('REVOLUT_P_APIKEY');
        $isSandBoxMode = Configuration::get('REVOLUT_P_SANDBOX');

        $mode = $isSandBoxMode ? 'sandbox' : 'live';
        $this->revolutApi = new RevolutApi($mode, $apiKeyLive, $apiKeySandbox, $this->version);

        // try to automatically setup Apply pay domain
        new RevolutApplePayOnBoardingHelper($this);
        $this->setWebHook();
    }

    public function setWebHook()
    {
        $default_revolut_webhook_url = Context::getContext()->link->getModuleLink('revolutpayment', 'webhook', []);
        $res = $this->revolutApi->setRevolutWebhookUrl($default_revolut_webhook_url);
        if ($res) {
            $this->setSuccessMessage($this->l('Webhook Setup is successful.'));

            return true;
        }

        $this->setErrorMessage($this->l('Can not setup Revolut webhook url.'), []);

        return false;
    }

    /**
     * Create Revolut orders table
     *
     * @return bool
     */
    public function installDb()
    {
        $result = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'revolut_payment_orders` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`id_order` INT NULL ,
				`id_cart` INT NULL ,
				`id_revolut_order` VARCHAR(250) NOT NULL ,
				`public_id` VARCHAR(250) NOT NULL ,
				`save_card` TINYINT NOT NULL DEFAULT "0" ,
				PRIMARY KEY (`id`),
				UNIQUE (`id_revolut_order`),
				UNIQUE (`id_order`)
			) ENGINE = ' . _MYSQL_ENGINE_ . ';'
        );

        $result &= Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'revolut_cards` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`customer_id` INT NOT NULL ,
				`card_number` VARCHAR(25) NOT NULL,
				`expire_month` INT NOT NULL ,
				`expire_year` INT NOT NULL ,
				PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ';'
        );

        // update module version, this will indicate that module tables are updated during update
        Configuration::updateValue('REVOLUT_MODULE_VERSION', $this->version);

        return $result;
    }

    /**
     * Uninstall this module and remove it from all hooks
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Create order state
     *
     * @return bool
     */
    public function installOrderState()
    {
        if ((int) Configuration::get('REVOLUT_OS_WAITING')) {
            $exist_order_state = new OrderState((int) Configuration::get('REVOLUT_OS_WAITING'));

            if ($exist_order_state->id) {
                return true;
            }
        }

        $order_state = new OrderState();
        $order_state->name = [];

        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $order_state->name[$language['id_lang']] = 'En attente de paiement Revolut';
            } else {
                $order_state->name[$language['id_lang']] = 'Awaiting Revolut payment';
            }
        }

        $order_state->send_email = false;
        $order_state->color = '#4169E1';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = false;
        $order_state->module_name = $this->name;

        if ($order_state->add()) {
            $source = _PS_MODULE_DIR_ . 'revolutpayment/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
            copy($source, $destination);
        }

        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                Configuration::updateValue('REVOLUT_OS_WAITING', (int) $order_state->id, false, null, (int) $shop['id_shop']);
            }
        } else {
            Configuration::updateValue('REVOLUT_OS_WAITING', (int) $order_state->id);
        }

        return true;
    }

    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
     */
    public function getContent()
    {
        $this->prbSettingsHelper->processPRBSettings();
        $this->postProcess();
        $this->initAutomaticSetup();

        return $this->displayRevolutSettingsPage();
    }

    public function postProcess()
    {
        if (!Tools::isSubmit('submit' . $this->name)) {
            return false;
        }

        $payWidgetIsEnabled = (string) Tools::getValue('REVOLUT_PAY_METHOD_ENABLE');
        $revolutBannerEnabled = (int) Tools::getValue('REVOLUT_SIGNUP_BANNER_ENABLE');
        $revolutBenefitsBannerEnabled = (int) Tools::getValue('REVOLUT_BENEFITS_BANNER_ENABLE');
        $revolutPayInformationalIconVariant = (string) Tools::getValue('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT');
        $cardWidgetIsEnabled = (string) Tools::getValue('REVOLUT_CARD_METHOD_ENABLE');
        $revolutSandbox = (string) Tools::getValue('REVOLUT_P_SANDBOX');
        $revolutAuthorizeOnly = (string) Tools::getValue('REVOLUT_P_AUTHORIZE_ONLY');
        $revolutApiKey = (string) Tools::getValue('REVOLUT_P_APIKEY');
        $revolutApiKeyLive = (string) Tools::getValue('REVOLUT_P_APIKEY_LIVE');
        $revolutWidgetType = (int) Tools::getValue('REVOLUT_P_WIDGET_TYPE');
        $revolutCustomStatus = (string) Tools::getValue('REVOLUT_P_CUSTOM_STATUS');
        $revolutAutoRefunds = (string) Tools::getValue('REVOLUT_P_AUTO_REFUNDS');
        $revolutCustomStatusRefund = (string) Tools::getValue('REVOLUT_P_CUSTOM_STATUS_REFUND');
        $revolutCustomStatusCapture = (string) Tools::getValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE');
        $revolutWebhookStatusAuthorised = (string) Tools::getValue('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED');

        $languages = Language::getLanguages();
        $checkTitleIsEmpty = false;

        foreach ($languages as $language) {
            $title = Tools::getValue('REVOLUT_P_TITLE_' . $language['id_lang']);
            $description = Tools::getValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang']);
            if (empty($title) && !$checkTitleIsEmpty) {
                $checkTitleIsEmpty = true;
            }

            Configuration::updateValue('REVOLUT_P_TITLE_' . $language['id_lang'], $title);
            Configuration::updateValue('REVOLUT_P_DESCRIPTION_' . $language['id_lang'], $description);
        }

        if ($checkTitleIsEmpty) {
            $this->setErrorMessage($this->l('Title is required.'), []);
        }

        Configuration::updateValue('REVOLUT_PAY_METHOD_ENABLE', $payWidgetIsEnabled);
        Configuration::updateValue('REVOLUT_SIGNUP_BANNER_ENABLE', $revolutBannerEnabled);
        Configuration::updateValue('REVOLUT_BENEFITS_BANNER_ENABLE', $revolutBenefitsBannerEnabled);
        Configuration::updateValue('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT', $revolutPayInformationalIconVariant);
        Configuration::updateValue('REVOLUT_CARD_METHOD_ENABLE', $cardWidgetIsEnabled);
        Configuration::updateValue('REVOLUT_P_SANDBOX', $revolutSandbox);
        Configuration::updateValue('REVOLUT_P_AUTHORIZE_ONLY', $revolutAuthorizeOnly);
        Configuration::updateValue('REVOLUT_P_APIKEY', $revolutApiKey);
        Configuration::updateValue('REVOLUT_P_APIKEY_LIVE', $revolutApiKeyLive);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS', $revolutCustomStatus);
        Configuration::updateValue('REVOLUT_P_AUTO_REFUNDS', $revolutAutoRefunds);
        Configuration::updateValue('REVOLUT_P_WIDGET_TYPE', $revolutWidgetType);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_REFUND', $revolutCustomStatusRefund);
        Configuration::updateValue('REVOLUT_P_CUSTOM_STATUS_CAPTURE', $revolutCustomStatusCapture);
        Configuration::updateValue('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED', $revolutWebhookStatusAuthorised);

        $this->setSuccessMessage($this->l('Settings updated.'));
    }

    /**
     * Returns configuration form
     * generate a configuration screen on the admin
     *
     * @return html
     */
    public function displayRevolutSettingsPage()
    {
        $this->context->controller->addCSS($this->_path . '/views/css/select2.min.css');
        $this->context->controller->addJS($this->_path . '/views/js/select2.min.js');

        $this->context->controller->addCSS($this->_path . '/views/css/admin.css');
        $this->context->controller->addJS($this->_path . '/views/js/admin.js');
        $languages = Language::getLanguages();
        $id_current_lang = $this->context->language->id;
        $order_statuses = $this->getOrderStatuses();

        $REVOLUT_P_TITLE = [];
        $REVOLUT_P_DESCRIPTION = [];

        foreach ($languages as $language) {
            $REVOLUT_P_TITLE[$language['id_lang']] = Configuration::get('REVOLUT_P_TITLE_' . $language['id_lang']);
            $REVOLUT_P_DESCRIPTION[$language['id_lang']] = Configuration::get('REVOLUT_P_DESCRIPTION_' . $language['id_lang']);
        }

        $this->context->smarty->assign(
            [
                'REVOLUT_P_TITLE' => $REVOLUT_P_TITLE,
                'REVOLUT_P_DESCRIPTION' => $REVOLUT_P_DESCRIPTION,
                'REVOLUT_PAY_METHOD_ENABLE' => Configuration::get('REVOLUT_PAY_METHOD_ENABLE'),
                'REVOLUT_SIGNUP_BANNER_ENABLE' => Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE'),
                'REVOLUT_BENEFITS_BANNER_ENABLE' => Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE'),
                'REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT' => Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT'),
                'REVOLUT_CARD_METHOD_ENABLE' => Configuration::get('REVOLUT_CARD_METHOD_ENABLE'),
                'REVOLUT_P_SANDBOX' => Configuration::get('REVOLUT_P_SANDBOX'),
                'REVOLUT_P_AUTHORIZE_ONLY' => Configuration::get('REVOLUT_P_AUTHORIZE_ONLY'),
                'REVOLUT_P_APIKEY' => Configuration::get('REVOLUT_P_APIKEY'),
                'REVOLUT_P_APIKEY_LIVE' => Configuration::get('REVOLUT_P_APIKEY_LIVE'),
                'REVOLUT_P_AUTO_REFUNDS' => Configuration::get('REVOLUT_P_AUTO_REFUNDS'),
                'REVOLUT_P_CUSTOM_STATUS' => Configuration::get('REVOLUT_P_CUSTOM_STATUS'),
                'REVOLUT_P_CUSTOM_STATUS_REFUND' => Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND'),
                'REVOLUT_P_CUSTOM_STATUS_CAPTURE' => Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE'),
                'REVOLUT_P_WEBHOOK_STATUS_AUTHORISED' => Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED'),
                'REVOLUT_P_WIDGET_TYPE' => Configuration::get('REVOLUT_P_WIDGET_TYPE'),
                'REVOLUT_PRB_LOCATIONS' => json_encode(explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'))),
                'REVOLUT_PAY_INFORMATIONAL_ICON_VARIANTS' => $this->REVOLUT_PAY_INFORMATIONAL_ICON_VARIANTS,
                'languages' => $languages,
                'id_current_lang' => $id_current_lang,
                'order_statuses' => $order_statuses,
                'prb_settings_form' => $this->prbSettingsHelper->renderPRBSettingsForm(),
                'payment_widget_types' => $this->payment_widget_types,
                'isPs17' => $this->isPs17,
                'section' => Tools::getValue('section'),
                'module_dir' => _MODULE_DIR_,
            ]
        );

        return $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
    }

    /**
     * Get order statuses
     *
     * @param
     *
     * @return array|void
     */
    public function getOrderStatuses()
    {
        $order_statuses = [
            [
                'id' => 0,
                'name' => $this->l('Choose status'),
            ],
        ];
        $prestashop_order_statuses = OrderState::getOrderStates($this->context->language->id);

        foreach ($prestashop_order_statuses as $prestashop_order_status) {
            $order_statuses[] = [
                'id' => $prestashop_order_status['id_order_state'],
                'name' => $prestashop_order_status['name'],
            ];
        }

        return $order_statuses;
    }

    /**
     * Add css & js to head
     *
     * @param
     *
     * @return array|void
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJquery();

        if (!empty(Tools::getValue('_rp_fr'))) {
            $this->context->controller->errors[] = Tools::getValue('_rp_fr');
        }

        $baseUri = parse_url($this->getBaseUri());
        $origin_url = isset($baseUri['host']) ? $baseUri['host'] : '';

        Media::addJsDef(
            [
                'cardWidgetIsEnabled' => $this->cardWidgetIsEnabled,
                'payWidgetIsEnabled' => $this->payWidgetIsEnabled,
                'checkoutWidgetDisplayType' => $this->checkoutWidgetDisplayType,
                'originUrl' => $origin_url,
            ]
        );

        if ($this->isPs17) {
            $this->hookHeaderV17();
        } else {
            $this->hookHeaderV16();
        }
    }

    public function deactivateUnknownAddresses()
    {
        $id_customer = 0;

        if (isset($this->context->cart->id_customer) && !empty($this->context->cart->id_customer)) {
            $id_customer = $this->context->cart->id_customer;
        }

        if (empty($id_customer) && isset($this->context->customer->id) && !empty($this->context->customer->id)) {
            $id_customer = $this->context->customer->id;
        }

        if (empty($id_customer)) {
            return false;
        }

        $id_addresses = Db::getInstance()->executeS('SELECT id_address FROM `' . _DB_PREFIX_ . 'address` WHERE firstname="unknown" AND lastname="unknown" AND alias="unknown" AND address1="unknown" AND id_customer=' . (int) $id_customer);

        if (empty($id_addresses)) {
            return false;
        }

        foreach ($id_addresses as $id_address) {
            $this->context->cart->updateAddressId(
                (int) $id_address['id_address'],
                Address::getFirstCustomerAddressId($id_customer)
            );
        }

        foreach ($this->context->cart->getProducts() as &$product) {
            $this->context->cart->setProductAddressDelivery(
                $product['id_product'],
                $product['id_product_attribute'],
                $product['id_address_delivery'],
                $this->context->cart->id_address_delivery
            );
        }

        return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'address` set active = 0, deleted = 1 WHERE firstname="unknown" AND lastname="unknown" AND alias="unknown" AND address1="unknown" AND id_customer=' . (int) $id_customer);
    }

    public function hookHeaderV17()
    {
        if ($this->context->controller->php_self == 'order-confirmation') {
            $this->context->controller->registerJavascript(
                $this->name . '',
                'modules/' . $this->name . '/views/js/upsell.banner.js'
            );
            $this->context->controller->registerJavascript(
                'revolut-upsell-js',
                $this->revolutApi->api_base_url . '/upsell/embed.js',
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 1]
            );
        }

        if ($this->context->controller->php_self == 'product' || $this->context->controller->php_self == 'cart') {
            $this->context->controller->registerJavascript(
                'revolut-js',
                $this->revolutApi->api_base_url . '/embed.js',
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 1]
            );

            $this->context->controller->registerJavascript(
                $this->name . '-blockUI',
                'modules/' . $this->name . '/views/js/jquery.blockUI.js'
            );

            $this->context->controller->registerJavascript(
                $this->name . '-library',
                'modules/' . $this->name . '/views/js/revolut.min.js'
            );

            $this->context->controller->registerJavascript(
                $this->name . '-payment-request',
                'modules/' . $this->name . '/views/js/version17/revolut.payment.request.js'
            );

            $this->context->controller->registerStylesheet(
                $this->name . 'blockui-style',
                'modules/' . $this->name . '/views/css/blockui.css'
            );
        }

        if ($this->context->controller->php_self == 'order' || Tools::getValue('module') == 'revolutpayment') {
            $logoPath = _MODULE_DIR_ . $this->name . '/views/img/';
            $revpay_logo = $logoPath . 'revolut.svg';
            $id_cart = $this->context->cart->id;
            $revolut_order = $this->getRevolutOrderByIdCart($id_cart);
            $public_id = isset($revolut_order['public_id']) ? $revolut_order['public_id'] : null;
            $amex_availability = false;
            if ($public_id) {
                $amex_availability = $this->revolutApi->get_available_card_brands($this->context->currency->iso_code);
            }

            Media::addJsDef(
                [
                    'revpay_logo' => $revpay_logo,
                    'logo_path' => $logoPath,
                    'amex_availability' => $amex_availability,
                    'informationalBannerData' => [
                        'amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'publicToken' => $this->getMerchantPublicToken(),
                        'orderToken' => $public_id,
                        'revolutPayIconVariant' => Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT'),
                        'locale' => $this->context->language->iso_code,
                    ],
                ]
            );

            $this->context->controller->registerJavascript(
                'revolut-js',
                $this->revolutApi->api_base_url . '/embed.js',
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 1]
            );

            $this->context->controller->registerJavascript(
                $this->name . '-blockUI',
                'modules/' . $this->name . '/views/js/jquery.blockUI.js'
            );

            $this->context->controller->registerStylesheet(
                $this->name . 'blockui-style',
                'modules/' . $this->name . '/views/css/blockui.css'
            );

            $this->context->controller->registerStylesheet(
                $this->name . '-style',
                'modules/' . $this->name . '/views/css/revolut.css'
            );

            $this->context->controller->registerJavascript(
                $this->name . '',
                'modules/' . $this->name . '/views/js/upsell.banner.js'
            );

            $this->context->controller->registerJavascript(
                'revolut-upsell-js',
                $this->revolutApi->api_base_url . '/upsell/embed.js',
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 1]
            );

            $paymentJsPath = '/views/js/version17/revolut.payment.js';

            $this->context->controller->registerJavascript(
                $this->name . '-payment',
                'modules/' . $this->name . $paymentJsPath
            );
        }

        Media::addJsDef(['currentPage' => $this->context->controller->php_self]);
    }

    public function hookHeaderV16()
    {
        if ($this->context->controller->php_self == 'order-confirmation') {
            $this->context->controller->addJS($this->_path . '/views/js/upsell.banner.js');
            $this->context->controller->addJs($this->revolutApi->api_base_url . '/upsell/embed.js', false);
        }
        if ($this->context->controller->php_self == 'product' || $this->context->controller->php_self == 'cart') {
            $this->context->controller->addJs($this->revolutApi->api_base_url . '/embed.js', false);
            $this->context->controller->addCSS($this->_path . '/views/css/blockui.css');
            $this->context->controller->addJs($this->_path . '/views/js/revolut.min.js');
            $this->context->controller->addJs($this->_path . '/views/js/jquery.blockUI.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.request.js');
        }

        if (
            $this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc'
            || Tools::getValue('module') == 'revolutpayment'
        ) {
            $revolut_order = $this->getRevolutOrderByIdCart($this->context->cart->id);
            $public_id = isset($revolut_order['public_id']) ? $revolut_order['public_id'] : null;
            Media::addJsDef(
                [
                    'revolut_locale' => $this->context->language->iso_code,
                    'informationalBannerData' => [
                        'amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'publicToken' => $this->getMerchantPublicToken(),
                        'orderToken' => $public_id,
                        'revolutPayIconVariant' => Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT'),
                        'locale' => $this->context->language->iso_code,
                    ],
                ]
            );

            $this->context->controller->addJs($this->revolutApi->api_base_url . '/embed.js', false);
            $this->context->controller->addCSS($this->_path . '/views/css/blockui.css');
            $this->context->controller->addCSS($this->_path . '/views/css/revolut_v16.css');
            $this->context->controller->addJs($this->_path . '/views/js/jquery.blockUI.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.request.js');
            $this->context->controller->addJs($this->_path . '/views/js/version16/revolut.payment.js');
            $this->context->controller->addJs($this->revolutApi->api_base_url . '/upsell/embed.js', false);
            $this->context->controller->addJS($this->_path . '/views/js/upsell.banner.js');
        }

        Media::addJsDef(['currentPage' => $this->context->controller->php_self]);
    }

    /**
     * Display this module as a payment option during the checkout for PS version 1.6
     *
     * @param array $params
     *
     * @return string
     */
    public function hookPayment($params)
    {
        /*
         * Verify if this module is active
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }

        // create revolut order
        $public_id = $this->createRevolutOrder();

        $this->context->smarty->assign(
            [
                'currency_error' => false,
                'revolut_order_public_id_error' => false,
                'ps_support_link' => $this->ps_support_link,
            ]
        );

        $has_error = false;
        if (empty($public_id)) {
            $this->context->smarty->assign(
                [
                    'revolut_order_public_id_error' => true,
                    'ps_support_link' => $this->ps_support_link,
                ]
            );
            $has_error = true;
        }

        if (!$this->checkCurrencySupport()) {
            $this->context->smarty->assign(
                [
                    'currency_error' => true,
                    'selected_currency' => $this->context->currency->iso_code,
                    'support_link' => $this->support_link,
                ]
            );
            $has_error = true;
        }

        $merchant_public_key = $this->getMerchantPublicToken();
        $is_rev_pay_v2 = (int) (!empty($merchant_public_key));

        // address data
        $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
        $customer_email = $this->context->customer->email;
        $address = new Address($this->context->cart->id_address_delivery);
        $phone_number = $address->phone_mobile;
        if (empty($phone_number)) {
            $phone_number = $address->phone;
        }
        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $cart = $this->context->cart;
        $paymentPageLink = $this->context->link->getModuleLink($this->name, 'payment', [], true);
        $card_widget_currency_error = !in_array($this->context->currency->iso_code, $this->card_payments_currency_list);
        $mobile_redirect_url = Context::getContext()->link->getModuleLink('revolutpayment', 'validation', []);
        $amex_availability = false;
        if (isset($public_id)) {
            $amex_availability = $this->revolutApi->get_available_card_brands($this->context->currency->iso_code);
        }

        $payment_option_availability = $this->revolutApi->get_available_payment_options($this->context->currency->iso_code);

        $cardWidgetIsEnabled = $this->cardWidgetIsEnabled && in_array('card', $payment_option_availability);
        $payWidgetIsEnabled = $this->payWidgetIsEnabled && in_array('pay_with_revolut', $payment_option_availability);

        if (!$cardWidgetIsEnabled && !$payWidgetIsEnabled) {
            return '';
        }

        $this->context->smarty->assign(
            [
                'is_revolut_signup_banner_enabled' => Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE'),
                'is_revolut_benefits_banner_enabled' => Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE'),
                'revolut_pay_informational_icon_variant' => Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT'),
                'card_widget_currency_error' => $card_widget_currency_error,
                'selected_currency' => $this->context->currency->iso_code,
                'nbProducts' => $cart->nbProducts(),
                'total' => $cart->getOrderTotal(true, Cart::BOTH),
                'action_link' => Context::getContext()->link->getModuleLink($this->name, 'validation', []),
                'action' => Context::getContext()->link->getModuleLink($this->name, 'validation', []),
                'formActionValidation' => Context::getContext()->link->getModuleLink($this->name, 'validation', []),
                'revolutpayment_include_path' => _PS_MODULE_DIR_ . $this->name . '/',
                'revolutpayment_path' => $this->getPathUri(),
                'public_id' => $public_id,
                'merchant_public_key' => $merchant_public_key,
                'is_rev_pay_v2' => $is_rev_pay_v2,
                'customer_name' => $customer_name,
                'mobile_redirect_url' => $mobile_redirect_url,
                'customer_email' => $customer_email,
                'paymentPageLink' => $paymentPageLink,
                'address' => $address,
                'state' => $state,
                'country' => $country,
                'revolut_payment_title' => $this->checkoutDisplayName,
                'merchant_type' => $this->revolutApi->mode,
                'payment_description' => $this->paymentDescription,
                'locale' => $this->context->language->iso_code,
                'phone_number' => $phone_number,
                'cardWidgetIsEnabled' => $cardWidgetIsEnabled,
                'payWidgetIsEnabled' => $payWidgetIsEnabled,
                'checkoutWidgetDisplayType' => $this->checkoutWidgetDisplayType,
                'order_total_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                'shipping_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $this->context->currency->iso_code),
                'amex_availability' => $amex_availability,
                'isPs16' => $this->isPs16,
            ]
        );
        if ($this->checkoutWidgetDisplayType != 1 || $has_error) {
            return $this->display(__FILE__, 'views/templates/hook/version16/payment_page_button.tpl');
        }

        return $this->display(__FILE__, 'views/templates/hook/version16/direct_payment.tpl');
    }

    public function getPathUri()
    {
        return parent::getPathUri();
    }

    public function getBaseUri()
    {
        if (version_compare(_PS_VERSION_, '1.6.1.15', '<')) {
            return Context::getContext()->shop->getBaseURL(true);
        }

        return $this->context->link->getBaseLink();
    }

    public function floatValue($val)
    {
        $val = str_replace(',', '.', $val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);

        return (float) $val;
    }

    public function create_revolut_customer($billing_phone, $billing_email)
    {
        try {
            if (empty($billing_phone) || empty($billing_email)) {
                return;
            }

            $body = [
                'phone' => $billing_phone,
                'email' => $billing_email,
            ];
            try {
                $create_customer = $this->revolutApi->apiRequest('/customers', $body);
            } catch (Exception $e) {
                PrestaShopLogger::addLog('create_revolut_customer : Customer email already registered or api call failed' . $e->getMessage(), 3);
            }

            if (!empty($create_customer['id'])) {
                $revolut_customer_id = $create_customer['id'];
            }

            return $revolut_customer_id;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('create_revolut_customer: ' . $e->getMessage(), 3);
        }
    }

    public function get_or_create_revolut_customer()
    {
        try {
            $customer_email = $this->context->customer->email;
            $address = new Address($this->context->cart->id_address_delivery);
            $customer_phone = $address->phone_mobile;
            if (empty($customer_phone)) {
                $customer_phone = $address->phone;
            }

            if (empty($customer_email) || empty($customer_phone)) {
                return;
            }

            $revolut_customer = $this->revolutApi->apiRequestGet('/customers?term=' . $customer_email);
            $revolut_customer_id = !empty($revolut_customer[0]['id']) ? $revolut_customer[0]['id'] : '';

            if (!empty($revolut_customer_id)) {
                $revolut_customer = $this->revolutApi->apiRequestGet('/customers/' . $revolut_customer_id);
                if ($revolut_customer['phone'] !== $customer_phone) {
                    $body = ['phone' => $customer_phone];
                    $this->revolutApi->apiRequestPatch("/customers/$revolut_customer_id", $body);
                }
            }

            if (empty($revolut_customer_id)) {
                $revolut_customer_id = $this->create_revolut_customer($customer_phone, $customer_email);
            }

            return $revolut_customer_id;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('get_or_create_revolut_customer : ' . $e->getMessage(), 3);
        }
    }

    /**
     * Display this module as a payment option during the checkout for PS version 1.7
     *
     * @param array $params
     *
     * @return array|void
     */
    public function hookPaymentOptions($params)
    {
        /*
         * Verify if this module is active
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }
        /**
         * Form action URL. The form data will be sent to the
         * validation controller when the user finishes
         * the order process.
         */
        $formActionValidation = $this->context->link->getModuleLink($this->name, 'validation', [], true);
        $formActionPayment = $this->context->link->getModuleLink($this->name, 'payment', [], true);

        $has_error = false;

        $this->context->smarty->assign(
            [
                'currency_error' => false,
                'revolut_order_public_id_error' => false,
                'ps_support_link' => $this->ps_support_link,
            ]
        );

        if (!$this->checkCurrencySupport()) {
            $this->context->smarty->assign(
                [
                    'currency_error' => true,
                    'selected_currency' => $this->context->currency->iso_code,
                    'support_link' => $this->support_link,
                ]
            );
            $has_error = true;
        }

        // create revolut order
        $public_id = $this->createRevolutOrder();
        if (empty($public_id)) {
            $this->context->smarty->assign(
                [
                    'revolut_order_public_id_error' => true,
                    'ps_support_link' => $this->ps_support_link,
                ]
            );
            $has_error = true;
        }

        $merchant_public_key = $this->getMerchantPublicToken();
        $is_rev_pay_v2 = (int) (!empty($merchant_public_key));

        if (Tools::usingSecureMode()) {
            $domain = Tools::getShopDomainSsl(true, true);
        } else {
            $domain = Tools::getShopDomain(true, true);
        }

        // address data
        $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
        $customer_email = $this->context->customer->email;
        $address = new Address($this->context->cart->id_address_delivery);
        $phone_number = $address->phone_mobile;
        if (empty($phone_number)) {
            $phone_number = $address->phone;
        }

        $state = new State($address->id_state);
        $country = new Country($address->id_country);
        $mobile_redirect_url = Context::getContext()->link->getModuleLink('revolutpayment', 'validation', []);
        $card_widget_currency_error = !in_array($this->context->currency->iso_code, $this->card_payments_currency_list);
        $this->context->smarty->assign(
            [
                'is_revolut_signup_banner_enabled' => Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE'),
                'is_revolut_benefits_banner_enabled' => Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE'),
                'revolut_pay_informational_icon_variant' => Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT'),
                'card_widget_currency_error' => $card_widget_currency_error,
                'selected_currency' => $this->context->currency->iso_code,
                'order_total_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code),
                'shipping_amount' => $this->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $this->context->currency->iso_code),
                'merchant_type' => $this->revolutApi->mode,
                'payment_description' => $this->paymentDescription,
                'revolut_payment_title' => $this->checkoutDisplayName,
                'phone_number' => $phone_number,
                'country' => $country,
                'state' => $state,
                'address' => $address,
                'customer_email' => $customer_email,
                'customer_name' => $customer_name,
                'merchant_public_key' => $merchant_public_key,
                'public_id' => $public_id,
                'is_rev_pay_v2' => $is_rev_pay_v2,
                'mobile_redirect_url' => $mobile_redirect_url,
                'formActionValidation' => $formActionValidation,
                'formActionPayment' => $formActionPayment,
                'locale' => $this->context->language->iso_code,
            ]
        );

        /**
         *  Load form template to be displayed in the checkout step
         */
        $revolutCartPaymentForm = $this->fetch('module:revolutpayment/views/templates/hook/version17/direct_payment.tpl');
        $revolutPayPaymentForm = $this->fetch('module:revolutpayment/views/templates/hook/revolut_pay_button.tpl');

        /**
         * Create a PaymentOption object containing the necessary data
         * to display this module in the checkout
         */
        $revolutCardPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $revolutCardPaymentOption->setModuleName($this->checkoutDisplayName)
            ->setCallToActionText($this->checkoutDisplayName)
            ->setLogo(_MODULE_DIR_ . $this->name . '/views/img/visa-logo.svg');

        if ($this->checkoutWidgetDisplayType != 2 || $has_error) {
            $revolutCardPaymentOption->setForm($revolutCartPaymentForm);
        }

        if ($this->checkoutWidgetDisplayType == 2) {
            $revolutCardPaymentOption->setAction($formActionPayment);
        } else {
            $revolutCardPaymentOption->setAction($formActionValidation);
        }

        $revolutPayPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $revolutPayPaymentOption->setModuleName($this->revolutpay_tittle)
            ->setCallToActionText($this->revolutpay_tittle)
            ->setLogo(_MODULE_DIR_ . $this->name . '/views/img/revolut.svg')
            ->setAction($formActionValidation)
            ->setForm($revolutPayPaymentForm);

        $payment_option_availability = $this->revolutApi->get_available_payment_options($this->context->currency->iso_code);

        $payment_options = [];

        if ($this->cardWidgetIsEnabled && in_array('card', $payment_option_availability)) {
            $payment_options[] = $revolutCardPaymentOption;
        }

        if ($this->payWidgetIsEnabled && in_array('pay_with_revolut', $payment_option_availability)) {
            $payment_options[] = $revolutPayPaymentOption;
        }

        return $payment_options;
    }

    /**
     * Display a message in the paymentReturn hook
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayPaymentReturn($params)
    {
        /*
         * Verify if this module is enabled
         */
        if (!$this->active || !$this->moduleEnable) {
            return;
        }

        if ($this->isPs17) {
            // 1.7 version has a detailed confimation page there is no need for confirmation message
            //            return $this->fetch('module:revolutpayment/views/templates/hook/payment_return.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
        }
    }

    /**
     * Hook actionOrderStatusUpdate
     *
     * @param
     *
     * @return array|void
     */
    public function hookActionOrderStatusUpdate($hook_data)
    {
        /*
         * Verify if this module is enabled
         */
        if (!$this->active || !$this->moduleEnable || !$this->customStatus) {
            return;
        }

        $new_order_status_id = $hook_data['newOrderStatus']->id;
        $id_order = $hook_data['id_order'];
        $order = new Order((int) $id_order);
        $capture_revolut_order = false;
        $refund_revolut_order = false;
        $cancel_revolut_order = false;

        if ($new_order_status_id == Configuration::get('REVOLUT_P_CUSTOM_STATUS_CAPTURE')) {
            $capture_revolut_order = true;
        }
        if ($new_order_status_id == Configuration::get('REVOLUT_P_CUSTOM_STATUS_REFUND')) {
            $refund_revolut_order = true;
        }
        if ($new_order_status_id == Configuration::get('PS_OS_CANCELED')) {
            $cancel_revolut_order = true;
        }

        if (!$capture_revolut_order && !$refund_revolut_order && !$cancel_revolut_order) {
            return;
        }

        $revolut_order = $this->getRevolutOrder($id_order);

        if (!empty($revolut_order['id_revolut_order'])) {
            // capture revolut order
            if ($capture_revolut_order) { // capture no update ps order status
                $result = $this->revolutApi->captureRevolutOrder($revolut_order['id_revolut_order']);

                if (!$result) {
                    $this->setErrorMessage($this->l('An error occured while capturing the Revolut order.'), $result);
                } else {
                    $update_params = [
                        'merchant_order_data' => ['reference' => $order->reference],
                    ];
                    $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);
                }
            }

            if ($cancel_revolut_order) {
                $result = $this->revolutApi->cancelRevolutOrder($revolut_order['id_revolut_order']); // cancel order
                if (empty($result['public_id'])) {
                    $this->setErrorMessage($this->l('An error occured while canceling the Revolut order.'), $result);
                }
            }

            // refund revolut order
            if ($refund_revolut_order && $this->autoRefunds) {
                // check existed partial refund
                $order_slips = OrderSlip::getOrdersSlip($order->id_customer, $id_order);

                $total_refunded_shipping = 0;
                $total_refunded_products = 0;

                if (count($order_slips)) {
                    foreach ($order_slips as $order_slip) {
                        $total_refunded_shipping += $order_slip['total_shipping_tax_incl'];
                        $total_refunded_products += $order_slip['total_products_tax_incl'];
                    }
                }

                // when request repeat, order slip can be duplicated
                if ($total_refunded_shipping > $order->total_shipping) {
                    $total_refunded_shipping = $order->total_shipping;
                }

                $total_refunded = $total_refunded_shipping + $total_refunded_products;
                $total_refundable = $order->total_paid - $total_refunded;

                if ($total_refundable > 0) {
                    $params = [
                        'amount' => $this->createRevolutAmount($total_refundable, $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'merchant_order_id' => $order->reference,
                        'description' => 'Prestashop order status change to Refunded',
                    ];
                    $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);
                    if (empty($result['public_id'])) {
                        $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
                    }
                }
            }
        }
    }

    public function setErrorMessage($errorMessage, $errorDetail)
    {
        if (!isset($this->context->controller->errors)) {
            return;
        }

        if (isset($errorDetail['errorId']) && !empty($errorDetail['errorId'])) {
            $errorMessage .= 'Error ID: ' . $errorDetail['errorId'];
        }

        if (isset($errorDetail['errorMsg']) && !empty($errorDetail['errorMsg'])) {
            $errorMessage .= 'Error ID: ' . $errorDetail['errorMsg'];
        }

        $this->context->controller->errors[] = $errorMessage;
    }

    public function setSuccessMessage($message)
    {
        if (!isset($this->context->controller->confirmations)) {
            return;
        }

        $this->context->controller->confirmations[] = $message;
    }

    /**
     * Hook actionOrderSlipAdd
     *
     * @param
     *
     * @return array|void
     */
    public function hookActionOrderSlipAdd($hook_data)
    {
        if (!$this->active || !$this->moduleEnable || !$this->autoRefunds) {
            return;
        }
        // for refund shipping
        $shipping_cost_amount = 0;
        $cancel_product = Tools::getValue('cancel_product');
        $is_voucher = $cancel_product['voucher'];

        if ($is_voucher) {
            return;
        }

        $ps_order = $hook_data['order'];

        $shipping_cost_amount_ps16 = Tools::getValue('partialRefundShippingCost');
        if (isset($cancel_product['shipping_amount'])) {
            $shipping_cost_amount = $this->floatValue($cancel_product['shipping_amount']);
        } elseif (!empty($shipping_cost_amount_ps16)) {
            $shipping_cost_amount = $this->floatValue($shipping_cost_amount_ps16);
        }

        if (count($hook_data['productList']) == 0 && $shipping_cost_amount == 0) {
            return;
        }
        // refund products
        $amount = 0;
        foreach ($hook_data['productList'] as $product) {
            $amount += $product['amount'];
        }

        // refund shipping
        if ($shipping_cost_amount > 0) {
            if (version_compare(_PS_VERSION_, '1.7.6', '>') && !$this->isTaxIncludedInOrder($ps_order)) {
                $carrier = new Carrier((int) $ps_order->id_carrier);
                $address = Address::initialize($ps_order->id_address_delivery, false);
                $tax_calculator = $carrier->getTaxCalculator($address);

                if ($tax_calculator instanceof TaxCalculator) {
                    $shipping_cost_amount = Tools::ps_round($tax_calculator->addTaxes($shipping_cost_amount), 2);
                }
            } elseif (version_compare(_PS_VERSION_, '1.7.6', '<=') && !Tools::getValue('TaxMethod')) {
                $tax = new Tax();
                $tax->rate = $hook_data['order']->carrier_tax_rate;
                $tax_calculator = new TaxCalculator([$tax]);
                $shipping_cost_amount = $tax_calculator->addTaxes($shipping_cost_amount);
            }

            $amount += $shipping_cost_amount;
        }

        $revolut_order = $this->getRevolutOrder($ps_order->id);
        if (isset($revolut_order['id_revolut_order']) && $revolut_order['id_revolut_order'] != '') {
            $params = [
                'amount' => $this->createRevolutAmount($amount, $this->context->currency->iso_code),
                'currency' => $this->context->currency->iso_code,
                'merchant_order_id' => $ps_order->reference,
                'description' => 'Prestashop order partially refund',
            ];

            $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);

            if (empty($result['id'])) {
                $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
            }
        }
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isTaxIncludedInOrder($order)
    {
        $customer = new Customer($order->id_customer);
        $taxCalculationMethod = Group::getPriceDisplayMethod((int) $customer->id_default_group);

        return $taxCalculationMethod === PS_TAX_INC;
    }

    /**
     * Hook hookActionFrontControllerSetMedia
     *
     * @param
     *
     * @return void
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->deactivateUnknownAddresses();
    }

    /**
     * Hook hookActionProductCancel
     *
     * @param
     *
     * @return array|void
     */
    public function hookActionProductCancel($hook_data)
    {
        if (!$this->active || !$this->moduleEnable || !$this->autoRefunds) {
            return;
        }

        if (Tools::getIsset($_POST) && count($_POST) > 0) {
            $id_order = Tools::getValue('id_order');
            $order = new Order((int) $id_order);
            $quantity = 0;
            $price = 0;

            if (Tools::getIsset(Tools::getValue('cancelQuantity')) && count(Tools::getValue('cancelQuantity')) > 0) {
                foreach (Tools::getValue('cancelQuantity') as $cancel_quantity) {
                    $quantity += $cancel_quantity;
                }
            }
            if (Tools::getIsset($_POST['product_price_tax_incl'])) {
                $price = Tools::getValue('product_price_tax_incl');
            }

            $refund_amount = $quantity * $price;

            if ($refund_amount > 0) {
                // do refund
                $revolut_order = $this->getRevolutOrder($id_order);
                if (isset($revolut_order['id_revolut_order']) && $revolut_order['id_revolut_order'] != '') {
                    $params = [
                        'amount' => $this->createRevolutAmount($refund_amount, $this->context->currency->iso_code),
                        'currency' => $this->context->currency->iso_code,
                        'merchant_order_id' => $order->reference,
                        'description' => 'Prestashop order standard refund',
                    ];
                    $result = $this->revolutApi->refundRevolutOrder($revolut_order['id_revolut_order'], $params);
                    if (empty($result['public_id'])) {
                        $this->setErrorMessage($this->l('An error occured while refunding the Revolut order.'), $result);
                    }
                }
            }
        }
    }

    public function hookDisplayProductPriceBlock(array $params)
    {
        $type = isset($params['type']) ? $params['type'] : '';
        if (!$this->isPs17 || $type != 'price') {
            return false;
        }

        return $this->hookDisplayRightColumnProduct([]);
    }

    public function hookDisplayRightColumnProduct(array $params)
    {
        if (!$this->active || $this->isSandBoxMode || !$this->paymentRequestIsEnabled) {
            return false;
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED') && !$this->context->customer->id) {
            return false;
        }

        $prb_button_locations = explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'));

        if (!in_array('product', $prb_button_locations)) {
            return false;
        }

        $id_product = (int) Tools::getValue('id_product');
        if (!$id_product) {
            return false;
        }

        $product = new Product($id_product);

        $id_product = $product->id;
        $is_virtual = $product->is_virtual;

        $price = Product::getPriceStatic($id_product);

        $public_id = $this->createRevolutOrder($price);
        $payment_option_availability = $this->revolutApi->get_available_payment_options($this->context->currency->iso_code);

        if (!count(array_intersect(['apple_pay', 'google_pay'], $payment_option_availability))) {
            return false;
        }

        $ajax_url = Context::getContext()->link->getModuleLink('revolutpayment', 'fastcheckout', []);

        $this->smarty->assign(
            [
                'is_product_page' => true,
                'ps_revolut_payment_request_params' => json_encode(
                    [
                        'is_product_page' => true,
                        'token' => Tools::getToken(false),
                        'revolut_public_id' => $public_id,
                        'ps_cart_id' => $this->context->cart->id,
                        'merchant_type' => $this->revolutApi->mode,
                        'carrier_list' => self::$free_shipping_options,
                        'ajax_url' => $ajax_url,
                        'request_shipping' => !$is_virtual,
                        'button_style' => $this->prbSettingsHelper->getPRBConfigFormValues(),
                    ]
                ),
            ]
        );

        return $this->display(__FILE__, '/views/templates/hook/payment_request.tpl');
    }

    public function hookDisplayExpressCheckout(array $params)
    {
        if (!$this->active || $this->isSandBoxMode || !$this->paymentRequestIsEnabled) {
            return false;
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED') && !$this->context->customer->id) {
            return false;
        }

        $prb_button_locations = explode(',', Configuration::get('REVOLUT_PRB_LOCATION_VALUES'));

        if (!in_array('cart', $prb_button_locations)) {
            return false;
        }

        $public_id = $this->createRevolutOrder();
        $payment_option_availability = $this->revolutApi->get_available_payment_options($this->context->currency->iso_code);

        if (!count(array_intersect(['apple_pay', 'google_pay'], $payment_option_availability))) {
            return false;
        }

        $ajax_url = Context::getContext()->link->getModuleLink('revolutpayment', 'fastcheckout', []);

        $this->smarty->assign(
            [
                'is_product_page' => false,
                'ps_revolut_payment_request_params' => json_encode(
                    [
                        'is_product_page' => false,
                        'token' => Tools::getToken(false),
                        'revolut_public_id' => $public_id,
                        'ps_cart_id' => $this->context->cart->id,
                        'merchant_type' => $this->revolutApi->mode,
                        'carrier_list' => self::$free_shipping_options,
                        'ajax_url' => $ajax_url,
                        'request_shipping' => !$this->context->cart->isVirtualCart(),
                        'button_style' => $this->prbSettingsHelper->getPRBConfigFormValues(),
                    ]
                ),
            ]
        );

        return $this->display(__FILE__, '/views/templates/hook/payment_request.tpl');
    }

    public function hookDisplayShoppingCartFooter(array $params)
    {
        return $this->hookDisplayExpressCheckout($params);
    }

    /**
     * Hook actionOrderStatusUpdate
     *
     * @param
     *
     * @return array|void
     */
    public function hookActionRevolutWebhook($params)
    {
        if (!empty($params['order_id']) && !empty($params['event'])) {
            if ($params['event'] == 'ORDER_COMPLETED') {
                $revolut_order = Db::getInstance()->getRow(
                    'SELECT UNHEX(`id_revolut_order`) as id_revolut_order, `save_card`, `id_order`'
                    . ' FROM `' . _DB_PREFIX_ . 'revolut_payment_orders`'
                    . ' WHERE UNHEX(`id_revolut_order`) LIKE "' . pSQL($params['order_id']) . '"'
                );

                if (!empty($revolut_order['id_revolut_order']) && !empty($revolut_order['id_order'])) {
                    sleep(3);
                    // update prestashop order status
                    // check if order already got Payment accepted state
                    $new_order_status = Configuration::get('REVOLUT_P_WEBHOOK_STATUS_AUTHORISED');
                    $order = new Order((int) $revolut_order['id_order']);
                    $order_history = $order->getHistory(Configuration::get('PS_LANG_DEFAULT'), $new_order_status);
                    if (!empty($order_history)) {
                        return;
                    }

                    $order_history = new OrderHistory();
                    $order_history->id_order = (int) $revolut_order['id_order'];
                    $order_history->changeIdOrderState($new_order_status, $revolut_order['id_order']);
                    $order_history->addWithemail();
                    $this->updateTransactionId((int) $revolut_order['id_order'], $revolut_order['id_revolut_order']);
                }
            }
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = null;
        if (isset($params['order'])) {
            $order = $params['order'];
        } elseif (isset($params['objOrder'])) {
            $order = $params['objOrder'];
        }

        $isRevolutSignUpBannerEnabled = Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE');

        if (!$order || !$isRevolutSignUpBannerEnabled) {
            return;
        }

        $revolut_order = $this->getRevolutOrderByIdCart($order->id_cart);
        $revolut_order_id = $revolut_order['id_revolut_order'];
        $revolut_order_public_id = $revolut_order['public_id'];
        $merchant_public_key = $this->getMerchantPublicToken();

        $transaction_id = $order->reference;
        $currency = $this->context->currency->iso_code;
        $payment_method = $order->module;
        $customer = new Customer($order->id_customer);
        $address_invoice = new Address($order->id_address_invoice);
        $address_delivery = new Address($order->id_address_delivery);
        $customer_email = $customer->email;
        // checks if phone number exists in either delivery or billing address
        $customer_phone = '';
        if (!empty($address_delivery->phone)) {
            $customer_phone = $address_delivery->phone;
        } elseif (!empty($address_invoice->phone)) {
            $customer_phone = $address_invoice->phone;
        }

        $this->context->smarty->assign(
            [
                'merchant_public_key' => $merchant_public_key,
                'revolut_order_id' => $revolut_order_id,
                'revolut_order_public_id' => $revolut_order_public_id,
                'locale' => $this->context->language->iso_code,
                'currency' => $currency,
                'transaction_id' => $transaction_id,
                'customer_email' => $customer_email,
                'payment_method' => $payment_method,
                'customer_phone' => $customer_phone,
                'banner_type' => $payment_method == $this->name ? 'enrollment' : 'promotional',
            ]
        );

        return $this->display(__FILE__, '/views/templates/hook/order_confirmation_banner.tpl');
    }

    public function createRevolutOrder($amount = null)
    {
        if (!Validate::isLoadedObject($this->context->cart) || $this->context->cart->OrderExists()) {
            $cart = new Cart();
            $cart->id_lang = (int) $this->context->cookie->id_lang;
            $cart->id_currency = (int) $this->context->cookie->id_currency;
            $cart->id_guest = (int) $this->context->cookie->id_guest;
            $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
            $cart->id_shop = $this->context->shop->id;
            $cart->id_address_delivery = 0;
            $cart->id_address_invoice = 0;
            $cart->save();

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;
            $this->context->cookie->write();
            CartRule::autoAddToCart($this->context);
        }

        if (!$amount) {
            $amount = $this->context->cart->getOrderTotal();
        }

        $id_cart = $this->context->cart->id;
        $currency = $this->context->currency->iso_code;
        $revolut_order = $this->getRevolutOrderByIdCart($id_cart);

        if (!empty($revolut_order)) {
            $order = $this->revolutApi->apiRequestGet('/orders/' . $revolut_order['id_revolut_order']);
            $revolut_customer_id = $this->get_or_create_revolut_customer();
            if (isset($order['state']) && $order['state'] == 'PENDING') {
                $update_params = [
                    'amount' => $this->createRevolutAmount($amount, $currency),
                    'currency' => $currency,
                    'customer' => empty($revolut_customer_id) ? null : ['id' => $revolut_customer_id],
                ];

                $public_id = $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);

                if (!empty($public_id)) {
                    return $public_id;
                }
            }
            // if there would be an error on update, remove db record in order to create from scratch
            $this->removeRevolutOrderByIdCart($id_cart);
        }

        $path = '/orders';
        $params = [
            'amount' => $this->createRevolutAmount($amount, $currency),
            'currency' => $currency,
            'merchant_order_data' => null,
            'customer' => null,
            'capture_mode' => $this->captureMode,
        ];

        if (!$this->authorizeOnly) {
            $params['cancel_authorised_after'] = $this->autoCancelTimeout;
        }

        $response = $this->revolutApi->apiRequest($path, $params, true, true);
        if (!isset($response['token']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error:  Can not create new Revolut order. Please check parameters and try again!',
                3
            );

            return '';
        }

        $id_revolut_order = $response['id'];
        $public_id = $response['token'];

        if (!$this->createRevolutPaymentRecord($id_revolut_order, $public_id, $id_cart)) {
            return '';
        }

        return $public_id;
    }

    public function getMerchantPublicToken()
    {
        $merchant_public_key = Configuration::get("REVOLUT_MERCHANT_PUBLIC_TOKEN_{$this->revolutApi->mode}");

        if (!empty($merchant_public_key)) {
            return $merchant_public_key;
        }

        $response = $this->revolutApi->merchantPublicApiRequestGet('/api/public-key/latest', true);

        $merchant_public_key = isset($response['public_key']) ? $response['public_key'] : '';
        if (empty($merchant_public_key)) {
            return '';
        }

        Configuration::updateValue("REVOLUT_MERCHANT_PUBLIC_TOKEN_{$this->revolutApi->mode}", $merchant_public_key);

        return $merchant_public_key;
    }

    public function checkCurrencySupport()
    {
        if (!in_array($this->context->currency->iso_code, $this->available_currency_list)) {
            return false;
        }

        return true;
    }

    public function getRevolutPaymentTitle($id_revolut_order)
    {
        $paymentTittle = $this->displayName;
        $order = $this->revolutApi->apiRequestGet('/orders/' . $id_revolut_order);
        if (isset($order['payments']) && !empty($order['payments'])) {
            $payment = reset($order['payments']);
            if (isset($payment['payment_method']) && !empty($payment['payment_method'])) {
                $paymentMethod = $payment['payment_method'];
                $paymentMethod = $paymentMethod['type'];
                if ($paymentMethod == 'REVOLUT') {
                    $paymentTittle = 'Revolut Pay';
                } elseif ($paymentMethod == 'CARD') {
                    $paymentTittle = 'Card ' . $payment['payment_method']['card']['card_brand'] . ' (via Revolut)';
                } elseif ($paymentMethod == 'APPLE_PAY') {
                    $paymentTittle = 'Apple Pay (via Revolut)';
                } elseif ($paymentMethod == 'GOOGLE_PAY') {
                    $paymentTittle = 'Google Pay (via Revolut)';
                }
            }
        }

        return $paymentTittle;
    }

    public function createPrestaShopOrder($customer)
    {
        $order_status = Configuration::get('REVOLUT_OS_WAITING'); // Awaiting check payment - Payment accepted after capture
        $cart = $this->context->cart;
        // create the order
        $this->validateOrder(
            (int) $this->context->cart->id,
            $order_status,
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->displayName,
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );

        $id_new_order = (int) $this->currentOrder;

        if (!$id_new_order) {
            PrestaShopLogger::addLog('Error: Can not create an order', 3);

            return;
        }

        if (!$this->updatePsOrderIdRecord($id_new_order, $cart->id)) {
            return;
        }

        return $id_new_order;
    }

    public function processRevolutOrderResult($ps_order_id, $revolut_order_id)
    {
        $this->maybeCaptureOrder($revolut_order_id);

        $paymentAcceptedOrderState = Configuration::get('PS_OS_PAYMENT');

        for ($i = 0; $i <= 9; ++$i) {
            // check if in paralel webhook call already updated the order state
            if ($this->getOrderCurrentState($ps_order_id) == $paymentAcceptedOrderState) {
                break;
            }

            $revolut_order = $this->revolutApi->apiRequestGet('/orders/' . $revolut_order_id);
            if (!empty($revolut_order['state'])) {
                if ($revolut_order['state'] == 'COMPLETED' || $revolut_order['state'] == 'IN_SETTLEMENT') {
                    // update order status
                    $this->updateOrderStatus($ps_order_id, $paymentAcceptedOrderState);
                    $this->updateTransactionId($ps_order_id, $revolut_order_id);
                    break;
                } elseif (in_array($revolut_order['state'], ['CANCELLED', 'FAILED', 'PENDING'])) {
                    $this->handleFailedPayment($ps_order_id, $revolut_order['state']);
                    break;
                } elseif ($this->authorizeOnly && $revolut_order['state'] == 'AUTHORISED') {
                    // if MANUAL option is selected and the payment is AUTHORISED just end the loop
                    // Merchant will check it manually
                    break;
                } elseif ($i == 9) {
                    // update order note
                    $this->addOrderMessage(
                        $ps_order_id,
                        'Payment is taking a bit longer than 
                                            expected to be completed. 
							                If the order is not moved to the "Completed order" 
							                state after 24h, please check your Revolut account to 
							                verify that this payment was taken. 
							                You might need to contact your customer if it wasn’t. 
							                (Revolut Order ID: ' . $revolut_order_id . ')'
                    );
                }
            }

            sleep(3);
        }

        // update Revolut order
        try {
            $ps_order = new Order((int) $ps_order_id);
            $update_params = [
                'merchant_order_data' => ['reference' => $ps_order->reference],
            ];
            $this->revolutApi->updateRevolutOrder($revolut_order_id, $update_params);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Failed to update #merchant_order_id: ' . $e->getMessage(), 3);
        }
    }

    public function maybeCaptureOrder($revolut_order_id)
    {
        if (!$this->authorizeOnly) {
            $capture_order_result = $this->revolutApi->captureRevolutOrder($revolut_order_id);
            if (!$capture_order_result) {
                PrestaShopLogger::addLog('Error: Could not capture payment for order id :  ' . $revolut_order_id, 3);
            }
        }
    }

    public function getOrderConfirmationLink($id_cart, $secure_key, $id_order)
    {
        return $this->context->link->getPageLink(
            'order-confirmation',
            true,
            (int) $this->context->language->id,
            [
                'id_cart' => (int) $id_cart,
                'id_module' => (int) $this->id,
                'id_order' => (int) $id_order,
                'key' => $secure_key,
            ]
        );
    }

    public function updateTransactionId($id_order, $id_revolut_order)
    {
        $order = new Order($id_order);
        $payment_method = $this->getRevolutPaymentTitle($id_revolut_order);
        $order->payment = $payment_method;
        $order->save();

        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'order_payment` SET transaction_id="' . pSQL($id_revolut_order) . '" , payment_method="' . pSQL($payment_method) . '"
                                    WHERE order_reference="' . pSQL($order->reference) . '"'
        );
    }

    public function hookActionObjectColissimoOrderCarrierUpdateAfter($hook_data)
    {
        $this->handleOrderCarrierUpdateAfter($hook_data, 'ColissimoOrderCarrier');
    }

    public function hookActionObjectOrderCarrierUpdateAfter($hook_data)
    {
        $this->handleOrderCarrierUpdateAfter($hook_data);
    }

    private function handleOrderCarrierUpdateAfter($hook_data, $logContext = 'OrderCarrier')
    {
        try {
            if (!$this->active || empty($hook_data['object'])) {
                return;
            }

            $order_carrier = $hook_data['object'];
            $ps_order = new Order($order_carrier->id_order);
            $carrier = new Carrier($order_carrier->id_carrier);

            $tracking_number = $ps_order->getWsShippingNumber();
            $carrier_name = $carrier->name;
            $revolut_order = $this->getRevolutOrder($ps_order->id);
            $shipping_details = $this->collectShippingDetails($ps_order->id);

            if (empty($tracking_number) || !isset($revolut_order['id_revolut_order']) || empty($revolut_order['id_revolut_order']) || empty($carrier_name) || empty($shipping_details)) {
                return;
            }

            $shippments = [
                'shipments' => [[
                    'shipping_company_name' => $carrier_name,
                    'tracking_number' => $tracking_number,
                ]],
            ];

            $params = [
                'shipping' => array_merge($shipping_details, $shippments),
            ];

            $this->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $params);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                "Error: Could not update Revolut order shipments data - ($logContext) : " . $e->getMessage(),
                3
            );
        }
    }

    public function hookDisplayPaymentTop()
    {
        if (!$this->active
            || !$this->moduleEnable
            || !Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE')
            || !Configuration::get('REVOLUT_PAY_METHOD_ENABLE')
            || version_compare(_PS_VERSION_, '1.7.0', '<')
        ) {
            return;
        }

        return $this->display(__FILE__, 'views/templates/hook/benefits_banner.tpl');
    }
}
