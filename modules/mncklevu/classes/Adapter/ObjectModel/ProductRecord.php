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

class ProductRecord extends AbstractObjectModel
{
    /**
     * @var string
     */
    public $record_id = null;

    /**
     * @var int
     */
    public $id_product = null;

    /**
     * @var int
     */
    public $id_product_attribute = null;

    /**
     * @var int
     */
    public $id_lang = null;

    /**
     * @var int
     */
    public $id_shop = null;

    /**
     * @var int
     */
    public $valid = null;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mncklevu_product_record',
        'primary' => 'id_mncklevu_product_record',
        'fields' => [
            'record_id' => [
                'type' => self::TYPE_STRING,
                'size' => 16,
                'required' => true,
            ],
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_product_attribute' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'valid' => [
                'type' => self::TYPE_BOOL,
                'required' => true,
            ],
        ]
    ];

    /**
     * @param int $languageId
     * @param int $productId
     * @param int $shopId
     *
     * @return bool
     */
    public static function setProductsRecordsAsNotValid($languageId, $productId = null, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        return (bool)Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '` a
            SET a.`valid` = 0
            WHERE a.`id_lang` = ' . (int)$languageId . ' AND a.`id_shop` = ' . (int)$shopId .
            ((int)$productId > 0 ? ' AND a.`id_product` = ' . (int)$productId : '') . ';
        ');
    }

    /**
     * @param string $recordId
     * @param int $languageId
     * @param int $shopId
     *
     * @return null|ProductRecord
     */
    public static function getObjectByRecordId($recordId, $languageId, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.' . self::$definition['primary'])
            ->from(self::$definition['table'], 'a')
            ->where('a.record_id = \'' . $recordId . '\'')
            ->where('a.id_lang = ' . (int)$languageId)
            ->where('a.id_shop = ' . (int)$shopId);

        $object = new ProductRecord((int)Db::getInstance()->getValue($query->build()));
        if (!Validate::isLoadedObject($object)) {
            return null;
        }

        return $object;
    }

    /**
     * @param int $languageId
     * @param int $productId
     * @param int $shopId
     *
     * @return array
     */
    public static function getNotValidRecordsIds($languageId, $productId = null, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.record_id')
            ->from(self::$definition['table'], 'a')
            ->where('a.id_lang = ' . (int)$languageId)
            ->where('a.id_shop = ' . (int)$shopId)
            ->where('a.valid = 0');

        if ((int)$productId > 0) {
            $query->where('a.id_product = ' . (int)$productId);
        }

        $result = Db::getInstance()->executeS($query->build());
        if (!is_array($result)) {
            return [];
        }

        return array_map(
            function($item) {
                return $item['record_id'];
            },
            $result
        );
    }

    /**
     * @param string $recordId
     * @param int $languageId
     * @param int $shopId
     *
     * @return bool
     */
    public static function deleteByRecordId($recordId, $languageId, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.' . self::$definition['primary'])
            ->from(self::$definition['table'], 'a')
            ->where('a.record_id = \'' . $recordId . '\'')
            ->where('a.id_lang = ' . (int)$languageId)
            ->where('a.id_shop = ' . (int)$shopId);

        $object = new ProductRecord((int)Db::getInstance()->getValue($query->build()));
        if (!Validate::isLoadedObject($object)) {
            return false;
        }

        return (bool)$object->delete();
    }

    /**
     * @param int $productId
     * @param int $languageId
     * @param int $shopId
     *
     * @return array
     */
    public static function getRecordsIdsByProductId($productId, $languageId, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.record_id')
            ->from(self::$definition['table'], 'a')
            ->where('a.id_product = ' . (int)$productId)
            ->where('a.id_lang = ' . (int)$languageId)
            ->where('a.id_shop = ' . (int)$shopId);

        $result = Db::getInstance()->executeS($query->build());
        if (!is_array($result)) {
            return [];
        }

        return array_map(
            function($item) {
                return $item['record_id'];
            },
            $result
        );
    }

    /**
     * @param int $productId
     * @param int $productAttributeId
     * @param int $languageId
     * @param int $shopId
     *
     * @return string
     */
    public static function getRecordId($productId, $productAttributeId, $languageId, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = (new DbQuery())
            ->select('a.record_id')
            ->from(self::$definition['table'], 'a')
            ->where('a.id_product = ' . (int)$productId)
            ->where('a.id_product_attribute = ' . (int)$productAttributeId)
            ->where('a.id_lang = ' . (int)$languageId)
            ->where('a.id_shop = ' . (int)$shopId);

        return (string)Db::getInstance()->getValue($query->build());
    }

    /**
     * @param string $recordId
     *
     * @return $this
     */
    public function setRecordId($recordId)
    {
        $this->record_id = (string)$recordId;

        return $this;
    }

    /**
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->id_product = (int)$productId;

        return $this;
    }

    /**
     * @param int $productAttributeId
     *
     * @return $this
     */
    public function setProductAttributeId($productAttributeId)
    {
        $this->id_product_attribute = (int)$productAttributeId;

        return $this;
    }

    /**
     * @param int $languageId
     *
     * @return $this
     */
    public function setLanguageId($languageId)
    {
        $this->id_lang = (int)$languageId;

        return $this;
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
     * @param bool $valid
     *
     * @return $this
     */
    public function setValid($valid)
    {
        $this->valid = (int)((bool)$valid);

        return $this;
    }

    /**
     * Get all product IDs that are synced to Klevu but are now inactive
     *
     * @param int $languageId
     * @param int $shopId
     *
     * @return array Array of product IDs
     */
    public static function getInactiveSyncedProductIds($languageId, $shopId = null)
    {
        if (!$shopId) {
            $shopId = Context::getContext()->shop->id;
        }

        $query = '
            SELECT DISTINCT pr.id_product
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` pr
            INNER JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = pr.id_product
            INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = p.id_product AND ps.id_shop = ' . (int)$shopId . '
            WHERE pr.id_lang = ' . (int)$languageId . '
            AND pr.id_shop = ' . (int)$shopId . '
            AND (ps.active = 0 OR ps.visibility NOT IN (\'both\', \'catalog\'))
        ';

        $result = Db::getInstance()->executeS($query);
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
}
