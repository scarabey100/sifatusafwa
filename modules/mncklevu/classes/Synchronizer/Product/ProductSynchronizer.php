<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\Synchronizer\Product;

use Context;
use Currency;
use Db;
use DbQuery;
use Hook;
use Language;
use Manufacturer;
use MncKlevu;
use MncKlevu\Klevu\Client;
use MncKlevu\Klevu\Record;
use MncKlevu\Klevu\RequestData;
use MncKlevu\Klevu\Response;
use MncKlevu\PrestaShop\Adapter\Configuration;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecord;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecordAttribute;
use MncKlevu\PrestaShop\Adapter\Presenter\Product\ProductPresenter;
use MncKlevu\PrestaShop\Adapter\Product\ProductCategoriesRetriever;
use MncKlevu\Synchronizer\Settings;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;
use PrestaShopLogger;
use ProductPresenterFactory;
use Shop;
use TaxConfiguration;
use Tools;
use Validate;

class ProductSynchronizer
{
    /**
     * @var string
     */
    const PRODUCT_IMAGE_NAME = 'home_default';

    /**
     * @var MncKlevu
     */
    protected $module;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductPresenter
     */
    protected $presenter;

    /**
     * @var ProductCategoriesRetriever
     */
    protected $categoriesRetriever;

    /**
     * @var ProductPresentationSettings
     */
    protected $productSettings;

    /**
     * @var Currency
     */
    protected $defaultCurrency;

    /**
     * @var array
     */
    protected static $locale = [];

    /**
     * @param MncKlevu $module
     */
    public function __construct(MncKlevu $module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->presenter = new ProductPresenter();
        $this->categoriesRetriever = new ProductCategoriesRetriever();
        $this->defaultCurrency = Currency::getDefaultCurrency();

        $this->productSettings = (new ProductPresenterFactory(
            $this->context,
            new TaxConfiguration()
        ))->getPresentationSettings();
    }

