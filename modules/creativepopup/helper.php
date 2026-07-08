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

define('CP_CONTENT_DIR', _PS_ROOT_DIR_);
define('CP_VIEWS_URL', _MODULE_DIR_ . 'creativepopup/views/');
define('CP_SCRIPTS_PRIORITY', (int) cp_get_option('cp_scripts_priority', 50));
define('CP_LOAD_UNPACKED', (int) cp_get_option('cp_load_unpacked', 0));

function cp_content_url($path = '')
{
    return rtrim(_MODULE_DIR_ . 'creativepopup/base/' . $path, '/');
}

function cp_get_posts($params = null)
{
    $context = Context::getContext();
    $id_lang = $context->language->id;
    $id_shop = $context->shop->id;
    $order_by = !empty($params['orderby']) ? $params['orderby'] : 'date_add';
    // compatibility fix
    $order_by === 'date' && $order_by = 'date_add';
    $order_way = !empty($params['order']) ? $params['order'] : 'DESC';
    $limit = !empty($params['limit']) ? $params['limit'] : 50;
    $offset = !empty($params['offset']) ? $params['offset'] : 0;

    if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
        exit(Tools::displayError());
    }

    $query = new DbQuery();
    $query->select('DISTINCT p.`id_product`, p.`date_add`, p.`date_upd`, pl.`name`, pl.`link_rewrite`, pl.`description`, pl.`description_short`, m.`name` AS manufacturer');
    $query->from('product', 'p')->join(Shop::addSqlAssociation('product', 'p'));
    $query->leftJoin('product_lang', 'pl', 'p.`id_product` = pl.`id_product` AND pl.id_shop = ' . (int) $id_shop);
    $query->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');
    $query->leftJoin('category_product', 'c', 'c.`id_product` = p.`id_product`');
    $query->leftJoin('product_tag', 'pt', 'pt.`id_product` = p.`id_product`');
    $query->where('pl.`id_lang` = ' . (int) $id_lang . ' AND product_shop.`active` = 1 AND product_shop.`visibility` IN ("both", "catalog")');
    empty($params['post_type']) || $query->where('m.`id_manufacturer` IN (' . implode(',', array_map('intval', $params['post_type'])) . ')');
    empty($params['category__in']) || $query->where('c.`id_category` IN (' . implode(',', array_map('intval', $params['category__in'])) . ')');
    empty($params['tag__in']) || $query->where('pt.`id_tag` IN (' . implode(',', array_map('intval', $params['tag__in'])) . ')');
    if ('name' === $order_by) {
        $query->orderBy('pl.`name` ' . pSQL($order_way));
    } elseif ('position' === $order_by) {
        $query->orderBy('c.`position` ' . ('ASC' === $order_way ? 'DESC' : 'ASC'));
    } elseif ('quantity' === $order_by) {
        $query->leftJoin('product_sale', 'ps', 'ps.`id_product` = p.`id_product`');
        $query->orderBy('ps.`quantity` ' . pSQL($order_way));
    } elseif ('reduction' === $order_by) {
        $query->innerJoin('specific_price', 'sp', 'sp.`id_product` = p.`id_product`');
        $query->where('sp.`from` <= NOW() AND (sp.`to` >= NOW() OR sp.`to` = "0000-00-00 00:00:00")');
        $query->orderBy('sp.`reduction` ' . pSQL($order_way));
    } elseif ('rand' === $order_by) {
        $query->orderBy('RAND()');
    } else {
        $query->orderBy('p.`' . bqSQL($order_by) . '` ' . pSQL($order_way));
    }
    $query->limit($limit, $offset);

    return Db::getInstance()->executeS($query);
}

function cp_get_post_type_object($post_type)
{
    return (object) [
        'labels' => (object) ['name' => $post_type['name']],
    ];
}

class CpManufacturerArray extends ArrayObject
{
    public function __toString()
    {
        return isset($this['slug']) ? (string) $this['slug'] : '';
    }
}

