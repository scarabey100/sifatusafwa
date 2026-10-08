<?php

namespace AthosFeed\Feed;

use AthosFeed\Contract\ProductDataProviderInterface;
use AthosFeed\Logger\FeedLogger;
use AthosFeed\Normalizer\ProductNormalizer;

class ProductFeedBuilder
{
    private $provider;
    private $normalizer;
    private $logger;

    public function __construct(ProductDataProviderInterface $provider, ProductNormalizer $normalizer, FeedLogger $logger)
    {
        $this->provider = $provider;
        $this->normalizer = $normalizer;
        $this->logger = $logger;
    }

    public function buildBatch($lastProductId, $limit, $languageId, $shopId, $includeInactive = true)
    {
        $limit = max(1, min(1000, (int) $limit));
        $ids = $this->provider->getProductIdsAfter((int) $lastProductId, $limit, (int) $shopId, (bool) $includeInactive);
        $records = array();
        $cursor = (int) $lastProductId;
        $errors = 0;
        $skipped = 0;

        foreach ($ids as $productId) {
            $cursor = max($cursor, (int) $productId);
            try {
                $source = $this->provider->getProductData($productId, $languageId, $shopId);
                $records[] = $this->normalizer->normalizeProduct($source);
                foreach ($source['combinations'] as $combination) {
                    try {
                        $records[] = $this->normalizer->normalizeCombination($source, $combination);
                    } catch (\Throwable $exception) {
                        ++$errors;
                        ++$skipped;
                        $this->logger->error('Combination {combination_id} of product {product_id} skipped: {error}', array(
                            'combination_id' => isset($combination['combination_id']) ? $combination['combination_id'] : 0,
                            'product_id' => $productId,
                            'error' => $exception->getMessage(),
                        ));
                    }
                }
            } catch (\Throwable $exception) {
                ++$errors;
                ++$skipped;
                $this->logger->error('Product {product_id} skipped: {error}', array('product_id' => $productId, 'error' => $exception->getMessage()));
            }
        }

        $this->logger->info('Built {records} records from {products} products; cursor={cursor}', array(
            'records' => count($records), 'products' => count($ids), 'cursor' => $cursor,
        ));

        return array('records' => $records, 'next_cursor' => $cursor, 'has_more' => count($ids) === $limit,
            'source_products' => count($ids), 'skipped' => $skipped, 'errors' => $errors);
    }
}
