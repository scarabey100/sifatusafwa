<?php

namespace AthosFeed\Export;

use AthosFeed\Configuration\ModuleConfiguration;
use AthosFeed\Feed\ProductFeedBuilder;
use AthosFeed\Logger\FeedLogger;
use AthosFeed\Normalizer\ProductNormalizer;
use AthosFeed\Provider\PrestaShopProductDataProvider;
use AthosFeed\Serializer\JsonLinesSerializer;
use AthosFeed\Validation\FeedValidator;

class ExportManager
{
    private $moduleDirectory;
    private $logger;

    public function __construct($moduleDirectory, FeedLogger $logger = null)
    {
        $this->moduleDirectory = rtrim($moduleDirectory, '/\\');
        $this->logger = $logger ?: new FeedLogger();
    }

    public function export($shopId, $languageId, array $options = array())
    {
        $started = microtime(true);
        $executionId = gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
        $logger = $this->logger->withContext(array('execution_id' => $executionId, 'shop' => $shopId, 'language' => $languageId, 'stage' => 'export'));
        $directory = $this->storageDirectory();
        $this->ensureDirectory($directory);
        $lock = fopen($directory . '/export-' . (int) $shopId . '-' . (int) $languageId . '.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            throw new \RuntimeException('Another export is already running for this shop and language');
        }

        $final = $this->feedPath($shopId, $languageId);
        $temporary = $directory . '/.' . basename($final) . '.' . $executionId . '.tmp';
        $handle = null;
        $pricingCart = null;
        try {
            $pricingCart = $this->configureContext($shopId, $languageId, $options);
            $handle = fopen($temporary, 'xb');
            if (!$handle) {
                throw new \RuntimeException('Cannot create temporary feed file');
            }
            $serializer = new JsonLinesSerializer();
            $validator = new FeedValidator();
            $builder = new ProductFeedBuilder(new PrestaShopProductDataProvider(), new ProductNormalizer(), $logger);
            $cursor = 0;
            $statistics = array('execution_id' => $executionId, 'status' => 'running', 'cursor' => 0,
                'products' => 0, 'variants' => 0, 'skipped' => 0, 'errors' => 0);
            $batchSize = isset($options['batch_size']) ? (int) $options['batch_size'] : (int) ModuleConfiguration::get(ModuleConfiguration::BATCH_SIZE, $shopId, 100);
            $includeInactive = isset($options['include_inactive']) ? (bool) $options['include_inactive'] : (bool) ModuleConfiguration::get(ModuleConfiguration::INCLUDE_INACTIVE, $shopId, false);
            do {
                $batch = $builder->buildBatch($cursor, $batchSize, $languageId, $shopId, $includeInactive);
                foreach ($batch['records'] as $record) {
                    if (!$this->includeRecord($record, $shopId)) {
                        continue;
                    }
                    $recordIds = array();
                    $recordErrors = $validator->validateRecord($record, 1, $recordIds);
                    if ($recordErrors) {
                        ++$statistics['skipped'];
                        ++$statistics['errors'];
                        $logger->error('Record {record_id} skipped during validation: {error}', array(
                            'record_id' => isset($record['id']) ? $record['id'] : 'unknown',
                            'error' => implode('; ', $recordErrors),
                        ));
                        continue;
                    }
                    $line = $serializer->serializeRecord($record) . "\n";
                    if (fwrite($handle, $line) !== strlen($line)) {
                        throw new \RuntimeException('Cannot write complete feed record');
                    }
                    if ($record['record_type'] === 'combination') {
                        ++$statistics['variants'];
                    } else {
                        ++$statistics['products'];
                    }
                }
                $cursor = $batch['next_cursor'];
                $statistics['cursor'] = $cursor;
                $statistics['skipped'] += $batch['skipped'];
                $statistics['errors'] += $batch['errors'];
            } while ($batch['has_more']);
            fflush($handle);
            fclose($handle);
            $handle = null;

            $minimumRecords = isset($options['minimum_records'])
                ? (int) $options['minimum_records']
                : (int) ModuleConfiguration::get(ModuleConfiguration::MINIMUM_RECORDS, $shopId, 10);
            $validator->validateFile($temporary, max(1, $minimumRecords));
            if (!rename($temporary, $final)) {
                throw new \RuntimeException('Atomic feed publication failed');
            }
            $statistics['status'] = 'success';
            $statistics['duration_seconds'] = round(microtime(true) - $started, 3);
            $statistics['file_size'] = filesize($final);
            $statistics['peak_memory_bytes'] = memory_get_peak_usage(true);
            $statistics['finished_at'] = gmdate('c');
            ModuleConfiguration::set(ModuleConfiguration::STATUS, json_encode($statistics), $shopId);
            $logger->info('Published feed with {products} products and {variants} variants', $statistics);
            return $statistics;
        } catch (\Throwable $exception) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (is_file($temporary)) {
                unlink($temporary);
            }
            $logger->error('Export failed: {error}', array('error' => $exception->getMessage()));
            throw $exception;
        } finally {
            $this->removePricingCart($pricingCart, $logger);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function feedPath($shopId, $languageId)
    {
        return $this->storageDirectory() . '/feed-' . (int) $shopId . '-' . (int) $languageId . '.ndjson';
    }

    public function status($shopId)
    {
        $status = json_decode((string) ModuleConfiguration::get(ModuleConfiguration::STATUS, $shopId, '{}'), true);
        return is_array($status) ? $status : array();
    }

    private function storageDirectory()
    {
        return $this->moduleDirectory . '/var';
    }

    private function ensureDirectory($directory)
    {
        if (!is_dir($directory) && !mkdir($directory, 0750, true)) {
            throw new \RuntimeException('Cannot create feed storage directory');
        }
    }

    private function includeRecord(array $record, $shopId)
    {
        return (bool) ModuleConfiguration::get(ModuleConfiguration::INCLUDE_OUT_OF_STOCK, $shopId, true) || !empty($record['__in_stock']);
    }

    private function configureContext($shopId, $languageId, array $options)
    {
        $context = \Context::getContext();
        $context->shop = new \Shop((int) $shopId);
        $context->language = new \Language((int) $languageId);
        $currencyId = isset($options['currency_id']) ? $options['currency_id'] : ModuleConfiguration::get(ModuleConfiguration::CURRENCY_ID, $shopId, \Configuration::get('PS_CURRENCY_DEFAULT'));
        $countryId = isset($options['country_id']) ? $options['country_id'] : ModuleConfiguration::get(ModuleConfiguration::COUNTRY_ID, $shopId, \Configuration::get('PS_COUNTRY_DEFAULT'));
        $groupId = isset($options['group_id']) ? $options['group_id'] : ModuleConfiguration::get(ModuleConfiguration::GROUP_ID, $shopId, \Configuration::get('PS_UNIDENTIFIED_GROUP'));
        $context->currency = new \Currency((int) $currencyId);
        $context->country = new \Country((int) $countryId, (int) $languageId);
        if (!$context->customer) {
            $context->customer = new \Customer();
        }
        $context->customer->id_default_group = (int) $groupId;
        $context->customer->id = 0;

        // PrestaShop 8 requires a real cart ID when prices are calculated
        // outside a Back Office employee context. Persist one empty anonymous
        // cart for this export and remove it in export()'s finally block.
        $context->cart = new \Cart();
        $context->cart->id_shop = (int) $shopId;
        $context->cart->id_shop_group = (int) $context->shop->id_shop_group;
        $context->cart->id_lang = (int) $languageId;
        $context->cart->id_currency = (int) $currencyId;
        $context->cart->id_customer = 0;
        $context->cart->id_guest = 0;
        $context->cart->id_address_delivery = 0;
        $context->cart->id_address_invoice = 0;
        $context->cart->secure_key = md5(uniqid((string) mt_rand(), true));
        if (!$context->cart->add() || !(int) $context->cart->id) {
            throw new \RuntimeException('Unable to create the temporary pricing cart');
        }

        return $context->cart;
    }

    private function removePricingCart($cart, FeedLogger $logger)
    {
        if (!$cart instanceof \Cart || !(int) $cart->id) {
            return;
        }
        try {
            if (!$cart->delete()) {
                $logger->error('Temporary pricing cart {cart_id} could not be removed', array('cart_id' => (int) $cart->id));
            }
        } catch (\Throwable $exception) {
            $logger->error('Temporary pricing cart {cart_id} cleanup failed: {error}', array(
                'cart_id' => (int) $cart->id,
                'error' => $exception->getMessage(),
            ));
        }
    }
}
