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

    
}
