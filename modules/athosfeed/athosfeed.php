<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/autoload.php';

use AthosFeed\Configuration\ModuleConfiguration as AthosConfig;

class AthosFeed extends Module
{
    public function __construct()
    {
        $this->name = 'athosfeed';
        $this->tab = 'administration';
        $this->version = '0.2.0';
        $this->author = 'Sifatusafwa';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Athos catalog integration');
        $this->description = $this->l('Generates and securely serves an Athos product feed in isolated test mode.');
        $this->ps_versions_compliancy = array('min' => '1.7.6.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook(array('displayHeader', 'displayFooterProduct', 'displayShoppingCartFooter'))
            && AthosConfig::set(AthosConfig::MODE, 'disabled')
            && AthosConfig::set(AthosConfig::BATCH_SIZE, 100)
            && AthosConfig::set(AthosConfig::CURRENCY_ID, (int) Configuration::get('PS_CURRENCY_DEFAULT'))
            && AthosConfig::set(AthosConfig::COUNTRY_ID, (int) Configuration::get('PS_COUNTRY_DEFAULT'))
            && AthosConfig::set(AthosConfig::GROUP_ID, (int) Configuration::get('PS_UNIDENTIFIED_GROUP'))
            && AthosConfig::set(AthosConfig::INCLUDE_INACTIVE, 0)
            && AthosConfig::set(AthosConfig::INCLUDE_OUT_OF_STOCK, 1)
            && AthosConfig::set(AthosConfig::FRONTEND_ENABLED, 0)
            && AthosConfig::set(AthosConfig::ACCESS_TOKEN, AthosConfig::randomToken())
            && AthosConfig::set(AthosConfig::CRON_TOKEN, AthosConfig::randomToken());
    }

    public function uninstall()
    {
        AthosConfig::deleteAll();
        foreach ((array) glob(__DIR__ . '/var/*') as $path) {
            if (is_file($path) && basename($path) !== '.gitignore' && basename($path) !== '.htaccess') {
                @unlink($path);
            }
        }
        return parent::uninstall();
    }

    public function getContent()
    {
        $message = '';
        if (Tools::isSubmit('submitAthosFeed')) {
            $mode = Tools::getValue('ATHOS_MODE');
            if (!in_array($mode, array('disabled', 'test', 'production'), true)) {
                $message .= $this->displayError($this->l('Invalid mode.'));
            } else {
                $batch = max(1, min(1000, (int) Tools::getValue('ATHOS_BATCH_SIZE')));
                AthosConfig::set(AthosConfig::MODE, $mode);
                AthosConfig::set(AthosConfig::BATCH_SIZE, $batch);
                foreach (array(AthosConfig::CURRENCY_ID => 'ATHOS_CURRENCY_ID', AthosConfig::COUNTRY_ID => 'ATHOS_COUNTRY_ID', AthosConfig::GROUP_ID => 'ATHOS_GROUP_ID') as $key => $input) {
                    AthosConfig::set($key, (int) Tools::getValue($input));
                }
                foreach (array(AthosConfig::INCLUDE_INACTIVE, AthosConfig::INCLUDE_OUT_OF_STOCK, AthosConfig::FRONTEND_ENABLED, AthosConfig::SEARCH_ENABLED, AthosConfig::CATEGORY_ENABLED) as $key) {
                    AthosConfig::set($key, (int) (bool) Tools::getValue('ATHOS_' . $key));
                }
                foreach (array(AthosConfig::ALLOWED_IPS, AthosConfig::SNAP_SCRIPT_URL, AthosConfig::SNAP_PUBLIC_CONFIG, AthosConfig::ACCOUNT_ID, AthosConfig::INDEX_ID, AthosConfig::PRODUCT_ZONE, AthosConfig::CART_ZONE) as $key) {
                    AthosConfig::set($key, trim((string) Tools::getValue('ATHOS_' . $key)));
                }
                if (Tools::getValue('ATHOS_REGENERATE_ACCESS')) {
                    AthosConfig::set(AthosConfig::ACCESS_TOKEN, AthosConfig::randomToken());
                }
                if (Tools::getValue('ATHOS_REGENERATE_CRON')) {
                    AthosConfig::set(AthosConfig::CRON_TOKEN, AthosConfig::randomToken());
                }
                $message .= $this->displayConfirmation($this->l('Settings saved. Long exports must be run via CLI or protected cron.'));
            }
        }
        return $message . $this->renderConfiguration();
    }

    private function renderConfiguration()
    {
        $status = json_decode((string) AthosConfig::get(AthosConfig::STATUS, null, '{}'), true);
        $fields = array(
            array('type' => 'select', 'label' => $this->l('Mode'), 'name' => 'ATHOS_MODE', 'options' => array('query' => array(array('id'=>'disabled','name'=>'Disabled'),array('id'=>'test','name'=>'Test'),array('id'=>'production','name'=>'Production')), 'id'=>'id', 'name'=>'name')),
            array('type' => 'text', 'label' => $this->l('Batch size'), 'name' => 'ATHOS_BATCH_SIZE'),
            array('type' => 'text', 'label' => $this->l('Currency ID'), 'name' => 'ATHOS_CURRENCY_ID'),
            array('type' => 'text', 'label' => $this->l('Country ID'), 'name' => 'ATHOS_COUNTRY_ID'),
            array('type' => 'text', 'label' => $this->l('Customer group ID'), 'name' => 'ATHOS_GROUP_ID'),
            array('type' => 'switch', 'label' => $this->l('Include inactive'), 'name' => 'ATHOS_INCLUDE_INACTIVE', 'values' => $this->yesNo()),
            array('type' => 'switch', 'label' => $this->l('Include out of stock'), 'name' => 'ATHOS_INCLUDE_OUT_OF_STOCK', 'values' => $this->yesNo()),
            array('type' => 'text', 'label' => $this->l('Allowed IPs (comma separated)'), 'name' => 'ATHOS_ALLOWED_IPS'),
            array('type' => 'switch', 'label' => $this->l('Enable isolated frontend adapter'), 'name' => 'ATHOS_FRONTEND_ENABLED', 'values' => $this->yesNo()),
            array('type' => 'text', 'label' => $this->l('Snap SDK script URL'), 'name' => 'ATHOS_SNAP_SCRIPT_URL'),
            array('type' => 'textarea', 'label' => $this->l('Snap public JSON configuration'), 'name' => 'ATHOS_SNAP_PUBLIC_CONFIG'),
            array('type' => 'text', 'label' => $this->l('Athos account identifier (public)'), 'name' => 'ATHOS_ACCOUNT_ID'),
            array('type' => 'text', 'label' => $this->l('Athos index identifier (public)'), 'name' => 'ATHOS_INDEX_ID'),
            array('type' => 'switch', 'label' => $this->l('Search integration point'), 'name' => 'ATHOS_SEARCH_ENABLED', 'values' => $this->yesNo()),
            array('type' => 'switch', 'label' => $this->l('Category integration point'), 'name' => 'ATHOS_CATEGORY_ENABLED', 'values' => $this->yesNo()),
            array('type' => 'text', 'label' => $this->l('Product recommendation zone'), 'name' => 'ATHOS_PRODUCT_ZONE'),
            array('type' => 'text', 'label' => $this->l('Cart recommendation zone'), 'name' => 'ATHOS_CART_ZONE'),
            array('type' => 'switch', 'label' => $this->l('Regenerate feed access token'), 'name' => 'ATHOS_REGENERATE_ACCESS', 'values' => $this->yesNo()),
            array('type' => 'switch', 'label' => $this->l('Regenerate cron token'), 'name' => 'ATHOS_REGENERATE_CRON', 'values' => $this->yesNo()),
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitAthosFeed';
        $helper->fields_value = $this->formValues();
        $summary = '<div class="alert alert-info"><strong>' . $this->l('Feed endpoint:') . '</strong> '
            . Tools::safeOutput($this->context->link->getModuleLink($this->name, 'feed')) . '<br>'
            . $this->l('Credentials are masked. Copy tokens from the database/secret manager when configuring Athos; they are never rendered here.') . '<br>'
            . '<strong>' . $this->l('Last export:') . '</strong> ' . Tools::safeOutput($status ? json_encode($status) : $this->l('not available')) . '</div>';
        return $summary . $helper->generateForm(array(array('form' => array('legend'=>array('title'=>$this->l('Athos feed and test-mode frontend')), 'input'=>$fields, 'submit'=>array('title'=>$this->l('Save'))))));
    }

    private function formValues()
    {
        $values = array('ATHOS_MODE'=>AthosConfig::get(AthosConfig::MODE, null, 'disabled'), 'ATHOS_BATCH_SIZE'=>AthosConfig::get(AthosConfig::BATCH_SIZE, null, 100));
        foreach (array(AthosConfig::CURRENCY_ID, AthosConfig::COUNTRY_ID, AthosConfig::GROUP_ID, AthosConfig::INCLUDE_INACTIVE, AthosConfig::INCLUDE_OUT_OF_STOCK, AthosConfig::ALLOWED_IPS, AthosConfig::FRONTEND_ENABLED, AthosConfig::SNAP_SCRIPT_URL, AthosConfig::SNAP_PUBLIC_CONFIG, AthosConfig::ACCOUNT_ID, AthosConfig::INDEX_ID, AthosConfig::SEARCH_ENABLED, AthosConfig::CATEGORY_ENABLED, AthosConfig::PRODUCT_ZONE, AthosConfig::CART_ZONE) as $key) {
            $values['ATHOS_' . $key] = AthosConfig::get($key);
        }
        $values['ATHOS_REGENERATE_ACCESS'] = 0;
        $values['ATHOS_REGENERATE_CRON'] = 0;
        return $values;
    }

    private function yesNo()
    {
        return array(array('id'=>'yes','value'=>1,'label'=>$this->l('Yes')), array('id'=>'no','value'=>0,'label'=>$this->l('No')));
    }

    public function hookDisplayHeader()
    {
        if (!$this->frontendAllowed()) {
            return;
        }
        $url = (string) AthosConfig::get(AthosConfig::SNAP_SCRIPT_URL);
        if (filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'https://') === 0) {
            $this->context->controller->registerJavascript('module-athosfeed-snap', $url, array('server'=>'remote', 'position'=>'bottom', 'priority'=>200));
            $this->context->controller->registerJavascript('module-athosfeed-adapter', 'modules/' . $this->name . '/views/js/adapter.js', array('position'=>'bottom', 'priority'=>201));
            Media::addJsDef(array('athosFeedConfig' => json_decode((string) AthosConfig::get(AthosConfig::SNAP_PUBLIC_CONFIG, null, '{}'), true) ?: array()));
        }
    }

    public function hookDisplayFooterProduct()
    {
        return $this->renderZone((string) AthosConfig::get(AthosConfig::PRODUCT_ZONE), 'product');
    }

    public function hookDisplayShoppingCartFooter()
    {
        return $this->renderZone((string) AthosConfig::get(AthosConfig::CART_ZONE), 'cart');
    }

    private function renderZone($zoneId, $placement)
    {
        if (!$this->frontendAllowed() || $zoneId === '') {
            return '';
        }
        $this->context->smarty->assign(array('athos_zone_id'=>htmlspecialchars($zoneId, ENT_QUOTES, 'UTF-8'), 'athos_placement'=>$placement));
        return $this->display(__FILE__, 'views/templates/hook/zone.tpl');
    }

    private function frontendAllowed()
    {
        return AthosConfig::get(AthosConfig::MODE) === 'test' && (bool) AthosConfig::get(AthosConfig::FRONTEND_ENABLED);
    }
}
