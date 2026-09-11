<?php

require dirname(__DIR__) . '/classes/autoload.php';

use AthosFeed\Normalizer\ProductNormalizer;
use AthosFeed\Serializer\JsonLinesSerializer;

$source = array('product_id'=>1, 'reference'=>'SKU', 'ean13'=>'', 'upc'=>'', 'name'=>'Product',
    'description_short'=>'Short', 'description'=>'Description', 'url'=>'https://example.test/product',
    'image'=>'https://example.test/image.jpg', 'additional_images'=>array(), 'price'=>10.0, 'sale_price'=>9.0,
    'currency'=>'EUR', 'quantity'=>1, 'available'=>true, 'active'=>true, 'available_for_order'=>true,
    'brand'=>'Brand', 'category_ids'=>array(1), 'category_names'=>array('Catalog'),
    'category_paths'=>array('Catalog'), 'default_category_id'=>1, 'default_category'=>'Catalog',
    'searchable_keywords'=>array('SKU'), 'features'=>array('Size'=>'M'), 'date_created'=>'2024-01-01',
    'date_updated'=>'2024-01-01', 'language'=>'en', 'language_id'=>1, 'shop_id'=>1, 'combinations'=>array());
$normalizer = new ProductNormalizer();
$serializer = new JsonLinesSerializer();
$start = microtime(true);
$bytes = 0;
for ($i = 1; $i <= 10000; ++$i) {
    $source['product_id'] = $i;
    $bytes += strlen($serializer->serializeRecord($normalizer->normalizeProduct($source))) + 1;
}
echo json_encode(array('records'=>10000, 'seconds'=>round(microtime(true)-$start, 4),
        'bytes'=>$bytes, 'peak_memory_bytes'=>memory_get_peak_usage(true))) . "\n";
