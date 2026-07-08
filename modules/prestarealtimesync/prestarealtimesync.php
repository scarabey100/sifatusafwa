<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// error_reporting(E_ALL);
// ini_set('display_errors', 'on');
// ini_set('display_startup_errors', true);
// ini_set("log_errors", 1);
// ini_set("error_log", __DIR__ . "/wk_error.log");

if (!defined('_PS_VERSION_'))
    exit;

require_once 'classes/PrestaerpClassInclude.php';
// $base = Tools::getHttpHost(true).__PS_BASE_URI__;

class Prestarealtimesync extends Module {
    // const INSTALL_SQL_FILE = 'install.sql';

    public function __construct(){
        $this->name = 'prestarealtimesync';
        $this->tab = 'administration';
        $this->version = '1.0';
        $this->author = 'WebKul Software Pvt. Ltd.';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('POB : PrestaShop-Odoo Bridge');
        $this->description = $this->l('This module provides a way to export your PrestaShop`s data to Odoo in real time via multichannel module. Also, provides synchronization of order(s) from Prestashop to Odoo.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall it?');

    }

    public function insertTab() {
        $this->installTab('OdooRealTimeSync','Odoo Real Time Sync');
        Configuration::updateGlobalValue('erp-prst-tab-id',(int)Tab::getIdFromClassName('OdooRealTimeSync'));
        $this->installTab('AdminWkErpConfiguration', 'Odoo Configuration', 'OdooRealTimeSync');
        if(_PS_VERSION_ >='1.7.7')
            $this->installTab('AdminErpManualOrders','Export Manual Orders','OdooRealTimeSync');
        return true;
    }

