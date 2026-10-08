<?php

namespace AthosFeed\Schema;

final class FeedSchema
{
    const FORMAT = 'ndjson';
    const REQUIRED = array('id', 'sku', 'name', 'url', 'price', 'thumbnail_url');

    public static function featureField($name)
    {
        return 'feature_' . self::slug($name);
    }

    public static function attributeField($name)
    {
        return 'attribute_' . self::slug($name);
    }

    public static function slug($value)
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
        if (function_exists('iconv')) {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($ascii !== false) {
                $value = $ascii;
            }
        }
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $value));
        return trim($value, '_') ?: 'unknown';
    }
}
