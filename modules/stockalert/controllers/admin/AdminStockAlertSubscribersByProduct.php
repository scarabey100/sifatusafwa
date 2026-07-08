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

class AdminStockAlertSubscribersByProductController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'id_product';
    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'stockalert_subscriber';
        $this->className = 'StockAlertSubscriber';
        $this->identifier = 'id_stockalert_subscriber';
        $this->tabClassName = 'AdminStockAlertSubscribersByProduct';
        $this->addRowAction('view');
        $this->lang = false;
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

        $this->fields_list = [
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
            'subscribers' => [
                'title' => $this->l('Number of subscribers'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'search' => false,
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

        // Query
        $this->_select .= 'a.`id_product`, a.`id_product_attribute`, pl.`name` as product_name, 1 as product_img, COUNT(`id_customer`) as `subscribers`, 1 as product_manufacturer, p.reference';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $this->context->language->id . ' AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (a.`id_product` = p.`id_product` AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')';

        $this->_where .= "AND (`date_send` IS NULL OR `date_send` = '" . date('Y-m-d H:i:s', 0) . "')";

        $this->_group .= ' GROUP BY `id_product`, `id_product_attribute`, `id_shop`';
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

    public function renderForm()
    {
        return false;
    }

    public function displayViewLink($token, $id)
    {
        $stockAlertSubscriber = new StockAlertSubscriber((int) $id);

        $tpl = Context::getContext()->smarty->createTemplate('helpers/list/list_action_view.tpl');

        $params = [
            'submitFilterstockalert_subscriber' => 1,
            'stockalert_subscriberFilter_a!id_product' => (int) $stockAlertSubscriber->id_product,
            'stockalert_subscriberFilter_id_product_attribute' => (int) $stockAlertSubscriber->id_product_attribute ?: '',
        ];

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $href = Context::getContext()->link->getAdminLink('AdminStockAlertSubscribers', true, [], $params);
        } else {
            $href = Context::getContext()->link->getAdminLink('AdminStockAlertSubscribers', true) . '&' . http_build_query($params);
        }

        $tpl->assign([
            'href' => $href,
            'action' => $this->l('View'),
        ]);

        return $tpl->fetch();
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
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $object['id_product'], 'updateproduct' => 1]) . '" target="_blank">' . Product::getProductName($object['id_product'], $object['id_product_attribute']) . '</a>';
        }

        return '<a href="' . Context::getContext()->link->getAdminLink('AdminProducts') . '&id_product=' . (int) $object['id_product'] . '&updateproduct" target="_blank">' . Product::getProductName($object['id_product'], $object['id_product_attribute']) . '</a>';

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
