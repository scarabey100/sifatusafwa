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
if (function_exists('set_time_limit')) {
    @set_time_limit(1200);
}

class GiftCardTools
{
    /**
     * This function permit to fixed the price if currency rate change
     */
    public static function addFixedPrice($id_product, $id_currency, $price)
    {
        $specific_price = new SpecificPrice();
        $specific_price->id_shop = 0;
        $specific_price->id_country = 0;
        $specific_price->id_group = 0;
        $specific_price->id_customer = 0;
        $specific_price->id_product = $id_product;
        $specific_price->id_product_attribute = 0;
        $specific_price->id_currency = $id_currency;
        $specific_price->price = $price;
        $specific_price->from_quantity = 0;
        $specific_price->reduction = 0;
        $specific_price->reduction_type = 'amount';
        $specific_price->from = '0000-00-00 00:00:00';
        $specific_price->to = '0000-00-00 00:00:00';

        return $specific_price->add();
    }

    /**
     * @param string $gift_card_template
     *
     * @return bool multitype:unknown
     */
    public static function getAvailableVars($gift_card_template = null)
    {
        $availablevars = [];
        $id_gift_card_template = 0;
        if (!is_object($gift_card_template)) {
            if (is_array($gift_card_template) && isset($gift_card_template['id_gift_card_template'])) {
                $id_gift_card_template = $gift_card_template['id_gift_card_template'];
            } elseif ((int) $gift_card_template) {
                $id_gift_card_template = (int) $gift_card_template;
            }
        }
        if ((int) $id_gift_card_template > 0) {
            $gift_card_template = new GiftCardTemplate($id_gift_card_template);
        } elseif (!is_object($gift_card_template)) {
            return false;
        }
        $template_file_path = $gift_card_template->img_dir . $gift_card_template->id . '/' . $gift_card_template->id . '.svg';
        if (!file_exists($template_file_path)) {
            return false;
        }
        $xml = simplexml_load_string(Tools::file_get_contents($template_file_path));
        if ($text = self::getTextAvailable($xml, 'giftcard_price')) {
            $availablevars['giftcard_price'] = $text;
        }
        if ($text = self::getTextAvailable($xml, 'giftcard_code')) {
            $availablevars['giftcard_code'] = $text;
        }
        if ($text = self::getTextAvailable($xml, 'giftcard_from')) {
            $availablevars['giftcard_from'] = $text;
        }
        if ($text = self::getTextAvailable($xml, 'giftcard_lastname')) {
            $availablevars['giftcard_lastname'] = $text;
        }
        if ($text = self::getTextAvailable($xml, 'giftcard_message', true)) {
            $availablevars['giftcard_message'] = $text;
        }
        if ($text = self::getTextAvailable($xml, 'giftcard_expirate')) {
            $availablevars['giftcard_expirate'] = $text;
        }
        for ($i = 1; $i <= 10; ++$i) {
            if ($text = self::getTextAvailable($xml, 'var_text' . $i)) {
                $availablevars['var_text' . $i] = $text;
            }
        }
        for ($i = 1; $i <= 10; ++$i) {
            if ($color = self::getColorAvailable($xml, 'var_color' . $i)) {
                $availablevars['var_color' . $i] = $color;
            }
        }

        return $availablevars;
    }

    public static function getTextAvailable($xml, $id, $flowlines = false)
    {
        if ($flowlines) {
            $text = '';
            $svg_text_arr = $xml->xpath('//svg:flowRoot[@id="' . $id . '"]');
            if (isset($svg_text_arr[0]->flowPara) && count((array) $svg_text_arr[0]->flowPara) > 0) {
                foreach (((array) $svg_text_arr[0]->flowPara) as $flowPara) {
                    if (!is_array($flowPara)) {
                        $text .= (string) $flowPara . chr(13) . chr(10);
                    }
                }
                if (!empty($text)) {
                    $text = Tools::substr($text, 0, -3);
                }

                return $text;
            }
            $svg_text_arr = $xml->xpath('//svg:text[@id="' . $id . '"]');
            if (isset($svg_text_arr[0]->tspan) && count((array) $svg_text_arr[0]->tspan) > 0) {
                foreach (((array) $svg_text_arr[0]->tspan) as $tspan) {
                    if (!is_array($tspan)) {
                        $text .= (string) $tspan . chr(13) . chr(10);
                    }
                }
                if (!empty($text)) {
                    $text = Tools::substr($text, 0, -3);
                }

                return $text;
            }
        }
        $svg_text_arr = $xml->xpath('//svg:tspan[@id="' . $id . '"]');
        if (isset($svg_text_arr) && is_array($svg_text_arr) && count($svg_text_arr) > 0) {
            return (string) $svg_text_arr[0];
        }
        $svg_text_arr = $xml->xpath('//svg:text[@id="' . $id . '"]');
        if (isset($svg_text_arr) && is_array($svg_text_arr) && count($svg_text_arr) > 0) {
            if (isset($svg_text_arr[0]->tspan)) {
                return (string) $svg_text_arr[0]->tspan;
            } else {
                return (string) $svg_text_arr[0];
            }
        }

        return false;
    }

    public static function getColorAvailable($xml, $id)
    {
        $svg_arr = $xml->xpath('//svg:path[@id="' . $id . '"] | //svg:g[@id="' . $id . '"] | //svg:rect[@id="' . $id . '"]');
        if (isset($svg_arr) && is_array($svg_arr) && count($svg_arr) > 0) {
            $svg_color_attributes = $svg_arr[0]->attributes();
            if (isset($svg_color_attributes) && isset($svg_color_attributes['style'])) {
                $style = $svg_color_attributes['style'];
                $fillcolorpatern = '/fill:(#.{6})/';
                $resultmatch = [];
                preg_match($fillcolorpatern, $style, $resultmatch);
                if (isset($resultmatch[1])) {
                    return $resultmatch[1];
                }
            }
        }

        return false;
    }

