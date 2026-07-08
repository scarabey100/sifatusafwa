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

class AdminWkErpConfigurationController extends ModuleAdminController{

    public function __construct(){
        #Hook::exec('displayOrderConfirmation', array('id_order'=>1));
        $this->bootstrap = true;
        $this->table     = 'configuration';
        $this->context   = Context::getContext();
        parent::__construct();
        $this->fields_options = array(
            'general' => array(
                'title' =>  $this->l('POB Connection Configuration'),
                'fields' => array(
                                        'ErpUrl' => array(
                            'title' => $this->l('Url'),
                            'required'=>true,
                            'type' => 'text'),
                    'ErpPort' => array(
                            'title' => $this->l('Port'),
                            'required'=>true,
                            'type' => 'text'),
                    'ErpDatabase' => array(
                            'title' => $this->l('Database'),
                            'required'=>true,
                            'type' => 'text'),
                    'ErpUsername' => array(
                            'title' => $this->l('User Name'),
                            'required'=>true,
                            'type' => 'text'),
                    'Erpcode' => array(
                        'title' => $this->l('code'),
                        'type' => 'hidden'),
                    
                    // 'ErpInstanceName' => array(
                    //     'title' => $this->l('Instance'),
                    //     'type' => 'text')
                    ),
                'submit' => array('title' => $this->l('Save and Stay'),
                                    'name' => 'save_data',)
                ),
            // ),
        );
        $type = 'text';
        if ($this->context->employee->id_profile!=='1')
            $type= 'hidden';

        $this->fields_options['general']['fields']['ErpPassword'] = array(
                            'title' => $this->l('Password'),
                            'required'=>true,
                            'type'=>"password");
        
        if (Configuration::getGlobalValue('Erpcode') || Configuration::getGlobalValue('ErpPassword')){
            $this->fields_options['general']['fields']['ErpPassword'] = array(
                'title' => $this->l('Password'),
                'required'=>true,
                'type'=>"text");
        }
        
        $this->fields_options['general']['fields']['ErpInstanceName'] = array(
                'title' => $this->l('Instance'),
                'align' => 'text-center',
                'disabled' => 'disabled',
                'type' => 'hidden');
        $this->fields_options['general']['fields']['ErpInstanceName1'] = array(
            'title' => $this->l('Instance'),
            'disabled' => 'disabled',
            'required' => true,
            'identifier' => 'id',
            'type' => 'select',
            'list' => [
                        array(
                            'id'=> "l1",
                            'name'=> "Choose an Instance"
                        ),
                        array(
                            'id'=> Configuration::getGlobalValue("ErpInstanceConfId"),
                            'name'=> Configuration::getGlobalValue("ErpInstanceName")
                        ),
                    ] ,
            
        );
        $this->fields_options['general']['buttons'] = array(
            'reset_settings' => array(
                'title' => $this->l('Reset Settings'),
                'name' => 'data_reset',
                'type' => 'submit',
                'class' => 'btn btn-default pull-right',
                'icon' => 'process-icon-save'
            )
        );
                
        $this->fields_options['general']['fields']['ErpSyncField'] = array(
            'title' => $this->l('Real Time Sync'),
            'cast' => 'intval',
            'type' => 'bool'
                    );
        // parent::__construct();
        // echo "<pre>";
        $index = count($this->_conf)+1;
        $this->_conf[$index] = $this->l('Operation  Done Successfully');
    }

    public function setMedia( $isNewTheme = false){
        parent::setMedia($isNewTheme);
        $this->addJS(_MODULE_DIR_.'prestarealtimesync/views/js/pob_js.js');
    }

    public function initPageHeaderToolbar(){
        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }

