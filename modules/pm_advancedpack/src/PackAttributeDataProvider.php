<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

namespace PrestaModule\AdvancedPack;
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Adapter\Attribute\AttributeDataProvider;
class PackAttributeDataProvider extends AttributeDataProvider
{
    public static function getAttributes($id_lang, $not_null = false)
    {
        $attributes = parent::getAttributes($id_lang, $not_null);
        foreach ($attributes as $k => $attributeGroup) {
            if (empty($attributeGroup['attribute_group']) || $attributeGroup['attribute_group'] != 'AP5-Pack') {
                continue;
            }
            unset($attributes[$k]);
        }
        return $attributes;
    }
}
