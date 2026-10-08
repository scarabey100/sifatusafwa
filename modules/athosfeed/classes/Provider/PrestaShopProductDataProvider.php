<?php

namespace AthosFeed\Provider;

use AthosFeed\Contract\ProductDataProviderInterface;

class PrestaShopProductDataProvider implements ProductDataProviderInterface
{
    private $context;

    public function __construct(\Context $context = null)
    {
        $this->context = $context ?: \Context::getContext();
    }

    public function getProductIdsAfter($lastProductId, $limit, $shopId, $includeInactive = true)
    {
        $sql = 'SELECT p.id_product FROM `' . _DB_PREFIX_ . 'product` p '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = p.id_product '
            . 'AND ps.id_shop = ' . (int) $shopId . ' '
            . 'WHERE p.id_product > ' . (int) $lastProductId . ' '
            . (!$includeInactive ? 'AND ps.active = 1 ' : '')
            . 'ORDER BY p.id_product ASC LIMIT ' . (int) $limit;
        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return is_array($rows) ? array_map(function ($row) { return (int) $row['id_product']; }, $rows) : array();
    }

    public function getProductData($productId, $languageId, $shopId)
    {
        $product = new \Product((int) $productId, false, (int) $languageId, (int) $shopId);
        if (!\Validate::isLoadedObject($product)) {
            throw new \RuntimeException('Product cannot be loaded');
        }

        $currency = new \Currency((int) $this->context->currency->id);
        $language = new \Language((int) $languageId);
        $combinations = $this->buildCombinations($product, $languageId, $shopId);
        $images = $this->buildImages($product, $languageId);
        $categories = $this->buildCategories($product, $languageId, $shopId);
        $basePrice = $this->price($product->id, 0, false);
        $salePrice = $this->price($product->id, 0, true);
        $quantity = (int) \StockAvailable::getQuantityAvailableByProduct($product->id, 0, $shopId);

        return array(
            'product_id' => (int) $product->id,
            'reference' => (string) $product->reference,
            'ean13' => (string) $product->ean13,
            'upc' => (string) $product->upc,
            'name' => (string) $product->name,
            'description_short' => (string) $product->description_short,
            'description' => (string) $product->description,
            'url' => $this->context->link->getProductLink($product, null, null, null, $languageId, $shopId),
            'image' => $images ? reset($images) : null,
            'additional_images' => $images ? array_slice($images, 1) : array(),
            'price' => $basePrice,
            'sale_price' => $salePrice,
            'currency' => (string) $currency->iso_code,
            'quantity' => $quantity,
            'available' => $this->isAvailable($product, $quantity),
            'active' => (bool) $product->active,
            'available_for_order' => (bool) $product->available_for_order,
            'brand' => $product->id_manufacturer ? \Manufacturer::getNameById($product->id_manufacturer) : null,
            'category_ids' => array_keys($categories),
            'category_names' => array_values(array_map(function ($category) { return $category['name']; }, $categories)),
            'category_paths' => array_values(array_map(function ($category) { return $category['path']; }, $categories)),
            'default_category_id' => (int) $product->id_category_default,
            'default_category' => isset($categories[(int) $product->id_category_default]) ? $categories[(int) $product->id_category_default]['name'] : null,
            'searchable_keywords' => array_values(array_filter(array((string) $product->reference, (string) $product->ean13, (string) $product->upc, (string) $product->name))),
            'features' => $this->buildFeatures($product, $languageId),
            'date_created' => (string) $product->date_add,
            'date_updated' => (string) $product->date_upd,
            'language' => (string) $language->iso_code,
            'language_id' => (int) $languageId,
            'shop_id' => (int) $shopId,
            'combinations' => $combinations,
        );
    }

