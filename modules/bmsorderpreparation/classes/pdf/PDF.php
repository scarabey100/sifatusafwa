<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
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

        if (count($this->datas) > 1) { // when bulk mode only
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

                $this->pdf_renderer->createHeader($templateObject->getHeader());
                $this->pdf_renderer->createFooter($templateObject->getFooter());
                $this->pdf_renderer->createPagination($templateObject->getPagination());
                $this->pdf_renderer->createContent($templateObject->getContent());
                $this->pdf_renderer->writePage();
                $render = true;
            }
        }

        if ($render) {
            // clean the output buffer
            if (ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }

            return $this->pdf_renderer->render($this->fileName, $display);
        }
    }

    protected function getInvoiceIdFromOrderId($orderId)
    {
        $sql = 'select id_order_invoice from ' . _DB_PREFIX_ . 'order_invoice where id_order = '.pSQL($orderId);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        return $result;
    }

    /**
     * Get correct PDF template classes.
     *
     * @param mixed $object
     *
     * @return HTMLTemplate|false
     *
     * @throws PrestaShopException
     */
    public function getTemplateObject($object)
    {
        $class = false;

        if ($this->template === PDF::TEMPLATE_INVOICE) {
            $class_name = 'HTMLTemplateBms' . $this->template;
        } else {
            $class_name = 'HTMLTemplate' . $this->template;
        }

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
