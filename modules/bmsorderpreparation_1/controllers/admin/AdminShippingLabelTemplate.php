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

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationMimeType.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/ShippingTemplateCodes.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationShippingTemplate.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationShippingTemplateType.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';

class AdminShippingLabelTemplateController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'bms_orderpreparation_shipping_template';
        $this->className = 'BmsOrderPreparationShippingTemplate';
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $id_lang = $this->context->language->id;
        $arrCarriers = Carrier::getCarriers($id_lang, false, false, false, null, Carrier::ALL_CARRIERS);

        $this->_select = "(SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
            FROM " . _DB_PREFIX_ . "carrier c
            WHERE FIND_IN_SET(id_carrier, a.shipping_methods)
        ) as shipping_methods_name";

        $this->fields_list = array(
            'id_bms_orderpreparation_shipping_template' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'align' => 'center'
            ),
            'shipping_methods_name' => array(
                'title' => $this->l('Associated shipping methods'),
                'align' => 'center',
                'search' => false,
                'orderby' => false
            )
        );
        $this->bulk_actions = array();
    }

    public function initContent()
    {
        parent::initContent();
        $this->setBmsMedia();
    }

    public function renderList()
    {
        return parent::renderList();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        BMSOrderPreparation::setPageHeaderToolbar($this);

        $this->context->smarty->assign('help_link', null);
    }

    private function getTags()
    {
        $orderId = Db::getInstance()->getValue('SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders` order by id_order desc');
        $order = new Order($orderId);

        $products = $order->getProducts();
        $order_details = array_shift($products);

        $customer = $order->getCustomer();
        $customer = ShippingTemplatesCodes::cleanArrayFromJsonObject($customer);

        $invoice_address = new Address((int) $order->id_address_invoice);
        $delivery_address = new Address((int) $order->id_address_delivery);

        $id_order_carrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . pSQL($order->id));
        $order_carrier = new OrderCarrier($id_order_carrier);

        $tags = array(
            'order' => ShippingTemplatesCodes::hydrateOrder($order),
            'order_details' => $order_details,
            'order_carrier' => ShippingTemplatesCodes::hydrateOrderCarrier($order_carrier),
            'customer' => $customer,
            'invoice_address' => ShippingTemplatesCodes::hydrateAddress($invoice_address),
            'delivery_address' => ShippingTemplatesCodes::hydrateAddress($delivery_address)
        );

        return $tags;
    }

    public function setBmsMedia()
    {
        $this->addJquery();
        $this->addJqueryUi('ui.accordion');
    }

    private function clearTags($tags)
    {
        unset($tags['order']->secure_key);
        unset($tags['customer']->secure_key);
        unset($tags['customer']->passwd);
        unset($tags['customer']->last_passwd_gen);
        unset($tags['customer']->reset_password_token);
        unset($tags['customer']->reset_password_validity);

        return $tags;
    }

    public function renderView()
    {
        $tags = $this->getTags();
        $tags = $this->clearTags($tags);

        $tpl = $this->createTemplate('tags.tpl');
        $tpl->assign('tags', $tags);
        return $tpl->fetch();
    }

    public function renderForm()
    {
        $this->initToolbar();
        $obj = $this->loadObject(true);

        if (! ($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_value = array();
        $this->fields_form = array(
            'tabs' => array(
                'general' => $this->l('General'),
                'export' => $this->l('Export'),
                'import' => $this->l('Import')
            ),
            'input' => array(),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $this->setGeneralTab($obj);
        if (($obj->id) && ($this->typeSupportsConfiguration($obj->id_template_type))) {
            $this->setExportTab($obj);
            $this->setImportTab($obj);
        }

        $this->fields_value['shipping_methods[]'] = explode(',', $obj->shipping_methods);
        return parent::renderForm() . $this->renderView();
    }

    protected function setExportTab($obj)
    {
        $arrMimeType = BmsOrderPreparationMimeType::getMimeTypes();
        array_push($this->fields_form['input'], array(
            'type' => 'text',
            'label' => $this->l('File name'),
            'name' => 'export_file_name',
            'tab' => 'export'
        ), array(
            'type' => 'select',
            'label' => $this->l('Mime type'),
            'name' => 'id_export_mime_type',
            'options' => array(
                'query' => $arrMimeType,
                'id' => 'id_mime_type',
                'name' => 'name'
            ),
            'tab' => 'export'
        ), array(
            'type' => 'textarea',
            'label' => $this->l('File header line'),
            'name' => 'export_file_header',
            'tab' => 'export'
        ), array(
            'type' => 'textarea',
            'label' => $this->l('Header line for orders'),
            'name' => 'export_file_order_header',
            'tab' => 'export'
        ), array(
            'type' => 'textarea',
            'label' => $this->l('Line for each products in orders'),
            'name' => 'export_file_order_products',
            'tab' => 'export'
        ), array(
            'type' => 'textarea',
            'label' => $this->l('Footer line for orders'),
            'name' => 'export_file_order_footer',
            'tab' => 'export'
        ), array(
            'type' => 'textarea',
            'label' => $this->l('File footer line'),
            'name' => 'export_file_footer_line',
            'tab' => 'export'
        ));
    }

    protected function setImportTab($obj)
    {
        $id_lang = $this->context->language->id;
        $arrayFileSeparator = array(
            array(
                "id_file_separator" => ',',
                "name" => "Coma"
            ),
            array(
                "id_file_separator" => ';',
                "name" => "Semicolon"
            )
        )
        ;
        array_push($this->fields_form['input'], array(
            'type' => 'switch',
            'label' => $this->l('Skip first line'),
            'name' => 'import_file_skip_first_line',
            'is_bool' => true,
            'tab' => 'import',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            )
        ), array(
            'type' => 'select',
            'label' => $this->l('Field separator'),
            'name' => 'import_file_separator',
            'options' => array(
                'query' => $arrayFileSeparator,
                'id' => 'id_file_separator',
                'name' => 'name'
            ),
            'tab' => 'import'
        ), array(
            'type' => 'text',
            'label' => $this->l('Shipment reference column index (from 1)'),
            'name' => 'import_file_shipment_reference_index',
            'tab' => 'import'
        ), array(
            'type' => 'text',
            'label' => $this->l('Tracking number column index (from 1)'),
            'name' => 'import_file_tracking_index',
            'tab' => 'import'
        ));
    }

    protected function setGeneralTab($obj)
    {
        $id_lang = $this->context->language->id;
        $arrTemplateType = BmsOrderPreparationShippingTemplateType::getTemplateTypes();
        $arrCarriers = Carrier::getCarriers($id_lang, false, false, false, null, Carrier::ALL_CARRIERS);
        array_push($this->fields_form['input'], array(
            'type' => 'text',
            'label' => $this->l('Name'),
            'name' => 'name',
            'required' => true,
            'tab' => 'general'
        ), array(
            'type' => 'switch',
            'label' => $this->l('Status'),
            'name' => 'active',
            'is_bool' => true,
            'tab' => 'general',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            )
        ), array(
            'type' => 'select',
            'label' => $this->l('Type'),
            'name' => 'id_template_type',
            'options' => array(
                'query' => $arrTemplateType,
                'id' => 'id_template_type',
                'name' => 'name'
            ),
            'tab' => 'general'
        ), array(
            'type' => 'select',
            'multiple' => 'true',
            'label' => $this->l('Associated shipping methods'),
            'name' => 'shipping_methods[]',
            'options' => array(
                'query' => $arrCarriers,
                'id' => 'id_carrier',
                'name' => 'name'
            ),
            'tab' => 'general'
        ));
    }

    public function processSave()
    {
        if (is_array(Tools::getValue("shipping_methods"))) {
            $_POST["shipping_methods"] = implode(Tools::getValue("shipping_methods"), ',');
        } else {
            $_POST["shipping_methods"] = "";
        }
        parent::processSave();
    }

    protected function typeSupportsConfiguration($type)
    {
        foreach(BmsOrderPreparationShippingTemplateType::getTemplateTypes() as $item)
        {
            if ($item['id_template_type'] == $type)
                return $item['configurable'];
        }
    }

}
