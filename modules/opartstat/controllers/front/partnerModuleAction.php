<?php
/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
if (!defined('_PS_VERSION_')) {
    exit;
  }
  
class OpartStatPartnerModuleActionModuleFrontController extends ModuleFrontController
{
    protected $parentModuleName = 'opartstat';
    
    public function initContent()
    {
        $res ='ok' ;
        if (!Tools::getIsset('mod')) {
            $res = "No module";
            $this->returnRes($res);
            return false;
        }
        $moduleName = Tools::getvalue('mod');        
        if (!validate::isModuleName($moduleName)) {
            $res = "Bad module name";
            $this->returnRes($res);
            return false;
        }
        if(!OpartStatTools::moduleIsLinked($moduleName)) {
            $res = "Module is not linked";
            $this->returnRes($res);
            return false;
        }         
        $directory = _PS_MODULE_DIR_ . $this->parentModuleName . '/config/metrics/partnersModules/';
        include_once($directory . $moduleName . '/install/' . 'install.php');
        $className = 'opartStat_'.$moduleName;
        $installModuleClass = new $className();  
        $installModuleClass->ajaxProcess();

        $this->returnRes($res, true);
        return true;
    }

    private function returnRes($res)
    {
        $json = json_encode($res);
        echo $json;
        die;
    }
}