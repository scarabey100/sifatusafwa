<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CpPopups
{
    public static $index;
    public static $popups;
    public static $frontPage;

    /**
     * Private constructor to prevent instantiate static class
     *
     * @return void
     */
    private function __construct()
    {
    }

    public static function init()
    {
        // Init Popups data
        self::$index = cp_get_index();
        self::$popups = [];

        // Make sure that the Popup Index is an array
        if (!is_array(self::$index)) {
            self::$index = [];
        }
    }

    public static function setup()
    {
        self::$frontPage = !cp_is_admin();
        self::autoinclude();
    }

    public static function addIndex($ids, &$props = null)
    {
        if (!$ids) {
            return false;
        }
        is_array($ids) || $ids = [$ids];

        if (!$props) {
            $popups = CpInstances::find($ids);
        }
        if (!is_array(self::$index)) {
            self::$index = [];
        }
        $tz = date_default_timezone_get();
        date_default_timezone_set(cp_get_option('timezone_string'));

        foreach ($ids as $i => $id) {
            if (isset($popups)) {
                $props = &$popups[$i]['data']['properties'];
            }
            if (!empty($props['schedule_start']) && !is_numeric($props['schedule_start'])) {
                $props['schedule_start'] = (int) strtotime(str_replace('/', '-', $props['schedule_start']));
            }
            if (!empty($props['schedule_end']) && !is_numeric($props['schedule_end'])) {
                $props['schedule_end'] = (int) strtotime(str_replace('/', '-', $props['schedule_end']));
            }
            if (!isset($props['cats']) || !isset($props['pages'])) {
                $props['cats'] = ['all'];
                $props['pages'] = ['all'];
            }
            self::$index[$id] = [
                'id' => (int) $id,
                'shop' => isset($props['shop']) ? (array) $props['shop'] : [0],
                'lang' => isset($props['lang']) ? (int) $props['lang'] : 0,
                'schedule_start' => isset($props['schedule_start']) ? (int) $props['schedule_start'] : 0,
                'schedule_end' => isset($props['schedule_end']) ? (int) $props['schedule_end'] : 0,
                'first_time_visitor' => !empty($props['popup_first_time_visitor']),
                'subscribed_visitor' => isset($props['popup_subscribed_visitor'])
                    ? (int) $props['popup_subscribed_visitor']
                    : 0,
                'repeat' => !empty($props['popup_repeat']),
                'repeat_days' => isset($props['popup_repeat_days']) ? $props['popup_repeat_days'] : '',
                'roles' => isset($props['popup_roles']) ? $props['popup_roles'] : [0],
                'disable' => (int) !empty($props['disableondesktop']) +
                    (int) !empty($props['disableontablet']) * 2 +
                    (int) !empty($props['disableonmobile']) * 4,
                'pages' => cp_get_pages($props['cats'], $props['pages']),
                'urls' => empty($props['urls']) ? '' : '~(' . preg_replace(
                    [
                        '~^(https?:)?//|#.*~im',
                        '~\*~',
                        '~\\\\~',
                        '~\~\.|\+|\?|\[|\^|\]|\$|\(|\)|\{|\}~',
                        '~\s*\r?\n\s*~',
                    ],
                    ['', '.*', '$0$0$0$0', '\\\\\\\\$0', '|'],
                    trim($props['urls'])
                ) . ')$~i',
            ];
            // self::$index[$id]['urls'] = preg_replace('~([^\\\\])\\\\\?~', '$1\\\\\\\\?', self::$index[$id]['urls']);
        }

        date_default_timezone_set($tz);

        return cp_update_index(self::$index);
    }

    public static function removeIndex($ids)
    {
        if (!is_array(self::$index)) {
            self::$index = [];
        }
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $id) {
            unset(self::$index[$id]);
        }

        return cp_update_index(self::$index);
    }

    protected static function autoinclude()
    {
        if (empty(self::$index) || !is_array(self::$index)) {
            return;
        }
        $context = Context::getContext();
        $groups = empty($context->customer) ? [] : $context->customer->getGroups();
        $time = time();

        if (method_exists($context, 'getMobileDetect')) {
            $context->getMobileDetect();
        } elseif (!$context->mobile_detect) {
            require_once _PS_TOOL_DIR_ . 'mobile_Detect/Mobile_Detect.php';
            $context->mobile_detect = new Mobile_Detect();
        }
        $device = $context->mobile_detect->isTablet() ? 2 : ($context->mobile_detect->isMobile() ? 4 : 1);
        $customer = !empty($context->customer->id) ? $context->customer : null;
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        foreach (self::$index as &$popup) {
            $subscribed = isset($popup['subscribed_visitor']) ? $popup['subscribed_visitor'] : 0;
            $shop = isset($popup['shop']) ? (array) $popup['shop'] : [0];

            // Shop, Language, Schedule, First time visitor, Subscription filter
            if ($shop[0] != 0 && !in_array($context->shop->id, $shop)
                || !empty($popup['lang']) && $popup['lang'] != $context->language->id
                || !empty($popup['schedule_start']) && $time < $popup['schedule_start']
                || !empty($popup['schedule_end']) && $time > $popup['schedule_end']
                || !empty($popup['disable']) && $popup['disable'] & $device
                || !empty($popup['first_time_visitor']) && !empty(${'_COOKIE'}['cp-popup-last-displayed'])
                || $subscribed > 0 && ($customer && empty($customer->newsletter) || !$customer && empty(${'_COOKIE'}['cp-subscribed']))
                || $subscribed < 0 && ($customer && !empty($customer->newsletter) || !$customer && !empty(${'_COOKIE'}['cp-subscribed']))
            ) {
                continue;
            }

            // Repeat control
            $key = 'cp-popup-' . $popup['id'];

            if (!$popup['repeat'] && !empty(${'_COOKIE'}[$key])) {
                continue;
            } elseif ($popup['repeat'] && $popup['repeat_days'] !== '') {
                if (!(float) $popup['repeat_days']) {
                    if (!empty(${'_COOKIE'}[$key])) {
                        continue;
                    }
                } else {
                    if (!empty(${'_COOKIE'}[$key]) && ${'_COOKIE'}[$key] > $time - 86400 * (float) $popup['repeat_days']) {
                        continue;
                    }
                }
            }

            // User roles, Pages, Urls
            if (!in_array('0', $popup['roles']) && !count(array_intersect($groups, $popup['roles']))
                || (empty($popup['pages']) || !self::checkPages($popup['pages'])) && (empty($popup['urls']) || !preg_match($popup['urls'], $url))
            ) {
                continue;
            }

            // Passed every test, include the Popup
            self::$popups[] = $popup;
        }
    }

    protected static function checkPages($pages)
    {
        // init properties
        foreach (['cat', 'prod', 'cms', 'page'] as $key) {
            if (empty($pages[$key])) {
                $pages[$key] = [];
            }
        }
        $controller = Context::getContext()->controller;
        $class = CreativePopup::$controllerClass;
        switch ($class) {
            case 'index':
                if ($pages['cat'] === 'all') {
                    return true;
                }

                return isset($pages['index']);
            case 'category':
                if ($pages['cat'] === 'all') {
                    return true;
                }
                $id = Tools::getValue('id_category');

                return in_array($id, $pages['cat']);
            case 'product':
                if ($pages['cat'] === 'all') {
                    return true;
                }
                $id = Tools::getValue('id_product');

                return in_array($id, $pages['prod']);
            case 'cms':
                if ($pages['cms'] === 'all') {
                    return true;
                }
                if (isset($controller->cms->id)) {
                    return in_array($controller->cms->id, $pages['page']);
                }
                if (isset($controller->cms_category->id)) {
                    return in_array($controller->cms_category->id, $pages['cms']);
                }

                return false;
            case 'manufacturer':
                if ($pages['cms'] === 'all') {
                    return true;
                }
                if (isset($pages['manufacturer'])) {
                    $id = Tools::getValue('id_manufacturer', 0);

                    return in_array($id, $pages['manufacturer']);
                }

                return false;
            case 'psblogpostsmodulefront':
                return isset($pages[$class]) && !$controller->id_post;
            case 'prestablogblogmodulefront':
                if ($pages['cms'] === 'all') {
                    return true;
                }
                if ($id = Tools::getValue('id', 0)) {
                    return isset($pages['bn']) && in_array($id, $pages['bn']);
                }
                $c = Tools::getValue('c', 0);

                return isset($pages['bc']) && in_array($c, $pages['bc']);
            default:
                if ($pages['cms'] === 'all') {
                    return true;
                }

                return isset($pages[$class]);
        }
    }

    public static function render($popup)
    {
        if (!empty(self::$popups) && is_array(self::$popups)) {
            foreach (self::$popups as $popup) {
                echo CpShortcode::handleShortcode(['id' => $popup['id'], 'filters' => '', 'popup' => true]);
            }
        }
    }
}
