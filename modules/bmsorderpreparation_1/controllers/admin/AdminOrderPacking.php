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

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationInProgress.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';


class AdminOrderPackingController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        // $this->table = '';
        // $this->className = '';
        // $this->identifier = '';
        ;

        parent::__construct();

        // $this->override_folder = 'home/';
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
        $ordersInprogress = BmsOrderPreparationInProgress::getOrdersList();

        if (!is_array($ordersInprogress) || !count($ordersInprogress)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPreparation', true) . '&no_order=1');
        }

        $this->context->smarty->assign('ordersInprogress', $ordersInprogress);
        $actionTab = BMSOrderPreparation::getActionsTabTpl('packing');

        $tpl = $this->createTemplate('home.tpl');
        return $actionTab->fetch() . $tpl->fetch();
    }

    public function ajaxPreProcess()
    {
        $result = false;

        if (Tools::getValue('action') == 'selectPanel') {
            $id_order = (int) Tools::getValue('id_order', null);

            if (! empty($id_order)) {
                $order = new Order($id_order);
                if (! empty($order->current_state)) {
                    $statutForPacked = Configuration::get("BMS_OP_STATUT_FOR_PACKED");
                    $statutForShipped = Configuration::get("BMS_OP_STATUT_FOR_SHIPPED");

                    if ($statutForPacked == $order->current_state || $statutForShipped == $order->current_state) {
                        $url = 'AdminOrderPackingConfirmTab';
                    } else {
                        $url = 'AdminOrderPackingTab';
                    }

                    if ($url) {
                        $result = true;
                    }
                } else {
                    die(json_encode(array(
                        'result' => false
                    )));
                }
                die(json_encode(array(
                    'result' => $result,
                    'url' => CompatibilityOrderPreparation::getPrestashopUrl($url, array('id_order' => $id_order), $this->context)
                )));
            }

            die(json_encode(array(
                'result' => false
            )));
        } elseif (Tools::getValue('action') == 'tracking') {
            $message = '';
            $id_order = (int) Tools::getValue('id_order', null);
            $trakingNumber = Tools::getValue('trakingNumber', null);

            if (empty($id_order) || empty($trakingNumber)) {
                $result = false;
                $message = $this->l('An error occured');
            } else {
                $order = new Order($id_order);
                if ($order->setWsShippingNumber($trakingNumber)) {
                    $order->shipping_number = $trakingNumber;
                    $order->update();
                    $statut = Configuration::get('BMS_OP_STATUT_FOR_SHIPPED');
                    /**
                     * setCurrentState modifie le statut de la commande, modifie l'historique des statuts + envoi un mail selon paramétrage du statut *
                     */
                    $order->setCurrentState($statut, (int) Context::getContext()->employee->id);
                    $result = true;
                    $message = $this->l('Tracking number updated, customer has been notified by email');
                } else {
                    $result = false;
                    $message = $this->l('An error occured while updating the tracking number');
                }
            }
            die(json_encode(array(
                'result' => $result,
                'message' => $message
            )));
        } elseif (Tools::getValue('action') == 'removeTracking') {
            $id_order = (int) Tools::getValue('id_order', null);

            if (empty($id_order)) {
                $result = false;
                $message = $this->l('An error occured');
            }
            $order = new Order($id_order);
            $order->setWsShippingNumber('');
            $order->shipping_number = '';
            $order->update();
            $statut = Configuration::get('BMS_OP_STATUT_FOR_SHIPPED');
            /**
             * setCurrentState modifie le statut de la commande, modifie l'historique des statuts + envoi un mail selon paramétrage du statut *
             */
            $order->setCurrentState($statut, (int) Context::getContext()->employee->id);
            $result = true;
            $message = $this->l('Tracking skipped, customer has been notified by email');

            die(json_encode(array(
                'result' => $result,
                'message' => $message
            )));
        } elseif (Tools::getValue('action') == 'changeWeight') {
            $id_order = (int) Tools::getValue('id_order', null);
            $weight = (float) Tools::getValue('weight', null);

            if (empty($id_order) || empty($weight)) {
                die(json_encode(array(
                    'result' => false
                )));
            }

            BmsOrderPreparationInProgress::setWsWeightNumber($id_order, $weight);
            die(json_encode(array(
                'result' => true
            )));
        }
    }

    public function setBmsMedia()
    {
        Media::addJsDef(array(
            'urlOrderPacking' => $this->context->link->getAdminLink("AdminOrderPacking"),
            'errorMessage' => $this->l('Unknown barcode'),
            'popupButtonText' => $this->l('Proceed processing the order'),
            'maxQuantityErrorMessage' => $this->l('This product can’t be added. You already added necessary quantity of this product.'),
            'unknownBarcodeErrorMessage' => $this->l('This product can’t be added. This product is not from this order.'),
        ));
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/orderPacking.js');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/iframe.js');
    }

    public function initPageHeaderToolbar()
    {

        // $this->page_header_toolbar_title = $this->l('Receive Purchase Order ') . $this->po->reference;
        parent::initPageHeaderToolbar();
        BMSOrderPreparation::setPageHeaderToolbar($this);
        $this->context->smarty->assign('help_link', null);
    }
}
