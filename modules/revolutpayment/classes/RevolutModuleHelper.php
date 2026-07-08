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
trait RevolutModuleHelper
{
    public function handleFailedPayment($ps_order_id, $revolut_state)
    {
        PrestaShopLogger::addLog(
            'Something went wrong while taking the payment. Payment should be retried. 
                Payment Status: ' . $revolut_state,
            3
        );

        // update order status to error
        $new_order_status = Configuration::get('PS_OS_ERROR');
        $this->updateOrderStatus($ps_order_id, $new_order_status);
        $this->addOrderMessage(
            $ps_order_id,
            'Something went wrong while completing this payment. Please reach out to your customer and ask them to try again. Revolut order state: ' . $revolut_state
        );
    }

    protected function updateOrderStatus($id_order, $order_status)
    {
        $history = new OrderHistory();
        $history->id_order = (int) $id_order;
        $history->changeIdOrderState($order_status, $id_order);
        $history->addWithemail();
    }

    protected function addOrderMessage($id_new_order, $message)
    {
        $customer_thread_model = new CustomerThread();
        $order_customer_thread = $customer_thread_model->getIdCustomerThreadByEmailAndIdOrder(
            $this->context->customer->email,
            $id_new_order
        );
        $customer_thread_id = 0;
        if ($order_customer_thread) {
            $customer_thread_id = $order_customer_thread;
        } else {
            $customer_thread = new CustomerThread();
            $customer_thread->id_shop = $this->context->customer->id_shop;
            $customer_thread->id_lang = $this->context->customer->id_lang;
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = $this->context->customer->id;
            $customer_thread->id_order = (int) $id_new_order;
            $customer_thread->status = 'open';
            $customer_thread->email = $this->context->customer->email;
            $customer_thread->token = Tools::getToken(false);
            $customer_thread->save();
            $customer_thread_id = $customer_thread->id;
        }
        if ($customer_thread_id) {
            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread_id;
            $customer_message->message = $message;
            $customer_message->private = 1;
            $customer_message->save();
        }
    }

    public function collectShippingDetails($ps_order_id)
    {
        $ps_order = new Order($ps_order_id);
        $id_address_delivery = $ps_order->id_address_delivery;
        $address = new Address($id_address_delivery);
        $country = new Country($address->id_country);

        if (empty($address->address1) || empty($address->postcode) || empty($address->city) || empty($country->iso_code)) {
            return [];
        }

        $id_customer = $ps_order->id_customer;
        $customer = new Customer($id_customer);
        $phone_number = null;
        $address_info = array_filter([
            'street_line_1' => $address->address1,
            'street_line_2' => $address->address2,
            'postcode' => $address->postcode,
            'city' => $address->city,
            'country_code' => $country->iso_code,
        ]);

        if (!empty($address->phone_mobile)) {
            $phone_number = $address->phone_mobile;
        } elseif (!empty($address->phone)) {
            $phone_number = $address->phone;
        }

        if (empty($phone_number) && empty($customer->email)) {
            return ['address' => $address_info];
        }

        $contact_info = array_filter([
            'name' => empty($address->firstname) || empty($address->lastname) ? null : "{$address->firstname} {$address->lastname}",
            'email' => $customer->email,
            'phone' => $phone_number,
        ]);

        $shipping_details = [
            'address' => $address_info,
            'contact' => $contact_info,
        ];

        return $shipping_details;
    }

    public function collectLineItems($ps_order_id)
    {
        $ps_order = new Order($ps_order_id);
        $currency = $this->context->currency->iso_code;
        $id_lang = $this->context->language->id;
        $line_items = [];

        if (!Validate::isLoadedObject($ps_order) && !empty($currency)) {
            return [];
        }

        $products = $ps_order->getProducts();

        foreach ($products as $product) {
            $product_id = isset($product['id_product']) ? $product['id_product'] : null;
            $product_name = isset($product['product_name']) ? $product['product_name'] : null;
            $product_type = isset($product['is_virtual']) && $product['is_virtual'] ? 'service' : 'physical';
            $quantity_value = isset($product['product_quantity']) ? $product['product_quantity'] : null;
            $unit_price_amount = isset($product['original_product_price']) ? $product['original_product_price'] : null;
            $total_amount = isset($product['unit_price_tax_incl']) ? $product['unit_price_tax_incl'] : null;

            if (empty($product_name) || empty($product_type) || empty($quantity_value) || empty($unit_price_amount) || empty($total_amount)) {
                continue;
            }

            $tax_name = (isset($product['tax_name']) && !empty($product['tax_name'])) ? $product['tax_name'] : 'Tax';
            $tax_amount = isset($product['tax_rate']) ? ($product['tax_rate'] / 100) * $unit_price_amount : null;
            $product_link = $this->context->link->getProductLink($product);
            $product_reference = isset($product['product_reference']) ? $product['product_reference'] : null;

            $product_obj = new Product($product_id, true, $id_lang);
            $product_description = !empty($product_obj->description) ? $product_obj->description : null;

            if (!empty($product_description) && strlen($product_description) > 1024) {
                $product_description = substr($product_description, 0, 1024);
            }

            $discount = null;
            $taxes = null;

            if (!empty($tax_amount)) {
                $taxes = [
                    'name' => $tax_name,
                    'amount' => $this->createRevolutAmount(Tools::ps_round($tax_amount, 2), $currency),
                ];
            }

            if ($unit_price_amount + $tax_amount > $total_amount) {
                $discount_amount = ($unit_price_amount + $tax_amount) - $total_amount;
                if ($discount_amount > 0) {
                    $discount = [
                        'name' => 'Discount',
                        'amount' => $this->createRevolutAmount($discount_amount, $currency),
                    ];
                }
            }

            $line_item = [
                'name' => $product_name,
                'type' => $product_type,
                'quantity' => [
                    'value' => $quantity_value,
                ],
                'unit_price_amount' => $this->createRevolutAmount($unit_price_amount, $currency),
                'total_amount' => $this->createRevolutAmount($total_amount, $currency),
                'taxes' => empty($taxes) ? null : [$taxes],
                'description' => $product_description,
                'external_id' => $product_reference,
                'discount' => $discount,
            ];

            $line_items[] = array_filter($line_item);
        }

        return $line_items;
    }

    public function updateRevolutOrderLineItemsAndShippingData($ps_order_id, $revolut_order_id)
    {
        try {
            $params = array_filter([
                'line_items' => $this->collectLineItems($ps_order_id),
                'shipping' => $this->collectShippingDetails($ps_order_id),
            ]);

            if (!empty($params)) {
                $this->revolutApi->updateRevolutOrder($revolut_order_id, $params);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error: Couldnt update order line items ' . $e->getMessage(), 3);
        }
    }

    public function createRevolutAmount($amount, $currency)
    {
        // YEN currency's minor amount is directly YENs, it does not have fractional values.
        if (!in_array($currency, ['JPY', 'jpy'])) {
            $amount = Tools::ps_round($amount * 100);
        }

        return $amount;
    }
}
