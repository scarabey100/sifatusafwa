<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
include_once _PS_MODULE_DIR_.'prestarealtimesync/prestarealtimesync.php';
include_once _PS_MODULE_DIR_.'prestarealtimesync/classes/PrestaerpClassInclude.php';

class AdminErpManualOrdersController extends AdminController{
    protected $statuses_array = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;

        parent::__construct();

        $this->_select = '
		a.id_currency,
		a.id_order AS id_pdf,
		CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		IF((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
		country_lang.name as cname,
		IF(a.valid, 1, 0) badge_success';

        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
		INNER JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery
		INNER JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
		INNER JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int) $this->context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->trans('Reference', array(), 'Admin.Global'),
            ),
            'new' => array(
                'title' => $this->trans('New client', array(), 'Admin.Orderscustomers.Feature'),
                'align' => 'text-center',
                'type' => 'bool',
                'tmpTableFilter' => true,
                'orderby' => false,
            ),
            'customer' => array(
                'title' => $this->trans('Customer', array(), 'Admin.Global'),
                'havingFilter' => true,
            ),
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->trans('Company', array(), 'Admin.Global'),
                    'filter_key' => 'c!company',
                ),
            ));
        }

        $this->fields_list = array_merge($this->fields_list, array(
            'total_paid_tax_incl' => array(
                'title' => $this->trans('Total', array(), 'Admin.Global'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true,
            ),
            'payment' => array(
                'title' => $this->trans('Payment', array(), 'Admin.Global'),
            ),
            'osname' => array(
                'title' => $this->trans('Status', array(), 'Admin.Global'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'date_add' => array(
                'title' => $this->trans('Date', array(), 'Admin.Global'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        ));

        if (Country::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
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

            $part1 = array_slice($this->fields_list, 0, 3);
            $part2 = array_slice($this->fields_list, 3);
            $part1['cname'] = array(
                'title' => $this->trans('Delivery', array(), 'Admin.Global'),
                'type' => 'select',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname',
            );
            $this->fields_list = array_merge($part1, $part2);
        }

        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_ORDER;

        if (Tools::isSubmit('id_order')) {
            // Save context (in order to apply cart rule)
            $order = new Order((int) Tools::getValue('id_order'));
            $this->context->cart = new Cart($order->id_cart);
            $this->context->customer = new Customer($order->id_customer);
        }
        $this->bulk_actions['exportToOdoo'] = array('text' => $this->l('
        Synchronize with Odoo'), 'icon' => 'icon-ok-sign');
        $index               = count($this->_conf)+1;
        $this->_conf[$index] = $this->l('All Selected Order(s) has
        been successfully exported to Odoo.');

        // $this->fields_list['erp_id'] = array(
        //     'title' => $this->l('Exported to Odoo'),
        //     'align' => 'center',
        //     'type' => 'bool',
        //     'width' => 'auto',
        //     'search' => false,
        //     'icon' => array(
        //     1 => array(
        //         'src' => 'enabled.gif',
        //         'alt' => $this->l('Synchronized')
        //     ),
        //     0 => array(
        //         'src' => 'disabled.gif',
        //         'alt' => $this->l('Need Synchronization')
        //     ),
        //     ),
        // );

    // $this->_join .='LEFT JOIN `'._DB_PREFIX_.'erp_order_merge`
    // om ON (om.`prst_order_id` = a.`id_order`)';
    // $this->_select .=',if(om.`erp_order_id` is null, 0, 1) as erp_id';
    }

    public static function setOrderCurrency($echo, $tr)
    {
        if (!empty($tr['id_currency'])) {
            $idCurrency = (int) $tr['id_currency'];
        } else {
            $order = new Order($tr['id_order']);
            $idCurrency = (int) $order->id_currency;
        }

        return Tools::displayPrice($echo, $idCurrency);
    } 
    public function createErpOrder($id_order)
    {
        try {
            if ($id_order) {
                $obj = new Prestarealtimesync();
                                $obj->hookDisplayOrderConfirmation(array('id_order'=>$id_order),$order_id = (int)$id_order);
                return array('status'=>true);
            }
        } catch (Exception $e) {
            return array('status'=>false,'message'=>'Order(ID:'.$id_order.')<br />'. $e->getMessage());
        }
    }


    public function getErpConnection()
    {
        if (!class_exists('xmlrpc_client')) {
            include _PS_MODULE_DIR_.'/prestarealtimesync/classes/xmlrpc.inc';
        }
        $client = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl") . ":"
        . Configuration::getGlobalValue("ErpPort") . "/xmlrpc/object");
        $sock   = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl") . ":"
        . Configuration::getGlobalValue("ErpPort") . "/xmlrpc/common");
        $msg    = new xmlrpcmsg('login');
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), 'string'));
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpUsername"), 'string'));
        if(Configuration::getGlobalValue("Erpcode")){
			$msg->addParam(new xmlrpcval(Configuration::getGlobalValue("Erpcode"), "string"));
		}
		else{
			$msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), 'string'));}
        $resp = $sock->send($msg);
        if (!$resp->faultCode()) {
            $userId = $resp->value()->scalarval();
            if ($userId > 0) {
                return array(
                'sock'    => $sock,
                'client'  => $client,
                'user_id' => $userId,
                'status'  => true
                );
            } else {
                $log = new pob_log();
                $log->logMessage(__FILE__, __LINE__, 'Authentication Error', 'CRITICAL');
                return array(
                'status'  =>false
                );
            }
        } else {
            $log = new pob_log();
            $log->logMessage(__FILE__, __LINE__, $resp->raw_data, 'Connection Error');
            return array(
                'status'  =>false
                );
        }
    }


    public function processBulkexportToOdoo()
    {
        $return = true;
        $message='';
        $connect = $this->getErpConnection();
        if ($connect['status']==true) {
            try {
                $data=$this->boxes;

                if (is_array($data) && (count($data))) {
                    //Deleting data
                    foreach ($data as $id_data) {
                        // if ($this->checkOrder($id_data)) {
                            $response=$this->createErpOrder($id_data);

                            if (!$response['status']) {
                                $return = false;
                                $message=$message.'<br />'.$response['message'];
                            }
                        // }
                    }
                }
            } catch (Exception $e) {
                $return = false;
                $message=$this->l($e->getMessage());
            }
        } else {
            $return = false;
            $message= 'Connection Error !!!';
        }

        if ($return) {
            $index       = count($this->_conf);
            Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
        } else {
            $this->errors[] = Tools::displayError('An error occurred while
            exporting Orders. Error:- '.$message);
        }
    }

    // public function checkOrder($id_order)
    // {
    //     $check = Db::getInstance()->getRow("SELECT `prst_order_id`  from
    //     `" . _DB_PREFIX_ . "erp_order_merge` where `prst_order_id`=" . (int)$id_order . "");
    //     if (empty($check)) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}