    /**
     * @param string $gift_card_template
     * @param unknown $params
     * @param number $id_lang
     *
     * @return bool
     */
    public static function buildTemplateSvgV2($gift_card_template = null, $params = [], $id_lang = 0)
    {
        if ($id_lang == 0) {
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        $id_gift_card_template = 0;
        if (!is_object($gift_card_template)) {
            if (is_array($gift_card_template) && isset($gift_card_template['id_gift_card_template'])) {
                $id_gift_card_template = $gift_card_template['id_gift_card_template'];
            } elseif ((int) $gift_card_template) {
                $id_gift_card_template = (int) $gift_card_template;
            }
        }
        if ((int) $id_gift_card_template > 0) {
            $gift_card_template = new GiftCardTemplate($id_gift_card_template);
        } elseif (!is_object($gift_card_template)) {
            return false;
        }
        /* Load svg to xml */
        $template_file_path = $gift_card_template->img_dir . $gift_card_template->id . '/' . $gift_card_template->id . '.svg';
        if (!file_exists($template_file_path)) {
            return false;
        }
        /* Initialisation des variables */
        $price = 0;
        if (isset($params['price']) && !empty($params['price'])) {
            $price = $params['price'];
        } else {
            $price = round((float) $gift_card_template->var_price_default);
        }
        $code = '';
        if (isset($params['code']) && !empty($params['code'])) {
            $code = $params['code'];
        } else {
            $code = $gift_card_template->var_code_default;
        }
        $from = '';
        if (isset($params['from']) && !empty($params['from'])) {
            $from = $params['from'];
        } else {
            $from = $gift_card_template->var_from_default;
        }
        $message = '';
        if (isset($params['message']) && !empty($params['message'])) {
            $message = $params['message'];
        } else {
            $message = $gift_card_template->var_message_default;
        }
        if (!empty($message) && version_compare(_PS_VERSION_, '1.5.6', '<') === true) {
            $message = str_replace('\\\'', '\'', $message);
        }
        $expirate = '';
        if (isset($params['expirate']) && !empty($params['expirate'])) {
            $expirate = $params['expirate'];
        } else {
            $expirate = $gift_card_template->var_expirate_default;
        }
        $lastname = '';
        if (isset($params['lastname']) && !empty($params['lastname'])) {
            $lastname = $params['lastname'];
        } else {
            $lastname = $gift_card_template->var_lastname_default;
        }
        $xml = simplexml_load_string(Tools::file_get_contents($template_file_path));
        /* Update price */
        $svgpricearr = $xml->xpath('//svg:tspan[@id="giftcard_price"] | //svg:text[@id="giftcard_price"]');
        if (isset($svgpricearr) && is_array($svgpricearr) && count($svgpricearr) > 0) {
            if ($price > 0) {
                $svgpricearr[0][0] = $price;
            } else {
                $svgpricearr[0][0] = '';
            }
        }
        /* Update discount code */
        $svgcodearr = $xml->xpath('//svg:tspan[@id="giftcard_code"] | //svg:text[@id="giftcard_code"]');
        if (isset($svgcodearr) && is_array($svgcodearr) && count($svgcodearr) > 0) {
            $svgcodearr[0][0] = $code;
        }
        /* Update from */
        $svgfromarr = $xml->xpath('//svg:tspan[@id="giftcard_from"] | //svg:text[@id="giftcard_from"]');
        if (isset($svgfromarr) && is_array($svgfromarr) && count($svgfromarr) > 0) {
            $svgfromarr[0][0] = $from;
        }
        /* Update lastname */
        $svglastnamearr = $xml->xpath('//svg:tspan[@id="giftcard_lastname"] | //svg:text[@id="giftcard_lastname"]');
        if (isset($svglastnamearr) && is_array($svglastnamearr) && count($svglastnamearr) > 0) {
            $svglastnamearr[0][0] = $lastname;
        }
        /* Update message */
        $svgmessagearr = $xml->xpath('//svg:flowRoot[@id="giftcard_message"]');
        if (isset($svgmessagearr) && is_array($svgmessagearr) && count($svgmessagearr) > 0) {
            if (isset($svgmessagearr[0]->flowPara)) {
                unset($svgmessagearr[0]->flowPara);
            }
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $message) as $line) {
                $svgmessagearr[0]->addChild('flowPara', $line);
            }
        } else {
            $svgmessagearr = $xml->xpath('//svg:text[@id="giftcard_message"]');
            if (isset($svgmessagearr) && is_array($svgmessagearr) && count($svgmessagearr) > 0) {
                if (isset($svgmessagearr[0]->tspan) && count($svgmessagearr[0]->tspan) > 0) {
                    $x = (int) $svgmessagearr[0]->tspan[0]->attributes()->x;
                    $y = (int) $svgmessagearr[0]->tspan[0]->attributes()->y;
                    $y2 = (int) $svgmessagearr[0]->tspan[1]->attributes()->y;
                    $dy = $y2 - $y;
                    if ($dy < 0) {
                        $dy *= -1;
                    }
                    $cptline = 0;
                    unset($svgmessagearr[0]->tspan);
                    foreach (preg_split("/((\r?\n)|(\r\n?))/", $message) as $line) {
                        $tspanmessage = $svgmessagearr[0]->addChild('tspan', $line);
                        $tspanmessage->addAttribute('x', $x);
                        $tspanmessage->addAttribute('y', $y);
                        $y += $dy;
                        ++$cptline;
                    }
                } else {
                    $svgmessagearr[0][0] = $message;
                }
            } else {
                $svgmessagearr = $xml->xpath(
                    '//svg:tspan[@id="giftcard_message"] | //svg:text[@id="giftcard_message"]'
                );
                if (isset($svgmessagearr) && is_array($svgmessagearr) && count($svgmessagearr) > 0) {
                    $svgmessagearr[0][0] = $message;
                }
            }
        }

