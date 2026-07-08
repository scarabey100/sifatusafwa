<?php
/**
 * 2007-2022 Boostmyshop
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationInProgress.php';
class HTMLTemplateOrderPickList extends HTMLTemplate
{
    public $allData;

    public $order;

    public $smarty;

    public $bulk_mode;

    public $allProducts;

    /**
     *
     * @param OrderInvoice $order_invoice
     * @param
     *            $smarty
     * @throws PrestaShopException
     */
    public function __construct($order, $smarty, $bulk_mode)
    {
        $this->order = $order;
        if (is_null($smarty)) {
            $smarty = Context::getContext()->smarty;
        }
        $this->smarty = $smarty;
        $this->bulk_mode = $bulk_mode;
    }

    public function getBulkFilename()
    {
        return parent::getBulkFilename();
    }

    /**
     * Returns the template's HTML header
     *
     * @return string HTML header
     */
    public function getHeader()
    {
        $this->assignCommonHeaderData();
        $allData = $this->allData;
        $shipping_methode = '';

        $allProducts = BmsOrderPreparationProductHelper::getAllProduct($allData);

        $order = $this->order;
        if (! empty($order->id_carrier)) {
            $carrier = new Carrier($order->id_carrier);
            $shipping_methode = $carrier->name;
        }

        $orderId = sprintf("%'09d", (int) $order->id);

        $barCodePath = _PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/barcode/';

        $bc = new pi_barcode();
        $bc->setSize(50, 150, 10);
        $bc->setFiletype('JPG');
        $bc->setType('C128');
        $bc->setCode($orderId);
        $code = $barCodePath . 'barcode_' . (int) $order->id . '.jpg';
        $bc->writeBarcodeFile($code);

        $this->smarty->assign(array(
            'id_order' => $orderId,
            'date' => Tools::displayDate(Date('Y-m-d')),
            'user' => Context::getContext()->employee->firstname . ' ' . Context::getContext()->employee->lastname,
            'method' => $shipping_methode,
            'productCount' => $allProducts['qte'],
            'weight' => round(BmsOrderPreparationInProgress::getWsWeightNumber((int) $order->id), 2),
            'codeBarre' => $code
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/header.tpl');
    }

    public function getFooter()
    {
        return '';
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $products = BmsOrderPreparationProductHelper::getAllProduct(array(
            $this->order
        ));
        $allProducts = $products['object'];

        $invoiceAddressPatternRules = json_decode(Configuration::get('PS_INVCE_INVOICE_ADDR_RULES'), true);
        $deliveryAddressPatternRules = json_decode(Configuration::get('PS_INVCE_DELIVERY_ADDR_RULES'), true);

        $invoice_address = new Address((int) $this->order->id_address_invoice);
        $formatted_invoice_address = AddressFormat::generateAddress($invoice_address, $invoiceAddressPatternRules, '<br />', ' ');

        $delivery_address = null;
        $formatted_delivery_address = '';
        if (isset($this->order->id_address_delivery) && $this->order->id_address_delivery) {
            $delivery_address = new Address((int) $this->order->id_address_delivery);
            $formatted_delivery_address = AddressFormat::generateAddress($delivery_address, $deliveryAddressPatternRules, '<br />', ' ');
        }

        $this->smarty->assign(array(
            'delivery_address' => $formatted_delivery_address,
            'invoice_address' => $formatted_invoice_address
        ));

        $templates = $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/addresses.tpl') . $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/thead.tpl');

        foreach ($allProducts as $product) {
            $code = '';
            if ($product->upc || $product->ean13) {
                if ($product->upc) {
                    $code = " - UPC : " . $product->upc;
                } elseif ($product->ean13) {
                    $code = " - EAN13 : " . $product->ean13;
                }
            }
            $this->smarty->assign(array(
                'quantities' => $product->product_quantity,
                'image' => (! empty($product->image) ? _PS_PROD_IMG_DIR_ . $product->image->getExistingImgPath() . '.jpg' : ''),
                'reference' => $product->product_reference,
                'codeBarre' => ($code ? $code : ''),
                'nom' => $product->product_name,
                'location' => $product->location
            ));

            $templates .= $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/orderPicklist/content.tpl');
        }

        return $templates;
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        return '';
    }
}
