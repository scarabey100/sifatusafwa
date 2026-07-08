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

class CookiesPlusFrontModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('saveCookiesPlusPreferences')) {
            $data = $this->module->saveCookiesPlusPreferences();

            // Save stats
            $cookiesAccepted = false;
            $cookiesRefused = false;
            $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
            foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
                if ($cookiesPlusFinality['technical']) {
                    continue;
                }

                if (Tools::getValue('cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']) == 'on') {
                    $cookiesAccepted = true;
                } elseif (Tools::getValue('cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']) == 'off') {
                    $cookiesRefused = true;
                }
            }

            // 1 = All accepted / Default
            // 2 = All refused
            // 3 = Configure
            $action = 1;
            if (!$cookiesAccepted && $cookiesRefused) {
                $action = 2;
            } elseif ($cookiesAccepted && $cookiesRefused) {
                $action = 3;
            }

            $query = 'INSERT INTO ' . _DB_PREFIX_ . 'cookiesplus_stats(action, date_add) VALUES (' . $action . ', "' . date('Y-m-d H:i:s') . '")';

            Db::getInstance()->execute($query);

            if (Tools::getValue('ajax')) {
                echo json_encode($data);
                exit;
            }
        }

        if (Tools::isSubmit('getPdf')) {
            $data = json_decode(CookiesPlusUserConsent::getCookiesPlusUserConsentDataByHash(Tools::getValue('hash')), true);

            if (!$data) {
                return Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
            }

            $pdf = new PDF($data, 'CookiesPlusModule', Context::getContext()->smarty);

            // Remove embedded fonts to minimize space
            $pdf->pdf_renderer->font_by_lang = array_merge($pdf->pdf_renderer->font_by_lang, [
                'dz' => '',
                'ar' => '',
                'au' => '',
                'at' => '',
                'az' => '',
                'bd' => '',
                'by' => '',
                'be' => '',
                'bo' => '',
                'ba' => '',
                'br' => '',
                'bg' => '',
                'cm' => '',
                'ca' => '',
                'cl' => '',
                'cn' => '',
                'co' => '',
                'cr' => '',
                'hr' => '',
                'cy' => '',
                'cz' => '',
                'dk' => '',
                'do' => '',
                'ec' => '',
                'eg' => '',
                'sv' => '',
                'ee' => '',
                'fj' => '',
                'fi' => '',
                'fr' => '',
                'ge' => '',
                'de' => '',
                'gr' => '',
                'gt' => '',
                'gy' => '',
                'hk' => '',
                'hu' => '',
                'in' => '',
                'id' => '',
                'ir' => '',
                'ie' => '',
                'il' => '',
                'it' => '',
                'ci' => '',
                'jp' => '',
                'je' => '',
                'ke' => '',
                'lv' => '',
                'li' => '',
                'lt' => '',
                'lu' => '',
                'mg' => '',
                'my' => '',
                'mt' => '',
                'mx' => '',
                'md' => '',
                'ma' => '',
                'nl' => '',
                'nz' => '',
                'ng' => '',
                'no' => '',
                'pk' => '',
                'pa' => '',
                'py' => '',
                'pe' => '',
                'ph' => '',
                'pl' => '',
                'pt' => '',
                'ro' => 'dejavusans',
                'ru' => '',
                'sa' => '',
                'sn' => '',
                'rs' => '',
                'sg' => '',
                'sk' => '',
                'si' => '',
                'za' => '',
                'kr' => '',
                'es' => '',
                'se' => '',
                'ch' => '',
                'tw' => '',
                'tz' => '',
                'th' => '',
                'tn' => '',
                'tr' => '',
                'ua' => '',
                'ae' => '',
                'gb' => '',
                'us' => '',
                'uy' => '',
                've' => '',
                'vn' => '',
            ]);

            $pdf->pdf_renderer->DEFAULT_FONT = '';

            ob_clean();

            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . Tools::getValue('hash') . '.pdf"');
            echo $pdf->render('S');

            exit;
        }

        return Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
    }
}
