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
 * @version   1.5.1
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__) . '/classes/HiPrestaModule.php';
include_once dirname(__FILE__) . '/classes/faqAdminForms.php';
include_once dirname(__FILE__) . '/classes/faq.php';
include_once dirname(__FILE__) . '/classes/faqcategory.php';
include_once dirname(__FILE__) . '/classes/faqpostcategory.php';
include_once dirname(__FILE__) . '/classes/faqblock.php';
include_once dirname(__FILE__) . '/classes/feedback.php';

class HiFaq extends Module
{
    public $psv;
    public $psv_round;
    public $errors = [];
    public $success = [];
    public $clean_db;
    public $module_hooks = [];
    public $product_page_hook;

    public $hiPrestaClass;
    public $adminForms;
    public $secure_key;

    public $main_page_meta_title;
    public $main_page_meta_description;
    public $main_page_meta_keywords;
    public $main_page_description;

    public $sidebar_position;
    public $search;
    public $layout;
    public $faqs_count;
    public $related_products;
    public $structured_data;
    public $faq_url;
    public $category_url;
    public $details_url;
    public $search_url;

    public $block_has_markup = false;

    // Design Settings
    public $searchBgColor;
    public $customCss;

    // Feedback
    public $feedback;
    public $feedbackPosition;
    public $feedbackAccordion;
    public $feedbacksCount;
    public $feedbackLastSeen;

    public $icons;

    public function __construct()
    {
        $this->name = 'hifaq';
        $this->tab = 'front_office_features';
        $this->version = '1.5.1';
        $this->author = 'hipresta';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = 'f5bd5f6e0e5a7d5d50c6418010fb30a2';
        parent::__construct();
        $this->globalVars();
        $this->displayName = $this->l('Frequently Asked Questions (FAQ) Pro');
        $this->description = $this->l('Effortlessly create a professional, SEO-friendly FAQ page and display FAQs on product pages or any other sections of your site. Enhance user experience with structured, interactive, and feedback-enabled FAQs.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->hiPrestaClass = new HiPrestaFaqModule($this);
        $this->adminForms = new HiFaqAdminForms($this);

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install()
            || !$this->installDb()
            || !$this->registerHook('moduleRoutes')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayRightColumn')
            || !$this->registerHook('displayLeftColumn')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('displayHiFAQ')
            || !$this->registerHook('displayHiFAQProduct')
            || !$this->registerHook('displayLeftColumnProduct')
            || !$this->registerHook('displayRightColumnProduct')
            || !$this->registerHook('displayProductTab')
            || !$this->registerHook('displayProductTabContent')
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('displayProductExtraContent')
            || !$this->registerHook('displayFooterProduct')
            || !$this->registerHook('overrideLayoutTemplate')
            || !$this->registerHook('displayNavFullWidth')
            || !$this->registerHook('displayFAQSearch')
            || !$this->registerHook('actionCreativeElementsInit')
            || !$this->registerHook('displayHiFAQCategory')
            || !$this->hiPrestaClass->createTabs('AdminFaq', 'AdminFaq', 'CONTROLLER_TABS_HI_FAQ', 0)
        ) {
            return false;
        }
        $this->proceedDb();

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $this->hiPrestaClass->deleteTabs('CONTROLLER_TABS_HI_FAQ');
        if (Configuration::get('CLEAN_HI_FAQ_DB')) {
            $this->proceedDb(true);
        }

        return true;
    }

