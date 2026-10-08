<?php

namespace AthosFeed\Diagnostics;

use AthosFeed\Configuration\ModuleConfiguration;

class CatalogDiagnostics
{
    public function collect($shopId, $languageId)
    {
        $shopId = (int) $shopId;
        $languageId = (int) $languageId;
        $prefix = _DB_PREFIX_;

        return array(
            'module_version' => ModuleConfiguration::MODULE_VERSION,
            'shop_id' => $shopId,
            'shop_name' => (string) \Db::getInstance()->getValue(
                'SELECT `name` FROM `' . $prefix . 'shop` WHERE `id_shop` = ' . $shopId
            ),
            'language_id' => $languageId,
            'language_iso_code' => (string) \Db::getInstance()->getValue(
                'SELECT `iso_code` FROM `' . $prefix . 'lang` WHERE `id_lang` = ' . $languageId
            ),
            'products_all_shops' => $this->count('SELECT COUNT(*) FROM `' . $prefix . 'product`'),
            'products_in_shop' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_shop` WHERE `id_shop` = ' . $shopId
            ),
            'active_products_in_shop' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_shop` WHERE `id_shop` = ' . $shopId . ' AND `active` = 1'
            ),
            'inactive_products_in_shop' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_shop` WHERE `id_shop` = ' . $shopId . ' AND `active` = 0'
            ),
            'localized_products' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_lang` WHERE `id_shop` = ' . $shopId
                . ' AND `id_lang` = ' . $languageId
            ),
            'products_without_reference' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product` p INNER JOIN `' . $prefix . 'product_shop` ps'
                . ' ON ps.`id_product` = p.`id_product` AND ps.`id_shop` = ' . $shopId
                . " WHERE ps.`active` = 1 AND (p.`reference` IS NULL OR TRIM(p.`reference`) = '')"
            ),
            'products_without_cover_image' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_shop` ps LEFT JOIN `' . $prefix . 'image_shop` i'
                . ' ON i.`id_product` = ps.`id_product` AND i.`id_shop` = ps.`id_shop` AND i.`cover` = 1'
                . ' WHERE ps.`id_shop` = ' . $shopId . ' AND ps.`active` = 1 AND i.`id_image` IS NULL'
            ),
            'combinations_in_shop' => $this->count(
                'SELECT COUNT(*) FROM `' . $prefix . 'product_attribute_shop` WHERE `id_shop` = ' . $shopId
            ),
            'include_inactive' => (bool) ModuleConfiguration::get(ModuleConfiguration::INCLUDE_INACTIVE, $shopId, false),
            'include_out_of_stock' => (bool) ModuleConfiguration::get(ModuleConfiguration::INCLUDE_OUT_OF_STOCK, $shopId, true),
            'batch_size' => (int) ModuleConfiguration::get(ModuleConfiguration::BATCH_SIZE, $shopId, 100),
            'minimum_records' => (int) ModuleConfiguration::get(ModuleConfiguration::MINIMUM_RECORDS, $shopId, 10),
        );
    }

    private function count($query)
    {
        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
