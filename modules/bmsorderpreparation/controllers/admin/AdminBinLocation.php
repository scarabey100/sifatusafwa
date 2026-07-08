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

class AdminBinLocationController extends ModuleAdminController
{

    public function __construct()
    {
    
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = false;
        parent::__construct();

        $this->override_folder = 'bin_location/';
        $this->list_no_link = true;

        $id_shop = (int)$this->context->shop->id;
        $id_lang = (int)$this->context->language->id;
        $reference =  Tools::getValue("productFilter_ref");
        $ean13 =  Tools::getValue("productFilter_ean13");
        $name =  Tools::getValue("productFilter_name");
        $quantity =  Tools::getValue("productFilter_quantity");
        $location =  Tools::getValue("productFilter_location");
 
        $this->fields_list = [
            'id_product' => [
                'title' => 'id_product',
                'align' => 'center',
                'class' => 'hidden', // hide from list
                'search' => false,
                'orderby' => false,
            ],
            'id_product_attribute' => [
                'title' => 'id_product_attribute',
                'align' => 'center',
                'class' => 'hidden', // hide from list
                'search' => false,
                'orderby' => false,
            ],
            'id_image' => [
                'title' => $this->l('Image'),
                'align' => 'center',
                'search' => false,
                'orderby' => false,
                'callback' => 'getImage',
            ],
            'ref' => [
                'title' => $this->l('Reference'),
                'align' => 'center',
//                'filter_key' => 'a!reference',
//                'filter_key' => 'pa!reference',
                //nalezny - removing two filter_keys to enable search by reference
                'havingFilter' => true,
            ],  
            'ean13' => [
                'title' => $this->l('EAN13'),
                'align' => 'center',
                'havingFilter' => true,
            ],
            'productName' => [
                'title' => $this->l('Product'),
                'align' => 'left',
                'filter_key' => 'pl!name',
            ],
            'stockLevel' => [
                'title' => $this->l('Stock level'),
                'align' => 'center',
                'filter_key' => 'sa!quantity',
                'callback' => 'getStock', // ensure input is rendered
            ],
            'location' => [
                'title' => $this->l('Bin location'),
                'align' => 'right',
                'filter_key' => 'sa!location',
//                'filter_key' => 'a!location',
//                'filter_key' => 'pa!location',
                //nalezny - removing second 'filter_key' to enable searching by bin location
                'callback' => 'getBinLocation', // ensure input is rendered
            ],
        ];
        // This will show BOTH simple products AND products with variations
            $this->_select = "
                pl.name AS productName,
                image_shop.id_image,
                IF(sa.id_product_attribute = 0, a.reference, 
                COALESCE(NULLIF(pa.reference, ''), a.reference)) AS ref,
                IF(sa.id_product_attribute = 0, a.ean13, 
                COALESCE(NULLIF(pa.ean13, ''), a.ean13)) AS ean13,
                sa.id_product_attribute,
                sa.quantity AS stockLevel,
                sa.location
            ";

            $this->_join = "
                LEFT JOIN " . _DB_PREFIX_ . "product_lang pl ON (a.id_product = pl.id_product AND pl.id_shop = $id_shop AND pl.id_lang = $id_lang)
                INNER JOIN " . _DB_PREFIX_ . "stock_available sa ON (
                    a.id_product = sa.id_product
                    " . StockAvailable::addSqlShopRestriction(null, null, 'sa') . "
                )
                LEFT JOIN " . _DB_PREFIX_ . "product_attribute pa ON (
                    a.id_product = pa.id_product 
                    AND sa.id_product_attribute = pa.id_product_attribute
                    AND sa.id_product_attribute > 0
                )
                LEFT JOIN " . _DB_PREFIX_ . "image_shop image_shop ON (
                    image_shop.id_product = a.id_product
                    AND image_shop.cover = 1
                    AND image_shop.id_shop = $id_shop
                )
            ";

        $this->_use_found_rows = true;

    }

