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
if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
    require_once _PS_TOOL_DIR_ . 'tcpdf/config/lang/eng.php';
    if (!class_exists('TCPDF')) {
        require_once _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';
    }
}

class AdminGiftCardOrderController extends ModuleAdminController
{
    public function __construct()
    {
        if (!$this->module) {
            $this->module = Module::getInstanceByName('giftcard');
        }
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->translator = Context::getContext()->getTranslator();
        }
        $this->bootstrap = true;
        $this->table = 'giftcardorder';
        $this->className = 'GiftCardOrder';
        $this->show_toolbar = false;
        $this->list_no_link = true;
        $this->fields_list = [
            'id_order' => [
                'title' => $this->l('Order'),
                'align' => 'left',
                'width' => 30,
                'callback' => 'printLinkOrder',
                'filter_key' => 'a!id_order',
            ],
            'id_gift_card_template' => [
                'title' => $this->l('Template'),
                'align' => 'left',
                'callback' => 'printImage',
                'width' => 170,
            ],
            'price' => [
                'title' => $this->l('Price'),
                'width' => 50,
                'align' => 'right',
                'prefix' => '<b>',
                'suffix' => '</b>',
                'type' => 'price',
                'filter_key' => 'a!price',
                'currency' => true,
            ],
            'receptmode' => [
                'title' => $this->l('Mode'),
                'align' => 'center',
                'width' => 50,
                'callback' => 'printCustom',
            ],
            'sended' => [
                'title' => $this->l('Recipient'),
                'align' => 'center',
                'width' => 30,
                'callback' => 'printSended',
            ],
            'customer' => [
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ],
            'discountcode' => [
                'title' => $this->l('Code'),
                'align' => 'left',
                'width' => 190,
                'callback' => 'printLinkCode',
            ],
            'status' => [
                'title' => $this->l('Status'),
                'width' => '150',
                'align' => 'center',
                'callback' => 'printStatus',
                'orderby' => false,
            ],
            'info' => [
                'title' => $this->l('Info'),
                'align' => 'left',
                'filter' => false,
                'orderby' => false,
                'callback' => 'printInfo',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'width' => 130,
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'id_gift_card_order' => [
                'title' => $this->l('Action'),
                'width' => 70,
                'align' => 'center',
                'callback' => 'printGCOAction',
                'orderby' => false,
            ],
        ];
        $this->identifier = 'id_gift_card_order';
        $this->context = Context::getContext();
        parent::__construct();
    }

