<?php
/**
 * 2008-2024 Prestaworld
 *
 * NOTICE OF LICENSE
 *
 * The source code of this module is under a commercial license.
 * Each license is unique and can be installed and used on only one website.
 * Any reproduction or representation total or partial of the module, one or more of its components,
 * by any means whatsoever, without express permission from us is prohibited.
 *
 * DISCLAIMER
 *
 * Do not alter or add/update to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @author    prestaworld
 * @copyright 2008-2024 Prestaworld
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * International Registered Trademark & Property of prestaworld
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;

class SaveCartForLaterMyCartModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id) {
            $objSaveCart = new PrestCartSave();
            $data = $objSaveCart->getCustomerSavedCart($this->context->customer->id);
            if ($data) {
                $productsForTemplate = array();
                foreach ($data as $key => $info) {
                    $objProduct = new Product($info['id_product'], false, $this->context->language->id);
                    $allAttr = $objProduct->getAttributesResume($this->context->language->id);
                    if ($allAttr) {
                        foreach ($allAttr as $attr) {
                            if ($attr['id_product_attribute'] == $info['id_product_attribute']) {
                                $data[$key]['attr'] = $attr['attribute_designation'];
                            }
                        }
                    }
                    $data[$key]['name'] = $objProduct->name;
                    $data[$key]['cust_qty'] = $info['quantity'];

                    $assembler = new ProductAssembler($this->context);
                    $presenterFactory = new ProductPresenterFactory($this->context);
                    $presentationSettings = $presenterFactory->getPresentationSettings();
                    $presenter = new ProductListingPresenter(
                        new ImageRetriever(
                            $this->context->link
                        ),
                        $this->context->link,
                        new PriceFormatter(),
                        new ProductColorsRetriever(),
                        $this->context->getTranslator()
                    );

                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct($data[$key]),
                        $this->context->language
                    );
                }

                $this->context->smarty->assign(array(
                    'products' => $productsForTemplate
                ));
            }
            $this->context->smarty->assign(array(
                'modules_dir' => _MODULE_DIR_,
                'static_token' => Tools::getToken(false)
            ));
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/mycart.tpl');
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        if (Configuration::get('PRESTA_SAVE_CART_DELETE_AGAIN')) {
            Media::addJsDef(array(
                'presta_delete' => 1
                ));
        }
        $params = array('success' => 1);
        Media::addJsDef(array(
            'cart_url' => $this->context->link->getPageLink('cart'),
            'cart_url_show' => $this->context->link->getPageLink(
                'cart',
                null,
                $this->context->language->id,
                array(
                    'action' => 'show'
                ),
                false,
                null,
                true
            ),
            'presta_url' => $this->context->link->getModuleLink($this->module->name, 'process'),
            'prestatoken' => Tools::getToken(false),
            'success' => $this->context->link->getModuleLink($this->module->name, 'mycart', $params),
            ));
        $this->context->controller->registerStyleSheet(
            'modules-savecartforlater-css',
            'modules/' . $this->module->name . '/views/css/savecart.css'
        );
        $this->context->controller->registerJavascript(
            'modules-savecartforlater',
            'modules/' . $this->module->name . '/views/js/savecart.js'
        );
    }
}
