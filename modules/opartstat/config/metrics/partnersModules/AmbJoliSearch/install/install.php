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

class opartStat_AmbJoliSearch
{
    public function install()
    {
        $mysqlVersion = Db::getInstance()->getVersion();
        $dateTimOrTimeStamp = ($mysqlVersion < '5.6.5') ? 'TIMESTAMP' : 'datetime';

        $sqls = array();

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_ambjolisearch` (
            `searchId` int(10) NOT NULL AUTO_INCREMENT,
            `createdAt` ' . pSQL($dateTimOrTimeStamp) . ' DEFAULT CURRENT_TIMESTAMP,
            `keyword` varchar(255) NOT NULL,
            PRIMARY KEY (`searchId`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sqls[] = 'INSERT INTO `' . _DB_PREFIX_ . 'opartstat_partnermodules_hook` (moduleName,hookName) VALUES ("AmbJoliSearch","displayBeforeBodyClosingTag")';

        $db =Db::getInstance();
        foreach ($sqls as $sql) {
            if ($db->execute($sql) == false) {
                return false;
            }
        }

        $opartStatModule = module::getInstanceByName('opartstat');

        if(!$opartStatModule->registerHook('displayBeforeBodyClosingTag'))
            return false;

        return true;
    }

    public function uninstall()
    {
        $sqls = array();
        $sqls[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'opartstat_ambjolisearch`;';        
        $sqls[] = 'DELETE FROM `' . _DB_PREFIX_ . 'opartstat_partnermodules_hook` WHERE moduleName = "AmbJoliSearch"';
        $db =Db::getInstance();
        foreach ($sqls as $sql) {
            if ($db->execute($sql) == false) {
                return false;
            }
        }
        return true;
    }

    public function displayBeforeBodyClosingTag() {
        $context = Context::getContext();
        $saveKwUrl = $context->link->getModuleLink('opartstat', 'partnerModuleAction',array('ajax'=>true)); 
        /** check sur is tiping dans le input */
        $script = "
            <script type='text/javascript'>
                window.addEventListener('load', (event) => {
                    var el = document.querySelector('#ui-id-1')
                    var inputSearch = document.querySelector('#search_widget').querySelector('input')

                    inputSearch.addEventListener('keyup', function( event ){
                        kw = inputSearch.value                        
                    })
        
                    document.body.addEventListener('click', function( event ){    
                        if( el.contains( event.target ) ){
                            console.log('dedans')
                            event.stopPropagation()
                            clickedElement =  clickOrigin(event)                            
                            saveKw('".$saveKwUrl."&mod=AmbJoliSearch&kw='+kw)     
                        }           
                    })
                })

                function clickOrigin(e){
                    var target = e.target;
                    var tag = [];
                    tag.tagType = target.tagName.toLowerCase();
                    tag.tagClass = target.className.split(' ');
                    tag.id = target.id;
                    tag.parent = target.parentNode.tagName.toLowerCase();

                    return tag;
                }

                async function saveKw(ajaxUrl) {
                    var result = await $.ajax({
                        type: 'POST',
                        url: ajaxUrl,
                        success: function (result) {
                        return true;
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log(textStatus);
                        console.log(XMLHttpRequest);
                        console.log(XMLHttpRequest.responseText);
                        //displayMetricError(metricName);
                        return false;
                        }
                    })
                    return result
                }
            </script>
        ";
        return $script;
    }

    public function ajaxProcess() {
        $res = "nothing";
        if (!Tools::getIsset('kw')) {
            $response = "No kw";
            $this->returnRes($res);
            return false;
        }
        $kw = Tools::getValue('kw');
        
        if (!validate::isString($kw)) {
            $res = "Bad kw";
            $this->returnRes($res);
            return false;
        }

        $sql = "insert into ". _DB_PREFIX_ . "opartstat_ambjolisearch (keyword) values ('".pSQL($kw)."')";
        $db =Db::getInstance();
        if(!$db->execute($sql))
            $res = "Db error";
        else
            $res = "Kw saved :".$kw;

        $this->returnRes($res);
        return true;
    }

    private function returnRes($res)
    {
        $json = json_encode($res);
        echo $json;
        die;
    }
}
