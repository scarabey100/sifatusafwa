<?php

namespace AthosFeed\Normalizer;

use AthosFeed\Schema\FeedSchema;

class ProductNormalizer
{
    public function normalizeProduct(array $source)
    {
        return $this->baseRecord($source, null);
    }

    public function normalizeCombination(array $source, array $combination)
    {
        return $this->baseRecord($source, $combination);
    }

    private function baseRecord(array $source, $combination)
    {
        $isVariant = is_array($combination);
        $combination = $isVariant ? $combination : array();
        $productId = (int) $source['product_id'];
        $combinationId = $isVariant ? (int) $combination['combination_id'] : 0;
        $price = $isVariant ? $combination['price'] : $source['price'];
        $salePrice = $isVariant ? $combination['sale_price'] : $source['sale_price'];

        $id = $isVariant ? 'variant-' . $combinationId : 'product-' . $productId;
        $quantity = isset($combination['quantity']) ? (int) $combination['quantity'] : (int) $source['quantity'];
        $available = isset($combination['available']) ? (bool) $combination['available'] : (bool) $source['available'];
        $image = isset($combination['image']) && $combination['image'] ? $combination['image'] : $source['image'];
        $attributes = isset($combination['attributes']) ? $this->cleanMap($combination['attributes']) : array();
        $features = $this->cleanMap($source['features']);

        $record = array(
            'id' => $id,
            'record_type' => $isVariant ? 'combination' : 'product',
            'product_id' => $productId,
            'combination_id' => $combinationId ?: null,
            '__parent_id' => $isVariant ? 'product-' . $productId : null,
            '__parent_title' => $isVariant ? (string) $source['name'] : null,
            '__parent_image' => $isVariant ? $source['image'] : null,
            '__variant_position' => $isVariant ? (int) $combination['position'] : null,
            '__standard_options' => $isVariant ? array_keys($attributes) : array(),
            '__selected_options' => $attributes,
            '__swatch_options' => $isVariant && isset($combination['swatches']) ? $combination['swatches'] : array(),
            'sku' => $this->fallback($combination, $source, 'reference'),
            'ean13' => $this->fallback($combination, $source, 'ean13'),
            'upc' => $this->fallback($combination, $source, 'upc'),
            'name' => (string) $source['name'],
            'description_short' => (string) $source['description_short'],
            'description' => (string) $source['description'],
            'url' => (string) $source['url'],
            'thumbnail_url' => $image,
            'additional_images' => array_values($source['additional_images']),
            'price' => $salePrice === null ? null : (float) $salePrice,
            'retail_price' => $price === null ? null : (float) $price,
            'discount_amount' => $price !== null && $salePrice !== null ? max(0.0, (float) $price - (float) $salePrice) : null,
            'on_sale' => $price !== null && $salePrice !== null && (float) $salePrice < (float) $price,
            'currency' => (string) $source['currency'],
            'quantity' => $quantity,
            '__in_stock' => $available,
            '__in_stock_pct' => $available ? 100 : 0,
            'active' => (bool) $source['active'],
            'available_for_order' => (bool) $source['available_for_order'],
            'brand' => $source['brand'] ?: null,
            'category_ids' => array_values($source['category_ids']),
            'category_names' => array_values($source['category_names']),
            'category_paths' => array_values($source['category_paths']),
            'default_category_id' => $source['default_category_id'],
            'default_category' => $source['default_category'],
            'searchable_keywords' => array_values(array_unique($source['searchable_keywords'])),
            'features' => $features,
            'attributes' => $attributes,
            'date_created' => (string) $source['date_created'],
            'date_updated' => (string) $source['date_updated'],
            'language' => $source['language'],
            'language_id' => (int) $source['language_id'],
            'shop_id' => (int) $source['shop_id'],
        );

        foreach ($features as $name => $value) {
            $record[FeedSchema::featureField($name)] = $value;
        }
        foreach ($attributes as $name => $value) {
            $record[FeedSchema::attributeField($name)] = $value;
        }

        return $record;
    }

    private function fallback(array $preferred, array $source, $key)
    {
        return isset($preferred[$key]) && $preferred[$key] !== '' ? (string) $preferred[$key] : (string) $source[$key];
    }

    private function cleanMap(array $values)
    {
        $result = array();
        foreach ($values as $name => $value) {
            $name = trim(preg_replace('/\s+/u', ' ', (string) $name));
            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
            if ($name !== '' && $value !== '') {
                $result[$name] = $value;
            }
        }
        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);
        return $result;
    }
}
