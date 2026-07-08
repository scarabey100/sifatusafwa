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

function upgrade_module_1_4_1()
{
    Configuration::updateValue('C_P_ACCEPT_FONT_SIZE', '1rem');
    Configuration::updateValue('C_P_MORE_INFO_FONT_SIZE', '1rem');
    Configuration::updateValue('C_P_REJECT_FONT_SIZE', '1rem');
    Configuration::updateValue('C_P_SAVE_FONT_SIZE', '1rem');

    if (Configuration::get('C_P_MYACCOUNT_AWESOME')) {
        Configuration::updateValue('C_P_MATERIAL_ICONS_LIBRARY', '3');
    } else {
        Configuration::updateValue('C_P_MATERIAL_ICONS_LIBRARY', '1');
        Configuration::updateValue('C_P_MATERIAL_ICONS', true);
    }

    $translations = [];

    // English
    $translations['C_P_TITLE']['en'] = '<span><strong>Your cookie settings</strong></span><br /><br />';

    // Spanish
    $translations['C_P_TITLE']['es'] = '<span><strong>Tu configuración de cookies</strong></span><br /><br />';

    // French
    $translations['C_P_TITLE']['fr'] = '<span><strong>Vos paramètres de cookies</strong></span><br /><br />';

    // Polish
    $translations['C_P_TITLE']['pl'] = '<span><strong>Ustawienia plików cookie</strong></span><br /><br />';

    // Romanian
    $translations['C_P_TITLE']['ro'] = '<span><strong>Setările cookie-urilor</strong></span><br /><br />';

    // Portuguese
    $translations['C_P_TITLE']['pt'] = '<span><strong>As tuas configurações de cookies</strong></span><br /><br />';

    // Slovak
    $translations['C_P_TITLE']['sk'] = '<span><strong>Nastavenia súborov cookie</strong></span><br /><br />';

    // Nederlands
    $translations['C_P_TITLE']['nl'] = '<span><strong>Je cookie-instellingen</strong></span><br /><br />';

    // Deutsch
    $translations['C_P_TITLE']['de'] = '<span><strong>Ihre Cookie-Einstellungen</strong></span><br /><br />';

    // Greek
    $translations['C_P_TITLE']['gr'] = '<span><strong>Οι ρυθμίσεις cookie σας</strong></span><br /><br />';

    // Italian
    $translations['C_P_TITLE']['it'] = '<span><strong>Impostazioni dei cookie</strong></span><br /><br />';

    // Svenska
    $translations['C_P_TITLE']['sv'] = '<span><strong>Dina cookieinställningar</strong></span><br /><br />';

    // Dansk
    $translations['C_P_TITLE']['da'] = '<span><strong>Dine indstillinger for cookies</strong></span><br /><br />';

    // Norsk
    $translations['C_P_TITLE']['no'] = '<span><strong>Dine innstillinger for informasjonskapsler</strong></span><br /><br />';

    // ČEŠTINA
    $translations['C_P_TITLE']['cs'] = '<span><strong>Tvá nastavení souborů cookie</strong></span><br /><br />';

    // Magyar
    $translations['C_P_TITLE']['hu'] = '<span><strong>Cookie-beállítások</strong></span><br /><br />';

    $fields = [];
    $languages = Language::getLanguages(false);
    foreach ($languages as $lang) {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $languageCode = $lang['iso_code'];
        } else {
            $languageCode = strtok($lang['language_code'], '-');
        }

        $fields['C_P_TITLE'][$lang['id_lang']] = isset($translations['C_P_TITLE'][$languageCode]) ? $translations['C_P_TITLE'][$languageCode] : $translations['C_P_TITLE']['en'];
    }

    Configuration::updateValue('C_P_TITLE', $fields['C_P_TITLE'], true);

    return true;
}
