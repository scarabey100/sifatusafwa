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

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationInProgress.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/ProductHelper.php';
class AdminOrderPackingConfirmTabController extends ModuleAdminController
{

    protected $filter = null;

    public $id_order = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'order_detail';
        $this->className = 'OrderDetail';
        $this->identifier = 'id_order_detail';
        $this->lang = false;

        parent::__construct();

        $this->override_folder = 'order_packing/';
    }

    public function init()
    {
        parent::init();

        if (Tools::getValue('no_template')) {
            $this->errors[] = $this->l('There is no template suited to the carrier of this order');
        }
        $id_order = (int) Tools::getValue('id_order', null);
        if (! $id_order) {
            $this->errors[] = $this->l('You do not have permission to delete this.');
        } else {
            $this->id_order = $id_order;
        }
    }

    public function initContent()
    {
        parent::initContent();
        $this->setBmsMedia();
    }

    public function initProcess()
    {
        $this->display_header = false;
        $this->display_header_javascript = true;
        $this->display_footer = false;
        $this->content_only = false;
        $this->lite_display = true;

        parent::initProcess();
    }

    public function renderList()
    {
        return $this->renderView();
    }

    public function renderView()
    {
        $order = new Order($this->id_order);
        $state = new OrderState($order->current_state);
        $carrier = new Carrier($order->id_carrier);
        $invoice_address = new Address((int) $order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $datas = BmsOrderPreparationProductHelper::getAllProduct(array(
            $order
        ));

        $tpl = $this->createTemplate("header.tpl");
        $body = $this->createTemplate("body.tpl");

        $body->assign('id_order', (int) $this->id_order);
        $body->assign('shippingNumber', $order->getWsShippingNumber());

        $tpl->assign('id_order', sprintf("%'09d", (int) $this->id_order));
        $tpl->assign('reference', $order->reference);
        $tpl->assign('customerName', $customer->firstname . ' ' . $customer->lastname);
        $tpl->assign('shippingMethod', $carrier->name);
        $tpl->assign('shippingCountry', $invoice_address->country);
        $tpl->assign('orderStatus', $state->name[$this->context->language->id]);
        $tpl->assign('productCount', $datas['qte']);

        $tpl->assign('nokSoundUrl', Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/sound/wrong.mp3');
        $tpl->assign('okSoundUrl', Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/sound/correct.mp3');

        return $tpl->fetch() . $body->fetch();
    }

    public function setBmsMedia()
    {
        Media::addJsDef(array(
            'urlOrderPacking' => $this->context->link->getAdminLink("AdminOrderPacking"),
            'errorMessage' => $this->l('Unknown barcode'),
            'errorMessageTracking' => $this->l('Please insert tracking number'),
            'id_order' => (int) $this->id_order
        ));
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/orderPacking.js');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/orderPackingConfirm.js');
    }
}
