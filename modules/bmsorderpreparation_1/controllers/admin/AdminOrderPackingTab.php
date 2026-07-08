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

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationInProgress.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/OrderPreparationShippingTemplate.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/ProductHelper.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/SwissPost.php';
class AdminOrderPackingTabController extends ModuleAdminController
{

    protected $filter = null;

    public $id_order = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'order_detail';
        $this->className = 'OrderDetail';
        $this->identifier = 'id_order_detail';
        $this->lang = false;
        $this->list_simple_header = true;
        $this->_default_pagination = 500;
        parent::__construct();
        $this->list_no_link = true;

        $this->override_folder = 'order_packing/';
        $this->setFieldsListe();
    }

    public function init()
    {
        parent::init();
        if (Tools::getValue('no_template')) {
            $this->errors[] = $this->l('There is no template suitable to the carrier of this order.');
        }

        $id_order = (int) Tools::getValue('id_order', null);
        if (! $id_order) {
            $this->errors[] = $this->l('You do not have permission to delete this.');
        } else {
            $this->id_order = $id_order;
        }

    }

    public function initContent()
    {
        parent::initContent();
        $this->setBmsMedia();
    }

    public function initProcess()
    {
        $this->display_header = false;
        $this->display_header_javascript = true;
        $this->display_footer = false;
        $this->content_only = false;
        $this->lite_display = true;

        parent::initProcess();
    }

    protected function setFieldsListe()
    {
        $this->fields_list = array(
            'image' => array(
                'title' => $this->l('Image'),
                'image' => 'p',
                'align' => 'center',
                'search' => false,
                'orderby' => false,
                'filter' => false
            ),
            'product_quantity' => array(
                'title' => $this->l('Qty to ship'),
                'align' => 'center',
                'search' => true
            ),
            'QtyPacked' => array(
                'title' => $this->l('Qty packed'),
                'align' => 'center',
                'callback' => 'getQtyPacked',
                'search' => false,
                'orderby' => false
            ),
            'product_reference' => array(
                'title' => $this->l('Reference'),
                'align' => 'center',
                'search' => true
            ),
            'product_ean13' => array(
                'title' => $this->l('Barcode'),
                'align' => 'center',
                'search' => true
            ),
            'location' => array(
                'title' => $this->l('Location'),
                'align' => 'center',
                'callback' => 'getLocation',
                'search' => true
            ),
            'product_name' => array(
                'title' => $this->l('Product'),
                'align' => 'center',
                'search' => true,
                'class' => 'productName'
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'callback' => 'setStatus',
                'search' => false,
                'orderby' => false
            )
        );
    }

    public function getQtyPacked($id_order_detail, $object)
    {
        $tpl = $this->createTemplate('add_qty.tpl');

        $tpl->assign('ean', $object['product_ean13']);
        $tpl->assign('upc', $object['product_upc']);
        $tpl->assign('id', $id_order_detail);
        $tpl->assign('max', $object['product_quantity']);

        return $tpl->fetch();
    }

    public function getLocation($id_order_detail, $object)
    {
        return BmsOrderPreparationProductHelper::getProductLocation($object['product_id'], $object['product_attribute_id']);
    }

    public function setStatus($id_order_detail, $object)
    {
        $tpl = $this->createTemplate('status.tpl');

        $tpl->assign('id', $id_order_detail);

        return $tpl->fetch();
    }

    public function renderList()
    {
        $this->_select = 'id_image, id_order_detail as QtyPacked, id_order_detail as status, id_order_detail as location';

        $this->_where .= "AND id_order=" . (int) $this->id_order;
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = a.`product_id` AND image_shop.`cover` = 1 AND image_shop.id_shop = ' . $this->context->shop->id . ') ';

        $order = new Order($this->id_order);
        $state = new OrderState($order->current_state);
        $carrier = new Carrier($order->id_carrier);
        $invoice_address = new Address((int) $order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $datas = BmsOrderPreparationProductHelper::getAllProduct(array(
            $order
        ));

        $tpl = $this->createTemplate("header.tpl");
        $tpl->assign('customerName', $customer->firstname . ' ' . $customer->lastname);
        $tpl->assign('shippingMethod', $carrier->name);
        $tpl->assign('shippingCountry', $invoice_address->country);
        $tpl->assign('orderStatus', $state->name[$this->context->language->id]);
        $tpl->assign('productCount', $datas['qte']);
        $tpl->assign('id_order', sprintf("%'09d", (int) $this->id_order));
        $tpl->assign('reference', $order->reference);

        $tpl->assign('nokSoundUrl', Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/sound/wrong.mp3');
        $tpl->assign('okSoundUrl', Context::getContext()->shop->getBaseURL(true) . 'modules/bmsorderpreparation/views/sound/correct.mp3');

        $footer = $this->createTemplate("footer.tpl");
        $footer->assign('weight', round(BmsOrderPreparationInProgress::getWsWeightNumber((int) $order->id), 2));
        $footer->assign('id_order', $this->id_order);

        Media::addJsDef(array(
            'id_order' => (int) $this->id_order
        ));
        Media::addJsDef(array(
            'totalToShip' => $datas['qte']
        ));
        Media::addJsDef(array(
            'errorSubmit' => $this->l('Please scan all products')
        ));
        Media::addJsDef(array(
            'errorWeight' => $this->l('Weight must to be a numer')
        ));
        Media::addJsDef(array(
            'urlSubmit' => CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderPackingTab', array('action' => 'submit', 'id_order' => $this->id_order), $this->context)
        ));

        return $tpl->fetch() . parent::renderList() . $footer->fetch();
    }

    /**
     * Pack the order
     */
    public function processSubmit()
    {
        $id_order = (int) $this->id_order;
        $order = new Order($id_order);
        $statut = Configuration::get('BMS_OP_STATUT_FOR_PACKED');

        /**
         * setCurrentState changes order status, append entry in history and notify customer according to status settings
         */
        $order->setCurrentState($statut, (int) Context::getContext()->employee->id);

        //check if we must process shipping template
        $template = BmsOrderPreparationShippingTemplate::getTemplateByCarrier($order->id_carrier);

        if ($template)
        {
            switch($template->id_template_type)
            {
                case 1:

                    break;  //CSv, nothing to do
                case 2:

                    try
                    {
                        $obj = new SwissPost();
                        $obj->submitShipment($order);
                    }
                    catch(Exception $ex)
                    {
                        die($ex->getMessage());
                    }
                    break;
            }
        }

        $this->redirect_after = CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderPackingConfirmTab', array('id_order' => $this->id_order), $this->context);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->context->smarty->assign('help_link', null);
    }

    public function setBmsMedia()
    {
        Media::addJsDef(array(
            'urlOrderPacking' => $this->context->link->getAdminLink("AdminOrderPacking"),
            'errorMessage' => $this->l('Unknown barcode'),
            'noPackedMessage' => $this->l('No Packed'),
            'missingMessage' => $this->l('missing'),
            'packedMessage' => $this->l('Packed')
        ));
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/orderPacking.js');
    }

    protected function getProducts($id_order)
    {
        $order = new Order((int) $id_order);
        $products = $order->getProducts();

        foreach ($products as &$product) {
            if ($product['image'] != null) {
                $name = 'product_mini_' . (int) $product['product_id'] . (isset($product['product_attribute_id']) ? '_' . (int) $product['product_attribute_id'] : '') . '.jpg';
                // generate image cache, only for back office
                $product['image_tag'] = ImageManager::thumbnail(_PS_IMG_DIR_ . 'p/' . $product['image']->getExistingImgPath() . '.jpg', $name, 45, 'jpg');
                if (file_exists(_PS_TMP_IMG_DIR_ . $name)) {
                    $product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_ . $name);
                } else {
                    $product['image_size'] = false;
                }
            }
        }

        ksort($products);

        return $products;
    }

    protected function setFilter()
    {
        $value = Tools::getValue('filter');
        if (empty($this->filter) && ! empty($value)) {
            $this->filter = Tools::getValue('filter');
        }
    }
}
