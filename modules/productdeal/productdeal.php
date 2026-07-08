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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Productdeal extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'productdeal';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'sifatusafwa.com';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product deal');
        $this->description = $this->l('Creates a copy of the book with custom values');

        $this->ps_versions_compliancy = array('min' => '8.2', 'max' => '9.0');
    }

    public function install()
    {
        return parent::install() &&
            $this->installTab() &&
            $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab();
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = 'AdminProductdeal';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Productdeal';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminProductdeal');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    public function getContent()
    {
        return false;
        if ((Tools::isSubmit('submitProductdealModule'))) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductdealModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'PRODUCTDEAL_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'PRODUCTDEAL_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'PRODUCTDEAL_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'PRODUCTDEAL_LIVE_MODE' => Configuration::get('PRODUCTDEAL_LIVE_MODE', true),
            'PRODUCTDEAL_ACCOUNT_EMAIL' => Configuration::get('PRODUCTDEAL_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'PRODUCTDEAL_ACCOUNT_PASSWORD' => Configuration::get('PRODUCTDEAL_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJs($this->_path . 'views/js/productdeal_back.js');
        $this->context->controller->addCss($this->_path . 'views/css/productdeal_back.css');

        $langIds = [];

        foreach (Language::getLanguages(false) as  $lang) {
            $langIds[] = $lang['id_lang'];
        }

        Media::addJsDef([
            'productdeal_ajax_link' => $this->context->link->getAdminLink('AdminProductdeal'),
            'langIds' => $langIds,
        ]);
    }
}
