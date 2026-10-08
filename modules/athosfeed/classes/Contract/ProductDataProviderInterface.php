<?php

namespace AthosFeed\Contract;

interface ProductDataProviderInterface
{
    public function getProductIdsAfter($lastProductId, $limit, $shopId, $includeInactive = true);

    public function getProductData($productId, $languageId, $shopId);
}
