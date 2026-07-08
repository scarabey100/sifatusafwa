<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_advancedpackajax_modalModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public $module;
    public function initContent()
    {
        $this->assignGeneralPurposeVariables();
        $shoppingCartModuleInstance = Module::getInstanceByName('ps_shoppingcart');
        if (Validate::isLoadedObject($shoppingCartModuleInstance) && $shoppingCartModuleInstance->active) {
            $modal = null;
            if (Tools::getValue('action') === 'add-to-cart') {
                $modal = $this->module->renderModal(
                    $this->context->cart,
                    Tools::getValue('id_product'),
                    Tools::getValue('id_product_attribute')
                );
            }
            ob_end_clean();
            header('Content-Type: application/json');
            die(json_encode([
                'preview' => $shoppingCartModuleInstance->renderWidget(null, ['cart' => $this->context->cart]),
                'modal' => $modal,
            ]));
        } else {
            ob_end_clean();
            header('Content-Type: application/json');
            die(json_encode([
                'preview' => null,
                'modal' => null,
            ]));
        }
    }
}
