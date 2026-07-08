<?php
/**
 * 2019 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext; 
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter; 
use PrestaShop\PrestaShop\Adapter\LegacyContext;   
use PrestaShop\PrestaShop\Core\Localization\Locale;

class EgWishlistViewModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }
    public function unique_key($array,$keyname){

        $new_array = array();
        foreach($array as $key=>$value){
       
          if(!isset($new_array[$value[$keyname]])){
            $new_array[$value[$keyname]] = $value;
          }
       
        }
        $new_array = array_values($new_array);
        return $new_array;
       }
       
    public function initContent()
    {
        parent::initContent();
        $idLang = (int)Context::getContext()->language->id;
        $engine = new PhpEncryption(_NEW_COOKIE_KEY_);
        $idCustomerToken = '';
        if(Tools::getIsset('shared') && Tools::getValue('shared') !== null) {
            if (Tools::getValue('shared') == 'logged' && Tools::getValue('id_customer') !== '' ) {
                $id_user = (int)Tools::getValue('id_customer');
                $wishlistProducts = EgWishListProduct::getWishlistProducts($id_user, $idLang);

            }
            else{
                $query = base64_decode(urldecode(Tools::getValue('shared')));
                parse_str($query , $linkValue);
    
                $productIds = array();
                $i=0;
                foreach ($linkValue as $cle => $linkItem) {
                    $productIds[$i] = (int)$linkValue[$cle];
                    $i++;
    
                }
    
                $wishlistProducts_y = EgWishListProduct::getWishlistProductsByIdProducts($productIds, $idLang);
    
                $wishlistProducts = $this->unique_key($wishlistProducts_y,'id_product');    
            }

            $typeRemove = 'rv_pr'; 
            $productsIds = [];
            $assembler = new ProductAssembler($this->context);

            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            } else {
                $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            }
            
            $presentedWishlistProducts = [];
            
            // Loop through the wishlist products
            foreach ($wishlistProducts as $item) {
                $productsIds[] = $item->id;
                $presentedWishlistProducts[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct(['id_product' => $item->id]),
                    $this->context->language
                );
            }

            $this->context->smarty->assign(array(
                'wishlistProducts' => $presentedWishlistProducts,
                'token' => $idCustomerToken,
                'product_count' => count($productsIds),
                'typeRemove' => $typeRemove,
                'readOnly' => true,
            ));
            // die(dump($var));
            
            $this->setTemplate('module:egwishlist/views/templates/front/egwishlist-account.tpl');
    


        }else{ 
            if (Context::getContext()->customer->isLogged()) {
                $idCustomer = (int)Context::getContext()->customer->id;
                //$idCustomerToken = $engine->encrypt($idCustomer);
                $wishlistProducts = EgWishListProduct::getWishlistProducts($idCustomer, $idLang);
                $typeRemove = 'rv_pr'; 
               
            } else {
                $wishlistProducts = EgWishListProduct::refreshShortlistData();
                $products = array();
                
                foreach ($wishlistProducts as $product) {
           
                    $id_lang = (int) Configuration::get('PS_LANG_DEFAULT'); 
                    $id_product = (int) $product["id_product"]; 
                    $prod = new Product($id_product, false, $id_lang);
                    $prod->url = $this->context->link->getProductLink($prod);
                     
                    $images = Product::getCover($id_product);
                    $image_url = $this->context->link->getImageLink($prod->link_rewrite, $images['id_image'], ImageType::getFormattedName('home'));
                    if ( isset($product["id_product_attribute"]) && !empty($product["id_product_attribute"]) ) {
                        $prod->id_product_attribute  =  $product["id_product_attribute"]  ; 
                        $combination = new Combination($product["id_product_attribute"]);
                        $attr = $combination->getAttributesName($id_lang);
                        $prod->attrs  = $attr; 
                    }

                    $prod->cover  = $image_url; 
                    $prod->id_egwishlist_product =  $id_product ;
                    array_push($products, $prod );
        
                } 
                $wishlistProducts = $products ;
                $typeRemove = 'rv_ck'; 
            } 
          
            $productsIds = [];
            $assembler = new ProductAssembler($this->context);

            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            } else {
                $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            }
            
            $presentedWishlistProducts = [];
            
            // Loop through the wishlist products
            foreach ($wishlistProducts as $item) {
                 
                if (Context::getContext()->customer->isLogged()) {
                    $id = $item['id_product']; 
                }else{
                    $id = $item->id; 
                }
                $productsIds[] =$id; 
                $presentedWishlistProducts[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct(['id_product' => $id]),
                    $this->context->language
                );
            }
            
           
            $makeLink = array();
            foreach ($presentedWishlistProducts as $key =>$item) {
                $item = (array)$item ;
                if ( isset($item['id_egwishlist_product']) && !empty($item['id_egwishlist_product']) ) {
                     $makeLink[$key] = $item['id_egwishlist_product'];
                }
             
            }   
                // encodage de la chaîne
            $linkVars = urlencode(base64_encode(http_build_query($makeLink)));
           
            if (Context::getContext()->customer->isLogged()) {
                $link_sharable = $this->context->link->getModuleLink(
                    $this->module->name,
                    'view',
                    array(
                        'shared' => 'logged',
                        'id_customer' => $idCustomer
                        )
                );
            } else {
                $link_sharable = $this->context->link->getModuleLink(
                    $this->module->name,
                    'view',
                    array('shared' => $linkVars)
                );
            } 

            $this->context->smarty->assign(array(
                'wishlistProducts' => $presentedWishlistProducts,
                'token' => $idCustomerToken,
                'shareLink' => $link_sharable,
                'typeRemove' => $typeRemove,
                'readOnly' => true,
            )); 

            $this->setTemplate('module:egwishlist/views/templates/front/egwishlist-account.tpl');    
        
        }

    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyWishlistLinkToBreadcrumb();
        return $breadcrumb;
    }
    protected function addMyWishlistLinkToBreadcrumb()
    {
        return array(
            'title' => $this->trans('Ma liste des favoris', [], 'Shop.Theme.Global'),
            'url' => ""
        );
    }
    public function displayAjaxRemoveWish()
    {
        $prod = Tools::getValue('idProduct');   
        if ( $this->context->customer->id ) {
            // $sql = 'DELETE FROM '._DB_PREFIX_.'egwishlist_product WHERE `id_egwishlist_product` = '.(int) pSQL($prod);
            $sql = "DELETE FROM `"._DB_PREFIX_."egwishlist_product` 
                    WHERE `id_product` = ".(int)$prod." AND `id_customer` = ".(int)$this->context->customer->id;
            if (Db::getInstance()->execute($sql)) {
                // echo json_encode(true);
                echo json_encode([
                    "status"=> true,
                    'wished_count' => $this->getWishlistProductsCount(1),
                ]);
            } else {
                // echo json_encode(false); 
                echo json_encode([
                    "status"=> false,
                    'wished_count' => $this->getWishlistProductsCount(1),
                ]);
            }
        } else {  
            $context = Context::getContext();
            if ($context->cookie->bcm_short_list != '') {
                $shortlisted = explode(',', $context->cookie->bcm_short_list);
            } else {
                $shortlisted = array();
            } 
            if (count($shortlisted) > 0) {
                foreach ($shortlisted as $id_product) {
                    if ($id_product != 0) {
                        $shortlisted_products[] = $id_product;
                    }
    
                } 
                $shortlisted_products = array_diff($shortlisted_products,  [$prod]); 
                $List = implode(',', $shortlisted_products);
                $context->cookie->bcm_short_list = $List;
                // echo json_encode(true);
                echo json_encode([
                    "status" => true,
                    'wished_count' => $this->getWishlistProductsCount(),
                ]);  
            } else {
                // echo json_encode(false); 
                echo json_encode([
                    "status" => false,
                    'wished_count' => $this->getWishlistProductsCount(),
                ]);
            }
        }
    }
    public function displayAjaxRemoveWishHome()
    {
        $prod = Tools::getValue('idProduct');   
        if ( $this->context->customer->id ) {
            $sql = 'DELETE FROM '._DB_PREFIX_.'egwishlist_product WHERE `id_product` = '.(int) pSQL($prod);
            if (Db::getInstance()->execute($sql)) {
                // echo json_encode(true);
                echo json_encode([
                    "status"=> true,
                    'wished_count' => $this->getWishlistProductsCount(1),
                ]);
            } else {
                // echo json_encode(false); 
                echo json_encode([
                    "status"=> false,
                    'wished_count' => $this->getWishlistProductsCount(1),
                ]); 
            }
        } else { 
            $context = Context::getContext();
            if ($context->cookie->bcm_short_list != '') {
                $shortlisted = explode(',', $context->cookie->bcm_short_list);
            } else {
                $shortlisted = array();
            }
            if (count($shortlisted) > 0) {
                foreach ($shortlisted as $id_product) {
                    if ($id_product != 0) {
                        $shortlisted_products[] = $id_product;
                    }
    
                } 
                $shortlisted_products = array_diff($shortlisted_products,[$prod]);
                $List = implode(',', $shortlisted_products);
                $context->cookie->bcm_short_list = $List;
                // echo json_encode(true); 
                echo json_encode([
                    "status"=> true,
                    'wished_count' => $this->getWishlistProductsCount(),
                ]); 
            } else {
                // echo json_encode(false); 
                echo json_encode([
                    "status"=> false,
                    'wished_count' => $this->getWishlistProductsCount(),
                ]); 
            }
           
        }
    }

    public function getWishlistProductsCount($loggedin = 0){
        if($loggedin){
            $id_customer = (int)Context::getContext()->customer->id;
            $sql = "SELECT COUNT(*) AS `wished_count` FROM `"._DB_PREFIX_."egwishlist_product`
                    WHERE `id_customer` = ".(int)$id_customer;
            
            return (int)DB::getInstance()->getValue($sql);
        }

        $cookie_list = Context::getContext()->cookie->bcm_short_list;

        return ($cookie_list != "") ? 
                count(explode(',', Context::getContext()->cookie->bcm_short_list))
                : 0;
    }

}
