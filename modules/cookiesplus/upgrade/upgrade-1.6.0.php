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

function upgrade_module_1_6_0()
{
    Configuration::updateValue('C_P_DEFAULT_CONSENT', true);

    $values = json_decode(Configuration::get('C_P_GTM_CONSENT'), true);
    if ($values) {
        foreach ($values as $key => &$item) {
            if (isset($item['gtmFinality']) && is_string($item['gtmFinality'])) {
                // Convert the string to an associative array with a true value
                $item['gtmFinality'] = [$item['gtmFinality'] => true];
            }

            if ($key === CookiesPlusFinality::MARKETING_COOKIE) {
                // Add new keys to the gtmFinality array
                $newConsents = [
                    'ad_user_data' => true,
                    'ad_personalization' => true,
                ];

                // Merge the existing gtmFinality array with the new keys
                $item['gtmFinality'] = array_merge($item['gtmFinality'], $newConsents);
            }
        }
        unset($item);
    }

    return true;
}
