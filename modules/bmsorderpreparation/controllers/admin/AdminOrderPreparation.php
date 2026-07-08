<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/vendors/pi_barcode.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/ProductHelper.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/pdf/PDF.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/pdf/HTMLTemplateOrderPickList.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/pdf/HTMLTemplateGlobalPickList.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/pdf/HTMLTemplateInvoice.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationInProgress.php';
class AdminOrderPreparationController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->default_form_language = $this->context->language->id;
    }

    public function initContent()
    {
        parent::initContent();
        $this->setBmsMedia();
    }

    public function renderList()
    {
        return $this->renderView();
    }

    public function renderView()
    {
        $actionTab = BMSOrderPreparation::getActionsTabTpl();
        $tpl = $this->createTemplate('home.tpl');

        if (Tools::getValue('no_order')) {
            Media::addJsDef(array(
                'no_order' => true
            ));
        }

        return $actionTab->fetch() . $tpl->fetch();
    }

    public function setBmsMedia()
    {
        $this->addJquery();
        $this->addJqueryUi('ui.tabs');

        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/iframe.js');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/tabs.js');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/home.js');
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        BMSOrderPreparation::setPageHeaderToolbar($this);

        $this->context->smarty->assign('help_link', null);
    }

    public function processFlush()
    {
        $orders = BmsOrderPreparationInProgress::getOrdersList();
        if (count($orders)) {
            if (Configuration::get('BMS_OP_WORKFLOW') == 'bulk') {
                foreach ($orders as $row) {
                    $id_order = (int) $row['id_order'];
                    $order = new Order($id_order);
                    $state = $order->current_state;
                    $order_state = new OrderState((int) $state);

                    $history = BmsOrderPreparationInProgress::getLastHistory($id_order);
                    $carrier = new Carrier($order->id_carrier, $order->id_lang);

                    $templateVars = array();
                    if ($order_state->id == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                        $templateVars = array(
                            '{followup}' => str_replace('@', $order->shipping_number, $carrier->url)
                        );
                    }

                    if (! $history->sendEmail($order, $templateVars)) {
                        $this->errors[] = $this->l('An error occurred while sending the e-mail to the customer. Order ') . $id_order;
                    }
                    if ($state == Configuration::get('BMS_OP_STATUT_FOR_SHIPPED')) {
                        $restore = false;
                    } else {
                        if (Configuration::get('BMS_OP_RESTORE_PREVIOUS_STATUS'))
                            $restore = true;
                        else
                            $restore = false;
                    }

                    BmsOrderPreparationInProgress::remove($id_order, $restore);
                }
            } else {
                foreach ($orders as $data) {
                    $id_order = (int) $data['id_order'];
                    $order = new Order($id_order);
                    $state = $order->current_state;
                    if ($state == Configuration::get('BMS_OP_STATUT_FOR_SHIPPED')) {
                        $restore = false;
                    } else {
                        if (Configuration::get('BMS_OP_RESTORE_PREVIOUS_STATUS'))
                            $restore = true;
                        else
                            $restore = false;
                    }

                    BmsOrderPreparationInProgress::remove($id_order, $restore);
                }
            }
            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
        } else {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPreparation', true) . '&no_order=1');
        }
    }

    public function processPrint()
    {

        $barCodePath = _PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/barcode/';
        $id_order = Tools::getValue('id_order', null);

        $order = array();
        $orders = array();
        $templates = array();
        $document = Tools::getValue("document", null);

        if (! empty($document)) {
            switch ($document) {
                case BMSPDF::ORDER_PICK_LIST:
                    $templates[] = BMSPDF::ORDER_PICK_LIST;
                    break;
                case PDF::TEMPLATE_DELIVERY_SLIP:
                    $templates[] = PDF::TEMPLATE_DELIVERY_SLIP;
                    break;
                case PDF::TEMPLATE_INVOICE:
                    $templates[] = PDF::TEMPLATE_INVOICE;
                    break;
            }
        } else {
            if (Configuration::get("BMS_OP_GLOBAL_PICK")) {
                $templates[] = BMSPDF::GLOBAL_PICK_LIST;
            }
            if (Configuration::get("BMS_OP_ORDER_PICK")) {
                $templates[] = BMSPDF::ORDER_PICK_LIST;
            }
            if (Configuration::get("BMS_OP_ORDER_PACKING")) {
                $templates[] = PDF::TEMPLATE_DELIVERY_SLIP;
            }
            if (Configuration::get("BMS_OP_ORDER_INVOICE")) {
                $templates[] = PDF::TEMPLATE_INVOICE;
            }
        }

        if ($id_order) {
            $orders[] = new Order($id_order);
        } else {
            $results = BmsOrderPreparationInProgress::getOrdersInProgressLite();

            foreach ($results as $id) {
                $order = new Order($id['id_order']);
                $orders[] = $order;
            }
        }

        if (count($orders)) {
            if (Configuration::get('BMS_OP_WORKFLOW') == 'bulk') {
                foreach ($orders as $order) {
                    $statut = Configuration::get("BMS_OP_STATUT_FOR_SHIPPED");
                    if (! empty($statut)) {
                        //we dont use function setCurrentState to prevent customer notification
                        if ($order->current_state != $statut)
                        {
                            $order->current_state = $statut;

                            $order->save();

                            $history = new OrderHistory();
                            $history->id_order = (int) $order->id;
                            $history->changeIdOrderState($statut, (int) $order->id);
                            $history->save();
                        }
                    }
                }
            }

            $pdf = new BMSPDF($orders, $templates, 'picking.pdf');

            $pdf->render();
            array_map("unlink", glob($barCodePath . '*.jpg'));
            array_map("unlink", glob(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/Invoice/barcode/*.jpg'));
            die();
        } else {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPreparation', true) . '&no_order=1');
        }
    }
}
