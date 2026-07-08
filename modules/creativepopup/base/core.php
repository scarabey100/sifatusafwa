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

/* ACTIONS */

// Basic configuration
define('CP_PLUGIN_VERSION', '1.6.11');

// Path info
define('CP_ROOT_PATH', __DIR__);

// Shared
include CP_ROOT_PATH . '/cp/scripts.php';
include CP_ROOT_PATH . '/cp/menus.php';
include CP_ROOT_PATH . '/cp/hooks.php';
include CP_ROOT_PATH . '/cp/shortcodes.php';
include CP_ROOT_PATH . '/includes/utils.php';
include CP_ROOT_PATH . '/classes/Posts.php';
include CP_ROOT_PATH . '/classes/Instances.php';
include CP_ROOT_PATH . '/classes/Sources.php';
include CP_ROOT_PATH . '/classes/Popups.php';

// Back-end only
if (cp_is_admin()) {
    include CP_ROOT_PATH . '/cp/actions.php';
    include CP_ROOT_PATH . '/cp/notices.php';
    include CP_ROOT_PATH . '/classes/Revisions.php';

    CpRevisions::init();
}

// Add default skins.
// Reads all sub-directories (individual skins) from the given path.
CpSources::addSkins(_PS_MODULE_DIR_ . 'creativepopup/views/css/core/skins/');

// Popup
CpPopups::init();
CpPopups::setup();
