<?php

class AdminExtraordercolumnsController extends ModuleAdminController
{
    public function ajaxProcessPopup()
    {
        $idOrder = Tools::getValue('idOrder');

        $order = new Order($idOrder);
        $this->context->cart = new Cart($order->id_cart);
        $carriers = Carrier::getCarriersForOrder(Address::getZoneById((int)$order->id_address_delivery));

        $this->context->smarty->assign(
            [
                'idOrder' => $idOrder,
                'order' => $order,
                'carriers' => $carriers,
            ]
        );

        exit($this->module->display(_PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . $this->module->name . '.php', 'popup.tpl'));
    }

    public function ajaxProcessCreate()
    {
        $idOrder = Tools::getValue('idOrder');
        $tracking_number = Tools::getValue('trackingNumber');
        $order = new Order((int)$idOrder);

        $message = 'error';

        if ($order->id) {
            if ($order->setWsShippingNumber($tracking_number)) {
                $order->shipping_number = $tracking_number;
                $order->update();
                $message = 'success';

                $orderCarrier = new OrderCarrier((int) $order->getIdOrderCarrier());
                $orderCarrier->sendInTransitEmail($order);
            }
        }

        die($message);
//        $idOrder = Tools::getValue('idOrder');
//        $idCarrierOld = Tools::getValue('idCarrierOld');
//        $idCarrierNew = Tools::getValue('idCarrierNew');
//        $trackingNumber = Tools::getValue('trackingNumber');
//        echo $idOrder.' -- '.$idCarrierNew.' -- '.$trackingNumber. '--'. $idCarrierOld;
    }
}