    public function postProcess(){      
        if (!$this->loadObject(true))
            return;
        $code=Tools::getValue('ErpPassword');
        $len=strlen(Tools::getValue('Erpcode'));
        $test=str_repeat('*', $len);    
        if(Tools::getValue('Erpcode')){
            Configuration::updateGlobalValue('ErpPassword',Tools::getValue('Erpcode'));
        }
        if($code && $code!=$test){
            Configuration::updateGlobalValue('Erpcode',$code);
            Configuration::updateGlobalValue('ErpPassword',$code);
        }
        $flag1=$flag2=$flag3=$flag4= True;
        $conf_obj = new WkErpConfiguration();
        // d(Tools::getIsset('ErpUrl'));
        if (Tools::isSubmit('data_reset')){
            $flag5 = $conf_obj->UpdateConfig();
                if ($flag5){
                $index       = count($this->_conf);
                Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
                }
        }
        elseif (Tools::getIsset('ErpUrl') && $conf_obj->AllowChanges()){
                        $msg = '';   
            $test_conn=['status'=>false];
            $flag1 = $conf_obj->UpdateConfig();
                        if ($flag1){
                $test_conn = $conf_obj->TestConnection();
                                                // $msg = $test_conn['message'];          
            }
                        else{
                // if (!Configuration::getGlobalValue('ErpInstanceConfId'))
                // {             
                                                        $msg = 'All fields are Required';
                $flag4 = false;
                //$this->displayInformation($this->l($msg));
                // }    
            }
            if (!$test_conn['status'] && $flag1) {
                                $msg = $test_conn['message'];
                                                                $this->errors[] =Tools::displayError($this->l($msg));
                $flag4 = false;
                // $this->displayError($this->l($message));
            }
            if ($flag4){
                                                $erp_name = $conf_obj->CheckErpInstance();
                                if ($erp_name['ErpInstanceConfId']==false){
                    if (!$erp_name)
                    {
                                                $msg = 'All fields are Required';
                        $flag4 = false;
                    }
                    else{
                    $this->errors[] = Tools::displayError($erp_name['ErpInstanceName']);
                    $msg = $erp_name['ErpInstanceName'];
                    $flag4 = false;
                    }
                }
                Configuration::updateGlobalValue('ErInstanceNamep',$erp_name['ErpInstanceName']." (Default Language: ".$erp_name['ErpInstanceLangCode'].")");
                Configuration::updateGlobalValue('ErpInstanceConfId',$erp_name['ErpInstanceConfId']);
                Configuration::updateGlobalValue('ErpInstanceLangCode',$erp_name['ErpInstanceLangCode']);
            }
            if(Configuration::getGlobalValue('ErpInstanceConfId')){
                                Configuration::updateGlobalValue('ErpInstanceName1',Tools::getValue("ErpInstanceName1"));
                }  
            if ($test_conn['status']){
                $password = $code;
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $hiddenPassword = str_repeat('*', strlen($password));
                Configuration::updateValue('ErpPassword',$hiddenPassword);
            } 
            if ($flag1 & $test_conn['status']& $flag4){
                                $message = $test_conn['message'];
                $this->displayInformation($this->l($message));
                $this->displayInformation($this->l($msg));
                $index       = count($this->_conf);
                Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
            }
            else{     
                $this->fields_options['general']['fields']['ErpPassword'] = array(
                    'title' => $this->l('Password'),
                    'required'=>true,
                    'type'=>"password");
                $this->fields_options['general']['fields']['ErpInstanceName1'] = array(
                    'title' => $this->l('Instance'),
                    'disabled' => 'disabled',
                    'required' => true,
                    'identifier' => 'id',
                    'type' => 'select',
                    'list' => [
                                array(
                                    'id'=> "l1",
                                    'name'=> "Choose an Instance"
                                ),
                            ] ,   
                );
                // $index       = count($this->_conf);
                $this->errors[] =Tools::displayError($this->l($msg));
                // Tools::redirectAdmin(self::$currentIndex . '&conf=' . $index . '&token=' . $this->token);
            }
            }elseif(Tools::isSubmit('save_data'))
            {
                    $this->errors[] =Tools::displayError($this->l("All fields are Required"));
            }       
        parent::processSave();
    }
}
