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

class StockAlertAccountModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        // Send noindex to avoid ghost carts by bots
        header('X-Robots-Tag: noindex', true);
    }

    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::initContent();

        if (!Context::getContext()->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&redirect=module&module=stockalert&action=account');
        }

        $idCustomer = Context::getContext()->customer->id;
        if ($idCustomer == null) {
            $idCustomer = 0;
        }

        if (Context::getContext()->customer->id) {
            $this->context->smarty->assign([
                'id_customer' => Context::getContext()->customer->id,
                'stockAlerts' => StockAlertSubscriber::getStockAlertsByCustomer((int) Context::getContext()->customer->id, (int) Context::getContext()->language->id),
            ]);

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->setTemplate('stockalert-account-16.tpl');

                return;
            }

            $this->setTemplate('module:stockalert/views/templates/front/stockalert-account-17.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();

        $page['body_classes']['page-customer-account'] = true;

        return $page;
    }

    public function postProcess()
    {
        if (Tools::getValue('process') === 'add') {
            $this->processAddStockAlertSubscriber();
        } elseif (Tools::getValue('process') === 'remove') {
            $this->processRemoveStockAlertSubscriber();
        } elseif (Tools::getValue('process') === 'check') {
            $this->processCheckStockAlertSubscriber();
        }
    }

    /**
     * Add an alert.
     */
    public function processAddStockAlertSubscriber()
    {
        $idProduct = (int) Tools::getValue('stockalert_id_product');
        $idProductAttribute = (int) Tools::getValue('stockalert_id_product_attribute');

        if (Configuration::get('SA_CAPTCHA')) {
            session_start();

            $captchaCode = Tools::getValue('stockalert_captcha_code');
            $captcha = $_SESSION['captchaKey'];

            if (!$captchaCode) {
                if ($this->ajax) {
                    echo json_encode(
                        [
                            'error' => true,
                            'message' => $this->module->l('Please introduce the captcha', 'account'),
                        ]
                    );
                    exit;
                }

                $this->errors[] = $this->module->l('Please introduce the captcha', 'account');
                $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
            } elseif( md5($captchaCode) != $captcha ) {
                if ($this->ajax) {
                    echo json_encode(
                        [
                            'error' => true,
                            'message' => $this->module->l('Incorrect captcha', 'account'),
                        ]
                    );
                    exit;
                }

                $this->errors[] = $this->module->l('Incorrect captcha', 'account');
                $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
            }
        }

        $context = Context::getContext();

        if ($context->customer->isLogged()) {
            $idCustomer = (int) $context->customer->id;
            $customer = new Customer($idCustomer);
            $customer_email = (string) trim($customer->email);
        } elseif (Validate::isEmail((string) Tools::getValue('stockalert_customer_email'))) {
            $customer_email = (string) trim(Tools::getValue('stockalert_customer_email'));
            $customer = $context->customer->getByEmail($customer_email);
            $idCustomer = (isset($customer->id) && ($customer->id != null)) ? (int) $customer->id : null;
        } else {
            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => true,
                        'message' => $this->module->l('Your e-mail address is invalid', 'account'),
                    ]
                );
                exit;
            }

            $this->errors[] = $this->module->l('Your e-mail address is invalid', 'account');
            $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
        }

        $send_mail = (int) Tools::getValue('stockalert_send_mail');
        $id_shop = (int) $context->shop->id;
        $id_lang = (int) $context->language->id;
        $product = new Product($idProduct, false, $id_lang, $id_shop, $context);

        $stockAlertSubscriber = StockAlertSubscriber::customerHasNotification($idCustomer, $idProduct, $idProductAttribute, $id_shop, null, $customer_email);

        if ($stockAlertSubscriber) {
            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => true,
                        'message' => $this->module->l('You already have an alert for this product', 'account'),
                    ]
                );
                exit;
            }

            $this->errors[] = $this->module->l('You already have an alert for this product', 'account');
            $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
        }

        if (!Validate::isLoadedObject($product)) {
            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => true,
                        'message' => $this->module->l('The product object cannot be loaded.', 'account'),
                    ]
                );
                exit;
            }

            $this->errors[] = $this->module->l('The product object cannot be loaded.', 'account');
            $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
        }

        $stockAlertSubscriber = new StockAlertSubscriber();

        $stockAlertSubscriber->id_customer = (int) $idCustomer;
        $stockAlertSubscriber->customer_email = (string) $customer_email;
        $stockAlertSubscriber->id_product = (int) $idProduct;
        $stockAlertSubscriber->id_product_attribute = (int) $idProductAttribute;
        $stockAlertSubscriber->send_mail = (int) $send_mail;
        $stockAlertSubscriber->id_shop = (int) $id_shop;
        $stockAlertSubscriber->id_lang = (int) $id_lang;
        $stockAlertSubscriber->date_send = date('Y-m-d H:i:s', 0);
        $stockAlertSubscriber->id_stockalert_alert = (int) Tools::getValue('stockalert_id_stockalert_alert');

        if ($stockAlertSubscriber->add() !== false) {
            // Send email
            $stockAlertAlert = new StockAlertAlert($stockAlertSubscriber->id_stockalert_alert);

            if ($stockAlertAlert->send_mail_admin
                && $stockAlertAlert->send_mail_admin_addresses) {
                $img = null;
                if ($idProductAttribute) {
                    $img = Product::getCombinationImageById($idProductAttribute, $id_lang);
                }

                if (!$img) {
                    $img = Product::getCover($idProduct);
                }

                $template_vars = [
                    '{customer}' => $customer_email,
                    '{product}' => Product::getProductName($idProduct, $idProductAttribute, $id_lang),
                    '{product_name}' => Product::getProductName($idProduct, $idProductAttribute, $id_lang),
                    '{product_link}' => $context->link->getProductLink($product, $product->link_rewrite, null, null, $id_lang, $id_shop, $idProductAttribute),
                    '{product_img}' => $context->link->getImageLink($product->link_rewrite, $img['id_image'], version_compare(_PS_VERSION_, '1.7', '<') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small')),
                ];

                $template = 'stockalert-admin';
                $iso = Language::getIsoById((int) $id_lang);
                $isoTemplate = $iso . '/' . $template;
                $templatePath = $this->module->getTemplateBasePath($isoTemplate, $this->module->name, Context::getContext()->shop->theme);

                // English template is required always
                if (!file_exists($templatePath . 'en/' . $template . '.txt')
                    || !file_exists($templatePath . 'en/' . $template . '.html')) {
                    // Copy emails
                    $this->module->copyMailsFolder($templatePath);
                }

                if (!file_exists($templatePath . $iso . '/' . $template . '.txt')
                    || !file_exists($templatePath . $iso . '/' . $template . '.html')) {
                    // Copy emails
                    $this->module->copyMailsFolder($templatePath);
                }

                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $subject = Translate::getModuleTranslation($this->module->name, 'New stock alert', static::class, null, false, Language::getLocaleByIso($iso));
                } else {
                    $context->shop->id = $id_shop;
                    $context->language->id = $id_lang;
                    $subject = Translate::getModuleTranslation($this->module->name, 'New stock alert', static::class);
                }

                try {
                    Mail::Send(
                        $id_lang,
                        $template,
                        $subject,
                        $template_vars,
                        explode(';', $stockAlertAlert->send_mail_admin_addresses),
                        null,
                        (string) Configuration::get('PS_SHOP_EMAIL', null, null, $id_shop),
                        (string) Configuration::get('PS_SHOP_NAME', null, null, $id_shop),
                        null,
                        null,
                        _PS_MODULE_DIR_ . $this->module->name . '/mails/',
                        false,
                        $id_shop,
                        Configuration::get('SA_BCC') ? explode(';', Configuration::get('SA_BCC')) : null,
                        $customer_email
                    );
                } catch (Exception $e) {
                    /*
                     * Something went wrong but don't care we need to continue.
                     * This can be caused by an invalid e-mail address.
                     */
                    PrestaShopLogger::addLog(
                        sprintf(
                            'Stockalert error: Could not send email to address [%s] because %s',
                            Configuration::get('PS_SHOP_EMAIL'),
                            $e->getMessage()
                        ),
                        3
                    );
                }
            }

            $this->module->actionsAfterSubscribe();

            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => false,
                        'message' => $this->module->l('Stock alert registered succesfully', 'account'),
                    ]
                );
                exit;
            }

            $this->context->smarty->assign([
                'success' => $this->module->l('Stock alert registered succesfully', 'account'),
            ]);

            $this->success[] = $this->module->l('Stock alert registered succesfully', 'account');

            $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
        }

        if ($this->ajax) {
            echo json_encode(
                [
                    'error' => true,
                    'message' => $this->module->l('Error registering your stock alert', 'account'),
                ]
            );
            exit;
        }

        $this->errors[] = $this->module->l('Error registering your stock alert', 'account');
        $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
    }

    /**
     * Remove an alert.
     */
    public function processRemoveStockAlertSubscriber()
    {
        $stockAlertSubscriber = new StockAlertSubscriber((int) Tools::getValue('stockalert_id_stockalert_subscriber'));

        $idProduct = (int) Tools::getValue('stockalert_id_product') ?: null;

        if (!Validate::isLoadedObject($stockAlertSubscriber)) {
            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => true,
                        'message' => $this->module->l('Stock alert not found', 'account'),
                    ]
                );
                exit;
            }

            $this->errors[] = $this->module->l('Stock alert not found', 'account');

            if ($idProduct) {
                $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
            }
        }

        if (!StockAlertSubscriber::deleteAlert((int) Tools::getValue('stockalert_id_stockalert_subscriber'))) {
            if ($this->ajax) {
                echo json_encode(
                    [
                        'error' => true,
                        'message' => $this->module->l('You can not remove this stock alert', 'account'),
                    ]
                );
                exit;
            }

            $this->errors[] = $this->module->l('You can not remove this stock alert', 'account');

            if ($idProduct) {
                $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
            }
        }

        if ($this->ajax) {
            echo json_encode(
                [
                    'error' => false,
                    'message' => $this->module->l('Stock alert removed successfully', 'account'),
                ]
            );
            exit;
        }

        $this->context->smarty->assign([
            'success' => $this->module->l('Stock alert removed successfully', 'account'),
        ]);

        $this->success[] = $this->module->l('Stock alert removed successfully', 'account');

        if ($idProduct) {
            $this->redirectWithNotifications($this->context->link->getProductLink($idProduct));
        }
    }

    /* Only for PS < 1.7 */
    public function processCheckStockAlertSubscriber()
    {
        if (!$this->ajax) {
            return;
        }

        $idCustomer = (int) $this->context->customer->id;
        $idProduct = (int) Tools::getValue('stockalert_id_product');
        $idProductAttribute = (int) Tools::getValue('stockalert_id_product_attribute');

        if (!$idProduct) {
            echo json_encode(
                [
                    'error' => true,
                ]
            );
            exit;
        }

        $stockAlert = StockAlertAlert::getStockAlertByProduct($idProduct, $idProductAttribute);

        // Product has no alert
        if (!$stockAlert) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '3',
                ]
            );
            exit;
        }

        $quantity = Product::getQuantity($idProduct, $idProductAttribute);
        if ($quantity > $stockAlert['stock']) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '3',
                ]
            );
            exit;
        }

        if (Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($idProduct))
            && !$stockAlert['out_of_stock']) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '3',
                ]
            );
            exit;
        }

        // Product has alert and is guest
        if (!(int) $this->context->customer->logged) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '4',
                    'stockAlert' => json_encode($stockAlert),
                ]
            );
            exit;
        }

        $stockAlertSubscriber = StockAlertSubscriber::customerHasNotification((int) $idCustomer, (int) $idProduct, (int) $idProductAttribute, (int) $this->context->shop->id);

        // Customer already has an alert
        if ($stockAlertSubscriber) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '2',
                    'stockAlert' => json_encode($stockAlertSubscriber),
                ]
            );
            exit;
        }

        // Product has alert and customer is not subscribed
        if ($stockAlert) {
            echo json_encode(
                [
                    'error' => false,
                    'result' => '1',
                    'stockAlert' => json_encode($stockAlert),
                ]
            );
            exit;
        }
    }

    // Translations in backoffice
    public function translations()
    {
        $this->module->l('New stock alert'); // Email subject
    }
}
