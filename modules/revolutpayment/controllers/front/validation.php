<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class RevolutpaymentValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!empty(Tools::getValue('_rp_fr'))) {
            return $this->redirectCheckoutWithError(Tools::getValue('_rp_fr'));
        }

        $public_id = !empty(Tools::getValue('_rp_oid')) ? Tools::getValue('_rp_oid') : Tools::getValue('public_id');
        $revolut_order_details = $this->module->getRevolutOrderByPublicId($public_id);

        if (empty($revolut_order_details) || empty($revolut_order_details['id_revolut_order']) || empty($revolut_order_details['id_cart'])) {
            return $this->redirectCheckoutWithError('Order process has been interrupted , Missing required payment parameters');
        }

        $revolut_order = $this->module->revolutApi->retrieveRevolutOrder($revolut_order_details['id_revolut_order']);

        if (in_array($revolut_order['state'], ['CANCELLED', 'FAILED', 'PENDING'])) {
            return $this->redirectCheckoutWithError('Something went wrong while taking the payment. Payment should be retried. Payment Status: ' . $revolut_order['state']);
        }

        if ($revolut_order_details['id_cart'] != $this->context->cart->id) {
            $cart = new Cart($revolut_order['id_cart']);
            $this->context->cookie->id_lang = $cart->id_lang;
            $this->context->cookie->id_currency = $cart->id_currency;

            $this->context->cookie->id_guest = $cart->id_guest;
            $this->context->cookie->id_customer = $cart->id_customer;
            $this->context->shop->id_shop_group = $cart->id_shop_group;
            $this->context->shop->id = $cart->id_shop;
            $this->context->cart->id_address_delivery = $cart->id_address_delivery;
            $this->context->cart->id_address_invoice = $cart->id_address_invoice;
            $this->context->id_customer = $cart->id_customer;

            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;
            $this->context->cookie->write();
            CartRule::autoAddToCart($this->context);
        }

        if (!isset($revolut_order['order_amount']) || !isset($revolut_order['order_amount']['value']) || $revolut_order['order_amount']['value'] != $this->module->createRevolutAmount($this->context->cart->getOrderTotal(), $this->context->currency->iso_code)) {
            $this->module->revolutApi->cancelRevolutOrder($revolut_order['id_revolut_order']);
            PrestaShopLogger::addLog('Error: Order (' . $revolut_order['id'] . ') canceled due to the total amount differences.', 3);
            $this->redirectCheckoutWithError('Something went wrong while taking the payment. The payment has been canceled, please try again.');
        }

        /*
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (
            !$this->module->active
            || $this->context->cart->id_customer == 0
            || $this->context->cart->id_address_delivery == 0
            || $this->context->cart->id_address_invoice == 0
        ) {
            $this->redirectCheckoutWithError();
        }

        /** @var CustomerCore $customer */
        $customer = new Customer($this->context->cart->id_customer);

        /*
         * Check if this is a vlaid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            $this->redirectCheckoutWithError();
        }

        $ps_order_id = (int) $this->module->createPrestaShopOrder($customer);

        if (!$ps_order_id) {
            PrestaShopLogger::addLog('Error: Can not create an order', 3);

            return $this->redirectCheckoutWithError();
        }

        $revolut_order = $this->module->getRevolutOrder($ps_order_id);

        if (empty($revolut_order['id_revolut_order'])) {
            return $this->redirectCheckoutWithError();
        }

        $this->module->processRevolutOrderResult($ps_order_id, $revolut_order['id_revolut_order']);

        $this->module->updateRevolutOrderLineItemsAndShippingData($ps_order_id, $revolut_order['id_revolut_order']);

        Tools::redirect($this->module->getOrderConfirmationLink($this->context->cart->id, $customer->secure_key, $ps_order_id));
    }

    protected function redirectCheckoutWithError($error_message = 'Something went wrong while taking the payment')
    {
        if ($this->module->isPs17) {
            return Tools::redirect('order?_rp_fr=' . $error_message);
        }

        Tools::redirect('index.php?controller=order&step=3&_rp_fr=' . $error_message);
    }
}
