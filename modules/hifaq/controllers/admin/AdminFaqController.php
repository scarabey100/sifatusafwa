<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminFaqController extends ModuleAdminController
{
    public $adminForms;
    public $secure_key;

    public function __construct()
    {
        $this->secure_key = Tools::getValue('secure_key');
        parent::__construct();

        $this->adminForms = $this->module->adminForms;
    }

    public function init()
    {
        parent::init();
        if ($this->ajax) {
            if ($this->secure_key == $this->module->secure_key) {
                switch (Tools::getValue('action')) {
                    case 'update_helper_list':
                        $type = Tools::getValue('type');
                        $content = '';
                        if ($type == 'faq_custom_list') {
                            $content = $this->adminForms->renderBlocksList();
                        } elseif ($type == 'extra_faq') {
                            $content = $this->module->renderExtraFaqList(Tools::getValue('id_product'));
                        }
                        exit(json_encode([
                            'content' => $content,
                        ]));
                    case 'save_faq_custom_list':
                        $id_lang = Configuration::get('PS_LANG_DEFAULT');
                        if (!Tools::getValue('title_' . $id_lang)) {
                            exit(json_encode([
                                'error' => $this->module->l('Title is required'),
                            ]));
                        }
                        if (!Tools::getValue('faq_type')) {
                            exit(json_encode([
                                'error' => $this->module->l('Type is required'),
                            ]));
                        }
                        if (!Tools::getValue('hook')) {
                            exit(json_encode([
                                'error' => $this->module->l('Position is required'),
                            ]));
                        }
                        if ((int) Tools::getValue('count') < 1) {
                            exit(json_encode([
                                'error' => $this->module->l('Invalide value for count'),
                            ]));
                        }

                        $this->module->saveBlock(Tools::getValue('id_block'));
                        exit(json_encode([
                            'error' => false,
                        ]));
                    case 'displaySelectedBlockForm':
                        $function = Tools::getValue('blockType');
                        if (!method_exists('HiFaqAdminForms', $function)) {
                            exit(json_encode([
                                'error' => $this->module->l('The following function not found') . ' ' . $function,
                            ]));
                        }
                        exit(json_encode([
                            'error' => false,
                            'content' => $this->adminForms->$function(),
                        ]));
                    case 'deleteBlockItem':
                        $block = new HiFAQBlock((int) Tools::getValue('id_block'));
                        $block->delete();
                        exit(json_encode([
                            'message' => $this->module->l('Successful delete'),
                        ]));
                    case 'productSearch':
                        exit($this->productSearch(urldecode(Tools::getValue('q'))));
                    case 'sortRelatedProducts':
                        $id_faq = (int) Tools::getValue('id_faq');

                        $related_products = Tools::getValue('related_products');
                        if ($related_products) {
                            $i = 1;
                            foreach ($related_products as $product) {
                                $this->module->updateRelatedProductPosition($id_faq, (int) $product['id_product'], $i);
                                ++$i;
                            }
                        }
                        exit;
                }
            } else {
                exit;
            }
        } else {
            Tools::redirectAdmin($this->module->hiPrestaClass->getModuleUrl());
        }
    }

    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_configure/';
    }

    protected function ajaxRender($value = null, $controller = null, $method = null)
    {
        if (method_exists(get_parent_class($this), 'ajaxRender')) {
            return parent::ajaxRender($value, $controller, $method);
        }

        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $method = $bt[1]['function'];
        }

        /* @deprecated deprecated since 1.6.1.1 */
        Hook::exec('actionAjaxDieBefore', ['controller' => $controller, 'method' => $method, 'value' => $value]);

        /*
         * @deprecated deprecated since 1.6.1.1
         * use 'actionAjaxDie'.$controller.$method.'Before' instead
         */
        Hook::exec('actionBeforeAjaxDie' . $controller . $method, ['value' => $value]);
        Hook::exec('actionAjaxDie' . $controller . $method . 'Before', ['value' => $value]);
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        echo $value;
    }

    protected function productSearch($query)
    {
        $result = '';
        if ($query && !is_array($query)) {
            $products = Search::find((int) $this->context->language->id, $query, 1, 10, 'position', 'desc', true, false);
            if ($products) {
                foreach ($products as $product) {
                    $result .= $product['id_product'] . '|' . $product['pname'] . '|' . $product['cname'] . "\n";
                }
            }
        }

        return $result;
    }

    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        if (ob_get_contents()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');

        $this->ajaxRender($value, $controller, $method);
        exit;
    }

    public function displayAjaxRenderFaqForm()
    {
        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderAddFAQForm(Tools::getValue('idItem')),
        ]));
    }

    public function displayAjaxRenderFaqList()
    {
        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderFAQsList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSortFaqElements()
    {
        $pageNumber = (int) Tools::getValue('pageNumber');
        $pageItems = (int) Tools::getValue('pageItems');
        if (!$pageItems) {
            $pageItems = 50;
        }
        $positionOffset = ($pageNumber - 1) * $pageItems;

        $sortedItems = Tools::getValue('sortedItems');
        foreach ($sortedItems as $relativePosition => $id) {
            $newPosition = $relativePosition + $positionOffset;

            Db::getInstance()->update('hifaq', ['position' => (int) $newPosition], 'id_faq = ' . (int) $id);
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Successfully updated'),
        ]));
    }

    public function displayAjaxDeleteFaq()
    {
        $faq = new HiFAQItem(Tools::getValue('idElement'));
        if (!$faq->delete()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('FAQ was not deleted successfully, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('FAQ successfully deleted'),
            'content' => $this->adminForms->renderFAQsList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxUpdateFaqStatus()
    {
        $faq = new HiFAQItem(Tools::getValue('idElement'));
        $faq->active = Tools::getValue('currentStatus') == '0' ? 1 : 0;
        if (!$faq->update()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('There was an error while changing the status, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Status successfully changed'),
            'content' => $this->adminForms->renderFAQsList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSaveFaq()
    {
        $id_lang = Configuration::get('PS_LANG_DEFAULT');

        if (!Tools::getValue('title_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Title is require'),
            ]));
        }
        if (!Tools::getValue('question_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Question is require'),
            ]));
        }
        if (!Tools::getValue('answer_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Answer is require'),
            ]));
        }
        if (!Tools::getValue('friendly_url_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Friendly URL is require'),
            ]));
        }
        foreach (Language::getLanguages(false) as $lang) {
            $friendly_url = Tools::getValue('friendly_url_' . $lang['id_lang']);
            $answer = Tools::getValue('answer_' . (int) $lang['id_lang']);

            if ($friendly_url && !Validate::isLinkRewrite($friendly_url)) {
                $this->ajaxDie(json_encode([
                    'error' => $this->module->l('Invalid value for friendly URL') . ': "' . $friendly_url . '" ' . $this->module->l('for') . ' ' . $lang['name'],
                ]));
            }

            if ($this->module->isFAQFriendlyURLExists($friendly_url, $lang['id_lang'], (int) Tools::getValue('id_faq'))) {
                $this->ajaxDie(json_encode([
                    'error' => $this->module->l('Friendly URL already exists') . ': "' . $friendly_url . '" ' . $this->module->l('for') . ' ' . $lang['name'],
                ]));
            }

            if (preg_match('/<[\s]*(i?frame|form|input|embed|object)/ims', $answer) && !Configuration::get('PS_ALLOW_HTML_IFRAME')) {
                $this->ajaxDie(json_encode([
                    'error' => $this->module->l('In order to use an iframe in HTML fields, you\'ll need to enable the option "Allow iframes on HTML fields" from Shop Parameters->General tab'),
                ]));
            }
        }

        $this->module->saveFAQ(Tools::getValue('id_faq'));
        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('FAQ successfully saved'),
            'content' => $this->adminForms->renderFAQsList(),
        ]));
    }

    public function displayAjaxAddRelatedCategories()
    {
        $idFaq = (int) Tools::getValue('id_faq');
        $categories = Tools::getValue('categories');

        $this->module->deleteRelatedCategories($idFaq);

        $categoriesCount = 0;
        if (is_array($categories) && $categories) {
            foreach ($categories as $id_category) {
                $this->module->addRelatedCategory($idFaq, $id_category);
            }

            $categoriesCount = count($categories);
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Categories successfully added'),
            'idFaq' => $idFaq,
            'categoriesCount' => $categoriesCount,
        ]));
    }

    public function displayAjaxRenderFaqRelatedProducts()
    {
        $this->ajaxDie(json_encode([
            'content' => $this->module->renderRelatedProductsModal(Tools::getValue('idElement')),
        ]));
    }

    public function displayAjaxRenderFaqRelatedCategories()
    {
        $this->ajaxDie(json_encode([
            'content' => $this->adminForms->renderRelatedCategories(Tools::getValue('idElement')),
        ]));
    }

    public function displayAjaxDeleteRelatedProduct()
    {
        $idFaq = (int) Tools::getValue('id_faq');
        $idProduct = (int) Tools::getValue('id_product');

        if (!$this->module->deleteRelatedProduct($idFaq, $idProduct)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Something went wrong, refresh the page and try again'),
            ]));
        } else {
            $this->ajaxDie(json_encode([
                'error' => false,
                'content' => $this->module->renderRelatedProducts($idFaq),
                'relatedProductsCount' => HiFAQItem::getRelatedProductsCount($idFaq),
                'message' => $this->module->l('Product successfully deleted'),
            ]));
        }
    }

    public function displayAjaxAddRelatedProduct()
    {
        $idFaq = (int) Tools::getValue('id_faq');
        $idProduct = (int) Tools::getValue('id_product');
        $product = new Product($idProduct);
        if (!$idProduct || !Validate::isLoadedObject($product)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Product doesn\'t exists.'),
            ]));
        }

        if ($this->module->relatedProductExists($idFaq, $idProduct)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('This product already added'),
            ]));
        }

        if ($this->module->addRelatedProduct($idFaq, $idProduct)) {
            $this->ajaxDie(json_encode([
                'error' => false,
                'content' => $this->module->renderRelatedProducts($idFaq),
                'relatedProductsCount' => HiFAQItem::getRelatedProductsCount($idFaq),
                'message' => $this->module->l('Product successfully added'),
            ]));
        } else {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Something went wrong, refresh the page and try again'),
            ]));
        }
    }

    public function displayAjaxRenderFaqCategoryForm()
    {
        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderCategoryForm(Tools::getValue('idItem')),
        ]));
    }

    public function displayAjaxRenderFaqCategoryList()
    {
        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderCategoriesList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSaveFaqCategory()
    {
        $id_lang = Configuration::get('PS_LANG_DEFAULT');
        if (!Tools::getValue('name_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Category name is require'),
            ]));
        }
        if (!Tools::getValue('friendlyurl_' . $id_lang)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Friendly URL is require'),
            ]));
        }
        foreach (Language::getLanguages(false) as $lang) {
            $friendly_url = Tools::getValue('friendlyurl_' . $lang['id_lang']);

            if ($friendly_url && !Validate::isLinkRewrite($friendly_url)) {
                $this->ajaxDie(json_encode([
                    'error' => $this->modile->l('Invalid value for friendly URL') . ': "' . $friendly_url . '" ' . $this->module->l('for') . ' ' . $lang['name'],
                ]));
            }

            if ($this->module->isCategoryFriendlyURLExists($friendly_url, $lang['id_lang'], (int) Tools::getValue('id_category'))) {
                $this->ajaxDie(json_encode([
                    'error' => $this->module->l('Friendly URL already exists') . ': "' . $friendly_url . '" ' . $this->module->l('for') . ' ' . $lang['name'],
                ]));
            }
        }

        $this->module->saveCategory(Tools::getValue('id_category'));
        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Category successfully saved'),
            'content' => $this->adminForms->renderCategoriesList(),
        ]));
    }

    public function displayAjaxUpdateFaqCategoryStatus()
    {
        $category = new HiFAQCategory(Tools::getValue('idElement'));
        $category->active = Tools::getValue('currentStatus') == '0' ? 1 : 0;
        if (!$category->update()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('There was an error while changing the status, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Status successfully changed'),
            'content' => $this->adminForms->renderCategoriesList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSortFaqCategoryElements()
    {
        $pageNumber = (int) Tools::getValue('pageNumber');
        $pageItems = (int) Tools::getValue('pageItems');
        if (!$pageItems) {
            $pageItems = 50;
        }
        $positionOffset = ($pageNumber - 1) * $pageItems;

        $sortedItems = Tools::getValue('sortedItems');
        foreach ($sortedItems as $relativePosition => $id) {
            $newPosition = $relativePosition + $positionOffset;

            Db::getInstance()->update('hifaqcategory', ['position' => (int) $newPosition], 'id = ' . (int) $id);
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Successfully updated'),
        ]));
    }

    public function displayAjaxDeleteFaqCategory()
    {
        $category = new HiFAQCategory(Tools::getValue('idElement'));

        if (!$category->delete()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Category was not deleted successfully, please try to refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Category successfully deleted'),
            'content' => $this->adminForms->renderCategoriesList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxRenderFaqBlockForm()
    {
        if (Tools::getValue('idItem')) {
            $content = $this->adminForms->renderBlockEditForm((int) Tools::getValue('idItem'));
        } else {
            $content = $this->adminForms->renderBlockTypeForm();
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $content,
            'contentType' => 'modal',
        ]));
    }

    public function displayAjaxUpdateFaqBlockStatus()
    {
        $block = new HiFAQBlock(Tools::getValue('idElement'));
        $block->active = Tools::getValue('currentStatus') == '0' ? 1 : 0;
        if (!$block->update()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('There was an error while changing the status, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Status successfully changed'),
            'content' => $this->adminForms->renderBlocksList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSortFaqBlockElements()
    {
        $pageNumber = (int) Tools::getValue('pageNumber');
        $pageItems = (int) Tools::getValue('pageItems');
        if (!$pageItems) {
            $pageItems = 50;
        }
        $positionOffset = ($pageNumber - 1) * $pageItems;

        $sortedItems = Tools::getValue('sortedItems');
        foreach ($sortedItems as $relativePosition => $id) {
            $newPosition = $relativePosition + $positionOffset;

            Db::getInstance()->update('hifaqblock', ['position' => (int) $newPosition], 'id_block = ' . (int) $id);
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Successfully updated'),
        ]));
    }

    public function displayAjaxRenderFaqBlockList()
    {
        $this->ajaxDie(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderBlocksList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxDeleteFaqBlock()
    {
        $block = new HiFAQBlock(Tools::getValue('idElement'));
        if (!$block->delete()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Block was not deleted successfully, please try to refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Block successfully deleted'),
            'content' => $this->adminForms->renderBlocksList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxSaveFaqBlock()
    {
        $action = Tools::getValue('actionType');

        if (!Tools::getValue('block_title_' . Configuration::get('PS_LANG_DEFAULT'))) {
            exit(json_encode([
                'error' => $this->module->l('Title is required'),
            ]));
        }

        if ($action != 'submitCustomFAQs') {
            if (!Tools::getValue('block_count') || !Validate::isInt(Tools::getValue('block_count'))) {
                if ($action == 'submitCategoriesBlock') {
                    exit(json_encode([
                        'error' => $this->module->l('Categories count is not valid'),
                    ]));
                } elseif ($action != 'submitSearchBlock') {
                    exit(json_encode([
                        'error' => $this->module->l('FAQs count is not valid'),
                    ]));
                }
            }
        }

        if ($action == 'submitCustomFAQs' && !Tools::getValue('block_faqs')) {
            exit(json_encode([
                'error' => $this->module->l('Please select at least one FAQ'),
            ]));
        }

        if ($action == 'submitCategoryFaqs' && !(int) Tools::getValue('block_category')) {
            exit(json_encode([
                'error' => $this->module->l('Please select the catgory'),
            ]));
        }

        $this->module->saveBlock(Tools::getValue('id_block'));

        exit(json_encode([
            'error' => false,
            'message' => $this->module->l('Block successfully saved'),
            'content' => $this->adminForms->renderBlocksList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
            'contentType' => 'modal',
        ]));
    }

    public function displayAjaxRenderFaqFeedbackList()
    {
        exit(json_encode([
            'error' => false,
            'content' => $this->adminForms->renderFeedbackList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxDeleteFaqFeedback()
    {
        $feedback = new HiFAQFeedback(Tools::getValue('idElement'));
        if (!$feedback->delete()) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Feedback was not deleted successfully, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Feedback successfully deleted'),
            'content' => $this->adminForms->renderFeedbackList(Tools::getValue('filters'), Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
            'filters' => Tools::getValue('filters') ? true : false,
        ]));
    }

    public function displayAjaxGetFeatureValues()
    {
        $idFeature = (int) Tools::getValue('idFeature');
        $featureValues = FeatureValue::getFeatureValuesWithLang($this->context->language->id, $idFeature);

        if (empty($featureValues)) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('No feature values found'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'featureValues' => $featureValues,
        ]));
    }

    public function displayAjaxSaveFeature()
    {
        $idFaq = (int) Tools::getValue('idFaq');
        $idFeature = (int) Tools::getValue('idFeature');
        $idFeatureValue = (int) Tools::getValue('idFeatureValue');

        if (!$idFaq) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('It seems we were unable to indentify the FAQ, please refresh the page and try again.'),
            ]));
        }

        if (!$idFeature) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Please choose the feature'),
            ]));
        }

        if (!$idFeatureValue) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Please choose the feature value'),
            ]));
        }

        // check if the feature has already been assigned to the FAQ
        $featureExists = $this->module->getRelatedFeature($idFaq, $idFeature, $idFeatureValue);
        if ($featureExists) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('This feature has already been assigned to the FAQ'),
            ]));
        }

        $success = $this->module->addRelatedFeature($idFaq, $idFeature, $idFeatureValue);
        if (!$success) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Something went wrong, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Feature successfully added'),
            'content' => $this->module->renderRelatedFeatures($idFaq),
            'relatedFeaturesCount' => HiFAQItem::getRelatedFeaturesCount($idFaq),
        ]));
    }

    public function displayAjaxRemoveFeature()
    {
        $idFaq = (int) Tools::getValue('idFaq');
        $idFaqFeature = (int) Tools::getValue('idFaqFeature');

        if (!$idFaq || !$idFaqFeature) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('It seems we were unable to indentify the FAQ or the Feature, please refresh the page and try again.'),
            ]));
        }

        $success = $this->module->removeRelatedFeature($idFaq, $idFaqFeature);

        if (!$success) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Something went wrong, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('Feature successfully removed'),
            'content' => $this->module->renderRelatedFeatures($idFaq),
            'relatedFeaturesCount' => HiFAQItem::getRelatedFeaturesCount($idFaq),
        ]));
    }

    public function displayAjaxResetFaqList()
    {
        $faqs = Db::getInstance()->executeS('
            SELECT `id_faq`
            FROM `' . _DB_PREFIX_ . 'hifaq`
            ORDER BY `id_faq` ASC'
        );

        $success = true;
        if (is_array($faqs) && $faqs) {
            $i = 1;
            foreach ($faqs as $faq) {
                $success &= Db::getInstance()->update('hifaq', ['position' => (int) $i], 'id_faq = ' . (int) $faq['id_faq']);
                ++$i;
            }
        }

        if (!$success) {
            $this->ajaxDie(json_encode([
                'error' => $this->module->l('Something went wrong, please refresh the page and try again.'),
            ]));
        }

        $this->ajaxDie(json_encode([
            'error' => false,
            'message' => $this->module->l('FAQs positions successfully reseted'),
            'content' => $this->adminForms->renderFAQsList([], Tools::getValue('pageItems'), Tools::getValue('pageNumber')),
        ]));
    }
}
