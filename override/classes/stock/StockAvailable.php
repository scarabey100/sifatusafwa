<?php
class StockAvailable extends StockAvailableCore
{
    /**
     * @param int $id_product
     * @param string $location
     * @param int $id_shop Optional
     * @param int $id_product_attribute Optional
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     */
    /*
    * module: bmsorderpreparation
    * date: 2025-08-12 13:41:44
    * version: 1.0.19
    */
    public static function setLocation($id_product, $location, $id_shop = null, $id_product_attribute = 0)
    {
        if (
            false === Validate::isUnsignedId($id_product)
            || (((false === Validate::isUnsignedId($id_shop)) && (null !== $id_shop)))
            || (false === Validate::isUnsignedId($id_product_attribute))
            || (false === Validate::isString($location))
        ) {
            $serializedInputData = [
                'id_product' => $id_product,
                'id_shop' => $id_shop,
                'id_product_attribute' => $id_product_attribute,
                'location' => $location,
            ];
            throw new \InvalidArgumentException(sprintf('Could not update location as input data is not valid: %s', json_encode($serializedInputData)));
        }
        $existing_id = StockAvailable::getStockAvailableIdByProductId($id_product, $id_product_attribute, $id_shop);
        if ($existing_id > 0) {
            Db::getInstance()->update(
                'stock_available',
                ['location' => pSQL($location)],
                'id_product = ' . (int) $id_product .
                ' AND id_product_attribute = ' . (int) $id_product_attribute .
                StockAvailable::addSqlShopRestriction(null, $id_shop)
            );
        } else {
            $params = [
                'location' => pSQL($location),
                'id_product' => (int) $id_product,
                'id_product_attribute' => (int) $id_product_attribute,
            ];
            StockAvailable::addSqlShopParams($params, $id_shop);
            Db::getInstance()->insert('stock_available', $params, false, true, Db::ON_DUPLICATE_KEY);
        }
    }
}
