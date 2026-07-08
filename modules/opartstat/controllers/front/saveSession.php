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
  
include_once (dirname(__FILE__).'/../../classes/opartSession.php');

class OpartStatSaveSessionModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $response = "nothing";
        if (!Tools::getIsset('action')) {
            $response = "No action requested";
            $this->returnRes($response, true);
            return false;
        }
        if (Tools::getValue('action') == 'save') {
            
            $pageUrl = Tools::getValue('pageUrl');
            if(!Validate::isAbsoluteUrl($pageUrl)){
                $response = "Url no valide";
                $this->returnRes($response, true);
                return false;
            }
            if (!$this->verifyShopDomain($pageUrl)) {
                $response = "  bad shop url";
                $this->returnRes($response, true);
                return false;
            }

            $opartSession = new OpartSession();
            $opartSession->userIp=Tools::getRemoteAddr();
            if(opartSession::isBlockedIp($opartSession->userIp)) {
                $response = " Blocked IP";
                $this->returnRes($response, true);
                return false;  
            }                          

            $opartSession->pageUrl=$pageUrl;
            $opartSession->country=Tools::getValue('country');    
            $referrer=(Tools::getValue('referrer')=='')?'':opartStatTools::getDomainNameFromUrl(Tools::getValue('referrer'));
            
            $url_components = parse_url($pageUrl);
            if(array_key_exists('query',$url_components)) {
                parse_str($url_components['query'], $params);
                if(array_key_exists('utm_source',$params) && $params['utm_source'] != "") {                
                    $referrer = $params['utm_source'];
                }

                if(array_key_exists('utm_medium',$params) && $params['utm_medium'] != "") {   
                    $opartSession->setUtmMedium($params['utm_medium']);         
                }

                if(array_key_exists('utm_campaign',$params) && $params['utm_campaign'] != "") { 
                    $opartSession->setUtmCampaign($params['utm_campaign']);   
                }

                if(array_key_exists('gclid',$params) && $params['gclid'] != "") { 
                    $opartSession->setGclid($params['gclid']); 
                     if(!Validate::isGenericName($opartSession->gclid)){

                            $response = "glicd no valide";
                            $this->returnRes($response, true);
                            return false;

                    }   
                }
            } 

            $opartSession->setReferrer($referrer);


              if(!Validate::isGenericName($opartSession->referrer)){

                $response = "referrer no valide";
                $this->returnRes($response, true);
                return false;

            }

            $opartSession->setUserLanguage(Tools::getValue('userLanguage'));

            $opartSession->controllerName=Tools::getValue('opartControllerName');
            $opartSession->elementId=(int)Tools::getValue('opartElementId');
            $opartSession->shopId=(int)Tools::getValue('opartshopId');
            $opartSession->userAgent=Tools::getValue('opartUserAgent');

            $context = Context::getContext();
            $opartSession->device = $context->getDevice();
            
            $opartSession->userId = $context->customer->id;

            $opartSession->save();

            $this->returnRes($response, true);
            return false;
        }
        $this->returnRes($response, false);
    }

    private function returnRes($res, $logIt)
    {
        $json = json_encode($res);
        echo $json;
        die;
    }

    private function verifyShopDomain($pageUrl)
    {
        $shopUrl = _PS_BASE_URL_ . Context::getContext()->shop->physical_uri;
        $shopDomain = $this->getDomain($shopUrl);
        $pageDomain = $this->getDomain($pageUrl);

        return ($shopDomain == $pageDomain);
    }

    private function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }
}