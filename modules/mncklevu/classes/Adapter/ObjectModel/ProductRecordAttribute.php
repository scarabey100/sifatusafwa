<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\ObjectModel;

use Context;
use Db;
use DbQuery;
use Validate;

class ProductRecordAttribute extends AbstractObjectModel
{
    /**
     * @var int
     */
    const SOURCE_TYPE_ATTRIBUTE_GROUP = 1;

    /**
     * @var int
     */
    const SOURCE_TYPE_FEATURE = 2;

    /**
     * @var int
     */
    const SOURCE_TYPE_MANUFACTURER = 3;

    /**
     * @var int
     */
    public $id_shop = null;

    /**
     * @var int
     */
    public $source_type = null;

    /**
     * @var int
     */
    public $source_id = null;

    /**
     * @var int
     */
    public $filterable = null;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mncklevu_product_record_attribute',
        'primary' => 'id_mncklevu_product_record_attribute',
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'source_type' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'source_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'filterable' => [
                'type' => self::TYPE_BOOL,
                'required' => true,
            ],
        ]
    ];

    /**
     * @param int $shopId
     *
     * @return array
     */
    public static function getProductRecordAttributes($shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('*')
            ->from(ProductRecordAttribute::$definition['table'], 'a')
            ->where('a.id_shop = ' . (int)$shopId);

        $result = Db::getInstance()->executeS($query->build());
        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @param int $sourceType
     * @param int $sourceId
     * @param bool $filterable
     * @param int $shopId
     *
     * @return bool
     */
    public static function checkExistence($sourceType, $sourceId, $filterable, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.' . ProductRecordAttribute::$definition['primary'])
            ->from(ProductRecordAttribute::$definition['table'], 'a')
            ->where('a.id_shop = ' . (int)$shopId)
            ->where('a.source_type = ' . (int)$sourceType)
            ->where('a.source_id = ' . (int)$sourceId)
            ->where('a.filterable = ' . (int)((bool)$filterable));

        return (bool)Db::getInstance()->getValue($query->build());
    }

    /**
     * @param int $shopId
     *
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->id_shop = (int)$shopId;

        return $this;
    }

    /**
     * @param int $sourceType
     *
     * @return $this
     */
    public function setSourceType($sourceType)
    {
        $this->source_type = (int)$sourceType;

        return $this;
    }

    /**
     * @param int $sourceId
     *
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->source_id = (int)$sourceId;

        return $this;
    }

    /**
     * @param bool $filterable
     *
     * @return $this
     */
    public function setFilterable($filterable)
    {
        $this->filterable = (int)((bool)$filterable);

        return $this;
    }
}