function cp_get_post_types()
{
    $mans = Manufacturer::getManufacturers();
    foreach ($mans as &$man) {
        $man = new CpManufacturerArray($man);
        $man['slug'] = $man['id_manufacturer'];
    }
    array_unshift($mans, new CpManufacturerArray([
        'name' => cp__("Don't filter manufacturers"),
        'slug' => 0,
    ]));

    return $mans;
}
function cp_get_categories()
{
    require_once _PS_MODULE_DIR_ . 'creativepopup/classes/CpHelper.php';

    return CpHelper::getCategoryList();
}
function cp_get_tags()
{
    require_once _PS_MODULE_DIR_ . 'creativepopup/classes/CpHelper.php';
    $tags = CpHelper::getTagList();
    foreach ($tags as &$tag) {
        $tag = (object) $tag;
    }

    return $tags;
}
function cp_get_taxonomies()
{
    return [];
}

function cp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
{
    $size .= '';
    $icon &= false;
    $attrs = [
        'src' => $attachment_id,
        'alt' => 'Background',
    ];
    if (is_array($attr)) {
        $attrs = array_merge($attrs, $attr);
    }
    $img = '<img';
    foreach ($attrs as $key => &$value) {
        $img .= ' ' . $key . '="' . $value . '"';
    }
    $img .= '>';

    return $img;
}

$GLOBALS['cp_action'] = [];

function cp_add_action($tag, $func, $priority = 10, $args = 1)
{
    if (!isset($GLOBALS['cp_action'][$tag])) {
        $GLOBALS['cp_action'][$tag] = [];
    }
    $GLOBALS['cp_action'][$tag][] = $func;
}

function cp_do_action($tag, $arg = [])
{
    if (isset($GLOBALS['cp_action'][$tag])) {
        foreach ($GLOBALS['cp_action'][$tag] as $func) {
            call_user_func_array($func, $arg);
        }
    }
}

function cp_has_action($tag, $func_to_check = '')
{
    $func_to_check .= '';

    return isset($GLOBALS['cp_action'][$tag]);
}

$GLOBALS['cp_filter'] = [];

function cp_add_filter($tag, $func, $priority = 10, $args = 1)
{
    if (!isset($GLOBALS['cp_filter'][$tag])) {
        $GLOBALS['cp_filter'][$tag] = [];
    }
    $GLOBALS['cp_filter'][$tag][] = $func;
}

function cp_apply_filters($tag, $value, $arg1 = null, $arg2 = null)
{
    if (isset($GLOBALS['cp_filter'][$tag])) {
        foreach ($GLOBALS['cp_filter'][$tag] as $func) {
            if ($arg1 === null) {
                $value = is_string($func) ? call_user_func($func, $value) : $func[0]->{$func[1]}($value);
            } else {
                $value = is_string($func) ? call_user_func($func, $value, $arg1, $arg2) : $func[0]->{$func[1]}($value, $arg1, $arg2);
            }
        }
    }

    return $value;
}

function cp_has_filter($tag, $func_to_check = '')
{
    $func_to_check .= '';

    return isset($GLOBALS['cp_filter'][$tag]);
}

function cp_get_userdata($userid)
{
    $user = new Employee((int) $userid);
    $user->user_nicename = $user->firstname . ' ' . $user->lastname;

    return $user;
}

function cp_get_avatar_url($userid)
{
    $user = new Employee((int) $userid);

    return method_exists($user, 'getImage')
        ? $user->getImage()
        : 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='
    ;
}

function cp_add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function)
{
    $page_title .= '';
    $menu_title .= '';
    $capability .= '';
    $screen_id = $parent_slug . '_page_' . $menu_slug;
    cp_add_action($screen_id, $function);

    return $screen_id;
}

function cp_is_ssl()
{
    return strpos(_PS_BASE_URL_SSL_, 'https') === 0; // @phpstan-ignore-line
}

define('CP_MINUTE_IN_SECS', 60);
define('CP_HOUR_IN_SECS', 60 * CP_MINUTE_IN_SECS);
define('CP_DAY_IN_SECS', 24 * CP_HOUR_IN_SECS);
define('CP_WEEK_IN_SECS', 7 * CP_DAY_IN_SECS);
define('CP_MONTH_IN_SECS', 30 * CP_DAY_IN_SECS);
define('CP_YEAR_IN_SECS', 365 * CP_DAY_IN_SECS);