    public function setMedia($isNewtheme = false)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            parent::setMedia();
        } else {
            parent::setMedia($isNewtheme);
        }
        $this->addCSS([
            _MODULE_DIR_ . 'giftcard/views/css/admin-ta-common.css',
            _MODULE_DIR_ . 'giftcard/views/css/icons/flaticon.css',
        ]);
        if (version_compare(_PS_VERSION_, '1.6.0', '<') === true) {
            $this->addCSS(_MODULE_DIR_ . 'giftcard/views/css/admin-ta-commonps15.css');
        }
    }

    public function postProcess()
    {
        $this->addCSS([
            _MODULE_DIR_ . 'giftcard/views/css/giftcardorder.css',
        ]);
        $this->addJS([
            _MODULE_DIR_ . 'giftcard/views/js/giftcardorder.js',
        ]);
        parent::postProcess();
    }

    public function initProcess()
    {
        parent::initProcess();
        $access = Profile::getProfileAccess(
            $this->context->employee->id_profile,
            (int) Tab::getIdFromClassName('AdminGiftCardOrder')
        );
        if (Tools::getValue('submitAction')) {
            if ($access['view'] === '1' && ($action = Tools::getValue('submitAction'))) {
                $this->action = $action;
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to view this.');
            }
        }
        if (Tools::isSubmit('sendEmail') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'send_email';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
    }

    public function ajaxProcessShowCustom()
    {
        if ($id = Tools::getValue('id_gift_card_order')) {
            $giftcardorder = new GiftCardOrder($id);
            $this->context->smarty->assign([
                'giftcardorder' => $giftcardorder,
            ]);
            $html = '';
            $html = $this->context->smarty->fetch(parent::getTemplatePath() . 'gift_card_order/list/custom_field.tpl');
            exit($html);
        }
    }

    public function renderList()
    {
        $this->_select = 'pl.`name` as product_name,a.price,i.id_image, a.id_gift_card_order as id_gift_card_order,
							CONCAT(LEFT(customer.`firstname`, 1), \'. \', customer.`lastname`) AS `customer`,
							cr.quantity as voucher_qty,cr.active as voucher_active, IFNULL(gou.id_order, ocr.id_order) used_order';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = a.`id_product` and i.`cover` = 1)
						LEFT JOIN `' . _DB_PREFIX_ . 'currency` curr ON (curr.`id_currency` = a.`id_currency`)
						INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = a.`id_order`)
						LEFT JOIN `' . _DB_PREFIX_ . 'customer` customer ON (customer.`id_customer` = o.`id_customer`)
						LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr on cr.id_cart_rule = a.id_cart_rule
						LEFT JOIN `' . _DB_PREFIX_ . 'order_cart_rule` ocr on ocr.id_cart_rule = a.id_cart_rule
						LEFT JOIN `' . _DB_PREFIX_ . 'giftcard_order_usage` gou on gou.id_cart_rule = a.id_cart_rule
						LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON a.id_product = p.id_product
						LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON a.id_product = pl.id_product AND pl.id_lang=' .
            (int) $this->context->language->id;
        $this->_group = 'group by id_gift_card_order';
        $this->_orderBy = 'id_gift_card_order';
        $this->_orderWay = 'DESC';
        $lists = parent::renderList();
        $this->initToolbar();
        $this->context->smarty->assign([
            'ps_version' => _PS_VERSION_,
        ]);
        $this->context->smarty->assign([
            'ta_gc_tab_select' => 'giftcardorder',
            'link' => $this->context->link,
        ]);
        $html = $this->context->smarty->fetch(parent::getTemplatePath() . '/menu-top.tpl');
        $html .= $this->context->smarty->fetch(parent::getTemplatePath() . 'gift_card_order/list/list_header.tpl');
        $html .= $lists;
        $html .= $this->context->smarty->fetch(parent::getTemplatePath() . 'footer-module.tpl');

        return $html;
    }

    /**
     * Remove all action button not use
     *
     * @see AdminController::initToolbar
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function printLinkCode($value, $gift_card_order)
    {
        if ($value && $value != '') {
            $link_code = $this->context->link->getAdminLink('AdminCartRules') . '&id_cart_rule=' .
                $gift_card_order['id_cart_rule'] . '&updatecart_rule';
            $html = '<a href="' . $link_code . '">' . $value . '&nbsp;<img src="../img/admin/details.gif" alt="' .
                $this->l('View') . '"  width="16px" height="17px"></a>';

            return $html;
        }

        return '';
    }

    public function printLinkOrder($value, $gift_card_order)
    {
        $link_order = $this->getAdminOrderLink($gift_card_order['id_order']);
        $html = '<a href="' . $link_order . '">#' . $value . '&nbsp;<img src="../img/admin/details.gif" alt="' .
            $this->l('View') . '"  width="16px" height="17px"></a>';

        return $html;
    }

    public function printCustom($value, $gift_card_order)
    {
        $html = '<a href="javascript:show_custom(\'' . $this->token . '\',' .
            $gift_card_order['id_gift_card_order'] . ')" id="viewgifcardcustom_' . $gift_card_order['id_gift_card_order'] .
            '" class="viewgifcardcustom"><img src="../modules/giftcard/views/img/customize.gif" alt="' .
            $this->l('View custom') . '"  width="46px" height="37px"/></a>';

        return $html;
    }

    public function printStatus($value, $gift_card_order)
    {
        if ($value == 'WAIT') {
            return '<span class="color_field badge" style="background-color:DarkOrange;color:white">' .
                $this->l('Wait order accept') . '</span>';
        } elseif ($value == 'SEND ERROR') {
            return '<span class="color_field badge" style="background-color:DarkRed;color:white">' .
                $this->l('Send error') . '</span>';
        } elseif ($value == 'CANCEL') {
            return '<span class="color_field badge" style="background-color:DarkGrey;color:white">' .
                $this->l('Canceled') . '</span>';
        } elseif ($value == 'CREATED') {
            if (isset($gift_card_order['used_order']) && (int) $gift_card_order['used_order'] > 0) {
                return '<span class="color_field badge" style="background-color:LimeGreen;color:white">' .
                    sprintf($this->l('Used in order #%s'), (int) $gift_card_order['used_order']) . '</span>
						<a href="' . $this->context->link->getAdminLink('AdminOrders') . '&id_order=' .
                    $gift_card_order['used_order'] . '&vieworder">
						<img src="../img/admin/details.gif" alt="' . $this->l('View') . '"  width="16px" height="17px"></a>';
            } else {
                return '<span class="color_field badge" style="background-color:BlueViolet;color:white">' .
                    $this->l('Not Used') . '</span>';
            }
        }

        return '';
    }

    public function printInfo($value, $gift_card_order)
    {
        if (!empty($value) && trim($value) != '') {
            return '<ul class="info_message">' . $value . '</ul>';
        }

        return '';
    }

    public function printSended($value, $gift_card_order)
    {
        if (isset($value) && trim($value) != '' && (int) $value == 1) {
            $html = '<img src="../modules/giftcard/views/img/email_ok.gif" alt="' .
                $this->l('Email Sended') . '"  title="' . $this->l('Email Sended') . '" width="46px" height="46px"/>';

            return $html;
        } elseif (isset($gift_card_order['receptmode'])
            && trim($gift_card_order['receptmode']) != ''
            && (int) $gift_card_order['receptmode'] == 2) {
            $html = '<img src="../modules/giftcard/views/img/delivery_mode.png" alt="' .
                $this->l('Send by post') . '"  title="' . $this->l('Send by post') . '" width="46px" height="46px"/>';

            return $html;
        }

        return '<img src="../modules/giftcard/views/img/email_wait.gif" alt="' .
            $this->l('The email is send') . '" title="' .
            $this->l('Email no send wait delivery date') . '" width="46px" height="46px"/>';
    }

    public function printImage($value, $gift_cart_template)
    {
        $id = (int) $gift_cart_template['id_gift_card_template'];
        $template_path = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/' . (int) $id . '/';
        $id_lang = (int) $this->context->language->id;
        $template_file_path = $template_path . (int) $id . '.jpg';
        $template_file_lang_path = $template_path . (int) $id . '-' . $id_lang . '.jpg';
        if ((int) $gift_cart_template['id_gift_card_template'] > 0 && file_exists($template_file_lang_path)) {
            $image = ImageManager::thumbnail(
                $template_file_lang_path,
                'giftcardtemplatemini_' . (int) $id . '-' . $id_lang . '.jpg',
                100,
                'jpg'
            );

            return $image;
        } elseif ((int) $gift_cart_template['id_gift_card_template'] > 0 && file_exists($template_file_path)) {
            $image = ImageManager::thumbnail(
                $template_file_path,
                'giftcardtemplatemini_' . (int) $id . '.jpg',
                100,
                'jpg'
            );

            return $image;
        }

        return '';
    }

    public function printGCOAction($value, $gift_card_order)
    {
        if ((int) $gift_card_order['receptmode'] < 2 && ((int) $gift_card_order['sended'] || $gift_card_order['status'] == 'CREATED')) {
            return '<a class="btn btn-default" href="index.php?tab=AdminGiftCardOrder&id_gift_card_order=' .
                (int) $gift_card_order['id_gift_card_order'] .
                '&sendEmail&token=' . Tools::getAdminTokenLite('AdminGiftCardOrder') . '">
			<i class="icon-mail"></i> Send email</a>';
        }
    }

    public function processSendEmail()
    {
        $id_gift_card_order = (int) Tools::getValue('id_gift_card_order', 0);
        if ($id_gift_card_order) {
            $gift_card_order = new GiftCardOrder((int) $id_gift_card_order);
            if ($gift_card_order->sended || $gift_card_order->status == 'CREATED') {
                $gift_card_order->sendingMail(null, true);
                $gift_card_order->update();
            }
        }
    }

    private function getAdminOrderLink($idOrder)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<') === true) {
            return $this->context->link->getAdminLink(
                'AdminOrders'
            ) . '&id_order=' . (int) $idOrder . '&vieworder';
        }

        return $this->context->link->getAdminLink(
            'AdminOrders',
            true,
            [
                'id_order' => (int) $idOrder,
                'vieworder' => 1,
            ]
        );
    }

    public function processGeneratePdf()
    {
        if (Tools::isSubmit('id_gift_card_order')) {
            $gift_card_order = new GiftCardOrder((int) Tools::getValue('id_gift_card_order'));
            $gift_card_order->generatePDF(true);
        } else {
            exit(Tools::displayError('The gift card order ID is missing.'));
        }
    }
}
