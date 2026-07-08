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
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';
require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/OrderPreparationSession.php';

if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
    require_once _PS_MODULE_DIR_ . 'advancedstock/classes/Model/AdvancedStockExtendedOrderDetail.php';

class AdminOrderTabController extends ModuleAdminController
{
    protected $filter = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'orders';
        $this->className = 'Order';
        $this->identifier = 'id_order';
        $this->lang = false;
        $this->explicitSelect = true;
        parent::__construct();
        $this->list_no_link = true;

        $this->setActions();
        // $this->override_folder = 'purchaseOrder/';
        $this->setFieldsListe();
    }

    public function init()
    {
        parent::init();
        if (Tools::getValue('filter')) {
            $this->filter = Tools::getValue('filter');
            $str_filter = '&filter=' . $this->filter;
            self::$currentIndex .= $str_filter;
            $this->list_id = $this->filter;

            $variable = Tools::getIsset('submitReset' . $this->list_id);

            if ($variable) {
                /* Cancel all filters for this tab */
                $this->action = 'reset_filters';
            }

            $messages = OrderPreparationSession::getMessages();
            foreach($messages as $message)
            {
                $this->errors[] = $message['message'];
            }

        }
        if (Tools::getValue('shippingStep') == 1) {
            self::$currentIndex .= '&shippingStep=1';
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
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        foreach ($carriers as $carrier) {
            $this->carriers_array[$carrier['id_carrier']] = $carrier['name'];
        }

        $this->fields_list = array(

            'id_order' => array(
                'title' => $this->l('Id'),
                'search' => false,
                'orderby' => false,
                'filter' => false
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'callback' => 'setLinkToOrder'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true
            ),
            'carrier' => array(
                'title' => $this->l('Carrier'),
                'type' => 'select',
                'list' => $this->carriers_array,
                'filter_key' => 'cr!id_carrier',
                'filter_type' => 'int',
                'order_key' => 'carrier'
            ),

            'cname' => array(
                'title' => $this->l('Delivery'),
                'type' => 'select',
                'list' => $this->getCountry(),
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname'
            ),
            'payment' => array(
                'title' => $this->l('Payment')
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
            'products' => array(
                'title' => $this->l('products'),
                'callback' => 'getTableProduct',
                'search' => false,
                'orderby' => false
            )
        );

        if (Tools::getValue('shippingStep') == 1) {
            $this->fields_list['tracking_input'] = array(
                'title' => $this->l('Tracking'),
                'callback' => 'getTrackingInput',
                'search' => false,
                'orderby' => false
            );
        }
    }

    public function setLinkToOrder($reference, $object)
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'helpers/link.tpl', $this->context->smarty);

        $tpl->assign('text', $reference);
        $tpl->assign('target', '_parent');

        $tpl->assign('url', CompatibilityOrderPreparation::getOrderLink($object['id_order'], $this->context));

        return $tpl->fetch();
    }

    public function getTableProduct($id_order, $object)
    {
        $products = $this->getProducts($id_order);

        $tpl = $this->createTemplate('products.tpl');
        $tpl->assign('products', $products);
        return $tpl->fetch();
    }

    public function getTrackingInput($id_order, $object)
    {
        $order = new Order($id_order);

        $order_state = $order->getCurrentOrderState();
        // if($order_state->shipped){
        $shipping_number = $order->getWsShippingNumber();
        $tpl = $this->createTemplate('tracking_input.tpl');
        $tpl->assign('shipping_number', $shipping_number);
        $tpl->assign('order_id', $id_order);
        return $tpl->fetch();
        // } else {
        // return '';
        // }
    }

    public function renderList()
    {
        $this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`' . ',' . 'country_lang.name as cname,osl.`name` AS `osname`,os.`color`' . ',' . 'a.id_order as products,a.id_order,cr.name as carrier';

        if (Tools::getValue('shippingStep') == 1) {
            $this->_select = $this->_select . ", id_order as tracking_input";
        }
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)' . ' ' . 'INNER JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery' . ' ' . 'INNER JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country' . ' ' . 'INNER JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int) $this->context->language->id . ')' . ' ' . 'LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)' . ' ' . 'LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')' . ' ' . 'LEFT JOIN `' . _DB_PREFIX_ . 'carrier` cr ON (cr.`id_carrier` = a.`id_carrier`)' . ' ' . '';

        $ids = null;

        switch ($this->filter) {
            case 'readyToShip':
                $ids = Configuration::get("BMS_OP_STATUT_READY_TO_SHIP");
                break;
            case 'backorder':
                $ids = Configuration::get("BMS_OP_STATUT_BACKORDERS");
                break;
            case 'pending':
                $ids = Configuration::get("BMS_OP_STATUT_PENDING");
                break;
            case 'holded':
                $ids = Configuration::get("BMS_OP_STATUT_HOLDED");
                break;
        }

        $query = new DbQuery();
        $query->select('id_order')->from('bms_orderpreparation_inprogress');

        if ($this->filter != 'inProgress') {
            if ($ids) {
                $this->_where .= "AND current_state in(" . pSQL($ids) . ") ";
            } else {
                $this->_where .= "AND current_state =-1 ";
            }
            $this->_where .= "AND id_order not in($query)";
        } else {
            $this->_where .= "AND id_order in($query)";
        }

        //append additionnal conditions related to advancedstock module (only for ready to ship & backorder tabs)
        if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
        {
            switch ($this->filter) {
                case 'readyToShip':
                    $oosOrderIds = $this->getAdvancedStockOosOrderIds($ids);
                    if (is_array($oosOrderIds) && count($oosOrderIds) > 0)
                        $this->_where .= "AND id_order NOT in(".implode(',', $oosOrderIds).")";
                    break;
                case 'backorder':
                    $oosOrderIds = $this->getAdvancedStockOosOrderIds($ids);
                    if (is_array($oosOrderIds) && count($oosOrderIds) > 0)
                        $this->_where .= "AND id_order in(".implode(',', $oosOrderIds).")";
                    break;
            }
        }

        $html = parent::renderList();

        //fix for JS issue with gamification module
        $gamifivationFixTpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'GamificationFix.tpl', $this->context->smarty);
        $html .= $gamifivationFixTpl->fetch();

        return $html;
    }

    /**
     * Return order IDS for which at least one product is not reserved
     * Considering only orders having status passed in parameter
     *
     * @param $statuses
     * @return array
     */
    public function getAdvancedStockOosOrderIds($statuses)
    {
        $sql = '
                    SELECT
                      DISTINCT od.id_order
                    FROM
                      `' . _DB_PREFIX_ . 'bms_advancedstock_extended_order_detail` eod
                        INNER JOIN `' . _DB_PREFIX_ . 'order_detail` od ON eod_order_detail_id = id_order_detail
                        INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON od.id_order = o.id_order
                    WHERE
                        (eod_qty_to_ship > 0)
                        AND
                        (eod_qty_to_ship > eod_reserved_qty)
                    ';
        if (is_array($statuses) && count($statuses) > 0)
            $sql .= ' AND o.current_state in(' . pSQL($statuses) . ')';
        $collection = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        $ids = array();
        foreach($collection as $item)
            $ids[] = $item['id_order'];

        return $ids;
    }

    public function processAddToInProgress()
    {
        $id_order = Tools::getValue('id_order', null);
        if ($id_order) {
            $errors = BmsOrderPreparationInProgress::append((int) $id_order);
            foreach($errors as $error)
                OrderPreparationSession::addMessage($error, true);
            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
        }
    }

    public function processDelete()
    {
        $id_order = Tools::getValue('id_order', null);
        if ($id_order) {
            $restore = Configuration::get('BMS_OP_RESTORE_PREVIOUS_STATUS');
            BmsOrderPreparationInProgress::remove((int)$id_order, $restore);
            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
        }
    }

    public function processBulkAddToInProgress()
    {
        if (Tools::isSubmit($this->filter . 'Box')) {
            $errors = BmsOrderPreparationInProgress::append(Tools::getValue($this->filter . 'Box'));
            foreach($errors as $error)
                OrderPreparationSession::addMessage($errors, true);
        }
        if (!count($this->errors)) {
            Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->context->smarty->assign('help_link', null);
    }

    public function setBmsMedia()
    {
        if (Tools::getValue('shippingStep') == 1) {
            $this->addJquery();
            $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/tracking_input.js');
            $link = $this->context->link->getAdminLink('AdminOrderPreparationShipping', true) . '&action=updateTrackingNumber';
            Media::addJsDef(array(
                'ajaxUrl' => $link
            ));
        }
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
            $product['url'] = CompatibilityOrderPreparation::getProductLink($product['product_id'], $this->context);

            if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
            {
                $class = "AdvancedStockExtendedOrderDetail";
                $method = "getEodFromOdId";
                $extendedOrderItem = $class::{$method}($product['id_order_detail']);
                if ($extendedOrderItem->getQtyToReserve() > 0) {
                    $product['color'] = '#ff0000';
                    $product['product_quantity'] = ((int)$extendedOrderItem->eod_reserved_qty).'/'.((int)$extendedOrderItem->eod_qty_to_ship);
                }
                else
                    $product['color'] = '#009933';
            }
            else {
                $product['color'] = '#000000';
                $product['product_quantity'] = (int)$product['product_quantity'];
            }


        }

        ksort($products);

        return $products;
    }

    protected function getCountry()
    {
        if (ObjectModel::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT DISTINCT c.id_country, cl.`name`
			FROM `' . _DB_PREFIX_ . 'orders` o
			' . Shop::addSqlAssociation('orders', 'o') . '
			INNER JOIN `' . _DB_PREFIX_ . 'address` a ON a.id_address = o.id_address_delivery
			INNER JOIN `' . _DB_PREFIX_ . 'country` c ON a.id_country = c.id_country
			INNER JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $this->context->language->id . ')
			ORDER BY cl.name ASC');

            $country_array = array();
            foreach ($result as $row) {
                $country_array[$row['id_country']] = $row['name'];
            }

            return $country_array;
        }
    }

    public function displayAddToInProgressLink($token, $id)
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'helpers/list_actions.tpl', $this->context->smarty);

        $tpl->assign(array(
            'href' => CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderTab', array('action' => 'addToInProgress', 'id_order' => (int) $id, 'filter' => $this->filter), $this->context),
            'action' => $this->l('add To In Progress'),
            'class' => "edit",
            'icon' => "icon-plus"
        ));

        return $tpl->fetch();
    }

    protected function setActions()
    {
        $this->setFilter();

        if ($this->filter != 'inProgress') {
            $this->addRowAction('addToInProgress');
            $this->bulk_actions = array(
                'addToInProgress' => array(
                    'text' => $this->l('Add To In Progress'),
                    'icon' => 'icon-plus'
                )
            );
        } else {
            if (Tools::getValue('shippingStep') != 1) {
                $this->addRowAction('remove');
                $this->bulk_actions = array();
            }
        }
    }

    protected function setFilter()
    {
        $filterValue = Tools::getValue('filter');
        if (empty($this->filter) && ! empty($filterValue)) {
            $this->filter = Tools::getValue('filter');
        }
    }

    public function displayRemoveLink($token, $id)
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'helpers/list_actions.tpl', $this->context->smarty);
        $tpl->assign(array(
            'href' => CompatibilityOrderPreparation::getPrestashopUrl('AdminOrderTab', array('action' => 'delete', 'id_order' => (int) $id, 'filter' => $this->filter), $this->context),
            'action' => $this->l('Remove'),
            'class' => "delete",
            'icon' => "icon-trash"
        ));

        return $tpl->fetch();
    }
}