    /**
     * @param int $languageId
     *
     * @return bool
     */
    public function setProductsRecordsAsNotValid($languageId)
    {
        return ProductRecord::setProductsRecordsAsNotValid($languageId);
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        $query = '
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'product` p ' .
            Shop::addSqlAssociation('product', 'p') . '
            WHERE product_shop.`visibility` IN (\'both\', \'catalog\') AND product_shop.`active` = 1;
        ';

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param int $start
     * @param int $limit
     *
     * @return array
     */
    public function getProductsIds($start, $limit)
    {
        $query = '
            SELECT p.id_product
            FROM `' . _DB_PREFIX_ . 'product` p ' .
            Shop::addSqlAssociation('product', 'p') . '
            WHERE product_shop.`visibility` IN (\'both\', \'catalog\') AND product_shop.`active` = 1
            ORDER BY p.id_product ASC' .
            ((int)$limit > 0 ? ' LIMIT ' . (int)$start . ',' . (int)$limit : '') . ';
        ';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if (!is_array($result)) {
            return [];
        }

        return array_map(
            function($item) {
                return (int)$item['id_product'];
            },
            $result
        );
    }

    /**
     * @return string
     */
    protected function getCurrency()
    {
        return $this->defaultCurrency->iso_code;
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return float
     */
    protected function getPrice(ProductLazyArray $product)
    {
        return Tools::convertPrice($product->regular_price_amount, $this->context->currency, false);
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return float
     */
    protected function getSalePrice(ProductLazyArray $product)
    {
        return Tools::convertPrice($product->price_amount, $this->context->currency, false);
    }

    /**
     * @param string $type
     * @param float $price
     * @param Currency $currency
     *
     * @return string
     */
    protected function getOtherPrice($type, $value, Currency $currency)
    {
        return $type . '_' . $currency->iso_code . ':' . Tools::convertPrice($value, $currency);
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return string
     */
    protected function getOtherPrices(ProductLazyArray $product)
    {
        $currencies = Currency::getCurrencies(true, false);
        if (!is_array($currencies)) {
            $currencies = is_object($currencies) ? [$currencies] : [];
        }

        $price = $this->getPrice($product);
        $salePrice = $this->getSalePrice($product);

        return implode(';', array_map(
            function(Currency $currency) use ($price, $salePrice) {
                return implode(';', [
                    $this->getOtherPrice(Record::PRICE_TYPE_ORIGINAL, $price, $currency),
                    $this->getOtherPrice(Record::PRICE_TYPE_FINAL, $salePrice, $currency),
                ]);
            },
            $currencies
        ));
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return bool
     */
    protected function getInStock(ProductLazyArray $product)
    {
        if ($this->productSettings->stock_management_enabled &&
            !$product->allow_oosp &&
            ($product->quantity <= 0)) {
            return false;
        }

        return (bool)$product->available_for_order;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return array
     */
    protected function getMostSpecificCategories(ProductLazyArray $product, $languageId)
    {
        return $this->categoriesRetriever->getMostSpecificCategories($product->id, $languageId);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string
     */
    protected function getCategory(ProductLazyArray $product, $languageId)
    {
        $categories = [];

        foreach ($this->getMostSpecificCategories($product, $languageId) as $categoryId => $hierarchy) {
            $categories[] = $hierarchy[$categoryId];
        }

        return implode(';', $categories);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string
     */
    protected function getListCategory(ProductLazyArray $product, $languageId)
    {
        $data = array_map(
            function($hierarchy) {
                return implode(';', $hierarchy);
            },
            $this->getMostSpecificCategories($product, $languageId)
        );

        if ($product->id_manufacturer && ($name = Manufacturer::getNameById($product->id_manufacturer))) {
            $data[] = $name;
        }

        return implode(';;', $data);
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return false|string
     */
    protected function getImageUrl(ProductLazyArray $product)
    {
        return isset($product->cover['bySize'][self::PRODUCT_IMAGE_NAME]) ?
            $product->cover['bySize'][self::PRODUCT_IMAGE_NAME]['url'] : false;
    }

    /**
     * @param int $attributeGroupId
     * @param int $languageId
     *
     * @return string
     */
    protected function getAttributeGroupPublicName($attributeGroupId, $languageId)
    {
        $query = (new DbQuery())
            ->select('agl.public_name')
            ->from('attribute_group_lang', 'agl')
            ->where('agl.id_attribute_group = ' . (int)$attributeGroupId)
            ->where('agl.id_lang = ' . (int)$languageId);

        return (string)Db::getInstance()->getValue($query->build());
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function replaceSpecialChars($string)
    {
        return str_replace([',', ':', ';'], ' ', $string);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $filterable
     *
     * @return false|string
     */
    protected function getAttributes(ProductLazyArray $product, $languageId, $filterable)
    {
        if (!is_array($product->attributes)) {
            return false;
        }

        $data = [];

        foreach ($product->attributes as $attribute) {
            if (!ProductRecordAttribute::checkExistence(ProductRecordAttribute::SOURCE_TYPE_ATTRIBUTE_GROUP,
                $attribute['id_attribute_group'], $filterable)) {
                continue;
            }

            $data[] = implode(':', [
                Settings::ID_PREFIX_ATTRIBUTE_GROUP . $attribute['id_attribute_group'],
                $this->replaceSpecialChars($this->getAttributeGroupPublicName(
                    $attribute['id_attribute_group'],
                    $languageId
                )),
                $this->replaceSpecialChars($attribute['name'])
            ]);
        }

        return implode(';', $data);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $filterable
     *
     * @return array
     */
    protected function getGroupedFeatures(ProductLazyArray $product, $languageId, $filterable)
    {
        $result = [];
        $features = is_array($product->features) ? $product->features : [];

        Hook::exec('actionGetKlevuProductFeatureListModifier', [
            'source_object' => $product,
            'feature_list' => &$features,
            'language_id' => $languageId
        ]);

        if (is_array($features)) {
            foreach ($features as $feature) {
                if (!ProductRecordAttribute::checkExistence(ProductRecordAttribute::SOURCE_TYPE_FEATURE,
                    $feature['id_feature'], $filterable)) {
                    continue;
                }

                $id = (int)$feature['id_feature'];

                if (!isset($result[$id])) {
                    $result[$id] = [
                        'id_feature' => $id,
                        'name' => $feature['name'],
                        'values' => [],
                    ];
                }

                $result[$id]['values'][] = $feature['value'];
            }
        }

        return $result;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $filterable
     *
     * @return string
     */
    protected function getFeatures(ProductLazyArray $product, $languageId, $filterable)
    {
        return implode(';', array_map(
            function($feature) {
                return implode(':', [
                    Settings::ID_PREFIX_FEATURE . $feature['id_feature'],
                    $this->replaceSpecialChars($feature['name']),
                    implode(',', array_map(
                        function($value) {
                            return $this->replaceSpecialChars($value);
                        },
                        $feature['values']
                    ))
                ]);
            },
            $this->getGroupedFeatures($product, $languageId, $filterable)
        ));
    }

    /**
     * @param int $languageId
     *
     * @return string|null
     */
    public function getLocale($languageId)
    {
        $languageId = (int)$languageId;

        if (!isset(self::$locale[$languageId])) {
            $language = new Language($languageId);
            self::$locale[$languageId] = Validate::isLoadedObject($language) ? $language->locale : null;
        }

        return self::$locale[$languageId];
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $filterable
     *
     * @return false|string
     */
    protected function getManufacturer(ProductLazyArray $product, $languageId, $filterable)
    {
        if (!$product->id_manufacturer ||
            !ProductRecordAttribute::checkExistence(
                ProductRecordAttribute::SOURCE_TYPE_MANUFACTURER,
                0,
                $filterable
            )) {
            return false;
        }

        $name = Manufacturer::getNameById($product->id_manufacturer);
        if (!$name) {
            return false;
        }

        return implode(':', [
            'manufacturer',
            $this->replaceSpecialChars(
                $this->module->l('Manufacturer', 'ProductSynchronizer', $this->getLocale($languageId))
            ),
            $this->replaceSpecialChars($name)
        ]);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $filterable
     *
     * @return array
     */
    protected function getRecordAttributes(ProductLazyArray $product, $languageId, $filterable)
    {
        $result = [];

        if ($filterable) {
            $result[] = 'klevu_price_placeholder:placeholder:' . $product->id;
        }

        if ($attributes = $this->getAttributes($product, $languageId, $filterable)) {
            $result[] = $attributes;
        }

        if ($features = $this->getFeatures($product, $languageId, $filterable)) {
            $result[] = $features;
        }

        if ($manufacturer = $this->getManufacturer($product, $languageId, $filterable)) {
            $result[] = $manufacturer;
        }

        return $result;
    }

    /**
     * @param string $hookName
     * @param ProductLazyArray $product
     * @param int $languageId
     */
    protected function getHookRecordAttributes($hookName, ProductLazyArray $product, $languageId)
    {
        $result = [];

        $hookAttributes = Hook::exec(
            $hookName,
            [
                'record_type' => Record::TYPE_PRODUCT,
                'source_object' => $product,
                'language_id' => $languageId,
            ],
            null,
            true
        );

        if (is_array($hookAttributes)) {
            foreach ($hookAttributes as $moduleAttributes) {
                if (!is_array($moduleAttributes)) {
                    continue;
                }

                foreach ($moduleAttributes as $attribute) {
                    if (!isset($attribute['id']) ||
                        !isset($attribute['name']) ||
                        !isset($attribute['values']) ||
                        !is_array($attribute['values'])) {
                        continue;
                    }

                    $result[] = implode(':', [
                        $attribute['id'],
                        $this->replaceSpecialChars($attribute['name']),
                        implode(',', array_map(
                            function($value) {
                                return $this->replaceSpecialChars($value);
                            },
                            $attribute['values']
                        ))
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string
     */
    protected function getOther(ProductLazyArray $product, $languageId)
    {
        return implode(';', array_merge(
            $this->getRecordAttributes($product, $languageId, true),
            $this->getHookRecordAttributes(
                MncKlevu::HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER,
                $product,
                $languageId
            )
        ));
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string
     */
    protected function getOtherAttributeToIndex(ProductLazyArray $product, $languageId)
    {
        return implode(';', array_merge(
            $this->getRecordAttributes($product, $languageId, false),
            $this->getHookRecordAttributes(
                MncKlevu::HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER_ATTRIBUTE_TO_INDEX,
                $product,
                $languageId
            )
        ));
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string|false
     */
    protected function getAdditionalDataToReturn(ProductLazyArray $product, $languageId)
    {
        return @json_encode(Hook::exec(
            'actionGetKlevuRecordAdditionalData',
            [
                'record_type' => Record::TYPE_PRODUCT,
                'source_object' => $product,
                'language_id' => $languageId,
            ],
            null,
            true
        ));
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return string
     */
    protected function getCreatedAt(ProductLazyArray $product)
    {
        return (new \DateTime($product->date_add))->format('Y-m-d\TH:i:sP');
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function transformProductId($id)
    {
        return Settings::ID_PREFIX_PRODUCT . (int)$id;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function transformVariantId($id)
    {
        return Settings::ID_PREFIX_PRODUCT_VARIANT . (int)$id;
    }

    /**
     * @param ProductLazyArray $product
     * @param Record $record
     * @param int $languageId
     *
     * @return bool
     */
    protected function saveProductRecord(ProductLazyArray $product, Record $record, $languageId)
    {
        if ($object = ProductRecord::getObjectByRecordId($record->getId(), $languageId)) {
            return $object
                ->setValid(true)
                ->save();
        }

        return (new ProductRecord())
            ->setRecordId($record->getId())
            ->setProductId($product->id)
            ->setProductAttributeId($product->id_product_attribute)
            ->setLanguageId($languageId)
            ->setShopId($this->context->shop->id)
            ->setValid(true)
            ->save();
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     * @param bool $useItemGroupId
     *
     * @return false|Record
     */
    protected function getRecord(ProductLazyArray $product, $languageId, $useItemGroupId = true)
    {
        $record = (new Record())
            ->setType(Record::TYPE_PRODUCT)
            ->setName($product->name)
            ->setSku($product->reference_to_display)
            ->setUrl($product->url)
            ->setCurrency($this->getCurrency())
            ->setPrice($this->getPrice($product))
            ->setSalePrice($this->getSalePrice($product))
            ->setOtherPrices($this->getOtherPrices($product))
            ->setInStock($this->getInStock($product))
            ->setCategory($this->getCategory($product, $languageId))
            ->setListCategory($this->getListCategory($product, $languageId))
            ->setImage($this->getImageUrl($product))
            ->setShortDesc($product->description_short)
            ->setDesc($product->description)
            ->setOther($this->getOther($product, $languageId))
            ->setOtherAttributeToIndex($this->getOtherAttributeToIndex($product, $languageId))
            ->setAdditionalDataToReturn($this->getAdditionalDataToReturn($product, $languageId))
            ->setCreatedAt($this->getCreatedAt($product));

        if (!$product->id_product_attribute) {
            $record->setId($this->transformProductId($product->id));
        } else {
            $record->setId($this->transformVariantId($product->id_product_attribute));

            if ($useItemGroupId) {
                $record->setItemGroupId($this->transformProductId($product->id));
            }
        }

        // die(dump($record));

        if (!$this->saveProductRecord($product, $record, $languageId)) {
            return false;
        }

        return $record;
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return false|Record[]
     */
    protected function getRecords($productId, $languageId)
    {
        $products = $this->presenter->present($productId, $languageId);
        if (!$products) {
            return false;
        }

        $result = [];
        $useItemGroupId = $this->module->getConfiguration()->get(Configuration::KEY_USE_ITEM_GROUP_ID);

        foreach ($products as $product) {
            $record = $this->getRecord($product, $languageId, $useItemGroupId);
            if (!$record) {
                return false;
            }

            $result[] = $record;
        }

        return $result;
    }

    /**
     * @param Response $response
     *
     * @return bool
     */
    protected function validateResponse(Response $response)
    {
        if ($response->getStatus() === Response::STATUS_SUCCESS) {
            return true;
        }

        if ($response->getStatus() === Response::STATUS_ERROR) {
            PrestaShopLogger::addLog('ProductSynchronizer::validateResponse - ' . $response->getError(), 3, null, null, null, true);
        } else {
            PrestaShopLogger::addLog('ProductSynchronizer::validateResponse - Unknown status', 3, null, null, null, true);
        }

        return false;
    }

    /**
     * @param Client $client
     * @param RequestData $data
     *
     * @return bool
     */
    protected function updateRecords(Client $client, RequestData $data)
    {
        // die(dump($data->getXml()));

        return $this->validateResponse($client->updateRecords($data));
    }

    /**
     * @param int $languageId
     * @param int $start
     * @param int $limit
     *
     * @return bool
     */
    public function updateProducts($languageId, $start, $limit)
    {
        $client = $this->module->getClient($languageId);
        $data = (new RequestData())->setSessionId($client->getSessionId());

        foreach ($this->getProductsIds($start, $limit) as $productId) {
            $records = $this->getRecords($productId, $languageId);
            if (!$records) {
                return false;
            }

            $data->addRecords($records);
        }

        return $this->updateRecords($client, $data);
    }

    /**
     * @param int $languageId
     *
     * @return Record[]
     */
    protected function getNotValidRecords($languageId)
    {
        return array_map(
            function($recordId) {
                return (new Record())->setId($recordId);
            },
            ProductRecord::getNotValidRecordsIds($languageId)
        );
    }

    /**
     * @param Client $client
     * @param RequestData $data
     *
     * @return bool
     */
    protected function deleteRecords(Client $client, RequestData $data)
    {
        return $this->validateResponse($client->deleteRecords($data));
    }

    /**
     * @param Record[] $records
     * @param int $languageId
     *
     * @return bool
     */
    protected function deleteProductsRecords(array $records, $languageId)
    {
        foreach ($records as $record) {
            if (!ProductRecord::deleteByRecordId($record->getId(), $languageId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $languageId
     *
     * @return bool
     */
    public function deleteNotValidRecords($languageId)
    {
        $records = $this->getNotValidRecords($languageId);
        if (!$records) {
            return true;
        }

        if (!$this->deleteRecords(
            $client = $this->module->getClient($languageId),
            (new RequestData())
                ->setSessionId($client->getSessionId())
                ->addRecords($records)
        )) {
            return false;
        }

        return $this->deleteProductsRecords($records, $languageId);
    }

    /**
     * @param int $productId
     * @param int $languageId
     * @param Record[] $actualRecords
     *
     * @return Record[]
     */
    protected function getRecordsToDelete($productId, $languageId, array $actualRecords = [])
    {
        $actualRecordsIds = array_map(
            function(Record $record) {
                return $record->getId();
            },
            $actualRecords
        );

        return array_map(
            function($recordId) {
                return (new Record())->setId($recordId);
            },
            array_filter(
                ProductRecord::getRecordsIdsByProductId($productId, $languageId),
                function($recordId) use ($actualRecordsIds) {
                    return !in_array($recordId, $actualRecordsIds);
                }
            )
        );
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return bool
     */
    public function updateProduct($productId, $languageId)
    {
        $records = $this->getRecords($productId, $languageId);
        if (!$records) {
            return false;
        }

        $client = $this->module->getClient($languageId);
        $sessionId = $client->getSessionId();

        if (($recordsToDelete = $this->getRecordsToDelete($productId, $languageId, $records)) && (
            !$this->deleteRecords(
                $client,
                (new RequestData())
                    ->setSessionId($sessionId)
                    ->addRecords($recordsToDelete)
            ) ||
            !$this->deleteProductsRecords($recordsToDelete, $languageId)
        )) {
            return false;
        }

        return $this->updateRecords(
            $client,
            (new RequestData())
                ->setSessionId($sessionId)
                ->addRecords($records)
        );
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return bool
     */
    public function deleteProduct($productId, $languageId)
    {
        $records = $this->getRecordsToDelete($productId, $languageId);
        if (!$records) {
            return false;
        }

        if (!$this->deleteRecords(
            $client = $this->module->getClient($languageId),
            (new RequestData())
                ->setSessionId($client->getSessionId())
                ->addRecords($records)
        )) {
            return false;
        }

        return $this->deleteProductsRecords($records, $languageId);
    }

    /**
     * Delete all inactive products that are currently synced to Klevu
     *
     * @param int $languageId
     *
     * @return bool
     */
    public function deleteInactiveProducts($languageId)
    {
        $inactiveProductIds = ProductRecord::getInactiveSyncedProductIds($languageId);
        if (empty($inactiveProductIds)) {
            return true;
        }

        $client = $this->module->getClient($languageId);
        $sessionId = $client->getSessionId();
        $allRecordsToDelete = [];

        // Get all records for inactive products
        foreach ($inactiveProductIds as $productId) {
            $records = $this->getRecordsToDelete($productId, $languageId);
            if ($records) {
                $allRecordsToDelete = array_merge($allRecordsToDelete, $records);
            }
        }

        if (empty($allRecordsToDelete)) {
            return true;
        }

        // Delete records from Klevu in batches if needed
        $batchSize = 100;
        $batches = array_chunk($allRecordsToDelete, $batchSize);

        foreach ($batches as $batch) {
            if (!$this->deleteRecords(
                $client,
                (new RequestData())
                    ->setSessionId($sessionId)
                    ->addRecords($batch)
            )) {
                return false;
            }

            // Delete from local database
            if (!$this->deleteProductsRecords($batch, $languageId)) {
                return false;
            }
        }

        return true;
    }
}