        /* Update expirate */
        $svgexpiratearr = $xml->xpath('//svg:tspan[@id="giftcard_expirate"] | //svg:text[@id="giftcard_expirate"]');
        if (isset($svgexpiratearr) && is_array($svgexpiratearr) && count($svgexpiratearr) > 0) {
            $svgexpiratearr[0][0] = $expirate;
        }
        /* Update colors */
        for ($i = 1; $i <= 10; ++$i) {
            $var = 'var_color' . $i;
            if (isset($gift_card_template->$var)) {
                self::replaceVarColor($xml, $var, $gift_card_template->$var);
            }
        }
        /* Update text */
        for ($i = 1; $i <= 10; ++$i) {
            $var = 'var_text' . $i;
            $textlangs = null;
            if (isset($gift_card_template->$var)) {
                $textlangs = $gift_card_template->$var;
            }
            if (isset($textlangs) && isset($textlangs[$id_lang])) {
                self::replaceVarText($xml, $var, $textlangs[$id_lang]);
            }
        }

        return $xml->asXml();
    }

    public static function replaceVarText($xml, $id, $value = '')
    {
        $svg_text_arr = $xml->xpath('//svg:tspan[@id="' . $id . '"]');
        if (isset($svg_text_arr) && is_array($svg_text_arr) && count($svg_text_arr) > 0) {
            $svg_text_arr[0][0] = $value;
        }
        $svg_text_arr = $xml->xpath('//svg:text[@id="' . $id . '"]');
        if (isset($svg_text_arr) && is_array($svg_text_arr) && count($svg_text_arr) > 0) {
            if (isset($svg_text_arr[0]->tspan)) {
                $svg_text_arr[0]->tspan = $value;
            } else {
                $svg_text_arr[0][0] = $value;
            }
        }
    }

    public static function replaceVarColor($xml, $id, $color = '')
    {
        $svg_color_arr = $xml->xpath('//svg:path[@id="' . $id . '"] | //svg:g[@id="' . $id . '"]');
        if (isset($svg_color_arr) && is_array($svg_color_arr) && count($svg_color_arr) > 0) {
            $svg_color_attributes = $svg_color_arr[0]->attributes();
            if (isset($svg_color_attributes) && isset($svg_color_attributes['style'])) {
                $oldstyle = (string) $svg_color_attributes['style'];
                $color = str_replace('#', '', $color);
                $newstyle = preg_replace('/fill:#(.{6})/', 'fill:#' . $color, $oldstyle);
                $svg_color_attributes['style'] = $newstyle;
            }
        }
    }

    /**
     * @param string $templatefile
     * @param number $price
     * @param string $discount_code
     * @param string $color
     * @param string $shop_text
     */
    public static function buildTemplateSvg(
        $templatefile,
        $price = 0,
        $discount_code = '',
        $color = '',
        $shop_text = ''
    ) {
        /* Load svg to xml */
        $template_dir = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/';
        $xml = simplexml_load_string(Tools::file_get_contents($template_dir . $templatefile));
        /* Update price */
        $svgpricearr = $xml->xpath('//svg:tspan[@id="giftcard_price"]');
        if (isset($svgpricearr) && is_array($svgpricearr) && count($svgpricearr) > 0) {
            $svgpricearr[0][0] = $price;
        }
        // $xml->xpath('//svg:tspan[@id="giftcard_price"]')[0]=$price;
        /* Update discount code */
        $svgcodearr = $xml->xpath('//svg:tspan[@id="giftcard_code"]');
        if (isset($svgcodearr) && is_array($svgcodearr) && count($svgcodearr) > 0) {
            $svgcodearr[0][0] = $discount_code;
        }
        /* Update text shop */
        $svgshoptextarr = $xml->xpath('//svg:tspan[@id="giftcard_shop_text"]');
        if (isset($svgshoptextarr) && is_array($svgshoptextarr) && count($svgshoptextarr) > 0) {
            $svgshoptextarr[0][0] = $shop_text;
        }
        /* Update backgroung color */
        $svg_bgc_arr = $xml->xpath('//svg:path[@id="giftcard_background"]');
        if (isset($svg_bgc_arr) && is_array($svg_bgc_arr) && count($svg_bgc_arr) > 0) {
            $svg_bgc_attributes = $svg_bgc_arr[0]->attributes();
            if (isset($svg_bgc_attributes) && isset($svg_bgc_attributes['style'])) {
                $oldstyle = $svg_bgc_attributes['style'];
                $color = str_replace('#', '', $color);
                $newstyle = preg_replace('/fill:#(.{6})/', 'fill:#' . $color, $oldstyle);
                $svg_bgc_attributes['style'] = $newstyle;
            }
        }

        return $xml->asXml();
    }

    public static function resizeImageWithTemplateExec(
        $svg,
        $file_dest,
        $ratio = 0,
        $width = null,
        $height = null,
        $format = 'jpg',
        $res = 100,
        $withres = true
    ) {
        $template_dir = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/';
        $template_path_temp = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/';
        if (file_exists($template_path_temp . 'preview.svg')) {
            unlink($template_path_temp . 'preview.svg');
        }
        if (file_exists($template_path_temp . 'preview.png')) {
            unlink($template_path_temp . 'preview.png');
        }
        if (file_put_contents($template_path_temp . 'preview.svg', $svg)) {
            // rsvg -w 1604 -h 1022 1.svg gc_test_1_1604x1022.png
            $width_n = $width * 2;
            $height_n = $height * 2;
            $width_n = 1400;
            $height_n = 891;
            // echo "rsvg -w ".$width_n." -h ".$height_n." ".$template_path_temp."preview.svg ".$file_dest;
            // @exec("rsvg-convert -w ".$width_n." -h ".$height_n." ".$template_path_temp."preview.svg > ".$file_dest,
            // $array
            // );
            /*$logger = new FileLogger(0); //0 == debug level, logDebug() won’t work without this.
            $logger->setFilename(_PS_ROOT_DIR_.'/modules/giftcard/logs/debugimagick.log');
            $logger->logDebug(
            "rsvg-convert -w ".$width_n." -h ".$height_n." > ".$template_path_temp."preview.svg
            ".$file_dest
            );
            $logger->logDebug(print_r($array, true));*/
        }

        return true;
    }

    /**
     * @param string $svg
     * @param string $file_dest
     * @param number $ratio
     * @param string $width
     * @param string $height
     * @param string $format
     *
     * @return bool
     */
    public static function resizeImageWithTemplate(
        $svg,
        $file_dest,
        $ratio = 0,
        $width = null,
        $height = null,
        $format = 'jpg',
        $res = 100,
        $withres = true
    ) {
        try {
            $im = new Imagick();
            $im->setBackgroundColor(new ImagickPixel('white'));
            // $im->setResolution(100, 100);
            if ((int) $ratio > 0) {
                $im->readImageBlob($svg);
                $ratio = (1600 / $im->width);
                $im->setResolution($ratio * 100, $ratio * 100);
                $im->readImageBlob($svg);
                $im->scaleImage(1600, 1200, true);
            } else {
                $im->readImageBlob($svg);
                if ($withres) {
                    $imageprops = $im->getImageGeometry();
                    $ratio = $width / $imageprops['width'];
                    if ($ratio < 1) {
                        $ratio = 1;
                    }
                    $im->setResolution($ratio * $res, $ratio * $res);
                }
                $im->readImageBlob($svg);
                $im->scaleImage($width, $height, true);
                $imageprops = $im->getImageGeometry();
                $borderheight = ($height - $imageprops['height']);
                if ($borderheight > 0) {
                    $im->borderImage('#FFFFFF', 0, (int) ($borderheight / 2));
                }
            }
            $im->setImageFormat($format);
            $im->writeImage($file_dest);
            $im->clear();
            $im->destroy();
        } catch (ImagickException $e) {
            return false;
        }

        return true;
    }

    public static function svgScaleHack($svg, $min_width, $min_height)
    {
        $re_w = '/(.*<svg[^>]* width=")([\d.]+px)(.*)/si';
        $re_h = '/(.*<svg[^>]* height=")([\d.]+px)(.*)/si';
        preg_match($re_w, $svg, $mw);
        preg_match($re_h, $svg, $mh);
        $width = (float) $mw[2];
        $height = (float) $mh[2];
        if (!$width || !$height) {
            return false;
        }
        // scale to make width and height big enough
        $scale = 1;
        if ($width < $min_width) {
            $scale = $min_width / $width;
        }
        if ($height < $min_height) {
            $scale = max($scale, $min_height / $height);
        }
        $width *= $scale * 2;
        $height *= $scale * 2;
        $svg = preg_replace($re_w, "\${1}{$width}px\${3}", $svg);
        $svg = preg_replace($re_h, "\${1}{$height}px\${3}", $svg);

        return $svg;
    }

    /**
     * @param array $params
     *                      id_shop,discountcode,price,id_currency,id_gift_card_order,template_color,template_shop_text,template_file,id_lang
     * @param string $fileoutput
     *
     * @return bool
     */
    public static function processGeneratePdfV2($gift_card_template, $params, $render = false, $filename = '')
    {
        if (isset($params) && is_array($params) && count($params) > 0) {
            $languagepdf = null;
            if ((int) $params['id_lang']) {
                $languagepdf = new Language((int) $params['id_lang']);
            } else {
                $languagepdf = Context::getContext()->language;
            }
            $pdfgenerator = new PDFGiftCard((bool) Configuration::get('PS_PDF_USE_CACHE'));
            $contentpdf = '';
            $contentpdf = Configuration::get('GIFTCARD_PDF_CONTENT', $params['id_lang']);
            $code = '';
            $price = 0;
            $template_color = '#ff6e27';
            $template_shop_text = '';
            $card_img_html = '';
            $card_price = 0;
            $product_name = '';
            $lastname = '';
            $date_to = '';
            $card_img_generated = false;
            $id_shop = 1;
            $from = '';
            $lastname = '';
            $message = '';
            $img_name = md5(uniqid(rand(), true)) . '.jpg';
            $tmp_file = _PS_ROOT_DIR_ . '/modules/giftcard/pdf/' . $img_name;
            if (isset($params['id_shop']) && !empty($params['id_shop'])) {
                $id_shop = (int) $params['id_shop'];
            }
            $shop = new Shop($id_shop);
            $shop_name = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $id_shop));
            $shop_url = new ShopUrl($id_shop);
            $shop = new Shop($id_shop);
            $shopbase_url = $shop_url->getURL();
            $logo_path = '';
            $width_logo = 0;
            $height_logo = 0;
            if (Configuration::get('PS_LOGO') !== false
                && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop))
                && is_file(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop))) {
                $logo_path = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop);
            } elseif (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop)
                && is_file(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop)))) {
                $logo_path = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop);
            } elseif (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $id_shop))
                && is_file(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop))) {
                $logo_path = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $id_shop);
            }
            if (!empty($logo_path) && is_file($logo_path)) {
                list($width_logo, $height_logo) = getimagesize($logo_path);
            }
            $id_gift_card_order = 0;
            $order_reference = '';
            if (isset($params['order_reference']) && !empty($params['order_reference'])) {
                $order_reference = $params['order_reference'];
            }
            if (isset($params['discountcode']) && !empty($params['discountcode'])) {
                $code = $params['discountcode'];
            }
            if (isset($params['product_name']) && !empty($params['product_name'])) {
                $product_name = $params['product_name'];
            }
            if (isset($params['price']) && !empty($params['price'])) {
                $price = (float) $params['price'];
                $card_price = round((float) $params['price']);
            }
            if (isset($params['id_gift_card_order']) && !empty($params['id_gift_card_order'])) {
                $id_gift_card_order = (int) $params['id_gift_card_order'];
            }
            if (isset($params['date_to']) && !empty($params['date_to'])) {
                $date_to = $params['date_to'];
            }
            if (isset($params['lastname']) && !empty($params['lastname'])) {
                $lastname = $params['lastname'];
            }
            if (isset($params['from']) && !empty($params['from'])) {
                $from = $params['from'];
            }
            if (isset($params['message']) && !empty($params['message'])) {
                $message = $params['message'];
            }
            if (!empty($message) && version_compare(_PS_VERSION_, '1.5.6', '<') === true) {
                $message = str_replace('\\\'', '\'', $message);
            }
            if (isset($params['id_currency']) && !empty($params['id_currency'])) {
                $id_currency = (int) $params['id_currency'];
            } else {
                $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            }
            $currency = new Currency($id_currency);
            $price_display = Tools::displayPrice((float) $price, $id_currency, false);
            $width = (int) Configuration::get('GIFTCARD_PDF_IMG_WIDTH');
            $height = (int) Configuration::get('GIFTCARD_PDF_IMG_HEIGHT');
            if (!((int) $width > 0)) {
                $width = 349;
            }
            if (!((int) $height > 0)) {
                $height = 195;
            }
            $ratio = 3;
            if ($gift_card_template->issvg) {
                $svgparams = [];
                $svgparams['price'] = $card_price;
                $svgparams['from'] = $from;
                $svgparams['lastname'] = $lastname;
                $svgparams['message'] = $message;
                $svgparams['product_name'] = $product_name;
                $svgparams['code'] = $code;
                $svgparams['expirate'] = $date_to;
                $svg = GiftCardTools::buildTemplateSvgV2($gift_card_template, $svgparams, (int) $params['id_lang']);
                $card_img_generated = GiftCardTools::resizeImageWithTemplate(
                    $svg,
                    $tmp_file,
                    $ratio,
                    $width * $ratio,
                    $height * $ratio,
                    'jpg',
                    200,
                    !$gift_card_template->pdf_image_only
                );
            } else {
                $template_path = $gift_card_template->img_dir . $gift_card_template->id . '/';
                $card_img_generated = self::imageResize(
                    $template_path . $gift_card_template->id . '.jpg',
                    $tmp_file,
                    $width * $ratio,
                    $height * $ratio
                );
            }
            if ($card_img_generated) {
                /**if (!isset($_SERVER['DOCUMENT_ROOT'])) {
                 * $card_img_html = '<img src="'.$tmp_file.'" width="'.$width.'px" height="'.$height.'px"/>';
                 * } else {
                 * $card_img_html = '<img src="'._PS_MODULE_DIR_.'giftcard/pdf/'.$img_name.
                        * '" width="'.$width.'px" height="'.$height.'px"/>';
                * }**/
                $ssl = (bool) Configuration::get('PS_SSL_ENABLED');
                $base = (($ssl) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
                $card_img_html = '<img src="' . $base . $shop->getBaseURI() . '/modules/giftcard/pdf/' . $img_name .
                    '" width="' . $width . 'px" height="' . $height . 'px"/>';
            }
            if ($gift_card_template->issvg && $gift_card_template->pdf_image_only) {
                $pdfgenerator->SetPrintHeader(false);
                $pdfgenerator->SetPrintFooter(false);
                // $pdfgenerator->AddPage('L');
                $pdfgenerator->AddPage('L');
                $bMargin = $pdfgenerator->getBreakMargin();
                $auto_page_break = $pdfgenerator->getAutoPageBreak();
                $pdfgenerator->setFontForLang($languagepdf->iso_code);
                $pdfgenerator->SetAutoPageBreak(false, 0);
                // $pdfgenerator->Image($tmp_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
                $pdfgenerator->Image($tmp_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
                $pdfgenerator->SetAutoPageBreak($auto_page_break, $bMargin);
                $pdfgenerator->setPageMark();
            } else {
                $contentpdf = str_replace('{$card_lastname}', $lastname, $contentpdf);
                $contentpdf = str_replace('{$card_from}', $from, $contentpdf);
                $contentpdf = str_replace('{$card_message}', $message, $contentpdf);
                $contentpdf = str_replace('{$shop_name}', $shop_name, $contentpdf);
                $contentpdf = str_replace('http://{$shop_link}', $shopbase_url, $contentpdf);
                $contentpdf = str_replace('{$shop_link}', $shopbase_url, $contentpdf);
                $contentpdf = str_replace('{$card_image}', $card_img_html, $contentpdf);
                $contentpdf = str_replace('{$product_name}', $product_name, $contentpdf);
                $contentpdf = str_replace('{$card_price}', $price_display, $contentpdf);
                $contentpdf = str_replace('{$card_code}', $code, $contentpdf);
                $contentpdf = str_replace('{$card_expirate}', $date_to, $contentpdf);
                $contentpdf = str_replace('http://{$logo_path}', $logo_path, $contentpdf);
                $contentpdf = str_replace('{$logo_path}', $logo_path, $contentpdf);
                $contentpdf = str_replace('{$width_logo}', $width_logo, $contentpdf);
                $contentpdf = str_replace('{$height_logo}', $height_logo, $contentpdf);
                $contentpdf = str_replace('{$order_reference}', $order_reference, $contentpdf);
                $pdfgenerator->setFontForLang($languagepdf->iso_code);
                $pdfgenerator->createContent($contentpdf);
                $pdfgenerator->SetPrintHeader(false);
                $pdfgenerator->SetPrintFooter(false);
                $pdfgenerator->writePage();
            }
            if ($card_img_generated) {
                @unlink($tmp_file);
            }
            if (ob_get_length() > 0) {
                @ob_end_clean();
            }
            if ($render) {
                $pdfgenerator->render($filename . '.pdf', true);
            }

            return $pdfgenerator->render($filename . '.pdf', false);
        }

        return false;
    }

    public static function buildcardtmp(
        $dest_file,
        $id_product,
        $width,
        $height,
        $use_custom,
        $card_price,
        $card_code,
        $template_file,
        $template_color,
        $template_shop_text
    ) {
        $card_img_generated = false;
        if ($use_custom) {
            $svg = self::buildTemplateSvg(
                $template_file,
                round((float) $card_price),
                $card_code,
                $template_color,
                $template_shop_text
            );
            GiftCardTools::resizeImageWithTemplate($svg, $dest_file, 0, $width * 4, $height * 4, 'jpg');
            $card_img_generated = true;
        }

        return $card_img_generated;
    }

    public static function getLangMail($id_lang)
    {
        $theme_path = _PS_THEME_DIR_;
        if ((int) $id_lang > 0) {
            $languagemail = new Language((int) $id_lang);
            if (!$languagemail || !(isset($languagemail->iso_code) && trim($languagemail->iso_code) != '')) {
                $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
                $languagemail = new Language((int) $id_lang);
            }
        } else {
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
            $languagemail = new Language((int) $id_lang);
        }
        if (!is_dir(_PS_ROOT_DIR_ . '/modules/giftcard/mails/' . Tools::strtolower($languagemail->iso_code))
            && !is_dir($theme_path . '/modules/giftcard/mails/' . Tools::strtolower($languagemail->iso_code))) {
            $id_lang = Language::getIdByIso('en');
            if (!$id_lang || !((int) $id_lang > 0)) {
                return false;
            }
        }

        return $id_lang;
    }

    public static function creatingDefaultTemplates($issvg = true)
    {
        $shopdefault = new Shop((int) Configuration::get('PS_SHOP_DEFAULT'));
        $shop_name = Tools::safeOutput(
            Configuration::get('PS_SHOP_NAME', null, null, (int) Configuration::get('PS_SHOP_DEFAULT'))
        );
        if (!Validate::isCatalogName($shop_name)) {
            $shop_name = '';
        }
        $tempate_datadefault_dir = _PS_ROOT_DIR_ . '/modules/giftcard/views/img/datadefault/template/';
        $xml = simplexml_load_string(Tools::file_get_contents($tempate_datadefault_dir . 'templates.xml'));
        $doc = new DOMDocument();
        $doc->load($tempate_datadefault_dir . 'templates.xml');
        $templatesdom = $doc->getElementsByTagName('template');
        // on prend seulement les langues actives
        $languages = Language::getLanguages(true);
        /* si langue différent de fr */
        /* LANGUE FR ET EN : fonctionnement nominal */
        $id_lang_fr = Language::getIdByIso('fr');
        $lang_fr_active = false;
        if (((int) $id_lang_fr) && $lang_fr = new Language($id_lang_fr)) {
            $lang_fr_active = true;
        }
        $id_lang_en = Language::getIdByIso('en');
        $lang_en_active = false;
        if (((int) $id_lang_en) && $lang_en = new Language($id_lang_fr)) {
            $lang_en_active = $lang_en->active;
        }
        $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
        /* Mode lang 1 : si pas de langue fr */
        $modedisplay = 0;
        if ($id_lang_en && (int) $id_lang_en > 0 && $id_lang_fr && (int) $id_lang_fr > 0) {
            $modedisplay = 1;
        }
        $langduplicateen = [];
        foreach ($languages as $language) {
            if (Tools::strtolower($language['iso_code'] != 'fr') && Tools::strtolower($language['iso_code'] != 'en')) {
                $langduplicateen[] = $language;
            }
        }
        foreach ($templatesdom as $templatedom) {
            $mode = $templatedom->getElementsByTagName('mode')->item(0)->nodeValue;
            $namedom = $templatedom->getElementsByTagName('name')->item(0);
            // on ecarte les images jpg fr si pas de langue francaise
            if ($mode == 'jpg' && $namedom->getElementsByTagName('fr')->length > 0 && !$lang_fr_active) {
                continue;
            }
            // on ecarte les images jpg en si pas de langue en mais langue fr existe
            // ignore template if mode not good
            // on écarte les svg
            if (Tools::strtolower($mode) != 'all' && (Tools::strtolower($mode) == 'svg' && !$issvg)) {
                continue;
            }
            $gift_card_template = new GiftCardTemplate();
            $image = $templatedom->getElementsByTagName('image')->item(0)->nodeValue;
            $isdefault = (int) $templatedom->getElementsByTagName('name')->item(0)->nodeValue;
            $gift_card_template->name = [];
            foreach ($languages as $language) {
                if ($namedom->getElementsByTagName($language['iso_code'])->length > 0) {
                    $gift_card_template->name[$language['id_lang']] = $namedom->getElementsByTagName(
                        $language['iso_code']
                    )->item(0)->nodeValue;
                } elseif ($namedom->getElementsByTagName('en')->length > 0) {
                    $gift_card_template->name[$language['id_lang']] = $namedom->getElementsByTagName(
                        'en'
                    )->item(0)->nodeValue;
                } elseif (Tools::strtolower($mode) != 'svg') {
                    $gift_card_template->name[$language['id_lang']] = $namedom->getElementsByTagName(
                        'fr'
                    )->item(0)->nodeValue;
                }
            }
            $gift_card_template->active = 1;
            $gift_card_template->physicaluse = 1;
            $gift_card_template->virtualuse = 1;
            // si ne contient pas la langue fr
            // la langue en sera assigné dans la langue défaut
            $gift_card_template->add();
            Db::getInstance()->delete(
                'giftcardtemplate_shop',
                '`id_gift_card_template` = ' . (int) $gift_card_template->id
            );
            $insertshop = [];
            foreach (Db::getInstance()->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop') as $row) {
                $insertshop[] = [
                    'id_gift_card_template' => (int) $gift_card_template->id,
                    'id_shop' => (int) $row['id_shop'],
                ];
            }
            Db::getInstance()->insert('giftcardtemplate_shop', $insertshop, false, true, Db::INSERT_IGNORE);
            /* UPDATE tag */
            $tagsdom = $templatedom->getElementsByTagName('tag');
            foreach ($tagsdom as $tagdom) {
                foreach ($languages as $language) {
                    if ($tagdom->getElementsByTagName($language['iso_code'])->length > 0) {
                        GiftCardTag::addTags(
                            $language['id_lang'],
                            (int) $gift_card_template->id,
                            $tagdom->getElementsByTagName($language['iso_code'])->item(0)->nodeValue
                        );
                    } elseif (Tools::strtolower($mode) == 'svg' && $tagdom->getElementsByTagName('en')->length > 0) {
                        GiftCardTag::addTags(
                            $language['id_lang'],
                            (int) $gift_card_template->id,
                            $tagdom->getElementsByTagName('en')->item(0)->nodeValue
                        );
                    }
                }
            }
            /*
             * Display all : si pas de langue fr
             */
            if ($lang_fr_active && Tools::strtolower($mode) == 'jpg') {
                if ($templatedom->getElementsByTagName('language')->length > 0) {
                    // mode selectif
                    if ($namedom->getElementsByTagName('fr')->length > 0) {
                        $gift_card_template->id_lang_display = $id_lang_fr;
                    } elseif ($namedom->getElementsByTagName('en')->length > 0 && $lang_en_active) {
                        $gift_card_template->id_lang_display = $id_lang_en;
                    }
                }
            }
            $gift_card_template->update();
            /* Update image */
            $gift_card_template->createImgFolder();
            $images_types = GiftCardTemplate::getImagesTypes();
            $template_path = $gift_card_template->img_dir . $gift_card_template->id . '/';
            if ($issvg && Tools::strtolower($mode) == 'svg') {
                $image_type_admin = [];
                $image_type_admin['name'] = '';
                $image_type_admin['width'] = 1600;
                $image_type_admin['height'] = 1052;
                $images_types[] = $image_type_admin;
                copy($tempate_datadefault_dir . $image . '.svg', $template_path . $gift_card_template->id . '.svg');
                $available_vars = GiftCardTools::getAvailableVars($gift_card_template);
                $gift_card_template->initCustomVar();
                $gift_card_template->updateCustomVar($available_vars);
                // init personnalize var
                $gift_card_template->issvg = true;
                /* Init custom field */
                for ($i = 1; $i <= 10; ++$i) {
                    $varcolor = 'var_color' . $i;
                    if ($templatedom->getElementsByTagName($varcolor)->length > 0) {
                        $gift_card_template->$varcolor = $templatedom->getElementsByTagName(
                            $varcolor
                        )->item(0)->nodeValue;
                    }
                }
                for ($i = 1; $i <= 10; ++$i) {
                    $vartext = 'var_text' . $i;
                    if ($templatedom->getElementsByTagName($vartext)->length > 0) {
                        $textdom = $templatedom->getElementsByTagName($vartext)->item(0);
                        $text_l = [];
                        foreach ($languages as $language) {
                            $id_lang = (int) $language['id_lang'];
                            if ($textdom->getElementsByTagName($language['iso_code'])->length > 0) {
                                $value = $textdom->getElementsByTagName($language['iso_code'])->item(0)->nodeValue;
                            } else {
                                $value = $textdom->getElementsByTagName('en')->item(0)->nodeValue;
                            }
                            $value = str_replace('{shop_name}', $shop_name, $value);
                            $text_l[$id_lang] = $value;
                        }
                        $gift_card_template->$vartext = $text_l;
                    }
                }
                $gift_card_template->update();
                $svg = [];
                foreach ($languages as $language) {
                    $svg[(int) $language['id_lang']] = GiftCardTools::buildTemplateSvgV2(
                        $gift_card_template,
                        [],
                        (int) $language['id_lang']
                    );
                }
                foreach ($images_types as $k => $image_type) {
                    foreach ($languages as $language) {
                        if (!GiftCardTools::resizeImageWithTemplate(
                            $svg[(int) $language['id_lang']],
                            $template_path . $gift_card_template->id .
                            (!empty($image_type['name']) ? '-' . Tools::stripslashes($image_type['name']) : '') .
                            '-' . $language['id_lang'] . '.jpg',
                            0,
                            $image_type['width'],
                            $image_type['height'],
                            'jpg'
                        )
                        ) {
                            echo Tools::displayError('An error occurred while copying image:') .
                                ' ' . Tools::stripslashes($image_type['name']);
                        }
                    }
                }
            } else {
                self::imageResize(
                    $tempate_datadefault_dir . $image,
                    $template_path . $gift_card_template->id . '.jpg'
                );
                foreach ($images_types as $k => $image_type) {
                    if (!self::imageResize(
                        $tempate_datadefault_dir . $image,
                        $template_path . $gift_card_template->id . '-' . Tools::stripslashes($image_type['name']) . '.jpg',
                        $image_type['width'],
                        $image_type['height']
                    )) {
                        echo Tools::displayError('An error occurred while copying image:') .
                            ' ' . Tools::stripslashes($image_type['name']);
                    }
                }
            }
            if (!$gift_card_template->isdefault && $isdefault) {
                $gift_card_template->changeToDefault();
            }
            // si mode jpg on duplique le modele en pour les autres langues que fr et en
            if ($lang_fr_active && Tools::strtolower($mode) == 'jpg'
                && $namedom->getElementsByTagName('en')->length > 0) {
                $gift_card_template_dup = $gift_card_template;
                foreach ($languages as $language) {
                    if (Tools::strtolower($language['iso_code']) != 'fr'
                        && Tools::strtolower($language['iso_code']) != 'en') {
                        $gift_card_template = GiftCardTemplate::duplicate((int) $gift_card_template_dup->id);
                        if ($gift_card_template) {
                            $gift_card_template->id_lang_display = (int) $language['id_lang'];
                            $gift_card_template->active = true;
                            $gift_card_template->deleteTags();
                            $tagsdom = $templatedom->getElementsByTagName('tag');
                            foreach ($tagsdom as $tagdom) {
                                if ($tagdom->getElementsByTagName('en')->length > 0) {
                                    GiftCardTag::addTags(
                                        $language['id_lang'],
                                        (int) $gift_card_template->id,
                                        $tagdom->getElementsByTagName('en')->item(0)->nodeValue
                                    );
                                }
                            }
                            $gift_card_template->update();
                        }
                    }
                }
                // On supprime la template précédent qui a été créé seulement pour modèle pour les autres languages
                if (!$lang_en_active && $gift_card_template_dup->id_lang_display == $id_lang_en) {
                    $gift_card_template_dup->delete();
                }
            }
        }
    }

    public static function creatingDefaultGiftCards()
    {
        $giftcard_datadefault_dir = _PS_ROOT_DIR_ . '/modules/giftcard/datadefault/giftcard/';
        $xml = simplexml_load_string(Tools::file_get_contents($giftcard_datadefault_dir . 'giftcards.xml'));
        $doc = new DOMDocument();
        $doc->load($giftcard_datadefault_dir . 'giftcards.xml');
        $giftcardsdom = $doc->getElementsByTagName('giftcard');
        $languages = Language::getLanguages(false);
        $currency_default = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        Context::getContext()->employee = new Employee(1);
        $currencies = Currency::getCurrencies();
        foreach ($giftcardsdom as $giftcarddom) {
            $gift_card = new GiftCardProduct();
            $namedom = $giftcarddom->getElementsByTagName('name')->item(0);
            $gift_card->name = [];
            foreach ($languages as $language) {
                if ($namedom->getElementsByTagName($language['iso_code'])->length > 0) {
                    $gift_card->name[$language['id_lang']] = $namedom->getElementsByTagName(
                        $language['iso_code']
                    )->item(0)->nodeValue;
                } else {
                    $gift_card->name[$language['id_lang']] = $namedom->getElementsByTagName(
                        'en'
                    )->item(0)->nodeValue;
                }
            }
            $period_val = (int) $giftcarddom->getElementsByTagName('period_val')->item(0)->nodeValue;
            $isdefaultgiftcard = (int) $giftcarddom->getElementsByTagName('isdefaultgiftcard')->item(0)->nodeValue;
            $amount = (float) (string) $giftcarddom->getElementsByTagName('amount')->item(0)->nodeValue;
            $quantity = (int) $giftcarddom->getElementsByTagName('quantity')->item(0)->nodeValue;
            $gift_card->period_val = $period_val;
            $gift_card->id_currency = 0;
            $gift_card->id_tax_rules_group = 0;
            $gift_card->price = $amount;
            $gift_card->amount = $amount;
            $gift_card->virtualcard = true;
            $gift_card->add();
            /*
             * if (version_compare(_PS_VERSION_, '1.6.0.8', '>') === true)
             * {
             * if (Configuration::get('PS_FORCE_ASM_NEW_PRODUCT') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
             * {
             * $gift_card->advanced_stock_management = 1;
             * StockAvailable::setProductDependsOnStock($gift_card->id, true, null, 0);
             * $gift_card->save();
             * }
             * }
             */
            StockAvailable::setQuantity($gift_card->id, 0, (int) $quantity);
            foreach ($currencies as $currencyitem) {
                if ((int) $currencyitem['id_currency'] != $currency_default) {
                    self::addFixedPrice($gift_card->id, (int) $currencyitem['id_currency'], (float) $amount);
                }
            }

            if (version_compare(_PS_VERSION_, '1.6.0.8', '>') === true) {
                if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT') != 0
                    && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                    $warehouse_location_entity = new WarehouseProductLocation();
                    $warehouse_location_entity->id_product = $gift_card->id;
                    $warehouse_location_entity->id_product_attribute = 0;
                    $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
                    $warehouse_location_entity->location = pSQL('');
                    $warehouse_location_entity->save();
                }
            }
            $gift_card_template = GiftCardTemplate::getDefault();
            $gift_card_template->generateProductImage($gift_card->id);
            if (!$gift_card->isdefaultgiftcard && $isdefaultgiftcard) {
                $gift_card->changeToDefault();
            }
        }
    }

    /**
     * GD image resize
     *
     * @param $source_path
     * @param $destination_path
     * @param $new_width
     * @param $new_height
     *
     * @return bool
     */
    public static function imageResize($source_path, $destination_path, $new_width = null, $new_height = null, $quality = 90)
    {
        // Vérifier si le fichier source existe
        if (!file_exists($source_path) || !filesize($source_path)) {
            return false;
        }

        // Récupérer les informations de l'image source
        list($width, $height, $type) = getimagesize($source_path);
        $rotate = 0;

        // Support pour la rotation basée sur les données EXIF pour JPEG
        if ($type == IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($source_path);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $rotate = 180;
                        break;
                    case 6:
                        $rotate = -90;
                        break;
                    case 8:
                        $rotate = 90;
                        break;
                }
            }
        }

        // Création de l'image source selon le type
        switch ($type) {
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($source_path);
                break;
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($source_path);
                break;
            case 18: // IMAGETYPE_WEBP
                $source_image = imagecreatefromwebp($source_path);
                break;
            default:
                return false; // Type non supporté
        }

        // Appliquer la rotation si nécessaire
        if ($rotate !== 0) {
            $source_image = imagerotate($source_image, $rotate, 0);
            // Recalculer les dimensions après rotation
            $width = imagesx($source_image);
            $height = imagesy($source_image);
        }

        // Définir les nouvelles dimensions si non spécifiées
        $new_width = $new_width ?: $width;
        $new_height = $new_height ?: $height;

        // Créer une nouvelle image vide
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Gestion de la transparence pour PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }

        // Copier et redimensionner l'ancienne image dans la nouvelle
        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Sauvegarder la nouvelle image
        switch ($type) {
            case IMAGETYPE_GIF:
                imagegif($new_image, $destination_path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($new_image, $destination_path, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $destination_path);
                break;
            case 18: // IMAGETYPE_WEBP
                imagewebp($new_image, $destination_path);
                break;
        }

        // Libérer la mémoire
        imagedestroy($source_image);
        imagedestroy($new_image);

        return true;
    }
}
