<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Preloadedproductfeatures extends Module
{
    public function __construct()
    {
        $this->name = 'preloadedproductfeatures';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'sifatusafwa.com';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Pre- loaded product features');
        $this->description = $this->l('Pre- loaded features for new product');

        $this->ps_versions_compliancy = array('min' => '8.2', 'max' => '9.0');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        return false;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJs($this->_path . 'views/js/preloadedproductfeatures_back.js');
        $this->context->controller->addCss($this->_path . 'views/css/preloadedproductfeatures_back.css');

        Media::addJsDef([
            'preloadedproductfeaturesIdProduct' => Tools::getValue('id_product'),
        ]);
    }
}
