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

$pages = &$pages;
$cpDefaults = &$cpDefaults;
$popup = [];

// Filter to override the defaults
if (cp_has_filter('cp_override_defaults')) {
    $newDefaults = cp_apply_filters('cp_override_defaults', $cpDefaults);
    if (!empty($newDefaults) && is_array($newDefaults)) {
        $cpDefaults = $newDefaults;
        unset($newDefaults);
    }
}

// Hook to alter popup data *before* filtering with defaults
if (cp_has_filter('cp_pre_parse_defaults')) {
    $result = cp_apply_filters('cp_pre_parse_defaults', $pages);
    if (!empty($result) && is_array($result)) {
        $pages = $result;
    }
}

// Filter popup data with defaults
$pages['properties'] = cp_apply_filters('cp_parse_defaults', $cpDefaults['slider'], $pages['properties']);
$skin = !empty($pages['properties']['attrs']['skin'])
    ? $pages['properties']['attrs']['skin']
    : $cpDefaults['slider']['skin']['value']
;
$pages['properties']['attrs']['skinsPath'] = _MODULE_DIR_ . 'creativepopup/views/css/core/skins/';

if (isset($pages['properties']['autoPauseSlideshow'])) {
    switch ($pages['properties']['autoPauseSlideshow']) {
        case 'auto':
            $pages['properties']['autoPauseSlideshow'] = 'auto';
            break;
        case 'enabled':
            $pages['properties']['autoPauseSlideshow'] = true;
            break;
        case 'disabled':
            $pages['properties']['autoPauseSlideshow'] = false;
            break;
    }
}

if (isset($pages['properties']['attrs']['popupShowOnClick'])) {
    $pages['properties']['attrs']['popupShowOnClick'] = addslashes($pages['properties']['attrs']['popupShowOnClick']);
}

// Pages and layers
if (isset($pages['layers']) && is_array($pages['layers'])) {
    foreach ($pages['layers'] as $pagekey => $page) {
        if (!empty($page['properties'])) {
            $popup['slides'][$pagekey] = cp_apply_filters(
                'cp_parse_defaults',
                $cpDefaults['slides'],
                $page['properties']
            );
        }
        if (isset($page['sublayers']) && is_array($page['sublayers'])) {
            foreach ($page['sublayers'] as $layerkey => $layer) {
                $layer['styles'] = stripslashes($layer['styles']);
                $layer['transition'] = stripslashes($layer['transition']);

                if (!empty($layer['transition'])) {
                    $layer = array_merge($layer, json_decode(stripslashes($layer['transition']), true));
                }

                if (!empty($layer['styles'])) {
                    $layerStyles = json_decode($layer['styles'], true);
                    if ($layerStyles === null) {
                        $layerStyles = json_decode(stripslashes($layer['styles']), true);
                    }
                    $layer['styles'] = $layerStyles;
                }

                if (!empty($layer['top'])) {
                    $layer['styles']['top'] = $layer['top'];
                }

                if (!empty($layer['left'])) {
                    $layer['styles']['left'] = $layer['left'];
                }

                // v6.5.6: Compatibility mode for media layers that used the
                // old checkbox based media settings.
                if (isset($layer['controls'])) {
                    if (true === $layer['controls']) {
                        $layer['controls'] = 'auto';
                    } elseif (false === $layer['controls']) {
                        $layer['controls'] = 'disabled';
                    }
                }

                $popup['slides'][$pagekey]['layers'][$layerkey] = cp_apply_filters(
                    'cp_parse_defaults',
                    $cpDefaults['layers'],
                    $layer
                );
            }
        }
    }
}

// Hook to alter popup data *after* filtering with defaults
if (cp_has_filter('cp_post_parse_defaults')) {
    $result = cp_apply_filters('cp_post_parse_defaults', $pages);
    if (!empty($result) && is_array($result)) {
        $pages = $result;
    }
}