    public function init()
    { 
        parent::init(); 
    }
    public function initContent()
    {
        parent::initContent();
        $this->setBmsMedia();
    }

    public function getProductName($id_product, $object)
    {
        return Product::getProductName($object['id_product'], $object['id_product_attribute'], (int) $this->context->language->id);
    }

    public function getStock($id_product, $object)
    {
        $tpl = $this->createTemplate('stock.tpl');
        $tpl->assign('id_product', $object['id_product']);
        $tpl->assign('id_attribute', ($object['id_product_attribute'] ? $object['id_product_attribute'] : 0));
        $tpl->assign('stock', $object['stockLevel']);

        return $tpl->fetch();
    }

    public function getImage($id_product, $object)
    {
        $tpl = $this->createTemplate('image.tpl');
        $id_cover = Image::getCover($object['id_product'])['id_image'];

        $link = new Link('https://','https://');
        $image_url = $link->getImageLink('thumb', $id_cover);

        $tpl->assign('id_product', $object['id_product']);
        $tpl->assign('image_url', $image_url);

        return $tpl->fetch();
    }

    public function getBinLocation($id_product, $object)
    {
        $tpl = $this->createTemplate('bin_location.tpl');

        $tpl->assign('id_product', $object['id_product']);
        $tpl->assign('id_attribute', ($object['id_product_attribute'] ? $object['id_product_attribute'] : 0));
        $tpl->assign('location', $object['location']);

        return $tpl->fetch();
    }
 
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        unset( $this->toolbar_btn['new'] );

        $this->context->smarty->assign('help_link', null);
    }

    public function setBmsMedia()
    {
        Media::addJsDef(array(
            'ajaxSaveBinLocationUrl' => $this->context->link->getAdminLink('AdminBinLocation', true),
            'pendingImgUrl' => Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/img/pending.png',
            'doneImgUrl' => Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/img/done.png',
        ));
        $rand = rand();
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/binLocation.js');
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/asdf.css');
        $this->addJS('/modules/bmsorderpreparation/views/js/binLocationScanner_v14.js?v='.$rand, false);
    }

    public function ajaxPreProcess()
    {
        $result = array('result' => false);
        
        if (Tools::getValue('method') == 'saveLocation')
        {

            $id_product = (int) Tools::getValue('id_product');
            $id_attribute = (int) Tools::getValue('id_attribute', 0);
            $value = Tools::getValue('value', null);

            //field location doesn't exists on Prestashop 1.6 version on table stock_available , skip if Prestashop is not a 1.7.5.0 or higher version.
            if(version_compare(_PS_VERSION_, '1.7.5.0', '>='))
            {
                StockAvailable::setLocation($id_product,$value,null,$id_attribute);
            }

            if (!$id_attribute)
                $obj = new Product($id_product);
            else
                $obj = new Combination($id_attribute);

            if ($obj)
            {
                $obj->location = $value;
                $obj->save();
            }

            $result['result'] = true;
            $result['message'] = 'Location updated to '.$value.' for product #'.$id_product.' and attribute #'.$id_attribute;
        }

        if (Tools::getValue('method') == 'saveStock')
        {

            $id_product = (int) Tools::getValue('id_product');
            $id_attribute = (int) Tools::getValue('id_attribute', 0);
            $value = Tools::getValue('value', null);

            StockAvailable::setQuantity($id_product, $id_attribute, $value); 

            $result['result'] = true;
            $result['message'] = 'Stock updated to '.$value.' for product #'.$id_product.' and attribute #'.$id_attribute;
        }

        die(json_encode($result));
    }

    public static function getProductFromBareCode($id_order)
    {
        $id_order = (int) $id_order;
        $query = new DbQuery();
        $query->select('id_order')
            ->from('bms_orderpreparation_inprogress', 'ord')
            ->where("id_order=" . (int) $id_order);
        $result = DB::getInstance()->getValue($query);

        return $result;
    }

}