function cp_human_time_diff($from, $to = '')
{
    if (empty($to)) {
        $to = time();
    }
    $since = '';
    $diff = (int) abs($to - $from);

    if ($diff < CP_HOUR_IN_SECS) {
        $mins = round($diff / CP_MINUTE_IN_SECS);
        if ($mins <= 1) {
            $mins = 1;
        }
        $since = sprintf(cp_n('%s min', '%s mins', $mins), $mins);
    } elseif ($diff < CP_DAY_IN_SECS && $diff >= CP_HOUR_IN_SECS) {
        $hours = round($diff / CP_HOUR_IN_SECS);
        if ($hours <= 1) {
            $hours = 1;
        }
        $since = sprintf(cp_n('%s hour', '%s hours', $hours), $hours);
    } elseif ($diff < CP_WEEK_IN_SECS && $diff >= CP_DAY_IN_SECS) {
        $days = round($diff / CP_DAY_IN_SECS);
        if ($days <= 1) {
            $days = 1;
        }
        $since = sprintf(cp_n('%s day', '%s days', $days), $days);
    } elseif ($diff < CP_MONTH_IN_SECS && $diff >= CP_WEEK_IN_SECS) {
        $weeks = round($diff / CP_WEEK_IN_SECS);
        if ($weeks <= 1) {
            $weeks = 1;
        }
        $since = sprintf(cp_n('%s week', '%s weeks', $weeks), $weeks);
    } elseif ($diff < CP_YEAR_IN_SECS && $diff >= CP_MONTH_IN_SECS) {
        $months = round($diff / CP_MONTH_IN_SECS);
        if ($months <= 1) {
            $months = 1;
        }
        $since = sprintf(cp_n('%s month', '%s months', $months), $months);
    } elseif ($diff >= CP_YEAR_IN_SECS) {
        $years = round($diff / CP_YEAR_IN_SECS);
        if ($years <= 1) {
            $years = 1;
        }
        $since = sprintf(cp_n('%s year', '%s years', $years), $years);
    }

    return $since;
}

function cp_add_user_meta($user_id, $key, $value)
{
    return cp_add_option('u' . (int) $user_id . '_' . $key, $value);
}

function cp_get_user_meta($user_id, $key = '')
{
    return cp_get_option('u' . (int) $user_id . '_' . $key);
}

function cp_update_user_meta($user_id, $key, $value)
{
    return cp_update_option('u' . (int) $user_id . '_' . $key, $value);
}

function cp_remote_retrieve_body($response)
{
    return $response['body'];
}

function cp_get_current_screen()
{
    return isset($GLOBALS['cp_screen']) ? $GLOBALS['cp_screen'] : (object) ['id' => '', 'base' => ''];
}

function cp_die($message = '', $title = '')
{
    exit($title ? "$title \n$message" : $message);
}

function cp__($text, $domain = 'creativepopup')
{
    return stripslashes(Translate::getModuleTranslation($domain, $text, '', null, true));
}

function cp_e($text, $domain = 'creativepopup')
{
    echo stripslashes(Translate::getModuleTranslation($domain, $text, '', null, true));
}

function cp_x($text, $context, $domain = 'creativepopup')
{
    // TODO
    $context .= '';

    return stripslashes(Translate::getModuleTranslation($domain, $text, '', null, true));
}

function cp_ex($text, $context, $domain = 'creativepopup')
{
    // TODO
    $context .= '';

    echo stripslashes(Translate::getModuleTranslation($domain, $text, '', null, true));
}

function cp_n($single, $plural, $number, $domain = 'creativepopup')
{
    return stripslashes(Translate::getModuleTranslation($domain, $number > 1 ? $plural : $single, '', null, true));
}

function cp_get_allowed_mime_types()
{
    return cp_apply_filters('allowed_mime_types', cp_get_mime_types());
}

