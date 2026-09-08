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

    
}
