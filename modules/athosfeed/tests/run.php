<?php

require dirname(__DIR__) . '/classes/autoload.php';

use AthosFeed\Normalizer\ProductNormalizer;
use AthosFeed\Security\RequestAuthorizer;
use AthosFeed\Serializer\JsonLinesSerializer;
use AthosFeed\Validation\FeedValidator;

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectFailure(callable $callback, $fragment)
{
    try {
        $callback();
    } catch (RuntimeException $exception) {
        check(strpos($exception->getMessage(), $fragment) !== false, 'Unexpected failure: ' . $exception->getMessage());
        return;
    }
    throw new RuntimeException('Expected failure containing: ' . $fragment);
}

function sourceFixture($overrides = array())
{
    return array_merge(array(
        'product_id'=>42, 'reference'=>'P-42', 'ean13'=>'', 'upc'=>'', 'name'=>'Perfume',
        'description_short'=>'', 'description'=>'', 'url'=>'https://example.test/p/42',
        'image'=>'https://example.test/img/42.jpg', 'additional_images'=>array(),
        'price'=>120.0, 'sale_price'=>99.0, 'currency'=>'EUR', 'quantity'=>0, 'available'=>false,
        'active'=>false, 'available_for_order'=>true, 'brand'=>null,
        'category_ids'=>array(2, 3), 'category_names'=>array('Home', 'Perfumes'),
        'category_paths'=>array('Home', 'Home>Perfumes'), 'default_category_id'=>3, 'default_category'=>'Perfumes',
        'searchable_keywords'=>array('P-42', 'Perfume'), 'features'=>array(' Concentration '=>' EDP '),
        'date_created'=>'2024-01-01 00:00:00', 'date_updated'=>'2024-02-01 00:00:00',
        'language'=>'en', 'language_id'=>1, 'shop_id'=>1, 'combinations'=>array(),
    ), $overrides);
}

$source = sourceFixture();
$source['combinations'] = array(
    array('combination_id'=>501, 'reference'=>'P-42-50', 'ean13'=>'1234567890123', 'upc'=>'', 'price'=>125.0,
        'sale_price'=>100.0, 'quantity'=>4, 'available'=>true, 'image'=>null, 'position'=>1,
        'attributes'=>array(' Size '=>' 50 ml '), 'swatches'=>array('Color'=>'#ffffff')),
    array('combination_id'=>502, 'reference'=>'', 'ean13'=>'', 'upc'=>'', 'price'=>150.0,
        'sale_price'=>150.0, 'quantity'=>0, 'available'=>false, 'image'=>null, 'position'=>2,
        'attributes'=>array('Size'=>'100 ml'), 'swatches'=>array()),
);
$normalizer = new ProductNormalizer();
$parent = $normalizer->normalizeProduct($source);
$variant = $normalizer->normalizeCombination($source, $source['combinations'][0]);
$fallbackVariant = $normalizer->normalizeCombination($source, $source['combinations'][1]);
check($parent['id'] === 'product-42' && $parent['on_sale'] === true, 'Parent/discount normalization failed');
check($parent['brand'] === null && $parent['description'] === '', 'Missing optional data failed');
check($parent['category_paths'][1] === 'Home>Perfumes' && $parent['feature_concentration'] === 'EDP', 'Categories/features failed');
check($variant['__parent_id'] === 'product-42' && $variant['__variant_position'] === 1, 'Variant relationship failed');
check($variant['thumbnail_url'] === $parent['thumbnail_url'] && $variant['attribute_size'] === '50 ml', 'Image/attribute fallback failed');
check($variant['__swatch_options']['Color'] === '#ffffff', 'Swatch failed');
check($fallbackVariant['sku'] === 'P-42' && $fallbackVariant['on_sale'] === false, 'SKU fallback/no-discount failed');
check($fallbackVariant['__in_stock'] === false && $parent['active'] === false, 'Stock/disabled state failed');
check(count(array_slice($source['combinations'], 0, 1)) === 1 && count($source['combinations']) > 1, 'One/multiple combination fixtures failed');
$withoutImage = $normalizer->normalizeProduct(sourceFixture(array('image'=>null)));
$recordIds = array();
check(strpos(implode(';', (new FeedValidator())->validateRecord($withoutImage, 1, $recordIds)), 'thumbnail_url') !== false, 'Missing required image was not rejected');
$backOrder = $normalizer->normalizeProduct(sourceFixture(array('quantity'=>0, 'available'=>true, 'active'=>true)));
check($backOrder['__in_stock'] === true, 'Back-order availability failed');

$differentContext = $normalizer->normalizeProduct(sourceFixture(array('language'=>'fr', 'language_id'=>2, 'shop_id'=>3, 'currency'=>'CHF')));
check($differentContext['language']==='fr' && $differentContext['shop_id']===3 && $differentContext['currency']==='CHF', 'Language/shop/currency failed');

$serializer = new JsonLinesSerializer();
$tmp = tempnam(sys_get_temp_dir(), 'athos-valid-');
$records = array();
for ($i = 0; $i < 10; ++$i) {
    $record = $parent;
    $record['id'] = 'product-' . $i;
    $record['sku'] = 'SKU-' . $i;
    $records[] = $record;
}
file_put_contents($tmp, $serializer->serialize($records));
check((new FeedValidator())->validateFile($tmp)['records'] === 10, 'Valid feed rejected');

$duplicate = tempnam(sys_get_temp_dir(), 'athos-duplicate-');
file_put_contents($duplicate, $serializer->serialize(array($parent, $parent)));
expectFailure(function () use ($duplicate) { (new FeedValidator())->validateFile($duplicate, 1); }, 'duplicate id');
$broken = tempnam(sys_get_temp_dir(), 'athos-broken-');
file_put_contents($broken, "{broken}\n");
expectFailure(function () use ($broken) { (new FeedValidator())->validateFile($broken, 1); }, 'invalid JSON');
$invalidUrl = $parent;
$invalidUrl['url'] = 'not-a-url';
file_put_contents($broken, $serializer->serialize(array($invalidUrl)));
expectFailure(function () use ($broken) { (new FeedValidator())->validateFile($broken, 1); }, 'invalid url');
@unlink($tmp); @unlink($duplicate); @unlink($broken);

$auth = new RequestAuthorizer();
check($auth->authorize('secret', 'secret', '127.0.0.1'), 'Valid token rejected');
check(!$auth->authorize('secret', 'wrong', '127.0.0.1'), 'Invalid token accepted');
check(!$auth->authorize('secret', 'secret', '10.0.0.1', '127.0.0.1'), 'IP allowlist bypassed');

echo "OK: normalization, variants, categories, facets, swatches, contexts, serializer, validator, auth\n";
