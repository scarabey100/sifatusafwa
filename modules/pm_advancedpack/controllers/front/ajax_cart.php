<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_advancedpackajax_cartModuleFrontController extends CartController
{
    /**
     * @see CartController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $presenter = new PrestaShop\PrestaShop\Adapter\Cart\CartPresenter();
        $presented_cart = $presenter->present($this->context->cart, true);
        if (!isset($presented_cart['products']) || !count($presented_cart['products'])) {
            return;
        }
        $groupPriceDisplayMethod = (int)Group::getCurrent()->price_display_method;
        $psTaxDisplay = (int)Configuration::get('PS_TAX_DISPLAY');
        if (!$groupPriceDisplayMethod && !$psTaxDisplay) {
            return;
        }
        $idPackList = AdvancedPack::getIdsPacks();
        if (empty($idPackList)) {
            return;
        }
        $lazyArrayMethod = (class_exists(PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartLazyArray::class) && $presented_cart instanceof PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartLazyArray);
        foreach ($presented_cart['products'] as $product) {
            if (!in_array($product['id_product'], $idPackList)) {
                continue;
            }
            $packIdTaxRulesGroup = AdvancedPack::getPackIdTaxRulesGroup((int)$product['id_product']);
            if ($lazyArrayMethod) {
                $presentedCartTotals = $presented_cart->offsetGet('totals');
                $presentedCartSubTotals = $presented_cart->offsetGet('subtotals');
            } else {
                $presentedCartTotals = $presented_cart['totals'];
                $presentedCartSubTotals = $presented_cart['subtotals'];
            }
            if (!$packIdTaxRulesGroup) {
                if ($groupPriceDisplayMethod) {
                    $presentedCartTotals['total']['amount'] -= ((int)$product['cart_quantity'] * $product['price_wt']);
                }
                $newPrice = AdvancedPack::getPackPrice((int)$product['id_product'], false, true, true, 6, AdvancedPack::getIdProductAttributeListByIdPack((int)$product['id_product'], (int)$product['id_product_attribute']), [], [], true);
                $newPriceWt = AdvancedPack::getPackPrice((int)$product['id_product'], true, true, true, 6, AdvancedPack::getIdProductAttributeListByIdPack((int)$product['id_product'], (int)$product['id_product_attribute']), [], [], true);
                if ($psTaxDisplay) {
                    $presentedCartSubTotals['tax']['amount'] += (int)$product['cart_quantity'] * ($newPriceWt - $newPrice);
                    $presentedCartSubTotals['tax']['value'] = Tools::displayPrice($presentedCartSubTotals['tax']['amount']);
                    $presentedCartTotals['total_excluding_tax']['amount'] -= (int)$product['cart_quantity'] * ($newPriceWt - $newPrice);
                    $presentedCartTotals['total_excluding_tax']['value'] = Tools::displayPrice($presentedCartTotals['total_excluding_tax']['amount']);
                    if ($lazyArrayMethod) {
                        $presented_cart->offsetSet('price_with_reduction_without_tax', $newPrice, true);
                    } else {
                        $product['price_with_reduction_without_tax'] = $newPrice;
                    }
                }
                if ($groupPriceDisplayMethod) {
                    if ($lazyArrayMethod) {
                        $product->offsetSet('price', Tools::displayPrice($newPrice), true);
                        $product->offsetSet('price_wt', $newPriceWt, true);
                        $product->offsetSet('total', Tools::displayPrice((int)$product['cart_quantity'] * $newPrice), true);
                        $product->offsetSet('total_wt', (int)$product['cart_quantity'] * $newPriceWt, true);
                        $presented_cart->offsetSet('product', $product, true);
                    } else {
                        $product['price'] = Tools::displayPrice($newPrice);
                        $product['price_wt'] = $newPriceWt;
                        $product['total'] = Tools::displayPrice((int)$product['cart_quantity'] * $newPrice);
                        $product['total_wt'] = ((int)$product['cart_quantity'] * $newPriceWt);
                    }
                }
                if ($groupPriceDisplayMethod) {
                    $presentedCartTotals['total']['amount'] += ((int)$product['cart_quantity'] * $newPrice);
                    $presentedCartTotals['total']['value'] = Tools::displayPrice($presentedCartTotals['total']['amount']);
                }
            } else {
                $idProductAttributeList = AdvancedPack::getIdProductAttributeListByIdPack((int)$product['id_product'], (int)$product['id_product_attribute']);
                if ($groupPriceDisplayMethod && AdvancedPack::getPackEcoTax((int)$product['id_product'], $idProductAttributeList) > 0) {
                    $newPrice = AdvancedPack::getPackPrice((int)$product['id_product'], false, true, true, 6, AdvancedPack::getIdProductAttributeListByIdPack((int)$product['id_product'], (int)$product['id_product_attribute']), [], [], true);
                    $newPriceWt = AdvancedPack::getPackPrice((int)$product['id_product'], true, true, true, 6, AdvancedPack::getIdProductAttributeListByIdPack((int)$product['id_product'], (int)$product['id_product_attribute']), [], [], true);
                    if ($psTaxDisplay) {
                        $diffTaxes = (AdvancedPack::getPackEcoTax((int)$product['id_product'], $idProductAttributeList) - AdvancedPack::getPackEcoTax((int)$product['id_product'], $idProductAttributeList, $packIdTaxRulesGroup));
                        $presentedCartSubTotals['tax']['amount'] -= (int)$product['cart_quantity'] * $diffTaxes;
                        $presentedCartSubTotals['tax']['value'] = Tools::displayPrice($presentedCartSubTotals['tax']['amount']);
                    }
                    $presentedCartTotals['total']['amount'] -= ((int)$product['cart_quantity'] * $product['price_with_reduction_without_tax']);
                    $presentedCartTotals['total_excluding_tax']['amount'] -= ((int)$product['cart_quantity'] * $product['price_with_reduction_without_tax']);
                    $presentedCartSubTotals['products']['amount'] -= ((int)$product['cart_quantity'] * $product['price_with_reduction_without_tax']);
                    if ($lazyArrayMethod) {
                        $product->offsetSet('price', Tools::displayPrice($newPrice), true);
                        $product->offsetSet('price_wt', $newPriceWt, true);
                        $product->offsetSet('total', Tools::displayPrice((int)$product['cart_quantity'] * $newPrice), true);
                        $product->offsetSet('total_wt', (int)$product['cart_quantity'] * $newPriceWt, true);
                        $presented_cart->offsetSet('product', $product, true);
                    } else {
                        $product['price'] = Tools::displayPrice($newPrice);
                        $product['price_wt'] = $newPriceWt;
                        $product['total'] = Tools::displayPrice((int)$product['cart_quantity'] * $newPrice);
                        $product['total_wt'] = ((int)$product['cart_quantity'] * $newPriceWt);
                    }
                    $presentedCartTotals['total']['amount'] += ((int)$product['cart_quantity'] * $newPrice);
                    $presentedCartTotals['total_excluding_tax']['amount'] += ((int)$product['cart_quantity'] * $newPrice);
                    $presentedCartSubTotals['products']['amount'] += ((int)$product['cart_quantity'] * $newPrice);
                    $presentedCartTotals['total']['value'] = Tools::displayPrice($presentedCartTotals['total']['amount']);
                    $presentedCartTotals['total_excluding_tax']['value'] = Tools::displayPrice($presentedCartTotals['total_excluding_tax']['amount']);
                    $presentedCartSubTotals['products']['value'] = Tools::displayPrice($presentedCartSubTotals['products']['amount']);
                }
            }
            if ($lazyArrayMethod) {
                $presented_cart->offsetSet('totals', $presentedCartTotals, true);
                $presented_cart->offsetSet('subtotals', $presentedCartSubTotals, true);
            } else {
                $presented_cart['totals'] = $presentedCartTotals;
                $presented_cart['subtotals'] = $presentedCartSubTotals;
            }
        }
        $this->context->smarty->assign([
            'cart' => $presented_cart,
        ]);
    }
}
