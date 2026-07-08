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

function upgrade_module_1_5_2($module)
{
    $module->updatePosition(Hook::getIdByName('header'), 0, 1);

    $cookie = [
        'active' => 1,
        'name' => 'cookiesplus',
        'provider' => Tools::getShopDomainSsl(true),
        'provider_url' => '',
        'purpose' => [
            'en' => 'Stores your cookie preferences.',
            'es' => 'Almacena las preferencias sobre cookies.',
            'ag' => 'Almacena las preferencias sobre cookies.',
            'cb' => 'Almacena las preferencias sobre cookies.',
            'mx' => 'Almacena las preferencias sobre cookies.',
            'fr' => 'Conserver vos préférences en matière de cookies.',
            'qc' => 'Conserver vos préférences en matière de cookies.',
            'pl' => 'Zapamiętuje preferencje dotyczące plików cookie.',
            'ro' => 'Reține preferințele dvs. legate de modulele cookie.',
            'pt' => 'Guarda as suas preferências quanto aos cookies.',
            'br' => 'Guarda as suas preferências quanto aos cookies.',
            'sk' => 'ukladá vaše preferencie týkajúce sa súborov cookie.',
            'nl' => 'Slaat uw cookie-voorkeuren op.',
            'de' => 'Speichert Ihre Cookie-Einstellungen.',
            'gr' => 'Αποθηκεύει τις προτιμήσεις σας για τα cookies.',
            'it' => 'Ricorda le tue preferenze in fatto di cookie.',
            'si' => 'shranjuje vaše nastavitve piškotkov..',
            'da' => 'Gemmer dine cookiepræferencer.',
            'no' => 'Lagrer informasjonskapselpreferansene dine.',
            'cs' => 'ukládá vaše preference týkající se cookies.',
            'hu' => 'A cookie-kkal kapcsolatos beállításokat tárolja.',
            'sv' => 'Sparar dina inställningar för kakor.',
        ],
        'expiry' => [
            'en' => '1 year',
            'es' => '1 año',
            'ag' => '1 año',
            'cb' => '1 año',
            'mx' => '1 año',
            'fr' => '1 année',
            'qc' => '1 année',
            'pl' => '1 rok',
            'ro' => '1 an',
            'pt' => '1 ano',
            'br' => '1 ano',
            'sk' => '1 rok',
            'nl' => '1 jaar',
            'de' => '1 Jahr',
            'gr' => '1 χρόνος',
            'it' => '1 anno',
            'si' => '1 leto',
            'da' => '1 år',
            'no' => '1 år',
            'cs' => '1 rok',
            'hu' => '1 év',
            'sv' => '1 år',
        ],
    ];

    $shops = Shop::getShops(false, null, true);
    $languages = Language::getLanguages(false);
    foreach ($shops as $shop) {
        $query = 'SELECT *
            FROM ' . _DB_PREFIX_ . 'cookiesplus_finality cf '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'cookiesplus_finality_lang cfl on cf.`id_cookiesplus_finality` = cfl.`id_cookiesplus_finality`
            WHERE
                cfl.`id_lang` = ' . Configuration::get('PS_LANG_DEFAULT') .
                ' AND cf.`technical` = 1
                AND cf.`id_shop` = ' . $shop . '
            ORDER BY `position`;'
        ;
        $result = Db::getInstance()->getRow($query);

        if (!$result) {
            continue;
        }

        $cookiesPlusCookie = new CookiesPlusCookie();
        $cookiesPlusCookie->id_shop = $shop;
        $cookiesPlusCookie->id_cookiesplus_finality = $result['id_cookiesplus_finality'];
        $cookiesPlusCookie->active = $cookie['active'];

        $cookiesPlusCookie->name = $cookie['name'];
        $cookiesPlusCookie->provider = isset($cookie['provider']) ? $cookie['provider'] : '';
        $cookiesPlusCookie->provider_url = isset($cookie['provider_url']) ? $cookie['provider_url'] : '';

        foreach ($languages as $lang) {
            $languageCode = strtok($lang['language_code'], '-');

            if (isset($cookie['purpose']['en'])) {
                $cookiesPlusCookie->purpose[$lang['id_lang']] = (isset($cookie['purpose'][$languageCode]) && $cookie['purpose'][$languageCode]) ? $cookie['purpose'][$languageCode] : $cookie['purpose']['en'];
            }

            if (isset($cookie['expiry']['en'])) {
                $cookiesPlusCookie->expiry[$lang['id_lang']] = (isset($cookie['expiry'][$languageCode]) && $cookie['expiry'][$languageCode]) ? $cookie['expiry'][$languageCode] : $cookie['expiry']['en'];
            }
        }

        $cookiesPlusCookie->save();
    }

    return true;
}
