<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

// https://josetxu.com/captcha-en-php/

class StockAlertCaptchaModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        // Send noindex to avoid ghost carts by bots
        header('X-Robots-Tag: noindex', true);
    }

    public function initContent()
    {
        //iniciamos sesion
        session_start();

        //numero de caracteres para el captcha
        $captchaTextSize = 5;

        //haz esto...
        do {
            //creamos la variable $md5Hash que sera una cadena aleatoria
            $md5Hash = md5(microtime(true) * time());
            //array para excluir caracteres
            $xChar = array("0", "1", "o", "O", "l");
            //remplazar caracteres excluidos
            $md5Hash = str_replace($xChar, "", $md5Hash);
        } while (strlen($md5Hash) < $captchaTextSize); //...mientras se cumpla esto

        //creamos la variable $captchaKey del tamaño definido en $captchaTextSize
        $captchaKey = substr($md5Hash, 0, $captchaTextSize);

        //creamos variable de sesion codificando $captchaKey
        $_SESSION['captchaKey'] = md5($captchaKey);
$logger = new \FileLogger(0);
if (version_compare(_PS_VERSION_, '1.7', '<')) {
    $logger->setFilename(_PS_ROOT_DIR_.'/log/idnovate.log');
} else {
    $logger->setFilename(_PS_ROOT_DIR_.'/var/logs/idnovate.log');
}
$logger->logDebug($_SESSION['captchaKey']);
$logger->logDebug($captchaKey);
        //seleccionamos imagen para el captcha
        $captchaImage = imagecreatefrompng(_PS_MODULE_DIR_ . $this->module->name . "/views/img/bg_captcha.png");

        //color del texto
        $textColor = imagecolorallocate($captchaImage, 255, 255, 255);

        //color de lineas aleatorias
        $lineColor = imagecolorallocate($captchaImage, 80, 80, 80);

        //ancho de lineas aleatorias
        imagesetthickness($captchaImage, 1);

        //tamaño de imagen
        $imageInfo = getimagesize(_PS_MODULE_DIR_ . $this->module->name . "/views/img/bg_captcha.png");

        //numero de lineas aleatorias
        $linesToDraw = rand(5, 8);

        //para cada linea aleatoria...
        for ($i = 0; $i < $linesToDraw; $i++) {
            //punto inicial
            $xStart = mt_rand(0, $imageInfo[0]);
            //punto final
            $xEnd = mt_rand(0, $imageInfo[0]);
            //pintamos la linea
            imageline($captchaImage, $xStart, 0, $xEnd, $imageInfo[1], $lineColor);
        }

        //pintamos el codigo en la imagen
        imagettftext($captchaImage, 20, 0, 10, 30, $textColor, _PS_MODULE_DIR_ . $this->module->name . "/views/fonts/VeraBd.ttf", $captchaKey);

        //encabezados para enviar como imagen
        header("Content-type: image/png");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Fri, 19 Jan 1994 05:00:00 GMT");
        header("Pragma: no-cache");

        //imagen captcha final
        imagepng($captchaImage);
    }
}