function cp_get_mime_types()
{
    return [
        // Image formats.
        'webp' => 'image/webp',
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'tiff|tif' => 'image/tiff',
        'ico' => 'image/x-icon',
        // Video formats.
        'asf|asx' => 'video/x-ms-asf',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wm' => 'video/x-ms-wm',
        'avi' => 'video/avi',
        'divx' => 'video/divx',
        'flv' => 'video/x-flv',
        'mov|qt' => 'video/quicktime',
        'mpeg|mpg|mpe' => 'video/mpeg',
        'mp4|m4v' => 'video/mp4',
        'ogv' => 'video/ogg',
        'webm' => 'video/webm',
        'mkv' => 'video/x-matroska',
        '3gp|3gpp' => 'video/3gpp', // Can also be audio
        '3g2|3gp2' => 'video/3gpp2', // Can also be audio
        // Text formats.
        'txt|asc|c|cc|h|srt' => 'text/plain',
        'csv' => 'text/csv',
        'tsv' => 'text/tab-separated-values',
        'ics' => 'text/calendar',
        'rtx' => 'text/richtext',
        'css' => 'text/css',
        'htm|html' => 'text/html',
        'vtt' => 'text/vtt',
        'dfxp' => 'application/ttaf+xml',
        // Audio formats.
        'mp3|m4a|m4b' => 'audio/mpeg',
        'ra|ram' => 'audio/x-realaudio',
        'wav' => 'audio/wav',
        'ogg|oga' => 'audio/ogg',
        'mid|midi' => 'audio/midi',
        'wma' => 'audio/x-ms-wma',
        'wax' => 'audio/x-ms-wax',
        'mka' => 'audio/x-matroska',
        // Misc application formats.
        'rtf' => 'application/rtf',
        'js' => 'application/javascript',
        'pdf' => 'application/pdf',
        'swf' => 'application/x-shockwave-flash',
        'class' => 'application/java',
        'tar' => 'application/x-tar',
        'zip' => 'application/zip',
        'gz|gzip' => 'application/x-gzip',
        'rar' => 'application/rar',
        '7z' => 'application/x-7z-compressed',
        'exe' => 'application/x-msdownload',
        'psd' => 'application/octet-stream',
        'xcf' => 'application/octet-stream',
        // MS Office formats.
        'doc' => 'application/msword',
        'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
        'wri' => 'application/vnd.ms-write',
        'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
        'mdb' => 'application/vnd.ms-access',
        'mpp' => 'application/vnd.ms-project',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
        'oxps' => 'application/oxps',
        'xps' => 'application/vnd.ms-xpsdocument',
        // OpenOffice formats.
        'odt' => 'application/vnd.oasis.opendocument.text',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        // WordPerfect formats.
        'wp|wpd' => 'application/wordperfect',
        // iWork formats.
        'key' => 'application/vnd.apple.keynote',
        'numbers' => 'application/vnd.apple.numbers',
        'pages' => 'application/vnd.apple.pages',
    ];
}

function cp_check_filetype($filename, $mimes = null)
{
    if (empty($mimes)) {
        $mimes = cp_get_allowed_mime_types();
    }
    $type = false;
    $ext = false;

    foreach ($mimes as $ext_preg => $mime_match) {
        $ext_preg = '!\.(' . $ext_preg . ')$!i';
        if (preg_match($ext_preg, $filename, $ext_matches)) {
            $type = $mime_match;
            $ext = $ext_matches[1];
            break;
        }
    }

    return compact('ext', 'type');
}

function cp_sanitize_file_name($filename)
{
    $filename_raw = $filename;
    $special_chars = [
        '?',
        '[',
        ']',
        '/',
        '\\',
        '=',
        '<',
        '>',
        ':',
        ';',
        ',',
        "'",
        '"',
        '&',
        '$',
        '#',
        '*',
        '(',
        ')',
        '|',
        '~',
        '`',
        '!',
        '{',
        '}',
        '%',
        '+',
        chr(0),
    ];
    $special_chars = cp_apply_filters('cp_sanitize_file_name_chars', $special_chars, $filename_raw);
    $filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
    $filename = str_replace($special_chars, '', $filename);
    $filename = str_replace(['%20', '+'], '-', $filename);
    $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
    $filename = trim($filename, '.-_');

    if (false === strpos($filename, '.')) {
        $mime_types = cp_get_mime_types();
        $filetype = cp_check_filetype('test.' . $filename, $mime_types);
        if ($filetype['ext'] === $filename) {
            $filename = 'unnamed-file.' . $filetype['ext'];
        }
    }

    // Split the filename into a base and extension[s]
    $parts = explode('.', $filename);

    // Return if only one extension
    if (count($parts) <= 2) {
        return cp_apply_filters('cp_sanitize_file_name', $filename, $filename_raw);
    }

    // Process multiple extensions
    $filename = array_shift($parts);
    $extension = array_pop($parts);
    $mimes = cp_get_allowed_mime_types();

    // Loop over any intermediate extensions. Postfix them with a trailing underscore
    // if they are a 2 - 5 character long alpha string not in the extension whitelist.
    foreach ((array) $parts as $part) {
        $filename .= '.' . $part;

        if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
            $allowed = false;
            foreach ($mimes as $ext_preg => $mime_match) {
                $mime_match &= true;
                $ext_preg = '!^(' . $ext_preg . ')$!i';
                if (preg_match($ext_preg, $part)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                $filename .= '_';
            }
        }
    }
    $filename .= '.' . $extension;

    return cp_apply_filters('cp_sanitize_file_name', $filename, $filename_raw);
}

