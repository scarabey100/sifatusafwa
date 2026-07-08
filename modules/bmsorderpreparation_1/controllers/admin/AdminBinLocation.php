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

class AdminBinLocationController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';


        $this->lang = false;

        parent::__construct();

        $this->list_no_link = true;
        $this->override_folder = 'bin_location/';

        $this->fields_list = array(
            'image' => array(
                'title' => $this->l('Image'),
                'image' => 'p',
                'align' => 'center',
                'search' => false,
                'orderby' => false,
                'filter' => false
            ),
            'ref' => array(
                'title' => $this->l('Reference'),
                'align' => 'center',
                'filter_key' => 'a!reference'
            ),
            'productName' => array(
                'title' => $this->l('Product'),
                'align' => 'left',
                'filter_key' => 'pl!name',
                'search' => true,
                'orderby' => true,
                'callback' => 'getProductName'
            ),
            'stockLevel' => array(
                'title' => $this->l('Stock level'),
                'align' => 'center',
                'filter_key' => 'sa!quantity',
                'search' => true,
                'orderby' => true
            ),
            'location' => array(
                'title' => $this->l('Bin location'),
                'align' => 'right',
                'search' => false,
                'orderby' => false,
                'callback' => 'getBinLocation'
            ),
        );

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

    public function getBinLocation($id_product, $object)
    {
        $tpl = $this->createTemplate('bin_location.tpl');

        $tpl->assign('id_product', $object['id_product']);
        $tpl->assign('id_attribute', ($object['id_product_attribute'] ? $object['id_product_attribute'] : 0));
        $tpl->assign('location', $object['location']);

        return $tpl->fetch();
    }

    public function renderList()
    {
        $this->tpl_list_vars['title'] = $this->l('Bin locations management');


        $this->_select = 'id_image, a.id_product, ' . 'IF(pa.`reference` IS NULL OR pa.`reference` = "" ,a.`reference`,pa.`reference`) as ref,' . 'a.`id_product` as productName,' . 'pa.`id_product_attribute`,' . 'sa.`quantity` as stockLevel, if (pa.`id_product_attribute` is null, a.location, if(pa.location is null, "", pa.location)) as location ';

        $this->_join =  'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa on (a.`id_product` = pa.`id_product`) ' .
            'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (a.`id_product` = pl.id_product and id_shop = '.$this->context->shop->id.' and id_lang = '.$this->context->language->id.') ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (a.`id_product` = sa.`id_product` AND IFNULL(pa.`id_product_attribute`,0) = sa.`id_product_attribute`' . StockAvailable::addSqlShopRestriction(null, null, 'sa') . ') ' .
            'LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = a.`id_product` AND image_shop.`cover` = 1 AND image_shop.id_shop = ' . $this->context->shop->id . ') ';

        return parent::renderList();
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
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/binLocation.js');
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
                $class = "StockAvailable";
                $method = "setLocation";
                $class::{$method}($id_product,$value,null,$id_attribute);
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

        die(json_encode($result));
    }
}
