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
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationShippingTemplate.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/controllers/admin/AdminOrderPreparation.php';
class AdminOrderPreparationShippingController extends AdminOrderPreparationController
{

    public function renderView()
    {
        $ordersInprogress = BmsOrderPreparationInProgress::getOrdersList();

        if (! count($ordersInprogress)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPreparation', true) . '&no_order=1');
        }

        $templates = BmsOrderPreparationShippingTemplate::getTemplates();

        $tpl = $this->createTemplate('shipping.tpl');
        $tpl->assign('templates', $templates);
        $actionTab = BMSOrderPreparation::getActionsTabTpl("shipping");
        return $actionTab->fetch() . $tpl->fetch();
    }

    public function processImport()
    {
        if (Tools::getValue('bms_process_import') == 1) {
            if (! $_FILES['bms_file']['name']) {
                $this->errors[] = $this->l('You have to select a file.');
            } else {
                $file = $_FILES['bms_file'];
                if ($file) {
                    $arr_file = explode(".", $file['name']);
                    $extension = end($arr_file);
                    $extension_allowed = array(
                        'csv'
                    );

                    if (! in_array($extension, $extension_allowed)) {
                        $this->errors[] = $this->l('This type of file is not allowed');
                    }
                    $maxSize = 10485760; // 10mo * 1024 *1024
                    if ($file['size'] > $maxSize) {
                        $this->errors[] = $this->l('The file size exceeds the max size. Max size') . ': 5 Mo';
                    }
                    if (empty($file['name']) || ! Tools::getValue('bms_template')) {
                        $this->errors[] = $this->l('An error occured');
                    }

                    if (! count($this->errors)) {
                        $id_shipping_template = Tools::getValue('bms_template');

                        $shipping_tpl = new BmsOrderPreparationShippingTemplate($id_shipping_template);

                        $import = $shipping_tpl->import($file);
                        if (is_array($import)) {
                            $this->errors = $import;
                        } else {
                            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPreparationShipping', true) . '&conf=4');
                        }
                    }
                }
            }
        }
    }

    public function processUpdateTrackingNumber()
    {
        $id_order = Tools::getValue('id_order');
        $tracking_number = Tools::getValue('tracking_number');
        $order = new Order((int) $id_order);

        $message = 'error';

        if ($order->id) {
            if ($order->setWsShippingNumber($tracking_number) == true) {
                $order->shipping_number = $tracking_number;
                $order->update();
                $message = 'success';
            }
        }
        die(json_encode(array(
            'message' => $message
        )));
    }

    public function processExportTpl()
    {
        if (Tools::getValue('bms_template_export')) {
            $id_tpl = Tools::getValue('bms_template_export');
            if ($id_tpl) {
                $template = new BmsOrderPreparationShippingTemplate($id_tpl);
                $orders = $this->getExportableOrders($template);
                $template->export($orders);
            }
        } else {
            if (Tools::getValue('id_order')) {
                $order = new Order((int) Tools::getValue('id_order'));
                $id_carrier = $order->id_carrier;
                $template = BmsOrderPreparationShippingTemplate::getTemplateByCarrier($id_carrier);
                if ($template) {
                    $orders[] = $order;
                    $template->export($orders);
                } else {
                    Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderPackingConfirmTab', true) . '&no_template=1&id_order=' . Tools::getValue('id_order'));
                }
            }
        }
    }

    private function getExportableOrders($template)
    {
        $shipping_methods = explode(',', $template->shipping_methods);

        $query = new DbQuery();
        $sql = $query->select('id_order')
            ->from('bms_orderpreparation_inprogress')
            ->build();
        $arr_id_order = Db::getInstance()->executeS($sql);

        $orders = array();
        if (is_array($arr_id_order) && count($arr_id_order)) {
            foreach ($arr_id_order as $order_line) {
                $order = new Order($order_line['id_order']);
                if (in_array($order->id_carrier, $shipping_methods)) {
                    $orders[] = $order;
                }
            }
        }

        return $orders;
    }
}