// MAGIC QUOTES
function cp_add_magic_quotes($array)
{
    foreach ((array) $array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = cp_add_magic_quotes($v);
        } else {
            $array[$k] = addslashes($v);
        }
    }

    return $array;
}
function cp_magic_quotes()
{
    ${'_GET'} = cp_add_magic_quotes(${'_GET'});
    ${'_POST'} = cp_add_magic_quotes(${'_POST'});
    $_COOKIE = cp_add_magic_quotes($_COOKIE);
    $_SERVER = cp_add_magic_quotes($_SERVER);

    // Force REQUEST to be GET + POST.
    $_REQUEST = array_merge(${'_GET'}, ${'_POST'});
}

function cp_is_admin()
{
    $ct = Context::getContext()->controller->controller_type;

    return $ct == 'moduleadmin' || $ct == 'admin';
}

function cp_get_current_user_id()
{
    $context = Context::getContext();

    return $context->employee ? $context->employee->id : 0;
}

function cp_get_index()
{
    $data = Db::getInstance()->getValue('SELECT `data` FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `id` = -1');

    return $data ? json_decode($data, true) : [];
}

function cp_update_index($data)
{
    return Db::getInstance()->insert(
        'creativepopup_revisions',
        [
            'id' => -1,
            'popup_id' => 0,
            'author' => 0,
            'data' => json_encode($data),
            'date_c' => time(),
        ],
        false,
        true,
        Db::REPLACE
    );
}

function cp_get_option_key($option)
{
    if (version_compare(_PS_VERSION_, '1.6', '<') && strlen($option) > 32) {
        // option key must have max 32 chars on PS v1.5.x
        return strtoupper(md5($option));
    }

    return strtoupper(str_replace('-', '_', $option));
}

function cp_get_option($option, $default = false)
{
    if ('timezone_string' == $option) {
        return date_default_timezone_get();
    }
    $res = Configuration::get(cp_get_option_key($option));

    return $res === false ? $default : json_decode($res, true);
}

function cp_add_option($option, $value)
{
    return Configuration::updateValue(cp_get_option_key($option), json_encode($value));
}

function cp_update_option($option, $value)
{
    return Configuration::updateValue(cp_get_option_key($option), json_encode($value));
}

function cp_delete_option($option)
{
    return Configuration::deleteByName(cp_get_option_key($option));
}

$GLOBALS['cp_style'] = [];

function cp_enqueue_style($handle, $src = false, $deps = [], $ver = false, $media = 'all')
{
    if (!empty($deps) && _PS_MODE_DEV_) {
        throw new Exception('TODO');
    }
    if ($src) {
        $css = ['src' => $src, 'ver' => $ver, 'media' => $media];
        $GLOBALS['cp_style'][$handle] = $css;
    } elseif (isset($GLOBALS['cp_style'][$handle])) {
        $css = $GLOBALS['cp_style'][$handle];
    }
    if (isset($css)) {
        $ctrl = Context::getContext()->controller;
        $v = $css['ver'] ? '?v=' . $css['ver'] : '';

        if (method_exists($ctrl, 'registerStylesheet')) {
            // @phpstan-ignore-next-line
            if ((Configuration::get('PS_CSS_THEME_CACHE') || _MEDIA_SERVER_1_) && strpos($css['src'], '://') === false) {
                $src = Tools::substr($css['src'], Tools::strlen(__PS_BASE_URI__));
            } else {
                $css['server'] = 'remote';
                $src = $css['src'] . $v;
            }
            $ctrl->registerStylesheet($handle, $src, $css);
        } else {
            $ctrl->css_files[$css['src'] . $v] = $css['media'];
        }
    }
}

function cp_register_style($handle, $src, $deps = [], $ver = false, $media = 'all')
{
    $GLOBALS['cp_style'][$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver, 'media' => $media];
}

$GLOBALS['cp_script'] = [];

