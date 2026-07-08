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
    /*
    * module: ets_superspeed
    */
    public static function getMethodName(string $hookName): string
    {
        return 'hook' . ucfirst($hookName);
    }

    /*
    * module: ets_superspeed
    */
    public static function callHookOn_parent(Module $module, string $hookName, array $hookArgs)
    {
        try {
            $methodName = self::getMethodName($hookName);
            if (is_callable([$module, $methodName])) {
                return static::coreCallHook($module, $methodName, $hookArgs);
            }
            foreach (static::getAllKnownNames($hookName) as $hook) {
                $methodName = self::getMethodName($hook);
                if (is_callable([$module, $methodName])) {
                    return static::coreCallHook($module, $methodName, $hookArgs);
                }
            }
        } catch (Exception $e) {
            if ($e) {
                return true;
            }
        }

        return '';
    }

    /*
    * module: ets_superspeed
    */
    public static function getDynamicHook($module, $hookName, $hookArgs)
    {
        if (!Ets_superspeed_cache_page::isAjax()) {
            if (!Ets_superspeed_cache_page::checkPageNoCache()) {
                $dynamicHook = Ets_superspeed_cache_page::getDynamicHookModule($module->id, $hookName);
                if (Tools::strtolower($hookName) == 'cetemplate') {
                    $hid = Configuration::get('posthemeoptionsheader_template') ?: 7;
                    if (isset($hookArgs['id']) && ($idCreative = $hookArgs['id']) && $idCreative == $hid) {
                        $dynamicHook = [
                            'empty_content' => 0,
                        ];
                    }
                }
                if ($dynamicHook) {
                    $params = [];
                    if (isset($hookArgs['id_product']) && $hookArgs['id_product']) {
                        $params['id_product'] = $hookArgs['id_product'];
                    } elseif (isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product']) && $hookArgs['product']['id_product']) {
                        $params['id_product'] = $hookArgs['product']['id_product'];
                    } elseif (isset($hookArgs['product']) && is_object($hookArgs['product']) && isset($hookArgs['product']->id) && $hookArgs['product']->id) {
                        $params['id_product'] = $hookArgs['product']->id;
                    }
                    if (isset($hookArgs['id_product_attribute']) && $hookArgs['id_product_attribute']) {
                        $params['id_product_attribute'] = $hookArgs['id_product_attribute'];
                    } elseif (isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product_attribute']) && $hookArgs['product']['id_product_attribute']) {
                        $params['id_product_attribute'] = $hookArgs['product']['id_product_attribute'];
                    }
                    if (isset($hookArgs['type']) && $hookArgs['type']) {
                        $params['type'] = $hookArgs['type'];
                    }
                    if (isset($hookArgs['tpl']) && $hookArgs['tpl']) {
                        $params['tpl'] = $hookArgs['tpl'];
                    }

                    return [
                        'html_before' => '<div id="ets_speed_dy_' . $module->id . $hookName . (isset($params['id_product']) ? '_' . $params['id_product'] : '') . (isset($params['id_product_attribute']) ? '_' . $params['id_product_attribute'] : '') . (isset($params['type']) ? '_' . $params['type'] : '') . '" data-moudule="' . $module->id . '" data-module-name="' . $module->name . '" data-hook="' . $hookName . '" data-params=\'' . json_encode($params) . '\' class="ets_speed_dynamic_hook" ' . (isset($idCreative) ? ' data-idCreative="' . (int) $idCreative . '"' : '') . '>',
                        'empty_content' => $dynamicHook['empty_content'],
                    ];
                }
            }
        }

        return false;
    }

    /*
    * module: ets_superspeed
    */
    public static function callHookOn(Module $module, string $hookName, array $hookArgs)
    {
        require_once dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php';
        $content = self::callHookOn_parent($module, $hookName, $hookArgs);
        if (is_array($content) || is_object($content) || Tools::strtolower($hookName) == 'header' || Tools::strtolower($hookName) == 'displayheader' || !Module::isEnabled('ets_superspeed')) {
            return $content;
        }
        $html = '';
        $time_start = microtime(true);
        $dynamicHook = self::getDynamicHook($module, $hookName, $hookArgs);
        if ($dynamicHook) {
            $html .= $dynamicHook['html_before'];
        }
        if (!$dynamicHook || ($dynamicHook && !$dynamicHook['empty_content'])) {
            $html .= $content;
        }
        if ($dynamicHook) {
            $html .= '</div>';
        }
        if (Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE')) {
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if (Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` WHERE id_module="' . (int) $module->id . '" AND hook_name="' . pSQL($hookName) . '" AND id_shop=' . (int) Context::getContext()->shop->id)) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` SET page="' . pSQL($_SERVER['REQUEST_URI']) . '",time="' . (float) $time . '",date_add ="' . pSQL(date('Y-m-d H:i:s')) . '" WHERE id_module="' . (int) $module->id . '" AND hook_name="' . pSQL($hookName) . '" AND id_shop=' . (int) Context::getContext()->shop->id);
            } else {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_superspeed_hook_time`(id_module,hook_name,page,time,date_add,id_shop) VALUES("' . (int) $module->id . '","' . pSQL($hookName) . '","' . pSQL($_SERVER['REQUEST_URI']) . '","' . (float) $time . '","' . pSQL(date('Y-m-d H:i:s')) . '","' . (int) Context::getContext()->shop->id . '")');
            }
        }

        return $html;
    }

    public static function coreRenderWidget($module, $hookName, $hookArgs)
    {
        $headersBeforeExecution = headers_list();

        $content = parent::coreRenderWidget($module, $hookName, $hookArgs);

        if (Module::isEnabled('cookiesplus')) {
            $forceDisplay = false;
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $cookiesPlus->blockModuleCode([
                    'display' => &$content,
                    'module' => &$module,
                    'hookName' => &$hookName,
                    'params' => &$hookArgs,
                    'forceDisplay' => &$forceDisplay,
                    'headersBeforeExecution' => $headersBeforeExecution,
                ]);
            }
        }

        if (is_array($content) || is_object($content) || Tools::strtolower($hookName) == 'header' || Tools::strtolower($hookName) == 'displayheader' || !Module::isEnabled('ets_superspeed')) {
            return $content;
        }
        if (Tools::strtolower($hookName) == 'header' || Tools::strtolower($hookName) == 'displayheader' || !Module::isEnabled('ets_superspeed')) {
            return $content;
        }
        $html = '';
        $time_start = microtime(true);
        $dynamicHook = self::getDynamicHook($module, $hookName, $hookArgs);
        if ($dynamicHook) {
            $html .= $dynamicHook['html_before'];
        }
        if (!$dynamicHook || ($dynamicHook && !$dynamicHook['empty_content'])) {
            $html .= $content;
        }
        if ($dynamicHook) {
            $html .= '</div>';
        }
        if (Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE')) {
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if (Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` WHERE id_module="' . (int) $module->id . '" AND hook_name="' . pSQL($hookName) . '" AND id_shop=' . (int) Context::getContext()->shop->id)) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` SET page="' . pSQL($_SERVER['REQUEST_URI']) . '",time="' . (float) $time . '",date_add ="' . pSQL(date('Y-m-d H:i:s')) . '" WHERE id_module="' . (int) $module->id . '" AND hook_name="' . pSQL($hookName) . '" AND id_shop=' . (int) Context::getContext()->shop->id);
            } else {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_superspeed_hook_time`(id_module,hook_name,page,time,date_add,id_shop) VALUES("' . (int) $module->id . '","' . pSQL($hookName) . '","' . pSQL($_SERVER['REQUEST_URI']) . '","' . (float) $time . '","' . pSQL(date('Y-m-d H:i:s')) . '","' . (int) Context::getContext()->shop->id . '")');
            }
        }

        return $html;
    }

    /*
    * module: cookiesplus
    */
    public static function getHookModuleExecList($hookName = null)
    {
        $modulesToInvoke = parent::getHookModuleExecList($hookName);

        if (Module::isEnabled('cookiesplus')) {
            // Static call
            // include_once _PS_MODULE_DIR_ . 'cookiesplus/cookiesplus.php';
            // $modulesToInvoke = CookiesPlus::blockModuleCacheStatic($modulesToInvoke, $hookName);

            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                $modulesToInvoke = $cookiesPlus->blockModuleCache($modulesToInvoke, $hookName);
            }
        }

        return !empty($modulesToInvoke) ? $modulesToInvoke : false;
    }

    public static function coreCallHook($module, $method, $params)
    {
        $headersBeforeExecution = headers_list();

        if (Module::isEnabled('cookiesplus')) {
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                if ($cookiesPlus->blockHookCall(['module' => &$module, 'hookName' => &$method, 'params' => &$params]) == true) {
                    return;
                }
            }
        }

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

        return $display;
    }
}