    public function installTab($class_name,$tab_name,$tab_parent_name=false) {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $class_name;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $tab_name;
        if($tab_parent_name) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tab_parent_name);
        }else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        return $tab->add();
    }

    public function changePosition(){
        $a = Db::getInstance()->execute("UPDATE  `" . _DB_PREFIX_ . "tab` SET `position`=7 where `class_name`='OdooRealTimeSync'");
        return true;
    }

    public function deleteTab() {
        $this->uninstallTab('AdminWkErpConfiguration');
        $this->uninstallTab('OdooRealTimeSync');
        $this->uninstallTab('AdminErpManualOrders');
        return true;
    }

    public function uninstallTab($class_name) {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab){
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        else
            return false;
    }

    public function install(){
        if (!parent::install() OR !$this->insertTab() OR !$this->changePosition() OR !$this->registerHook('actionPaymentConfirmation') OR !$this->registerHook('displayOrderConfirmation') OR !$this->registerHook('actionObjectProductUpdateAfter') OR !$this->registerHook('actionObjectCategoryUpdateAfter') OR !$this->registerHook('actionValidateOrder') OR !$this->registerHook('displayBackOfficeHeader'))
        // OR !$this->registerHook('actionOrderStatusUpdate')
            return false;
        return true;
    }

    public function uninstall(){
        $this->deleteTab();
        $this->deleteConfiguration();
        if (!parent::uninstall())
            return false;
        else
            return true;
    }

    public function hookDisplayBackOfficeHeader(){
        $this->context->controller->addCss($this->_path.'views/css/admin/css/prestaerpmenu.css');
        $this->context->controller->addJS($this->_path . 'views/js/fieldscript.js');
    }

    public function deleteConfiguration() {
        $c1=Configuration::deleteByName('ErpUrl');
        $c2=Configuration::deleteByName('ErpPort');
        $c3=Configuration::deleteByName('ErpDatabase');
        $c4=Configuration::deleteByName('ErpUsername');
        $c5=Configuration::deleteByName('ErpPassword');
        $c7=Configuration::deleteByName('erp-prst-tab-id');
        $c8=Configuration::deleteByName('ErpTestConnection');
        
        if(!$c1 || !$c2 || !$c3 || !$c4 || !$c5 || !$c7 || !$c8)
            return False;
        else
            return True;
    }

    public function get_erp_connection(){
        if (!class_exists('xmlrpc_client'))
            include 'xmlrpc.inc';
        $sock = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/common");
        $client = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/object");
        $msg    = new xmlrpcmsg('login');
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), 'string'));
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpUsername"), 'string'));
        if(Configuration::getGlobalValue("Erpcode")){
            $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("Erpcode"), 'string'));
        }
        else{
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), 'string'));}
        $resp = $sock->send($msg);
        if (!$resp->faultCode()){
            $userId = $resp->value()->scalarval();
            if ($userId > 0){
            return array(
                'client'  =>    $client,
                'user_id' =>    $userId,
                'status'  =>    True
                );}
        else{
            $log = new pob_log();
            $log->logMessage(__FILE__,__LINE__,'Authentication Error','CRITICAL');
            return array(
                'status'  =>False
                );
            }
        }
        else{
            $log = new pob_log();
            $log->logMessage(__FILE__,__LINE__,$resp->raw_data,'CRITICAL: ');
            return array(
                'status'  =>False
                );
        }
    }

    // public function hookActionOrderStatusUpdate($params){
    //     $db = Configuration::getGlobalValue("ErpDatabase");
    //     $pwd = Configuration::getGlobalValue("ErpPassword");
    //     $model = "multi.channel.sale";
    //     $method = "sync_order_by_status_presta";
    //     $erp_sync = Configuration::getGlobalValue("ErpSyncField");
    //     $ecommerce = (array("ecommerce" => new xmlrpcval("prestashop" ,"string"))); 
    //     $order_id = $params["id_order"];
    //     $status = $params["newOrderStatus"];
    //     $status = $status->id;
    //     $payment_method = Db::getInstance()->getRow("SELECT `payment`  from `" . _DB_PREFIX_ . "orders` where `id_order`= " .$order_id);
    //     $payment_method = $payment_method["payment"];
    //     $channel_id = Configuration::getGlobalValue('ErpInstanceConfId');
    //     $vals_list = array(
    //         new xmlrpcval($channel_id, "int"),
    //         new xmlrpcval($order_id, "int"),
    //         new xmlrpcval($status, "int"),
    //         new xmlrpcval($payment_method,"string"),
    //     );
    //     $context = array(
    //         "context"=> new xmlrpcval($ecommerce, "struct")
    //         );

    //     if ($erp_sync == 1){
    //         $wkerpconf = new WkErpConfiguration;
    //         $resp = $wkerpconf->odooMethodCall($db, $pwd, $model, $method, $vals_list,$context);
    //         print_r($resp->raw_data);
    //         die;
    //     }
    //     if ($resp) {
    //         $log = new pob_log();
    //         $log->logMessage(__FILE__,__LINE__,$resp->raw_data);
    //     }   
    // }


    public function hookDisplayOrderConfirmation($params ,$order_id=False){
        $order = $params['order'];   
        if ($order || sizeof($params)>=7) {
            // Order is new
            return true;  
        }
        $log = new pob_log();
        $id_order = false;
        $total_line_array = array();
        $product_list = array();
        $instance_id = Configuration::getGlobalValue('ErpInstanceName1');
        if(_PS_VERSION_ <'1.7.7'){
            if (isset($params['id_order']->id))
                $id_order = $params['id_order']->id;
            else
                $id_order = $params['id_order'];
        }
        if(!$id_order){
            $id_order = (Tools::getValue('id_order')) ? Tools::getValue('id_order'):$order_id;}
        if ($id_order){
            $shipping_price = Db::getInstance()->getRow("SELECT `id_carrier`,`shipping_cost_tax_excl`  from `" . _DB_PREFIX_ . "order_carrier` where `id_order`=" .$id_order. " ORDER BY date_add DESC");
            $carrier_name = Db::getInstance()->getRow("SELECT `name` from `" . _DB_PREFIX_ . "carrier` where `id_carrier`=" .$shipping_price['id_carrier']. "");

            $order         = Db::getInstance()->getRow("SELECT `delivery_date`,`current_state`,`id_customer`,`id_address_delivery`,`id_address_invoice`,`id_currency`,`id_shop`,`total_discounts_tax_incl`,`total_discounts_tax_excl`,`reference`,`total_paid_tax_incl`,`total_shipping_tax_incl`,`total_shipping_tax_excl`,`total_paid`,`date_add`,`date_upd`,`invoice_date`,`total_paid`,`carrier_tax_rate`,`module`  from `" . _DB_PREFIX_ . "orders` where `id_order`=" . $id_order. "");

            $order_details   = Db::getInstance()->executeS("SELECT `product_id`,`reduction_percent`,`product_attribute_id`,`id_order_detail`,`product_quantity`,`product_name`,`reduction_amount_tax_incl`,`original_product_price`,`product_price`,`unit_price_tax_excl`,`reduction_amount_tax_excl`  from `" . _DB_PREFIX_ . "order_detail` where `id_order`=" .$id_order. "");
            $customer_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "customer` where `id_customer`=" .$order['id_customer']. "");
            foreach ($order_details as $details) {
                $tax_id = Db::getInstance()->getRow("SELECT `id_tax`  from `" . _DB_PREFIX_ . "order_detail_tax` where `id_order_detail`=" . $details['id_order_detail'] . "");

                $price =($details['unit_price_tax_excl']);
                $tax = array();
                if ($tax_id){
                if ($tax_id['id_tax']>0){
                    $tax = Db::getInstance()->getRow("SELECT `rate`  from `" . _DB_PREFIX_ . "tax` where `id_tax`=" . $tax_id['id_tax']. "");
                    $item_rate = round(array_shift($tax));
                    $tax =array(
                        'rate' => $item_rate,
                    );
                }}
                $product_dict = $this->get_product_data($details['product_id'], $instance_id);
                $line_array= array(
                    'line_name' => $details['product_name'],
                    'line_product_id' => $details['product_id'],
                    'line_price_unit' => $price,
                    'line_taxes'      => array($tax),
                    'line_product_uom_qty' => $details['product_quantity'],
                    'line_product_default_code'=> $product_dict['default_code'],
                    'line_source' => 'product'
                );
                if ($details['product_attribute_id']==0){
                    $line_array['line_variant_ids'] = "No Variants";
                }
                else{
                    $line_array['line_variant_ids'] = $details['product_attribute_id'];
                }
                // $product_dict = $this->get_product_data($details['product_id'], $instance_id);
                array_push($product_list,$product_dict);
                $line_tuple=array(0,0,$line_array);
                array_push($total_line_array,$line_tuple);
            }
            if ($shipping_price['id_carrier']>0){
                $line_array = array(
                    'line_product_id' => 'shipping',
                    'line_price_unit' => 
                        $shipping_price['shipping_cost_tax_excl'],
                    'line_product_uom_qty' => 1,
                    'line_name' => 'shipping',
                    'line_source' => 'delivery',
                    // 'line_taxes' => new xmlrpcval( , ),
                );
                $line_tuple=array(0,0,$line_array);
                array_push($total_line_array,$line_tuple);
            }
            if (($order['total_discounts_tax_incl']>0) OR ($order['total_discounts_tax_excl']>0)){
                $discount_line_array = array(
                    'line_product_id' => 'Discount',
                    'line_price_unit' => array(
                            'total_discounts_tax_incl' => $order['total_discounts_tax_incl'],
                            'total_discounts_tax_excl' => $order['total_discounts_tax_excl'], 
                        ), 
                    'line_product_uom_qty' =>  1,
                    'line_name' => 'Discount',
                    'line_source' => 'discount',
                );
                $line_tuple=array(0,0,$discount_line_array);
                array_push($total_line_array, $line_tuple);
            }
            $GLOBALS['kwargs']['product_vals'] = $product_list;
            $curr_code = Db::getInstance()->executeS("SELECT `iso_code` FROM `"._DB_PREFIX_."currency` WHERE `id_currency`=".$order['id_currency']."");
            $vals = array(
                'name'                  => $order['reference'],
                'store_id'              => $id_order,
                'payment_method'        => $order['module'],
                'order_state'           => $order['current_state'],
                'partner_id'            => $order['id_customer'],
                'currency'              => $curr_code[0]['iso_code'],
                'carrier_id'            => $carrier_name['name'],
                'line_type'             => 'multi',
                'customer_name'         => $customer_data['firstname']." ".$customer_data['lastname'],
                'customer_email'        => $customer_data['email'],
                'channel_id'            => $instance_id,
                'invoice_partner_id'    => $order['id_address_invoice'],
                'invoice_email'         => $customer_data['email'],
                'shipping_partner_id'   => $order['id_address_delivery'],
                'shipping_email'        => $customer_data['email'],
                'line_ids'              => $total_line_array,
                "date_order"            => $order['date_add'],
                "confirmation_date"     => $order["delivery_date"],
                "date_invoice"          => $order['invoice_date']

            );
            $invoice_address_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "address` where `id_address`=" .$order['id_address_invoice']. "");
            $invoice_address_state_data =  Db::getInstance()->getRow(
                                                                        "SELECT *  from `" . _DB_PREFIX_ . "state`
                                                                        where `id_state`=" . (int)$invoice_address_data['id_state'] . ""
                                                                    );
            $invoice_address_country_data =  Db::getInstance()->getRow(
                                                                        "SELECT *  from `" . _DB_PREFIX_ . "country`
                                                                        where `id_country`=" . (int)$invoice_address_data['id_country'] . ""
                                                                    );                                                        
            $invoice_address = array(

                'invoice_name'     => $invoice_address_data['firstname'].$invoice_address_data['lastname'],
                'invoice_phone'    => $invoice_address_data['phone'],
                'invoice_street'    => $invoice_address_data['address1'],
                'invoice_street2'    => $invoice_address_data['address2'],
                'invoice_zip'    => $invoice_address_data['postcode'],
                'invoice_city'    => $invoice_address_data['city'],
                'invoice_state_name'    => $invoice_address_state_data['name'],
                'invoice_state_id'    => $invoice_address_state_data['iso_code'],
                'invoice_country_id'  => $invoice_address_country_data['iso_code']              
            );          
            if ($order['id_address_delivery'] != $order['id_address_invoice']){
                $shipping_address_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "address` where `id_address`=" .$order['id_address_delivery']. "");
                $shipping_address_state_data =  Db::getInstance()->getRow(
                                                                            "SELECT *  from `" . _DB_PREFIX_ . "state`
                                                                            where `id_state`=" . (int)$shipping_address_data['id_state'] . ""
                                                                        );
                $shipping_address_country_data =  Db::getInstance()->getRow(
                                                                            "SELECT *  from `" . _DB_PREFIX_ . "country`
                                                                            where `id_country`=" . (int)$shipping_address_data['id_country'] . ""
                                                                        );                                                        
                $shipping_address = array(

                    'shipping_name'     => $shipping_address_data['firstname'].$shipping_address_data['lastname'],
                    'shipping_phone'    => $shipping_address_data['phone'],
                    'shipping_street'    => $shipping_address_data['address1'],
                    'shipping_street2'    => $shipping_address_data['address2'],
                    'shipping_zip'    => $shipping_address_data['postcode'],
                    'shipping_city'    => $shipping_address_data['city'],
                    'shipping_state_name'    => $shipping_address_state_data['name'],
                    'shipping_state_id'    => $shipping_address_state_data['iso_code'],
                    'shipping_country_id'  => $shipping_address_country_data['iso_code'] 
                );
                $order_vals = array_merge($vals,$invoice_address,$shipping_address);
            }else{
                $order_vals = array_merge($vals,$invoice_address);
            }  
            $vals_list =  array((int)$instance_id,array($order_vals));
            $vals_list_serialized = php_xmlrpc_encode($vals_list);
            $global_serialized = php_xmlrpc_encode($GLOBALS['kwargs']);

            $connect = $this->get_erp_connection();
                        if ($connect['status']==true){
                $client = $connect['client'];
                $userId = $connect['user_id'];
                $msg1 = new xmlrpcmsg('execute_kw');
                $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
                $msg1->addParam(new xmlrpcval($userId, "int"));
                if(Configuration::getGlobalValue("Erpcode")){
                    $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("Erpcode"), "string"));
                }
                else{
                    $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));}
                $msg1->addParam(new xmlrpcval('multi.channel.sale', 'string'));
                $msg1->addParam(new xmlrpcval('sync_order_feeds', 'string'));
                $msg1->addParam($vals_list_serialized);
                $msg1->addParam($global_serialized);
                $resp = $client->send($msg1);
                $response_array=$resp->value()->me['struct'];
                $status  = $response_array['kwargs'];
                $status_message   = $response_array['message']->me['string'];
                $log->logMessage(__FILE__,__LINE__,'Order id '.$id_order.' Export response: '.$status_message.'','INFO');
                //$log->logMessage(__FILE__,__LINE__,'Order id '.$id_order.' kwrgs resp : '.$status.'','INFO');
                return true;
            }
        }
    }

    public function get_image_link($id_product, $link_rewrite,$id_image=False){
        $protocol = Tools::getCurrentUrlProtocolPrefix();
        if (!$id_image){
            $prd_img   = Db::getInstance()->getRow("SELECT `id_image` from `" . _DB_PREFIX_ . "image` where `id_product`=" . $id_product . " and `cover`=1");
            $id_image=$prd_img['id_image'];
        }
        $link      = new LinkCore();
        $image_url = $protocol . $link->getImageLink($link_rewrite, $id_image, 'home_default');
        if (Tools::file_get_contents($image_url)) {
            $content   = Tools::file_get_contents($image_url);
            $imageData = base64_encode($content);
        } else {
        $image_url = $protocol . $link->getImageLink($link_rewrite, $id_product.'-'.$id_image, 'home_default');
        if (Tools::file_get_contents($image_url)) {
            $content   = Tools::file_get_contents($image_url);
            $imageData = base64_encode($content);
        } else {
            return false;
            }
        }
        return $imageData;
    }

    public function get_product_data($id_product, $instance_id){
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');;
        $product_data = Db::getInstance()->getRow("SELECT p.`reference`,p.`id_product`,p.`id_category_default`,p.`ean13`,p.`price`,p.`wholesale_price`,p.`weight`,pl.`name`,pl.`description`,pl.`link_rewrite`,pl.`description_short`
        FROM "._DB_PREFIX_."product  p
        LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON (pl.`id_product` = p.`id_product` and pl.id_shop=p.id_shop_default and pl.id_lang=".$id_lang.")
        WHERE p.`id_product`=".$id_product."");
        $image              =   $this->get_image_link($id_product, $product_data['link_rewrite']);
        $product_key = array(
            'name'              => $product_data['name'],
            'store_id'          => $id_product,
            'channel_id'        => $instance_id,
            'default_code'      => $product_data['reference'],
            'list_price'        => $product_data['price'],
            // 'qty_available'     => new xmlrpcval($stock_quantity(),'int'),
            // 'standard_price'    => new xmlrpcval($sale_price(),'double'),
            // 'description_sale'  => $product_data['description_short'],
            'image'         => $image,
        );
        $categ_str ='';
        $category_lst = array();
        $category_product_data = Db::getInstance()->executeS("SELECT `id_category` FROM "._DB_PREFIX_."category_product WHERE `id_product` = ".$id_product."");
        foreach($category_product_data as $category)
        {
            $categ_str = $categ_str.$category['id_category'].",";
            $category_list = $this->get_category_data($category['id_category'], $instance_id, $category_list);
        }
        $GLOBALS['kwargs']['category_vals'] = $category_list;
        $product_key['extra_categ_ids']=$categ_str;
        $variants = Db::getInstance()->executeS(
            'SELECT pa.`id_product_attribute` as id
			FROM `'._DB_PREFIX_.'product_attribute` pa
			'.Shop::addSqlAssociation('product_attribute', 'pa').'
			WHERE pa.`id_product` = '.(int)$id_product
        );
        if ($variants)
        {
            $attribute_and_term = array();
            $variant_list= array();
            foreach ($variants as $variable)
            {
                $attr_detail=$this->getAttributesDetails($id_lang, $variable['id']);
                $attribute_str = '';
                foreach($attr_detail as $value){
                    $attribute_str .="{'name':'".$value['attr_name']."', 'value':'".$value['name']."', 'attrib_name_id': ".$value['id_attribute_group'].", 'attrib_value_id' : ".$value['id_attribute']."},";
                }
                $id_image = Db::getInstance()->executeS('SELECT min(id_image) as id_image FROM `'._DB_PREFIX_.'product_attribute_image` where `id_product_attribute`= '.$variable['id'].'');

                $image_var = $this->get_image_link($product_data['id_product'], $product_data['link_rewrite'],$id_image[0]['id_image']);
                $attribute_str= "[".$attribute_str."]";
                $variant_key = array(
                'store_id'          => $variable['id'],
                'default_code'      => $attr_detail[0]['reference'],
                'list_price'        => $product_data['price']+$attr_detail[0]['attribute_price'],
                'qty_available'     => $attr_detail[0]['attribute_quantity'],
                // 'standard_price'    => new xmlrpcval($attr_detail['sale_price'],'double'),
                'description_sale'  => '',
                'image'         => $image_var,
                'name_value'        => $attribute_str,
                );
                $variant_tuple=array(0,0,$variant_key);
                array_push($variant_list,$variant_tuple);
            }
        $product_key['feed_variants']=$variant_list;
        }
        return $product_key;

    }

    public function getAttributesDetails($id_lang, $id_attribute){
        $data = Db::getInstance()->executeS('SELECT a.`id_attribute`, a.`id_attribute_group`,pac.`id_product_attribute`, agl.`name` as `attr_name`, al.`name`, attr.`price` as `attribute_price`,attr.`reference`,attr.`ean13`,stk.`quantity` as `attribute_quantity`
        FROM `'._DB_PREFIX_.'product_attribute_combination` pac
        LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
        LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'product_attribute` attr ON (pac.id_product_attribute = attr.id_product_attribute)
        LEFT JOIN `'._DB_PREFIX_.'stock_available` stk ON (attr.id_product = stk.id_product and attr.id_product_attribute = stk.id_product_attribute)

        WHERE pac.id_product_attribute = '.$id_attribute.'') ;

        return $data;
    }

    function get_category_data($id_category, $instance_id, $category_list){
        $category_data = Db::getInstance()->getRow("SELECT c.`id_parent`, cl.`name`, c.`id_shop_default` from `" . _DB_PREFIX_ . "category` c
        LEFT JOIN `"._DB_PREFIX_."category_lang` cl on (cl.`id_category` = c.`id_category`)
        where c.`id_category`=" . $id_category . "");
        if ($category_data['id_parent']=='0'){
            $parent_id=null;
        }
        $parent_id=$category_data['id_parent'];
        $category_key =array(
            'name' => $category_data['name'],
            'store_id' => $id_category,
            'channel_id' => $instance_id,
            // 'parent_id' => $category_data['id_parent'],
        );
        // array_push($category_list,$category_key);
        if(_PS_VERSION_ >='1.7.7'){
            foreach ($category_key as $key => $value) {
                $category_list[$key] = $value;
                }
        }
        else{
            array_push($category_list,$category_key);
        }
        if ($category_data['id_parent'] > 0){
            $category_list = $this->get_category_data($category_data['id_parent'],$instance_id, $category_list);
        }
        return $category_list;
    }



    public function hookActionObjectCategoryUpdateAfter($params){
        $id_category = Tools::getValue('id_category');
        if ($id_category){
            // code to export category.
        }
    }

    public function hookActionObjectProductUpdateAfter($params){
        $id_product = Tools::getValue('id_product');
        if ($id_product){
            // return true;
            // Code to Update product.
            }
    }

    public function hookActionValidateOrder($params){
        $Issync=Configuration::getGlobalValue('ErpSyncField');
        // $id_cart = Tools::getValue('id_cart');
        $id_cart = $params['cart']->id;
        if ($id_cart && $Issync){
            $id_order = false;
            $id_order = Order::getOrderByCartId((int)($id_cart));
            $log = new pob_log();
            $total_line_array = array();
            $product_list = array();
            $instance_id = Configuration::getGlobalValue('ErpInstanceName1');
            if(_PS_VERSION_ <'1.7.7'){
                                if (isset($params['order']->id))
                    $id_order = $params['order']->id;
                else
                    $id_order = $params['id_order'];
            }
            // if (isset($params['objOrder']->id))
            //     $id_order = $params['objOrder']->id;
            // else
            //     $id_order = $params['id_order'];
                        if(!$id_order)
                $id_order = (Tools::getValue('id_order')) ? Tools::getValue('id_order'):$order_id;
  
            if ($id_order){
                                $shipping_price = Db::getInstance()->getRow("SELECT `id_carrier`,`shipping_cost_tax_excl`  from `" . _DB_PREFIX_ . "order_carrier` where `id_order`=" .$id_order. " ORDER BY date_add DESC");
                $carrier_name = Db::getInstance()->getRow("SELECT `name` from `" . _DB_PREFIX_ . "carrier` where `id_carrier`=" .$shipping_price['id_carrier']. "");
    
                $order         = Db::getInstance()->getRow("SELECT `delivery_date`,`current_state`,`id_customer`,`id_address_delivery`,`id_address_invoice`,`id_currency`,`id_shop`,`total_discounts_tax_incl`,`total_discounts_tax_excl`,`reference`,`total_paid_tax_incl`,`total_shipping_tax_incl`,`total_shipping_tax_excl`,`total_paid`,`date_add`,`date_upd`,`invoice_date`,`total_paid`,`carrier_tax_rate`,`module`  from `" . _DB_PREFIX_ . "orders` where `id_order`=" . $id_order. "");
    
                $order_details   = Db::getInstance()->executeS("SELECT `product_id`,`reduction_percent`,`product_attribute_id`,`id_order_detail`,`product_quantity`,`product_name`,`reduction_amount_tax_incl`,`original_product_price`,`product_price`,`unit_price_tax_excl`,`reduction_amount_tax_excl`  from `" . _DB_PREFIX_ . "order_detail` where `id_order`=" .$id_order. "");
                $customer_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "customer` where `id_customer`=" .$order['id_customer']. "");
                if ($order['id_address_delivery'] == $order['id_address_invoice']){
                    $address_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "address` where `id_address`=" .$order['id_address_delivery']. "");
                }

                foreach ($order_details as $details) {
                    $tax_id = Db::getInstance()->getRow("SELECT `id_tax`  from `" . _DB_PREFIX_ . "order_detail_tax` where `id_order_detail`=" . $details['id_order_detail'] . "");
    
                    $price =($details['unit_price_tax_excl']);
                    $tax = array();
                    if ($tax_id['id_tax']>0){
                        $tax = Db::getInstance()->getRow("SELECT `rate`  from `" . _DB_PREFIX_ . "tax` where `id_tax`=" . $tax_id['id_tax']. "");
                        $item_rate = round(array_shift($tax));
                        $tax =array(
                            'rate' => $item_rate,
                        );
                    }
                    $product_dict = $this->get_product_data($details['product_id'], $instance_id);
                    $line_array= array(
                        'line_name' => $details['product_name'],
                        'line_product_id' => $details['product_id'],
                        //'line_variant_ids' => $details['product_attribute_id'],
                        'line_price_unit' => $price,
                        'line_taxes'      => array($tax),
                        'line_product_default_code' => $product_dict['default_code'],
                        'line_product_uom_qty' => $details['product_quantity'],
                        'line_source' => 'product'
                    );
                    if ($details['product_attribute_id']==0){
                        $line_array['line_variant_ids'] = "No Variants";
                    }
                    else{
                        $line_array['line_variant_ids'] = $details['product_attribute_id'];
                    }
                    // $product_dict = $this->get_product_data($details['product_id'], $instance_id);
                    array_push($product_list,$product_dict);
                    $line_tuple=array(0,0,$line_array);
                    array_push($total_line_array,$line_tuple);
                }
                if ($shipping_price['id_carrier']>0){
                    $line_array = array(
                        'line_product_id' => 'shipping',
                        'line_price_unit' => 
                            $shipping_price['shipping_cost_tax_excl'],
                        'line_product_uom_qty' => 1,
                        'line_name' => 'shipping',
                        'line_source' => 'delivery',
                        // 'line_taxes' => new xmlrpcval( , ),
                    );
                    $line_tuple=array(0,0,$line_array);
                    array_push($total_line_array,$line_tuple);
                }
                if (($order['total_discounts_tax_incl']>0) OR ($order['total_discounts_tax_excl']>0)){
                    $discount_line_array = array(
                        'line_product_id' => 'Discount',
                        'line_price_unit' => array(
                                'total_discounts_tax_incl' => $order['total_discounts_tax_incl'],
                                'total_discounts_tax_excl' => $order['total_discounts_tax_excl'], 
                            ), 
                        'line_product_uom_qty' =>  1,
                        'line_name' => 'Discount',
                        'line_source' => 'discount',
                    );
                    $line_tuple=array(0,0,$discount_line_array);
                    array_push($total_line_array, $line_tuple);
                }
                $GLOBALS['kwargs']['product_vals'] = $product_list;
                $curr_code = Db::getInstance()->executeS("SELECT `iso_code` FROM `"._DB_PREFIX_."currency` WHERE `id_currency`=".$order['id_currency']."");
                $vals = array(
                    'name'                  => $order['reference'],
                    'store_id'              => $id_order,
                    'payment_method'        => $order['module'],
                    'order_state'           => $order['current_state'],
                    'partner_id'            => $order['id_customer'],
                    'currency'              => $curr_code[0]['iso_code'],
                    'carrier_id'            => $carrier_name['name'],
                    'line_type'             => 'multi',
                    'customer_name'         => $customer_data['firstname']." ".$customer_data['lastname'],
                    'customer_email'        => $customer_data['email'],
                    'channel_id'            => $instance_id,
                    'invoice_partner_id'    => $order['id_address_invoice'],
                    'invoice_email'         => $customer_data['email'],
                    'shipping_partner_id'   => $order['id_address_delivery'],
                    'shipping_email'        => $customer_data['email'],
                    'line_ids'              => $total_line_array,
                    "date_order"            => $order['date_add'],
                    "confirmation_date"     => $order["delivery_date"],
                    "date_invoice"          => $order['invoice_date']
                );
                $invoice_address_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "address` where `id_address`=" .$order['id_address_invoice']. "");
                $invoice_address_state_data =  Db::getInstance()->getRow(
                                                                            "SELECT *  from `" . _DB_PREFIX_ . "state`
                                                                            where `id_state`=" . (int)$invoice_address_data['id_state'] . ""
                                                                        );
                $invoice_address_country_data =  Db::getInstance()->getRow(
                                                                            "SELECT *  from `" . _DB_PREFIX_ . "country`
                                                                            where `id_country`=" . (int)$invoice_address_data['id_country'] . ""
                                                                        );                                                        
                $invoice_address = array(

                    'invoice_name'     => $invoice_address_data['firstname'].$invoice_address_data['lastname'],
                    'invoice_phone'    => $invoice_address_data['phone'],
                    'invoice_street'    => $invoice_address_data['address1'],
                    'invoice_street2'    => $invoice_address_data['address2'],
                    'invoice_zip'    => $invoice_address_data['postcode'],
                    'invoice_city'    => $invoice_address_data['city'],
                    'invoice_state_name'    => $invoice_address_state_data['name'],
                    'invoice_state_id'    => $invoice_address_state_data['iso_code'],
                    'invoice_country_id'  => $invoice_address_country_data['iso_code']              
                ); 
                if ($order['id_address_delivery'] != $order['id_address_invoice']){
                    $shipping_address_data = Db::getInstance()->getRow("SELECT * from `" . _DB_PREFIX_ . "address` where `id_address`=" .$order['id_address_delivery']. "");
                    $shipping_address_state_data =  Db::getInstance()->getRow(
                                                                                "SELECT *  from `" . _DB_PREFIX_ . "state`
                                                                                where `id_state`=" . (int)$shipping_address_data['id_state'] . ""
                                                                            );
                    $shipping_address_country_data =  Db::getInstance()->getRow(
                                                                                "SELECT *  from `" . _DB_PREFIX_ . "country`
                                                                                where `id_country`=" . (int)$shipping_address_data['id_country'] . ""
                                                                            );                                                        
                    $shipping_address = array(
    
                        'shipping_name'     => $shipping_address_data['firstname'].$shipping_address_data['lastname'],
                        'shipping_phone'    => $shipping_address_data['phone'],
                        'shipping_street'    => $shipping_address_data['address1'],
                        'shipping_street2'    => $shipping_address_data['address2'],
                        'shipping_zip'    => $shipping_address_data['postcode'],
                        'shipping_city'    => $shipping_address_data['city'],
                        'shipping_state_name'    => $shipping_address_state_data['name'],
                        'shipping_state_id'    => $shipping_address_state_data['iso_code'],
                        'shipping_country_id'  => $shipping_address_country_data['iso_code'] 
                    );
                    $order_vals = array_merge($vals,$invoice_address,$shipping_address);
                }else{
                    $order_vals = array_merge($vals,$invoice_address);
                }
                $vals_list =  array((int)$instance_id,array($order_vals));
                $vals_list_serialized = php_xmlrpc_encode($vals_list);
                $global_serialized = php_xmlrpc_encode($GLOBALS['kwargs']);
                $connect = $this->get_erp_connection();
                                if ($connect['status']==true){
                    $client = $connect['client'];
                    $userId = $connect['user_id'];
                    $msg1 = new xmlrpcmsg('execute_kw');
                    $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
                    $msg1->addParam(new xmlrpcval($userId, "int"));
                    if(Configuration::getGlobalValue("Erpcode")){
                        $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("Erpcode"), "string"));
                    }
                    else{
                        $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));}
                    $msg1->addParam(new xmlrpcval('multi.channel.sale', 'string'));
                    $msg1->addParam(new xmlrpcval('sync_order_feeds', 'string'));
                    $msg1->addParam($vals_list_serialized);
                    $msg1->addParam($global_serialized);
                    $resp = $client->send($msg1);
                    $response_array=$resp->value()->me['struct'];
                    $status  = $response_array['kwargs'];
                    $status_message   = $response_array['message']->me['string'];
                    $log->logMessage(__FILE__,__LINE__,'Order id '.$id_order.' Export response: '.$status_message.'','INFO');
                    // $log->logMessage(__FILE__,__LINE__,'Order id '.$id_order.' kwrgs resp : '.$status.'','INFO');
                    return true;
                }
            }
        }
           }
}
?>