function cp_enqueue_script($handle, $src = false, $deps = [], $ver = false)
{
    if (!empty($deps) && 0) {
        throw new Exception('TODO');
    }
    if ($src) {
        $js = ['src' => $src, 'ver' => $ver, 'priority' => CP_SCRIPTS_PRIORITY];
        $GLOBALS['cp_script'][$handle] = $js;
    } elseif (isset($GLOBALS['cp_script'][$handle])) {
        $js = $GLOBALS['cp_script'][$handle];
    }
    if (isset($js) && empty($js['added'])) {
        $GLOBALS['cp_script'][$handle]['added'] = true;
        $ctrl = Context::getContext()->controller;
        $v = $js['ver'] ? '?v=' . $js['ver'] : '';

        if (method_exists($ctrl, 'registerJavascript')) {
            if (CP_LOAD_UNPACKED && strpos($js['src'], '/js/core/') && basename($js['src']) != 'greensock.js') {
                $js['src'] = preg_replace('~\.js$~', '.unpacked.js', $js['src']);
            }
            // @phpstan-ignore-next-line
            if (Configuration::get('PS_JS_THEME_CACHE') || _MEDIA_SERVER_1_) {
                $src = Tools::substr($js['src'], Tools::strlen(__PS_BASE_URI__));
            } else {
                $js['server'] = 'remote';
                $src = $js['src'] . $v;
            }
            $ctrl->registerJavascript($handle, $src, $js);
        } else {
            if (!cp_is_admin() && preg_match('~/(\w+\.)?((?:\w+\.)+js)$~', $js['src'], $match)) {
                foreach ($ctrl->js_files as $jsFile) {
                    if (strpos($jsFile, $match[2]) !== false) {
                        return;
                    }
                }
            }
            if (CP_LOAD_UNPACKED && strpos($js['src'], '/js/core/') && basename($js['src']) != 'greensock.js') {
                $js['src'] = preg_replace('~\.js$~', '.unpacked.js', $js['src']);
            }
            $ctrl->js_files[] = $js['src'] . $v;
        }
    }
}

function cp_register_script($handle, $src, $deps = [], $ver = false)
{
    $GLOBALS['cp_script'][$handle] = [
        'src' => $src,
        'deps' => $deps,
        'ver' => $ver,
        'priority' => CP_SCRIPTS_PRIORITY,
    ];
}

$GLOBALS['ls_local'] = [];

function cp_localize_script($handle, $name, $data)
{
    if (version_compare(_PS_VERSION_, '1.6.0.11', '>=')) {
        Media::addJsDef([$name => $data]);
    } else {
        $GLOBALS['ls_local'][$handle] = "var $name = " . json_encode($data) . ';';
    }
}

function cp_referer()
{
    $protocol = Tools::getShopProtocol();
    $id_shop = Configuration::getGlobalValue('PS_SHOP_DEFAULT');

    return $protocol . ('http://' === $protocol ? ShopUrl::getMainShopDomain($id_shop) : ShopUrl::getMainShopDomainSSL($id_shop));
}

function cp_add_url_token($path)
{
    return preg_replace_callback('/controller=([^&]+)/', 'cp_add_url_token_helper', $path);
}
function cp_add_url_token_helper($match)
{
    return parse_url(Context::getContext()->link->getAdminLink($match[1]), PHP_URL_QUERY);
}
function cp_admin_url($path = '', $js = false)
{
    $path = cp_add_url_token($path);

    return $js ? $path : htmlentities($path, ENT_COMPAT | ENT_HTML401, 'UTF-8');
}
function cp_redirect($location)
{
    Tools::redirectAdmin(cp_add_url_token($location));
}

function cp_urlencode_deep($value)
{
    $value = is_array($value) ? array_map('cp_urlencode_deep', $value) : urlencode($value);

    return $value;
}

