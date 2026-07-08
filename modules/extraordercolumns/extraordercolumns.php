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

use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\OrderGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;

class Extraordercolumns extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'extraordercolumns';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'sifatusafwa.com';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Extra order column');
        $this->description = $this->l('The module adds new columns to orderli sto page in administration');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '9.0');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->installTab() &&
            $this->registerHook('actionOrderGridDefinitionModifier') &&
            $this->registerHook('actionOrderGridQueryBuilderModifier') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('displayBackOfficeHeader');
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
        $tab->class_name = 'AdminExtraordercolumns';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Extra order columns';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminExtraordercolumns');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        $definition = $params['definition'];

        $column = new DataColumn('carrier_reference');
        $column->setName($this->l('Carrier'));
        $column->setOptions([
            'field' => 'carrier_name',
        ]);

        $definition
            ->getColumns()
            ->addAfter(
                'total_paid_tax_incl',
                $column
            )
        ;

        $column = new DataColumn('tracking_number');
        $column->setName($this->l('Tracking'));
        $column->setOptions([
            'field' => 'tracking_number',
            'clickable' => false,
        ]);

        $definition
            ->getColumns()
            ->addAfter(
                'osname',
                $column
            )
        ;

        $column = new DataColumn('email');
        $column->setName($this->l('Email'));
        $column->setOptions([
            'field' => 'email',
        ]);

        $definition
            ->getColumns()
            ->addAfter(
                'customer',
                $column
            )
        ;

        $definition->getFilters()->add(
            (new Filter('carrier_reference', TextType::class))
                ->setAssociatedColumn('carrier_reference')
                ->setTypeOptions([
                    'required' => false,
                    'translation_domain' => false,
                    'attr' => [
                        'placeholder' => 'Select a carrier',
                    ],
                ])
        );

        $definition->getFilters()->add(
            (new Filter('tracking_number', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'tracking #',
                    ],
                ])
                ->setAssociatedColumn('tracking_number')
        );

        $definition->getFilters()->add(
            (new Filter('email', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => '',
                    ],
                ])
                ->setAssociatedColumn('email')
        );

    }

    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        if (empty($params['search_query_builder']) || empty($params['search_criteria'])) {
            return;
        }

        $searchQueryBuilder = $params['search_query_builder'];

        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'o.`id_carrier`, car.`id_reference` AS `carrier_reference`, car.`name` AS `carrier_name`'
        );

        $searchQueryBuilder->leftJoin(
            'o',
            '`' . _DB_PREFIX_ . 'carrier`',
            'car',
            'car.`id_carrier` = o.`id_carrier`'
        );

        if ('carrier_reference' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('car.`name`', $searchCriteria->getOrderWay());
        }


        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('carrier_reference' === $filterName) {
                $searchQueryBuilder->andWhere('car.`name` LIKE :carrier_reference');
                $searchQueryBuilder->setParameter('carrier_reference', '%' . $filterValue . '%');
            }
        }

        $searchQueryBuilder->addSelect(
            'oc.`tracking_number`'
        );

        $searchQueryBuilder->leftJoin(
            'o',
            '`' . _DB_PREFIX_ . 'order_carrier`',
            'oc',
            'oc.`id_order` = o.`id_order`'
        );

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('tracking_number' === $filterName) {
                $searchQueryBuilder->andWhere("oc.`tracking_number` LIKE :tracking_number");
                $searchQueryBuilder->setParameter('tracking_number', '%' . $filterValue . '%');
            }
        }

        //
        $searchQueryBuilder->addSelect(
            'cu.`email`'
        );

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('email' === $filterName) {
                $searchQueryBuilder->andWhere("cu.`email` LIKE :email");
                $searchQueryBuilder->setParameter('email', '%' . $filterValue . '%');
            }
        }
    }


    public function getContent()
    {

        if ((Tools::isSubmit('submitExtraordercolumnsModule'))) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitExtraordercolumnsModule';
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

    /**
     * Create the structure of your form.
     */
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
                        'name' => 'EXTRAORDERCOLUMNS_LIVE_MODE',
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
                        'name' => 'EXTRAORDERCOLUMNS_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'EXTRAORDERCOLUMNS_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'EXTRAORDERCOLUMNS_LIVE_MODE' => Configuration::get('EXTRAORDERCOLUMNS_LIVE_MODE', true),
            'EXTRAORDERCOLUMNS_ACCOUNT_EMAIL' => Configuration::get('EXTRAORDERCOLUMNS_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'EXTRAORDERCOLUMNS_ACCOUNT_PASSWORD' => Configuration::get('EXTRAORDERCOLUMNS_ACCOUNT_PASSWORD', null),
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
        $this->context->controller->addJs($this->_path . 'views/js/extraordercolumns_back.js');
        $this->context->controller->addCss($this->_path . 'views/css/extraordercolumns_back.css');

        Media::addJsDef([
            'extraordercolumns_ajax_link' => $this->context->link->getAdminLink('AdminExtraordercolumns'),
        ]);
    }
}
