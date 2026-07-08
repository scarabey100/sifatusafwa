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

include_once _PS_MODULE_DIR_ . 'featuresforcombinations/classes/FFC.php';

class FeaturesforcombinationsOverride extends Featuresforcombinations
{
        /**
     * @param mixed $params
     */
    public function hookActionGetProductPropertiesAfter($params)
    {
        $product = &$params['product'];

        $id_lang = (int) $params['id_lang'];

        $id_product = (int) $product['id_product'];
        $id_product_attribute = (int) $product['id_product_attribute'];

        $ffc = FFC::getCombinationsFeatures($id_product, $id_product_attribute, $id_lang);
 
        if (isset($params['product']['features']) && !empty($params['product']['features'])) {
        // Use associative array with feature name as key for easier merging
        $final_features = [];
        
        foreach ($params['product']['features'] as $feature) {
            $final_features[$feature['name']] = $feature;
        }

        foreach ($ffc as $f2) {
            // If feature exists and value is empty in $ffc, keep existing
            if (isset($final_features[$f2['name']])) {
                if (!empty($f2['value'])) {
                    $final_features[$f2['name']]['value'] = $f2['value'];
                }
            } else {
                // Add new feature from $ffc
                $final_features[$f2['name']] = [
                    'name' => $f2['name'],
                    'value' => $f2['value'],
                    'id_feature' => null,
                    'position' => null,
                    'id_feature_value' => null,
                ];
            }
        }

        $params['product']['features'] = array_values($final_features);
        } else {
            $params['product']['features'] = $ffc;
        }
      
    }
}
