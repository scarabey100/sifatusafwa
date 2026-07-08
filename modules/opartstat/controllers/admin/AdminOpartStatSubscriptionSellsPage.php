<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../classes/opartStatTools.php');

//psaccount
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class AdminOpartStatSubscriptionSellsPageController extends ModuleAdminController
{

    public $name = 'opartstat';

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->name = 'opartstat';
        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $v = $this->module->version;
        $viewUrl = $this->context->shop->getBaseURL(true) . 'modules/' . $this->module->name . '/views/';
        
        $this->addCSS($viewUrl.'css/admin.css?v='.$v);
        $this->addCSS($viewUrl.'css/settings.css?v='.$v);
    
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
          $this->addCSS($viewUrl.'css/16.css?v='.$v);
        }
    }

    public function InitContent()
    {
        $subscriptionSettingsLink = $this->context->link->getAdminLink('AdminOpartStatSettingsSubscription', true);

        $shopHasActiveSubscription = opartStatTools::shopHasActiveSubscription();
        if($shopHasActiveSubscription)
            Tools::redirectAdmin($subscriptionSettingsLink);

        parent::initContent();
        $adminLinksArray = OpartStatTools::getAdminMenuLinks('subscription');
        $this->context->smarty->assign($adminLinksArray);

        $this->context->smarty->assign(array(
            'subscriptionSettingsLink' => $subscriptionSettingsLink,
            'sellsPageHtml' => $this->getSellsPageHtml()
        ));

        $output =  $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/settings/partial/header.tpl'
        );

        /* if (count($this->postError) > 0) {
            foreach ($this->postError as $err) {
                $output .= $this->module->displayError($err);
            }
        } elseif (count($this->postConf) > 0) {
            foreach ($this->postConf as $conf) {
                $output .= $this->module->displayConfirmation($conf);
            }
        } */


        $output .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/settings/subscriptionSellsPage.tpl'
        );

        $this->context->smarty->assign(array(
            'content' => $this->content . $output,
        ));

        return;
    }    

    public function getSellsPageHtml() {
        $langIsoCode = $this->context->language->iso_code;
        $page = "controllers/front/getSellsPage.php?isoLang=".$langIsoCode;
        $ch = curl_init();

        $url = OpartStatTools::getSaasDomain().$page;       
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $html = curl_exec($ch);

        return $html;
    }
}
