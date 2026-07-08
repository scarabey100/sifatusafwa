<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/ShippingLabelTemplate.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/ShippingTemplateCodes.php';
class CsvXml extends BmsShippingLabelTemplate
{

    public function createExportFiles($orders = array(), $config = array())
    {
        $header = $config['header'];
        $order_header = $config['order_header'];
        $order_products = $config['order_products'];
        $order_footer = $config['order_footer'];
        $final_footer = $config['final_footer'];
        $export_file_name = $config['export_file_name'];

        $orders_content = "";
        foreach ($orders as $order) {
            $line_header = $order_header;
            $line_footer = $order_footer;

            /**
             * insertion tags order *
             */
            ShippingTemplatesCodes::hydrateOrder($order);
            foreach ($order as $key_order => $value_order) {
                $line_header = str_replace("{order." . $key_order . "}", $value_order, $line_header);
                $line_footer = str_replace("{order." . $key_order . "}", $value_order, $line_footer);
            }

            /**
             * insertion tags customer *
             */
            $customer = $order->getCustomer();
            foreach ($customer as $key_customer => $value_customer) {
                $line_header = str_replace("{customer." . $key_customer . "}", $value_customer, $line_header);
                $line_footer = str_replace("{customer." . $key_customer . "}", $value_customer, $line_footer);
            }

            /**
             * insertion tags invoice address *
             */
            $invoice_address = new Address((int) $order->id_address_invoice);
            ShippingTemplatesCodes::hydrateAddress($invoice_address);
            foreach ($invoice_address as $key_invoice => $value_invoice) {
                $line_header = str_replace("{invoice_address." . $key_invoice . "}", $value_invoice, $line_header);
                $line_footer = str_replace("{invoice_address." . $key_invoice . "}", $value_invoice, $line_footer);
            }

            /**
             * insertion tags delivery address *
             */
            $delivery_address = new Address((int) $order->id_address_delivery);
            ShippingTemplatesCodes::hydrateAddress($delivery_address);
            foreach ($delivery_address as $key_delivery => $value_delivery) {
                $line_header = str_replace("{delivery_address." . $key_delivery . "}", $value_delivery, $line_header);
                $line_footer = str_replace("{delivery_address." . $key_delivery . "}", $value_delivery, $line_footer);
            }

            /**
             * insertion tags order carrier *
             */
            $id_order_carrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `' . _DB_PREFIX_ . 'order_carrier` WHERE `id_order` = ' . pSQL($order->id));
            $order_carrier = new OrderCarrier($id_order_carrier);
            ShippingTemplatesCodes::hydrateOrderCarrier($order_carrier);
            foreach ($order_carrier as $key_delivery => $value_delivery) {
                $line_header = str_replace("{order_carrier." . $key_delivery . "}", $value_delivery, $line_header);
                $line_footer = str_replace("{order_carrier." . $key_delivery . "}", $value_delivery, $line_footer);
            }

            $order_details = $order->getProducts();

            $order_details_contents = "";

            foreach ($order_details as $product) {
                $line_content = $order_products;
                foreach ($product as $key_product => $value_product) {
                    if (! is_array($value_product) && ! is_object($value_product)) {
                        $line_content = str_replace("{order_details." . $key_product . "}", $value_product, $line_content);
                    }
                }
                $order_details_contents = ($order_details_contents == "" ? $line_content : $order_details_contents . "\n" . $line_content);
            }

            if ($orders_content == "") {
                $orders_content = $line_header . "\n" . $order_details_contents . "\n" . $line_footer;
            } else {
                $orders_content = $orders_content . "\n" . $line_header . "\n" . $order_details_contents . "\n" . $line_footer;
            }
        }

        $content = $header . "\n" . $orders_content . "\n" . $final_footer;

        $content = utf8_decode($content);
        header("Content-type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $export_file_name);
        header("Pragma: no-cache");
        header("Expires: 0");

        echo $content;
        die();
    }

    public function importTrackingFiles($file, $config = array())
    {
        try {
            $errors = array();
            $tmpName = $file['tmp_name'];

            $delimiter = $this->getFileDelimiter($tmpName);

            $import_file_separator = $config['import_file_separator'];
            $import_file_skip_first_line = $config['import_file_skip_first_line'];
            $import_file_shipment_reference_index = $config['import_file_shipment_reference_index'];
            $import_file_tracking_index = $config['import_file_tracking_index'];
            $skip_first_line = $import_file_skip_first_line;

            if ($import_file_separator != $delimiter) {
                $errors[] = "This file doesn't have the same delimiter that the one set in the template configuration";
            }

            $fic = fopen($tmpName, "r");
            if (! count($errors)) {
                while ($tab = fgetcsv($fic, 0, $import_file_separator)) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }

                    if (isset($tab[$import_file_shipment_reference_index - 1]) && isset($tab[$import_file_tracking_index - 1])) {
                        $id_order = $tab[$import_file_shipment_reference_index - 1];
                        $id_tracking = $tab[$import_file_tracking_index - 1];
                        $order = new Order($id_order);
                        if ($order->id && $order->getWsShippingNumber() == "") {
                            $order->setWsShippingNumber($id_tracking);
                        }
                    }
                }
                return true;
            } else {
                return $errors;
            }
        } catch (PrestaShopException $e) {
            $errors[] = $e->getMessage();
            return $errors;
        }
    }
}
