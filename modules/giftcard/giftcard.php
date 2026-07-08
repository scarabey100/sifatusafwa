<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'giftcard/models/GiftCardProduct.php';
require_once _PS_MODULE_DIR_ . 'giftcard/models/GiftCardOrder.php';
require_once _PS_MODULE_DIR_ . 'giftcard/models/GiftCardOrderUsage.php';
require_once _PS_MODULE_DIR_ . 'giftcard/models/GiftCardTag.php';
require_once _PS_MODULE_DIR_ . 'giftcard/models/GiftCardTemplate.php';
require_once _PS_MODULE_DIR_ . 'giftcard/models/PDFGiftCard.php';
require_once _PS_MODULE_DIR_ . 'giftcard/tools/GiftCardTools.php';
require_once _PS_MODULE_DIR_ . 'giftcard/tools/MailGiftCard.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Giftcard extends Module
{
    public function __construct()
    {
        $this->name = 'giftcard';
        $this->version = '2.1.97';
        $this->module_key = '5cab7347ab5e4b12806ca91019ec40ba';
        $this->accesslog = false;
        parent::__construct();
        $this->bootstrap = true;
        $this->page = basename(__FILE__, '.php');
        $this->tab = 'pricing_promotion';
        $this->author = 'Timactive';
        $this->displayName = $this->l('Gift card');
        $this->description = $this->l('Add the functionality needed to the creation, management and use of gift card.');
        $this->template_front_directory = dirname(__FILE__) . '/views/templates/front/';
        $this->template_admin_directory = dirname(__FILE__) . '/views/templates/admin/';
        $this->confirmUninstall =
            $this->l('Are you sure uninstall module gift card , all information will be deleted?');
        $this->log_directory = dirname(__FILE__) . '/logs/';
        $this->img_directory = _MODULE_DIR_ . 'giftcard/views/img/';
        $this->log_fic_name = '';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->imagick = false;
        if (extension_loaded('imagick')) {
            $this->imagick = true;
        }
        if ((Configuration::get('GIFTCARD_VERSION') != $this->version) && Configuration::get('GIFTCARD_VERSION')) {
            $this->runUpgrades(true);
        }
    }

    public function install($delete_params = true)
    {
        if (!parent::install() || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !$this->registerHook('displayHeader') || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('displayFooter') || !$this->registerHook('displayLeftColumn')) {
            return false;
        }
        if ($delete_params) {
            $sql = '';
            include dirname(__FILE__) . '/sql/sql-install.php';
            Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');
            Configuration::updateGlobalValue('PS_VIRTUAL_PROD_FEATURE_ACTIVE', '1');
            foreach ($sql as $s) {
                if (!Db::getInstance()->execute($s)) {
                    return false;
                }
            }
            $categorygift = new Category();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                if ($language['iso_code'] == 'fr') {
                    $categorygift->name[(int) $language['id_lang']] = 'Module Carte Cadeau ne pas supprimer';
                } else {
                    $categorygift->name[(int) $language['id_lang']] = 'Gift Card Addon no delete';
                }
            }
            foreach ($languages as $language) {
                if ($language['iso_code'] == 'fr') {
                    $categorygift->link_rewrite[(int) $language['id_lang']] = 'carte-cadeau-' .
                        (int) $language['id_lang'];
                } else {
                    $categorygift->link_rewrite[(int) $language['id_lang']] = 'gift-card-' .
                        (int) $language['id_lang'];
                }
            }
            $categorygift->active = 0;
            $catroot = Category::getRootCategory();
            Configuration::updateValue('GIFTCARD_TOKEN', $this->generateCode('', 20, false));
            Configuration::updateValue('GIFTCARD_PREFIX', 'GC_');
            Configuration::updateValue('GIFTCARD_CODE_LENGTH', 12);
            /* from */
            $from_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $from_l[$language['id_lang']] = 'De la part de';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $from_l[$language['id_lang']] = 'Desde';
                } else {
                    $from_l[$language['id_lang']] = 'From';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_FROM', $from_l);
            Configuration::updateValue('GIFTCARD_PDF_IMG_WIDTH', 300);
            Configuration::updateValue('GIFTCARD_PDF_IMG_HEIGHT', 194);
            Configuration::updateValue('GIFTCARD_MAIL_IMG_WIDTH', 300);
            Configuration::updateValue('GIFTCARD_MAIL_IMG_HEIGHT', 194);
            Configuration::updateValue('GIFTCARD_FRONT_IMG_WIDTH', 528);
            Configuration::updateValue('GIFTCARD_FRONT_IMG_HEIGHT', 342);
            Configuration::updateValue('GIFTCARD_FRONT_LIMG_WIDTH', 800);
            Configuration::updateValue('GIFTCARD_FRONT_LIMG_HEIGHT', 518);
            Configuration::updateValue('GIFTCARD_THEME_CGC', 'classicsimple');
            Configuration::updateValue('GIFTCARD_COUPON_TO_PAYMENT', 0);
            $frontcontent_install_dir = _PS_ROOT_DIR_ . '/modules/giftcard/datadefault/frontcontent/';
            $frontcontents = [];
            foreach ($languages as $language) {
                $content = '';
                if (@file_exists($frontcontent_install_dir . $language['iso_code'] . '/content.html')) {
                    $content = @Tools::file_get_contents($frontcontent_install_dir .
                        $language['iso_code'] . '/content.html');
                } elseif (@file_exists($frontcontent_install_dir . 'en/content.html')) {
                    $content = @Tools::file_get_contents($frontcontent_install_dir . 'en/content.html');
                }
                $frontcontents[$language['id_lang']] = $content;
            }
            foreach ($frontcontents as $lang => $frontcontent) {
                Configuration::updateValue('GIFTCARD_FRONT_CONTENT', [
                    $lang => $frontcontent,
                ], true);
            }
            $to_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $to_l[$language['id_lang']] = 'Email destinataire';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $to_l[$language['id_lang']] = 'Email destinatario';
                } else {
                    $to_l[$language['id_lang']] = 'Email recipient';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_TO', $to_l);
            $lastname_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $lastname_l[$language['id_lang']] = 'Nom';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $lastname_l[$language['id_lang']] = 'Appelido';
                } else {
                    $lastname_l[$language['id_lang']] = 'Lastname';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_LASTNAME', $lastname_l);
            $deliverydatel = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $deliverydatel[$language['id_lang']] = 'Date d\'envoi';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $deliverydatel[$language['id_lang']] = 'Fecha de envío';
                } else {
                    $deliverydatel[$language['id_lang']] = 'Date send';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_DATESEND', $deliverydatel);
            $message_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $message_l[$language['id_lang']] = 'Message';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $message_l[$language['id_lang']] = 'Mensaje';
                } else {
                    $message_l[$language['id_lang']] = 'Message';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_MESSAGE', $message_l);
            $template_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $template_l[$language['id_lang']] = 'Modèle';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $template_l[$language['id_lang']] = 'Modelo';
                } else {
                    $template_l[$language['id_lang']] = 'Template';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_TEMPLATE', $template_l);

            $picture_l = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $picture_l[$language['id_lang']] = 'Image';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $picture_l[$language['id_lang']] = 'Imagen';
                } else {
                    $picture_l[$language['id_lang']] = 'Picture';
                }
            }
            Configuration::updateValue('GIFTCARD_CF_IMAGE', $picture_l);

            $objs = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $objs[$language['id_lang']] = 'Carte cadeau offerte par %s';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $objs[$language['id_lang']] = 'Tarjeta regalo ofrecido por %s';
                } else {
                    $objs[$language['id_lang']] = 'Gift card offer from %s';
                }
            }

            Configuration::updateValue('GIFTCARD_MAIL_OBJ_REC', $objs);
            $objs = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $objs[$language['id_lang']] = 'Votre carte cadeau';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $objs[$language['id_lang']] = 'Tarjeta regalo';
                } else {
                    $objs[$language['id_lang']] = 'Your gift card';
                }
            }
            Configuration::updateValue('GIFTCARD_MAIL_OBJ_CUST', $objs);
            $pdf_install_dir = _PS_ROOT_DIR_ . '/modules/giftcard/datadefault/pdf/';
            $pdfcontents = [];
            foreach ($languages as $language) {
                $content = '';
                if (@file_exists($pdf_install_dir . $language['iso_code'] . '/pdfcontent.html')) {
                    $content = @Tools::file_get_contents($pdf_install_dir . $language['iso_code'] . '/pdfcontent.html');
                } elseif (@file_exists($pdf_install_dir . 'en/pdfcontent.html')) {
                    $content = @Tools::file_get_contents($pdf_install_dir . 'en/pdfcontent.html');
                }
                $pdfcontents[$language['id_lang']] = $content;
            }
            foreach ($pdfcontents as $lang => $pdfcontent) {
                Configuration::updateValue('GIFTCARD_PDF_CONTENT', [
                    $lang => $pdfcontent,
                ], true);
            }
            $pdf_prefixs = [];
            foreach ($languages as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $pdf_prefixs[$language['id_lang']] = 'CARTECADEAU-';
                } elseif (Tools::strtolower($language['iso_code']) == 'es') {
                    $pdf_prefixs[$language['id_lang']] = 'TARJETAREGALO-';
                } else {
                    $pdf_prefixs[$language['id_lang']] = 'GIFTCARD-';
                }
            }
            Configuration::updateValue('GIFTCARD_PDF_PREFIX', $pdf_prefixs);
            Configuration::updateValue('GIFTCARD_VERSION', $this->version);
            $categorygift->id_parent = $catroot->id;
            if (!$categorygift->add()) {
                return false;
            }
            if (!Configuration::updateGlobalValue('GIFTCARD_CATEGORY_ID', $categorygift->id)) {
                return false;
            }
            if (!$this->installModuleTab()) {
                return false;
            }
        }

        return true;
    }

    public function runUpgrades($install = false)
    {
        if (Configuration::get('GIFTCARD_VERSION') != $this->version) {
            foreach ([
                         '1.0.5',
                         '1.0.6',
                         '1.0.7',
                         '1.0.11',
                         '1.0.12',
                         '2.1.0',
                     ] as $version) {
                $file = dirname(__FILE__) . '/upgrade/install-' . $version . '.php';
                if (version_compare($version, Configuration::get('GIFTCARD_VERSION')) > 0 && file_exists($file)) {
                    include_once $file;
                    call_user_func('upgrade_module_' . str_replace('.', '_', $version), $this, $install);
                }
            }
        }
        Configuration::updateValue('GIFTCARD_VERSION', $this->version);
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function uninstall($delete_params = true)
    {
        if (!parent::uninstall()) {
            return false;
        }
        if ($delete_params) {
            if (!Configuration::deleteByName('GIFTCARD_CATEGORY_ID')
                || !Configuration::deleteByName('GIFTCARD_TOKEN')
                || !Configuration::deleteByName('GIFTCARD_PREFIX')
                || !Configuration::deleteByName('GIFTCARD_CF_FROM')
                || !Configuration::deleteByName('GIFTCARD_CF_TO')
                || !Configuration::deleteByName('GIFTCARD_CF_DATESEND')
                || !Configuration::deleteByName('GIFTCARD_CF_MESSAGE')
                || !Configuration::deleteByName('GIFTCARD_CF_TEMPLATE')
                || !Configuration::deleteByName('GIFTCARD_CF_IMAGE')
                || !Configuration::deleteByName('GIFTCARD_MAIL_OBJ_REC')
                || !Configuration::deleteByName('GIFTCARD_MAIL_OBJ_CUST')
                || !Configuration::deleteByName('GIFTCARD_PDF_CONTENT')
                || !Configuration::deleteByName('GIFTCARD_PDF_IMG_WIDTH')
                || !Configuration::deleteByName('GIFTCARD_PDF_IMG_HEIGHT')
                || !Configuration::deleteByName('GIFTCARD_MAIL_IMG_WIDTH')
                || !Configuration::deleteByName('GIFTCARD_MAIL_IMG_HEIGHT')
                || !Configuration::deleteByName('GIFTCARD_FRONT_IMG_WIDTH')
                || !Configuration::deleteByName('GIFTCARD_FRONT_IMG_HEIGHT')
                || !Configuration::deleteByName('GIFTCARD_FRONT_LIMG_WIDTH')
                || !Configuration::deleteByName('GIFTCARD_FRONT_LIMG_HEIGHT')
                || !Configuration::deleteByName('GIFTCARD_PDF_PREFIX')
                || !Configuration::deleteByName('GIFTCARD_CODE_LENGTH')
                || !Configuration::deleteByName('GIFTCARD_THEME_CGC')
                || !Configuration::deleteByName('GIFTCARD_COUPON_TO_PAYMENT')
                || !Configuration::deleteByName('GIFTCARD_VOUCHER_SHOP_RESTRICTION')
                || !$this->uninstallModuleTab()) {
                return false;
            }
            $sql = '';
            include dirname(__FILE__) . '/sql/sql-uninstall.php';
            $giftcards = GiftCardProduct::getGiftCards(null, false, null, null, false);
            $templates = GiftCardTemplate::getTemplates();
            /* delete properly giftcards */
            foreach ($giftcards as $giftcard) {
                $product = new Product((int) $giftcard['id_product']);
                if ($product->id && !$product->delete()) {
                    return false;
                }
            }
            /* delete properly template */
            foreach ($templates as $template) {
                $template = new GiftCardTemplate((int) $template['id_gift_card_template']);
                if ($template->id && !$template->delete()) {
                    return false;
                }
            }
            foreach ($sql as $s) {
                if (!Db::getInstance()->execute($s)) {
                    return false;
                }
            }
            if (($id_category = (int) Configuration::get('GIFTCARD_CATEGORY_ID')) && $id_category > 0) {
                $category = new Category($id_category);
                if (!$category->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    /* Installation de l'onglet */
    private function installModuleTab()
    {
        $admin_tab_catalog_id = Tab::getIdFromClassName('AdminCatalog');
        $admin_tab_order_id = Tab::getIdFromClassName('AdminParentOrders');
        /* Gift Card Template */
        $tabgiftcardtemplate = new Tab();
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardtemplate->name[(int) $language['id_lang']] = 'Cartes Cadeaux Modèles';
            } else {
                $tabgiftcardtemplate->name[(int) $language['id_lang']] = 'Templates Gift Cards';
            }
        }
        $tabgiftcardtemplate->class_name = 'AdminGiftCardTemplate';
        $tabgiftcardtemplate->module = 'giftcard';
        $tabgiftcardtemplate->id_parent = $admin_tab_catalog_id;
        if (!$tabgiftcardtemplate->save()) {
            return false;
        }
        $tabgiftcardproduct = new Tab();
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardproduct->name[(int) $language['id_lang']] = 'Cartes Cadeaux';
            } else {
                $tabgiftcardproduct->name[(int) $language['id_lang']] = 'Gift Cards';
            }
        }
        $tabgiftcardproduct->class_name = 'AdminGiftCard';
        $tabgiftcardproduct->module = 'giftcard';
        $tabgiftcardproduct->id_parent = $admin_tab_catalog_id;
        if (!$tabgiftcardproduct->save()) {
            return false;
        }
        $tabgiftcardorder = new Tab();
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardorder->name[(int) $language['id_lang']] = 'Cartes Cadeaux';
            } else {
                $tabgiftcardorder->name[(int) $language['id_lang']] = 'Gift Cards';
            }
        }
        $tabgiftcardorder->class_name = 'AdminGiftCardOrder';
        $tabgiftcardorder->module = 'giftcard';
        $tabgiftcardorder->id_parent = $admin_tab_order_id;
        if (!$tabgiftcardorder->save()) {
            return false;
        }

        return true;
    }

    private function uninstallModuleTab()
    {
        $id_tab_product = Tab::getIdFromClassName('AdminGiftCard');
        $id_tab_order = Tab::getIdFromClassName('AdminGiftCardOrder');
        $id_tab_template = Tab::getIdFromClassName('AdminGiftCardTemplate');
        if ($id_tab_template != 0) {
            $tab_template = new Tab($id_tab_template);
            $tab_template->delete();
        }
        if ($id_tab_product != 0) {
            $tab_product = new Tab($id_tab_product);
            $tab_product->delete();
        }
        if ($id_tab_order != 0) {
            $tab_order = new Tab($id_tab_order);
            $tab_order->delete();

            return true;
        }

        return false;
    }

    public function hookDisplayLeftColumn($params)
    {
        $this->context->smarty->assign([
            'link_choicegiftcard' => $this->context->link->getModuleLink('giftcard', 'choicegiftcard'),
        ]);

        return $this->display(__FILE__, 'blockgiftcard.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->context->controller->registerStylesheet(
                'modules-giftcard-block',
                'modules/' . $this->name . '/views/css/giftcard.css',
                ['media' => 'all', 'priority' => 150]
            );
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/giftcard.css', 'all');
        }
    }

    public function hookDisplayFooter()
    {
        $show_img = false;
        $img_src = '';

        if ((int) Configuration::get('GIFTCARD_TRIGGERWEB') == 1
            && (int) date('Ymd') > (int) Configuration::get('GIFTCARD_BATCHLASTDATE')) {
            $show_img = true;
            $img_src = $this->_path . 'cron.php?token=' . Configuration::get('GIFTCARD_TOKEN') .
                '&time=' . time();
        }

        $this->context->smarty->assign([
            'show_img' => $show_img,
            'img_src' => $img_src,
        ]);

        return $this->display(__FILE__, 'hookDisplayFooter.tpl');
    }

    public function hookDisplayAdminOrder($params)
    {
        $errors = [];
        $infos = [];
        $warnings = [];
        $id_order = (int) $params['id_order'];
        $giftCardOrders = GiftCardOrder::exists($id_order, null, true);
        if (Tools::isSubmit('buildGiftCardOrder')) {
            // get cart by order
            $order = new Order((int) $id_order);
            $cart = new Cart((int) $order->id_cart);
            if (!$giftCardOrders && Validate::isLoadedObject($cart)) {
                $customer = new Customer((int) $cart->id_customer);
                $currentState = new OrderState((int) $order->current_state);
                // check if product in order is giftcardproduct
                $orderProductsDetails = $order->getProductsDetail();
                $hasGiftCardProduct = false;
                foreach ($orderProductsDetails as $orderProductDetails) {
                    if ($this->isGiftCard($orderProductDetails['product_id'])) {
                        $hasGiftCardProduct = true;
                        break;
                    }
                }
                if ($hasGiftCardProduct) {
                    $this->hookActionValidateOrder(array_merge($params, [
                        'order' => $order,
                        'cart' => $cart,
                        'customer' => $customer,
                        'orderStatus' => $currentState,
                    ]));
                }
            }
        }
        if (Tools::isSubmit('cancelGiftCardOrder') && Validate::isUnsignedId(Tools::getValue('id_gift_card_order'))) {
            $giftcard_order = new GiftCardOrder((int) Tools::getValue('id_gift_card_order'));
            if (isset($giftcard_order->id_cart_rule) && (int) $giftcard_order->id_cart_rule > 0) {
                $cart_rule = new CartRule((int) $giftcard_order->id_cart_rule);
                $cart_rule->active = 0;
                if (Validate::isLoadedObject($cart_rule) && !$cart_rule->update()) {
                    $errors[] = $this->l('Error update cartrule');
                } else {
                    $giftcard_order->status = 'CANCEL';
                    $firstname = $this->context->employee->firstname;
                    $lastname = $this->context->employee->lastname;
                    $message = $this->l('Cancel Gift card by employee ') . Tools::ucfirst($firstname) . ' ' .
                        Tools::ucfirst($lastname);
                    $giftcard_order->addInfoLine($message);
                    if (!$giftcard_order->update()) {
                        $errors[] = $this->l('Error update gift card order');
                    } else {
                        $infos[] = $this->l('Gift card is canceled with successful');
                    }
                }
            }
        }
        if (Tools::isSubmit('activeGiftCardOrder') && Validate::isUnsignedId(Tools::getValue('id_gift_card_order'))) {
            $giftcard_order = new GiftCardOrder((int) Tools::getValue('id_gift_card_order'));
            if (isset($giftcard_order->id_cart_rule) && (int) $giftcard_order->id_cart_rule > 0) {
                $cart_rule = new CartRule((int) $giftcard_order->id_cart_rule);
                $cart_rule->active = 1;
                if (!$cart_rule->update()) {
                    $errors[] = $this->l('Error update cartrule');
                } else {
                    $giftcard_order->status = 'CREATED';
                    $firstname = $this->context->employee->firstname;
                    $lastname = $this->context->employee->lastname;
                    $message = $this->l('Active Gift card by employee ') . Tools::ucfirst($firstname) . ' ' .
                        Tools::ucfirst($lastname);
                    $giftcard_order->addInfoLine($message);
                    if (!$giftcard_order->update()) {
                        $errors[] = $this->l('Error update gift card order');
                    } else {
                        $infos[] = $this->l('Gift card is actived with successful');
                    }
                }
            }
        }
        $this->_html = '';
        $current_index = AdminController::$currentIndex;
        $purchaseOrders = GiftCardOrder::getPurchaseOrders(
            (int) $params['id_order'],
            (int) $this->context->language->id
        );
        $giftCardOrders = GiftCardOrder::exists($id_order, null, true);
        if (!$giftCardOrders) {
            $order = new Order((int) $id_order);
            $cart = new Cart((int) $order->id_cart);
            $currentState = new OrderState((int) $order->current_state);
            if (!$giftCardOrders && Validate::isLoadedObject($cart) && $currentState->logable) {
                // check if product in order is giftcardproduct
                $orderProductsDetails = $order->getProductsDetail();
                $hasGiftCardProduct = false;
                foreach ($orderProductsDetails as $orderProductDetails) {
                    if ($this->isGiftCard($orderProductDetails['product_id'])) {
                        $hasGiftCardProduct = true;
                        break;
                    }
                }
                if ($hasGiftCardProduct) {
                    $this->_html .= $this->display(__FILE__, 'block_build_gc.tpl');
                }
            }
        }
        if (($giftCardOrders && isset($giftCardOrders)) || ($purchaseOrders && isset($purchaseOrders))) {
            $order = new Order((int) $id_order);
            $order_state = new OrderState($order->current_state);
            $this->context->smarty->assign([
                'ps_version' => _PS_VERSION_,
                'order' => $order,
                'currentState' => $order_state,
                'infos' => $infos,
                'errors' => $errors,
                'warnings' => $warnings,
                'current_index' => $current_index,
                'purchaseorders' => $purchaseOrders,
                'giftcardsorder' => $giftCardOrders,
                'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__,
                'params' => $params,
            ]);
            $this->_html .= $this->display(__FILE__, 'admin_order.tpl');
        }

        return $this->_html;
    }

    public function isGiftCard($id_product)
    {
        return GiftCardProduct::isGiftCard($id_product);
    }

    public static function getOrderedCustomizationsInProduct($id_cart, $id_product)
    {
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT `id_customization`, `quantity`,`id_product`
            FROM `' . _DB_PREFIX_ . 'customization`
            WHERE `id_product`=' . (int) $id_product . ' AND `id_cart` = ' . (int) $id_cart
        )) {
            return false;
        }
        $customizations = [];
        foreach ($result as $row) {
            $customizations[(int) $row['id_customization']] = $row;
        }

        return $customizations;
    }

    public static function getCustDataValue($id_customization, $index)
    {
        $sql = 'SELECT `value` FROM `' . _DB_PREFIX_ . 'customized_data`
				 WHERE `id_customization` = ' . (int) $id_customization . ' AND `index`=' . (int) $index;
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            return false;
        }

        return (string) $result['value'];
    }

    public function hookActionValidateOrder($params)
    {
        // $currency = $params['currency'];
        $move_to_payment = (int) Configuration::get('GIFTCARD_COUPON_TO_PAYMENT');
        $order = $params['order'];
        $order_status = $params['orderStatus'];
        if (!GiftCardOrder::exists($order->id) && isset($params['cart'])
            && (int) $params['cart']->id && !GiftCardOrder::cartexists((int) $params['cart']->id)) {
            $customer = $params['customer'];
            $products = $params['cart']->getProducts();
            $products_group = [];
            foreach ($products as $product) {
                $products_group[(int) $product['id_product']] = $product;
            }
            // $currency_default = (int)Configuration::get('PS_CURRENCY_DEFAULT');
            foreach ($products_group as $product) {
                $id_product = (int) $product['id_product'];
                // $productqtyincart = (int)$product['cart_quantity'];
                $productqtydo = 0;
                $card = new GiftCardProduct($id_product);
                if (GiftCardProduct::isGiftCard((int) $product['id_product'])) {
                    /* first build customizations */
                    $customizations = self::getOrderedCustomizationsInProduct($params['cart']->id, $id_product);
                    foreach ($customizations as $customization) {
                        $customquantity = (int) $customization['quantity'];
                        $from = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_from
                        );
                        $template_id = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_template
                        );
                        $mail_to = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_mailto
                        );
                        $lastname = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_lastname
                        );
                        $message = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_message
                        );
                        $delivery_date = self::getCustDataValue(
                            $customization['id_customization'],
                            $card->id_customization_field_deliverydate
                        );
                        $i = 0;
                        for ($i = 1; $i <= $customquantity; ++$i) {
                            $giftcard_order = new GiftCardOrder();
                            $giftcard_order->id_customization = (int) $customization['id_customization'];
                            $giftcard_order->id_gift_card_template = (int) $template_id;
                            if ($card->is_virtual) {
                                $giftcard_order->receptmode = ((isset($mail_to) && !empty($mail_to)
                                    && trim($mail_to) != '') ? 1 : 0);
                            } else {
                                $giftcard_order->receptmode = 2;
                            }
                            $giftcard_order->to_mail = ((isset($mail_to) && trim($mail_to) != '') ? $mail_to : null);
                            $giftcard_order->message = ((isset($message) && trim($message) != '') ? $message : null);
                            $giftcard_order->lastname = ((isset($lastname) && trim($lastname) != '') ?
                                $lastname : null);
                            $giftcard_order->from = ((isset($from) && trim($from) != '') ? $from : null);
                            $giftcard_order->sended = false;
                            $giftcard_order->period_val = $card->period_val;
                            $giftcard_order->delivery_date = ((isset($delivery_date) && trim($delivery_date) != '') ?
                                $delivery_date : null);
                            $giftcard_order->customer_mail = $customer->email;
                            $giftcard_order->id_currency = $order->id_currency;
                            $giftcard_order->id_lang = $order->id_lang;
                            $giftcard_order->id_cart = $params['cart']->id;
                            $giftcard_order->id_order = $order->id;
                            $giftcard_order->id_product = (int) $product['id_product'];
                            $giftcard_order->price = $card->amount;
                            $giftcard_order->status = 'WAIT';
                            $giftcard_order->add();
                            ++$productqtydo;
                        }
                    }
                }
            }
            if ($order_status->logable) {
                $this->buildAndSend($order->id);
            }
        }
        // move to payment
        if ((int) $move_to_payment) {
            GiftCardOrder::moveToPayment($order);
        }
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $new_os = $params['newOrderStatus'];
        $id_order = (int) $params['id_order'];
        if ($new_os->logable) {
            $this->buildAndSend($id_order);
        }
        /*if (!$new_os->logable) {
            $sql = 'SELECT go.* FROM `' . _DB_PREFIX_ . 'giftcardorder` go
                    WHERE go.status != \'CANCEL\' AND go.`id_order` = ' . (int) $id_order;
            $result = Db::getInstance()->ExecuteS($sql);
            $errors = array();
            $infos = array();
            foreach ($result as $row_gc) {
                $giftcard_order = new GiftCardOrder((int) $row_gc['id_gift_card_order']);
                if ($giftcard_order && (int) $giftcard_order->id && $giftcard_order->status !== 'CANCEL' &&
                    isset($giftcard_order->id_cart_rule) && (int) $giftcard_order->id_cart_rule > 0) {
                    $cart_rule = new CartRule((int) $giftcard_order->id_cart_rule);
                    $cart_rule->active = 0;
                    if (!$cart_rule->update()) {
                        $errors[] = $this->l('Error update cartrule');
                    } else {
                        $giftcard_order->status = 'CANCEL';
                        if (isset($this->context->employee)) {
                            $firstname = $this->context->employee->firstname;
                            $lastname = $this->context->employee->lastname;
                            $message = $this->l('Cancel Gift card by employee ') . Tools::ucfirst($firstname) . ' ' .
                            Tools::ucfirst($lastname);
                            $giftcard_order->addInfoLine($message);
                        } else {
                            $message = $this->l('Cancel Gift card ');
                            $giftcard_order->addInfoLine($message);
                        }
                        if (!$giftcard_order->update()) {
                            $errors[] = $this->l('Error update gift card order');
                        } else {
                            $infos[] = $this->l('Gift card is canceled with successful');
                        }
                    }
                }
            }
        }*/
    }

    /* Send mail to recipient of the gift card */
    public function sendPendingMailRecipient()
    {
        $giftcardorders = GiftCardOrder::getGiftCardOrdersToSend();
        if ($giftcardorders) {
            foreach ($giftcardorders as $go) {
                $giftcard_order = new GiftCardOrder((int) $go['id_gift_card_order']);
                $giftcard_order->sendingMail();
                $giftcard_order->update();
            }
        }
    }

    public static function generateCode($prefix = '', $length = 8, $cardrulecheck = false)
    {
        $code = '';
        $possible = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxlength = Tools::strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = Tools::substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($code, $char)) {
                $code .= $char;
                ++$i;
            }
        }

        return $prefix . $code;
    }

    public function buildAndSend($id_order)
    {
        if ($giftcardsorders = GiftCardOrder::exists($id_order, 'WAIT', true)) {
            if ($giftcardsorders) {
                $order = new Order((int) $id_order);
                $customer = new Customer((int) $order->id_customer);
                foreach ($giftcardsorders as $key => $giftcardsorder) {
                    /* for each */
                    $giftcard_order = new GiftCardOrder((int) $giftcardsorder['id_gift_card_order']);
                    $giftcard = new GiftCardProduct((int) $giftcardsorder['id_product']);
                    $cart_rule = new CartRule();
                    /* Information */
                    $cart_rule->highlight = 0;
                    if (!isset($giftcard->id) || !Validate::isLoadedObject($giftcard)
                        || !((int) $giftcard->id > 0)) {
                        $languages = Language::getLanguages(false);
                        foreach ($languages as $language) {
                            if ($language['iso_code'] == 'fr') {
                                $cart_rule->name[(int) $language['id_lang']] = 'Carte cadeau ' .
                                    (string) $giftcard_order->price;
                            } else {
                                $cart_rule->name[(int) $language['id_lang']] = 'Gift card ' .
                                    (string) $giftcard_order->price;
                            }
                        }
                    } else {
                        $cart_rule->name = $giftcard->name;
                    }
                    $cart_rule->partial_use = (int) $giftcard->cr_partial_use;
                    $cart_rule->free_shipping = (int) $giftcard->cr_free_shipping;
                    /* Condition */
                    $cart_rule->date_to = date(
                        'Y-m-d H:i:s',
                        mktime(
                            date('H'),
                            date('i'),
                            date('s'),
                            date('m') + ((int) $giftcard_order->period_val),
                            date('d'),
                            date('Y')
                        )
                    );
                    $cart_rule->date_from = date('Y-m-d H:i:s');
                    $cart_rule->quantity = 1;
                    $cart_rule->quantity_per_user = 1;
                    /* minimum amount */
                    $cart_rule->minimum_amount = 0;
                    /* Compatibilité avec les autre règles paniers */
                    $cart_rule->cart_rule_restriction = 0;
                    /*
                     * **** Action *****
                     */
                    $cart_rule->reduction_tax = 1;
                    $cart_rule->reduction_currency = $giftcard_order->id_currency;
                    $cart_rule->reduction_amount = $giftcard_order->price;
                    $cart_rule->reduction_percent = 0;
                    $cart_rule->reduction_product = 0;
                    $cart_rule->priority = 10;
                    $prefix = Configuration::get('GIFTCARD_PREFIX');
                    $code = $this->generateCode(
                        $prefix,
                        (int) Configuration::get('GIFTCARD_CODE_LENGTH'),
                        true
                    );
                    $cart_rule->code = $code;
                    $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
                    $language_default = new Language($default_lang);
                    if (Tools::strtolower($language_default->iso_code) == 'fr') {
                        $cart_rule->description = 'Module Carte cadeau';
                    } elseif (Tools::strtolower($language_default->iso_code) == 'es') {
                        $cart_rule->description = 'Tarjeta de Regalo';
                    } else {
                        $cart_rule->description = 'Gift Card Addon';
                    }
                    if ($cart_rule->add()) {
                        $voucherShopRestriction = (int) Configuration::get('GIFTCARD_VOUCHER_SHOP_RESTRICTION');
                        if ($voucherShopRestriction) {
                            $cart_rule->shop_restriction = 1;
                            // add cart rule shop restriction
                            Db::getInstance()->insert(
                                'cart_rule_shop',
                                [
                                    'id_cart_rule' => (int) $cart_rule->id,
                                    'id_shop' => (int) $order->id_shop,
                                ]
                            );
                        }
                        $giftcard_order->status = 'CREATED';
                    } else {
                        $giftcard_order->status = 'ERROR';
                    }
                    $giftcard_order->discountcode = $cart_rule->code;
                    $giftcard_order->id_cart_rule = $cart_rule->id;
                    $giftcard_order->sended = 0;
                    $giftcard_order->sendingMail($customer);
                    $giftcard_order->update();
                }
            }
        }
    }

    public function renderMenuTop()
    {
        $html_menu = '';
        $this->context->smarty->assign([
            'ta_gc_tab_select' => 'settings',
            'link' => $this->context->link,
        ]);
        $html_menu = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu-top.tpl');

        return $html_menu;
    }

    public function renderFooterModule()
    {
        $html = '';
        $this->context->smarty->assign([
            'link' => $this->context->link,
        ]);
        $html = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer-module.tpl');

        return $html;
    }

    public function buildFieldLang($field_key = null)
    {
        $languages = Language::getLanguages(false);
        $cffield_l = [];
        foreach ($languages as $language) {
            $cffield_l[$language['id_lang']] = Tools::getValue($field_key . '_' . $language['id_lang']);
        }

        return $cffield_l;
    }

    public function validateCustomField($cffield_l, $field_name, $methodvalidate = 'isName')
    {
        $error = '';
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        if (!isset($cffield_l[$default_lang]) || empty($cffield_l[$default_lang])) {
            $language_default = new Language($default_lang);
            $error .= $this->displayError(sprintf($this->l('Field \'%s\' is required for the default lang ') .
                $language_default->iso_code, $field_name));
        }
        foreach ($cffield_l as $key => $cffield) {
            if (!empty($cffield) && !Validate::$methodvalidate($cffield)) {
                $language = new Language((int) $key);
                if ($language && isset($language->iso_code) && Validate::isLanguageIsoCode($language->iso_code)) {
                    $error .= $this->displayError(sprintf(
                        $this->l('Field %1$s with value \'%2$s\' is not valid format, iso code %3$s'),
                        $field_name,
                        $cffield,
                        $language->iso_code
                    ));
                }
            }
        }

        return $error;
    }

    public function getContent()
    {
        $this->context->controller->addCSS([
            _MODULE_DIR_ . 'giftcard/views/css/admin-ta-common.css',
            _MODULE_DIR_ . 'giftcard/views/css/icons/flaticon.css',
        ]);
        if (version_compare(_PS_VERSION_, '1.6.0', '<') === true) {
            $this->context->controller->addCSS(_MODULE_DIR_ . 'giftcard/views/css/admin-ta-commonps15.css');
        }
        $output = null;
        if (!Tools::isSubmit('previewpdf')) {
            $output .= $this->renderMenuTop();
        }
        if (Tools::isSubmit('loaddefault')) {
            $issvg = ((int) Tools::getValue('withgenerictemplate') ? true : false);
            if (!$this->imagick && $issvg) {
                $output .= $this->displayError(
                    $this->l('To use generic templates, you must install imagick, imagick is a popular free extension php')
                );
            } else {
                GiftCardTools::creatingDefaultTemplates($issvg);
                GiftCardTools::creatingDefaultGiftCards();
            }
        }
        if (Tools::isSubmit('save_workflow')) {
            Configuration::updateValue(
                'GIFTCARD_COUPON_TO_PAYMENT',
                (int) Tools::getValue('GIFTCARD_COUPON_TO_PAYMENT')
            );
        }
        if (Tools::isSubmit('submit' . $this->name)) {
            $prefix = (string) Tools::getValue('prefix');
            $front_content_l = $this->buildFieldLang('front_content');
            $errorreturn = self::validateCustomField($front_content_l, $this->l('Text in front office'), 'isCleanHtml');
            if (empty($errorreturn)) {
                foreach ($front_content_l as $lang => $front_content) {
                    Configuration::updateValue('GIFTCARD_FRONT_CONTENT', [
                        $lang => $front_content,
                    ], true);
                }
            } else {
                $output .= $errorreturn;
            }
            $cf_template_l = $this->buildFieldLang('cf_template');
            $errorreturn = self::validateCustomField($cf_template_l, $this->l('Template label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_TEMPLATE', $cf_template_l);
            } else {
                $output .= $errorreturn;
            }

            $cf_picture_l = $this->buildFieldLang('cf_picture');
            $errorreturn = self::validateCustomField($cf_picture_l, $this->l('Picture label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_IMAGE', $cf_picture_l);
            } else {
                $output .= $errorreturn;
            }

            Configuration::updateValue('GIFTCARD_THEME_CGC', Tools::getValue('theme_cgc', 'classic'));
            $cf_from_l = $this->buildFieldLang('cf_from');
            $errorreturn = self::validateCustomField($cf_from_l, $this->l('From label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_FROM', $cf_from_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_lastname_l = $this->buildFieldLang('cf_lastname');
            $errorreturn = self::validateCustomField($cf_lastname_l, $this->l('Lastname label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_LASTNAME', $cf_lastname_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_to_l = $this->buildFieldLang('cf_to');
            $errorreturn = self::validateCustomField($cf_to_l, $this->l('Mail recipient label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_TO', $cf_to_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_date_send_l = $this->buildFieldLang('cf_date_send');
            $errorreturn = self::validateCustomField($cf_date_send_l, $this->l('Date send label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_DATESEND', $cf_date_send_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_message_l = $this->buildFieldLang('cf_message');
            $errorreturn = self::validateCustomField($cf_message_l, $this->l('Message label'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_CF_MESSAGE', $cf_message_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_mail_obj_rec_l = $this->buildFieldLang('mail_obj_rec');
            $errorreturn = self::validateCustomField(
                $cf_mail_obj_rec_l,
                $this->l('Subject of email recipient'),
                'isMailSubject'
            );
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_MAIL_OBJ_REC', $cf_mail_obj_rec_l);
            } else {
                $output .= $errorreturn;
            }
            $cf_mail_obj_cust_l = $this->buildFieldLang('mail_obj_cust');
            $errorreturn = self::validateCustomField(
                $cf_mail_obj_cust_l,
                $this->l('Subject of email customer'),
                'isMailSubject'
            );
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_MAIL_OBJ_CUST', $cf_mail_obj_cust_l);
            } else {
                $output .= $errorreturn;
            }
            $pdf_content_l = $this->buildFieldLang('pdf_content');
            $errorreturn = self::validateCustomField($pdf_content_l, $this->l('PDF content'), 'isCleanHtml');
            if (empty($errorreturn)) {
                foreach ($pdf_content_l as $lang => $pdf_content) {
                    Configuration::updateValue('GIFTCARD_PDF_CONTENT', [
                        $lang => $pdf_content,
                    ], true);
                }
            } else {
                $output .= $errorreturn;
            }
            $pdf_prefix_l = $this->buildFieldLang('pdf_prefix');
            $errorreturn = self::validateCustomField($pdf_prefix_l, $this->l('PDF prefix'));
            if (empty($errorreturn)) {
                Configuration::updateValue('GIFTCARD_PDF_PREFIX', $pdf_prefix_l);
            } else {
                $output .= $errorreturn;
            }
            $pdf_img_width = (int) Tools::getValue('pdf_img_width', 0);
            if (!$pdf_img_width > 0) {
                $output .= $this->displayError($this->l('The field width must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_PDF_IMG_WIDTH', $pdf_img_width);
            }
            $pdf_img_height = (int) Tools::getValue('pdf_img_height', 0);
            if (!$pdf_img_height > 0) {
                $output .= $this->displayError($this->l('The field height must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_PDF_IMG_HEIGHT', $pdf_img_height);
            }
            $front_img_width = (int) Tools::getValue('front_img_width', 0);
            if (!$front_img_width > 0) {
                $output .= $this->displayError($this->l('The field width must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_FRONT_IMG_WIDTH', $front_img_width);
            }
            $front_img_height = (int) Tools::getValue('front_img_height', 0);
            if (!$front_img_height > 0) {
                $output .= $this->displayError($this->l('The field height must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_FRONT_IMG_HEIGHT', $front_img_height);
            }
            $front_limg_width = (int) Tools::getValue('front_limg_width', 0);
            if (!$front_limg_width > 0) {
                $output .= $this->displayError($this->l('The field width must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_FRONT_LIMG_WIDTH', $front_limg_width);
            }
            $front_limg_height = (int) Tools::getValue('front_limg_height', 0);
            if (!$front_limg_height > 0) {
                $output .= $this->displayError($this->l('The field height must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_FRONT_LIMG_HEIGHT', $front_limg_height);
            }
            $mail_img_width = (int) Tools::getValue('mail_img_width', 0);
            if (!$mail_img_width > 0) {
                $output .= $this->displayError($this->l('The field img width must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_MAIL_IMG_WIDTH', $mail_img_width);
            }
            $mail_img_height = (int) Tools::getValue('mail_img_height', 0);
            if (!$mail_img_height > 0) {
                $output .= $this->displayError($this->l('The field img height must superior to 0'));
            } else {
                Configuration::updateValue('GIFTCARD_MAIL_IMG_HEIGHT', $mail_img_height);
            }
            GiftCardProduct::updateAllCustomFields();
            $triggerweb = (int) Tools::getValue('triggerweb', 0);
            Configuration::updateValue('GIFTCARD_TRIGGERWEB', $triggerweb);
            if (!$prefix || empty($prefix) || !Validate::isGenericName($prefix)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('GIFTCARD_PREFIX', $prefix);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
            $codeLength = (int) Tools::getValue('code_length');
            if ($codeLength <= 9) {
                $output .= $this->displayError($this->l('The field code lenght must superior to 9'));
            } else {
                Configuration::updateValue('GIFTCARD_CODE_LENGTH', $codeLength);
            }
            Configuration::updateGlobalValue(
                'GIFTCARD_VOUCHER_SHOP_RESTRICTION',
                (int) Tools::getValue('GIFTCARD_VOUCHER_SHOP_RESTRICTION')
            );
            if (!$prefix || empty($prefix) || !Validate::isGenericName($prefix)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('GIFTCARD_PREFIX', $prefix);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        if (Tools::isSubmit('previewpdf')) {
            $giftcard_template = GiftCardTemplate::getDefault();
            if (!$giftcard_template) {
                $output .= $this->displayError($this->l('Default gift card template is required to preview PDF'));
            } else {
                $params = [];
                $params['id_shop'] = (int) Configuration::get('PS_SHOP_DEFAULT');
                $params['id_currency'] = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                $params['id_gift_card_order'] = 1;
                $params['discountcode'] = 'GC_JN43F8Q2OMTG7K1CREDP';
                $params['price'] = 100;
                $params['date_to'] = Tools::displayDate(date('Y-m-d', strtotime(date('Y-m-d', time()) . ' + 365 day')));
                $params['id_lang'] = (int) Tools::getValue('pdfpreview_id_lang', Configuration::get('PS_LANG_DEFAULT'));
                $params['message'] = 'Message';
                $params['lastname'] = 'John';
                $params['from'] = 'William';
                $prefix_pdf = Configuration::get('GIFTCARD_PDF_PREFIX', (int) $params['id_lang']);
                if (!$prefix_pdf || empty($prefix_pdf)) {
                    $prefix_pdf = 'GIFTCARD';
                }
                $filename = $prefix_pdf . sprintf('%06d', 0);
                GiftCardTools::processGeneratePdfV2($giftcard_template, $params, true, $filename);
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Get default Language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $cronurl = $this->context->link->getModuleLink(
            'giftcard',
            'cron',
            ['token' => Configuration::get('GIFTCARD_TOKEN')],
            true
        );
        // Init Fields form array
        // $giftcards = GiftCardProduct::getGiftCards();
        if ($this->imagick) {
            $description_useimagick = $this->l('Imagick is installed you can use generic template');
        } else {
            $description_useimagick =
                $this->l('The module will work with JPEG images, but if you want use also vector templates with extension SVG, php imagick extension is required');
        }
        $fields_form = [];
        /*
         * $fields_form[0]['form'] = array (
         * 'legend' => array (
         * 'title' => $this->l('Documentation'),
         * 'image' => $this->img_directory.'icon/pdf.gif'
         * ),
         * 'description' => $this->l('Read').'&nbsp;'.$this->l('before use it').'<br/>
         * );
         */
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Front office and configuration'),
                'image' => $this->img_directory . 'icon/front_conf.png',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Front office theme'),
                    'name' => 'theme_cgc',
                    'desc' => $this->l('The module proposes several themes of the page of the choice of the gift card, select here the theme that is suitable for you.'),
                    'required' => true,
                    'options' => [
                        'query' => [
                            [
                                'id' => 'basic',
                                'name' => 'Basic',
                            ],
                            [
                                'id' => 'classic',
                                'name' => 'Classic',
                            ],
                            [
                                'id' => 'classicsimple',
                                'name' => 'Classic simple',
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Prefix'),
                    'name' => 'prefix',
                    'desc' => $this->l('Prefix used for gift card code (e.g. GC-****************)'),
                    'size' => 15,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Code length'),
                    'name' => 'code_length',
                    'desc' => $this->l('Number of characters used for gift card code (e.g. GC-1234567890123456)'),
                    'size' => 10,
                    'required' => true,
                ],
                [
                    'type' => 'radio',
                    'label' => $this->l('Shop Restriction (applicable only for multi-shop feature)'),
                    'desc' => $this->l('If you activate this option, the value of the gift card will only be usable in the shop where the gift card was purchased.'),
                    'name' => 'GIFTCARD_VOUCHER_SHOP_RESTRICTION',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'GIFTCARD_VOUCHER_SHOP_RESTRICTION_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'GIFTCARD_VOUCHER_SHOP_RESTRICTION_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Text in front office:'),
                    'name' => 'front_content',
                    'autoload_rte' => true,
                    'required' => true,
                    'lang' => true,
                    'rows' => 10,
                    'cols' => 100,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Height card image'),
                    'name' => 'front_img_height',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Height card image in front office'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Width card image'),
                    'name' => 'front_img_width',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Width card image in front office'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Height card image'),
                    'name' => 'front_limg_height',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Height large card image in front office'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Width card image'),
                    'name' => 'front_limg_width',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Width large card image in front office'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Template label'),
                    'name' => 'cf_template',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Picture label'),
                    'name' => 'cf_picture',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('From label'),
                    'name' => 'cf_from',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Lastname label'),
                    'name' => 'cf_lastname',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Mail recipient label'),
                    'name' => 'cf_to',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Message label'),
                    'name' => 'cf_message',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Date send label'),
                    'name' => 'cf_date_send',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'hint' => $this->l('Invalid characters: numbers and') . ' !<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('This will be displayed in the cart summary, as well as on the invoice'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button',
            ],
        ];
        $fields_form[1]['form'] = [
            'legend' => [
                'title' => $this->l('Email'),
                'image' => $this->img_directory . 'icon/mail.png',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Mail subject'),
                    'name' => 'mail_obj_cust',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'desc' => $this->l('Received by the customer who purchased the gift card'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Mail subject'),
                    'name' => 'mail_obj_rec',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'desc' => $this->l('Received by the recipient of the gift card'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Width card image'),
                    'name' => 'mail_img_width',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Width card image in mail'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Height card image'),
                    'name' => 'mail_img_height',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Height card image in mail'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button',
            ],
        ];
        $fields_form[2]['form'] = [
            'legend' => [
                'title' => $this->l('Pdf'),
                'image' => $this->img_directory . 'icon/pdf.gif',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Pdf prefix'),
                    'name' => 'pdf_prefix',
                    'lang' => true,
                    'size' => 40,
                    'required' => true,
                    'desc' => $this->l('Prefix used for gift card name (e.g. GIFTCARD-00001.pdf)'),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Pdf content:'),
                    'name' => 'pdf_content',
                    'autoload_rte' => true,
                    'required' => true,
                    'lang' => true,
                    'rows' => 10,
                    'cols' => 100,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Width card image'),
                    'name' => 'pdf_img_width',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Width card image in pdf generated'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Height card image'),
                    'name' => 'pdf_img_height',
                    'size' => 5,
                    'required' => true,
                    'suffix' => 'px',
                    'desc' => $this->l('Height card image in pdf generated'),
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Preview PDF Language'),
                    'name' => 'pdfpreview_id_lang',
                    'desc' => $this->l('Use just to see the result'),
                    'required' => true,
                    'options' => [
                        'query' => Language::getLanguages(false),
                        'id' => 'id_lang',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save and Preview'),
                'name' => 'previewpdf',
                'class' => 'button',
            ],
        ];
        $fields_form[3]['form'] = [
            'legend' => [
                'title' => $this->l('Using process'),
                'image' => $this->img_directory . 'icon/info.png',
            ],
            'input' => [
                [
                    'type' => 'radio',
                    'label' => $this->l('Move to payment'),
                    'name' => 'GIFTCARD_COUPON_TO_PAYMENT',
                    'required' => true,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'GIFTCARD_COUPON_TO_PAYMENT_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'GIFTCARD_COUPON_TO_PAYMENT_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'desc' => '<b>**' . $this->l('Beta feature') . '**</b>&nbsp;' .
                        $this->l('When order is completed move the value of coupon to payment.') . '<br/>' .
                        $this->l('Please see the documentation, this feature can require some check/updates.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button',
                'name' => 'save_workflow',
            ],
        ];
        $fields_form[4]['form'] = [
            'legend' => [
                'title' => $this->l('Planification'),
                'image' => $this->img_directory . 'icon/time.gif',
            ],
            'description' => $this->l('A process is launched every day to check the gifts cards to send. For customers who checked a date specific delivery of the gift card at the time of purchase.'),
            'input' => [
                [
                    'type' => 'radio',
                    'label' => $this->l('First visitor'),
                    'name' => 'triggerweb',
                    'required' => true,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('First visitor'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Crontab'),
                        ],
                    ],
                    'desc' => '<b><u>' . $this->l('First visitor') . '</u></b><br/>' .
                        $this->l('When the first visitor of the day connects to your site,is launched in background') .
                        '<br/>' . $this->l('This solution is use when you cannot access a crontab on your server') . '</br>' .
                        '<b><u>' . $this->l('Crontab') . '</u></b><br/>' .
                        $this->l('Contact your host or webmaster to configure crontab.') . '</br>' .
                        $this->l('The following lines are to be added to crontab :') . '</br>' .
                        '30 4 *       *       *       curl <b>' . $cronurl . '</b></br>' .
                        $this->l('Also, giftcard is launch every day with a specific hour, here with this cron 4hours 30min AM') . '</br>',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button',
            ],
        ];
        $fields_form[5]['form'] = [
            'legend' => [
                'title' => $this->l('Ready to use'),
                'image' => $this->img_directory . 'icon/info.png',
            ],
            'description' => $description_useimagick . '<br/>' .
                $this->l('This action automatically creates models and gift cards.') . '<br/>' .
                $this->l('The process may take a few minutes..'),
            'input' => [
                [
                    'type' => 'radio',
                    'label' => $this->l('Customizable template'),
                    'name' => 'withgenerictemplate',
                    'required' => true,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'withgenerictemplate_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'withgenerictemplate_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Launch'),
                'class' => 'button',
                'name' => 'loaddefault',
            ],
        ];
        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true; // false -> remove toolbar
        $helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $languages = Language::getLanguages(false);
        $nblang = count($languages);
        for ($i = 0; $i < $nblang; ++$i) {
            if ($languages[$i]['id_lang'] == $default_lang) {
                $languages[$i]['is_default'] = 1;
            } else {
                $languages[$i]['is_default'] = 0;
            }
        }
        $helper->languages = $languages;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' .
                    $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];
        // Load current value
        $helper->fields_value['withgenerictemplate'] = ($this->imagick ? 1 : 0);
        $helper->fields_value['theme_cgc'] = Configuration::get('GIFTCARD_THEME_CGC');
        $helper->fields_value['prefix'] = Configuration::get('GIFTCARD_PREFIX');
        $helper->fields_value['code_length'] = (int) Configuration::get('GIFTCARD_CODE_LENGTH');
        $helper->fields_value['pdf_img_width'] = Configuration::get('GIFTCARD_PDF_IMG_WIDTH');
        $helper->fields_value['pdf_img_height'] = Configuration::get('GIFTCARD_PDF_IMG_HEIGHT');
        $helper->fields_value['mail_img_width'] = Configuration::get('GIFTCARD_MAIL_IMG_WIDTH');
        $helper->fields_value['mail_img_height'] = Configuration::get('GIFTCARD_MAIL_IMG_HEIGHT');
        $helper->fields_value['front_img_width'] = Configuration::get('GIFTCARD_FRONT_IMG_WIDTH');
        $helper->fields_value['front_img_height'] = Configuration::get('GIFTCARD_FRONT_IMG_HEIGHT');
        $helper->fields_value['front_limg_width'] = Configuration::get('GIFTCARD_FRONT_LIMG_WIDTH');
        $helper->fields_value['front_limg_height'] = Configuration::get('GIFTCARD_FRONT_LIMG_HEIGHT');
        $helper->fields_value['triggerweb'] = Configuration::get('GIFTCARD_TRIGGERWEB');
        $helper->fields_value['pdfpreview_id_lang'] = Tools::getValue(
            'pdfpreview_id_lang',
            (int) Configuration::get('PS_LANG_DEFAULT')
        );
        $helper->fields_value['pdfpreview_withinformation'] = (int) Tools::getValue('pdfpreview_withinformation', 0);
        $helper->fields_value['pdfpreview_id_gift_card'] = (int) Tools::getValue('pdfpreview_id_gift_card', 0);
        $helper->fields_value['GIFTCARD_COUPON_TO_PAYMENT'] = (int) Configuration::get('GIFTCARD_COUPON_TO_PAYMENT');
        foreach ($languages as $language) {
            $helper->fields_value['front_content'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_FRONT_CONTENT', (int) $language['id_lang']);
            $helper->fields_value['cf_template'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_TEMPLATE', (int) $language['id_lang']);
            $helper->fields_value['cf_picture'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_IMAGE', (int) $language['id_lang']);
            $helper->fields_value['cf_from'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_FROM', (int) $language['id_lang']);
            $helper->fields_value['cf_to'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_TO', (int) $language['id_lang']);
            $helper->fields_value['cf_lastname'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_LASTNAME', (int) $language['id_lang']);
            $helper->fields_value['cf_date_send'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_DATESEND', (int) $language['id_lang']);
            $helper->fields_value['cf_message'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_CF_MESSAGE', (int) $language['id_lang']);
            $helper->fields_value['mail_obj_rec'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_MAIL_OBJ_REC', (int) $language['id_lang']);
            $helper->fields_value['mail_obj_cust'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_MAIL_OBJ_CUST', (int) $language['id_lang']);
            $helper->fields_value['pdf_content'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_PDF_CONTENT', (int) $language['id_lang']);
            $helper->fields_value['pdf_prefix'][(int) $language['id_lang']] =
                Configuration::get('GIFTCARD_PDF_PREFIX', (int) $language['id_lang']);
        }

        return $helper->generateForm($fields_form);
    }

    public function accessLogsDirectory()
    {
        if (!is_readable(_PS_ROOT_DIR_ . '/modules/giftcard/logs')
            || !is_writable(_PS_ROOT_DIR_ . '/modules/giftcard/logs')) {
            return false;
        }

        return true;
    }

    public function log($log_message)
    {
        if ($this->accesslog) {
            if ($this->log_fic_name == '') {
                $this->log_fic_name = 'giftcardlog-' . date('Ymd') . '.log';
            }
            $fp = @fopen($this->log_directory . $this->log_fic_name, 'a');
            @fwrite($fp, date('H\Hi') . ' ' . $log_message . '\n');
            @fclose($fp);
        }
    }

    public function loglongline($log_message)
    {
        if ($this->accesslog) {
            if ($this->log_fic_name == '') {
                $this->log_fic_name = 'giftcardlog-' . date('Ymd') . '.log';
            }
            $fp = @fopen($this->log_directory . '/' . $this->log_fic_name, 'a');
            if ($log_message) {
                @fwrite($fp, $log_message . '\n');
            }
            @fwrite($fp, '============================================================================\n');
            @fclose($fp);
        }
    }
}