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

/**
 * @since 1.5.0
 */
class EgWishlistActionsModuleFrontController extends ModuleFrontController
{
    /**
     * @var int
     */
    public $id_product;
    public $id_product_attribute;
    public $ssl = true;
    public $php_self = 'view';


    public function init()
    {
        parent::init();
        $this->id_product = (int)Tools::getValue('id_product');
        $this->id_product_attribute = (int)Tools::getValue('id_product_attribute');
    }

    public function postProcess()
    {
        if (Tools::getValue('process') == 'remove') {
            $this->processRemove();
        } elseif (Tools::getValue('process') == 'add') {
            $this->processAdd();
        } elseif (Tools::getValue('process') == 'remove-all') {
            $this->processRemoveAll();
        }
        elseif (Tools::getValue('process') == 'addToCart') {
            $this->processAddToCart();
        }
    }

    public function processAddToCart() 
    {
        $idProduct = (int) Tools::getValue('idProduct');
        $id_product_attribute = (int) Tools::getValue('idProductAttribute');

        if (Tools::getValue('isWishlist') == '1') {
            $idCustomer = $this->context->customer->id;
            $idProductAttribute = $id_product_attribute;
            if ($this->context->customer->isLogged()) {
                if (!EgWishListProduct::isCustomerWishlistProduct($idCustomer, $idProduct, $idProductAttribute)) {
                    $this->addProductToShortlist($idCustomer, $idProduct, $idProductAttribute);
                    $this->ajaxDie(json_encode(array(
                        'success' => true,
                        'data' => [
                            'message' => 'Product added to wishlist',
                            'type' => 'added',
                            'wished_count' => $this->getWishlistProductsCount($idCustomer),
                        ]
                    )));
                }
            } else {
                $already_added = $this->getCookieProducts();
                if (!in_array($idProduct, $already_added)) {
                    $this->addProductToShortlistByCookie($idProduct);
                    $prod = Hook::exec('displayOneProduct', array("id" => $idProduct,"id_wishlisted" => $idProduct)); 
                    $this->ajaxDie(json_encode(array(
                        'success' => true,
                        'data' => [
                            'message' => 'Product added h to wishlist',
                            'type' => 'added',
                            'product' =>  $prod,
                            'wished_count' => $this->getWishlistProductsCount(),
                        ]
                    )));
                }
            }
        }  
    }
    /**
     * Remove a wishlist product.
     */
    public function processRemove()
    {
        header('Content-Type: application/json');

        $context = Context::getContext();
        $rvType = Tools::getValue('rvType');
        $idProduct = (int) Tools::getValue('idProduct');
        $idCustomer = (int) $context->customer->id;
        if ($context->customer->isLogged() && $rvType == 'rv_pr') {
           EgWishListProduct::cleanShortListByIds($idProduct, $idCustomer);
            $this->ajaxDie(json_encode(array(
                'success' => true,
                'data' => [
                    'message' => $this->trans('Product removed from wishlist', [], "Modules.Egwishlist.Shop"),
                    'dataTitle' => $this->trans('Add to wishlist', [], "Modules.Egwishlist.Shop"),
                    'type' => 'removed',
                    'wished_count' => $this->getWishlistProductsCount($idCustomer),
                ]
            )));
        }
        if ($rvType == 'rv_ck') {
            $saved_arr = array();
            if ($this->context->cookie->bcm_short_list != '') {
                $saved_arr = explode(',', $this->context->cookie->bcm_short_list);
            }
            $this->context->cookie->bcm_short_list = implode(',', array_diff($saved_arr, array($idProduct)));
            $this->ajaxDie(json_encode(array(
                'success' => true,
                'data' => [
                    'message' => $this->trans('Product removed from wishlist', [], "Modules.Egwishlist.Shop"),
                    'dataTitle' => $this->trans('Add to wishlist', [], "Modules.Egwishlist.Shop"),
                    'type' => 'removed',
                    'wished_count' => $this->getWishlistProductsCount(),
                ]
            )));
        }
    }

