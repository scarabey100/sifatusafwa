<?php
/*
* 2007-2012 PrestaShop
* NOTICE OF LICENSE
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
* DISCLAIMER
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*  @author Webkul Sogtware Pvt. Ltd <www.webkul.com>
*  @copyright  2009-2015 Webkul Software Pvt. Ltd.
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


class AdminOrdersController extends AdminOrdersControllerCore
{
	public function __construct()
	{
		 
		parent::__construct();
		
		
		$this->bulk_actions['exportToOdoo'] = array('text' => $this->l('Synchronize with Odoo'), 'icon' => 'icon-ok-sign');
		$index  = count($this->_conf)+1;
        $this->_conf[$index] = $this->l('All Selected Order(s) has been successfully exported to Odoo.');
		// $this->fields_list['erp_id'] = array(
        //         'title' => $this->l('Exported to Odoo'),
        //         'align' => 'center',
        //         'type' => 'bool',
        //         'width' => 'auto',
		// 		'search' => false,
		// 		'icon' => array(
		// 		1 => array(
		// 			'src' => 'enabled-2.gif',
		// 			'alt' => $this->l('Synchronized')
		// 		),
		// 		0 => array(
		// 			'src' => 'disabled.gif',
		// 			'alt' => $this->l('Need Synchronization')
		// 		),
		// 		),
        //     );
        
        // $this->_join .='LEFT JOIN `'._DB_PREFIX_.'erp_order_merge` om ON (om.`prst_order_id` = a.`id_order`)';
		// $this->_select .=',if(om.`erp_order_id` is null, 0, 1) as erp_id';
	}
	
	public function createErpOrder($id_order)
	{
	try
	{
		if ($id_order) 
			{	
				Hook::exec('displayOrderConfirmation', array('id_order'=>$id_order));
				//Hook::exec('ActionValidateOrder', array('id_order'=>$id_order));
				return array('status'=>True);
			}
		
	}
	catch (Exception $e)
		{
			return array('status'=>False,'message'=>'Order(ID:'.$id_order.')<br />'. $e->getMessage());
		}
	}


	public function get_erp_connection()
	{
		if (!class_exists('xmlrpc_client'))
			include _PS_MODULE_DIR_.'/prestarealtimesync/xmlrpc.inc';
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
		// $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpSyncField"), 'bool'));
		$resp = $sock->send($msg);
		if (!$resp->faultCode())
		{	
			$userId = $resp->value()->scalarval();
			if ($userId > 0){
			return array(
				'sock'    =>	$sock,
				'client'  =>	$client,
				'user_id' =>	$userId,
				'status'  =>	True
				);
		}
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
			$log->logMessage(__FILE__,__LINE__,$resp->raw_data,'Connection Error');
			return array(
				'status'  =>False
				);
		}
	}

	
	public function processBulkexportToOdoo()
    {	
		$return = True;
		$message='';
		$connect = $this->get_erp_connection();
		if ($connect['status']==true)
		{
		try{
			$data=$this->boxes;
			if (is_array($data) && (count($data))) {
				//Deleting data
				foreach ($data as $id_data) {
					// if($this->check_order($id_data))
						// {
							$response=$this->createErpOrder($id_data);

							if (!$response['status'])
								{
									$return = False;
									$message=$message.'<br />'.$response['message'];
								}
						// }
					}
				}
			}
		catch(Exception $e)
			{
				$return = False;
				$message=$this->l($e->getMessage());
			}
			}
		else
		{
			$return = False;
			$message= 'Connection Error !!!';
		}

		if($return)
			{	
				$index       = count($this->_conf);
				// Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
				Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
			}
		else
			$this->errors[] = Tools::displayError('An error occurred while exporting Orders. Error:- '.$message);	
    
	}
	
	// public function check_order($id_order)
    // {
    //     $check = Db::getInstance()->getRow("SELECT `prst_order_id`  from `" . _DB_PREFIX_ . "erp_order_merge` where `prst_order_id`=" . $id_order . "");
    //     if (empty($check))
    //         return True;
    //     else
    //         return False;
    // }
	
}
?>