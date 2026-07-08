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
class GiftCardOrder extends ObjectModel
{
    public $customer_mail;

    public $to_mail;

    public $message;

    public $lastname;

    public $receptmode = 0;
    /* 0: print at home 1: send by email */
    public $id_gift_card_template;

    public $id_customization;

    public $from;

    public $delivery_date;

    public $id_order;

    public $id_cart;

    public $id_product;

    public $id_currency;

    public $price;

    public $quantity;

    public $discountcode;

    public $id_lang;

    public $id_cart_rule;

    public $date_add;

    public $date_upd;

    public $info;

    public $status;

    public $period_val;

    public $sended = 0;

    protected $table = 'giftcardorder';

    protected $identifier = 'id_gift_card_order';

    public static $definition = [
        'table' => 'giftcardorder',
        'primary' => 'id_gift_card_order',
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_gift_card_template' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'receptmode' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'period_val' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_customization' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'customer_mail' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'to_mail' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'message' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'lastname' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'from' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'delivery_date' => [
                'type' => self::TYPE_DATE,
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_cart' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_lang' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_currency' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'quantity' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'discountcode' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'status' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'sended' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'id_cart_rule' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true,
            ],
            'info' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
                'size' => 3999999999999,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
        ],
    ];

    public static function exists($id_order, $status = null, $return = false, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = Context::getContext()->language->id;
        }
        if (Validate::isUnsignedId($id_order)) {
            $sql = 'SELECT go.*,c.quantity as voucher_qty,c.active as voucher_active,
                oc.id_order used_order,cl.`name` as cart_rule_name
                FROM `' . _DB_PREFIX_ . 'giftcardorder` go
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` c on c.id_cart_rule = go.id_cart_rule
                LEFT JOIN `' . _DB_PREFIX_ . 'order_cart_rule` oc on oc.id_cart_rule = c.id_cart_rule
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` cl on c.id_cart_rule = cl.id_cart_rule
                    AND cl.id_lang = ' . (int) $id_lang .
                ' WHERE 1 ' . (isset($status) ? ' AND go.`status` = \'' . pSQL($status) . '\' ' : '') .
                ' AND go.`id_order` = ' . (int) $id_order;
            $result = Db::getInstance()->ExecuteS($sql);
            if (empty($result) === false && $result != false && count($result)) {
                if ($return === false) {
                    return true;
                } else {
                    return $result;
                }
            }
        }

        return false;
    }

    public static function cartexists($id_cart, $return = false)
    {
        $id_lang = Context::getContext()->language->id;
        if (Validate::isUnsignedId($id_cart)) {
            $sql = 'SELECT go.*,c.quantity as voucher_qty,c.active as voucher_active,oc.id_order used_order,
                cl.`name` as cart_rule_name
                FROM `' . _DB_PREFIX_ . 'giftcardorder` go
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` c on c.id_cart_rule = go.id_cart_rule
                LEFT JOIN `' . _DB_PREFIX_ . 'order_cart_rule` oc on oc.id_cart_rule = c.id_cart_rule
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` cl on c.id_cart_rule = cl.id_cart_rule AND cl.id_lang = ' .
                (int) $id_lang .
                ' WHERE 1 AND go.`id_cart` = ' . (int) $id_cart;
            $result = Db::getInstance()->ExecuteS($sql);
            if (empty($result) === false && $result != false && count($result)) {
                if ($return === false) {
                    return true;
                } else {
                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Return list giftcardorder associed to giftcard purchase
     */
    public static function getPurchaseOrders($id_order_purchase, $id_lang = null)
    {
        if (Validate::isUnsignedId($id_order_purchase)) {
            $sql = 'SELECT go.*,c.quantity as voucher_qty,
                c.active as voucher_active,oc.id_order used_order,
                osl.`name` as status_name,os.logable as status_logable,os.`color` as status_color,
                cl.`name` as cart_rule_name
                FROM `' . _DB_PREFIX_ . 'giftcardorder` go
                INNER JOIN `' . _DB_PREFIX_ . 'order_cart_rule` oc on oc.id_cart_rule = go.id_cart_rule
                INNER JOIN `' . _DB_PREFIX_ . 'cart_rule` c on c.id_cart_rule = oc.id_cart_rule
                INNER JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` cl on c.id_cart_rule = cl.id_cart_rule
                    AND cl.id_lang = ' . (int) $id_lang . '
                INNER JOIN `' . _DB_PREFIX_ . 'orders` o on o.id_order = go.id_order
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os on os.id_order_state = o.current_state
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl on osl.id_order_state = o.current_state
                    AND osl.id_lang=' . (int) $id_lang .
                ' WHERE oc.id_order=' . (int) $id_order_purchase;
            $result = Db::getInstance()->ExecuteS($sql);

            return $result;
        }

        return false;
    }

    public static function isGiftCardDiscount($id_cart_rule)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('giftcardorder', 'go');
        $sql->where('go.id_cart_rule = ' . (int) $id_cart_rule);

        return (bool) Db::getInstance()->getValue($sql);
    }

    public static function getGiftCardOrdersToSend($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        $sql = 'SELECT go.* FROM `' . _DB_PREFIX_ . 'giftcardorder` go
			WHERE go.`status` = \'CREATED\'
			AND go.`sended`=0
			AND go.`receptmode`=1
			AND go.`delivery_date` <=\'' . pSQL($date) . '\'
			AND go.`id_cart_rule` not in
			(select id_cart_rule from `' . _DB_PREFIX_ . 'order_cart_rule`)';
        $result = Db::getInstance()->ExecuteS($sql);

        return $result;
    }

    public function addInfoLine($message)
    {
        $info_line = '<li><span class="info_date">' . date('Y-m-d H:i:s') .
            '</span><span class="info_message">' . $message . '</span></li>';
        $this->info = $info_line . (isset($this->info) ? $this->info : '');
    }

    public function generatePDF($render = false)
    {
        $prefix_pdf = Configuration::get('GIFTCARD_PDF_PREFIX', $this->id_lang);
        if (!$prefix_pdf || empty($prefix_pdf)) {
            $prefix_pdf = 'GIFTCARD';
        }
        $file_name = $prefix_pdf . sprintf('%06d', $this->id);
        $order = new Order((int) $this->id_order);
        $gift_card_template = new GiftCardTemplate((int) $this->id_gift_card_template);
        $cart_rule = new CartRule((int) $this->id_cart_rule);
        $params = [];
        $params['id_shop'] = $order->id_shop;
        $product = new Product((int) $this->id_product, false, (int) $order->id_lang);
        if (Validate::isLoadedObject($product)) {
            $params['product_name'] = $product->name;
        } else {
            $params['product_name'] = '';
        }
        $params['price'] = $product->getPrice();
        // $params['price'] = $this->price;
        $params['id_currency'] = $this->id_currency;
        $params['id_gift_card_order'] = $this->id;
        $params['discountcode'] = $this->discountcode;
        $params['id_product'] = $this->id_product;
        $params['date_to'] = Tools::displayDate($cart_rule->date_to, null);
        $params['id_lang'] = $order->id_lang;
        $params['message'] = $this->message;
        $params['lastname'] = $this->lastname;
        $params['from'] = $this->from;
        $params['order_reference'] = $order->reference;
        if ($render) {
            GiftCardTools::processGeneratePdfV2($gift_card_template, $params, $render, $file_name);
        } else {
            return GiftCardTools::processGeneratePdfV2($gift_card_template, $params, $render, $file_name);
        }
    }

    public function mailAttachements()
    {
        $attachments = [];
        $file_attachement = [];
        $prefix_pdf = Configuration::get('GIFTCARD_PDF_PREFIX', $this->id_lang);
        if (!$prefix_pdf || empty($prefix_pdf)) {
            $prefix_pdf = 'GIFTCARD';
        }
        $file_attachement['content'] = $this->generatePDF(false);
        $file_attachement['name'] = $prefix_pdf . sprintf('%06d', $this->id) . '.pdf';
        $file_attachement['mime'] = 'application/pdf';
        $attachments[] = $file_attachement;

        return $attachments;
    }

    public function getGiftCardImage()
    {
        $img_name = md5(uniqid(rand(), true)) . '.jpg';
        $tmp_file = _PS_ROOT_DIR_ . '/modules/giftcard/pdf/' . $img_name;
        $img_width = Configuration::get('GIFTCARD_MAIL_IMG_WIDTH');
        $img_height = Configuration::get('GIFTCARD_MAIL_IMG_HEIGHT');
        if (!((int) $img_width > 0)) {
            $img_width = 349;
        }
        if (!((int) $img_height > 0)) {
            $img_height = 195;
        }
        // $giftCardProduct = new TAGCPMProduct((int)$this->id_product);
        $gift_card_template = new GiftCardTemplate((int) $this->id_gift_card_template);
        if ($gift_card_template->issvg) {
            $svgparams = [];
            $svgparams['price'] = round($this->price);
            $svgparams['from'] = $this->from;
            $svgparams['lastname'] = $this->lastname;
            $svgparams['message'] = $this->message;
            $svgparams['mailto'] = $this->to_mail;
            $svgparams['code'] = $this->discountcode;
            $svg = GiftCardTools::buildTemplateSvgV2($gift_card_template, $svgparams, $this->id_lang);
            $card_img_generated = GiftCardTools::resizeImageWithTemplate(
                $svg,
                $tmp_file,
                0,
                $img_width,
                $img_height,
                'jpg'
            );
        } else {
            $template_path = $gift_card_template->img_dir . $gift_card_template->id . '/';
            $card_img_generated = ImageManager::resize(
                $template_path . $gift_card_template->id . '.jpg',
                $tmp_file,
                $img_width,
                $img_height,
                'jpg'
            );
        }

        return $tmp_file;
    }

    public function mailTemplateVars()
    {
        $template_vars = [];
        $cart_rule = new CartRule((int) $this->id_cart_rule);
        $template_vars['{giftcard_code}'] = $this->discountcode;
        $template_vars['{giftcard_from}'] = $this->from;
        $template_vars['{giftcard_to_mail}'] = $this->to_mail;
        $template_vars['{giftcard_lastname}'] = $this->lastname;
        $template_vars['{giftcard_message}'] = $this->message;
        $template_vars['{giftcard_price}'] = Tools::displayPrice(
            (float) $this->price,
            (int) $this->id_currency,
            false
        );
        $template_vars['{giftcard_expirate}'] = Tools::displayDate($cart_rule->date_to, null);

        return $template_vars;
    }

    public function sendingMail($customer = null, $admin = false)
    {
        $order = new Order((int) $this->id_order);
        if (!isset($customer)) {
            $order = new Order((int) $this->id_order);
            $customer = new Customer((int) $order->id_customer);
        }
        $id_lang = (int) GiftCardTools::getLangMail($this->id_lang);
        if (!$id_lang || !((int) $id_lang > 0)) {
            $this->sended = 1;
            $message = 'Lang ko mail not sended &gt; ' . $customer->email;
            $this->addInfoLine($message);
            $message = 'Lang ko mail not sended &gt; ' . $this->to_mail;
            $this->addInfoLine($message);
        }
        $template_vars = $this->mailTemplateVars();
        $template_vars['{firstname}'] = $customer->firstname;
        $template_vars['{lastname}'] = $customer->lastname;
        $template_vars['{order_reference}'] = $order->reference;
        $list_attachments = $this->mailAttachements();
        $giftcard_image = $this->getGiftCardImage();
        $imageData = base64_encode(file_get_contents($giftcard_image));
        $imageMimeType = mime_content_type($giftcard_image);
        $base64Image = 'data:' . $imageMimeType . ';base64,' . $imageData;
        $template_vars['{giftcard_image}'] = $base64Image;

        if ((int) $this->receptmode == 1 && isset($this->to_mail) && !empty($this->to_mail)) {
            if ($admin
                || (!$this->sended && ($this->delivery_date == null
                        || date('Y-m-d') >= date('Y-m-d', strtotime($this->delivery_date))))) {
                /* Recipient */
                $subjet = Configuration::get('GIFTCARD_MAIL_OBJ_REC', (int) $id_lang);
                $status = Mail::send(
                    (int) $id_lang,
                    'giftcardsend_recipient',
                    sprintf($subjet, $this->from),
                    $template_vars,
                    $this->to_mail,
                    null,
                    null,
                    null,
                    $list_attachments,
                    null,
                    _PS_ROOT_DIR_ . '/modules/giftcard/mails/',
                    false,
                    (int) $order->id_shop,
                    null
                );
                $this->sended = 1;
                $this->update(); /* Evite un envoi multiple suite à une erreur dans les étapes suivantes */
                $message = 'Mail &gt;' . $this->to_mail . ' : ' . ($status ? 'OK' : 'KO');
                $this->addInfoLine($message);
                /* Notif Customer */
                $subjet = Configuration::get('GIFTCARD_MAIL_OBJ_CUST', (int) $id_lang);
                $status = Mail::send(
                    (int) $id_lang,
                    'giftcardsend_notifcustomer',
                    $subjet,
                    $template_vars,
                    $customer->email,
                    null,
                    null,
                    Configuration::get('PS_SHOP_NAME') . ' ',
                    $list_attachments,
                    null,
                    _PS_ROOT_DIR_ . '/modules/giftcard/mails/',
                    false,
                    (int) $order->id_shop,
                    null
                );
                $message = 'Mail &gt; ' . $customer->email . ' : ' . ($status ? 'OK' : 'KO');
                $this->addInfoLine($message);
            }
        } else {
            if ((int) $this->receptmode == 0) {
                /* Print at home */
                $giftcard_image = $this->getGiftCardImage();
                $subjet = Configuration::get('GIFTCARD_MAIL_OBJ_CUST', (int) $id_lang);
                $status = Mail::send(
                    (int) $id_lang,
                    'giftcardprintathome',
                    $subjet,
                    $template_vars,
                    $customer->email,
                    null,
                    null,
                    null,
                    $list_attachments,
                    null,
                    _PS_ROOT_DIR_ . '/modules/giftcard/mails/',
                    false,
                    (int) $order->id_shop,
                    null
                );
                $this->sended = 1;
                $this->update();
                $message = 'Mail &gt;' . $customer->email . ' : ' . ($status ? 'OK' : 'KO');
                $this->addInfoLine($message);
            }
        }
        if (isset($giftcard_image) && $giftcard_image && !empty($giftcard_image)) {
            @unlink($giftcard_image);
        }
    }

    public static function getGCCartRule($id_cart_rule)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('giftcardorder', 'gcocr');
        $sql->where('gcocr.id_cart_rule = ' . (int) $id_cart_rule);

        return Db::getInstance()->getRow($sql);
    }

    public static function getOrderCartRule($id_order, $id_cart_rule)
    {
        $sql = new DbQuery();
        $sql->select('ocr.*');
        $sql->from('order_cart_rule', 'ocr');
        $sql->where('ocr.`id_cart_rule` = ' . (int) $id_cart_rule);
        $sql->where('ocr.`id_order` = ' . (int) $id_order);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public static function moveToPayment($id_order)
    {
        $order = null;
        if (!is_object($id_order) && is_numeric($id_order)) {
            $order = new Order((int) $id_order);
        } elseif (is_object($id_order)) {
            $order = $id_order;
        } else {
            return;
        }
        $ps_price_compute_precision = 2;
        if (version_compare(_PS_VERSION_, '1.6.0.10', '>') === true) {
            $ps_price_compute_precision = _PS_PRICE_COMPUTE_PRECISION_;
        }
        $cart = new Cart($order->id_cart);
        if ((int) $order->id) {
            $cart_rules = $order->getCartRules();
            foreach ($cart_rules as $cart_rule_row) {
                $gco_row = self::getGCCartRule($cart_rule_row['id_cart_rule']);
                if ($gco_row && (int) $gco_row['id_gift_card_order']) {
                    $id_cart_rule = (int) $cart_rule_row['id_cart_rule'];
                    $cart_rule = new CartRule($id_cart_rule);
                    if ($cart_rule->partial_use) {
                        $ocr = self::getOrderCartRule($order->id, (int) $cart_rule->id);
                        if ($ocr && (int) $ocr['id_order_cart_rule']) {
                            $order_cart_rule = new OrderCartRule((int) $ocr['id_order_cart_rule']);
                            // round exception
                            $order->total_discounts_tax_excl = max(
                                $order->total_discounts_tax_excl - $order_cart_rule->value_tax_excl,
                                0
                            );
                            $order->total_discounts_tax_incl = max(
                                $order->total_discounts_tax_incl - $order_cart_rule->value,
                                0
                            );
                            $order->total_discounts = $order->total_discounts_tax_incl;
                            $order->total_paid_tax_excl = (float) Tools::ps_round(
                                $order->total_paid_tax_excl + $order_cart_rule->value_tax_excl,
                                $ps_price_compute_precision
                            );
                            $order->total_paid_tax_incl = (float) Tools::ps_round(
                                $order->total_paid_tax_incl + $order_cart_rule->value,
                                $ps_price_compute_precision
                            );
                            $order->total_paid = $order->total_paid_tax_incl;
                            $order->update();
                            $giftCardOrderUsage = new GiftCardOrderUsage();
                            $giftCardOrderUsage->id_cart_rule = (int) $cart_rule->id;
                            $giftCardOrderUsage->id_order = (int) $order->id;
                            $giftCardOrderUsage->value_tax_excl = (float) $order_cart_rule->value_tax_excl;
                            $giftCardOrderUsage->value_tax_incl = (float) $order_cart_rule->value;
                            $giftCardOrderUsage->add();
                            $order_cart_rule->delete();
                            if ((int) $cart->id) {
                                $cart->removeCartRule((int) $cart_rule->id);
                            }
                            $currency = new Currency($order->id_currency);
                            $order_payment = new OrderPayment();
                            $order_payment->order_reference = $order->reference;
                            $order_payment->id_currency = $currency->id;
                            // we kept the currency rate for historization reasons
                            $order_payment->conversion_rate = $currency->conversion_rate;
                            // if payment_method is define, we used this
                            $order_payment->payment_method = 'giftcard';
                            $order_payment->transaction_id = $cart_rule->code;
                            $order_payment->amount = $order_cart_rule->value;
                            $order_payment->date_add = $order->date_add;
                            $order_payment->add();
                            if ((int) $order_payment->id_currency == (int) $order->id_currency) {
                                $order->total_paid_real += $order_payment->amount;
                            } else {
                                $order->total_paid_real += Tools::ps_round(
                                    Tools::convertPrice($order_payment->amount, $order_payment->id_currency, false),
                                    2
                                );
                            }
                            // We put autodate parameter of add method to true if date_add field is null
                            $order->update();
                        }
                    }
                }
            }
        }
    }
}
