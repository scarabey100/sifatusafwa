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

class HiFaqFaqDetailsModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $controller_name = 'faqdetails';
    public $faq;

    protected $redirectionExtraExcludedKeys = ['module', 'faq_link_rewrite'];

    public function __construct()
    {
        parent::__construct();

        if ($this->module->sidebar_position == 'left') {
            $this->display_column_left = true;
        } elseif ($this->module->sidebar_position == 'right') {
            $this->display_column_right = true;
        }

        $this->faq = HiFAQItem::getDetails(Tools::getValue('faq_link_rewrite'));

        if (isset($this->faq) && $this->faq['id_lang'] != $this->context->language->id) {
            $this->faq = HiFAQItem::getDetailsByID($this->faq['id_faq']);
            Tools::redirect($this->module->getFAQURL($this->faq['friendly_url']));
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->module->l('FAQs', 'faqdetails'),
            'url' => $this->module->getMainURL(),
        ];

        if ($this->faq) {
            $breadcrumb['links'][] = [
                'title' => $this->faq['question'],
                'url' => $this->module->getMainURL(),
            ];
        }

        return $breadcrumb;
    }

    public function setMedia()
    {
        if ($this->module->psv == 1.6) {
            $this->context->controller->addCSS(_THEME_CSS_DIR_ . 'product_list.css', 'all');
        }

        return parent::setMedia();
    }

    public function initContent()
    {
        parent::initContent();

        if ($this->faq) {
            $this->faq['url'] = $this->module->getFAQURL(Tools::getValue('faq_link_rewrite'));

            if ($this->module->feedbacksCount) {
                $this->faq['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($this->faq['id_faq']);
                $this->faq['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($this->faq['id_faq'], 0);
            }
        }

        $related_products = $this->module->{'prepareProductsForFront' . $this->module->psv_round}($this->module->getRelatedProducts($this->faq['id_faq']));

        $this->context->smarty->assign([
            'main_page_url' => $this->module->getMainURL(),
            'psv' => $this->module->psv,
            'faq' => $this->faq,
            'display_related_products' => $this->module->related_products,
            'related_products' => $related_products,
            'feedback' => $this->module->feedback,
            'feedbackPosition' => $this->module->feedbackPosition,
            'modTplDir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
        ]);

        if ($this->module->psv >= 1.7) {
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/faq-details-17.tpl');
        } else {
            $this->setTemplate('faq-details.tpl');
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
            $langUrl = $this->module->getFaqUrlById($this->faq['id_faq'], $lang['id_lang']);
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
