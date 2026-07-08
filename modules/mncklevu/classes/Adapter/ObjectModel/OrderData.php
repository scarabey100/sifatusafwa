<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\ObjectModel;

use Db;
use DbQuery;

class OrderData extends AbstractObjectModel
{
    /**
     * @var int
     */
    public $id_order_detail = null;

    /**
     * @var string
     */
    public $klevu_product_id = null;

    /**
     * @var string
     */
    public $klevu_product_group_id = null;

    /**
     * @var string
     */
    public $klevu_product_variant_id = null;

    /**
     * @var int
     */
    public $klevu_unit = null;

    /**
     * @var float
     */
    public $klevu_sale_price = null;

    /**
     * @var string
     */
    public $klevu_currency = null;

    /**
     * @var string
     */
    public $klevu_shopper_ip = null;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mncklevu_order_data',
        'primary' => 'id_mncklevu_order_data',
        'fields' => [
            'id_order_detail' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ],
            'klevu_product_id' => [
                'type' => self::TYPE_STRING,
                'size' => 16,
                'required' => true
            ],
            'klevu_product_group_id' => [
                'type' => self::TYPE_STRING,
                'size' => 16,
                'required' => true
            ],
            'klevu_product_variant_id' => [
                'type' => self::TYPE_STRING,
                'size' => 16,
                'required' => true
            ],
            'klevu_unit' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true
            ],
            'klevu_sale_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true
            ],
            'klevu_currency' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isLanguageIsoCode',
                'size' => 3,
                'required' => true
            ],
            'klevu_shopper_ip' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isIp2Long',
                'size' => 16,
                'required' => true
            ]
        ]
    ];

    /**
     * @param int $orderDetailId
     *
     * @return int
     */
    public static function getIdByOrderDetailId($orderDetailId)
    {
        $query = (new DbQuery())
            ->select('a.' . self::$definition['primary'])
            ->from(self::$definition['table'], 'a')
            ->where('a.id_order_detail = ' . (int)$orderDetailId)
            ->build();

        return (int)Db::getInstance()->getValue($query);
    }

    /**
     * @param int $orderDetailId
     *
     * @return $this
     */
    public function setOrderDetailId($orderDetailId)
    {
        $this->id_order_detail = (int)$orderDetailId;

        return $this;
    }

    /**
     * @param string $klevuProductId
     *
     * @return $this
     */
    public function setKlevuProductId($klevuProductId)
    {
        $this->klevu_product_id = (string)$klevuProductId;

        return $this;
    }

    /**
     * @param string $klevuProductGroupId
     *
     * @return $this
     */
    public function setKlevuProductGroupId($klevuProductGroupId)
    {
        $this->klevu_product_group_id = (string)$klevuProductGroupId;

        return $this;
    }

    /**
     * @param string $klevuProductVariantId
     *
     * @return $this
     */
    public function setKlevuProductVariantId($klevuProductVariantId)
    {
        $this->klevu_product_variant_id = (string)$klevuProductVariantId;

        return $this;
    }

    /**
     * @param int $klevuUnit
     *
     * @return $this
     */
    public function setKlevuUnit($klevuUnit)
    {
        $this->klevu_unit = (int)$klevuUnit;

        return $this;
    }

    /**
     * @param float $klevuSalePrice
     *
     * @return $this
     */
    public function setKlevuSalePrice($klevuSalePrice)
    {
        $this->klevu_sale_price = (float)$klevuSalePrice;

        return $this;
    }

    /**
     * @param string $klevuCurrency
     *
     * @return $this
     */
    public function setKlevuCurrency($klevuCurrency)
    {
        $this->klevu_currency = (string)$klevuCurrency;

        return $this;
    }

    /**
     * @param string $klevuShopperIp
     *
     * @return $this
     */
    public function setKlevuShopperIp($klevuShopperIp)
    {
        $this->klevu_shopper_ip = (string)$klevuShopperIp;

        return $this;
    }
}
