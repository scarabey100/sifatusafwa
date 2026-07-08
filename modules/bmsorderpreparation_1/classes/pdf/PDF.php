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

class BMSPDF extends PDFCore
{

    const GLOBAL_PICK_LIST = 'GlobalPickList';

    const ORDER_PICK_LIST = 'OrderPickList';

    public function __construct($datas, $templates, $fileName, $smarty = null, $orientation = 'P')
    {
        if (is_null($smarty)) {
            $smarty = Context::getContext()->smarty;
        }
        $this->pdf_renderer = new PDFGenerator((bool) Configuration::get('PS_PDF_USE_CACHE'), $orientation);
        $this->templates = $templates;
        $this->datas = $datas;
        $this->fileName = $fileName;

        /*
         * We need a Smarty instance that does NOT escape HTML.
         * Since in BO Smarty does not autoescape
         * and in FO Smarty does autoescape, we use
         * a new Smarty of which we're sure it does not escape
         * the HTML.
         */
        $this->smarty = clone $smarty;
        $this->smarty->escape_html = false;

        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->registerSmartyFunctions($smarty);

        if (is_array($this->datas) && count($this->datas) > 1) { // when bulk mode only
            $this->send_bulk_flag = true;
        }
    }

    public function registerSmartyFunctions($smarty)
    {
        /*
         * We need to get the old instance of the LazyRegister
         * because some of the functions are already defined
         * and we need to check in the old one first
         */
        $original_lazy_register = SmartyLazyRegister::getInstance($smarty);

        /*
         * For PDF we restore some functions from Smarty
         * they've been removed in PrestaShop 1.7 so
         * new themes don't use them. Although PDF haven't been
         * reworked so every PDF controller must extend this class.
         */

        smartyRegisterFunction($this->smarty, 'function', 'convertPrice', array('Product', 'convertPrice'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'convertPriceWithCurrency', array('Product', 'convertPriceWithCurrency'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'displayWtPrice', array('Product', 'displayWtPrice'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'displayWtPriceWithCurrency', array('Product', 'displayWtPriceWithCurrency'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'displayPrice', array('Tools', 'displayPriceSmarty'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'modifier', 'convertAndFormatPrice', array('Product', 'convertAndFormatPrice'), true, $original_lazy_register); // used twice
        smartyRegisterFunction($this->smarty, 'function', 'displayAddressDetail', array('AddressFormat', 'generateAddressSmarty'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'getWidthSize', array('Image', 'getWidth'), true, $original_lazy_register);
        smartyRegisterFunction($this->smarty, 'function', 'getHeightSize', array('Image', 'getHeight'), true, $original_lazy_register);
    }

    public function render($display = true)
    {
        $render = false;
        $this->pdf_renderer->startPageGroup();
        $this->pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);

        $nbPassagePickList = 0;


        foreach ($this->datas as $data) {

            foreach ($this->templates as $template) {
                $this->template = $template;

                switch ($template) {
                    case self::GLOBAL_PICK_LIST:
                        if ($nbPassagePickList >= 1) {
                            continue 2;
                        }

                        $nbPassagePickList ++;
                        $templateObject = $this->getTemplateObject($data);
                        if (! $templateObject) {
                            continue;
                        }

                        $templateObject->allData = $this->datas;
                        break;

                    case self::ORDER_PICK_LIST:
                        $templateObject = $this->getTemplateObject($data);

                        if (! $templateObject) {
                            continue;
                        }
                        $templateObject->allData = array(
                            $data
                        );
                        break;
                    case PDF::TEMPLATE_DELIVERY_SLIP:
                    case PDF::TEMPLATE_INVOICE:
                        if ($data->invoice_number == 0) {
                            continue 2;
                        }


                        $invoiceId = $this->getInvoiceIdFromOrderId($data->id);

                        $templateObject = $this->getTemplateObject(new OrderInvoice($invoiceId));

                        if (! $templateObject) {
                            continue;
                        }

                        break;
                }

                // $template->assignHookData($data);

                // Get the header content
                $header = $templateObject->getHeader();
                
                // Get the footer content
                $footer = $templateObject->getFooter();
                
                // Get the content
                $content = $templateObject->getContent();
                
                // Force barcode display for invoice templates
                if ($template == PDF::TEMPLATE_INVOICE) {
                    // Create barcode directory with full permissions
                    $barCodePath = _PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/invoice/barcode/';
                    if (!file_exists($barCodePath)) {
                        mkdir($barCodePath, 0777, true);
                    }
                    
                    // Get order ID from the invoice object
                    $orderID = 0;
                    if (isset($templateObject->order) && $templateObject->order) {
                        $orderID = $templateObject->order->id;
                    } elseif (isset($data->id_order) && $data->id_order) {
                        $orderID = $data->id_order;
                    } elseif (isset($data->id) && $data->id) {
                        $orderID = $data->id;
                    }
                    
                    if ($orderID > 0) {
                        // Generate barcode for the order ID
                        if (!class_exists('pi_barcode')) {
                            require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/vendors/pi_barcode.php';
                        }
                        
                        // Format order ID to 9 digits with leading zeros
                        $orderIDFormatted = str_pad((string)$orderID, 9, '0', STR_PAD_LEFT);
                        
                        $bc = new pi_barcode();
                        $bc->setSize(50, 150, 10);
                        $bc->setFiletype('JPG');
                        $bc->setType('C128');
                        $bc->setCode($orderIDFormatted);
                        $orderBarcode = $barCodePath . 'barcode_order_' . $orderID . '.jpg';
                        $bc->writeBarcodeFile($orderBarcode);
                        
                        // Add barcode HTML at the bottom of the content - with absolute path for PDF renderer
                        $barcodeHtml = '<table width="100%" border="0" cellpadding="4" cellspacing="0">
                            <tr><td height="30">&nbsp;</td></tr>
                            <tr>
                                <td style="text-align: right;">
                                    <img src="' . $orderBarcode . '" style="width: 150px; height: 50px;"><br>
                                </td>
                            </tr>
                        </table>';
                        
                        // Append barcode to the end of the content
                        $content .= $barcodeHtml;
                    }
                }
                
                $this->pdf_renderer->createHeader($header);
                $this->pdf_renderer->createFooter($footer);
                $this->pdf_renderer->createPagination($templateObject->getPagination());
                $this->pdf_renderer->createContent($content);
                $this->pdf_renderer->writePage();
                $render = true;
            }
        }

        if ($render) {
            // clean the output buffer
            if (ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }
            
            // Generate the PDF
            $result = $this->pdf_renderer->render($this->fileName, $display);
            
            // Always clean up barcode files after PDF generation
            $barCodePath = _PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/invoice/barcode/';
            if (file_exists($barCodePath) && is_dir($barCodePath)) {
                $files = glob($barCodePath . 'barcode_order_*.jpg');
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                    }
                }
            }
            
            return $result;
        }
    }

    protected function getInvoiceIdFromOrderId($orderId)
    {
        $sql = 'select id_order_invoice from ' . _DB_PREFIX_ . 'order_invoice where id_order = '.pSQL($orderId);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        return $result;
    }

    public function getTemplateObject($object)
    {
        $class = false;
        $class_name = 'HTMLTemplate' . $this->template;

        if (class_exists($class_name)) {
            // Some HTMLTemplateXYZ implementations won't use the third param but this is not a problem (no warning in PHP),
            // the third param is then ignored if not added to the method signature.
            $class = new $class_name($object, $this->smarty, $this->send_bulk_flag);

            if (!($class instanceof HTMLTemplate)) {
                throw new PrestaShopException('Invalid class. It should be an instance of HTMLTemplate');
            }
        }

        return $class;
    }
}
