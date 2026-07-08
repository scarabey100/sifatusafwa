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

class HTMLTemplateGlobalPickList extends HTMLTemplate
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
        $this->allProducts = BmsOrderPreparationProductHelper::getAllProduct($allData);
        $orderCount = is_array($allData) ? count($allData) : 0;

        $this->smarty->assign(array(
            'date' => Tools::displayDate(Date('Y-m-d')),
            'user' => Context::getContext()->employee->firstname . ' ' . Context::getContext()->employee->lastname,
            'orderCount' => $orderCount,
            'refCount' => $this->allProducts['nb'],
            'productCount' => $this->allProducts['qte']
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/globalPicklist/header.tpl');
    }

    protected function getProductCount($id_order)
    {
        $query = new DbQuery();
        $query->select('count(id_order_detail)')
            ->from('order_detail')
            ->where('id_order=' . (int) $id_order . ' and id_shop=' . (int) Context::getContext()->shop->id);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

        return $result;
    }

    protected function setShopId()
    {
        $id_shop = (int) Context::getContext()->shop->id;
        $this->shop = new Shop($id_shop);
        if (Validate::isLoadedObject($this->shop)) {
            Shop::setContext(Shop::CONTEXT_SHOP, (int) $this->shop->id);
        }
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
        $allProducts = $this->allProducts['object'];
        $templates = $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/globalPicklist/thead.tpl');
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

            $templates .= $this->smarty->fetch(_PS_MODULE_DIR_ . 'bmsorderpreparation/views/templates/admin/pdf/globalPicklist/content.tpl');
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
        // return 'purchase_order.pdf';
    }
}
