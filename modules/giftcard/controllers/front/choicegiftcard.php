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
class GiftCardChoiceGiftCardModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public $theme_cgc = 'classic';

    public function __construct()
    {
        parent::__construct();
        $this->theme_cgc = Configuration::get('GIFTCARD_THEME_CGC', 'classic');
        $this->context = Context::getContext();
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->assign();
    }

    public function setMedia()
    {
        parent::setMedia();
        if (count($this->errors)) {
            return;
        }
        /*
     * $this->addCSS ( array (
     * _PS_CSS_DIR_.'jquery.fancybox-1.3.4.css' => 'screen',
     * _MODULE_DIR_.'giftcard/views/css/choicegiftcard.css' => 'screen'
     * ) );
     */
        $this->addJqueryPlugin([
            'fancybox',
            'scrollTo',
            'serialScroll',
        ]);
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            if ($this->theme_cgc == 'classic' || $this->theme_cgc == 'basic') {
                // classic and basic templates
                $this->context->controller->registerStylesheet(
                    'modules-giftcard-gcicons',
                    'modules/giftcard/views/css/icons/gcicons.css',
                    ['media' => 'all', 'priority' => 150]
                );
                $this->context->controller->registerStylesheet(
                    'modules-giftcard-choicegiftcard',
                    'modules/giftcard/views/css/cgc_theme/' . $this->theme_cgc . '/choicegiftcard.css',
                    ['media' => 'all', 'priority' => 150]
                );
                $this->context->controller->registerStylesheet(
                    'modules-giftcard-choicegiftcard-modal',
                    'modules/giftcard/views/css/cgc_theme/choicegiftcard-cart-sucess-modal.css',
                    ['media' => 'all', 'priority' => 150]
                );
                $this->context->controller->registerJavascript(
                    'modules-giftcard-choicegiftcard-jcarousel',
                    'modules/giftcard/views/js/vendors/jquery.jcarousel.min.js',
                    ['position' => 'bottom', 'priority' => 150]
                );
                $this->context->controller->registerJavascript(
                    'modules-giftcard-choicegiftcard',
                    'modules/giftcard/views/js/choicegiftcard.js',
                    ['position' => 'bottom', 'priority' => 150]
                );
            } elseif ($this->theme_cgc == 'classicsimple') {
                // Classic simple template
                $this->context->controller->registerStylesheet(
                    'modules-giftcard-choicegiftcard',
                    'modules/giftcard/views/css/cgc_theme/' . $this->theme_cgc . '/choicegiftcard.css',
                    ['media' => 'all', 'priority' => 150]
                );
                $this->context->controller->registerJavascript(
                    'modules-giftcard-choicegiftcard',
                    'modules/giftcard/views/js/front/' . $this->theme_cgc . '/choicegiftcard.js',
                    ['position' => 'bottom', 'priority' => 150]
                );
            }
        } else {
            $this->addCSS([
                _PS_CSS_DIR_ . 'jquery.fancybox-1.3.4.css' => 'screen',
                _MODULE_DIR_ . 'giftcard/views/css/icons/gcicons.css' => 'screen',
                _MODULE_DIR_ . 'giftcard/views/css/cgc_theme/' . $this->theme_cgc . '/choicegiftcard.css' => 'screen',
                _MODULE_DIR_ . 'giftcard/views/css/cgc_theme/choicegiftcard-cart-sucess-modal.css' => 'screen',
            ]);
            $this->addJS([
                _THEME_JS_DIR_ . 'tools.js',
                _MODULE_DIR_ . 'giftcard/views/js/vendors/jquery.jcarousel.min.js',
                _MODULE_DIR_ . 'giftcard/views/js/choicegiftcard.js',
            ]);
        }

        return true;
    }

    /* Use to preview image in email */
    private function dataUri($file, $mime)
    {
        $contents = Tools::file_get_contents($file);
        $base64 = base64_encode($contents); // use preview image, viewed with Francois Gaillard & Emmanuel

        return 'data:' . $mime . ';base64,' . $base64;
    }

    public function postProcess()
    {
        /* PREVIEW EMAIL */
        if (trim(Tools::getValue('action')) == 'preview') {
            if (Tools::getToken() !== Tools::getValue('token')) {
                exit($this->module->l('Invalid token', 'choicegiftcard'));
            }
            $template_id = (int) Tools::getValue('id_gift_card_template', 0);
            $id_shop = $this->context->shop->id;
            $gift_card_template = null;
            if (!$template_id) {
                $gift_card_template = GiftCardTemplate::getDefault();
            } else {
                $gift_card_template = new GiftCardTemplate($template_id);
            }
            $output = '';
            if (!$gift_card_template) {
                $output .= Tools::displayError($this->l('Default gift card template is required to preview PDF'));
            } else {
                $receptmode = (int) Tools::getValue('receptmode', 0);
                $id_product = (int) Tools::getValue('id_product_virtual');
                if ($receptmode > 1) {
                    $id_product = (int) Tools::getValue('id_product_physical');
                }
                if ($id_product) {
                    $card = new GiftCardProduct($id_product);
                } else {
                    $card = GiftCardProduct::getDefault();
                }
                $params = [];
                $params['id_shop'] = (int) $id_shop;
                $params['id_currency'] = $this->context->currency->id;
                $params['discountcode'] = '##################';
                $params['price'] = round($card->price, 2);
                $params['date_to'] = Tools::displayDate(
                    date('Y-m-d', strtotime(date('Y-m-d', time()) . ' + ' . (int) $card->period_val . ' month'))
                );
                $params['id_lang'] = $this->context->language->id;
                $params['message'] = Tools::getValue('message', '');
                $params['lastname'] = Tools::getValue('lastname', '');
                $params['from'] = Tools::getValue('from', '');
                $prefix_pdf = Configuration::get('GIFTCARD_PDF_PREFIX', (int) $params['id_lang']);
                if (!$prefix_pdf || empty($prefix_pdf)) {
                    $prefix_pdf = 'GIFTCARD';
                }
                $filename = $prefix_pdf . sprintf('%06d', 0);
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                GiftCardTools::processGeneratePdfV2($gift_card_template, $params, true, $filename);
            }
        }
        /* LOAD CONTENT */
        if (!Tools::getValue('action')) {
            $card = null;
            if (Tools::getValue('id_product')) {
                $card = new GiftCardProduct((int) Tools::getValue('id_product'));
            }
            /* CONTROL PRICE SPECIFIC : explain in rule : RG-PRICE-01 */
            $cards = GiftCardProduct::getGiftCards(
                $this->context->language->id,
                true,
                $this->context->currency->id,
                (int) $this->context->shop->id
            );
            $cardschecked = [];
            $nb_checked = 0;
            $virtual_cards_available = false;
            $physical_cards_available = false;
            $current_currency_id = (int) $this->context->currency->id;
            $default_currency_id = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            $filter_by_currency_validity = ($current_currency_id !== $default_currency_id);
            foreach ($cards as $carditem) {
                if ($filter_by_currency_validity) {
                    $card_obj = new GiftCardProduct((int) $carditem['id_product']);
                    if (!$card_obj->validityPrice($current_currency_id)) {
                        continue;
                    }
                }
                if ((int) $carditem['virtualcard']) {
                    $virtual_cards_available = true;
                } else {
                    $physical_cards_available = true;
                }
                $cardschecked[] = $carditem;
                $cardschecked[$nb_checked]['price_dp'] = Tools::displayPrice(
                    $carditem['price'],
                    $this->context->currency,
                    false
                );
                ++$nb_checked;
            }
            $tags = GiftCardTag::getTags($this->context);
            $templates = GiftCardTemplate::getTemplates(
                $this->context->language->id,
                true,
                (int) $this->context->shop->id
            );
            $template_default = GiftCardTemplate::getDefault();
            $templates_group_tag = GiftCardTemplate::getTemplatesGroupByTag(
                $this->context->language->id,
                true,
                (int) $this->context->shop->id
            );
            $front_content = Configuration::get('GIFTCARD_FRONT_CONTENT', (int) $this->context->language->id);
            $this->context->smarty->assign([
                'gc_tags' => $tags,
                'template_default' => $template_default,
                'front_content' => $front_content,
                'templates' => $templates,
                'gc_templates' => $templates,
                'token_page' => Tools::getToken(),
                'giftcard_templates_dir' => _MODULE_DIR_ . 'giftcard/img/templates/',
                'templatesGroupTag' => $templates_group_tag,
                'currencySign' => $this->context->currency->sign,
                'currencyRate' => $this->context->currency->conversion_rate,
                'currencyFormat' => $this->context->currency->format,
                'currencyBlank' => $this->context->currency->blank,
                'sl_year' => date('Y'),
                'sl_month' => date('n'),
                'sl_day' => date('j'),
                'years' => [
                    date('Y'),
                    (int) date('Y') + 1,
                    (int) date('Y') + 2,
                ],
                'months' => Tools::dateMonths(),
                'days' => Tools::dateDays(),
                'card' => $card,
                'cards' => $cardschecked,
                'id_lang' => $this->context->language->id,
                'virtual_cards_available' => $virtual_cards_available,
                'physical_cards_available' => $physical_cards_available,
            ]);
        } elseif (trim(Tools::getValue('action')) == 'addgiftcard') {
            $errors = [];
            $receptmode = (int) Tools::getValue('receptmode', 0);
            $id_product = (int) Tools::getValue('id_product_virtual');
            if ($receptmode > 1) {
                $id_product = (int) Tools::getValue('id_product_physical');
            }
            $card = new GiftCardProduct($id_product);
            if (Tools::getToken() !== Tools::getValue('token')) {
                $errors[] = $this->module->l('Invalid token', 'choicegiftcard');
            }
            if (count($errors) == 0) {
                $currency_default = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                if ($this->context->currency->id != $currency_default
                    && !$card->validityPrice((int) $this->context->currency->id)) {
                    // message display to customer
                    $errors[] = $this->module->l('This card is disabled, please select other card thank.',
                        'choicegiftcard');
                } else {
                    $mailto = Tools::getValue('mailto', '');
                    $lastname = Tools::getValue('lastname', '');
                    $from = Tools::getValue('from', '');
                    $message = trim(Tools::getValue('message', ''));
                    $template_id = (int) Tools::getValue('id_gift_card_template', 0);
                    $delivery_date = (Tools::getValue('years', '') == '' ? '' :
                        (int) Tools::getValue('years') . '-' .
                        (int) Tools::getValue('months') . '-' .
                        (int) Tools::getValue('days'));
                    $gift_card_template = null;
                    if ((int) $receptmode == 1 && (!Validate::isEmail($mailto) || empty($mailto))) {
                        $errors[] = $this->module->l('Badly formatted email address', 'choicegiftcard');
                    }
                    if (!$template_id > 0) {
                        $errors[] = $this->module->l('You must select a model', 'choicegiftcard') . $template_id;
                    } else {
                        $gift_card_template = new GiftCardTemplate($template_id);
                        if ((int) $gift_card_template->id > 0) {
                            $this->module->l('Template is not valide, please choice other', 'choicegiftcard');
                        }
                    }
                    if (empty($from)) {
                        $errors[] = $this->module->l('From is required', 'choicegiftcard');
                    } else {
                        if (!Validate::isMessage($from)) {
                            $errors[] = sprintf(
                                $this->module->l('The field %s contain invalid character <>{}', 'choicegiftcard'),
                                $this->module->l('from', 'choicegiftcard')
                            );
                        }
                    }
                    if (empty($lastname)) {
                        $errors[] = $this->module->l('Recipient firstname is required', 'choicegiftcard');
                    } else {
                        if (!Validate::isMessage($lastname)) {
                            $errors[] = sprintf(
                                $this->module->l('The field %s contain invalid character <>{}', 'choicegiftcard'),
                                $this->module->l('Recipient first name', 'choicegiftcard')
                            );
                        }
                    }
                    if (empty($message)) {
                        $errors[] = $this->module->l('Message is required', 'choicegiftcard');
                    } else {
                        if (!Validate::isMessage($message)) {
                            $errors[] = sprintf(
                                $this->module->l('The field %s contain invalid character <>{}', 'choicegiftcard'),
                                $this->module->l('Message', 'choicegiftcard')
                            );
                        }
                    }
                    if ((int) $receptmode == 1
                        && !@checkdate(Tools::getValue('months'), Tools::getValue('days'), Tools::getValue('years'))) {
                        $errors[] = $this->module->l('Date send card is invalid', 'choicegiftcard');
                    }
                    if ((int) Tools::getValue('receptmode') == 1) {
                        $date_now = strtotime(date('Y') . '-' . date('m') . '-' . date('d'));
                        $date_send = strtotime(Tools::getValue('years') .
                            '-' . Tools::getValue('months') . '-' . Tools::getValue('days'));
                        $diff_date = (($date_now - $date_send) / 3600 / 24);
                        if ($diff_date >= 1) {
                            $errors[] = $this->module->l('The mailing date must be greater than or equal to the date of days',
                                'choicegiftcard');
                        }
                    }
                }
            }
            if (count($errors) == 0) {
                /* add new card if not exist */
                if (!$this->context->cart->id) {
                    if (Context::getContext()->cookie->id_guest) {
                        $guest = new Guest(Context::getContext()->cookie->id_guest);
                        $this->context->cart->mobile_theme = $guest->mobile_theme;
                    }
                    $this->context->cart->add();
                    if ($this->context->cart->id) {
                        $this->context->cookie->id_cart = (int) $this->context->cart->id;
                    }
                }
                $id_cfield_mailto = (int) $card->id_customization_field_mailto;
                $id_cfield_message = (int) $card->id_customization_field_message;
                $id_cfield_from = (int) $card->id_customization_field_from;
                $id_cfield_lastname = (int) $card->id_customization_field_lastname;
                $id_cfield_deliverydate = (int) $card->id_customization_field_deliverydate;
                $id_cfield_template = (int) $card->id_customization_field_template;
                $id_cfield_image = (int) $card->id_customization_field_image;
                /* Upload de l'image */
                $file_name = md5(uniqid(rand(), true));
                $product_picture_width = (int) Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                $product_picture_height = (int) Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                if ($gift_card_template->issvg) {
                    $svgparams = [];
                    $svgparams['price'] = round($card->amount);
                    $svgparams['from'] = $from;
                    $svgparams['lastname'] = $lastname;
                    $svgparams['message'] = $message;
                    $svgparams['mailto'] = $mailto;
                    $svg = GiftCardTools::buildTemplateSvgV2(
                        $gift_card_template,
                        $svgparams,
                        $this->context->language->id
                    );
                    if (!GiftCardTools::resizeImageWithTemplate(
                        $svg,
                        _PS_UPLOAD_DIR_ . $file_name,
                        0,
                        1600,
                        920,
                        'jpg'
                    )) {
                        $errors[] = $this->module->l('An error occurred while creating template');
                    } elseif (!GiftCardTools::resizeImageWithTemplate(
                        $svg,
                        _PS_UPLOAD_DIR_ . $file_name . '_small',
                        0,
                        $product_picture_width,
                        $product_picture_height,
                        'jpg'
                    )) {
                        $errors[] = $this->module->l('An error occurred while creating template');
                    } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777)
                        || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777)) {
                        $errors[] = $this->module->l('An error occurred while creating template.');
                    } else {
                        $this->context->cart->addPictureToProduct(
                            $card->id,
                            $id_cfield_image,
                            Product::CUSTOMIZE_FILE,
                            $file_name
                        );
                    }
                } else {
                    $template_path = $gift_card_template->img_dir . $gift_card_template->id . '/';
                    if (!GiftCardTools::imageResize(
                        $template_path . $gift_card_template->id . '.jpg',
                        _PS_UPLOAD_DIR_ . $file_name
                    )) {
                        $errors[] = $this->module->l('An error occurred while copying image');
                    } elseif (!GiftCardTools::imageResize(
                        $template_path . $gift_card_template->id . '.jpg',
                        _PS_UPLOAD_DIR_ . $file_name . '_small',
                        $product_picture_width,
                        $product_picture_height
                    )) {
                        $errors[] = $this->module->l('An error occurred while copying image');
                    } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777)
                        || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777)) {
                        $errors[] = $this->module->l('An error occurred while creating template.');
                    } else {
                        $this->context->cart->addPictureToProduct(
                            $card->id,
                            $id_cfield_image,
                            Product::CUSTOMIZE_FILE,
                            $file_name
                        );
                    }
                }
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_from,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $from
                );
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_lastname,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $lastname
                );
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_message,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $message
                );
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_template,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $template_id
                );
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_mailto,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $mailto
                );
                $this->context->cart->addTextFieldToProduct(
                    $card->id,
                    $id_cfield_deliverydate,
                    Product::CUSTOMIZE_TEXTFIELD,
                    $delivery_date
                );
                $customization_datas = $this->context->cart->getProductCustomization(
                    $card->id,
                    null,
                    true
                );
                $id_customization = 0;
                if (!empty($customization_datas) || (int) $customization_datas[0]['id_customization']) {
                    $id_customization = (int) $customization_datas[0]['id_customization'];
                    $update_quantity = $this->context->cart->updateQty(
                        1,
                        $card->id,
                        null,
                        $id_customization
                    );
                    Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'customization`
                    SET
                        `quantity` = 1,
                        `in_cart` = 1
                    WHERE `id_customization` = ' . (int) $id_customization);
                    if (!$update_quantity) {
                        $errors[] = Tools::displayError(
                            'You already have the maximum quantity available for this product.',
                            false
                        );
                    }
                } else {
                    $errors[] = Tools::displayError(
                        'Error during customization creation.',
                        false
                    );
                }
                if (!count($errors) > 0) {
                    if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                        $presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter();
                    } else {
                        $presenter = new PrestaShop\PrestaShop\Adapter\Cart\CartPresenter();
                    }
                    $presentedCart = $presenter->present($this->context->cart);
                    $imageRetriever = new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                        $this->context->link,
                        $this->context->language
                    );
                    $imageCustom = $imageRetriever->getCustomizationImage(
                        $file_name
                    );
                    $this->context->smarty->assign([
                        'gc_upload_img' => rtrim($this->context->link->getBaseLink(), '/') .
                            '/upload/' . $file_name,
                        'imageCustom' => $imageCustom,
                        'gc_from' => $from,
                        'gc_to' => $lastname,
                        'gc_price' => Tools::displayPrice($card->amount, $this->context->currency, false),
                        'cart' => $presentedCart,
                        'cart_url' => $this->context->link->getPageLink(
                            'cart',
                            null,
                            $this->context->language->id,
                            ['action' => 'show']
                        ),
                    ]);
                    if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
                        $modal_content = $this->context->smarty->fetch(
                            'module:giftcard/views/templates/front/cgc_theme/_partials/choicegiftcard-success-modal.tpl'
                        );
                    } else {
                        $modal_content = $this->context->smarty->fetch(
                            _PS_MODULE_DIR_ . 'giftcard/views/templates/front/cgc_theme/_partials/' .
                            'choicegiftcard-success-modal.tpl'
                        );
                    }
                    if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                        $presentedCart['products'] = $this->get('prestashop.core.filter.front_end_object.product_collection')
                            ->filter($presentedCart['products']);
                    }
                    $results = [
                        'hasError' => false,
                        'modal_content' => $modal_content,
                        'cart' => $presentedCart,
                        'id_product' => $card->id,
                        'id_customization' => $id_customization,
                        'message' => $this->module->l('The gift card as added in your cart', 'choicegiftcard'),
                        'errors' => $errors,
                    ];
                    exit(json_encode($results));
                }
            }
            if (count($errors) > 0) {
                $results = [
                    'hasError' => true,
                    'errors' => $errors,
                ];
                exit(json_encode($results));
            }
        }
    }

    private function getBaseLink()
    {
        $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $shop = Context::getContext()->shop;
        $base = (($ssl) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);

        return $base . $shop->getBaseURI();
    }

    public function assign()
    {
        $this->context->smarty->assign([
            'linkcgc' => $this->context->link->getModuleLink('giftcard', 'choicegiftcard', [], true),
        ]);
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->setTemplate(
                'module:giftcard/views/templates/front/cgc_theme/' . $this->theme_cgc . '/choicegiftcard.tpl'
            );
        } else {
            $this->setTemplate('cgc_theme/' . $this->theme_cgc . '/choicegiftcard.tpl');
        }
    }
}
