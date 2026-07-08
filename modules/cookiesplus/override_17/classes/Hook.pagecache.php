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
    public static function getHookModuleExecList($hookName = null)
    {
        $modulesToInvoke = parent::getHookModuleExecList($hookName);

        if (Module::isEnabled('cookiesplus')) {
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $modulesToInvoke = $cookiesPlus->blockModuleCache($modulesToInvoke, $hookName);
            }
        }

        return !empty($modulesToInvoke) ? $modulesToInvoke : false;
    }

    public static function coreCallHook($module, $method, $params)
    {
        if (!Module::isEnabled('pagecache') || !file_exists(_PS_MODULE_DIR_ . 'pagecache/pagecache.php')) {
            if (Module::isEnabled('cookiesplus')) {
                if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                    if ($cookiesPlus->blockHookCall(['module' => &$module, 'hookName' => &$method, 'params' => &$params]) == true) {
                        return;
                    }
                }
            }

            $display = parent::coreCallHook($module, $method, $params);

            if (Module::isEnabled('cookiesplus')) {
                if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                    $cookiesPlus->blockModuleCode([
                        'display' => &$display,
                        'module' => &$module,
                        'hookName' => &$method,
                        'params' => &$params,
                    ]);
                }
            }

            return $display;
        } else {
            require_once _PS_MODULE_DIR_ . 'pagecache/pagecache.php';

            return PageCache::execHook(PageCache::HOOK_TYPE_MODULE, $module, $method, $params);
        }
    }

    public static function coreRenderWidget($module, $hook_name, $params)
    {
        if (!Module::isEnabled('pagecache') || !file_exists(_PS_MODULE_DIR_ . 'pagecache/pagecache.php')) {
            $display = parent::coreRenderWidget($module, $hook_name, $params);

            if (Module::isEnabled('cookiesplus')) {
                if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                    $cookiesPlus->blockModuleCode([
                        'display' => &$display,
                        'module' => &$module,
                        'hookName' => &$hook_name,
                        'params' => &$params,
                    ]);
                }
            }

            return $display;
        } else {
            require_once _PS_MODULE_DIR_ . 'pagecache/pagecache.php';

            return PageCache::execHook(PageCache::HOOK_TYPE_WIDGET, $module, $hook_name, $params);
        }
    }
}
