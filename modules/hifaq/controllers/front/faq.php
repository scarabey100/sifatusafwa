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

class HiFaqFaqModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $controller_name = 'faq';

    protected $redirectionExtraExcludedKeys = ['module'];

    public function __construct()
    {
        if (!Tools::getIsset('type')) {
            $_GET['type'] = null;
        }

        parent::__construct();

        if ($this->module->sidebar_position == 'left') {
            $this->display_column_left = true;
        } elseif ($this->module->sidebar_position == 'right') {
            $this->display_column_right = true;
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->l('FAQs'),
            'url' => $this->module->getMainURL(),
        ];

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();

        $categories = HiFAQCategory::getCategories(true);
        for ($i = 0, $ci = count($categories); $i < $ci; ++$i) {
            $categories[$i]['url'] = $this->module->getCategoryURL($categories[$i]['friendly_url']);
            $categories[$i]['faqs'] = HiFAQItem::getFAQsByIdCategory($categories[$i]['id'], true, $this->module->faqs_count);
            $categories[$i]['faqs_count'] = HiFAQPostCategory::getCategoryFAQsCount($categories[$i]['id']);

            if (is_array($categories[$i]['faqs']) && $categories[$i]['faqs']) {
                foreach ($categories[$i]['faqs'] as $key => $faq) {
                    $categories[$i]['faqs'][$key]['url'] = $this->module->getFAQURL($faq['friendly_url']);
                }
            }
        }

        $this->context->smarty->assign([
            'psv' => $this->module->psv,
            'categories' => $categories,
            'meta_title' => $this->module->main_page_meta_title[$this->context->language->id],
            'meta_description' => $this->module->main_page_meta_description[$this->context->language->id],
            'meta_keywords' => $this->module->main_page_meta_keywords[$this->context->language->id],
            'main_page_description' => $this->module->main_page_description[$this->context->language->id],
            'search' => $this->module->search,
            'faqs_layout' => $this->module->layout,
            'faqs_count' => $this->module->faqs_count,
        ]);
        if ($this->module->psv >= 1.7) {
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/faq17.tpl');
        } else {
            $this->setTemplate('faq.tpl');
        }
    }

    public function displayAjaxFeedback()
    {
        $idFaq = (int) Tools::getValue('idFaq');

        if (!$idFaq) {
            exit(json_encode([
                'error' => $this->l('Something went wrong, please refresh the page and try again.'),
            ]));
        }

        $status = (int) Tools::getValue('feedback');
        $comment = Tools::getValue('comment');
        $existingFeedback = HiFAQFeedback::getFeedbackByCustomer($idFaq, Tools::getRemoteAddr(), (int) $this->context->customer->id, (int) $this->context->customer->id_guest);

        if ($existingFeedback) {
            $idFeedback = $existingFeedback['id_feedback'];
        } else {
            $idFeedback = 0;
        }
        $feedback = new HiFAQFeedback($idFeedback);

        $feedback->id_faq = $idFaq;
        $feedback->ip_address = Tools::getRemoteAddr();
        $feedback->feedback = $status;
        $feedback->comment = $comment;

        if (!$feedback->id_customer) {
            $feedback->id_customer = (int) $this->context->customer->id;
        }
        if (!$feedback->id_guest) {
            $feedback->id_guest = (int) $this->context->customer->id_guest;
        }

        if (!$feedback->save()) {
            exit(json_encode([
                'error' => $this->l('We couldn\'t save your feedback, please try again.'),
            ]));
        }

        exit(json_encode([
            'error' => false,
        ]));
    }
}
