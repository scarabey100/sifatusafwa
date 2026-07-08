<?php
/**
 * 2007-2025 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class SpecificPriceRule extends SpecificPriceRuleCore
{
    public function getAffectedProducts($products = false)
    {
        $conditions_group = $this->getConditions();
        $current_shop_id = Context::getContext()->shop->id;

        $result = [];

        if ($conditions_group) {
            foreach ($conditions_group as $condition_group) {
                // Base request
                $query = new DbQuery();
                $query->select('p.`id_product`')
                    ->from('product', 'p')
                    ->leftJoin('product_shop', 'ps', 'p.`id_product` = ps.`id_product`')
                    ->where('ps.id_shop = ' . (int) $current_shop_id);

                $attributes_join_added = false;

                // Add the conditions
                foreach ($condition_group as $id_condition => $condition) {
                    if ($condition['type'] == 'attribute') {
                        if (!$attributes_join_added) {
                            $query->select('pa.`id_product_attribute`')
                                ->leftJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`')
                                ->join(Shop::addSqlAssociation('product_attribute', 'pa', false));

                            $attributes_join_added = true;
                        }

                        $query->leftJoin(
                            'product_attribute_combination',
                            'pac' . (int) $id_condition,
                            'pa.`id_product_attribute` = pac' . (int) $id_condition . '.`id_product_attribute`'
                        )
                            ->where('pac' . (int) $id_condition . '.`id_attribute` = ' . (int) $condition['value']);
                    } elseif ($condition['type'] == 'manufacturer') {
                        $query->where('p.id_manufacturer = ' . (int) $condition['value']);
                    } elseif ($condition['type'] == 'category') {
                        $query->leftJoin(
                            'category_product',
                            'cp' . (int) $id_condition,
                            'p.`id_product` = cp' . (int) $id_condition . '.`id_product`'
                        )
                            ->where('cp' . (int) $id_condition . '.id_category = ' . (int) $condition['value']);
                    } elseif ($condition['type'] == 'supplier') {
                        $query->where('EXISTS(
							SELECT
								`ps' . (int) $id_condition . '`.`id_product`
							FROM
								`' . _DB_PREFIX_ . 'product_supplier` `ps' . (int) $id_condition . '`
							WHERE
								`p`.`id_product` = `ps' . (int) $id_condition . '`.`id_product`
								AND `ps' . (int) $id_condition . '`.`id_supplier` = ' . (int) $condition['value'] . '
						)');
                    } elseif ($condition['type'] == 'feature') {
                        // FEATURESFORCOMBINATIONS
                        $query->leftJoin(
                            'feature_product',
                            'fp' . (int) $id_condition,
                            'p.`id_product` = fp' . (int) $id_condition . '.`id_product`
                                AND fp' . (int) $id_condition . '.`id_feature_value` = ' . (int) $condition['value']
                        );

                        if (!$attributes_join_added) {
                            $query->select('pa.`id_product_attribute`')
                                ->leftJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`')
                                ->join(Shop::addSqlAssociation('product_attribute', 'pa', false));
                            $attributes_join_added = true;
                        }
                        $query->leftJoin(
                            'featuresforcombinations',
                            'ffc' . (int) $id_condition,
                            'p.`id_product` = ffc' . (int) $id_condition . '.`id_product`
                                AND pa.`id_product_attribute` = ffc' . (int) $id_condition . '.`id_product_attribute`
                                AND ffc' . (int) $id_condition . '.`id_feature_value` = ' . (int) $condition['value']
                        );
                        $query->where('(
                            fp' . (int) $id_condition . '.`id_product` IS NOT NULL
                            OR ffc' . (int) $id_condition . '.`id_product` IS NOT NULL
                        )');
                        // END FEATURESFORCOMBINATIONS
                    }
                }

                // Products limitation
                if ($products && count($products)) {
                    $query->where('p.`id_product` IN (' . implode(', ', array_map('intval', $products)) . ')');
                }

                // Force the column id_product_attribute if not requested
                if (!$attributes_join_added) {
                    $query->select('NULL as `id_product_attribute`');
                }
                $result = array_merge($result, Db::getInstance()->executeS($query));
            }
        } else {
            // All products without conditions
            if ($products && count($products)) {
                $query = new DbQuery();
                $query->select('p.`id_product`')
                    ->select('NULL as `id_product_attribute`')
                    ->from('product', 'p')
                    ->leftJoin('product_shop', 'ps', 'p.`id_product` = ps.`id_product`')
                    ->where('ps.id_shop = ' . (int) $current_shop_id);
                $query->where('p.`id_product` IN (' . implode(', ', array_map('intval', $products)) . ')');
                $result = Db::getInstance()->executeS($query);
            } else {
                $result = [['id_product' => 0, 'id_product_attribute' => null]];
            }
        }

        return $result;
    }
}