function cp_add_query_arg()
{
    $args = func_get_args();
    if (is_array($args[0])) {
        if (count($args) < 2 || false === $args[1]) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $args[1];
        }
    } else {
        if (count($args) < 3 || false === $args[2]) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $args[2];
        }
    }

    if ($frag = strstr($uri, '#')) {
        $uri = substr($uri, 0, -strlen($frag));
    } else {
        $frag = '';
    }

    if (0 === stripos($uri, 'http://')) {
        $protocol = 'http://';
        $uri = substr($uri, 7);
    } elseif (0 === stripos($uri, 'https://')) {
        $protocol = 'https://';
        $uri = substr($uri, 8);
    } else {
        $protocol = '';
    }

    if (strpos($uri, '?') !== false) {
        list($base, $query) = explode('?', $uri, 2);
        $base .= '?';
    } elseif ($protocol || strpos($uri, '=') === false) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    $qs = [];
    parse_str($query, $qs);
    $qs = cp_urlencode_deep($qs); // this re-URL-encodes things that were already in the query string
    if (is_array($args[0])) {
        $kayvees = $args[0];
        $qs = array_merge($qs, $kayvees);
    } else {
        $qs[$args[0]] = $args[1];
    }

    foreach ($qs as $k => $v) {
        if ($v === false) {
            unset($qs[$k]);
        }
    }

    $ret = cp_build_query($qs);
    $ret = trim($ret, '?');
    $ret = preg_replace('#=(&|$)#', '$1', $ret);
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim($ret, '?');

    return $ret;
}

