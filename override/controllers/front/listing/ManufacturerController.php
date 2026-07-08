<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
class ManufacturerController extends ManufacturerControllerCore
{
    /*
    * module: mncklevu
    * date: 2026-02-15 14:24:06
    * version: 1.6.0
    */
    public function initContent()
    {
        if (!Module::isEnabled('mncklevu')) {
            parent::initContent();
        } else {
            
            $module = Module::getInstanceByName('mncklevu');
            if (!$module->getAllowedInFrontendStatus()) {
                parent::initContent();
            } elseif (Configuration::get('PS_DISPLAY_SUPPLIERS')) {
                ProductListingFrontController::initContent();
    
                if (Validate::isLoadedObject($this->manufacturer) &&
                    $this->manufacturer->active &&
                    $this->manufacturer->isAssociatedToShop()) {
                    $this->assignManufacturer();
                    $this->template = 'module:mncklevu/views/templates/front/manufacturer.tpl';
                } else {
                    $this->assignAll();
                    $this->label = $this->trans(
                        'List of all brands',
                        array(),
                        'Shop.Theme.Catalog'
                    );
                    $this->setTemplate('catalog/manufacturers', array('entity' => 'manufacturers'));
                }
            } else {
                $this->redirect_after = '404';
                $this->redirect();
            }
        }
    }
    /**
     * @return bool
     */
    /*
    * module: mncklevu
    * date: 2026-02-15 14:24:06
    * version: 1.6.0
    */
    public function setMedia()
    {
        $result = parent::setMedia();
        if (Module::isEnabled('mncklevu')) {
            
            $module = Module::getInstanceByName('mncklevu');
            if ($module->getAllowedInFrontendStatus() &&
                Validate::isLoadedObject($this->manufacturer) &&
                $this->manufacturer->active &&
                $this->manufacturer->isAssociatedToShop()) {
                $this->registerJavascript(
                    'modules-mncklevu-category-page',
                    'https://js.klevu.com/theme/default/v2/category-page.js',
                    ['position' => 'head', 'server' => 'remote']
                );
    
                Media::addJsDef([
                    'klevu_pageCategory' => $this->manufacturer->name,
                    'klevu_pageManufacturer' => true
                ]);
            }
        }
        return $result;
    }
}
