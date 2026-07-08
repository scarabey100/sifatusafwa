<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminStockAlertSubscribersController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'date_add';
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'stockalert_subscriber';
        $this->className = 'StockAlertSubscriber';
        $this->tabClassName = 'AdminStockAlertSubscriber';
        $this->identifier = 'id_stockalert_subscriber';
        $this->lang = false;
        $this->addRowAction('delete');
        $this->_orderWay = $this->_defaultOrderWay;
        $this->list_no_link = true;
        $this->allow_export = true;

        parent::__construct();

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
            'send_mail' => [
                'text' => sprintf('%1$s "%2$s"', $this->l('Toggle'), $this->l('Send email to customer')),
                'confirm' => sprintf('%1$s "%2$s"?', $this->l('Change'), $this->l('Send email to customer')),
                'icon' => 'icon-refresh',
            ],
        ];

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $manufacturers_array = [];
        $manufacturers = Manufacturer::getManufacturers(false, 0, false);
        $manufacturersWithAlerts = StockAlertSubscriber::getManufacturersWithAlerts();
        $idManufacturersWithAlerts = array_column($manufacturersWithAlerts, 'id_manufacturer');
        foreach ($manufacturers as $manufacturer) {
            if (in_array($manufacturer['id_manufacturer'], $idManufacturersWithAlerts)) {
                $manufacturers_array[$manufacturer['id_manufacturer']] = $manufacturer['name'];
            }
        }

        $language_array = [];
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $language_array[$language['id_lang']] = $language['name'];
        }

        $this->fields_list = [
            'id_stockalert_subscriber' => [
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'id_customer' => [
                'title' => $this->l('Customer'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'customerCallback',
            ],
            'customer_email' => [
                'title' => $this->l('Customer email'),
                'align' => 'text-center',
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'productIdCallback',
                'filter_key' => 'a!id_product',
            ],
            'id_product_attribute' => [
                'title' => $this->l('Product Attribute ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'productAttributeIdCallback',
            ],
            'reference' => [
                'title' => $this->l('Product reference'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'productReferenceCallback',
            ],
            'product_name' => [
                'title' => $this->l('Product'),
                'class' => 'fixed-width-xxl',
                'callback' => 'productNameCallback',
                'filter_key' => 'pl!name',
                // 'search' => false,
            ],
            'product_img' => [
                'title' => $this->l('Image'),
                'class' => 'fixed-width-xxl',
                'align' => 'text-center',
                'callback' => 'productImgCallback',
                'search' => false,
            ],
            'product_manufacturer' => [
                'title' => $this->l('Manufacturer'),
                'class' => 'fixed-width-xxl',
                'callback' => 'productManufacturerCallback',
                'filter_key' => 'p!id_manufacturer',
                'type' => 'select',
                'list' => $manufacturers_array,
                'filter_type' => 'int',
            ],
            'id_lang' => [
                'title' => $this->l('Language'),
                'callback' => 'languageCallback',
                'type' => 'select',
                'list' => $language_array,
                'filter_key' => 'l!id_lang',
            ],
            'send_mail' => [
                'title' => $this->l('Send email to customer'),
                'type' => 'bool',
                'align' => 'text-center',
                'callback' => 'sendMailCallback',
            ],
            'date_add' => [
                'title' => $this->l('Date added'),
            ],
        ];

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        if (!Shop::isFeatureActive()) {
            $this->shopLinkType = '';
        } else {
            $this->shopLinkType = 'shop';
        }

        $this->_select .= '1 as product_name, 1 as product_img, 1 as product_manufacturer, p.reference';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $this->context->language->id . ' AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (a.`id_product` = p.`id_product` AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')';
        $this->_where .= "AND (`date_send` IS NULL OR `date_send` = '" . date('Y-m-d H:i:s', 0) . "')";
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/stockalert-admin.css');
    }

    public function initContent()
    {
        $warningError = '';
        if ($warnings = $this->module->getWarnings(false)) {
            $warningError = $this->module->displayError($warnings);
        }

        parent::initContent();

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->smarty->assign([
                    'this_path' => $this->module->getPathUri(),
                    'support_id' => $this->module->addons_id_product,
                ]);

                $available_iso_codes = ['en', 'es'];
                $default_iso_code = 'en';
                $template_iso_suffix = in_array($this->context->language->iso_code, $available_iso_codes) ? $this->context->language->iso_code : $default_iso_code;
                $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/company/information_' . $template_iso_suffix . '.tpl');
            }
        }

        $this->context->smarty->assign([
            'content' => $warningError . $this->content,
            'token' => $this->token,
        ]);
    }

    public function initProcess()
    {
        parent::initProcess();

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (Tools::isSubmit('changeSendMailVal') && $this->id_object) {
                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'change_send_mail_val';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }
            }
        } else {
            if (Tools::isSubmit('changeSendMailVal') && $this->id_object) {
                if ($this->access('edit')) {
                    $this->action = 'change_send_mail_val';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }
            }
        }

        /*if (!isset($_POST['submitFilterstockalert_subscriber'])) {
            $this->processResetFilters();
        }*/
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->context->smarty->clearAssign('help_link', '');
    }

    public function renderList()
    {
        if (Tools::isSubmit('submitBulksend_mailstockalert_subscriber')) {
            if (Tools::getValue('stockalert_subscriberBox')) {
                $stockAlertSubscribers = Tools::getValue('stockalert_subscriberBox');
                foreach ($stockAlertSubscribers as $stockAlertSubscriber) {
                    $stockAlertSubscriber = new StockAlertSubscriber((int) $stockAlertSubscriber);
                    $stockAlertSubscriber->send_mail = !$stockAlertSubscriber->send_mail;
                    $stockAlertSubscriber->save();
                }
                $this->confirmations[] = $this->l('Update successful');
            }
        }

        return parent::renderList();
    }

    public function renderForm()
    {
        return false;
    }

    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

        $tpl->assign([
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&delete' . $this->table . '&token=' . ($token != null ? $token : $this->token),
            'confirm' => $this->l('Delete the selected item?') . $name,
            'action' => $this->l('Delete'),
            'id' => $id,
        ]);

        return $tpl->fetch();
    }

    public function customerCallback($value)
    {
        if ($value == 0) {
            return $this->l('-');
        }

        $customer = new Customer($value);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminCustomers', true, ['customerId' => (int) $customer->id, 'viewcustomer' => true]) . '&viewcustomer&id_customer=' . (int) $customer->id . '" target="_blank">' . $customer->id . ' - ' . $customer->firstname . ' ' . $customer->lastname . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminCustomers') . '&id_customer=' . (int) $customer->id . '&viewcustomer" target="_blank">' . $customer->id . ' - ' . $customer->firstname . ' ' . $customer->lastname . '</a>';
    }

    public function productIdCallback($value, $object)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $object['id_product'], 'updateproduct' => 1]) . '" target="_blank">' . $value . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $object['id_product'] . '&updateproduct" target="_blank">' . $value . '</a>';
    }

    public function productAttributeIdCallback($value, $object)
    {
        if (!$value) {
            return '--';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $object['id_product'], 'updateproduct' => 1]) . '" target="_blank">' . $value . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $object['id_product'] . '&updateproduct" target="_blank">' . $value . '</a>';
    }

    public function productReferenceCallback($value, $object)
    {
        if (!$value) {
            return '--';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $object['id_product'], 'updateproduct' => 1]) . '" target="_blank">' . $value . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $object['id_product'] . '&updateproduct" target="_blank">' . $value . '</a>';
    }

    public function productNameCallback($value, $object)
    {
        $productName = Product::getProductName($object['id_product'], $object['id_product_attribute']);

        if (!$productName) {
            $productName = Product::getProductName($object['id_product']);
        }

        if (!$productName) {
            return '--';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $object['id_product'], 'updateproduct' => 1]) . '" target="_blank">' . $productName . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $object['id_product'] . '&updateproduct" target="_blank">' . $productName . '</a>';

        /*$img = Product::getCover($object['id_product']);
        $productImg = $this->context->link->getImageLink($object['id_product'].'.'.$object['id_product_attribute'], $img['id_image'], (version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small')));

        return '<div style="width: 75%; display: inline-block;">'.$productName.'</div><div style="width: 25%; display: inline-block;"><img style="max-width: 100%; height: auto;" src="'.$productImg.'"/></div>';*/
    }

    public function productImgCallback($value, $object)
    {
        $img = null;
        if ($object['id_product_attribute']) {
            $img = Product::getCombinationImageById($object['id_product_attribute'], $object['id_lang']);
        }

        if (!$img) {
            $img = Product::getCover($object['id_product']);
        }

        if ($img) {
            $productImg = $this->context->link->getImageLink($object['id_product'] . '.' . $object['id_product_attribute'], $img['id_image'], version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small'));
            $productName = Product::getProductName($object['id_product'], $object['id_product_attribute']);

            return '<img alt="' . $productName . '" title="' . $productName . '" src="' . $productImg . '"/>';
        }
    }

    public function productManufacturerCallback($value, $object)
    {
        $product = new Product((int) $object['id_product']);

        if ((int) $product->id_manufacturer) {
            return Manufacturer::getNameById((int) $product->id_manufacturer);
        }

        return '--';
    }

    public function languageCallback($value, $object)
    {
        $language = new Language($object['id_lang']);

        return $language->name;
    }

    public function sendMailCallback($value, $object)
    {
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            return '<a class="list-action-enable ' . ($value ? 'action-enabled' : 'action-disabled') . '" href="index.php?' . htmlspecialchars('tab=' . $this->controller_name . '&id_stockalert_subscriber=' . (int) $object['id_stockalert_subscriber'] . '&changeSendMailVal=1&token=' . Tools::getAdminTokenLite('AdminStockAlertSubscribers')) . '">' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=' . $this->controller_name . '&id_stockalert_subscriber=' . (int) $object['id_stockalert_subscriber'] . '&changeSendMailVal=1&token=' . Tools::getAdminTokenLite('AdminStockAlertSubscribers') . '">
                ' . ($value ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />') .
            '</a>';
        }
    }

    public function processChangeSendMailVal()
    {
        $stockAlertSubscriber = new StockAlertSubscriber($this->id_object);
        if (!Validate::isLoadedObject($stockAlertSubscriber)) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        $stockAlertSubscriber->send_mail = !$stockAlertSubscriber->send_mail;
        if (!$stockAlertSubscriber->save()) {
            $this->errors[] = Tools::displayError('An error occurred while updating object.');
        }

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (is_null($class)) {
            $class = $this->tabClassName . 'Controller';
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->module->l($string, $class);
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }
}
