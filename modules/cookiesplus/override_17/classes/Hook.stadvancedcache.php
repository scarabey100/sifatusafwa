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
        /*
        * module: cookiesplus
        */
        $headersBeforeExecution = headers_list();

        $display = parent::coreCallHook($module, $method, $params);

        if (Module::isEnabled('cookiesplus')) {
            $forceDisplay = false;
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $cookiesPlus->blockModuleCode([
                    'display' => &$display,
                    'module' => &$module,
                    'hookName' => &$method,
                    'params' => &$params,
                    'forceDisplay' => &$forceDisplay,
                    'headersBeforeExecution' => $headersBeforeExecution,
                ]);

                if ($forceDisplay) {
                    return $display;
                }
            }
        }

        /*
        * module: stadvancedcache
        */
        if (!is_string($display) || is_array($display) || strpos($method, 'displayPDF') !== false || strpos($method, 'displayInvoice') !== false) {
            return $display;
        }
        // $dyn_hooks = uns erialize(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks = json_decode(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = [];
        $hook_name = lcfirst(str_replace('hook', '', $method));
        if (in_array($hook_name, ['header', 'top', 'footer'])) {
            $hook_name = 'display' . ucfirst($hook_name);
        }
        if (is_string($display) && key_exists($module->name, $dyn_hooks) && in_array($hook_name, $dyn_hooks[$module->name])) {
            $display = '<!--stadvcache:' . $module->name . ':' . $hook_name . '[' . self::prepareParams($params) . ']-->' . $display . '<!--stadvcache:' . $module->name . ':' . $hook_name . '-->';
        }

        return $display;
    }

    public static function coreRenderWidget($module, $hook_name, $params)
    {
        /*
        * module: cookiesplus
        */
        $headersBeforeExecution = headers_list();

        $display = parent::coreRenderWidget($module, $hook_name, $params);

        if (Module::isEnabled('cookiesplus')) {
            $forceDisplay = false;
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $cookiesPlus->blockModuleCode([
                    'display' => &$display,
                    'module' => &$module,
                    'hookName' => &$hook_name,
                    'params' => &$params,
                    'forceDisplay' => &$forceDisplay,
                    'headersBeforeExecution' => $headersBeforeExecution,
                ]);

                if ($forceDisplay) {
                    return $display;
                }
            }
        }

        /*
        * module: stadvancedcache
        */
        $display = parent::coreRenderWidget($module, $hook_name, $params);
        if (!is_string($display) || is_array($display) || strpos($hook_name, 'displayPDF') !== false || strpos($hook_name, 'displayInvoice') !== false) {
            return $display;
        }
        // $dyn_hooks = uns erialize(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks = json_decode(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = [];
        $hook_name = lcfirst(str_replace('hook', '', $hook_name));
        if (in_array($hook_name, ['header', 'top', 'footer'])) {
            $hook_name = 'display' . ucfirst($hook_name);
        }
        if (is_string($display) && key_exists($module->name, $dyn_hooks) && in_array($hook_name, $dyn_hooks[$module->name])) {
            $display = '<!--stadvcache:' . $module->name . ':' . $hook_name . '[' . self::prepareParams($params) . ']-->' . $display . '<!--stadvcache:' . $module->name . ':' . $hook_name . '-->';
        }

        return $display;
    }

    public static function prepareParams($params)
    {
        /*
        * module: stadvancedcache
        */
        $str = '';
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                if (in_array($key, ['altern', 'cookie', 'cart'])) {
                    continue;
                }
                if ($key == 'product') {
                    if (is_object($val)) {
                        $str .= 'ip_o=' . $val->id;
                    } else {
                        $str .= 'ip=' . $val['id_product'];
                    }
                } elseif ($key == 'category') {
                    if (is_object($val)) {
                        $str .= 'ic_o=' . $val->id;
                    } else {
                        $str .= 'ic=' . $val['id_category'];
                    }
                } elseif (is_int($val) || is_bool($val)) {
                    $str .= $key . '=' . (int) $val;
                } elseif (is_string($val)) {
                    $str .= $key . '=' . urlencode($val);
                }
                $str && $str .= '*';
            }
        }

        return rtrim($str, '*');
    }
}