function cp_http_build_query($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
{
    $ret = [];

    foreach ((array) $data as $k => $v) {
        if ($urlencode) {
            $k = urlencode($k);
        }
        if (is_int($k) && $prefix != null) {
            $k = $prefix . $k;
        }
        if (!empty($key)) {
            $k = $key . '%5B' . $k . '%5D';
        }
        if ($v === null) {
            continue;
        } elseif ($v === false) {
            $v = '0';
        }

        if (is_array($v) || is_object($v)) {
            array_push($ret, cp_http_build_query($v, '', $sep, $k, $urlencode));
        } elseif ($urlencode) {
            array_push($ret, $k . '=' . urlencode($v));
        } else {
            array_push($ret, $k . '=' . $v);
        }
    }

    if (null === $sep) {
        $sep = '&';
    }

    return implode($sep, $ret);
}

function cp_build_query($data)
{
    return cp_http_build_query($data, null, '&', '', false);
}

function cp_http()
{
    $context = Context::getContext();
    $module_key = $context->controller->module->module_key;

    return [
        'user_agent' => "{$_SERVER['SERVER_SOFTWARE']} PrestaShop/" . _PS_VERSION_,
        'max_redirects' => 5,
        'header' => [
            'Cache-Control: no-cache',
            'Cookie: ' . md5(substr(_COOKIE_KEY_, 0, 24) . $module_key) . '=' . hash('sha256', _COOKIE_IV_ . date('Y-m-d')),
            'Pragma: no-cache',
            'Referer: ' . cp_referer(),
        ],
    ];
}

require_once _PS_MODULE_DIR_ . 'creativepopup/base/classes/CpCache.phtml';

function cp_set_transient($transient, $value, $expiration = 0)
{
    $expiration = (int) $expiration;
    try {
        $result = CpCache::getInstance()->set($transient, $value, $expiration);
    } catch (Exception $ex) {
        $result = false;
    }

    return $result;
}

function cp_get_transient($transient)
{
    try {
        $result = CpCache::getInstance()->get($transient);
    } catch (Exception $ex) {
        $result = false;
    }

    return $result;
}

function cp_delete_transient($transient)
{
    try {
        $result = CpCache::getInstance()->delete($transient);
    } catch (Exception $ex) {
        $result = false;
    }

    return $result;
}

function cp_upload_dir()
{
    return [
        'basedir' => _PS_IMG_DIR_,
        'baseurl' => _PS_IMG_,
    ];
}

function cp_get_options($arr, $key, $val = 'name', $all = 0)
{
    $options = [];
    if ($all !== false) {
        $options[$all] = cp__('- All -');
    }
    foreach ($arr as &$opt) {
        $options[$opt[$key]] = $opt[$val];
    }

    return $options;
}

function cp_get_pages(&$cats, &$pages)
{
    $arr = [];

    if (isset($cats, $pages)) {
        foreach (['cats', 'pages'] as $key) {
            $var = &$$key;
            if (in_array('all', $var)) {
                $arr[$key == 'cats' ? 'cat' : 'cms'] = 'all';
            } else {
                foreach ($var as &$val) {
                    $v = explode('-', $val);
                    if (count($v) > 1) {
                        if (empty($arr[$v[0]])) {
                            $arr[$v[0]] = [];
                        }
                        $arr[$v[0]][] = (int) $v[1];
                    } elseif ($val) {
                        $arr[$val] = 1;
                    }
                }
            }
        }
    }

    return $arr;
}

function cp_get_image_type_name($name)
{
    return method_exists('ImageType', 'getFormattedName') ? ImageType::getFormattedName($name) : (
        method_exists('ImageType', 'getFormatedName') ? ImageType::{'getFormatedName'}($name) : $name . '_default'
    );
}

/* ADD ACTIONS / FILTERS */

function cp_popup_store()
{
    $context = Context::getContext();

    if ($license = Configuration::getGlobalValue('CP_LICENSE')) {
        exit(json_encode(['key' => $license, 'success' => 1]));
    }
    $msg = cp__('Premium start templates are only available after license activation.');
    $btn = ' <a href="' . $context->link->getAdminLink('AdminCreativePopup') . '&action=activate" style="float:none; display:inline-block">' . cp__('Activate') . '</a>';
    exit(json_encode(['msg' => $msg . $btn, 'success' => 0]));
}
cp_add_action('ajax_cp_popup_store', 'cp_popup_store');

function cp_remote_get($url, $args = [])
{
    $http = array_merge(cp_http(), [
        'method' => 'GET',
        'timeout' => empty($args['timeout']) ? 5 : $args['timeout'],
    ]);

    if (!empty($args['body'])) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($args['body']);
    }

    if (function_exists('curl_version')) {
        curl_setopt_array($ch = curl_init($url), [
            CURLOPT_HTTPHEADER => (array) $http['header'],
            CURLOPT_USERAGENT => $http['user_agent'],
            CURLOPT_MAXREDIRS => $http['max_redirects'],
            CURLOPT_TIMEOUT => $http['timeout'],
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if (false !== $resp) {
            return $resp;
        }
    }

    $http['header'] = implode("\r\n", $http['header']);

    return in_array(ini_get('allow_url_fopen'), ['On', 'on', '1'])
        ? @call_user_func('file_get_contents', $url, false, stream_context_create(['http' => $http]))
        : false;
}

function cp_get_modules()
{
    $modules = [];
    $dirs = glob(_PS_MODULE_DIR_ . '*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        $modules[basename($dir)] = 1;
    }

    return $modules;
}

function cp_get_media_link($filepath)
{
    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
        return Context::getContext()->link->getMediaLink($filepath);
    }
    // PS v1.5
    static $ssl = null;

    if ($ssl === null) {
        $ssl = (bool) Configuration::get('PS_SSL_ENABLED');
    }

    return Tools::getProtocol($ssl) . Tools::getMediaServer($filepath) . $filepath;
}

function cp_pre_parse_defaults($data)
{
    if (!empty($data['properties']['backgroundimage']) && $data['properties']['backgroundimage'][0] == '/') {
        $data['properties']['backgroundimage'] = cp_get_media_link($data['properties']['backgroundimage']);
    }
    foreach ($data['layers'] as &$page) {
        if (!empty($page['properties']['background']) && $page['properties']['background'][0] == '/') {
            $page['properties']['background'] = cp_get_media_link($page['properties']['background']);
        }
        if (!empty($page['properties']['thumbnail']) && $page['properties']['thumbnail'][0] == '/') {
            $page['properties']['thumbnail'] = cp_get_media_link($page['properties']['thumbnail']);
        }
        if (!empty($page['sublayers'])) {
            foreach ($page['sublayers'] as &$layer) {
                if (!empty($layer['image']) && $layer['image'][0] == '/') {
                    $layer['image'] = cp_get_media_link($layer['image']);
                }
                if (!empty($layer['poster']) && $layer['poster'][0] == '/') {
                    $layer['poster'] = cp_get_media_link($layer['poster']);
                }
            }
        }
    }

    return $data;
}
cp_add_filter('cp_pre_parse_defaults', 'cp_pre_parse_defaults');

function cp_filter_get_template_args($body_args)
{
    if ($license_key = Configuration::getGlobalValue('CP_LICENSE')) {
        $body_args['license'] = $license_key;
        $body_args['domain'] = ShopUrl::{'getMainShopDomain' . (Tools::getShopProtocol() === 'https://' ? 'SSL' : '')}(
            Configuration::getGlobalValue('PS_SHOP_DEFAULT')
        );
    }

    return $body_args;
}
cp_add_filter('cp_download_popup/body_args', 'cp_filter_get_template_args');
