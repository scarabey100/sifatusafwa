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
*  @author Webkul Software Pvt. Ltd <www.webkul.com>
*  @copyright  2009-2015 Webkul Software Pvt. Ltd.
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class WkErpConfiguration extends ObjectModel{

  
    public static $definition = array(
        'table' => 'configuration',
        'primary' => 'id_configuration'
    );

    public function AllowChanges(){
        if (Tools::getValue('ErpUrl') || Tools::getValue('ErpPort') || Tools::getValue('ErpDatabase') || Tools::getValue("ErpSyncField")){
                    $log = new pob_log();
                    $log->logMessage(__FILE__,__LINE__,'OpenERP Credentials Has been updated.','WARN');
                    return True;
        }
        else
            return False;
    }

    public function CheckErpInstance(){
		if (Configuration::getGlobalValue('ErpUrl') && Configuration::getGlobalValue('ErpPort') && Configuration::getGlobalValue('ErpDatabase') && Configuration::getGlobalValue('ErpUsername') ){

			if (!class_exists('xmlrpc_client'))
					include_once 'xmlrpc.inc';
				$sock = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/common");
				$client = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/object");
				$msg = new xmlrpcmsg('login');
				$msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), 'string'));
				$msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpUsername"), 'string'));
				$msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), 'string'));
				$response =  $sock->send($msg);
				if (!$response->faultCode()){
					// $pieces = explode(":", Tools::getHttpHost(true));
                    // $url_final = $pieces[0].':'.$pieces[1];
                    
                    $url_final = Tools::getHttpHost(true);
					$url = array(
								new xmlrpcval($url_final.__PS_BASE_URI__.'api', "string"),
								new xmlrpcval($url_final.__PS_BASE_URI__.'api/', "string")
					        ); 
					$condition = array(new xmlrpcval(
						array(
								new xmlrpcval('url', "string"),
								new xmlrpcval('in', "string"),
								new xmlrpcval($url, "array")),
							"array"),
							);
					$userId = $response->value()->scalarval();
					$msg1   = new xmlrpcmsg('execute');
					$msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
					$msg1->addParam(new xmlrpcval($userId, "int"));
					$msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
					$msg1->addParam(new xmlrpcval("multi.channel.sale", "string"));
					$msg1->addParam(new xmlrpcval("search", "string"));
					$msg1->addParam(new xmlrpcval($condition,"array"));
					$response1  = $client->send($msg1);
					if (!$response1->faultCode()){
						$check = $response1->value()->me['array'];
						if ($check){
							$condition1 = array(new xmlrpcval('name', "string"),
											new xmlrpcval('ps_language_id', "string")
							);
							$config_obj = $response1->value()->scalarval();
							$config_id = $config_obj[0]->me['int'];

							$msg2   = new xmlrpcmsg('execute');
							$msg2->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
							$msg2->addParam(new xmlrpcval($userId, "int"));
							$msg2->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
							$msg2->addParam(new xmlrpcval("multi.channel.sale", "string"));
							$msg2->addParam(new xmlrpcval("read", "string"));
							$msg2->addParam(new xmlrpcval($config_id,"int"));
							$msg2->addParam(new xmlrpcval($condition1,"array"));
							$response2  = $client->send($msg2);
							if (!$response2->faultCode()){
								$val = $response2->value()->me['array'][0]->me['struct'];
								$name_instance = $val['name']->me['string'];
								$id_language = $val['ps_language_id']->me['string'];
                $val = Db::getInstance()->getRow("SELECT `name` FROM`"._DB_PREFIX_."lang` where `id_lang`= ".$id_language."");
                $lang_code=$val['name'];
                Configuration::updateGlobalValue('ErpInstanceName',$name_instance." (Default Language: ".$lang_code.")");
                Configuration::updateGlobalValue('ErpInstanceConfId',$config_id);
                Configuration::updateGlobalValue('ErpInstanceLangCode',$lang_code);
                return array('ErpInstanceName'=>$name_instance,'ErpInstanceConfId'=>$config_id, 'ErpInstanceLangCode'=>$lang_code);
							}
              else{
                return array('ErpInstanceName' =>'Error in connection, please check the login details.','ErpInstanceConfId' =>false ,'ErpInstanceLangCode'=>'en_US' );           
              }
						}
						else{
                return array('ErpInstanceName'=>'Map the Current Instance at Odoo First !!!','ErpInstanceConfId'=>false, 'ErpInstanceLangCode'=>'en_US');
						}
					}
				}else{
            return array('ErpInstanceName' =>'Error in connection, please check the login details.','ErpInstanceConfId' =>false ,'ErpInstanceLangCode'=>'en_US' );
        }
			}
		}

    public function CheckOrderInvoice(){
        $flag=True;
        $check_order_confirm = Configuration::getGlobalValue('ErpConfirmOrder');
        if (!$check_order_confirm){
            $flag=False;
            Configuration::updateGlobalValue('ErpAutoInvoice',False);
        }
        return $flag;
    }

    public function CheckErpEnd($module) {
        if (!class_exists('xmlrpc_client'))
            include_once 'xmlrpc.inc';
        
        $sock = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/common");
        $client = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/object");
        $msg = new xmlrpcmsg('login');
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), 'string'));
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpUsername"), 'string'));
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), 'string'));
        $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpSyncfield"), 'bool'));
        $response =  $sock->send($msg);
        if (!$response->faultCode()){
            $condition=array(new xmlrpcval(
                                array(
                                        new xmlrpcval('state', "string"),
                                        new xmlrpcval('=', "string"),
                                        new xmlrpcval('installed', "string")),
                                    "array"),
                            new xmlrpcval(
                                array(
                                        new xmlrpcval('name', "string"),
                                        new xmlrpcval('=', "string"),
                                        new xmlrpcval($module, "string")),
                                    "array")
            );
            $userId = $response->value()->scalarval();
            $msg1   = new xmlrpcmsg('execute');
            $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
            $msg1->addParam(new xmlrpcval($userId, "int"));
            $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
            $msg1->addParam(new xmlrpcval("ir.module.module", "string"));
            $msg1->addParam(new xmlrpcval("search", "string"));
            $msg1->addParam(new xmlrpcval($condition,"array"));
            $response1  = $client->send($msg1);
            if (!$response1->faultCode()){
                $value = $response1->value()->scalarval();
                if ($value)
                    return array('is_error'=>False,'is_installed'=>True);
                else
                    return array('is_error'=>False,'is_installed'=>False);
            }
            else{
                $error_message = $response1->faultString();
                return array('is_error'=>True,'error_message'=>$error_message);
            }
        }
        else{
            $error_message = $response->faultString();
            return array('is_error'=>True,'error_message'=>$error_message);
        }
    }

    public function TestConnection(){
        if (Configuration::getGlobalValue('ErpUrl') && Configuration::getGlobalValue('ErpPort') && Configuration::getGlobalValue('ErpDatabase') && Configuration::getGlobalValue('ErpUsername')){
            $sock = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/common");
            $client = new xmlrpc_client(Configuration::getGlobalValue("ErpUrl").":".Configuration::getGlobalValue("ErpPort")."/xmlrpc/2/object");
            $msg = new xmlrpcmsg('login');
            $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), 'string'));
            $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpUsername"), 'string'));
            $msg->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), 'string'));
            $response =  $sock->send($msg);
            if (!$response->faultCode()){
                $userId = $response->value()->scalarval();
                if (!$userId){
                    Configuration::updateGlobalValue('Erpcode',null);
                    Configuration::updateGlobalValue('ErpPassword',null);
                    Configuration::updateGlobalValue('ErpInstanceName1', "l1");
                    Configuration::updateGlobalValue('ErpInstanceName',''." (Default Language: ".''.")");
                    return array(
                                'status' => false,
                                'message' => "Invalid Credentials for Connection!!!"
                            );
                }
                $condition=array(new xmlrpcval('lang', "string"));
                $msg1   = new xmlrpcmsg('execute');
                $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
                $msg1->addParam(new xmlrpcval($userId, "int"));
                $msg1->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
                $msg1->addParam(new xmlrpcval("res.users", "string"));
                $msg1->addParam(new xmlrpcval("read", "string"));
                $msg1->addParam(new xmlrpcval($userId,"int"));
                $msg1->addParam(new xmlrpcval($condition,"array"));
                $response1  = $client->send($msg1);
                if (!$response1->faultCode()){
                    $value = $response1->value()->scalarval();
                    $lang_code=$value[0]->me['struct']['lang']->me['string'];
                    $condition1=array(new xmlrpcval(
                            array(
                                    new xmlrpcval('code', "string"),
                                    new xmlrpcval('=', "string"),
                                    new xmlrpcval($lang_code, "string")),
                                "array"),
                            );
                    $msg2   = new xmlrpcmsg('execute');
                    $msg2->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
                    $msg2->addParam(new xmlrpcval($userId, "int"));
                    $msg2->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
                    $msg2->addParam(new xmlrpcval("res.lang", "string"));
                    $msg2->addParam(new xmlrpcval("search", "string"));
                    $msg2->addParam(new xmlrpcval($condition1,"array"));
                    $response2  = $client->send($msg2);
                    if (!$response2->faultCode()){
                        $value = $response2->value()->scalarval();
                        $lang_id=$value[0]->me['int'];
                        $msg3   = new xmlrpcmsg('execute');
                        $msg3->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpDatabase"), "string"));
                        $msg3->addParam(new xmlrpcval($userId, "int"));
                        $msg3->addParam(new xmlrpcval(Configuration::getGlobalValue("ErpPassword"), "string"));
                        $msg3->addParam(new xmlrpcval("res.lang", "string"));
                        $msg3->addParam(new xmlrpcval("name_get", "string"));
                        $msg3->addParam(new xmlrpcval($lang_id,"int"));
                        $response3  = $client->send($msg3);
                        if (!$response3->faultCode()){
                            $value = $response3->value()->scalarval();
                            $lang_name=$value[0]->me['array'][1]->me['string'];

                            return array(
                                'status' => true,
                                'message' => "Successfully connected.(Default Language: ".$lang_name." )"
                            );
                        }
                        else{
                            $error_message = $response3->faultString();
                            $log = new pob_log();
                            $log->logMessage(__FILE__,__LINE__,$response3->raw_data);
                            return array(
                                'status' => true,
                                'message' => "Connected but with Error(You can ignore and continue)"
                            );
                        }
                    }
                    else{
                        $error_message = $response2->faultString();
                        $log = new pob_log();
                        $log->logMessage(__FILE__,__LINE__,$response2->raw_data);
                        return array(
                                'status' => true,
                                'message' => "Connected but with Error(You can ignore and continue)"
                            );
                        }
                }
                else{
                    $error_message = $response1->faultString();
                    $log = new pob_log();
                    $log->logMessage(__FILE__,__LINE__,$response1->raw_data);
                    return $error_message;
                }
            }
            else{
                $error_message = $response->faultString();
                $log = new pob_log();
                $log->logMessage(__FILE__,__LINE__,$response->raw_data);
                return array(
                    'status' => false,
                    'message' => 'Error in Connection with Odoo'
                );
            }
        }
    }

    public function updateConfig(){
        if (Tools::isSubmit('data_reset'))
        {
            // $c1=Configuration::deleteByName('ErpUrl');
            // $c2=Configuration::deleteByName('ErpPort');
            // $c3=Configuration::deleteByName('ErpDatabase');
            // $c4=Configuration::deleteByName('ErpUsername');
            // $c5=Configuration::deleteByName('ErpPassword');
            Configuration::updateGlobalValue('ErpUrl',null);
            Configuration::updateGlobalValue('ErpPort', null);
            Configuration::updateGlobalValue('ErpDatabase', null);
            Configuration::updateGlobalValue('ErpUsername', null);
            Configuration::updateGlobalValue('ErpPassword', null);
            Configuration::updateGlobalValue('Erpcode', null);
            Configuration::updateGlobalValue('ErpSyncField', false);
            Configuration::updateGlobalValue('ErpInstanceName1', "l1");
            Configuration::updateGlobalValue('ErpInstanceName',''." (Default Language: ".''.")");
                return True;
        }elseif (!Tools::getValue('ErpUrl') || !Tools::getValue('ErpPort') || !Tools::getValue('ErpDatabase') || !Tools::getValue('ErpUsername') || (!Tools::getValue('ErpPassword') && Configuration::getGlobalValue('Erpcode')== null)){
            return False;
       }else{
                        $Url      = Tools::getValue('ErpUrl');
            $Port     = Tools::getValue('ErpPort');
            $Database = Tools::getValue('ErpDatabase');
            $Username = Tools::getValue('ErpUsername');
            // $Password = Tools::getValue('ErpPassword');
            $IsSync   = Tools::getValue('ErpSyncField');
            Configuration::updateGlobalValue('ErpUrl', $Url);
            Configuration::updateGlobalValue('ErpPort', $Port);
            Configuration::updateGlobalValue('ErpDatabase', $Database);
            Configuration::updateGlobalValue('ErpUsername', $Username);
            // Configuration::updateGlobalValue('ErpPassword', $Password);
            Configuration::updateGlobalValue('ErpSyncField', $IsSync);
            return True;
        }
    }


    public function odooMethodCall($db, $pwd, $model, $method, $method_params,$kwargs=false)
    {   
        require_once  __DIR__."/../prestarealtimesync.php";
        $presterp = new Prestarealtimesync;
        $connect = $presterp->get_erp_connection();
        if ($connect["status"] == true)
        {
            $client = $connect["client"];
            $user_id = $connect["user_id"];
            $msg = new xmlrpcmsg("execute_kw");
            $msg->addParam(new xmlrpcval($db, "string"));
            $msg->addParam(new xmlrpcval($user_id, "int"));
            $msg->addParam(new xmlrpcval($pwd, "string"));
            $msg->addParam(new xmlrpcval($model, "string"));
            $msg->addParam(new xmlrpcval($method, "string"));
            $msg->addParam(new xmlrpcval($method_params, "array"));
            if ($kwargs){
                $msg->addParam(new xmlrpcval($kwargs, 'struct'));
            }
            $resp = $client->send($msg);
            if($resp->faultCode() && $resp->faultCode()!=1){
                $err_message = $resp->errstr;
                Tools::displayError('odoo method call response: '.$err_message);
                return false;
            }
            return true;
        }
    }


}
