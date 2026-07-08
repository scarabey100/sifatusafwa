<?php
/**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright 2007-2022 Boostmyshop
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class BmsOrderPreparationShippingTemplateType extends ObjectModel
{

    public $id;

    public $id_bms_orderpreparation_shipping_template_type;

    public $name;

    /**
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bms_orderpreparation_shipping_template_type',
        'primary' => 'id_bms_orderpreparation_shipping_template_type',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 100,
                'required' => true
            )
        )
    );

    /**
     * Return list of template types
     *
     *
     * @return array|false
     */
    public static function getTemplateTypes()
    {
        $types = array();

        $types[] = array(   'id_template_type' => 1,
                            'name' => 'Order details file export',
                            'code' => 'order_details_fil_export',
                            'execute_on_packed' => false,
                            'configurable' => true
                        );

        if (Module::isInstalled('swisspostbarcodegenerator'))
        {
            $types[] = array(
                            'id_template_type' => 2,
                            'name' => 'SwissPostBarcode Generator',
                            'code' => 'swiss_post_barcode_generator',
                            'execute_on_packed' => true,
                            'configurable' => false
                            );
        }

        return $types;

    }
}
