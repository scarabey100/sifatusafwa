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
class pm_advancedpackadd_packModuleFrontController extends ModuleFrontController
{
    protected $idPack;
    protected $quantity;
    protected $idProductAttributeList = [];
    protected $productPackExclude = [];
    protected $productPackQuantityList = [];
    protected $productPackCustomizationList = [];
    protected $packHasEditedQuantity = false;
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public function init()
    {
        parent::init();
        header('X-Robots-Tag: noindex, nofollow', true);
        $this->ajax = true;
        $this->idPack = (int)Tools::getValue('id_pack');
        $this->quantity = (int)abs(Tools::getValue('qty', 1));
        if ($this->quantity <= 0) {
            $this->quantity = 1;
        }
        $idProductAttributeList = Tools::getValue('id_product_attribute_list');
        $idProductAttributeList = (array)json_decode($idProductAttributeList);
        if (!is_array($idProductAttributeList)) {
            $this->idProductAttributeList = [];
        } else {
            foreach ($idProductAttributeList as $idProductPack => $idProductAttribute) {
                if (empty($idProductPack) || empty($idProductAttribute) || !is_numeric($idProductPack) || !is_numeric($idProductAttribute)) {
                    continue;
                } else {
                    $this->idProductAttributeList[(int)$idProductPack] = (int)$idProductAttribute;
                }
            }
        }
        unset($idProductAttributeList);
        if (Tools::getIsset('productPackExclude')) {
            $this->productPackExclude = array_unique(array_map('intval', (array)Tools::getValue('productPackExclude')));
        }
        if (Tools::getIsset('productPackQuantityList')) {
            $tmp_productPackQuantityList = (array)Tools::getValue('productPackQuantityList');
            if (is_array($tmp_productPackQuantityList) && count($tmp_productPackQuantityList)) {
                $packProducts = AdvancedPack::getPackContent($this->idPack, null, false, [], $this->productPackQuantityList);
                foreach ($tmp_productPackQuantityList as $packChoiceRow) {
                    if (!is_numeric($packChoiceRow['quantity']) || (int)$packChoiceRow['quantity'] <= 0) {
                        $packChoiceRow['quantity'] = 1;
                    }
                    $this->productPackQuantityList[(int)$packChoiceRow['idProductPack']] = (int)$packChoiceRow['quantity'];
                }
                foreach ($packProducts as $packProduct) {
                    if (isset($this->productPackQuantityList[(int)$packProduct['id_product_pack']]) && $this->productPackQuantityList[(int)$packProduct['id_product_pack']] != (int)$packProduct['quantity']) {
                        $this->packHasEditedQuantity = true;
                        break;
                    }
                }
            }
        }
        if (Tools::getIsset('productPackCustomizationList')) {
            $tmp_productPackCustomizationList = (array)Tools::getValue('productPackCustomizationList');
            if (is_array($tmp_productPackCustomizationList) && count($tmp_productPackCustomizationList)) {
                $packProducts = AdvancedPack::getPackContent($this->idPack);
                foreach ($tmp_productPackCustomizationList as $packChoiceRow) {
                    if (!in_array((int)$packChoiceRow['idProductPack'], $this->productPackExclude)) {
                        $this->productPackCustomizationList[(int)$packChoiceRow['idProductPack']][(int)$packChoiceRow['idCustomizationField']] = $packChoiceRow['value'];
                    }
                }
            }
        }
        if (!$this->context->cart->id) {
            if (Context::getContext()->cookie->id_guest) {
                $guest = new Guest(Context::getContext()->cookie->id_guest);
                $this->context->cart->mobile_theme = $guest->mobile_theme;
            }
            $this->context->cart->add();
            if (!empty($this->context->cart->id)) {
                $this->context->cookie->id_cart = (int)$this->context->cart->id;
            }
        }
    }
    public function postProcess()
    {
        if (!$this->isTokenValid()) {
            Tools::redirect('index.php');
        }
    }
    public function displayAjax()
    {
        if (!count($this->productPackExclude) && !$this->packHasEditedQuantity) {
            AdvancedPack::addPackToCart($this->idPack, $this->quantity, $this->idProductAttributeList, $this->productPackCustomizationList, true, true);
        } else {
            AdvancedPack::addExplodedPackToCart($this->idPack, $this->quantity, $this->idProductAttributeList, $this->productPackCustomizationList, $this->productPackQuantityList, $this->productPackExclude);
        }
    }
    public function initContent()
    {
    }
}
