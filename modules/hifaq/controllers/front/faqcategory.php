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

class HiFaqFaqCategoryModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $controller_name = 'faqcategory';
    public $category;

    protected $redirectionExtraExcludedKeys = ['module', 'faqc_link_rewrite'];

    public function __construct()
    {
        parent::__construct();

        if ($this->module->sidebar_position == 'left') {
            $this->display_column_left = true;
        } elseif ($this->module->sidebar_position == 'right') {
            $this->display_column_right = true;
        }

        $this->category = HiFAQCategory::getCategoryByFriendlyURL(Tools::getValue('faqc_link_rewrite'));
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->module->l('FAQs', 'faqcategory'),
            'url' => $this->module->getMainURL(),
        ];

        if ($this->category) {
            $breadcrumb['links'][] = [
                'title' => $this->category['name'],
                'url' => $this->module->getMainURL(),
            ];
        }

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();

        $id_category = HiFAQCategory::getIdByLinkRewrite(Tools::getValue('faqc_link_rewrite'));

        if (!$id_category) {
            $faqs = [];
            $category = false;
        } else {
            $faqs = HiFAQItem::getFAQsByIdCategory($id_category);
            $category = new HiFAQCategory($id_category, $this->context->language->id);

            if (Validate::isLoadedObject($category) && $category->friendly_url != Tools::getValue('faqc_link_rewrite')) {
                Tools::redirect($this->module->getCategoryURL($category->friendly_url));
            }
        }

        for ($i = 0, $c = count($faqs); $i < $c; ++$i) {
            $faqs[$i]['url'] = $this->module->getFAQURL($faqs[$i]['friendly_url']);

            if ($this->module->feedbacksCount) {
                $faqs[$i]['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faqs[$i]['id_faq']);
                $faqs[$i]['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faqs[$i]['id_faq'], 0);
            }
        }

        $this->context->smarty->assign([
            'psv' => $this->module->psv,
            'faqs' => $faqs,
            'faqCategory' => $category,
            'main_page_url' => $this->module->getMainURL(),
            'feedbackAccordion' => $this->module->feedbackAccordion,
            'modTplDir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
            'icons' => $this->module->icons,
        ]);

        if ($this->module->psv >= 1.7) {
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/faq-category-17.tpl');
        } else {
            $this->setTemplate('faq-category.tpl');
        }
    }

    protected function getAlternativeLangsUrl()
    {
        $alternativeLangs = [];
        $languages = Language::getLanguages(true, $this->context->shop->id);

        if (count($languages) < 2) {
            // No need to display alternative lang if there is only one enabled
            return $alternativeLangs;
        }

        foreach ($languages as $lang) {
            $langUrl = $this->module->getCategoryUrlById($this->category['id'], $lang['id_lang']);
            if (!$langUrl) {
                continue;
            }
            if (method_exists($this, 'sanitizeUrl')) {
                $langUrl = $this->sanitizeUrl($langUrl);
            }

            $alternativeLangs[$lang['language_code']] = $langUrl;
        }

        return $alternativeLangs;
    }
}
