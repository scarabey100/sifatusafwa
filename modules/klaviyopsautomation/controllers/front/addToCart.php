<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\KlaviyoPsAjaxModuleFrontController;

class KlaviyoPsAddToCartModuleFrontController extends KlaviyoPsAjaxModuleFrontController
{
    /**
     * @inheritDoc
     */
    protected function ajaxProcess()
    {
        $payload = $this->buildAddedToCartPayload();

        // If we cannot build a cart because we don't have a cart in context or if the cart is actually empty.
        if (!isset($payload['ItemCount']) || $payload['ItemCount'] == 0) {
            $this->errors[] = 'Invalid or empty cart.';
        } else {
            $this->returnData = $payload;
        }
    }

    /**
     * Build payload for Added to Cart event.
     *
     * @return array
     */
    private function buildAddedToCartPayload()
    {
        if (!isset($this->context->cart) || empty($this->context->cart->id)) {
            return array();
        }

        $cartId = $this->context->cart->id;
        $cartObject = new Cart($cartId);
        $cartLineItemsArray = KlaviyoUtils::buildCartLineItemsArray($cartObject);

        return array_merge(
            $this->getAddedItem(),
            array(
                '$value' => $cartObject->getOrderTotal(),
                'ItemNames' => isset($cartLineItemsArray['itemNames']) ? $cartLineItemsArray['itemNames'] : array(),
                'Items' => isset($cartLineItemsArray['lineItems']) ? $cartLineItemsArray['lineItems'] : array(),
                'ItemCount' => isset($cartLineItemsArray['itemCount']) ? (int) $cartLineItemsArray['itemCount'] : 0,
                'Categories' => isset($cartLineItemsArray['uniqueCategories']) ? $cartLineItemsArray['uniqueCategories'] : array(),
                'Tags' => isset($cartLineItemsArray['uniqueTags']) ? $cartLineItemsArray['uniqueTags'] : array(),
                'ReclaimCartUrl' => KlaviyoUtils::buildReclaimCartUrl($cartObject),
                'external_catalog_id' => KlaviyoUtils::formatKlaviyoCatalogIdentifier($this->context->shop->id, $this->context->language->id),
                'integration_key' => KlaviyoValue::SERVICE,
            )
        );
    }

    /**
     * Get details of most recently added item to a cart.
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getAddedItem()
    {
        // Cart does not have getLastProduct method if no context.
        try {
            $itemDetails = $this->context->cart->getLastProduct();
        } catch (Exception $e) {
            return array();
        }

        // getLastProduct() can return false if item is not found.
        if (!$itemDetails) {
            return array();
        }

        if (empty($itemDetails['id_product'])) {
            return array();
        }

        $productId = (int) $itemDetails['id_product'];
        $productAttributeId = isset($itemDetails['id_product_attribute']) ? (int) $itemDetails['id_product_attribute'] : 0;
        $langId = (int) $this->context->language->id;
        $shopId = (int) $this->context->shop->id;
        $product = new Product($productId, $full = false, $id_lang = $langId, $id_shop = $shopId);

        return array(
            'AddedItemCategories' => ProductPayloadService::getCategoryNamesForProduct($productId, $langId),
            'AddedItemDescription' => strip_tags($itemDetails['description_short']),
            'AddedItemImageURL' => KlaviyoUtils::getProductImageLink($productId, $itemDetails['id_product_attribute'], $shopId, $langId),
            'AddedItemPrice' => (float) $itemDetails['price'],
            'AddedItemPriceInclTax' => $product->getPrice(),
            'AddedItemProductID' => (int) $productId,
            'AddedItemProductName' => $itemDetails['name'],
            'AddedItemSKU' => $itemDetails['reference'],
            'AddedItemTags' => ProductPayloadService::getProductTagsArray($productId, $langId),
            'AddedItemURL' => ProductPayloadService::getProductUrl($product, $langId, $shopId),
            'AddedItemConstructedVariantID' => KlaviyoUtils::formatKlaviyoVariantIdentifier($itemDetails['id_product'], $itemDetails['id_product_attribute']),
        );
    }
}

class KlaviyoPsAutomationAddToCartModuleFrontController extends KlaviyoPsAddToCartModuleFrontController
{
}
