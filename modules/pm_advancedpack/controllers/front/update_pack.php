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
class pm_advancedpackupdate_packModuleFrontController extends ModuleFrontController
{
    protected $idPack;
    protected $fromAnchor = false;
    protected $packAttributesList = [];
    protected $productPackChoice = [];
    protected $productPackExclude = [];
    protected $productPackQuantityList = [];
    protected $jsonOutput = [];
    protected $fromQuickView = false;
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public $module;
    public function init()
    {
        parent::init();
        header('X-Robots-Tag: noindex, nofollow', true);
        $this->ajax = true;
        $this->idPack = (int)Tools::getValue('id_pack');
        if (Tools::getIsset('productPackExclude')) {
            $this->productPackExclude = array_unique(array_map('intval', (array)Tools::getValue('productPackExclude')));
        }
        if (Tools::getIsset('productPackChoice')) {
            $tmp_productPackChoice = (array)Tools::getValue('productPackChoice');
            if (is_array($tmp_productPackChoice) && count($tmp_productPackChoice)) {
                foreach ($tmp_productPackChoice as $packChoiceRow) {
                    $this->productPackChoice[(int)$packChoiceRow['idProductPack']] = array_map('intval', $packChoiceRow['attributesList']);
                }
            }
            unset($tmp_productPackChoice);
            if (!count($this->productPackChoice)) {
                $this->errors[] = $this->module->getFrontTranslation('errorInvalidPackChoice');
            }
            $this->packAttributesList = [];
            foreach ($this->productPackChoice as $idProductPack => $attributeList) {
                if (in_array($idProductPack, $this->productPackExclude)) {
                    continue;
                }
                list($idProductAttribute) = AdvancedPack::combinationExists((int)$idProductPack, $attributeList, true);
                if ($idProductAttribute !== false) {
                    $this->packAttributesList[(int)$idProductPack] = (int)$idProductAttribute;
                }
            }
        }
        if (Tools::getIsset('productPackQuantityList')) {
            $tmp_productPackQuantityList = (array)Tools::getValue('productPackQuantityList');
            if (is_array($tmp_productPackQuantityList) && count($tmp_productPackQuantityList)) {
                foreach ($tmp_productPackQuantityList as $packChoiceRow) {
                    if (!is_numeric($packChoiceRow['quantity']) || (int)$packChoiceRow['quantity'] <= 0) {
                        $packChoiceRow['quantity'] = 1;
                    }
                    $this->productPackQuantityList[(int)$packChoiceRow['idProductPack']] = (int)$packChoiceRow['quantity'];
                }
            }
        }
        if (Tools::getValue('fromQuickView')) {
            $this->fromQuickView = true;
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
        if (!count($this->errors) && AdvancedPack::isValidPack($this->idPack)) {
            $this->packAttributesList = [];
            $config = $this->module->_getModuleConfiguration();
            if (Tools::getIsset('packAnchor') && Tools::getValue('packAnchor')) {
                $packAnchor = Tools::getValue('packAnchor');
                $this->packAttributesList = AdvancedPack::getPackProductAttributeListFromAnchor($this->idPack, $packAnchor);
                $this->fromAnchor = !empty($this->packAttributesList);
            }
            $packCompleteAttributesList = [];
            $packErrorsList = [];
            $packFatalErrorsList = [];
            $packForceHideInfoList = [];
            foreach ($this->productPackChoice as $idProductPack => $attributeList) {
                if (in_array($idProductPack, $this->productPackExclude)) {
                    continue;
                }
                list($idProductAttribute, $attributeList) = AdvancedPack::combinationExists((int)$idProductPack, $attributeList, true);
                if ($idProductAttribute === false) {
                    $packErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorWrongCombination');
                } else {
                    $this->packAttributesList[(int)$idProductPack] = (int)$idProductAttribute;
                }
                $packCompleteAttributesList[(int)$idProductPack] = $attributeList;
            }
            $packContent = AdvancedPack::getPackContent($this->idPack, null, false, $this->packAttributesList, $this->productPackQuantityList);
            $packContentDefaultAttributes = [];
            if ($packContent !== false) {
                foreach ($packContent as $packProduct) {
                    $packContentDefaultAttributes[(int)$packProduct['id_product_pack']] = (int)$packProduct['default_id_product_attribute'];
                    if (in_array((int)$packProduct['id_product_pack'], $this->productPackExclude)) {
                        continue;
                    }
                    $product = new Product((int)$packProduct['id_product']);
                    if (Validate::isLoadedObject($product) && !$product->active) {
                        $packFatalErrorsList[(int)$packProduct['id_product_pack']][] = $this->module->getFrontTranslation('errorProductIsDisabled');
                        $packForceHideInfoList[(int)$packProduct['id_product_pack']] = true;
                    } elseif (Validate::isLoadedObject($product) && !$product->checkAccess(Validate::isLoadedObject(Context::getContext()->customer) ? Context::getContext()->customer->id : 0)) {
                        $packFatalErrorsList[(int)$packProduct['id_product_pack']][] = $this->module->getFrontTranslation('errorProductAccessDenied');
                        $packForceHideInfoList[(int)$packProduct['id_product_pack']] = true;
                    } elseif (Validate::isLoadedObject($product) && !$product->available_for_order) {
                        $packFatalErrorsList[(int)$packProduct['id_product_pack']][] = $this->module->getFrontTranslation('errorProductIsNotAvailableForOrder');
                    }
                }
            }
            if (AdvancedPack::getPackAllowRemoveProduct($this->idPack) && count($packContent) >= 2 && ($packContent == false || count($this->productPackExclude) >= count($packContent))) {
                $this->errors[] = $this->module->getFrontTranslation('errorInvalidExclude');
            }
            if (!count($this->errors)) {
                $packQuantityList = $packQuantityOriginalList = AdvancedPack::getPackAvailableQuantityList($this->idPack, $this->packAttributesList, $this->productPackQuantityList);
                if (count($this->productPackQuantityList)) {
                    $packQuantityOriginalList = AdvancedPack::getPackAvailableQuantityList($this->idPack, $this->packAttributesList);
                }
                foreach ($packQuantityList as $idProductPack => $remainingQuantities) {
                    if (count($remainingQuantities) > 1) {
                        continue;
                    }
                    if (isset($packQuantityList[(int)$idProductPack]) && array_sum($packQuantityList[(int)$idProductPack]) <= 0) {
                        if (count($this->productPackQuantityList) && isset($packQuantityOriginalList[(int)$idProductPack]) && array_sum($packQuantityOriginalList[(int)$idProductPack]) <= 0) {
                            $packFatalErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorProductIsOutOfStock');
                        } else {
                            $packErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorProductIsOutOfStock');
                        }
                    }
                }
                foreach ($this->packAttributesList as $idProductPack => $idProductAttribute) {
                    if (isset($packQuantityList[(int)$idProductPack]) && array_sum($packQuantityList[(int)$idProductPack]) <= 0) {
                        if (count($this->productPackQuantityList) && isset($packQuantityOriginalList[(int)$idProductPack]) && array_sum($packQuantityOriginalList[(int)$idProductPack]) <= 0) {
                            $packFatalErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorProductIsOutOfStock');
                        } else {
                            $packErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorProductIsOutOfStock');
                        }
                    } elseif (isset($packQuantityList[(int)$idProductPack][$idProductAttribute]) && $packQuantityList[(int)$idProductPack][$idProductAttribute] <= 0) {
                        $otherCombinationWasFound = false;
                        if (!empty($config['priorityForCombinationsInStock']) && $this->fromAnchor) {
                            foreach ($packQuantityList[(int)$idProductPack] as $packIdProductAttribute => $stockValue) {
                                if ($packIdProductAttribute == (int)$packContentDefaultAttributes[(int)$idProductPack]) {
                                    continue;
                                }
                                if ($stockValue <= 0) {
                                    continue;
                                }
                                $this->packAttributesList[$idProductPack] = $packIdProductAttribute;
                                if (!isset(AdvancedPack::$forcePackAttributeList[$this->idPack])) {
                                    AdvancedPack::$forcePackAttributeList[$this->idPack] = [];
                                }
                                AdvancedPack::$forcePackAttributeList[$this->idPack][$idProductPack] = $packIdProductAttribute;
                                $otherCombinationWasFound = true;
                                break;
                            }
                        }
                        if (!$otherCombinationWasFound) {
                            $packErrorsList[(int)$idProductPack][] = $this->module->getFrontTranslation('errorProductOrCombinationIsOutOfStock');
                        }
                    }
                }
                foreach ($this->productPackExclude as $idProductPackExcluded) {
                    if (isset($packErrorsList[$idProductPackExcluded])) {
                        unset($packErrorsList[$idProductPackExcluded]);
                    }
                    if (isset($packFatalErrorsList[$idProductPackExcluded])) {
                        unset($packFatalErrorsList[$idProductPackExcluded]);
                    }
                }
                if ($this->fromQuickView) {
                    $this->context->smarty->assign('from_quickview', true);
                } else {
                    $this->context->smarty->assign('from_quickview', false);
                }
                $this->jsonOutput['packAvailableQuantity'] = AdvancedPack::getPackAvailableQuantity($this->idPack, $this->packAttributesList, $this->productPackQuantityList, $this->productPackExclude);
                $this->jsonOutput['packContentTable'] = $this->module->displayPackContentTable($this->idPack, $this->packAttributesList, $packCompleteAttributesList, $this->productPackQuantityList, $this->productPackExclude, $packErrorsList, $packFatalErrorsList, $packForceHideInfoList);
                $this->jsonOutput['packPriceContainer'] = $this->module->displayPackPriceContainer($this->idPack, $this->packAttributesList, $this->productPackQuantityList, $this->productPackExclude, $packErrorsList, $packFatalErrorsList);
                $this->jsonOutput['packUrlAnchor'] = AdvancedPack::getPackAnchorByAttributes($this->idPack, $this->packAttributesList);
                $this->jsonOutput['HOOK_EXTRA_RIGHT'] = Hook::exec('displayRightColumnProduct');
                $this->jsonOutput['packErrorsList'] = $packErrorsList;
                $this->jsonOutput['packFatalErrorsList'] = $packFatalErrorsList;
                $this->jsonOutput['packHasErrors'] = count($packErrorsList) ? true : false;
                $this->jsonOutput['packHasFatalErrors'] = count($packFatalErrorsList) ? true : false;
                $this->jsonOutput['packAttributesList'] = (array)json_encode($this->packAttributesList);
                $this->jsonOutput['productPackExclude'] = (array)$this->productPackExclude;
                die(json_encode($this->jsonOutput));
            }
        } else {
            $this->errors[] = $this->module->getFrontTranslation('errorInvalidPack');
        }
        die(json_encode(['hasError' => true, 'errors' => $this->errors]));
    }
    public function initContent()
    {
        $this->assignGeneralPurposeVariables();
        $this->context->smarty->assign('pmlink', Context::getContext()->link);
    }
    public function getProduct()
    {
        $packObj = new Product((int)$this->idPack, false, Context::getContext()->language->id);
        return $packObj;
    }
    public function isFromQuickView()
    {
        return $this->fromQuickView;
    }
    public function getPackQuantityList()
    {
        return $this->productPackQuantityList;
    }
    public function getPackExcludeList()
    {
        return $this->productPackExclude;
    }
    public function getPackAttributesList()
    {
        return $this->packAttributesList;
    }
}