    private function installDb()
    {
        $res = (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaq` (
                `id_faq` int unsigned NOT NULL AUTO_INCREMENT,
                `active` TINYINT  NOT NULL,
                `position` int unsigned NOT NULL,
                PRIMARY KEY (`id_faq`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaq_lang` (
                `id_faq` int unsigned NOT NULL,
                `id_lang` int unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `question` varchar(255) NOT NULL,
                `answer` text NOT NULL,
                `meta_title` varchar (255) NOT NULL,
                `meta_description` varchar (255) NOT NULL,
                `meta_keywords` varchar (255) NOT NULL,
                `friendly_url` varchar (255) NOT NULL,
              PRIMARY KEY (`id_faq`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaq_shop` (
                `id_faq` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id_faq`,`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqcategory` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `active` TINYINT  NOT NULL,
                `position` int unsigned NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqcategory_lang` (
                `id` int unsigned NOT NULL,
                `id_lang` int unsigned NOT NULL,
                `name` varchar(100) NOT NULL,
                `description` text NOT NULL,
                `meta_title` varchar(100) NOT NULL,
                `meta_description` varchar (100) NOT NULL,
                `friendly_url` varchar (255) NOT NULL,
              PRIMARY KEY (`id`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqpostcategory` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `id_faq` int unsigned NOT NULL,
                `id_category` int unsigned NOT NULL,
                PRIMARY KEY (`id`, `id_faq`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqcategory_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`,`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock` (
                `id_block` int unsigned NOT NULL AUTO_INCREMENT,
                `active` TINYINT  NOT NULL,
                `title_active` TINYINT  NOT NULL,
                `type` varchar (100) NOT NULL,
                `count` int NOT NULL,
                `hook` varchar (100) NOT NULL,
                `accordion` TINYINT  NOT NULL,
                `position` int unsigned NOT NULL,
                PRIMARY KEY (`id_block`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock_lang` (
                `id_block` int unsigned NOT NULL,
                `id_lang` int unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
              PRIMARY KEY (`id_block`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock_shop` (
                `id_block` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_block`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblockfaqs` (
                `id_block` int unsigned NOT NULL,
                `id_faq` int unsigned NOT NULL,
              PRIMARY KEY (`id_block`, `id_faq`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedproduct` (
                `id_hifaqrelatedproduct` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_faq` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `position` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_hifaqrelatedproduct`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedcategory` (
                `id_hifaqrelatedcategory` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_faq` int(10) unsigned NOT NULL,
                `id_category` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_hifaqrelatedcategory`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedproductfeature` (
                `id_hifaqrelatedproductfeature` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_faq` int(10) unsigned NOT NULL,
                `id_feature` int(10) unsigned NOT NULL,
                `id_feature_value` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_hifaqrelatedproductfeature`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool) Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqfeedback` (
                `id_feedback` int(10) unsigned not null auto_increment,
                `id_faq` int(10) unsigned not null,
                `id_customer` int(10) unsigned not null,
                `id_guest` int(10) unsigned not null,
                `ip_address` varchar(100) not null,
                `feedback` tinyint not null,
                `comment` text not null,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_feedback`, `id_faq`, `id_customer`, `id_guest`, `ip_address`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');

        return $res;
    }

    private function proceedDb($drop = false)
    {
        if ($this->psv >= 1.7) {
            $hook = 'displayProductAdditionalInfo';
        } else {
            $hook = 'displayRightColumnProduct';
        }
        if (!$drop) {
            Configuration::updateValue('CLEAN_HI_FAQ_DB', false);
            Configuration::updateValue('HI_FAQ_PRODUCT_PAGE_HOOK', $hook);
            Configuration::updateValue('HIFAQ_SIDEBAR_POSITION', 'left');
            Configuration::updateValue('HIFAQ_SEARCH', true);
            Configuration::updateValue('HIFAQ_LAYOUT', 3);
            Configuration::updateValue('HIFAQ_FAQS_COUNT', 3);
            Configuration::updateValue('HIFAQ_RELATED_PRODUCTS', true);
            Configuration::updateValue('HIFAQ_STRUCTURED_DATA', true);
            Configuration::updateValue('HIFAQ_FAQS_URL', 'faq');
            Configuration::updateValue('HIFAQ_CATEGORY_URL', 'category');
            Configuration::updateValue('HIFAQ_DETAILS_URL', 'faqs');
            Configuration::updateValue('HIFAQ_SEARCH_URL', 'search');

            Configuration::updateValue('HIFAQ_SEARCH_BG_COLOR', '#2fb5d2');
            Configuration::updateValue('HIFAQ_FEEDBACK', true);
            Configuration::updateValue('HIFAQ_FEEDBACK_POSITION', 1);
            Configuration::updateValue('HIFAQ_FEEDBACK_ACCORDION', 0);
            Configuration::updateValue('HIFAQ_FEEDBACK_COUNT', 0);

            if ($this->psv >= 1.7) {
                Configuration::updateValue('HIFAQ_ICONS', 'material');
            } else {
                Configuration::updateValue('HIFAQ_ICONS', 'fontAwesome');
            }
        } else {
            Configuration::deleteByName('CLEAN_HI_FAQ_DB');
            Configuration::deleteByName('HI_FAQ_PRODUCT_PAGE_HOOK');
            Configuration::deleteByName('HIFAQ_SIDEBAR_POSITION');
            Configuration::deleteByName('HIFAQ_SEARCH');
            Configuration::deleteByName('HIFAQ_LAYOUT');
            Configuration::deleteByName('HIFAQ_FAQS_COUNT');
            Configuration::deleteByName('HIFAQ_RELATED_PRODUCTS');
            Configuration::deleteByName('HIFAQ_STRUCTURED_DATA');
            Configuration::deleteByName('HIFAQ_FAQS_URL');
            Configuration::deleteByName('HIFAQ_CATEGORY_URL');
            Configuration::deleteByName('HIFAQ_DETAILS_URL');
            Configuration::deleteByName('HIFAQ_SEARCH_URL');

            Configuration::deleteByName('HIFAQ_SEARCH_BG_COLOR');
            Configuration::deleteByName('HIFAQ_CUSTOM_CSS');

            Configuration::deleteByName('HIFAQ_MAIN_META_TITLE');
            Configuration::deleteByName('HIFAQ_MAIN_META_DESCRIPTION');
            Configuration::deleteByName('HIFAQ_MAIN_META_KEYWORDS');
            Configuration::deleteByName('HIFAQ_MAIN_DESCRIPTION');

            Configuration::deleteByName('HIFAQ_FEEDBACK');
            Configuration::deleteByName('HIFAQ_FEEDBACK_POSITION');
            Configuration::deleteByName('HIFAQ_FEEDBACK_ACCORDION');
            Configuration::deleteByName('HIFAQ_FEEDBACK_LAST_SEEN');
            Configuration::deleteByName('HIFAQ_FEEDBACK_COUNT');

            Configuration::deleteByName('HIFAQ_ICONS');

            $dbTables = [
                'hifaq',
                'hifaq_lang',
                'hifaq_shop',
                'hifaqcategory',
                'hifaqcategory_lang',
                'hifaqcategory_shop',
                'hifaqpostcategory',
                'hifaqblock',
                'hifaqblock_lang',
                'hifaqblock_shop',
                'hifaqblockfaqs',
                'hifaqfeedback',
                'hifaqrelatedproduct',
                'hifaqrelatedcategory',
                'hifaqrelatedproductfeature',
            ];
            foreach ($dbTables as $table) {
                Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL($table) . '`');
            }

            $files = glob(_PS_MODULE_DIR_ . $this->name . '/views/img/upload/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($file != _PS_MODULE_DIR_ . $this->name . '/views/img/upload/index.php') {
                        unlink($file);
                    }
                }
            }
        }
    }

    private function globalVars()
    {
        $this->psv = (float) Tools::substr(_PS_VERSION_, 0, 3);
        $this->psv_round = ($this->psv == '1.6') ? '16' : '17';
        $this->secure_key = $this->hashKey($this->name);
        $this->clean_db = (bool) Configuration::get('CLEAN_HI_FAQ_DB');
        $this->product_page_hook = Configuration::get('HI_FAQ_PRODUCT_PAGE_HOOK');

        foreach (Language::getLanguages(false) as $lang) {
            $this->main_page_meta_title[$lang['id_lang']] = Configuration::get('HIFAQ_MAIN_META_TITLE', $lang['id_lang']);
            $this->main_page_meta_description[$lang['id_lang']] = Configuration::get('HIFAQ_MAIN_META_DESCRIPTION', $lang['id_lang']);
            $this->main_page_meta_keywords[$lang['id_lang']] = Configuration::get('HIFAQ_MAIN_META_KEYWORDS', $lang['id_lang']);
            $this->main_page_description[$lang['id_lang']] = Configuration::get('HIFAQ_MAIN_DESCRIPTION', $lang['id_lang']);
        }

        $this->sidebar_position = Configuration::get('HIFAQ_SIDEBAR_POSITION');
        $this->search = (bool) Configuration::get('HIFAQ_SEARCH');
        $this->layout = (int) Configuration::get('HIFAQ_LAYOUT');
        $this->faqs_count = (int) Configuration::get('HIFAQ_FAQS_COUNT');
        $this->related_products = (bool) Configuration::get('HIFAQ_RELATED_PRODUCTS');
        $this->structured_data = (bool) Configuration::get('HIFAQ_STRUCTURED_DATA');
        $this->faq_url = trim(Configuration::get('HIFAQ_FAQS_URL'));
        $this->category_url = trim(Configuration::get('HIFAQ_CATEGORY_URL'));
        $this->details_url = trim(Configuration::get('HIFAQ_DETAILS_URL'));
        $this->search_url = trim(Configuration::get('HIFAQ_SEARCH_URL'));

        // Design Settings
        $this->searchBgColor = Configuration::get('HIFAQ_SEARCH_BG_COLOR');
        $this->customCss = Configuration::get('HIFAQ_CUSTOM_CSS');

        // Feedback
        $this->feedback = (bool) Configuration::get('HIFAQ_FEEDBACK');
        $this->feedbackPosition = (int) Configuration::get('HIFAQ_FEEDBACK_POSITION');
        $this->feedbackAccordion = (bool) Configuration::get('HIFAQ_FEEDBACK_ACCORDION');
        $this->feedbackLastSeen = (int) Configuration::get('HIFAQ_FEEDBACK_LAST_SEEN');
        $this->feedbacksCount = (int) Configuration::get('HIFAQ_FEEDBACK_COUNT');

        $this->icons = Configuration::get('HIFAQ_ICONS');
    }

    private function hashKey($key)
    {
        if ($this->psv < 1.7) {
            return Tools::encrypt($key);
        }

        return Tools::hash($key);
    }

    public function renderMenuTabs()
    {
        $tabs = [
            'faqs' => [
                'title' => $this->l('FAQs'),
                'icon' => 'icon-book',
            ],
            'category_list' => [
                'title' => $this->l('Categories'),
                'icon' => 'icon-folder-open',
            ],
            'faq_list' => [
                'title' => $this->l('FAQ Blocks'),
                'icon' => 'icon-th',
            ],
            'sidebar' => [
                'title' => $this->l('Sidebar'),
                'icon' => 'icon-columns',
            ],
            'seo_settings' => [
                'title' => $this->l('SEO'),
                'icon' => 'icon-sitemap',
            ],
            'feedbackSettings' => [
                'title' => $this->l('Feedback Settings'),
                'icon' => 'icon-comment',
            ],
            'feedback' => [
                'title' => $this->l('Feedbacks'),
                'icon' => 'icon-comment',
                'counterTotal' => HiFAQFeedback::getTotalFeedbacksCount(),
                'counterNew' => HiFAQFeedback::getNewFeedbacksCount($this->feedbackLastSeen),
            ],
            'designSettings' => [
                'title' => $this->l('Design Settings'),
                'icon' => 'icon-paint-brush',
            ],
            'generel_settings' => [
                'title' => $this->l('General settings'),
                'icon' => 'icon-cog',
            ],
            'rateMe' => [
                'title' => $this->l('Leave a review'),
                'icon' => 'icon-star',
                'url' => $this->getRateUrl(),
                'target' => '_blank',
            ],
            'contactUs' => [
                'title' => $this->l('Contact Us'),
                'icon' => 'icon-support',
                'url' => $this->getContactUrl(),
                'target' => '_blank',
            ],
            'version' => [
                'title' => $this->l('Version'),
                'icon' => 'icon-info',
            ],
        ];
        $recommendations = $this->getModuleRecommendations();
        if ($recommendations) {
            $tabs['moreModules'] = [
                'title' => $this->l('More Modules'),
                'icon' => 'icon-puzzle-piece',
            ];
        }
        $this->context->smarty->assign(
            [
                'psv' => $this->psv,
                'tabs' => $tabs,
                'module_version' => $this->version,
                'module_url' => $this->hiPrestaClass->getModuleUrl(),
                'module_tab_key' => $this->name,
                'active_tab' => Tools::getValue($this->name),
            ]
        );

        return $this->display(__FILE__, 'views/templates/admin/menu_tabs.tpl');
    }

    public function getModuleRecommendations()
    {
        $recommendations = '';
        if (file_exists(__DIR__ . '/libs/hi-modules/modules.json')) {
            $recommendations = Tools::file_get_contents(__DIR__ . '/libs/hi-modules/modules.json');
            if ($recommendations) {
                $recommendations = json_decode($recommendations, true);
            }
        }

        return $recommendations ? $recommendations : [];
    }

    public function renderModuleAdvertisingForm()
    {
        $recommendations = $this->getModuleRecommendations();
        $this->context->smarty->assign('modules', $recommendations);

        return $this->display(__FILE__, 'views/templates/admin/hipresta-modules.tpl');
    }

    public function getRateUrl()
    {
        $langIsoCode = $this->context->language->iso_code;
        $psLanguages = ['en', 'fr', 'es', 'de', 'it', 'nl', 'pl', 'pt', 'ru'];

        if (in_array($langIsoCode, $psLanguages)) {
            return 'https://addons.prestashop.com/' . $this->context->language->iso_code . '/ratings.php';
        }

        return 'https://addons.prestashop.com/en/ratings.php';
    }

    public function getContactUrl()
    {
        $langIsoCode = $this->context->language->iso_code;
        $psLanguages = ['en', 'fr', 'es', 'de', 'it', 'nl', 'pl', 'pt', 'ru'];

        if (in_array($langIsoCode, $psLanguages)) {
            return 'https://addons.prestashop.com/' . $this->context->language->iso_code . '/contact-us?id_product=43454';
        }

        return 'https://addons.prestashop.com/en/contact-us?id_product=43454';
    }

    public function renderChangelog()
    {
        $changelog = '';
        if (file_exists(dirname(__FILE__) . '/changelog.txt')) {
            $changelog = Tools::file_get_contents(dirname(__FILE__) . '/changelog.txt');
        }
        $this->context->smarty->assign('changelog', $changelog);

        return $this->display(__FILE__, 'views/templates/admin/version.tpl');
    }

    public function renderDocumentation()
    {
        $this->context->smarty->assign([
            'docAssetsDir' => _MODULE_DIR_ . $this->name . '/libs/hi-modules-doc/img',
            'contactLink' => $this->getContactUrl(),
        ]);

        return $this->display(__FILE__, 'libs/hi-modules-doc/doc.tpl');
    }

    public function renderShopGroupError()
    {
        $this->context->smarty->assign(
            [
                'psv' => $this->psv,
            ]
        );

        return $this->display(__FILE__, 'views/templates/admin/shop_group_error.tpl');
    }

    public function renderModuleAdminVariables()
    {
        $this->context->smarty->assign(
            [
                'psv' => $this->psv,
                'id_lang' => $this->context->language->id,
                'faq_secure_key' => $this->secure_key,
                'faq_admin_controller_dir' => $this->context->link->getAdminLink('AdminFaq'),
            ]
        );

        return $this->display(__FILE__, 'views/templates/admin/variables.tpl');
    }

    public function renderDisplayForm($content)
    {
        $this->context->smarty->assign(
            [
                'psv' => $this->psv,
                'errors' => $this->errors,
                'success' => $this->success,
                'content' => $content,
            ]
        );

        return $this->display(__FILE__, 'views/templates/admin/display_form.tpl');
    }

    public function renderModalTpl($extra_page = false)
    {
        $this->context->smarty->assign(
            [
                'psv' => $this->psv,
                'extra_page' => $extra_page,
            ]
        );

        return $this->display(__FILE__, 'views/templates/admin/modal.tpl');
    }

    public function returnFaqFrontUrl($url_type, $id = '', $link_rewrite = '')
    {
        $url = '';
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            if ($url_type == 'faq') {
                $url .= $this->context->link->getPageLink('faqs', null, null);
            } elseif ($url_type == 'category') {
                $url .= $this->context->link->getPageLink('hi-faq-category', null, null, ['id_category' => $id, 'faq_link_rewrite' => Tools::str2url($link_rewrite)]);
            }
        } else {
            if ($url_type == 'faq') {
                $url .= $this->context->link->getModuleLink('hifaq', 'faq');
            } elseif ($url_type === 'category') {
                $url .= $this->context->link->getModuleLink('hifaq', 'faqcategory');
                if ($link_rewrite != '') {
                    $url .= '&faqc_link_rewrite=' . $link_rewrite;
                }
            }
        }

        return $url;
    }

    public function setMultilangValue($name, $id_lang, $id_lang_default = null)
    {
        if (!$id_lang_default) {
            $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        }

        if (!Tools::getValue($name . '_' . $id_lang)) {
            return Tools::getValue($name . '_' . $id_lang_default);
        }

        return Tools::getValue($name . '_' . $id_lang);
    }

    public function saveFAQ($id_faq = null)
    {
        $faq = new HiFAQItem($id_faq);
        $faq->active = Tools::getValue('active');
        $faq->id_product = (int) Tools::getValue('id_product');

        if (!$id_faq) {
            $faq->position = HiFAQItem::getPosition();
        }

        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');

        foreach (Language::getLanguages(false) as $lang) {
            $faq->title[$lang['id_lang']] = $this->setMultilangValue('title', $lang['id_lang'], $id_lang_default);
            $faq->question[$lang['id_lang']] = $this->setMultilangValue('question', $lang['id_lang'], $id_lang_default);
            $faq->answer[$lang['id_lang']] = $this->setMultilangValue('answer', $lang['id_lang'], $id_lang_default);
            $faq->meta_title[$lang['id_lang']] = $this->setMultilangValue('meta_title', $lang['id_lang'], $id_lang_default);
            $faq->meta_description[$lang['id_lang']] = $this->setMultilangValue('meta_description', $lang['id_lang'], $id_lang_default);
            $faq->meta_keywords[$lang['id_lang']] = $this->setMultilangValue('meta_keywords', $lang['id_lang'], $id_lang_default);
            $faq->friendly_url[$lang['id_lang']] = $this->setMultilangValue('friendly_url', $lang['id_lang'], $id_lang_default);
        }

        if (!$faq->save()) {
            return false;
        }

        if (!$id_faq) {
            $id_faq = $faq->id;
        }

        $this->assignFAQToShops($id_faq);

        Db::getInstance()->delete('hifaqpostcategory', '`id_faq` = ' . (int) $id_faq);

        $faq_categories = Tools::getValue('faq_category');
        if (is_array($faq_categories) && $faq_categories) {
            $faqpostcategory = new HiFAQPostCategory();
            $faqpostcategory->id_faq = (int) $id_faq;

            foreach ($faq_categories as $id_category) {
                $faqpostcategory->id_category = $id_category;
                $faqpostcategory->add();
            }
        }

        return $id_faq;
    }

    public function assignFAQToShops($id_faq)
    {
        $shop_ids = [];

        if (Shop::isFeatureActive()) {
            $shop_group = Tools::getValue('checkBoxShopGroupAsso_hifaq');
            if (is_array($shop_group) && $shop_group) {
                foreach ($shop_group as $shops) {
                    foreach (ShopGroup::getShopsFromGroup($shops) as $shop) {
                        $shop_ids[] = $shop['id_shop'];
                    }
                }
            }
            $shops = Tools::getValue('checkBoxShopAsso_hifaq');
            if (is_array($shops) && $shops) {
                foreach ($shops as $id) {
                    if (!in_array($id, $shop_ids)) {
                        $shop_ids[] = $id;
                    }
                }
            }

            // The post should be assigned at least to 1 shop.
            if (!$shop_ids) {
                $shop_ids[] = $this->context->shop->id;
            }
        } else {
            $shop_ids[] = $this->context->shop->id;
        }

        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hifaq_shop` WHERE id_faq = ' . (int) $id_faq);
        if (is_array($shop_ids) && $shop_ids) {
            foreach ($shop_ids as $id) {
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'hifaq_shop` (`id_faq`, `id_shop`)
                    VALUES(' . (int) $id_faq . ', ' . (int) $id . ')');
            }
        }
    }

    public function saveCategory($id_category = null)
    {
        $languages = Language::getLanguages(false);
        $category = new HiFAQCategory($id_category);

        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        foreach ($languages as $lang) {
            $category->name[$lang['id_lang']] = $this->setMultilangValue('name', $lang['id_lang'], $id_lang_default);
            $category->description[$lang['id_lang']] = $this->setMultilangValue('description', $lang['id_lang'], $id_lang_default);
            $category->meta_title[$lang['id_lang']] = $this->setMultilangValue('meta_title', $lang['id_lang'], $id_lang_default);
            $category->meta_description[$lang['id_lang']] = $this->setMultilangValue('meta_description', $lang['id_lang'], $id_lang_default);
            $category->friendly_url[$lang['id_lang']] = $this->setMultilangValue('friendlyurl', $lang['id_lang'], $id_lang_default);
        }
        $category->active = Tools::getValue('active');

        if (!$id_category) {
            $category->position = HiFAQCategory::getPosition();
        }

        if (!$category->save()) {
            return false;
        }

        if (!$id_category) {
            $id_category = $category->id;
        }

        $this->assignCategoryToShops($id_category);

        return $id_category;
    }

    public function assignCategoryToShops($id_category)
    {
        $shop_ids = [];

        if (Shop::isFeatureActive()) {
            $shop_group = Tools::getValue('checkBoxShopGroupAsso_hifaqcategory');
            if (is_array($shop_group) && $shop_group) {
                foreach ($shop_group as $shops) {
                    foreach (ShopGroup::getShopsFromGroup($shops) as $shop) {
                        $shop_ids[] = $shop['id_shop'];
                    }
                }
            }
            $shops = Tools::getValue('checkBoxShopAsso_hifaqcategory');
            if (is_array($shops) && $shops) {
                foreach ($shops as $id) {
                    if (!in_array($id, $shop_ids)) {
                        $shop_ids[] = $id;
                    }
                }
            }

            // The post should be assigned at least to 1 shop.
            if (!$shop_ids) {
                $shop_ids[] = $this->context->shop->id;
            }
        } else {
            $shop_ids[] = $this->context->shop->id;
        }

        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hifaqcategory_shop` WHERE id = ' . (int) $id_category);
        if (is_array($shop_ids) && $shop_ids) {
            foreach ($shop_ids as $id) {
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'hifaqcategory_shop` (`id`, `id_shop`)
                    VALUES(' . (int) $id_category . ', ' . (int) $id . ')');
            }
        }
    }

    public function saveBlock($id_block = null)
    {
        $languages = Language::getLanguages(false);
        $block = new HiFAQBlock($id_block);
        $block->active = (bool) Tools::getValue('block_active');
        $block->title_active = pSQL(Tools::getValue('block_title_active'));
        foreach ($languages as $lang) {
            $block->title[$lang['id_lang']] = $this->setMultilangValue('block_title', $lang['id_lang']);
        }
        $block->type = pSQL(Tools::getValue('block_type'));
        $block->count = (int) Tools::getValue('block_count');
        $block->hook = pSQL(Tools::getValue('block_position'));
        $block->accordion = (bool) Tools::getValue('accordion');

        if (!$id_block) {
            $block->position = HiFAQBlock::getPosition();
        }
        if (!$block->save()) {
            return false;
        }

        if (!$id_block) {
            $id_block = $block->id;
        }

        if ($block->type == 'categoryFaqs') {
            $this->saveBlockSetting($id_block, 'HI_FAQ_BLOCK_CATEGORY', (int) Tools::getValue('block_category'));
        }

        $this->assignBlockToShops($id_block);

        return $id_block;
    }

    public function assignBlockToShops($id_block)
    {
        $shop_ids = [];

        if (Shop::isFeatureActive()) {
            $shop_group = Tools::getValue('checkBoxShopGroupAsso_hifaqblock');
            if (is_array($shop_group) && $shop_group) {
                foreach ($shop_group as $shops) {
                    foreach (ShopGroup::getShopsFromGroup($shops) as $shop) {
                        $shop_ids[] = $shop['id_shop'];
                    }
                }
            }
            $shops = Tools::getValue('checkBoxShopAsso_hifaqblock');
            if (is_array($shops) && $shops) {
                foreach ($shops as $id) {
                    if (!in_array($id, $shop_ids)) {
                        $shop_ids[] = $id;
                    }
                }
            }

            // The post should be assigned at least to 1 shop.
            if (!$shop_ids) {
                $shop_ids[] = $this->context->shop->id;
            }
        } else {
            $shop_ids[] = $this->context->shop->id;
        }

        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'hifaqblock_shop` WHERE id_block = ' . (int) $id_block);
        if (is_array($shop_ids) && $shop_ids) {
            foreach ($shop_ids as $id) {
                Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'hifaqblock_shop` (`id_block`, `id_shop`)
                    VALUES(' . (int) $id_block . ', ' . (int) $id . ')');
            }
        }
    }

    public function renderFakeForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Fake'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'label' => $this->l('Fake'),
                        'name' => 'fake',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int) ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->tpl_vars = [
            'fields_value' => ['fake' => ''],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getMainURL($type = null)
    {
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            return $this->context->link->getPageLink('module-' . $this->name . '-faq', null, null, ['type' => $type]);
        } else {
            return $this->context->link->getModuleLink('hifaq', 'faq', ['type' => $type]);
        }
    }

    public function getCategoryURL($link_rewrite, $idLang = null)
    {
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            return $this->context->link->getPageLink('module-' . $this->name . '-faqcategory', null, $idLang, ['faqc_link_rewrite' => $link_rewrite]);
        } else {
            return $this->context->link->getModuleLink('hifaq', 'faqcategory', ['faqc_link_rewrite' => $link_rewrite], null, $idLang);
        }
    }

    public function getSearchURL($query = null)
    {
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            return $this->context->link->getPageLink('module-' . $this->name . '-faqsearch', null, null, ['query' => $query]);
        } else {
            return $this->context->link->getModuleLink('hifaq', 'faqsearch', ['query' => $query]);
        }
    }

    public function getFAQURL($link_rewrite, $id_lang = null)
    {
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            return $this->context->link->getPageLink('module-' . $this->name . '-faqdetails', null, $id_lang, ['faq_link_rewrite' => $link_rewrite]);
        } else {
            return $this->context->link->getModuleLink('hifaq', 'faqdetails', [], null, $id_lang) . '&faq_link_rewrite=' . $link_rewrite;
        }
    }

    public function getFaqUrlById($id_faq, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }

        $faq = HiFAQItem::getDetailsByID($id_faq, $id_lang);

        if (empty($faq['id_faq'])) {
            return '';
        }

        return $this->getFAQURL($faq['friendly_url'], $id_lang);
    }

    public function getCategoryUrlById($idCategory, $idLang = null)
    {
        if (!$idLang) {
            $idLang = $this->context->language->id;
        }

        $category = HiFAQCategory::getCategoryById($idCategory, $idLang);

        if (empty($category['id'])) {
            return '';
        }

        return $this->getCategoryURL($category['friendly_url'], $idLang);
    }

    private function clearPSRouteCaches()
    {
        $moduleKey = 'PS_ROUTE_module-' . $this->name;
        $configs = Db::getInstance()->executeS('SELECT `name` from `' . _DB_PREFIX_ . 'configuration` WHERE `name` like \'%' . $moduleKey . '%\'');

        if (is_array($configs) && $configs) {
            foreach ($configs as $config) {
                Configuration::deleteByName($config['name']);
            }
        }

        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_settings_form')) {
            Configuration::updateValue('CLEAN_HI_FAQ_DB', (bool) Tools::getValue('clean_db'));
            Configuration::updateValue('HI_FAQ_PRODUCT_PAGE_HOOK', Tools::getValue('product_page_hook'));
            Configuration::updateValue('HIFAQ_SEARCH', (bool) Tools::getValue('search'));
            Configuration::updateValue('HIFAQ_LAYOUT', (int) Tools::getValue('layout'));
            Configuration::updateValue('HIFAQ_FAQS_COUNT', (int) Tools::getValue('faqs_count'));
            Configuration::updateValue('HIFAQ_RELATED_PRODUCTS', (bool) Tools::getValue('related_products'));

            Configuration::updateValue('HIFAQ_ICONS', Tools::getValue('icons'));

            $this->success[] = $this->l('Successfuly Saved');
        }

        if (Tools::isSubmit('submit_sidebar_settings')) {
            Configuration::updateValue('HIFAQ_SIDEBAR_POSITION', Tools::getValue('sidebar_position'));

            $this->success[] = $this->l('Sidebar settings updated successfully');
        }

        if (Tools::isSubmit('submit_seo_form')) {
            Configuration::updateValue('HIFAQ_STRUCTURED_DATA', (bool) Tools::getValue('structured_data'));
            Configuration::updateValue('HIFAQ_FAQS_URL', trim(Tools::getValue('faq_url')));
            Configuration::updateValue('HIFAQ_CATEGORY_URL', trim(Tools::getValue('category_url')));
            Configuration::updateValue('HIFAQ_DETAILS_URL', trim(Tools::getValue('details_url')));
            Configuration::updateValue('HIFAQ_SEARCH_URL', trim(Tools::getValue('search_url')));

            foreach (Language::getLanguages(false) as $lang) {
                Configuration::updateValue(
                    'HIFAQ_MAIN_META_TITLE',
                    [$lang['id_lang'] => Tools::getValue('main_page_meta_title_' . $lang['id_lang'])]
                );
                Configuration::updateValue(
                    'HIFAQ_MAIN_META_DESCRIPTION',
                    [$lang['id_lang'] => Tools::getValue('main_page_meta_description_' . $lang['id_lang'])]
                );
                Configuration::updateValue(
                    'HIFAQ_MAIN_META_KEYWORDS',
                    [$lang['id_lang'] => Tools::getValue('main_page_meta_keywords_' . $lang['id_lang'])]
                );
                Configuration::updateValue(
                    'HIFAQ_MAIN_DESCRIPTION',
                    [$lang['id_lang'] => Tools::getValue('main_page_description_' . $lang['id_lang'])],
                    true
                );
            }

            $this->clearPSRouteCaches();

            $this->success[] = $this->l('SEO Settings successfully updated');
        }

        if (Tools::isSubmit('submitDesignSettingsForm')) {
            Configuration::updateValue('HIFAQ_SEARCH_BG_COLOR', Tools::getValue('searchBgColor'));
            Configuration::updateValue('HIFAQ_CUSTOM_CSS', Tools::getValue('customCss'));

            $this->success[] = $this->l('Design Settings successfully updated');
        }

        if (Tools::isSubmit('submitFeedbackForm')) {
            Configuration::updateValue('HIFAQ_FEEDBACK', (bool) Tools::getValue('feedback'));
            Configuration::updateValue('HIFAQ_FEEDBACK_POSITION', (int) Tools::getValue('feedbackPosition'));
            Configuration::updateValue('HIFAQ_FEEDBACK_ACCORDION', (bool) Tools::getValue('feedbackAccordion'));
            Configuration::updateValue('HIFAQ_FEEDBACK_COUNT', (bool) Tools::getValue('feedbacksCount'));

            $this->success[] = $this->l('Feedback Settings successfully updated');
        }
    }

    public function displayForm()
    {
        $html = '';
        $content = '';
        if (!$this->hiPrestaClass->isSelectedShopGroup()) {
            $html .= $this->renderMenuTabs();
            switch (Tools::getValue($this->name)) {
                case 'generel_settings':
                    $content .= $this->adminForms->renderSettingsForm();
                    break;
                case 'faqs':
                    $this->renderTreeJS();
                    $content .= $this->renderModalTpl();
                    $content .= $this->adminForms->renderFAQsList();
                    $content .= $this->renderFakeForm();
                    break;
                case 'category_list':
                    $this->renderTreeJS();
                    $content .= $this->renderModalTpl();
                    $content .= $this->adminForms->renderCategoriesList();
                    $content .= $this->renderFakeForm();
                    break;
                case 'faq_list':
                    $this->renderTreeJS();
                    $content .= $this->renderModalTpl();
                    $content .= $this->adminForms->renderBlocksList();
                    break;
                case 'sidebar':
                    $content .= $this->adminForms->renderSidebarSettings();
                    break;
                case 'seo_settings':
                    $content .= $this->adminForms->renderSEOForm();
                    break;
                case 'feedbackSettings':
                    $content .= $this->adminForms->renderFeedbackForm();
                    break;
                case 'feedback':
                    $content .= $this->adminForms->renderFeedbackList();

                    Configuration::updateValue('HIFAQ_FEEDBACK_LAST_SEEN', (int) HiFAQFeedback::getLatestFeedbackId());
                    break;
                case 'designSettings':
                    $content .= $this->adminForms->renderDesignSettingsForm();
                    break;
                case 'version':
                    $content .= $this->renderChangelog();
                    break;
                case 'moreModules':
                    $content .= $this->renderModuleAdvertisingForm();
                    break;
                default:
                    $this->renderTreeJS();
                    $content .= $this->renderModalTpl();
                    $content .= $this->adminForms->renderFAQsList();
                    $content .= $this->renderFakeForm();
                    break;
            }
            $html .= $this->renderDisplayForm($content);
            $html .= $this->renderDocumentation();
        } else {
            $html .= $this->renderShopGroupError();
        }

        $html .= $this->renderModuleAdminVariables();

        // Select2
        $this->context->controller->addCSS($this->_path . 'libs/select2/select2.min.css', 'all');
        $this->context->controller->addCSS($this->_path . 'libs/select2/select2-bootstrap.min.css', 'all');
        $this->context->controller->addJS($this->_path . 'libs/select2/select2.min.js');

        $this->context->controller->addCSS($this->_path . 'libs/hi-modules-table/table.css', 'all');
        $this->context->controller->addJS($this->_path . 'libs/hi-modules-table/table.js');

        $this->context->controller->addCSS($this->_path . 'libs/magnific-popup/magnific-popup.css', 'all');
        $this->context->controller->addJS($this->_path . 'libs/magnific-popup/jquery.magnific-popup.min.js');

        $this->context->controller->addCSS($this->_path . 'libs/hi-modules-doc/doc.css', 'all');
        $this->context->controller->addJS($this->_path . 'libs/hi-modules-doc/doc.js');

        $this->context->controller->addCSS($this->_path . 'views/css/admin.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');

        $this->context->controller->addJqueryUI('ui.sortable');

        if (Tools::getValue($this->name) == 'designSettings') {
            // CSS editor
            $this->context->controller->addCSS($this->_path . 'libs/codemirror/codemirror.css', 'all');
            $this->context->controller->addJS($this->_path . 'libs/codemirror/codemirror.js');
            $this->context->controller->addJS($this->_path . 'libs/codemirror/css.js');

            // color picker
            $this->context->controller->addCSS($this->_path . 'libs/spectrum/spectrum.min.css', 'all');
            $this->context->controller->addJS($this->_path . 'libs/spectrum/spectrum.min.js');
        }

        return $html;
    }

    public function renderTreeJS()
    {
        if ($this->psv > 1.6) {
            return;
        }

        $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);
        $bo_theme = ((Validate::isLoadedObject($this->context->employee)
            && $this->context->employee->bo_theme) ? $this->context->employee->bo_theme : 'default');

        if (!file_exists(_PS_BO_ALL_THEMES_DIR_ . $bo_theme . DIRECTORY_SEPARATOR . 'template')) {
            $bo_theme = 'default';
        }

        $js_path = __PS_BASE_URI__ . $admin_webpath . '/themes/' . $bo_theme . '/js/tree.js?v=' . _PS_VERSION_;

        $this->context->controller->addJs($js_path);
    }

    public function getContent()
    {
        // for ($i=1; $i < 108; $i++) {
        //     $faq = new HiFAQItem();
        //     $faq->position = HiFAQItem::getPosition();
        //     foreach (Language::getLanguages(false) as $lang) {
        //         $faq->title[$lang['id_lang']] = 'Title' . ' ' . $i;
        //         $faq->question[$lang['id_lang']] = 'Question ' . $i;
        //         $faq->answer[$lang['id_lang']] = 'Answer ' . $i;
        //         $faq->friendly_url[$lang['id_lang']] = 'test-faq-' . $i;
        //     }

        //     $faq->add();

        //     $id_faq = $faq->id;

        //     $this->assignFAQToShops($id_faq);
        // }
        if (Tools::isSubmit('submit_settings_form')
            || Tools::isSubmit('submit_sidebar_settings')
            || Tools::isSubmit('submit_seo_form')
            || Tools::isSubmit('submitDesignSettingsForm')
            || Tools::isSubmit('submitFeedbackForm')
        ) {
            $this->postProcess();

            Configuration::loadConfiguration();
            $this->globalVars();
        }

        return $this->displayForm();
    }

    public function isCategoryFriendlyURLExists($friendly_url, $id_lang, $id_category)
    {
        return Db::getInstance()->getValue('
            SELECT id
            FROM `' . _DB_PREFIX_ . 'hifaqcategory_lang`
            WHERE `friendly_url` = \'' . pSQL($friendly_url) . '\'
            AND id_lang = ' . (int) $id_lang . '
            AND id != ' . (int) $id_category . '
        ');
    }

    public function isFAQFriendlyURLExists($friendly_url, $id_lang, $id_faq)
    {
        return Db::getInstance()->getValue('
            SELECT id_faq
            FROM `' . _DB_PREFIX_ . 'hifaq_lang`
            WHERE `friendly_url` = \'' . pSQL($friendly_url) . '\'
            AND id_lang = ' . (int) $id_lang . '
            AND id_faq != ' . (int) $id_faq . '
        ');
    }

    public function isFAQController()
    {
        return isset($this->context->controller->module) && $this->context->controller->module instanceof HiFaq;
    }

    public function hookOverrideLayoutTemplate($params)
    {
        $faq_pages = ['module-hifaq-faq', 'module-hifaq-faqcategory', 'module-hifaq-faqdetails'];
        if (in_array($params['entity'], $faq_pages)) {
            if ($this->sidebar_position == 'left') {
                return 'layouts/layout-left-column.tpl';
            } elseif ($this->sidebar_position == 'right') {
                return 'layouts/layout-right-column.tpl';
            } else {
                return 'layouts/layout-full-width.tpl';
            }
        }
    }

    public function renderSearchResults($faqs)
    {
        if (!is_array($faqs) || !$faqs) {
            $faqs = [];
        }

        foreach ($faqs as $key => $faq) {
            $faqs[$key]['url'] = $this->getFAQURL($faq['friendly_url']);
        }

        $this->context->smarty->assign([
            'faqs' => $faqs,
        ]);

        return $this->display(__FILE__, 'search-results.tpl');
    }

    public function getAdminProductDetails($products)
    {
        if (!$products || !is_array($products)) {
            return [];
        }

        $product_details = [];
        $i = 0;
        $id_language = $this->context->language->id;
        foreach ($products as $res) {
            $product = new Product($res['id_product'], true, $id_language, Shop::getContextShopID());
            if (Validate::isLoadedObject($product)) {
                $product_details[$i]['name'] = $product->name;
                $product_details[$i]['reference'] = $product->reference;
                $product_details[$i]['id_product'] = $res['id_product'];
                $product_details[$i]['link'] = $this->context->link->getProductLink($product);
                $cover_image = $product->getCover($res['id_product']);

                if ($cover_image) {
                    $product_details[$i]['img_link'] = $this->context->link->getImageLink(
                        $product->link_rewrite ? $product->link_rewrite : 'no-link-rewrite',
                        $cover_image['id_image'],
                        $this->hiPrestaClass->getImageType('home')
                    );
                } else {
                    $product_details[$i]['img_link'] = $this->context->link->getImageLink(
                        $product->link_rewrite ? $product->link_rewrite : 'no-link-rewrite',
                        $product->defineProductImage(
                            $product->getImages(
                                $id_language
                            ),
                            $id_language
                        ),
                        $this->hiPrestaClass->getImageType('home')
                    );
                }
                ++$i;
            }
        }

        return $product_details;
    }

    public function getRelatedProductPosition($id_faq)
    {
        $position = Db::getInstance()->getValue('
            SELECT MAX(position)
            FROM ' . _DB_PREFIX_ . 'hifaqrelatedproduct
            WHERE id_faq = ' . (int) $id_faq);

        if (!$position) {
            return 1;
        }

        return $position + 1;
    }

    public function addRelatedProduct($id_faq, $id_product)
    {
        return Db::getInstance()->insert('hifaqrelatedproduct', [
            'id_faq' => (int) $id_faq,
            'id_product' => (int) $id_product,
            'position' => $this->getRelatedProductPosition($id_faq),
        ]);
    }

    public function deleteRelatedProduct($id_faq, $id_product)
    {
        return Db::getInstance()->delete('hifaqrelatedproduct', '`id_faq` = ' . (int) $id_faq . ' AND `id_product` = ' . (int) $id_product);
    }

    public function relatedProductExists($id_faq, $id_product)
    {
        return Db::getInstance()->getValue('
            SELECT id_hifaqrelatedproduct
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedproduct`
            WHERE `id_faq` = ' . (int) $id_faq . '
            AND id_product = ' . (int) $id_product . '
        ');
    }

    public function getRelatedProducts($id_faq)
    {
        return Db::getInstance()->ExecuteS('
            SELECT `id_product`
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedproduct`
            WHERE `id_faq` = ' . (int) $id_faq . '
            ORDER BY `position` ASC
        ');
    }

    public function updateRelatedProductPosition($id_faq, $id_product, $position)
    {
        Db::getInstance()->Execute('
            UPDATE ' . _DB_PREFIX_ . 'hifaqrelatedproduct 
            SET position=' . (int) $position . '
            WHERE id_faq =' . (int) $id_faq . ' AND id_product =' . (int) $id_product . '
        ');
    }

    public function renderRelatedProducts($id_faq)
    {
        $this->context->smarty->assign([
            'relatedProducts' => $this->getAdminProductDetails($this->getRelatedProducts($id_faq)),
            'id_faq' => $id_faq,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/_related-products/related-products.tpl');
    }

    public function getRelatedFeature($idFaq, $idFeature, $idFeatureValue)
    {
        return Db::getInstance()->getRow('
            SELECT * FROM `' . _DB_PREFIX_ . 'hifaqrelatedproductfeature`
            WHERE id_faq = ' . (int) $idFaq . '
            AND id_feature = ' . (int) $idFeature . '
            AND id_feature_value = ' . (int) $idFeatureValue
        );
    }

    public function getRelatedFeatureByFaq($idFaq)
    {
        return Db::getInstance()->executeS('
            SELECT * FROM `' . _DB_PREFIX_ . 'hifaqrelatedproductfeature`
            WHERE id_faq = ' . (int) $idFaq
        );
    }

    public function addRelatedFeature($idFaq, $idFeature, $idFeatureValue)
    {
        return Db::getInstance()->insert('hifaqrelatedproductfeature', [
            'id_faq' => (int) $idFaq,
            'id_feature' => (int) $idFeature,
            'id_feature_value' => (int) $idFeatureValue,
        ]);
    }

    public function removeRelatedFeature($idFaq, $idFaqFeature)
    {
        return Db::getInstance()->delete('hifaqrelatedproductfeature', '
            `id_faq` = ' . (int) $idFaq . '
            AND `id_hifaqrelatedproductfeature` = ' . (int) $idFaqFeature
        );
    }

    public function renderAddRelatedProductForm($idFaq)
    {
        $this->context->smarty->assign([
            'id_faq' => $idFaq,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/_related-products/add-related-product-form.tpl');
    }

    public function renderRelatedFeaturesForm($idFaq)
    {
        $features = Feature::getFeatures($this->context->language->id);
        $this->context->smarty->assign([
            'id_faq' => $idFaq,
            'features' => $features,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/_related-products/related-features-form.tpl');
    }

    public function renderRelatedFeatures($idFaq)
    {
        $features = $this->getRelatedFeatureByFaq($idFaq);
        if (is_array($features) && $features) {
            foreach ($features as $key => $feature) {
                $featureData = Feature::getFeature($this->context->language->id, $feature['id_feature']);
                $featureValue = Db::getInstance()->getValue('
                    SELECT `value`
                    FROM `' . _DB_PREFIX_ . 'feature_value_lang`
                    WHERE `id_feature_value` = ' . (int) $feature['id_feature_value'] . '
                    AND `id_lang` = ' . (int) $this->context->language->id . '
                ');

                $features[$key]['featureName'] = $featureData['name'];
                $features[$key]['featureValue'] = $featureValue;
            }
        }

        $this->context->smarty->assign([
            'id_faq' => $idFaq,
            'features' => $features,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/_related-products/related-features.tpl');
    }

    public function renderRelatedProductsModal($idFaq)
    {
        $this->context->smarty->assign([
            'relatedProducts' => $this->renderRelatedProducts($idFaq),
            'addProductForm' => $this->renderAddRelatedProductForm($idFaq),
            'renderRelatedFeatures' => $this->renderRelatedFeatures($idFaq),
            'relatedFeaturesForm' => $this->renderRelatedFeaturesForm($idFaq),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/_related-products/related-products-modal.tpl');
    }

    public function addRelatedCategory($id_faq, $id_category)
    {
        return Db::getInstance()->insert('hifaqrelatedcategory', [
            'id_faq' => (int) $id_faq,
            'id_category' => (int) $id_category,
        ]);
    }

    public function getRelatedCategories($id_faq)
    {
        return Db::getInstance()->ExecuteS('
            SELECT `id_category`
            FROM `' . _DB_PREFIX_ . 'hifaqrelatedcategory`
            WHERE `id_faq` = ' . (int) $id_faq);
    }

    public function deleteRelatedCategories($id_faq)
    {
        return Db::getInstance()->delete('hifaqrelatedcategory', '`id_faq` = ' . (int) $id_faq);
    }

    public function prepareProductsForFront17($products)
    {
        if (!is_array($products) || count($products) == 0) {
            return;
        }
        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
                new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        } else {
            $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
                new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        }

        $products_for_template = [];

        foreach ($products as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }

    public function prepareProductsForFront16($result)
    {
        $link = new Link();
        $products = [];
        $product_details = [];
        $i = 0;
        $id_language = $this->context->language->id;
        if (!empty($result)) {
            foreach ($result as $res) {
                $products[$i] = new Product($res['id_product'], true, $id_language, Shop::getContextShopID());
                if (Validate::isLoadedObject($products[$i])) {
                    if ($products[$i]->active) {
                        $quantity = $products[$i]->getQuantity(
                            $res['id_product'],
                            $products[$i]->getDefaultAttribute($res['id_product'])
                        );
                        $product_details[$i]['name'] = $products[$i]->name;
                        $product_details[$i]['reference'] = $products[$i]->reference;
                        $product_details[$i]['description_short'] = $products[$i]->description_short;
                        $product_details[$i]['minimal_quantity'] = $products[$i]->minimal_quantity;
                        $product_details[$i]['id_product'] = $res['id_product'];
                        $product_details[$i]['link_rewrite'] = $products[$i]->link_rewrite;
                        $product_details[$i]['link'] = $link->getProductLink($res['id_product']);
                        $product_details[$i]['available_for_order'] = $products[$i]->available_for_order;
                        $product_details[$i]['show_price'] = $products[$i]->show_price;
                        $product_details[$i]['specific_prices'] = $products[$i]->specificPrice;
                        $product_details[$i]['customizable'] = $products[$i]->customizable;
                        $product_details[$i]['available_later'] = $products[$i]->available_later;
                        $product_details[$i]['available_now'] = $products[$i]->available_now;
                        $product_details[$i]['id_manufacturer'] = $products[$i]->id_manufacturer;
                        $product_details[$i]['id_supplier'] = $products[$i]->id_supplier;
                        $product_details[$i]['color_list'] = $this->getColorlist($res['id_product']);
                        $product_details[$i]['allow_oosp'] = $products[$i]->isAvailableWhenOutOfStock(
                            StockAvailable::outOfStock($res['id_product'])
                        );
                        $product_details[$i]['quantity'] = $quantity;
                        $product_details[$i]['quantity_all_versions'] = $products[$i]->getQuantity($res['id_product']);
                        $price_tax_exc = $products[$i]->getPrice(false);
                        $product_details[$i]['price_tax_exc'] = Tools::displayPrice((float) $price_tax_exc);
                        $product_details[$i]['price_without_reduction'] = $products[$i]->getPriceWithoutReduct(false, null);
                        $price = $products[$i]->getPriceStatic(trim($res['id_product']), true, null, 2);
                        $product_details[$i]['price'] = Tools::displayPrice((float) $price);
                        $product_details[$i]['new'] = $products[$i]->new;

                        $def_attr = $products[$i]->getDefaultAttribute($res['id_product']);
                        if (!empty($def_attr)) {
                            $product_details[$i]['id_product_attribute'] = $def_attr;
                        } else {
                            $product_details[$i]['id_product_attribute'] = 0;
                        }
                        $cover_image = $products[$i]->getCover($res['id_product']);
                        $legend = $products[$i]->getImages($id_language);
                        foreach ($legend as $leg) {
                            if ($leg['cover'] == 1) {
                                $product_details[$i]['legend'] = $leg['legend'];
                            }
                        }
                        /* img_link for custom_admin_product.tpl */
                        $avalibale_image = Image::getImages($id_language, $res['id_product']);
                        if (Configuration::get('PS_LEGACY_IMAGES')) {
                            $ids = $products[$i]->id . '-' . $cover_image['id_image'];
                        } else {
                            $ids = $cover_image['id_image'];
                        }
                        if (!empty($avalibale_image)) {
                            $product_details[$i]['id_image'] = $ids;
                            $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()) . $link->getImageLink(
                                $products[$i]->link_rewrite,
                                $cover_image['id_image'],
                                $this->hiPrestaClass->getImageType('home')
                            );
                        } else {
                            $product_details[$i]['id_image'] = $products[$i]->defineProductImage(
                                $products[$i]->getImages(
                                    $id_language
                                ),
                                $id_language
                            );
                            $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()) . $link->getImageLink(
                                $products[$i]->link_rewrite,
                                $products[$i]->defineProductImage(
                                    $products[$i]->getImages(
                                        $id_language
                                    ),
                                    $id_language
                                ),
                                $this->hiPrestaClass->getImageType('home')
                            );
                        }
                        ++$i;
                    }
                }
            }
        }

        return $product_details;
    }

    public function getColorlist($id_product = '')
    {
        if ($id_product == '' || !file_exists(_PS_THEME_DIR_ . 'product-list-colors.tpl')) {
            return;
        }
        $products_need_cache = [];
        $products_need_cache[] = (int) $id_product;
        $colors = false;
        if (count($products_need_cache)) {
            $colors = Product::getAttributesColorList($products_need_cache);
        }
        $tpl = $this->context->smarty->createTemplate(_PS_THEME_DIR_ . 'product-list-colors.tpl', Product::getColorsListCacheId($id_product));
        if (isset($colors[$id_product])) {
            $tpl->assign([
                'id_product' => $id_product,
                'colors_list' => $colors[$id_product],
                'link' => Context::getContext()->link,
                'img_col_dir' => _THEME_COL_DIR_,
                'col_img_dir' => _PS_COL_IMG_DIR_,
            ]);
        }

        if (isset($colors[$id_product])) {
            $color_list_html = $tpl->fetch(_PS_THEME_DIR_ . 'product-list-colors.tpl', Product::getColorsListCacheId($id_product));
        } else {
            $color_list_html = '';
        }

        return $color_list_html;
    }

    public function renderLink($link, $title, $target = '_self')
    {
        $this->context->smarty->assign([
            'link' => $link,
            'title' => $title,
            'target' => $target,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/link.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJqueryPlugin('typewatch');

        $this->context->controller->addCSS($this->_path . 'views/css/front.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/front.js');

        if (Dispatcher::getInstance()->getController() == 'faqdetails') {
            $faq = HiFAQItem::getDetails(Tools::getValue('faq_link_rewrite'));
            if ($faq) {
                $faq['url'] = $this->getFAQURL(Tools::getValue('faq_link_rewrite'));
            }
            $this->context->smarty->assign([
                'meta_title' => $faq['meta_title'] ? $faq['meta_title'] : $faq['title'],
                'meta_description' => $faq['meta_description'],
                'meta_keywords' => $faq['meta_keywords'],
            ]);
        }

        if (Dispatcher::getInstance()->getController() == 'faq') {
            $faqs = [];
            if ($this->structured_data) {
                $faqs = HiFAQItem::getFAQs(true);
            }

            $this->context->smarty->assign([
                'meta_title' => $this->main_page_meta_title[$this->context->language->id],
                'meta_description' => $this->main_page_meta_description[$this->context->language->id],
                'meta_keywords' => $this->main_page_meta_keywords[$this->context->language->id],
                'schema_faqs' => $faqs,
            ]);
        }

        if (Dispatcher::getInstance()->getController() == 'faqcategory') {
            $faqs = [];
            $category = HiFAQCategory::getCategoryByFriendlyURL(Tools::getValue('faqc_link_rewrite'));
            if ($this->structured_data) {
                $id_category = HiFAQCategory::getIdByLinkRewrite(Tools::getValue('faqc_link_rewrite'));
                $faqs = HiFAQItem::getFAQsByIdCategory($id_category);
            }

            $this->context->smarty->assign([
                'meta_title' => isset($category['meta_title']) ? $category['meta_title'] : '',
                'meta_description' => isset($category['meta_description']) ? $category['meta_description'] : '',
                // 'meta_keywords' => $category['meta_keywords'],
                'schema_faqs' => $faqs,
            ]);
        }

        $this->context->smarty->assign([
            'psv' => $this->psv,
            'protocol' => Tools::getProtocol(Tools::usingSecureMode()),
            'controller_name' => Dispatcher::getInstance()->getController(),
            'search_url' => $this->getSearchURL(),
            'id_lang' => $this->context->language->id,
            'secure_key' => $this->secure_key,
            'hiFaqCustomCss' => $this->customCss,
            'hiFaqSearchBgColor' => $this->searchBgColor,
            'hiFaqMainUrl' => $this->getMainURL(),
        ]);

        return $this->display(__FILE__, 'header.tpl');
    }

    public function getCategoriesBlockContent($block)
    {
        $categories = HiFAQCategory::getCategories(true, (int) $block['count']);
        if (!$categories) {
            return;
        }
        foreach ($categories as $key => $category) {
            $categories[$key]['url'] = $this->getCategoryURL($category['friendly_url']);
            $categories[$key]['faqs_count'] = HiFAQPostCategory::getCategoryFAQsCount($category['id']);
        }

        $this->context->smarty->assign([
            'faqCategories' => $categories,
            'title' => $block['title'],
            'title_active' => $block['title_active'],
            'psv' => $this->psv,
        ]);

        return $this->display(__FILE__, 'categories-block.tpl');
    }

    public function getSearchBlockContent($block)
    {
        $query = '';
        if ($this->isFAQController()) {
            $query = Tools::getValue('query');
            if (!$query) {
                $query = Tools::getValue('s');
            }
        }

        $this->context->smarty->assign([
            'title' => $block['title'],
            'title_active' => $block['title_active'],
            'psv' => $this->psv,
            'search_url' => $this->getSearchURL(),
            'query' => $query,
            'ps_rewrite_settings' => Configuration::get('PS_REWRITING_SETTINGS'),
            'icons' => $this->icons,
        ]);

        return $this->display(__FILE__, 'search-block.tpl');
    }

    public function getBlockFAQs($block)
    {
        if (!$block || !is_array($block)) {
            return [];
        }

        $faqs = [];
        if ($block['type'] == 'latest') {
            $faqs = HiFAQItem::getFAQs(true, $block['count'], 0, 'DESC');
        } elseif ($block['type'] == 'old') {
            $faqs = HiFAQItem::getFAQs(true, $block['count'], 0, 'ASC');
        } elseif ($block['type'] == 'categoryFaqs') {
            $faqs = HiFAQItem::getFAQsByIdCategory($this->getBlockSetting($block['id_block'], 'HI_FAQ_BLOCK_CATEGORY'), true, $block['count'], 0, 'DESC');
        } elseif ($block['type'] == 'custom') {
            $custom_faqs = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'hifaqblockfaqs` where id_block = ' . (int) $block['id_block']);
            if (is_array($custom_faqs) && $custom_faqs) {
                foreach ($custom_faqs as $faq) {
                    $faqs[] = HiFAQItem::getDetailsByID($faq['id_faq']);
                }
            }
        }

        for ($i = 0, $c = count($faqs); $i < $c; ++$i) {
            $faqs[$i]['url'] = $this->getFAQURL($faqs[$i]['friendly_url']);
        }

        return $faqs;
    }

    public function getBlockContent($block)
    {
        $faqs = $this->getBlockFAQs($block);
        if (!$faqs || !is_array($faqs)) {
            return;
        }

        if ($this->feedbackAccordion && $this->feedbacksCount) {
            foreach ($faqs as $key => $faq) {
                $faqs[$key]['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq']);
                $faqs[$key]['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq'], 0);
            }
        }

        $structured_data = $this->structured_data;

        if ($this->isFAQPage() || $this->block_has_markup) {
            $structured_data = false;
        }

        // The page can have only 1 markup structure
        if ($structured_data) {
            $this->block_has_markup = true;
        }

        $this->context->smarty->assign([
            'title' => $block['title'],
            'title_active' => $block['title_active'],
            'accordion' => $block['accordion'],
            'id_block' => $block['id_block'],
            'faqs' => $faqs,
            'psv' => $this->psv,
            'structured_data' => $structured_data,
            'feedbackAccordion' => $this->feedbackAccordion,
            'modTplDir' => _PS_MODULE_DIR_ . $this->name . '/views/templates',
            'icons' => $this->icons,
        ]);

        return $this->display(__FILE__, 'faqs-block.tpl');
    }

    public function getHookBlocks($hook)
    {
        $blocks = HiFAQBlock::getBlocksByHook($hook);
        if (!$blocks) {
            return false;
        }

        $return = '';
        foreach ($blocks as $block) {
            if ($block['type'] == 'categories') {
                $return .= $this->getCategoriesBlockContent($block);
            } elseif ($block['type'] == 'search') {
                $return .= $this->getSearchBlockContent($block);
            } else {
                $return .= $this->getBlockContent($block);
            }
        }

        return $return;
    }

    public function getBlockForCreativeElements($id_block)
    {
        $block = HiFAQBlock::getBlocksByID($id_block);

        if (!$block) {
            return '';
        }

        $return = '';
        if ($block['type'] == 'categories') {
            $return .= $this->getCategoriesBlockContent($block);
        } elseif ($block['type'] == 'search') {
            $return .= $this->getSearchBlockContent($block);
        } else {
            $return .= $this->getBlockContent($block);
        }

        return $return;
    }

    public function renderFAQForCreativeElements($id_faq)
    {
        $faq = HiFAQItem::getDetailsByID($id_faq);

        if (!$faq) {
            return;
        }

        $faq['url'] = $this->getFAQURL($faq['friendly_url']);

        $this->context->smarty->assign([
            'faq' => $faq,
            'psv' => $this->psv,
            'icons' => $this->icons,
        ]);

        return $this->display(__FILE__, 'single-faq.tpl');
    }

    public function hookDisplayHome($params)
    {
        return $this->getHookBlocks('displayHome');
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->getHookBlocks('displayLeftColumn');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->getHookBlocks('displayRightColumn');
    }

    public function hookDisplayFooter($params)
    {
        return $this->getHookBlocks('displayFooter');
    }

    public function hookDisplayHiFAQ($params)
    {
        if (!isset($params['id']) || !$params['id']) {
            return;
        }

        $id_block = (int) $params['id'];
        $block = HiFAQBlock::getBlocksByID($id_block);

        if ($block && is_array($block) && $block['active']) {
            return $this->getBlockContent($block);
        }
    }

    public function getProductFaqsContent($hook)
    {
        if (Tools::getValue('id_product')) {
            $idProduct = (int) Tools::getValue('id_product');
            $faqs_product = HiFAQItem::getFAQsByProductID($idProduct);

            $categories = Product::getProductCategories($idProduct);
            $category_faqs = HiFAQItem::getFAQsByCategories($categories);

            // FAQs by product features
            $productFeatures = Product::getFeaturesStatic($idProduct);
            $featureFaqs = [];
            if (is_array($productFeatures) && $productFeatures) {
                foreach ($productFeatures as $feature) {
                    $faqs = HiFAQItem::getFAQsByProductFeatures($feature['id_feature'], $feature['id_feature_value']);
                    if ($faqs) {
                        $featureFaqs = array_unique(array_merge($featureFaqs, $faqs), SORT_REGULAR);
                    }
                }
            }

            $faqs = array_unique(array_merge($faqs_product, $category_faqs, $featureFaqs), SORT_REGULAR);

            if (!is_array($faqs) || !$faqs) {
                return;
            }

            foreach ($faqs as $key => $faq) {
                $faqs[$key]['url'] = $this->getFAQURL($faq['friendly_url']);

                if ($this->feedbackAccordion && $this->feedbacksCount) {
                    $faqs[$key]['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq']);
                    $faqs[$key]['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq'], 0);
                }
            }

            $this->context->smarty->assign([
                'psv' => $this->psv,
                'hook' => $hook,
                'faqs' => $faqs,
                'structured_data' => $this->structured_data,
                'feedbackAccordion' => $this->feedbackAccordion,
                'modTplDir' => _PS_MODULE_DIR_ . $this->name . '/views/templates',
                'icons' => $this->icons,
            ]);

            return $this->display(__FILE__, 'product-faqs.tpl');
        } else {
            return false;
        }
    }

    // PrestaShop 1.6
    public function hookDisplayProductTab($params)
    {
        if ($this->product_page_hook == 'displayProductTab') {
            $faqs = HiFAQItem::getFAQsByProductID(Tools::getValue('id_product'));
            if (!is_array($faqs) && !$faqs) {
                return;
            }

            return $this->display(__FILE__, 'producttab.tpl');
        }
    }

    // PrestraShop 1.6
    public function hookDisplayProductTabContent($params)
    {
        if ($this->product_page_hook == 'displayProductTab') {
            return $this->getProductFaqsContent('displayProductTab');
        }
    }

    public function hookDisplayRightColumnProduct($params)
    {
        if ($this->product_page_hook == 'displayRightColumnProduct') {
            return $this->getProductFaqsContent('displayRightColumnProduct');
        }
    }

    public function hookDisplayLeftColumnProduct($params)
    {
        if ($this->product_page_hook == 'displayLeftColumnProduct') {
            return $this->getProductFaqsContent('displayLeftColumnProduct');
        }
    }

    // PrestaShop 1.7
    public function hookDisplayProductAdditionalInfo($params)
    {
        if ($this->product_page_hook == 'displayProductAdditionalInfo') {
            return $this->getProductFaqsContent('displayProductAdditionalInfo');
        }
    }

    // PrestaShop 1.7
    public function hookDisplayProductExtraContent($params)
    {
        if ($this->product_page_hook == 'displayProductExtraContent') {
            $content = [];
            $faqs_product = HiFAQItem::getFAQsByProductID(Tools::getValue('id_product'));
            $categories = Product::getProductCategories(Tools::getValue('id_product'));
            $category_faqs = HiFAQItem::getFAQsByCategories($categories);

            $faqs = array_unique(array_merge($faqs_product, $category_faqs), SORT_REGULAR);

            if (!is_array($faqs) || !$faqs) {
                return;
            }

            $content[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                ->setTitle($this->l('FAQs'))
                ->setContent($this->getProductFaqsContent('displayProductExtraContent'));

            return $content;
        } else {
            return [];
        }
    }

    public function hookDisplayFooterProduct($params)
    {
        if ($this->product_page_hook == 'displayFooterProduct') {
            return $this->getProductFaqsContent('displayFooterProduct');
        }
    }

    public function hookDisplayHiFAQProduct($params)
    {
        if (Dispatcher::getInstance()->getController() == 'product') {
            return $this->getProductFaqsContent('custom');
        }
    }

    public function hookModuleRoutes($params)
    {
        $return = [];
        $main_page = [
            'controller' => 'faq',
            'rule' => $this->faq_url . '{/filter/:type}',
            'params' => [
                'fc' => 'module',
                'module' => 'hifaq',
                'controller' => 'faq',
            ],
            'keywords' => [
                'type' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'type'],
            ],
        ];
        $category = [
            'controller' => 'faqcategory',
            'rule' => $this->faq_url . '{/' . $this->category_url . '/:faqc_link_rewrite}',
            'params' => [
                'fc' => 'module',
                'module' => 'hifaq',
                'controller' => 'faqcategory',
            ],
            'keywords' => [
                'faqc_link_rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'faqc_link_rewrite'],
            ],
        ];
        $search = [
            'controller' => 'faqsearch',
            'rule' => $this->faq_url . '/' . $this->search_url . '/{:query}',
            'params' => [
                'fc' => 'module',
                'module' => 'hifaq',
                'controller' => 'faqsearch',
            ],
            'keywords' => [
                'query' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'query'],
            ],
        ];
        $details = [
            'controller' => 'faqdetails',
            'rule' => $this->faq_url . '{' . ($this->details_url ? '/' . $this->details_url : '') . '/:faq_link_rewrite}',
            'params' => [
                'fc' => 'module',
                'module' => 'hifaq',
                'controller' => 'faqdetails',
            ],
            'keywords' => [
                'faq_link_rewrite' => ['regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'faq_link_rewrite'],
            ],
        ];

        $return['module-' . $this->name . '-faq'] = $main_page;
        $return['module-' . $this->name . '-faqcategory'] = $category;
        $return['module-' . $this->name . '-faqsearch'] = $search;
        $return['module-' . $this->name . '-faqdetails'] = $details;

        return $return;
    }

    public function isFAQPage()
    {
        $faq_pages = ['faq', 'faqcategory', 'faqdetails', 'faqsearch'];

        if (in_array(Dispatcher::getInstance()->getController(), $faq_pages)) {
            return true;
        }

        return false;
    }

    public function hookDisplayNavFullWidth($params)
    {
        if ($this->isFAQPage() && $this->search) {
            $this->context->smarty->assign([
                'searchPageUrl' => $this->getSearchURL(),
                'icons' => $this->icons,
            ]);

            return $this->display(__FILE__, 'search.tpl');
        }
    }

    public function hookDisplayFAQSearch($params)
    {
        if ($this->isFAQPage() && $this->search) {
            $this->context->smarty->assign([
                'searchPageUrl' => $this->getSearchURL(),
                'icons' => $this->icons,
            ]);

            return $this->display(__FILE__, 'search.tpl');
        }
    }

    public function hookActionCreativeElementsInit()
    {
        CE\add_action('elementor/widgets/widgets_registered', [$this, 'registerBlocksWidget']);
        CE\add_action('elementor/widgets/widgets_registered', [$this, 'registerFAQsWidget']);
    }

    public function registerBlocksWidget()
    {
        include _PS_MODULE_DIR_ . $this->name . '/classes/ce/widgetBlocks.php';

        CE\Plugin::instance()->widgets_manager->registerWidgetType(new CE\WidgetHiFAQBlocks());
    }

    public function registerFAQsWidget()
    {
        include _PS_MODULE_DIR_ . $this->name . '/classes/ce/widgetFAQs.php';

        CE\Plugin::instance()->widgets_manager->registerWidgetType(new CE\WidgetHiFAQs());
    }

    public function saveBlockSetting($idBlock, $settingName, $settingValue)
    {
        return (bool) Configuration::updateValue($settingName . '_' . (int) $idBlock, $settingValue);
    }

    public function getBlockSetting($idBlock, $settingName)
    {
        return Configuration::get($settingName . '_' . (int) $idBlock);
    }

    public function getBlockTypeName($type)
    {
        switch ($type) {
            case 'categories':
                return 'Categories';
            case 'custom':
                return 'Custom FAQs';
            case 'latest':
                return 'Latest FAQs';
            case 'categoryFaqs':
                return 'Category FAQs';
            case 'search':
                return 'Search';
            case 'old':
                return 'Old Faqs';

            default:
                return $type;
        }
    }

    public function hookDisplayHiFAQCategory($params)
    {
        if (isset($params['idCategory']) && $params['idCategory']) {
            $idCategory = (int) $params['idCategory'];
        } else {
            $idCategory = (int) Tools::getValue('id_category');
        }

        if (!$idCategory) {
            return;
        }

        $faqs = HiFAQItem::getFAQsByCategories([$idCategory]);

        if (!$faqs || !is_array($faqs)) {
            return;
        }

        for ($i = 0, $c = count($faqs); $i < $c; ++$i) {
            $faqs[$i]['url'] = $this->getFAQURL($faqs[$i]['friendly_url']);
        }

        if ($this->feedbackAccordion && $this->feedbacksCount) {
            foreach ($faqs as $key => $faq) {
                $faqs[$key]['goodFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq']);
                $faqs[$key]['badFeedbacksCount'] = HiFAQFeedback::getFeedbacksCountByIdFaq($faq['id_faq'], 0);
            }
        }

        $structured_data = $this->structured_data;
        if ($this->isFAQPage() || $this->block_has_markup) {
            $structured_data = false;
        }

        // The page can have only 1 markup structure
        if ($structured_data) {
            $this->block_has_markup = true;
        }

        $this->context->smarty->assign([
            'psv' => $this->psv,
            'faqs' => $faqs,
            'structured_data' => $this->structured_data,
            'feedbackAccordion' => $this->feedbackAccordion,
            'modTplDir' => _PS_MODULE_DIR_ . $this->name . '/views/templates',
            'icons' => $this->icons,
        ]);

        return $this->display(__FILE__, 'category-faqs.tpl');
    }
}
