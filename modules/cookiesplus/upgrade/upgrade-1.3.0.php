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

function upgrade_module_1_3_0()
{
    include dirname(__FILE__) . '/../sql/install.php';

    $cookiesDefault = [];
    $cookiesDefault['cookie'][CookiesPlusFinality::NECESSARY_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::NECESSARY_COOKIE);
    $cookiesDefault['cookie'][CookiesPlusFinality::PREFERENCE_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::PREFERENCE_COOKIE);
    $cookiesDefault['cookie'][CookiesPlusFinality::STATISTIC_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::STATISTIC_COOKIE);
    $cookiesDefault['cookie'][CookiesPlusFinality::MARKETING_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::MARKETING_COOKIE);
    // $cookiesDefault['cookie'][CookiesPlusFinality::UNCLASSIFIED_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::UNCLASSIFIED_COOKIE);
    // $cookiesDefault['cookie'][CookiesPlusFinality::PERFORMANCE_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::PERFORMANCE_COOKIE);

    $shops = Shop::getShops(false, null, true);
    $languages = Language::getLanguages(false);

    foreach ($shops as $shop) {
        foreach ($cookiesDefault['cookie'] as $cookieDefault) {
            $cookiesPlusFinality = new CookiesPlusFinality();
            $cookiesPlusFinality->id_shop = $shop;
            $cookiesPlusFinality->technical = (isset($cookieDefault['technical']) && $cookieDefault['technical']) ? $cookieDefault['technical'] : 0;
            $cookiesPlusFinality->active = (isset($cookieDefault['active']) && $cookieDefault['active']) ? $cookieDefault['active'] : 0;

            if (isset($cookieDefault['modules']) && $cookieDefault['modules']) {
                $modulesIds = [];
                $modules = Module::getModulesOnDisk(true);
                foreach ($modules as $module) {
                    if ($module->installed && in_array($module->name, $cookieDefault['modules'])) {
                        $modulesIds[] = $module->id;
                    }
                }

                $cookiesPlusFinality->modules = json_encode($modulesIds);

                // If store has any of the modules, enable this finality
                if ($modulesIds) {
                    $cookiesPlusFinality->active = 1;
                }
            }

            foreach ($languages as $lang) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $languageCode = $lang['iso_code'];
                } else {
                    $languageCode = strtok($lang['language_code'], '-');
                }

                $cookiesPlusFinality->name[$lang['id_lang']] = (isset($cookieDefault['name'][$languageCode]) && $cookieDefault['name'][$languageCode]) ? $cookieDefault['name'][$languageCode] : $cookieDefault['name']['en'];
                $cookiesPlusFinality->description[$lang['id_lang']] = (isset($cookieDefault['description'][$languageCode]) && $cookieDefault['description'][$languageCode]) ? $cookieDefault['description'][$languageCode] : $cookieDefault['description']['en'];
            }

            $cookiesPlusFinality->save();

            if (isset($cookieDefault['cookies']) && $cookieDefault['cookies']) {
                foreach ($cookieDefault['cookies'] as $cookie) {
                    $cookiesPlusCookie = new CookiesPlusCookie();
                    $cookiesPlusCookie->id_shop = $shop;
                    $cookiesPlusCookie->id_cookiesplus_finality = $cookiesPlusFinality->id;
                    $cookiesPlusCookie->active = $cookie['active'];
                    $cookiesPlusCookie->name = $cookie['name'];
                    $cookiesPlusCookie->provider = isset($cookie['provider']) ? $cookie['provider'] : '';
                    $cookiesPlusCookie->provider_url = isset($cookie['provider_url']) ? $cookie['provider_url'] : '';

                    // If store has any of the modules, enable this finality
                    if (isset($cookie['modules']) && $cookie['modules']) {
                        $modules = Module::getModulesOnDisk(true);
                        foreach ($modules as $module) {
                            if ($module->installed && in_array($module->name, $cookie['modules'])) {
                                $cookiesPlusCookie->active = 1;
                                $cookiesPlusFinality = new CookiesPlusFinality((int) $cookiesPlusFinality->id);
                                $cookiesPlusFinality->active = 1;
                                $cookiesPlusFinality->save();
                                continue;
                            }
                        }
                    }

                    foreach ($languages as $lang) {
                        if (version_compare(_PS_VERSION_, '1.5', '<')) {
                            $languageCode = $lang['iso_code'];
                        } else {
                            $languageCode = strtok($lang['language_code'], '-');
                        }

                        if (isset($cookie['purpose']['en'])) {
                            $cookiesPlusCookie->purpose[$lang['id_lang']] = (isset($cookie['purpose'][$languageCode]) && $cookie['purpose'][$languageCode]) ? $cookie['purpose'][$languageCode] : $cookie['purpose']['en'];
                        }

                        if (isset($cookie['expiry']['en'])) {
                            $cookiesPlusCookie->expiry[$lang['id_lang']] = (isset($cookie['expiry'][$languageCode]) && $cookie['expiry'][$languageCode]) ? $cookie['expiry'][$languageCode] : $cookie['expiry']['en'];
                        }
                    }

                    $cookiesPlusCookie->save();
                }
            }
        }
    }

    if (version_compare(_PS_VERSION_, '1.6', '<')) {
        Configuration::updateValue('C_P_BACKGROUND_COLOR', '#FFFFFF');
        Configuration::updateValue('C_P_FONT_COLOR', '#000000');
    }

    Configuration::updateValue('C_P_BOTS', Configuration::get('C_P_BOTS') . '|Cookiebot|Lighthouse');

    return true;
}
