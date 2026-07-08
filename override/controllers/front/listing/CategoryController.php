<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
class CategoryController extends CategoryControllerCore
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
            return;
        }
        
        $module = Module::getInstanceByName('mncklevu');
        $catIds = [];
        $catIdsStr = $module->getConfiguration()->get(\MncKlevu\PrestaShop\Adapter\Configuration::KEY_PRODUCT_PAGE_CATEGORIES);
        if ($catIdsStr) {
            $catIds = array_filter(array_map('intval', explode(',', $catIdsStr)));
        }
        if (in_array((int)$this->category->id, $catIds)) {
            parent::initContent();
            return;
        }
        if (!$module->getAllowedInFrontendStatus()) {
            parent::initContent();
        } else {
            FrontController::initContent();
            if ($this->category->checkAccess($this->context->customer->id)) {
                $this->template = 'module:mncklevu/views/templates/front/category.tpl';
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
            if ($module->getAllowedInFrontendStatus()) {
                $this->registerJavascript(
                    'modules-mncklevu-category-page',
                    'https://js.klevu.com/theme/default/v2/category-page.js',
                    ['position' => 'head', 'server' => 'remote']
                );
    
                Media::addJsDef([
                    'klevu_pageCategory' => implode(
                        ';',
                        (new MncKlevu\PrestaShop\Adapter\Product\ProductCategoriesRetriever())
                            ->getHierarchy(
                                $this->category->id,
                                $this->context->language->id
                            )
                    ),
                    'products_per_page' => Configuration::get('PS_PRODUCTS_PER_PAGE'),
                ]);
            }
        }
        return $result;
    }
}
