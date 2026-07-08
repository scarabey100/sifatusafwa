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

class HiFaqFaqSearchModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $controller_name = 'faqsearch';

    public function __construct()
    {
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
            'title' => $this->module->l('FAQs', 'faqsearch'),
            'url' => $this->module->getMainURL(),
        ];

        $query = urldecode(Tools::getValue('query'));
        if (!$query) {
            $query = urldecode(Tools::getValue('faqQuery'));
        }
        if ($query && $query != '') {
            $breadcrumb['links'][] = [
                'title' => $query,
                'url' => '',
            ];
        }

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();

        if ($this->ajax) {
            if (Tools::getValue('secure_key') != $this->module->secure_key) {
                echo 'Hack Attempt';
                exit;
            }

            if (Tools::getValue('action') == 'searchFAQ') {
                $query = Tools::getValue('query');

                $faqs = HiFAQItem::searchFAQs($query);

                exit(json_encode([
                    'content' => $this->module->renderSearchResults($faqs),
                ]));
            }

            exit;
        }

        $query = urldecode(Tools::getValue('query'));
        if (!$query) {
            $query = urldecode(Tools::getValue('faqQuery'));
        }
        $faqs = HiFAQItem::searchFAQs($query);

        $mainPageURL = $this->module->getMainURL();

        for ($i = 0, $c = count($faqs); $i < $c; ++$i) {
            $faqs[$i]['url'] = $this->module->getFAQURL($faqs[$i]['friendly_url']);

            if ($this->module->feedbacksCount) {
                $faqs[$i]['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faqs[$i]['id_faq']);
                $faqs[$i]['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faqs[$i]['id_faq'], 0);
            }
        }

        $this->context->smarty->assign([
            'query' => $query,
            'psv' => $this->module->psv,
            'faqs' => $faqs,
            'mainPageURL' => $mainPageURL,
            'feedbackAccordion' => $this->module->feedbackAccordion,
            'modTplDir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
            'icons' => $this->module->icons,
        ]);
        if ($this->module->psv >= 1.7) {
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/search-17.tpl');
        } else {
            $this->setTemplate('search.tpl');
        }
    }
}
