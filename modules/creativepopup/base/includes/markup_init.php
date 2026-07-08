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
$popupID = &$popupID;

// Preload Skin
$skin = empty($pages['properties']['attrs']['skin']) ? 'noskin' : $pages['properties']['attrs']['skin'];
cp_enqueue_style('cp-skin-' . $skin, CP_VIEWS_URL . "css/core/skins/$skin/skin.css", false, CP_PLUGIN_VERSION);

// Get init code
$init = [];
foreach ($pages['properties']['attrs'] as $key => $val) {
    if (is_bool($val)) {
        $val = $val ? 'true' : 'false';
        $init[] = $key . ': ' . $val;
    } elseif (is_numeric($val)) {
        $init[] = $key . ': ' . $val;
    } else {
        $init[] = "$key: '$val'";
    }
}

// Popup
if (!empty($pages['properties']['attrs']['type']) && $pages['properties']['attrs']['type'] === 'popup') {
    $cpPlugins[] = 'popup';
}

if (!empty($cpPlugins)) {
    $init[] = 'plugins: ' . json_encode(array_values(array_unique($cpPlugins)));
}

$init = implode(', ', $init);

$cpInit[] = '<js>' . PHP_EOL;
$cpInit[] = 'cpjq("#' . $popupID . '")';
if (!empty($pages['callbacks']) && is_array($pages['callbacks'])) {
    foreach ($pages['callbacks'] as $event => $function) {
        $cpInit[] = '.on("' . $event . '", ' . stripslashes($function) . ')';
    }
}
$cpInit[] = '.creativePopup({' . $init . '});' . PHP_EOL;
$cpInit[] = '</js>';
