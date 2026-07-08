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

include_once dirname(__FILE__) . '/classes/StockAlertSubscriber.php';
include_once dirname(__FILE__) . '/classes/StockAlertAlert.php';
include_once dirname(__FILE__) . '/classes/StockAlertIdnovateValidation.php';

class StockAlert extends Module
{
    public $moduleTabs;

    /* Main functions */
    public function __construct()
    {
        $this->name = 'stockalert';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'idnovate';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '188c04ee1ac90cee5e4f15891a8f5564';
        $this->addons_id_product = '49338';
        $this->ps_versions_compliancy = ['min' => '1.5', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Stock Alert');
        $this->description = $this->l('Sends e-mail notifications to customers regarding stock.');

        $this->moduleTabs = [
            [
                'class_name' => 'STOCKALERT',
                'parent_class_name' => 'SELL',
                'name' => [
                    'en' => 'Stock alerts',
                    'es' => 'Alertas de stock',
                ],
                'module' => $this->name,
                'icon' => 'add_alert',
            ],
            [
                'class_name' => 'AdminStockAlertConfiguration',
                'name' => [
                    'en' => 'Module configuration',
                    'es' => 'Configuración del módulo',
                ],
                'parent_class_name' => 'STOCKALERT',
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminStockAlertAlerts',
                'name' => [
                    'en' => 'Alerts configuration',
                    'es' => 'Configuración de las alertas',
                ],
                'parent_class_name' => 'STOCKALERT',
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminStockAlertSubscribers',
                'name' => [
                    'en' => 'Alerts',
                    'es' => 'Alertas',
                ],
                'parent_class_name' => 'STOCKALERT',
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminStockAlertSubscribersByProduct',
                'name' => [
                    'en' => 'Alerts by product',
                    'es' => 'Alertas por producto',
                ],
                'parent_class_name' => 'STOCKALERT',
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminStockAlertSubscribersSent',
                'name' => [
                    'en' => 'Alerts sent',
                    'es' => 'Alertas enviadas',
                ],
                'parent_class_name' => 'STOCKALERT',
                'module' => $this->name,
            ],
        ];
    }

    public function install()
    {
        $result = true;

        $result &= $this->copyOverrideFolder();
        $result &= parent::install();
        $result &= include dirname(__FILE__) . '/sql/install.php';
        $result &= $this->createFirstAlert();
        $result &= $this->installTabs($this->moduleTabs);
        $result &= $this->registerHook('actionUpdateQuantity');
        $result &= $this->registerHook('actionProductDelete');
        $result &= $this->registerHook('actionProductAttributeDelete');
        $result &= $this->registerHook('actionProductAttributeUpdate');
        $result &= $this->registerHook('actionAttributeDelete');
        $result &= $this->registerHook('registerGDPRConsent');
        $result &= $this->registerHook('actionDeleteGDPRCustomer');
        $result &= $this->registerHook('actionExportGDPRData');
        $result &= version_compare(_PS_VERSION_, '1.7', '>=') || $this->registerHook('actionProductOutOfStock');
        // $result &= version_compare(_PS_VERSION_, '1.7', '<') || $this->registerHook('displayProductAdditionalInfo');
        $result &= $this->registerHook('displayHeader');
        $result &= $this->registerHook('displayCustomerAccount');
        $result &= $this->registerHook('displayMyAccountBlock');
        $result &= $this->registerHook('displayMyAccountBlockfooter');
        $result &= $this->registerHook('displayProductPriceBlock');
        $result &= $this->registerHook('displayBackOfficeHeader');
        $result &= $this->registerHook('registerGDPRConsent');
        $result &= $this->registerHook('displayOutOfStockBlock');
        $result &= $this->registerHook('displayProductListReviews');
        $result &= $this->registerHook('displayOutOfStockProductListBlock');
        $result &= $this->registerHook('actionAjaxDieProductControllerdisplayAjaxRefreshBefore');
        if (version_compare(_PS_VERSION_, '1.7', '>=')
            && $this->context->shop->theme->getName() === 'panda') {
            $result &= Configuration::updateValue('SA_ICONS_LIBRARY', 2);
        } else {
            $result &= Configuration::updateValue('SA_ICONS_LIBRARY', 1);
        }

        return (bool) $result;
    }

    public function uninstall()
    {
        $result = true;

        $result &= $this->copyOverrideFolder();
        $result &= parent::uninstall();
        $result &= include dirname(__FILE__) . '/sql/uninstall.php';
        $result &= $this->uninstallTabs($this->moduleTabs);

        return (bool) $result;
    }

    public function enable($force_all = false)
    {
        if (!$this->copyOverrideFolder()) {
            return false;
        }

        return parent::enable($force_all);
    }

    public function copyOverrideFolder()
    {
        if (version_compare(_PS_VERSION_, '8.1', '>=')) {
            return true;
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return true;
        }

        if (!is_writable(_PS_MODULE_DIR_ . $this->name)) {
            return false;
        }

        $override_folder_name_from = 'override_';

        // Rename /_override to /override
        $override_folder_from = _PS_MODULE_DIR_ . $this->name . '/' . $override_folder_name_from;
        $override_folder_to = _PS_MODULE_DIR_ . $this->name . '/override';

        if (file_exists($override_folder_to) && is_dir($override_folder_to)) {
            $this->recursiveRmdir($override_folder_to);
        }

        if (is_dir($override_folder_from)) {
            $this->copyDir($override_folder_from, $override_folder_to);
        }

        return true;
    }

    public function copyDir($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            if (!mkdir($dst) && !is_dir($dst)) {
                throw new PrestaShopException(sprintf('Directory "%s" was not created', $dst));
            }
            while (false !== ($file = readdir($dir))) {
                if (($file !== '.') && ($file !== '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        if (!file_exists($dst) && !mkdir($dst, 0755, true)
                            && !is_dir($dst)) {
                            throw new PrestaShopException(sprintf('Directory "%s" was not created', $dst));
                        }
                        if (is_writable(dirname($dst))) {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        } else {
                            return false;
                        }
                    }
                }
            }
            closedir($dir);
        }
    }

    protected function recursiveRmdir($dir)
    {
        if (substr($dir, -1) !== '/') {
            $dir .= '/';
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . $object) === 'dir') {
                        $this->recursiveRmdir($dir . $object);
                    } else {
                        unlink($dir . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function getContent()
    {
        return Tools::redirectAdmin('index.php?controller=AdminStockAlertAlerts&token=' . Tools::getAdminTokenLite('AdminStockAlertAlerts'));
    }

    /* Hooks */
    public function hookDisplayBackOfficeHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')
            && method_exists($this->context->controller, 'addCSS')) {
            $this->context->controller->addCSS($this->_path . 'views/css/menuTabIcon.css');
        }
    }

    public function hookActionUpdateQuantity($params)
    {
        return StockAlertSubscriber::sendStockAlertSubscriberAlerts((int) $params['id_product'], (int) $params['id_product_attribute'], (int) $params['quantity']);
    }

    public function hookActionProductDelete($params)
    {
        $sql = '
            DELETE FROM `' . _DB_PREFIX_ . StockAlertSubscriber::$definition['table'] . '`
            WHERE `id_product` = ' . (int) $params['product']->id;

        Db::getInstance()->execute($sql);
    }

    public function hookActionProductAttributeDelete($params)
    {
        if ($params['deleteAllAttributes']) {
            $sql = '
                DELETE FROM `' . _DB_PREFIX_ . StockAlertSubscriber::$definition['table'] . '`
                WHERE `id_product` = ' . (int) $params['id_product'];
        } else {
            $sql = '
                DELETE FROM `' . _DB_PREFIX_ . StockAlertSubscriber::$definition['table'] . '`
                WHERE `id_product_attribute` = ' . (int) $params['id_product_attribute'] . '
                AND `id_product` = ' . (int) $params['id_product'];
        }

        Db::getInstance()->execute($sql);
    }

    public function hookActionAttributeDelete($params)
    {
        if ($params['deleteAllAttributes']) {
            $sql = '
                DELETE FROM `' . _DB_PREFIX_ . StockAlertSubscriber::$definition['table'] . '`
                WHERE `id_product` = ' . (int) $params['id_product'];
        } else {
            $sql = '
                DELETE FROM `' . _DB_PREFIX_ . StockAlertSubscriber::$definition['table'] . '`
                WHERE `id_product_attribute` = ' . (int) $params['id_product_attribute'] . '
                AND `id_product` = ' . (int) $params['id_product'];
        }

        Db::getInstance()->execute($sql);
    }

    public function hookActionProductAttributeUpdate($params)
    {
        $sql = '
            SELECT `id_product`, `quantity`
            FROM `' . _DB_PREFIX_ . 'stock_available`
            WHERE `id_product_attribute` = ' . (int) $params['id_product_attribute'];

        $result = Db::getInstance()->getRow($sql);

        return StockAlertSubscriber::sendStockAlertSubscriberAlerts((int) $result['id_product'], (int) $params['id_product_attribute'], $result['quantity']);
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . "stockalert_subscriber WHERE customer_email = '" . pSQL($customer['email']) . "'";
            if (Db::getInstance()->execute($sql)) {
                return json_encode(true);
            }

            return json_encode($this->l('Stock alert: Unable to delete customer using email.'));
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . "stockalert_subscriber WHERE customer_email = '" . pSQL($customer['email']) . "'";
            if ($res = Db::getInstance()->ExecuteS($sql)) {
                return json_encode($res);
            }

            return json_encode($this->l('Stock alert: Unable to export customer using email.'));
        }
    }

    public function hookDisplayProductButtons($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    // For PS < 1.7
    public function hookActionProductOutOfStock($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    // For PS >= 1.7
    public function hookDisplayReassurance($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    public function hookDisplayProductActions($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    public function hookDisplayProductListReviews($params)
    {
        return $this->displayBlockInProductListPage($params);
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (isset($params['product'])) {
            if (is_array($params['product'])) {
                $idProduct = (int)$params['product']['id_product'];
            } else {
                $idProduct = (int)$params['product']->id;
            }

            if ($idProduct === (int)Tools::getValue('id_product')) {
                if (Dispatcher::getInstance()->getController() === 'product'
                    && $params['type'] === 'after_price') {
                    return $this->displayBlockInProductPage($params);
                }
            }
        }
    }

    public function hookDisplayRightColumnProduct($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    public function hookDisplayLeftColumnProduct($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    // Custom hook
    public function hookDisplayOutOfStockBlock($params)
    {
        return $this->displayBlockInProductPage($params);
    }

    public function hookDisplayOutOfStockProductListBlock($params)
    {
        return $this->displayBlockInProductListPage($params);
    }

    public function displayBlockInProductPage($params)
    {
        if (!Configuration::get('PS_STOCK_MANAGEMENT')) {
            return false;
        }

        // Get needed vars
        if ($this->context->customer->isLogged()) {
            $idCustomer = $this->context->customer->id;
        } else {
            $idCustomer = 0;
        }

        if ($params
            && isset($params['product'])) {
            if (is_array($params['product'])) {
                $idProduct = $params['product']['id_product'];
                $idProductAttribute = $params['product']['id_product_attribute'];
            } else {
                $idProduct = $params['product']->id;
                $idProductAttribute = isset($params['product']->id_product_attribute) ? $params['product']->id_product_attribute : null;
            }
        }

        if (empty($idProduct)) {
            $idProduct = Tools::getValue('id_product');
        }

        if (empty($idProductAttribute)) {
            $idProductAttribute = Tools::getValue('id_product_attribute');
        }

        if (empty($idProductAttribute)) {
            $idProductAttribute = Product::getDefaultAttribute($idProduct);
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $html = '';

            $html .= $this->display(__FILE__, 'product-remove-16.tpl');

            // don't send $idProductAttribute param. Display block if any attribute from the product can have it
            // $stockAlert = StockAlertAlert::getStockAlertByProduct($idProduct);

            $this->context->smarty->assign([
                'is_guest' => !$idCustomer,
                'id_module' => $this->id,
            ]);

            $html .= $this->display(__FILE__, 'product-add-16.tpl');

            return $html;
        }

        $stockAlertSubscriber = StockAlertSubscriber::customerHasNotification($idCustomer, $idProduct, $idProductAttribute, (int) $this->context->shop->id);
        if ($stockAlertSubscriber) {
            if ($idCustomer <= 0) {
                $this->context->smarty->assign('email', 1);
            }

            $this->context->smarty->assign([
                'id_product' => $idProduct,
                'id_stockalert_subscriber' => $stockAlertSubscriber['id_stockalert_subscriber'],
            ]);

            return $this->display(__FILE__, 'product-remove-17.tpl');
        }

        $stockAlert = StockAlertAlert::getStockAlertByProduct($idProduct, $idProductAttribute);

        if (!$stockAlert) {
            return false;
        }

        $quantity = Product::getQuantity($idProduct, $idProductAttribute);
        if ((int) $quantity > (int) $stockAlert['stock']) {
            return '<di' . 'v class="stockalert-add-container"></div>';
        }

        if (Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($idProduct))
            && !$stockAlert['out_of_stock']) {
            return '<di' . 'v class="stockalert-add-container"></div>';
        }

        $this->context->smarty->assign([
            'is_guest' => !$idCustomer,
            'id_product' => $idProduct,
            'id_product_attribute' => $idProductAttribute,
            'send_mail' => $stockAlert['send_mail'],
            'id_stockalert_alert' => $stockAlert['id_stockalert_alert'],
            'stockalert_popup' => $stockAlert['popup'],
            'custom_text' => $stockAlert['custom_text'],
            'id_module' => $this->id,
            'isAjax' => Tools::getValue('ajax') || Tools::isSubmit('ajax'),
            'displayCaptcha' => Configuration::get('SA_CAPTCHA'),
            'captchaController' => $this->context->link->getModuleLink($this->name, 'captcha', [], true),
        ]);

        return '<di' . 'v class="stockalert-add-container">' . $this->display(__FILE__, 'product-add-17.tpl') . '</div>';
    }

    public function displayBlockInProductListPage($params)
    {
        if (!Configuration::get('PS_STOCK_MANAGEMENT')) {
            return false;
        }

        // Get needed vars
        if ($this->context->customer->isLogged()) {
            $idCustomer = $this->context->customer->id;
        } else {
            $idCustomer = 0;
        }

        if (is_array($params['product'])) {
            $idProduct = $params['product']['id_product'];
            $idProductAttribute = $params['product']['id_product_attribute'];
        } else {
            $idProduct = $params['product']->id;
            $idProductAttribute = isset($params['product']->id_product_attribute) ? $params['product']->id_product_attribute : null;
        }

        if (empty($idProductAttribute)) {
            $idProductAttribute = Tools::getValue('id_product_attribute');
        }

        if (empty($idProductAttribute)) {
            $idProductAttribute = Product::getDefaultAttribute($idProduct);
        }

        $stockAlert = StockAlertAlert::getStockAlertByProduct($idProduct, $idProductAttribute);

        if (!$stockAlert) {
            return false;
        }

        if ($this->context->controller->php_self == 'category'
            && !$stockAlert['product_list']) {
            return false;
        }

        if ($this->context->controller->php_self == 'product'
            && !$stockAlert['product_list_product']) {
            return false;
        }

        if (count(Product::getProductAttributesIds($params['product']->id)) > 1) {
            // If there's no stock of any combination
            if (!StockAvailable::getQuantityAvailableByProduct($params['product']->id)) {
                 $this->context->smarty->assign([
                    'id_product' => $idProduct,
                    'productLink' => $this->context->link->getProductLink($params['product']->id),
                ]);

                return $this->display(__FILE__, 'product-list-add-dummy-17.tpl');
            }

            return false;
        }

        $this->context->smarty->assign([
            'is_guest' => !$idCustomer,
            'id_product' => $idProduct,
            'id_product_attribute' => $idProductAttribute,
            'id_module' => $this->id,
            'link' => $this->context->link,
            'displayCaptcha' => Configuration::get('SA_CAPTCHA'),
            'captchaController' => $this->context->link->getModuleLink($this->name, 'captcha', [], true),
        ]);

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $html = '';

            $html .= $this->display(__FILE__, 'product-remove-16.tpl');

            // don't send $idProductAttribute param. Display block if any attribute from the product can have it
            // $stockAlert = StockAlertAlert::getStockAlertByProduct($idProduct);

            $html .= $this->display(__FILE__, 'product-add-16.tpl');

            return $html;
        }

        $stockAlertSubscriber = StockAlertSubscriber::customerHasNotification($idCustomer, $idProduct, $idProductAttribute, (int) $this->context->shop->id);
        if ($stockAlertSubscriber) {
            if ($idCustomer <= 0) {
                $this->context->smarty->assign('email', 1);
            }

            $this->context->smarty->assign([
                'id_stockalert_subscriber' => $stockAlertSubscriber['id_stockalert_subscriber'],
            ]);

            return $this->display(__FILE__, 'product-list-remove-17.tpl');
        }

        $quantity = Product::getQuantity($idProduct, $idProductAttribute);
        if ($quantity > $stockAlert['stock']) {
            return false;
        }

        if (Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($idProduct))
            && !$stockAlert['out_of_stock']) {
            return false;
        }

        $this->context->smarty->assign([
            'send_mail' => $stockAlert['send_mail'],
            'id_stockalert_alert' => $stockAlert['id_stockalert_alert'],
            'stockalert_popup' => $stockAlert['popup'],
        ]);

        return $this->display(__FILE__, 'product-list-add-17.tpl');
    }

    public function hookDisplayCustomerAccount($params)
    {
        $this->context->smarty->assign([
            'SA_ICONS_LIBRARY' => Configuration::get('SA_ICONS_LIBRARY'),
        ]);

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return $this->display(__FILE__, 'my-account-16.tpl');
        }

        return $this->display(__FILE__, 'my-account-17.tpl');
    }

    public function hookDisplayMyAccountBlock()
    {
        return $this->display(__FILE__, 'my-account-footer.tpl');
    }

    public function hookDisplayMyAccountBlockfooter()
    {
        return $this->hookDisplayMyAccountBlock();
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJqueryPlugin('fancybox');

        // if (in_array(Dispatcher::getInstance()->getController(), array('product', 'account'))) {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addCSS($this->_path . 'views/css/stockalert-front-16.css');
            $this->context->controller->addJS($this->_path . 'views/js/stockalert-front-16.js');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/stockalert-front-17.css');
            $this->context->controller->addJS($this->_path . 'views/js/stockalert-front-17.js');
        }
        // }

        $this->context->smarty->assign(
            [
                'SA_JS' => Configuration::get('SA_JS'),
                'SA_CSS' => Configuration::get('SA_CSS'),
                'stockalert_url_check' => $this->context->link->getModuleLink($this->name, 'account', ['process' => 'check'], true),
                'stockalert_url_add' => $this->context->link->getModuleLink($this->name, 'account', ['process' => 'add'], true),
                'stockalert_url_remove' => $this->context->link->getModuleLink($this->name, 'account', ['process' => 'remove'], true),
            ]
        );

        return $this->display(__FILE__, 'stockalert-vars.tpl');
    }

    /**
     * empty listener for registerGDPRConsent hook
     */
    public function hookRegisterGDPRConsent()
    {
        /* registerGDPRConsent is a special kind of hook that doesn't need a listener, see :
           https://build.prestashop.com/howtos/module/how-to-make-your-module-compliant-with-prestashop-official-gdpr-compliance-module/
          However since Prestashop 1.7.8, modules must implement a listener for all the hooks they register: a check is made
          at module installation.
        */
    }

    public function hookActionAjaxDieProductControllerdisplayAjaxRefreshBefore($params)
    {
        $json = json_decode($params['value'], true);

        $params['product']['id_product'] = Tools::getValue('id_product');

        $groups = Tools::getValue('group');

        if (empty($groups)) {
            $params['product']['id_product_attribute'] = 0;
        } else {
            $params['product']['id_product_attribute'] = Product::getIdProductAttributeByIdAttributes(
                Tools::getValue('id_product'),
                $groups,
                true
            );
        }

        $json['product_out_of_stock'] = $this->displayBlockInProductPage($params);

        $params['value'] = json_encode($json);

        /*
        $value = json_decode($params['value'], true);
        $value['foo'] = 'bar';

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        echo json_encode($value);
        exit();
        */
    }

    /* Additional functions */
    public function importAlertsFromMailAlertsModule()
    {
        $tableExists = Db::getInstance()->getRow(
            "SELECT *
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
                    AND TABLE_NAME = '" . _DB_PREFIX_ . "mailalert_customer_oos';"
        );

        if ($tableExists) {
            $query = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'stockalert_subscriber` (`id_customer`, `customer_email`, `id_product`, `id_product_attribute`, `id_shop`, `id_lang`, `send_mail`, `date_add`, `date_send`)
            SELECT `id_customer`, `customer_email`, `id_product`, `id_product_attribute`, `id_shop`, `id_lang`, 1, "' . date('Y-m-d H:i:s', 0) . '", "' . date('Y-m-d H:i:s', 0) . '"
            FROM `' . _DB_PREFIX_ . 'mailalert_customer_oos`;';

            return Db::getInstance()->execute($query);
        }

        return true;
    }

    public function importAlertsFromBackInStockModule()
    {
        $tableExists = Db::getInstance()->getRow(
            "SELECT *
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
                    AND TABLE_NAME = '" .  _DB_PREFIX_ . "product_update_product_detail';"
        );

        if ($tableExists) {
            $query = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'stockalert_subscriber` (`id_customer`, `customer_email`, `id_product`, `id_product_attribute`, `id_shop`, `id_lang`, `send_mail`, `date_add`, `date_send`)
            SELECT `customer_id`, `email`, `product_id`, `product_attribute_id`, `store_id`, 
                COALESCE(
                 (SELECT id_lang FROM `' . _DB_PREFIX_ . 'lang` WHERE iso_code = `lang_iso` LIMIT 1), 
                 (SELECT id_lang FROM `' . _DB_PREFIX_ . 'lang` ORDER BY id_lang ASC LIMIT 1)
               ) AS id_lang, 
                1, "' . date('Y-m-d H:i:s', 0) . '", "' . date('Y-m-d H:i:s', 0) . '"
            FROM `' . _DB_PREFIX_ . 'product_update_product_detail`
            WHERE email <> "" AND active = "1" AND send = "0";';

            return Db::getInstance()->execute($query);
        }

        return true;
    }

    public function copyMailsFolder($templatePath = null)
    {
        if (version_compare(_PS_VERSION_, '8', '>=')) {
            $version = '17';
        } elseif (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $version = '17';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $version = '16';
        } else {
            $version = '15';
        }

        $mailsFolder = _PS_MODULE_DIR_ . $this->name . '/mails_' . $version . '/';

        if (!$templatePath) {
            $templatePath = _PS_MODULE_DIR_ . $this->name . '/mails/';
        }

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if (!file_exists($mailsFolder . $language['iso_code'])) {
                $this->recurseCopy($mailsFolder . 'en', $templatePath . $language['iso_code']);
            } else {
                $this->recurseCopy($mailsFolder . $language['iso_code'], $templatePath . $language['iso_code']);
            }
        }

        return true;
    }

    protected function recurseCopy($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        if (!file_exists($dst)) {
                            mkdir($dst, 0755, true);
                        }
                        if (is_writable(dirname($dst))) {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        } else {
                            return false;
                        }
                    }
                }
            }
            closedir($dir);
        }
    }

    public function createFirstAlert()
    {
        $defaultLanguage = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $stockAlertAlertName = [
            'en' => 'All products',
            'es' => 'Todos los productos',
        ];

        $shops = Shop::getShops(false, null, true);
        foreach ($shops as $shop) {
            $stockAlertAlert = new StockAlertAlert();
            $stockAlertAlert->id_shop = $shop;
            $stockAlertAlert->name = isset($stockAlertAlertName[$defaultLanguage->iso_code]) ? $stockAlertAlertName[$defaultLanguage->iso_code] : $stockAlertAlertName['en'];

            $stockAlertAlert->save();
        }

        return true;
    }

    public function installTabs($moduleTabs = null)
    {
        if (!$moduleTabs) {
            $moduleTabs = $this->moduleTabs;
        }

        $languages = Language::getLanguages(false);

        foreach ($moduleTabs as $moduleTab) {
            if (!Tab::getIdFromClassName($moduleTab['class_name'])) {
                $tab = new Tab();
                $tab->class_name = $moduleTab['class_name'];
                $tab->module = $moduleTab['module'];
                $tab->active = 1;

                foreach ($languages as $language) {
                    if (is_array($moduleTab['name'])) {
                        if (isset($moduleTab['name'][$language['iso_code']])) {
                            $tab->name[$language['id_lang']] = $moduleTab['name'][$language['iso_code']];
                        } else {
                            $tab->name[$language['id_lang']] = $moduleTab['name']['en'];
                        }
                    } else {
                        $tab->name[$language['id_lang']] = $moduleTab['name'];
                    }
                }

                if (isset($moduleTab['parent_class_name']) && is_string($moduleTab['parent_class_name'])) {
                    $tab->id_parent = Tab::getIdFromClassName($moduleTab['parent_class_name']);
                } elseif (isset($moduleTab['id_parent'])) {
                    $tab->id_parent = $moduleTab['id_parent'];
                } else {
                    $tab->id_parent = -1;
                }

                if (isset($moduleTab['icon'])) {
                    $tab->icon = $moduleTab['icon'];
                }

                $tab->add();
                if (!$tab->id) {
                    return false;
                }
            }
        }

        return true;
    }

    public function uninstallTabs($moduleTabs = null)
    {
        if (!$moduleTabs) {
            $moduleTabs = $this->moduleTabs;
        }

        foreach ($moduleTabs as $moduleTab) {
            $idTab = Tab::getIdFromClassName($moduleTab['class_name']);
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        return true;
    }

    public function getWarnings($getAll = true)
    {
        $warning = [];

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
                $warning[] = sprintf($this->l('%1$s "%2$s" at %3$s - %4$s'), $this->l('Disable'), $this->l('Disable non PrestaShop modules'), $this->l('Advanced Parameters'), $this->l('Performance'));
            }
        }

        if ((version_compare(_PS_VERSION_, '1.5.0.13', '<') && !Module::isInstalled($this->name))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=') && !Module::isEnabled($this->name))) {
            $warning[] = $this->l('Module is not enabled in this shop.');
        }

        if ((bool) Configuration::get('MA_CUSTOMER_QTY')
            && ((version_compare(_PS_VERSION_, '1.5.0.13', '<') && Module::isInstalled('ps_emailalerts'))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=') && Module::isEnabled('ps_emailalerts'))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=') && Module::isEnabled('mailalerts')))
        ) {
            $warning[] = sprintf($this->l('Please disable "%1$s" option from "%2$s" module.'), $this->l('Product availability'), $this->l('Mail Alerts'));
        }

        if (!Configuration::get('PS_DISP_UNAVAILABLE_ATTR')) {
            $warning[] = sprintf($this->l('Please enable "%1$s" option from "%2$s" menu.'), $this->l('Display unavailable attributes on the product page'), $this->l('Product Settings')) . '<br />' . $this->l('If you don\'t enable this option, you won\'t be able to get subscribed to combinations without stock.');
        }

        if ((!Shop::isFeatureActive()
                || (Shop::isFeatureActive()
                    && (Shop::getContext() != Shop::CONTEXT_ALL && Shop::getContext() != Shop::CONTEXT_GROUP)))
            && !Configuration::get('PS_STOCK_MANAGEMENT')) {
            $warning[] = $this->l('Stock management is disabled');
        }

        if (count($warning) && version_compare(_PS_VERSION_, '1.6.1', '<')) {
            return $warning[0];
        }

        if (count($warning) && !$getAll) {
            return $warning[0];
        }

        return $warning;
    }

    public static function getTemplateBasePath($isoTemplate, $moduleName, $theme)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $basePathList = [
                _PS_ROOT_DIR_ . '/themes/' . $theme->getName() . '/',
                _PS_ROOT_DIR_ . '/themes/' . $theme->get('parent') . '/',
                _PS_ROOT_DIR_,
            ];
        } else {
            $basePathList = [
                _PS_ROOT_DIR_ . '/themes/' . $theme->name . '/',
                _PS_ROOT_DIR_,
            ];
        }

        if ($moduleName !== false) {
            $templateRelativePath = '/modules/' . $moduleName . '/mails/';
        } else {
            $templateRelativePath = '/mails/';
        }

        foreach ($basePathList as $base) {
            $templatePath = $base . $templateRelativePath;
            if (file_exists($templatePath . $isoTemplate . '.txt') || file_exists($templatePath . $isoTemplate . '.html')) {
                return $templatePath;
            }
        }
    }

    public function actionsAfterSubscribe()
    {
        // Override this function in your store, to execute some action afther a suscription
    }
}
