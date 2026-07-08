<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

class MncKlevuSearchResultsModuleFrontController extends ModuleFrontController
{
    /**
     * @return string
     */
    protected function getPageTitle()
    {
        return $this->module->l('Search results', 'SearchResults');
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign('page_title', $this->getPageTitle());

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/search_results.tpl');
    }

    /**
     * @return array
     */
    public function getBreadcrumbLinks()
    {
        $result = parent::getBreadcrumbLinks();

        $result['links'][] = [
            'title' => $this->getPageTitle(),
            'url' => $this->context->link->getModuleLink(
                $this->module->name,
                MncKlevu::FRONT_CONTROLLER_NAME_SEARCH_RESULTS,
                [],
                true
            ),
        ];

        return $result;
    }

    /**
     * @return bool
     */
    public function setMedia()
    {
        $result = parent::setMedia();

        $this->registerJavascript(
            'modules-' . $this->module->name . '-search-results-page',
            'https://js.klevu.com/theme/default/v2/search-results-page.js',
            ['position' => 'head', 'server' => 'remote']
        );

        return $result;
    }
}
