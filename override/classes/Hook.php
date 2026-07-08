<?php
/**
 * Composite Hook.php override: cookiesplus v1.8.2 + litespeedcache v1.6.0.
 *
 * PrestaShop allows only ONE override/classes/Hook.php. Both modules ship
 * their own override that collides on coreCallHook() and coreRenderWidget().
 * Installing one clobbers the other silently. This file inlines both
 * modules' method bodies so both stay functional.
 *
 * Order within each method: cookiesplus runs first (pre-filter, parent,
 * post-filter). If cookiesplus sets $forceDisplay, it returns early —
 * litespeedcache's ESI wrap is correctly skipped. Otherwise, litespeedcache
 * wraps the output with ESI markers before the final return.
 *
 * Dormancy on non-LSWS environments (staging / local): the `_LITESPEED_CACHE_`
 * constant is defined unconditionally by the litespeedcache module
 * constructor (modules/litespeedcache/litespeedcache.php:112-114), so the
 * defined() guard is NOT the dormancy mechanism. The real guard is
 * LiteSpeedCache::injectCallHook() / injectRenderWidget() returning false
 * when CCBM_CAN_INJECT_ESI is not set (which requires the X-LSCACHE header
 * from a LiteSpeed web server — absent on Apache/local/staging).
 *
 * DO NOT overwrite on module upgrade. If either source module changes its
 * Hook.php, re-merge manually by running the compose procedure in
 * docs/runbooks/sfs-2-rollout-plan.md "Known conflict" section.
 *
 * -- cookiesplus license --
 * ISC License. Copyright (c) 2025 idnovate.com.
 *   @author    idnovate
 *   @copyright 2025 idnovate.com
 *   @license   https://opensource.org/licenses/ISC ISC License
 *
 * -- litespeedcache license --
 * GNU GPL-3.0. Copyright (c) 2017 LiteSpeed Technologies, Inc.
 *   @author    LiteSpeed Technologies
 *   @copyright 2017 LiteSpeed Technologies, Inc.
 *   @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Hook extends HookCore
{
    /*
    * source: cookiesplus v1.8.2 (verbatim)
    */
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

    /*
    * composite: cookiesplus v1.8.2 pre/post-filter + litespeedcache v1.6.0 ESI wrap
    */
    public static function coreCallHook($module, $method, $params)
    {
        // --- litespeedcache v1.6.0 debug trace (no logic, only LiteSpeedCacheLog calls; gated on _LITESPEED_DEBUG_) ---
        if (defined('_LITESPEED_DEBUG_')
            && _LITESPEED_DEBUG_ >= LiteSpeedCacheLog::LEVEL_HOOK_DETAIL) {
            $mesg = '  in hook coreCallHook ' . get_class($module) . ' - ' . $method;
            if ($method == 'hooklitespeedEsiBegin') {
                $mesg .= ' params m=' . $params['m'] . ' field=' . $params['field'];
                if (isset($params['hook'])) {
                    $mesg .= ' hook=' . $params['hook'];
                }
                if (isset($params['tpl'])) {
                    $mesg .= ' tpl=' . $params['tpl'];
                }
            }
            LiteSpeedCacheLog::log($mesg, LiteSpeedCacheLog::LEVEL_HOOK_DETAIL);
        }

        // --- cookiesplus pre-filter (verbatim from modules/cookiesplus/override/classes/Hook.php) ---
        $headersBeforeExecution = headers_list();
        if (Module::isEnabled('cookiesplus')) {
            if ($cookiesPlus = Module::getInstanceByName('cookiesplus')) {
                if ($cookiesPlus->blockHookCall(['module' => &$module, 'hookName' => &$method, 'params' => &$params]) == true) {
                    return;
                }
            }
        }

        // --- core ---
        $display = parent::coreCallHook($module, $method, $params);

        // --- cookiesplus post-filter (verbatim) ---
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

        // --- litespeedcache v1.6.0 ESI wrap (reached only when !$forceDisplay) ---
        // v1.6.0 signature: injectCallHook now takes 3rd $params argument; allows null $display
        // and normalizes empty output to "&nbsp;" so the ESI block is non-empty.
        if (defined('_LITESPEED_CACHE_') && (is_string($display) || $display === null)
                && ($marker = LiteSpeedCache::injectCallHook($module, $method, $params)) !== false) {
            if ($display === null || $display === '') {
                $display = '&nbsp;';
            }
            $display = $marker . $display . LiteSpeedCache::ESI_MARKER_END;
        }

        return $display;
    }

    /*
    * composite: cookiesplus v1.8.2 post-filter + litespeedcache v1.6.0 ESI wrap
    */
    public static function coreRenderWidget($module, $hook_name, $params)
    {
        // --- litespeedcache v1.6.0 debug trace (gated on _LITESPEED_DEBUG_) ---
        if (defined('_LITESPEED_DEBUG_')
            && _LITESPEED_DEBUG_ >= LiteSpeedCacheLog::LEVEL_HOOK_DETAIL) {
            $mesg = '  in hook coreRenderWidget module ' . get_class($module) . ' - ' . $hook_name;
            LiteSpeedCacheLog::log($mesg, LiteSpeedCacheLog::LEVEL_HOOK_DETAIL);
        }

        // --- core (cookiesplus only post-filters this method; no pre-filter) ---
        $headersBeforeExecution = headers_list();
        $display = parent::coreRenderWidget($module, $hook_name, $params);

        // --- cookiesplus post-filter (verbatim) ---
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

        // --- litespeedcache v1.6.0 ESI wrap (reached only when !$forceDisplay) ---
        // v1.6.0 signature: injectRenderWidget now takes 3rd $params argument.
        if (defined('_LITESPEED_CACHE_') && is_string($display)
                && ($marker = LiteSpeedCache::injectRenderWidget($module, $hook_name, $params)) !== false) {
            $display = $marker . $display . LiteSpeedCache::ESI_MARKER_END;
        }

        return $display;
    }
}
