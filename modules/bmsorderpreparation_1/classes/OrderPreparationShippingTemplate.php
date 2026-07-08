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

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationMimeType.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/SwissPost.php';
class BmsOrderPreparationShippingTemplate extends ObjectModel
{

    public $id;

    public $id_orderpreparation_shipping_template;

    public $name;

    public $active;

    public $date_add;

    public $shipping_methods;

    public $id_template_type;

    public $id_export_mime_type;

    public $id_import_mime_type = 1;

    public $export_file_name;

    public $export_file_header;

    public $export_file_order_header;

    public $export_file_order_products;

    public $export_file_order_footer;

    public $export_file_footer_line;

    public $import_file_separator;

    public $import_file_skip_first_line;

    public $import_file_shipment_reference_index;

    public $import_file_tracking_index;

    /**
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bms_orderpreparation_shipping_template',
        'primary' => 'id_bms_orderpreparation_shipping_template',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 100,
                'required' => true
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'shipping_methods' => array(
                'type' => self::TYPE_STRING
            ),
            'id_template_type' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_export_mime_type' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_import_mime_type' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'export_file_name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 50
            ),
            'export_file_header' => array(
                'type' => self::TYPE_HTML
            ),
            'export_file_order_header' => array(
                'type' => self::TYPE_HTML
            ),
            'export_file_order_products' => array(
                'type' => self::TYPE_HTML
            ),
            'export_file_order_footer' => array(
                'type' => self::TYPE_HTML
            ),
            'export_file_footer_line' => array(
                'type' => self::TYPE_HTML
            ),
            'import_file_separator' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 5
            ),
            'import_file_skip_first_line' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'import_file_shipment_reference_index' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'import_file_tracking_index' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            )
        )
    );

    /**
     * Return list of templates
     *
     *
     * @return array|false
     */
    public static function getTemplates($only_active = true)
    {
        if ($only_active) {
            $where = 'WHERE `active` = 1';
        } else {
            $where = '';
        }
        return Db::getInstance()->executeS('
			SELECT `id_bms_orderpreparation_shipping_template` as id_template, name
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ' . $where . '
		    ORDER BY `name` ASC
		');
    }

    public function import($file)
    {
        // die('je suis la;');
        // $mime_type = new BmsOrderPreparationMimeType($this->id_import_mime_type);
        $tpl = $this->getTplObject();

        if ($tpl) {
            $config = array(
                'import_file_separator' => $this->import_file_separator,
                'import_file_skip_first_line' => $this->import_file_skip_first_line,
                'import_file_shipment_reference_index' => $this->import_file_shipment_reference_index,
                'import_file_tracking_index' => $this->import_file_tracking_index
            );
            return $tpl->importTrackingFiles($file, $config);
        } else {
            return false;
        }
    }

    public function export($orders = array())
    {

        switch($this->id_template_type)
        {
            case 1:     //simple file export
                $tpl = $this->getTplObject();

                if ($tpl) {
                    $config = array(
                        'header' => $this->export_file_header,
                        'order_header' => $this->export_file_order_header,
                        'order_products' => $this->export_file_order_products,
                        'order_footer' => $this->export_file_order_footer,
                        'final_footer' => $this->export_file_footer_line,
                        'export_file_name' => $this->export_file_name
                    );
                    $tpl->createExportFiles($orders, $config);
                } else {
                    return false;
                }
                break;
            case 2:     //swisspost
                $tpl = new SwissPost();
                $tpl->exportLabel($orders);
                break;
        }

    }

    private function getTplObject()
    {

        $mime_type = new BmsOrderPreparationMimeType($this->id_import_mime_type);
        if (file_exists(_PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/' . $mime_type->class . '.php')) {
            require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/' . $mime_type->class . '.php';
            $class_name = $mime_type->class;
            $tpl = new $class_name();
            return $tpl;
        } else {
            return false;
        }
    }

    public static function getTemplateByCarrier($id_carrier)
    {
        $templates = BmsOrderPreparationShippingTemplate::getTemplates();

        foreach ($templates as $tpl) {
            $template = new BmsOrderPreparationShippingTemplate($tpl['id_template']);
            $shipping_methods = explode(',', $template->shipping_methods);
            if (in_array($id_carrier, $shipping_methods)) {
                return $template;
            }
        }

        return false;
    }
}
