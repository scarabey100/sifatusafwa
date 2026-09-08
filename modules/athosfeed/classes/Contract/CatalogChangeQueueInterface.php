<?php

namespace AthosFeed\Contract;

interface CatalogChangeQueueInterface
{
    /**
     * Idempotently enqueue a future product upsert/deactivation/delete operation.
     */
    public function enqueue($operation, $productId, $shopId, $languageId);
}
