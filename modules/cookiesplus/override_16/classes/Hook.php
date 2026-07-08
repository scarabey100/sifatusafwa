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

class Hook extends HookCore
{
    public static function exec($hook_name, $hook_args = [], $id_module = null, $array_return = false, $check_exceptions = true, $use_push = false, $id_shop = null)
    {
        $headersBeforeExecution = headers_list();

        $display = parent::exec($hook_name, $hook_args, $id_module, true, $check_exceptions, $use_push, $id_shop);

        if (Module::isEnabled('cookiesplus')) {
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $cookiesPlus->blockModuleCode([
                    'return' => &$display,
                    'hookName' => &$hook_name,
                    'hook_args' => &$hook_args,
                    'id_module' => &$id_module,
                    'array_return' => &$array_return,
                    'check_exceptions' => &$check_exceptions,
                    'headersBeforeExecution' => $headersBeforeExecution,
                ]);
            }
        }

        if ($array_return) {
            return $display;
        }

        $output = '';
        foreach ((array) $display as $moduleDisplay) {
            $output .= $moduleDisplay;
        }

        return $output;
    }
}
