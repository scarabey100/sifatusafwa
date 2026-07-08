<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaModule\AdvancedPack;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Cart;
use Shop;
use Order;
use Tools;
use Currency;
use Customer;
use Language;
use OrderDetail;
use ReflectionClass;
use ReflectionProperty;
use PrestaShop\Decimal\Number;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Adapter\Order\OrderAmountUpdater as OrderAmountUpdaterPS;
class OrderAmountUpdater extends OrderAmountUpdaterPS
{
    private $decoratedService;
    public function __construct($decoratedService)
    {
        $this->decoratedService = $decoratedService;
    }
    public function __call($name, $arguments)
    {
        $reflectedClass = new ReflectionClass($this->decoratedService);
        $method = $reflectedClass->getMethod($name);
        $method->setAccessible(true);
        return $method->invoke($this->decoratedService, ...$arguments);
    }
    public function __get($name)
    {
        $reflectionProperty = new ReflectionProperty($this->decoratedService, $name);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($this->decoratedService);
    }
    public function update(
        Order $order,
        Cart $cart,
        ?int $orderInvoiceId = null
    ): void {
        $this->cleanCaches();
        if (version_compare(_PS_VERSION_, '1.7.7.3', '>=') && method_exists($cart, 'getAssociatedLanguage')) {
            $lang = $cart->getAssociatedLanguage();
        } else {
            $lang = new Language($cart->id_lang);
        }
        $this->contextStateManager
            ->saveCurrentContext()
            ->setCart($cart)
            ->setCurrency(new Currency($cart->id_currency))
            ->setCustomer(new Customer($cart->id_customer))
            ->setLanguage($lang)
            ->setCountry($cart->getTaxCountry())
            ->setShop(new Shop($cart->id_shop))
        ;
        try {
            $computingPrecision = $this->getPrecisionFromCart($cart);
            if (version_compare(_PS_VERSION_, '1.7.7.1', '>=')) {
                $this->updateOrderDetails($order, $cart);
            } else {
                $this->updateOrderDetails($order, $cart, $computingPrecision);
            }
            if (version_compare(_PS_VERSION_, '1.7.7.5', '>=')) {
                $productsComparator = new \PrestaShop\PrestaShop\Adapter\Cart\Comparator\CartProductsComparator($cart);
            }
            $this->updateOrderCartRules($order, $cart, $computingPrecision, $orderInvoiceId);
            if (version_compare(_PS_VERSION_, '1.7.7.5', '>=') && method_exists($this, 'updateOrderModifiedProducts')) {
                $modifiedProducts = $productsComparator->getModifiedProducts();
                $this->updateOrderModifiedProducts($modifiedProducts, $cart, $order);
            }
            $this->updateOrderTotals($order, $cart, $computingPrecision);
            $this->updateOrderCarrier($order, $cart);
            if (!$order->update()) {
                throw new OrderException('Could not update order invoice in database.');
            }
            $this->updateOrderInvoices($order, $cart, $computingPrecision);
        } finally {
            $this->contextStateManager->restorePreviousContext();
        }
    }
    private function updateOrderDetails(Order $order, Cart $cart, int $computingPrecision = null): void
    {
        if (version_compare(_PS_VERSION_, '1.7.7.1', '>=')) {
            $cartProducts = $cart->getProducts(true, false, null, true, $this->keepOrderPrices);
        } else {
            $cartProducts = $cart->getProducts(true);
        }
        $cartProductsIds = [];
        foreach ($cartProducts as $cartProduct) {
            $cartProductsIds[] = (int)$cartProduct['id_product'];
        }
        foreach ($order->getCartProducts() as $orderProduct) {
            if (!in_array((int)$orderProduct['product_id'], $cartProductsIds)) {
                continue;
            }
            $orderDetail = new OrderDetail($orderProduct['id_order_detail'], null, $this->contextStateManager->getContext());
            $cartProduct = $this->getProductFromCart($cartProducts, (int) $orderDetail->product_id, (int) $orderDetail->product_attribute_id);
            if (version_compare(_PS_VERSION_, '1.7.7.1', '>=')) {
                $this->orderDetailUpdater->updateOrderDetail(
                    $orderDetail,
                    $order,
                    new Number((string) $cartProduct['price_with_reduction_without_tax']),
                    new Number((string) $cartProduct['price_with_reduction'])
                );
            } else {
                $orderDetail->id_tax_rules_group = $orderDetail->getTaxRulesGroupId();
                $unitPriceTaxExcl = (float)$cartProduct['price_with_reduction_without_tax'];
                $unitPriceTaxIncl = (float)$cartProduct['price_with_reduction'];
                $orderDetail->product_price = (float)$cartProduct['price'];
                $orderDetail->unit_price_tax_excl = $unitPriceTaxExcl;
                $orderDetail->unit_price_tax_incl = $unitPriceTaxIncl;
                $roundType = $this->getOrderConfiguration('PS_ROUND_TYPE', $order);
                switch ($roundType) {
                    case Order::ROUND_TOTAL:
                        $orderDetail->total_price_tax_excl = $unitPriceTaxExcl * $orderDetail->product_quantity;
                        $orderDetail->total_price_tax_incl = $unitPriceTaxIncl * $orderDetail->product_quantity;
                        break;
                    case Order::ROUND_LINE:
                        $orderDetail->total_price_tax_excl = Tools::ps_round($unitPriceTaxExcl * $orderDetail->product_quantity, $computingPrecision);
                        $orderDetail->total_price_tax_incl = Tools::ps_round($unitPriceTaxIncl * $orderDetail->product_quantity, $computingPrecision);
                        break;
                    case Order::ROUND_ITEM:
                    default:
                        $orderDetail->product_price = $orderDetail->unit_price_tax_excl = Tools::ps_round($unitPriceTaxExcl, $computingPrecision);
                        $orderDetail->unit_price_tax_incl = Tools::ps_round($unitPriceTaxIncl, $computingPrecision);
                        $orderDetail->total_price_tax_excl = $orderDetail->unit_price_tax_excl * $orderDetail->product_quantity;
                        $orderDetail->total_price_tax_incl = $orderDetail->total_price_tax_incl * $orderDetail->product_quantity;
                        break;
                }
                $orderDetail->updateTaxAmount($order);
                if (!$orderDetail->update()) {
                    throw new OrderException('An error occurred while editing the product line.');
                }
            }
        }
    }
}