      /**
     * Remove a wishlist product.
     */
    public function processRemoveAll()
    {
        header('Content-Type: application/json');

        $context = Context::getContext();
        $rvType = Tools::getValue('rvType');
        $idProducts =  Tools::getValue('idProducts');
        $idCustomer = (int) $context->customer->id;
        if ($context->customer->isLogged() && $rvType == 'rv_pr') {
           EgWishListProduct::cleanAllShortListByIds($idProducts, $idCustomer);
            $this->ajaxDie(json_encode(array(
                'success' => true,
                'data' => [
                    'message' => $this->trans('all Product removed from wishlist', [], "Modules.Egwishlist.Shop"),
                    'type' => 'removed',
                    'wished_count' => $this->getWishlistProductsCount($idCustomer),
                ]
            )));
        }
        if ($rvType == 'rv_ck') {
            if ($this->context->cookie->bcm_short_list != '') {
                $this->context->cookie->bcm_short_list = '';
            }
            $this->ajaxDie(json_encode(array(
                'success' => true,
                'data' => [
                    'message' => $this->trans('Product removed from wishlist', [], "Modules.Egwishlist.Shop"),

                    'type' => 'removed',
                    'wished_count' => $this->getWishlistProductsCount(),
                ]
            )));
        }
    }
  
    /**
     * Add a shortList product.
     */
    public function processAdd()
    {
        header('Content-Type: application/json');
        $context = Context::getContext();
        $idCustomer = (int)$context->customer->id;
        $idProduct = (int) Tools::getValue('idProduct');
        $idProductAttribute = (int) Tools::getValue('idProductAttribute');

        if ($this->context->cookie->logged) {
            if (!EgWishListProduct::isCustomerWishlistProduct($idCustomer, $idProduct, $idProductAttribute)) {
                $this->addProductToShortlist($idCustomer, $idProduct, $idProductAttribute);
                $this->ajaxDie(json_encode(array(
                    'success' => true,
                    'data' => [
                        'message' => $this->trans('Product added to wishlist', [], "Modules.Egwishlist.Shop"),
                        'dataTitle' => $this->trans('Remove from wishlist', [], "Modules.Egwishlist.Shop"),
                        'type' => 'added',
                        'wished_count' => $this->getWishlistProductsCount($idCustomer),
                        ]
                    )));
            }
        } else {
            $already_added = $this->getCookieProducts();
           
            if (!in_array($idProduct, $already_added)) {
                $this->addProductToShortlistByCookie($idProduct);
                $prod = Hook::exec('displayOneProduct', array("id" => $idProduct,"id_wishlisted" => $idProduct)); 
                $this->ajaxDie(json_encode(array(
                    'success' => true,
                    'data' => [
                        'message' => $this->trans('Product added to wishlist', [], "Modules.Egwishlist.Shop"),
                        'dataTitle' => $this->trans('Remove from wishlist', [], "Modules.Egwishlist.Shop"),
                        'type' => 'added',
                        'product' =>  $prod,
                        'wished_count' => $this->getWishlistProductsCount(),
                        ]
                    )));
                }
            }
        }
        
        private function addProductToShortlist($idCustomer, $idProduct, $idProductAttribute)
        {
            $idShop = (int) Context::getContext()->shop->id;
            $wishlistProduct = new EgWishListProduct();
            $wishlistProduct->id_product = $idProduct;
            $wishlistProduct->id_customer = $idCustomer;
            $wishlistProduct->id_product_attribute = $idProductAttribute;
            $wishlistProduct->id_shop = $idShop; 
            $id_egwishlist_product =  EgWishListProduct::getIdLastProduct(); 
            $prod = Hook::exec('displayOneProduct', array("id" => $idProduct,"id_wishlisted" => $id_egwishlist_product)); 
            if ($wishlistProduct->add()) {
                $this->ajaxDie(json_encode(array(
                    'success' => true,
                    'data' => [
                        'message' => $this->trans('Product added to wishlist', [], "Modules.Egwishlist.Shop"),
                        'dataTitle' => $this->trans('Product added to wishlist', [], "Modules.Egwishlist.Shop"),
                        'product' => $prod,
                        'wished_count' => $this->getWishlistProductsCount($idCustomer),
                        'type' => 'added'
                ]
            )));
        }
    }

    private function addProductToShortlistByCookie($idProduct)
    {
        $is_added = true;
        if ($is_added) {
            if ($this->context->cookie->bcm_short_list != '') {
                $this->context->cookie->bcm_short_list = $this->context->cookie->bcm_short_list. ',' . $idProduct;
            } else {
                $this->context->cookie->bcm_short_list = $idProduct;
            }
        }

        return $is_added;
    }

    public function getCookieProducts()
    {
        $this->context->cookie->bcm_short_list = trim($this->context->cookie->bcm_short_list);
        $this->context->cookie->bcm_short_list = trim($this->context->cookie->bcm_short_list, ',');
        if ($this->context->cookie->bcm_short_list != '') {
            $egListed = explode(',', $this->context->cookie->bcm_short_list);
        } else {
            $egListed = array();
        }
        return $egListed;
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