    private function buildCombinations(\Product $product, $languageId, $shopId)
    {
        $rows = $product->getAttributeCombinations((int) $languageId);
        $combinationImages = $product->getCombinationImages((int) $languageId);
        $grouped = array();
        foreach ((array) $rows as $row) {
            $id = (int) $row['id_product_attribute'];
            if (!$id) {
                continue;
            }
            if (!isset($grouped[$id])) {
                $quantity = (int) \StockAvailable::getQuantityAvailableByProduct($product->id, $id, $shopId);
                $grouped[$id] = array(
                    'combination_id' => $id,
                    'reference' => (string) $row['reference'],
                    'ean13' => (string) $row['ean13'],
                    'upc' => (string) $row['upc'],
                    'price' => $this->price($product->id, $id, false),
                    'sale_price' => $this->price($product->id, $id, true),
                    'quantity' => $quantity,
                    'available' => $this->isAvailable($product, $quantity),
                    'image' => $this->combinationImage($product, $id, $combinationImages),
                    'attributes' => array(),
                    'swatches' => array(),
                    'position' => count($grouped) + 1,
                );
            }
            if (!empty($row['group_name']) && !empty($row['attribute_name'])) {
                $grouped[$id]['attributes'][(string) $row['group_name']] = (string) $row['attribute_name'];
                $color = isset($row['attribute_color']) ? $row['attribute_color'] : (isset($row['color']) ? $row['color'] : '');
                $isColor = !empty($row['is_color_group']) || (isset($row['group_type']) && $row['group_type'] === 'color');
                if ($isColor && $color !== '') {
                    $grouped[$id]['swatches'][(string) $row['group_name']] = (string) $color;
                }
            }
        }

        ksort($grouped, SORT_NUMERIC);
        return array_values($grouped);
    }

    private function price($productId, $combinationId, $withReduction)
    {
        $specificPrice = null;

        return (float) \Product::getPriceStatic(
            (int) $productId,
            true,
            (int) $combinationId,
            6,
            null,
            false,
            (bool) $withReduction,
            1,
            false,
            0,
            (int) $this->context->cart->id,
            0,
            $specificPrice,
            true,
            true,
            $this->context,
            false
        );
    }

    private function isAvailable(\Product $product, $quantity)
    {
        return (bool) $product->active && (bool) $product->available_for_order
            && ($quantity > 0 || (int) $product->out_of_stock === 1 || ((int) $product->out_of_stock === 2 && (bool) \Configuration::get('PS_ORDER_OUT_OF_STOCK')));
    }

    private function buildImages(\Product $product, $languageId)
    {
        $urls = array();
        foreach ((array) $product->getImages((int) $languageId) as $image) {
            $urls[] = $this->context->link->getImageLink($product->link_rewrite, $product->id . '-' . (int) $image['id_image'], 'large_default');
        }
        return array_values(array_unique($urls));
    }

    private function combinationImage(\Product $product, $combinationId, array $images)
    {
        if (!empty($images[$combinationId][0]['id_image'])) {
            return $this->context->link->getImageLink($product->link_rewrite, $product->id . '-' . (int) $images[$combinationId][0]['id_image'], 'large_default');
        }
        return null;
    }

    private function buildCategories(\Product $product, $languageId, $shopId)
    {
        $result = array();
        foreach ((array) $product->getCategories() as $categoryId) {
            $category = new \Category((int) $categoryId, (int) $languageId, (int) $shopId);
            if (\Validate::isLoadedObject($category)) {
                $parents = $category->getParentsCategories((int) $languageId);
                $path = array();
                foreach (array_reverse((array) $parents) as $parent) {
                    if (!empty($parent['name']) && (int) $parent['id_category'] !== (int) \Configuration::get('PS_ROOT_CATEGORY')) {
                        $path[] = trim((string) $parent['name']);
                    }
                }
                $result[(int) $categoryId] = array(
                    'id' => (int) $categoryId,
                    'name' => trim((string) $category->name),
                    'path' => implode('>', array_values(array_unique($path))),
                );
            }
        }
        return $result;
    }

    private function buildFeatures(\Product $product, $languageId)
    {
        $result = array();
        foreach ((array) $product->getFrontFeatures((int) $languageId) as $feature) {
            if (isset($feature['name'], $feature['value']) && $feature['name'] !== '' && $feature['value'] !== '') {
                $result[(string) $feature['name']] = (string) $feature['value'];
            }
        }
        return $result;
    }
}
