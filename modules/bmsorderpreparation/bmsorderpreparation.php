<?php
/**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright 2007-2022 Boostmyshop
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (! defined('_PS_VERSION_')) {
    exit();
}

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';

class BMSOrderPreparation extends Module
{

    public function __construct()
    {
        $this->name = 'bmsorderpreparation';
        $this->tab = 'administration';
        $this->version = '1.0.19';
        $this->author = 'BoostMyShop';
        $this->need_instance = 0;
        $this->module_key = '56a42e4b8da4e38a4b51f121c1de925e';
        $this->ps_versions_compliancy = array(
            'min' => '1.6.1.1',
            'max' => _PS_VERSION_
        );
        $this->displayName = $this->l('Order Preparation');
        $this->description = $this->l('Provides advanced features to pick, pack and ship orders');
        $this->bootstrap = true;

        parent::__construct();
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->fields = array(
            'BMS_OP_WORKFLOW',
            'BMS_OP_USER_MODE',
            'BMS_OP_RESTORE_PREVIOUS_STATUS',
            'BMS_OP_STATUT_IN_PROGRESS',
            'BMS_OP_STATUT_FOR_PACKED',
            'BMS_OP_STATUT_FOR_SHIPPED',
            'BMS_OP_STATUT_READY_TO_SHIP',
            'BMS_OP_STATUT_BACKORDERS',
            'BMS_OP_STATUT_PENDING',
            'BMS_OP_STATUT_HOLDED',
            'BMS_OP_GLOBAL_PICK',
            'BMS_OP_ORDER_PICK',
            'BMS_OP_ORDER_PACKING',
            'BMS_OP_ORDER_INVOICE'
        );
        $this->multipleFields = array(
            'BMS_OP_STATUT_READY_TO_SHIP',
            'BMS_OP_STATUT_BACKORDERS',
            'BMS_OP_STATUT_PENDING',
            'BMS_OP_STATUT_HOLDED'
        );
    }


    protected function initDefaultConfigValues()
    {
        Configuration::updateValue('BMS_OP_STATUT_FOR_SHIPPED', 4);
        Configuration::updateValue('BMS_OP_STATUT_READY_TO_SHIP', '2,11');
        Configuration::updateValue('BMS_OP_STATUT_BACKORDERS', 9);
        Configuration::updateValue('BMS_OP_STATUT_PENDING', '10,13,1,8,12');

        Configuration::updateValue('BMS_OP_USER_MODE', 1);
        Configuration::updateValue('BMS_OP_GLOBAL_PICK', 1);
        Configuration::updateValue('BMS_OP_ORDER_PICK', 1);
        Configuration::updateValue('BMS_OP_ORDER_PACKING', 1);
        Configuration::updateValue('BMS_OP_ORDER_INVOICE', 1);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        if (! parent::install()) {
            return false;
        }
        if (!$this->registerHook('displayBackOfficeHeader'))
            return false;

        $this->installOrderStates();
        $this->initDefaultConfigValues();
        $this->installMenu();
        $this->installSubMenu();

        return true;
    }

    public function installOrderStates()
    {
        if (! Configuration::get('BMS_OP_STATUT_IN_PROGRESS')) {
            $in_progress = new OrderState();
            $in_progress->name = array();
            foreach (Language::getIDs(false) as $id_lang) {
                $in_progress->name[$id_lang] = "Preparation in progress";
            }
            $in_progress->color = "#FF8C00";
            $in_progress->invoice = true;
            $in_progress->logable = true;
            $in_progress->delivery = false;
            $in_progress->shipped = false;
            $in_progress->hidden = false;
            $in_progress->pdf_invoice = true;
            $in_progress->pdf_delivery = false;
            $in_progress->send_email = false;
            $in_progress->save();
            Configuration::updateValue('BMS_OP_STATUT_IN_PROGRESS', $in_progress->id);
        }

        if (! Configuration::get('BMS_OP_STATUT_FOR_PACKED')) {
            $packed = new OrderState();
            $packed->name = array();
            foreach (Language::getIDs(false) as $id_lang) {
                $packed->name[$id_lang] = "Packed";
            }
            $packed->color = "#4169E1";
            $packed->invoice = true;
            $packed->delivery = true;
            $packed->shipped = false;
            $packed->hidden = false;
            $packed->logable = true;
            $packed->pdf_invoice = true;
            $packed->pdf_delivery = true;
            $packed->send_email = false;
            $packed->save();
            Configuration::updateValue('BMS_OP_STATUT_FOR_PACKED', $packed->id);
        }
    } 
    public function installMenu()
    {
        $tab = new Tab();

        foreach (Language::getLanguages(true) as $lang) {
            switch ($lang['iso_code']) {
                case 'fr':
                    $tab->name[$lang['id_lang']] = 'Préparation de commande';
                    break;
                default:
                    $tab->name[$lang['id_lang']] = $this->l('Order preparation');
                    break;
            }
        }

        $tab->class_name = 'AdminOrderPreparationHome';
        $tab->id_parent = Tab::getIdFromClassName('SELL');
        $tab->module = $this->name;
        $tab->add();
    }

    /**
     * Installe le sous-menu
     */
    public function installSubMenu()
    {
        $arraySubMenu = array(

            array(
                'name' => $this->l('Order preparation'),
                'name_fr' => $this->l('Préparation de commande'),
                'class_name' => 'AdminOrderPreparation'
            ),
            array(
                'name' => $this->l('Shipping label template'),
                'class_name' => 'AdminShippingLabelTemplate'
            ),
            array(
                'name' => $this->l('List of orders'),
                'class_name' => 'AdminOrderTab'
            ),
            array(

                'name' => $this->l('Order Packing'),
                'class_name' => 'AdminOrderPacking'
            ),
            array(
                'name' => $this->l('Order Packing Tab'),
                'class_name' => 'AdminOrderPackingTab'
            ),
            array(
                'name' => $this->l('Shipping'),
                'class_name' => 'AdminOrderPreparationShipping'
            ),
            array(
                'name' => $this->l('Order Packing Confirm Tab'),
                'class_name' => 'AdminOrderPackingConfirmTab'
            ),
            array(
                'name' => $this->l('Bin locations management'),
                'class_name' => 'AdminBinLocation'
            )

        );

        $idTabParent = Tab::getIdFromClassName('BMSOrderPreparation');

        foreach ($arraySubMenu as $subMenu) {
            $tab = new Tab();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = (isset($subMenu['name_' . $lang['iso_code']]) ? $subMenu['name_' . $lang['iso_code']] : $subMenu['name']);
            }
            $tab->class_name = $subMenu['class_name'];
            $tab->id_parent = (int) empty($subMenu['id_parent']) ? $idTabParent : $subMenu['id_parent'];
            $tab->module = $this->name;
            $tab->active = (isset($subMenu['active']) ? $subMenu['active'] : 0);
            $tab->add();
        }
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        $keys = $this->fields;
        foreach ($keys as $field) {
            // on ne supprime pas les statuts in progress et packed
            if ($field != "BMS_OP_STATUT_IN_PROGRESS" && $field != "BMS_OP_STATUT_FOR_PACKED") {
                Configuration::deleteByName($field);
            }
        }

        if (!$this->uninstallTabs()) {
            return false;
        } 
        return  parent::uninstall();
    } 

    protected function uninstallTabs()
    {
        foreach (Tab::getCollectionFromModule($this->name) as $tab) {
            $tab->delete();
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output = $this->save();
        }

        $this->context->controller->addCSS($this->getPathUri() . 'views/css/form.css');
        return $output . $this->displayForm();
    }

    protected function save()
    {
        $keys = $this->fields;
        foreach ($keys as $field) {
            $value = Tools::getValue($field);
            Configuration::updateValue($field, (is_array($value) ? implode(',', $value) : $value));
        }
        return $this->displayConfirmation($this->l('Settings saved'));
    }

    protected function displayForm()
    {
        $fields_form = $this->getForm();
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        $keys = $this->fields;
        $fields_value = array();
        foreach ($keys as $field) {
            $value = Configuration::get($field);
            $fields_value[$field . (in_array($field, $this->multipleFields) ? '[]' : '')] = (strpos($value, ',') !== false ? explode(',', $value) : $value);
        }

        $helper->fields_value = $fields_value;

        return $helper->generateForm($fields_form);
    }

    protected function getForm()
    {
        $fields_form = array();
        $arr_method = array(
            array(
                'id_option' => 'order',
                'name' => $this->l('Order by order')
            ),
            array(
                'id_option' => 'bulk',
                'name' => $this->l('Bulk')
            )
        );

        $fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('General')
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Preparation workflow'),
                    'name' => 'BMS_OP_WORKFLOW',
                    'options' => array(
                        'query' => $arr_method,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                )/*,
                array(
                    'type' => 'switch',
                    'required' => true,
                    'label' => $this->l('Single user mode'),
                    'name' => 'BMS_OP_USER_MODE',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->l('No')
                        )
                    )
                ),*/
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );
        $orderStates = $this->getOrderStates();
        $orderStatesNoDelivery = $this->getOrderStates(false, true);
        $orderStatesDelivery = $this->getOrderStates(true);

        $fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Order statuses')
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Status for in progress orders'),
                    'name' => 'BMS_OP_STATUT_IN_PROGRESS',
                    'options' => array(
                        'query' => $this->addEmptyFirstValueToCombo($orderStates),
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Status for packed order'),
                    'name' => 'BMS_OP_STATUT_FOR_PACKED',
                    'options' => array(
                        'query' => $this->addEmptyFirstValueToCombo($orderStates),
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Restore previous status'),
                    'desc' => $this->l('Once "Flush" action is executed, all orders not being "shipped" will have their status set back to original one (the one they had before being added to the "In Progress" tab).') . '<br/>' .
                              $this->l('Note : "Shipped" orders are the ones having status defined in next option "Status for shipped orders".'),
                    'name' => 'BMS_OP_RESTORE_PREVIOUS_STATUS',
                    'options' => array(
                        'query' => $this->getYesNo(),
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Status for shipped orders'),
                    'name' => 'BMS_OP_STATUT_FOR_SHIPPED',
                    'options' => array(
                        'query' => $this->addEmptyFirstValueToCombo($orderStatesDelivery),
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'multiple' => 'true',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Ready to ship'),
                    'name' => 'BMS_OP_STATUT_READY_TO_SHIP',
                    'options' => array(
                        'query' => $orderStatesNoDelivery,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Only order statuses with option "Set the order as shipped" defined to "No" will be selectable there.')
                ),
                array(
                    'type' => 'select',
                    'multiple' => 'true',
                    'class' => 'bigSelect',
                    'required' => true,
                    'label' => $this->l('Backorders'),
                    'name' => 'BMS_OP_STATUT_BACKORDERS',
                    'options' => array(
                        'query' => $orderStatesNoDelivery,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Only order statuses with option "Set the order as shipped" defined to "No" will be selectable there.')
                ),
                array(
                    'type' => 'select',
                    'multiple' => 'true',
                    'class' => 'bigSelect',
                    'col' => 6,
                    'required' => true,
                    'label' => $this->l('Pending payment'),
                    'name' => 'BMS_OP_STATUT_PENDING',
                    'options' => array(
                        'query' => $orderStatesNoDelivery,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Only order statuses with option "Set the order as shipped" defined to "No" will be selectable there.')
                ),
                array(
                    'type' => 'select',
                    'class' => 'bigSelect',
                    'multiple' => 'true',
                    'required' => true,
                    'label' => $this->l('Holded '),
                    'name' => 'BMS_OP_STATUT_HOLDED',
                    'options' => array(
                        'query' => $orderStatesNoDelivery,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Only order statuses with option "Set the order as shipped" defined to "No" will be selectable there.')
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Download documents')
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'required' => true,
                    'label' => $this->l('Attach global pick list'),
                    'name' => 'BMS_OP_GLOBAL_PICK',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->l('No')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'required' => true,
                    'label' => $this->l('Attach order pick list'),
                    'name' => 'BMS_OP_ORDER_PICK',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->l('No')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'required' => true,
                    'label' => $this->l('Attach order packing slip'),
                    'name' => 'BMS_OP_ORDER_PACKING',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->l('No')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'required' => true,
                    'label' => $this->l('Attach order invoice'),
                    'name' => 'BMS_OP_ORDER_INVOICE',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->l('No')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );
        return $fields_form;
    }

    protected function getOrderStates($justDelivery = false, $justNoDelivery = false)
    {
        $arrOrderStates = OrderState::getOrderStates($this->context->language->id);
        $arr = null;

        foreach ($arrOrderStates as $state) {
            if ($state['name'] == "Refunded" || $state['name'] == "Canceled")
                continue;

            if ($justDelivery && $state['shipped'] == 0) {
                continue;
            }
            if ($justNoDelivery && $state['shipped'] == 1) {
                continue;
            }
            $arr[] = array(
                'id_option' => $state['id_order_state'],
                'name' => $state['name']
            );
        }

        return $arr;
    }

    protected function addEmptyFirstValueToCombo($arr)
    {
        array_unshift($arr, array(
            'id_option' => 0,
            'name' => $this->l('Empty')
        ));
        return $arr;
    }

    protected function getYesNo()
    {
        $arr = array();
        $arr[] = array('id_option' => 1, 'name' => $this->l('Yes'));
        $arr[] = array('id_option' => 0, 'name' => $this->l('No'));
        return $arr;
    }

    public static function getActionsTabTpl($key_actif = 'order_selection')
    {
        $links = array();
        $module = new BMSOrderPreparation();
        $links['order_selection'] = array(
            'href' => Context::getContext()->link->getAdminLink('AdminOrderPreparation', true),
            'desc' => $module->l('Order selection'),
            'icon' => 'process-icon-new'
        );

        if (Configuration::get('BMS_OP_WORKFLOW') == 'bulk') {
            $dl_label = $module->l('Ship orders and download documents');
        } else {
            $dl_label = $module->l('Download documents');
        }

        $links['download_documents'] = array(
            'href' => CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderPreparation', array('action' => 'print' ),Context::getContext()),
            'desc' => $dl_label,
            'icon' => 'process-icon-print icon-print'
        );

        if (Configuration::get('BMS_OP_WORKFLOW') == 'bulk') {
            $links['shipping'] = array(
                'href' => Context::getContext()->link->getAdminLink('AdminOrderPreparationShipping'),
                'desc' => $module->l('Shipping'),
                'icon' => 'process-icon-new'
            );
            $flush_label = $module->l('Notify customer and flush in progress orders');
        } else {
            $links['packing'] = array(
                'href' => Context::getContext()->link->getAdminLink('AdminOrderPacking', true, array(), array(
                    'action' => 'print'
                )),
                'desc' => $module->l('Packing'),
                'icon' => 'process-icon-new'
            );
            $flush_label = $module->l('Flush shipped orders');
        }

        $links['flush'] = array(
            'href' => CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderPreparation', array('action' => 'flush'), Context::getContext()),
            'desc' => $flush_label,
            'icon' => 'process-icon-trash icon-trash'
        );

        $actionTab = Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin//helpers/actionTab.tpl', Context::getContext()->smarty);
        $actionTab->assign('links', $links);
        $actionTab->assign('key_actif', $key_actif);

        return $actionTab;
    }

    public static function setPageHeaderToolbar($controller)
    {
        $module = new BMSOrderPreparation();

        $controller->page_header_toolbar_btn['binLocations'] = array(
            'href' => Context::getContext()->link->getAdminLink('AdminBinLocation', true),
            'desc' => $module->l('Bin locations'),
            'icon' => 'process-icon-edit'
        );

        $controller->page_header_toolbar_btn['shippingTpl'] = array(
            'href' => Context::getContext()->link->getAdminLink('AdminShippingLabelTemplate', true),
            'desc' => $module->l('Shipping templates'),
            'icon' => 'process-icon-envelope'
        );

        $controller->page_header_toolbar_btn['setting'] = array(
            'href' => Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=Administration&configure=bmsorderpreparation',
            'desc' => $module->l('Settings'),
            'icon' => 'process-icon-configure'
        );


    }

    public function hookDisplayBackOfficeHeader() {
        $this->context->controller->addCss($this->_path.'views/css/tab.css');
    }

}
