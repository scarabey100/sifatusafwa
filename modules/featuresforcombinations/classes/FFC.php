<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class FFC extends Product
{
    public static function getAllForProductAttribute($id_product, $id_product_attribute)
    {
        if (!Feature::isFeatureActive()) {
            return [];
        }
        $context = Context::getContext();

        $query = 'SELECT ffc.`id_feature`, ffc.`id_feature_value`, fv.`custom`
                FROM `' . _DB_PREFIX_ . 'featuresforcombinations` ffc
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` fv
                    ON (ffc.`id_feature_value` = fv.`id_feature_value`)
                WHERE `id_product` = ' . (int) $id_product . '
                    AND `id_product_attribute` = ' . (int) $id_product_attribute;

        $result = Db::getInstance()->executeS($query);

        $custom_values = [];
        foreach ($result as &$res) {
            $res['predefined_values'] = FeatureValue::getFeatureValuesWithLang(
                $context->language->id,
                $res['id_feature']
            );
            $predefinedValueIds = array_map('intval', array_column($res['predefined_values'], 'id_feature_value'));
            $res['custom'] = (bool) $res['custom'] || !in_array((int) $res['id_feature_value'], $predefinedValueIds, true);

            if ($res['custom']) {
                $customs = Db::getInstance()->executeS(
                    'SELECT `value`, `id_lang`
                    FROM `' . _DB_PREFIX_ . 'feature_value_lang`
                    WHERE `id_feature_value` = ' . (int) $res['id_feature_value']
                );

                if (!empty($customs)) {
                    $custom_values[(int) $res['id_feature_value']] = [];
                    foreach ($customs as $cus) {
                        $custom_values[(int) $res['id_feature_value']][(int) $cus['id_lang']] = $cus['value'];
                    }
                }
            }
        }

        return ['features' => $result, 'custom_values' => $custom_values];
    }

    public static function getAllForProductAttributeNew($id_product, $id_product_attribute)
    {
        if (!Feature::isFeatureActive()) {
            return [];
        }
        $context = Context::getContext();

        $query = 'SELECT ffc.`id_feature` as feature_id, ffc.`id_feature_value` as feature_value_id, fv.`custom`
                FROM `' . _DB_PREFIX_ . 'featuresforcombinations` ffc
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` fv
                    ON (ffc.`id_feature_value` = fv.`id_feature_value`)
                WHERE `id_product` = ' . (int) $id_product . '
                    AND `id_product_attribute` = ' . (int) $id_product_attribute;

        $result = Db::getInstance()->executeS($query);

        foreach ($result as &$res) {
            $res['predefined_values'] = FeatureValue::getFeatureValuesWithLang(
                $context->language->id,
                $res['feature_id']
            );
            $predefinedValueIds = array_map('intval', array_column($res['predefined_values'], 'id_feature_value'));
            $res['custom'] = (bool) $res['custom'] || !in_array((int) $res['feature_value_id'], $predefinedValueIds, true);
            $res['custom_value_id'] = null;

            if ($res['custom']) {
                $customFeatureValueId = (int) $res['feature_value_id'];
                $res['custom_value_id'] = $customFeatureValueId;
                $res['feature_value_id'] = 0;
                $res['custom_value'] = [];

                $customs = Db::getInstance()->executeS(
                    'SELECT `value`, `id_lang`
                    FROM `' . _DB_PREFIX_ . 'feature_value_lang`
                    WHERE `id_feature_value` = ' . $customFeatureValueId
                );

                if (!empty($customs)) {
                    foreach ($customs as $cus) {
                        $res['custom_value'][(int) $cus['id_lang']] = $cus['value'];
                    }
                }
            }
        }

        return $result;
    }

    public static function getCombinationsFeatures($id_product, $id_product_attribute, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        $query = 'SELECT DISTINCT fl.`name`, fvl.`value`
            FROM `' . _DB_PREFIX_ . 'featuresforcombinations` ffc
            -- CROSS JOIN `' . _DB_PREFIX_ . 'feature_product` pf
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                ON (ffc.`id_feature` = fl.`id_feature`
                    AND fl.`id_lang` = ' . (int) $id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                ON (ffc.`id_feature_value` = fvl.`id_feature_value`
                    AND fvl.`id_lang` = ' . (int) $id_lang . ')
            INNER JOIN `' . _DB_PREFIX_ . 'feature` f
                ON (f.`id_feature` = ffc.`id_feature`)' . Shop::addSqlAssociation('feature', 'f') . '
            WHERE ffc.`id_product` = ' . (int) $id_product . '
                AND ffc.`id_product_attribute` = ' . (int) $id_product_attribute . '
                -- AND pf.`id_product` = ' . (int) $id_product . '
            ORDER BY f.`position`';

        return Db::getInstance()->executeS($query);
    }
    public static function getCombinationsFeaturesNew($id_product, $id_product_attribute, $product_features = null,$id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int)Context::getContext()->language->id;
        }
 
        $ffc = FFC::getCombinationsFeatures($id_product, $id_product_attribute, $id_lang);
       
        $deduped_ffc = self::mergePreferFfc($product_features, $ffc);
    
        return $deduped_ffc;
    }

    /**
     * Remove from $ffc any feature that exactly matches (name+value) an existing feature.
        */
    protected static function mergePreferFfc(array $existing, array $ffc): array
    {
        $result = [];

        // Index FFC by name for fast lookup
        $ffc_by_name = [];
        foreach ($ffc as $ffc_feature) {
            if (isset($ffc_feature['name'])) {
                $ffc_by_name[$ffc_feature['name']] = $ffc_feature;
            }
        }

        // For each existing feature, use the FFC one if present, else the existing
        foreach ($existing as $exist_feature) {
            if (!isset($exist_feature['name'])) {
                continue;
            }
            $name = $exist_feature['name'];
            if (isset($ffc_by_name[$name])) {
                $result[] = $ffc_by_name[$name];
                // consumed, so remove to avoid duplication later
                unset($ffc_by_name[$name]);
            } else {
                $result[] = $exist_feature;
            }
        }

        // Append any remaining FFC features that had no existing counterpart
        foreach ($ffc_by_name as $remaining) {
            $result[] = $remaining;
        }

        return $result;
    }

    public function addCombinationsFeaturesToDB($id_product_attribute, $id_feature, $id_value, $custom = false)
    {
        if ($custom) {
            $row = ['id_feature' => (int) $id_feature, 'custom' => 1];
            Db::getInstance()->insert('feature_value', $row);
            $id_value = Db::getInstance()->Insert_ID();
        }
        $row = [
            'id_product' => (int) $this->id,
            'id_product_attribute' => (int) $id_product_attribute,
            'id_feature' => (int) $id_feature,
            'id_feature_value' => (int) $id_value,
        ];
        Db::getInstance()->insert('featuresforcombinations', $row, false, true, Db::INSERT_IGNORE);
        SpecificPriceRule::applyAllRules([(int) $this->id]);
        if ($id_value) {
            return $id_value;
        }
    }

    public static function bulkAddCombinationsFeaturesToDB($id_attribute, $id_feature, $id_value, $custom = false)
    {
        $product_attributes = static::getProductAttributesByAttributeId($id_attribute);
        $rows = array_map(function ($products) use ($id_feature, $id_value, $custom) {
            if ($custom) {
                $row = ['id_feature' => (int) $id_feature, 'custom' => 1];
                Db::getInstance()->insert('feature_value', $row);
                $id_value = Db::getInstance()->Insert_ID();
            }
            return [
                'id_product' => (int) $products['id_product'],
                'id_product_attribute' => (int) $products['id_product_attribute'],
                'id_feature' => (int) $id_feature,
                'id_feature_value' => (int) $id_value,
            ];
        }, $product_attributes);
        Db::getInstance()->insert('featuresforcombinations', $rows, false, true, Db::INSERT_IGNORE);
        foreach ($rows as $row) {
            SpecificPriceRule::applyAllRules([(int) $row['id_product']]);
        }
    }

    public function deleteCombinationsFeatures($id_product_attribute)
    {
        // @see AdminProductsController::deleteFeatures
        $features = Db::getInstance()->executeS(
            'SELECT ffc.*, fv.*
            FROM `' . _DB_PREFIX_ . 'featuresforcombinations` as ffc
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` as fv ON (fv.`id_feature_value` = ffc.`id_feature_value`)
            WHERE ffc.`id_product` = ' . (int) $this->id . '
                AND ffc.`id_product_attribute` = ' . (int) $id_product_attribute
        );

        foreach ($features as $tab) {
            if ($tab['custom']) {
                Db::getInstance()->delete(
                    'feature_value',
                    '`id_feature_value` = ' . (int) $tab['id_feature_value']
                );
                Db::getInstance()->delete(
                    'feature_value_lang',
                    '`id_feature_value` = ' . (int) $tab['id_feature_value']
                );
            }
        }

        Db::getInstance()->delete(
            'featuresforcombinations',
            '`id_product` = ' . (int) $this->id . ' AND `id_product_attribute` = ' . (int) $id_product_attribute
        );
    }

    private static function getProductAttributesByAttributeId($attributeId)
    {
        return Db::getInstance()->executeS(
            'SELECT pa.`id_product_attribute`, pa.`id_product`
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            INNER JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pa.`id_product_attribute` = pac.`id_product_attribute`
            WHERE pac.`id_attribute` = ' . (int) $attributeId
        );
    }

    public function deleteFFC($productAttributeId, $featureId, $featureValueId)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'featuresforcombinations` 
            WHERE `id_product` = ' . (int) $this->id . '
            AND `id_product_attribute` = ' . (int) $productAttributeId . ' 
            AND `id_feature` = ' . (int) $featureId . ' 
            AND `id_feature_value` = ' . (int) $featureValueId
        );
    }
}
