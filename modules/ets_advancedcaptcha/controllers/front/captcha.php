<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

class Ets_advancedcaptchaCaptchaModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->imageCaptcha();
    }

    /**
     * @throws \Random\RandomException
     */
    public function imageCaptcha()
    {
        if (headers_sent())
            return;
        ob_start();
        $random_bytes = openssl_random_pseudo_bytes(4); // 4 bytes = 32 bits
        $random_int = hexdec(bin2hex($random_bytes));
        $security_code = Tools::substr(hash('sha256', $random_int), 17, 6);
        if (($posTo = Tools::getValue('pos', false)) && Validate::isCleanHtml($posTo)) {
            $captcha = Ets_advancedcaptcha::PREFIX_CODE . $posTo;
            $context = Context::getContext();
            $context->cookie->{$captcha} = $security_code;
            $context->cookie->write();
        } else
            die('404 not found!');

        if (Configuration::get('PA_CAPTCHA_TYPE') == 'basic') {
            $width = 100;
            $height = 30;
            $image = imagecreate($width, $height);
            $black = imagecolorallocate($image, 0, 0, 0);
            $noise_color = imagecolorallocate($image, 150, 200, 220);
            $background_color = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $background_color);
            for ($i = 0; $i < ($width * $height) / 3; $i++) {
                imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $noise_color);
            }
            for ($i = 0; $i < ($width * $height) / 150; $i++) {
                imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise_color);
            }
            imagestring($image, 5, 30, 6, $security_code, $black);
        } elseif (Configuration::get('PA_CAPTCHA_TYPE') == 'complex') {
            $this->captchaComplex($security_code, '#011C6C', '#ffffff', 120, 40, 10, 25);
        } elseif (Configuration::get('PA_CAPTCHA_TYPE') == 'colorful') {
            $image = imagecreatetruecolor(150, 35);
            $width = imagesx($image);
            $height = imagesy($image);
            $black = imagecolorallocate($image, 0, 0, 0);
            $white = imagecolorallocate($image, 255, 255, 255);
            $red = imagecolorallocatealpha($image, 255, 0, 0, 75);
            $green = imagecolorallocatealpha($image, 0, 255, 0, 75);
            $blue = imagecolorallocatealpha($image, 0, 0, 255, 75);
            imagefilledrectangle($image, 0, 0, $width, $height, $white);
            imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $red);
            imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $green);
            imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $blue);
            imagefilledrectangle($image, 0, 0, $width, 0, $black);
            imagefilledrectangle($image, $width - 1, 0, $width - 1, $height - 1, $black);
            imagefilledrectangle($image, 0, 0, 0, $height - 1, $black);
            imagefilledrectangle($image, 0, $height - 1, $width, $height - 1, $black);
            imagestring($image, 10, (int)(($width - (Tools::strlen($security_code) * 9)) / 2), (int)(($height - 15) / 2), $security_code, $black);
        }
        header("Content-Type: image/jpeg");
        if(isset($image))
        {
            ImageJpeg($image);
            ImageDestroy($image);
        }
        ob_end_flush();
        exit();
    }

    public function captchaComplex($text, $textColor, $backgroundColor, $imgWidth, $imgHeight, $noiceLines = 0, $noiceDots = 0, $noiceColor = '#162453')
    {
        $font = dirname(__FILE__) . '/../../views/fonts/monofont.ttf';
        $textColor = $this->hexToRGB($textColor);
        $fontSize = $imgHeight * 0.75;

        $im = imagecreatetruecolor($imgWidth, $imgHeight);
        $textColor = imagecolorallocate($im, $textColor['r'], $textColor['g'], $textColor['b']);

        $backgroundColor = $this->hexToRGB($backgroundColor);
        $backgroundColor = imagecolorallocate($im, $backgroundColor['r'], $backgroundColor['g'], $backgroundColor['b']);

        if ($noiceLines > 0) {
            $noiceColor = $this->hexToRGB($noiceColor);
            $noiceColor = imagecolorallocate($im, $noiceColor['r'], $noiceColor['g'], $noiceColor['b']);
            for ($i = 0; $i < $noiceLines; $i++) {
                imageline($im, mt_rand(0, $imgWidth), mt_rand(0, $imgHeight),
                    mt_rand(0, $imgWidth), mt_rand(0, $imgHeight), $noiceColor);
            }
        }

        if ($noiceDots > 0) {
            for ($i = 0; $i < $noiceDots; $i++) {
                imagefilledellipse($im, mt_rand(0, $imgWidth),
                    mt_rand(0, $imgHeight), 3, 3, $textColor);
            }
        }

        imagefill($im, 0, 0, $backgroundColor);
        list($x, $y) = $this->imageTTFCenter($im, $text, $font, $fontSize);
        imagettftext($im, $fontSize, 0, $x, $y, $textColor, $font, $text);

        imagejpeg($im, NULL, 90);
        header('Content-Type: image/jpeg');
        imagedestroy($im);
        ob_end_flush();
        exit();
    }

    protected function hexToRGB($colour)
    {
        if ($colour[0] == '#') {
            $colour = Tools::substr($colour, 1);
        }
        if (Tools::strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (Tools::strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('r' => $r, 'g' => $g, 'b' => $b);
    }

    protected function imageTTFCenter($image, $text, $font, $size, $angle = 8)
    {
        $xi = imagesx($image);
        $yi = imagesy($image);
        $box = imagettfbbox($size, $angle, $font, $text);
        $xr = abs(max($box[2], $box[4])) + 5;
        $yr = abs(max($box[5], $box[7]));
        $x = (int)(($xi - $xr) / 2);
        $y = (int)(($yi + $yr) / 2);
        return array($x, $y);
    }

}