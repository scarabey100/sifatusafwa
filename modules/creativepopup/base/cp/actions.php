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

cp_add_action('init', 'cp_register_form_actions');
function cp_register_form_actions()
{
    cp_add_action('save_post', 'cp_delete_caches');
    if (!empty(Context::getContext()->employee->id)) {
        // Popups list layout
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'layout') {
            $type = (${'_GET'}['type'] === 'list') ? 'list' : 'grid';
            cp_update_user_meta(cp_get_current_user_id(), 'cp-popups-layout', $type);
            cp_redirect('index.php?controller=AdminCreativePopup');
        }

        // Remove popup
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'remove') {
            cp_add_action('admin_init', 'cp_remove_popup');
        }

        // Restore popup
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'restore') {
            cp_add_action('admin_init', 'cp_restore_popup');
        }

        // Duplicate popup
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'duplicate') {
            cp_add_action('admin_init', 'cp_duplicate_popup');
        }

        // Export popup
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'export') {
            ${'_POST'}['sliders'] = [(int) ${'_GET'}['id']];
            ${'_POST'}['cp-export'] = true;
        }

        // Empty caches
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'empty_caches') {
            cp_add_action('admin_init', 'cp_empty_caches');
        }

        // Update Library
        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'update_store') {
            cp_delete_option('cp-store-last-updated');
            cp_redirect('index.php?controller=AdminCreativePopup&message=updateStore');
        }

        // Popup list bulk actions
        if (isset(${'_POST'}['cp-bulk-action'])) {
            cp_add_action('admin_init', 'cp_popups_bulk_action');
        }

        // Add new popup
        if (isset(${'_POST'}['cp-add-new-popup'])) {
            cp_add_action('admin_init', 'cp_add_new_popup');
        }

        // Google Fonts
        if (isset(${'_POST'}['cp-save-google-fonts'])) {
            cp_add_action('admin_init', 'cp_save_google_fonts');
        }

        // Advanced settings
        if (isset(${'_POST'}['cp-save-advanced-settings'])) {
            cp_add_action('admin_init', 'cp_save_advanced_settings');
        }

        // Access permission
        if (isset(${'_POST'}['cp-access-permission'])) {
            cp_add_action('admin_init', 'cp_save_access_permissions');
        }

        // Import popups
        if (isset(${'_POST'}['cp-import'])) {
            cp_add_action('admin_init', 'cp_import_popups');
        }

        // Export popups
        if (isset(${'_POST'}['cp-export'])) {
            cp_add_action('admin_init', 'cp_export_popups');
        }

        // Revisions Options
        if (isset(${'_POST'}['cp-revisions-options'])) {
            cp_add_action('admin_init', 'cp_save_revisions_options');
        }

        // Revert popup
        if (isset(${'_POST'}['cp-revert-popup'])) {
            cp_add_action('admin_init', 'cp_revert_popup');
        }

        // Custom CSS editor
        if (isset(${'_POST'}['cp-user-css'])) {
            cp_add_action('admin_init', 'cp_save_user_css');
        }

        // Skin editor
        if (isset(${'_POST'}['cp-user-skins'])) {
            cp_add_action('admin_init', 'cp_save_user_skin');
        }

        // Transition builder
        if (isset(${'_POST'}['cp-user-transitions'])) {
            cp_add_action('admin_init', 'cp_save_user_transitions');
        }

        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'hide-important-notice') {
            $storeData = cp_get_option('cp-store-data', false);
            if (!empty($storeData) && !empty($storeData['important_notice']['date'])) {
                cp_update_option('cp-last-important-notice', $storeData['important_notice']['date']);
            }

            cp_redirect('index.php?controller=AdminCreativePopup');
        }

        if (isset(${'_GET'}['page']) && ${'_GET'}['page'] == 'popups' && isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'hide-support-notice') {
            cp_update_option('cp-show-support-notice', 0);
            cp_redirect('index.php?controller=AdminCreativePopup');
        }

        // AJAX functions
        cp_add_action('ajax_cp_save_popup', 'cp_save_popup');
        cp_add_action('ajax_cp_save_screen_options', 'cp_save_screen_options');
        cp_add_action('ajax_cp_get_mce_popups', 'cp_get_mce_popups');
        cp_add_action('ajax_cp_get_mce_pages', 'cp_get_mce_pages');
        cp_add_action('ajax_cp_get_mce_layers', 'cp_get_mce_layers');
        cp_add_action('ajax_cp_get_post_details', 'cp_get_post_details');
        cp_add_action('ajax_cp_get_search_posts', 'cp_get_search_posts');
        cp_add_action('ajax_cp_get_taxonomies', 'cp_get_taxonomies');
        cp_add_action('ajax_cp_upload_from_url', 'cp_upload_from_url');
        cp_add_action('ajax_cp_store_opened', 'cp_store_opened');
        cp_add_action('ajax_cp_change_status', 'cp_change_status');
    }
}

// Template store last viewed
function cp_store_opened()
{
    cp_update_user_meta(cp_get_current_user_id(), 'cp-store-last-viewed', date('Y-m-d'));
    exit;
}

function cp_delete_caches()
{
    try {
        CpCache::getInstance()->delete('cp-popup-data-*');
    } catch (Exception $ex) {
        // TODO
    }
}

function cp_empty_caches()
{
    cp_delete_caches();
    cp_redirect('index.php?controller=AdminCreativePopup&message=cacheEmpty');
}

function cp_add_new_popup()
{
    $id = CpInstances::add(${'_POST'}['title']);
    cp_redirect('index.php?controller=AdminCreativePopup&action=edit&id=' . $id . '&showsettings=1');
}

function cp_popups_bulk_action()
{
    if (${'_POST'}['action'] === 'publish') {
        // Publish
        if (!empty(${'_POST'}['sliders']) && is_array(${'_POST'}['sliders'])) {
            CpInstances::changeStatus(${'_POST'}['sliders'], 1);
            cp_redirect('index.php?controller=AdminCreativePopup&message=publishSuccess');
        } else {
            cp_redirect('index.php?controller=AdminCreativePopup&message=publishSelectError&error=1');
        }
    } elseif (${'_POST'}['action'] === 'unpublish') {
        // Unpublish
        if (!empty(${'_POST'}['sliders']) && is_array(${'_POST'}['sliders'])) {
            CpInstances::changeStatus(${'_POST'}['sliders'], 0);
            cp_redirect('index.php?controller=AdminCreativePopup&message=unpublishSuccess');
        } else {
            cp_redirect('index.php?controller=AdminCreativePopup&message=unpublishSelectError&error=1');
        }
    } elseif (${'_POST'}['action'] === 'export') {
        // Export
        cp_export_popups();
    } elseif (${'_POST'}['action'] === 'remove') {
        // Remove
        if (!empty(${'_POST'}['sliders']) && is_array(${'_POST'}['sliders'])) {
            foreach (${'_POST'}['sliders'] as $item) {
                CpInstances::remove((int) $item);
            }
            cp_redirect('index.php?controller=AdminCreativePopup&message=removeSuccess');
        } else {
            cp_redirect('index.php?controller=AdminCreativePopup&message=removeSelectError&error=1');
        }
    } elseif (${'_POST'}['action'] === 'delete') {
        // Delete
        if (!empty(${'_POST'}['sliders']) && is_array(${'_POST'}['sliders'])) {
            foreach (${'_POST'}['sliders'] as $item) {
                CpInstances::delete((int) $item);
                CpRevisions::clear((int) $item);
            }
            cp_redirect('index.php?controller=AdminCreativePopup&message=deleteSuccess');
        } else {
            cp_redirect('index.php?controller=AdminCreativePopup&message=deleteSelectError&error=1');
        }
    } elseif (${'_POST'}['action'] === 'restore') {
        // Restore
        if (!empty(${'_POST'}['sliders']) && is_array(${'_POST'}['sliders'])) {
            foreach (${'_POST'}['sliders'] as $item) {
                CpInstances::restore((int) $item);
            }
            cp_redirect('index.php?controller=AdminCreativePopup&message=restoreSuccess');
        } else {
            cp_redirect('index.php?controller=AdminCreativePopup&message=restoreSelectError&error=1');
        }
    } elseif (${'_POST'}['action'] === 'merge') {
        // Merge
        if (!isset(${'_POST'}['sliders'][1]) || !is_array(${'_POST'}['sliders'])) {
            // Error check
            cp_redirect('index.php?controller=AdminCreativePopup&error=1&message=mergeSelectError');
        }

        if ($popups = CpInstances::find(${'_POST'}['sliders'])) {
            $ids = [];
            $data = [];
            foreach ($popups as $key => $item) {
                // Get IDs
                $ids[] = '#' . $item['id'];

                // Merge popups
                if ($key === 0) {
                    $data = $item['data'];
                } else {
                    $data['layers'] = array_merge($data['layers'], $item['data']['layers']);
                }
            }

            // Save as new
            $name = cp__('Merged popups of ') . implode(', ', $ids);
            $data['properties']['title'] = $name;
            CpInstances::add($name, $data);
        }

        cp_redirect('index.php?controller=AdminCreativePopup&message=mergeSuccess');
    }
}

function cp_save_google_fonts()
{
    // Build object to save
    $fonts = [];
    if (!empty(${'_POST'}['fontsData']) && is_array(${'_POST'}['fontsData'])) {
        foreach (${'_POST'}['fontsData'] as $val) {
            if (!empty($val['urlParams'])) {
                $fonts[] = [
                    'param' => $val['urlParams'],
                    'admin' => isset($val['onlyOnAdmin']) ? true : false,
                ];
            }
        }
    }

    // Google Fonts character sets
    array_shift(${'_POST'}['scripts']);
    cp_update_option('cp-google-font-scripts', ${'_POST'}['scripts']);

    // Save & redirect back
    cp_update_option('cp-google-fonts', $fonts);
    cp_redirect('index.php?controller=AdminCreativePopup&message=googleFontsUpdated');
}

function cp_save_advanced_settings()
{
    $options = [
        'use_cache',
        'load_unpacked',
        'load_fontawesome',
        'save_history',
        'gsap_sandboxing',
        'force_load_origami',
    ];
    foreach ($options as $item) {
        $prefix = 'gsap_sandboxing' == $item || 'load_fontawesome' == $item ? 'ls_' : 'cp_';
        cp_update_option($prefix . $item, (int) array_key_exists($item, ${'_POST'}));
    }

    $tpl = ${'_POST'}['init_type'];
    cp_update_option('cp_init_type', 'script' === $tpl || 'cdata' === $tpl ? $tpl : '');
    cp_update_option('cp_scripts_priority', (int) ${'_POST'}['scripts_priority']);

    cp_redirect('index.php?controller=AdminCreativePopup&message=generalUpdated');
}

function cp_save_screen_options()
{
    ${'_POST'}['options'] = !empty(${'_POST'}['options']) ? ${'_POST'}['options'] : [];
    cp_update_option('cp-screen-options', ${'_POST'}['options']);
    exit;
}

function cp_get_mce_popups()
{
    $popups = CpInstances::find(['limit' => 100]);
    foreach ($popups as $key => $item) {
        $popups[$key]['preview'] = cp_apply_filters('cp_preview_for_popup', $item);
        $popups[$key]['name'] = !empty($item['name']) ? htmlspecialchars($item['name']) : 'Unnamed';
    }

    exit(json_encode($popups));
}

function cp_get_mce_pages()
{
    $sliderID = (int) ${'_GET'}['sliderID'];

    $slider = CpInstances::find($sliderID);
    $slider = $slider['data'];
    $slides = [];

    foreach ($slider['layers'] as $key => $slide) {
        if (!empty($slide['properties']['backgroundId'])) {
            $slides[$key]['preview'] = cp_apply_filters(
                'cp_get_image',
                $slide['properties']['backgroundId'],
                $slide['properties']['background']
            );
        }

        $slides[$key]['name'] = !empty($slide['properties']['title'])
            ? htmlspecialchars(stripslashes($slide['properties']['title']))
            : 'Page #' . ($key + 1)
        ;
    }

    exit(json_encode($slides));
}

function cp_get_mce_layers()
{
    $sliderID = (int) ${'_GET'}['sliderID'];
    $slideIndex = (int) ${'_GET'}['slideIndex'];
    $layers = [];

    $slider = CpInstances::find($sliderID);
    $slider = $slider['data'];

    foreach ($slider['layers'][$slideIndex]['sublayers'] as $key => $layer) {
        // Parse embedded JSON data
        $layer['styles'] = !empty($layer['styles'])
            ? (object) json_decode(stripslashes($layer['styles']), true)
            : new stdClass()
        ;
        $layer['transition'] = !empty($layer['transition'])
            ? (object) json_decode(stripslashes($layer['transition']), true)
            : new stdClass()
        ;
        $layer['html'] = !empty($layer['html']) ? stripslashes($layer['html']) : '';

        // Add 'top', 'left' and 'wordwrap' to the styles object
        if (isset($layer['top'])) {
            $layer['styles']->top = $layer['top'];
            unset($layer['top']);
        }
        if (isset($layer['left'])) {
            $layer['styles']->left = $layer['left'];
            unset($layer['left']);
        }
        if (isset($layer['wordwrap'])) {
            $layer['styles']->wordwrap = $layer['wordwrap'];
            unset($layer['wordwrap']);
        }

        if (!empty($layer['transition']->showuntil)) {
            $layer['transition']->startatout = 'transitioninend + ' . $layer['transition']->showuntil;
            $layer['transition']->startatouttiming = 'transitioninend';
            $layer['transition']->startatoutvalue = $layer['transition']->showuntil;
            unset($layer['transition']->showuntil);
        }

        if (!empty($layer['transition']->parallaxlevel)) {
            $layer['transition']->parallax = true;
        }

        // Custom attributes
        $layer['innerAttributes'] = !empty($layer['innerAttributes'])
            ? (object) $layer['innerAttributes']
            : new stdClass()
        ;
        $layer['outerAttributes'] = !empty($layer['outerAttributes'])
            ? (object) $layer['outerAttributes']
            : new stdClass()
        ;

        // v6.5.6: Convert old checkbox media settings to the new
        // select based options.
        if (isset($layer['transition']->controls)) {
            if (true === $layer['transition']->controls) {
                $layer['transition']->controls = 'auto';
            } elseif (false === $layer['transition']->controls) {
                $layer['transition']->controls = 'disabled';
            }
        }

        $layers[$key] = $layer;

        if (!empty($layer['imageId'])) {
            $layers[$key]['image'] = cp_apply_filters('cp_get_image', $layer['imageId'], $layer['image']);
        }

        if (!empty($layer['posterId'])) {
            $layers[$key]['poster'] = cp_apply_filters('cp_get_image', $layer['posterId'], $layer['poster']);
        }

        $layers[$key]['name'] = !empty($layer['subtitle'])
            ? Tools::substr(htmlspecialchars(stripslashes($layer['subtitle'])), 0, 32)
            : 'Layer #' . ($key + 1)
        ;
    }

    exit(json_encode($layers));
}

function cp_save_popup()
{
    // Vars
    $id = (int) ${'_POST'}['id'];
    $data = ${'_POST'}['sliderData'];

    // Parse popup settings
    $data['properties'] = json_decode(stripslashes(html_entity_decode($data['properties'])), true);

    // Parse page data
    if (!empty($data['layers']) && is_array($data['layers'])) {
        foreach ($data['layers'] as $pageKey => $pageData) {
            $pageData = json_decode(stripslashes($pageData), true);

            if (!empty($pageData['sublayers'])) {
                foreach ($pageData['sublayers'] as $layerKey => $layerData) {
                    if (!empty($layerData['transition'])) {
                        $pageData['sublayers'][$layerKey]['transition'] = addslashes($layerData['transition']);
                    }

                    if (!empty($layerData['styles'])) {
                        $pageData['sublayers'][$layerKey]['styles'] = addslashes($layerData['styles']);
                    }
                }
            }

            $data['layers'][$pageKey] = $pageData;
        }
    }

    $title = $data['properties']['title'];

    // Update the popup
    if (empty($id)) {
        $id = CpInstances::add($title, $data);
    } else {
        CpInstances::update($id, $title, $data);
        // Popup Index
        if (!CpInstances::isDeleted($id)) {
            !empty($data['properties']['status'])
                ? CpPopups::addIndex($id, $data['properties'])
                : CpPopups::removeIndex($id)
            ;
        }

        // remove popup related cookie
        setcookie('cp-popup-' . $id, '', 1, '/');
    }

    // Revisions handling
    if (CpRevisions::$active) {
        $lastRevision = CpRevisions::last($id);

        if (!$lastRevision || $lastRevision->date_c < time() - 60 * CpRevisions::$interval) {
            CpRevisions::add($id, json_encode($data));

            if (CpRevisions::count($id) > CpRevisions::$limit) {
                CpRevisions::shift($id);
            }
        }
    }

    exit(json_encode(['status' => 'ok']));
}

function cp_save_revisions_options()
{
    cp_update_option('cp-revisions-enabled', (int) isset(${'_POST'}['cp-revisions-enabled']));
    cp_update_option('cp-revisions-limit', ${'_POST'}['cp-revisions-limit']);
    cp_update_option('cp-revisions-interval', ${'_POST'}['cp-revisions-interval']);

    if (empty(${'_POST'}['cp-revisions-enabled'])) {
        CpRevisions::truncate();
    }

    cp_redirect('index.php?controller=AdminCreativePopupRevisions');
}

function cp_revert_popup()
{
    $popupId = (int) ${'_POST'}['slider-id'];
    $revisionId = (int) ${'_POST'}['revision-id'];

    CpRevisions::revert($popupId, $revisionId);

    cp_redirect('index.php?controller=AdminCreativePopup&action=edit&id=' . $popupId);
}

/* Action to duplicate popup */

function cp_duplicate_popup()
{
    // Check and get the ID
    if (!isset(${'_GET'}['id'])) {
        return;
    }

    // Get the original popup
    $popup = CpInstances::find((int) ${'_GET'}['id']);
    $data = $popup['data'];

    // Name check
    if (empty($data['properties']['title'])) {
        $data['properties']['title'] = 'Unnamed';
    }

    // Insert the duplicate
    $data['properties']['title'] .= ' copy';
    CpInstances::add($data['properties']['title'], $data);

    // Success
    cp_redirect('index.php?controller=AdminCreativePopup&message=duplicateSuccess');
}

/* Action to remove popup */

function cp_remove_popup()
{
    // Check received data
    if (empty(${'_GET'}['id'])) {
        return false;
    }

    // Remove the popup
    CpInstances::remove((int) ${'_GET'}['id']);

    // Reload page
    cp_redirect('index.php?controller=AdminCreativePopup&message=removeSuccess');
}

/* Action to restore popup */

function cp_restore_popup()
{
    // Check received data
    if (empty(${'_GET'}['id'])) {
        return false;
    }

    // Remove the popup
    CpInstances::restore((int) ${'_GET'}['id']);

    // Reload page
    cp_redirect('index.php?controller=AdminCreativePopup&message=restoreSuccess');

    exit;
}

// PLUGIN USER PERMISSIONS
// -----------------------
function cp_save_access_permissions()
{
    // Get capability
    $capability = 'custom' == ${'_POST'}['custom_role'] ? ${'_POST'}['custom_capability'] : ${'_POST'}['custom_role'];

    // Test value
    if (empty($capability) || empty(Context::getContext()->employee->id)) {
        cp_redirect('index.php?controller=AdminCreativePopup&error=1&message=permissionError');
    } else {
        cp_update_option('cp_custom_capability', $capability);
        cp_redirect('index.php?controller=AdminCreativePopup&message=permissionSuccess');
    }
}

// IMPORT SLIDERS
// --------------
function cp_import_popups()
{
    // Check export file if any
    if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
        cp_redirect('index.php?controller=AdminCreativePopup&error=1&message=importSelectError');
    }

    include CP_ROOT_PATH . '/classes/ImportUtil.php';
    $import = new CpImportUtil($_FILES['import_file']['tmp_name'], $_FILES['import_file']['name']);

    if (!empty($import->lastImportId) && (int) $import->popupCount === 1) {
        // One popup, redirect to editor
        cp_redirect('index.php?controller=AdminCreativePopup&action=edit&id=' . $import->lastImportId);
    } else {
        // Multiple popups, redirect to popup list
        cp_redirect('index.php?controller=AdminCreativePopup&message=importSuccess&popupCount=' . $import->popupCount);
    }
}

// EXPORT SLIDERS
// --------------
function cp_export_popups($popupId = 0)
{
    $zip = null;
    $popups = [];
    // Get popups
    if (!empty($popupId)) {
        $popups = CpInstances::find($popupId);
    } elseif (isset(${'_POST'}['sliders'][0]) && ${'_POST'}['sliders'][0] == -1) {
        $popups = CpInstances::find(['limit' => 500]);
    } elseif (!empty(${'_POST'}['sliders'])) {
        $popups = CpInstances::find(${'_POST'}['sliders']);
    } else {
        cp_redirect('index.php?controller=AdminCreativePopup&error=1&message=exportSelectError');
    }

    // Check results
    if (empty($popups)) {
        cp_redirect('index.php?controller=AdminCreativePopup&error=1&message=exportNotFound');
    }

    if (class_exists('ZipArchive')) {
        include CP_ROOT_PATH . '/classes/ExportUtil.php';
        $zip = new CpExportUtil();
    }

    // Gather popup data
    $data = [];
    foreach ($popups as $item) {
        // init PS specific props
        unset($item['data']['properties']['hook']);
        unset($item['data']['properties']['shop']);
        unset($item['data']['properties']['lang']);
        unset($item['data']['properties']['cats']);
        unset($item['data']['properties']['pages']);
        unset($item['data']['properties']['position']);

        // Gather Google Fonts used in popup
        $item['data']['googlefonts'] = $zip->fontsForPopup($item['data']);

        // Popup settings array for fallback mode
        $data[] = $item['data'];

        // If ZipArchive is available
        if (class_exists('ZipArchive')) {
            // Add popup folder and settings.json
            $name = empty($item['name']) ? 'popup_' . $item['id'] : $item['name'];
            $name = cp_sanitize_file_name($name);
            $zip->addSettings(json_encode($item['data']), $name);

            // Add images?
            if (!isset(${'_POST'}['skip_images'])) {
                $images = $zip->getImagesForPopup($item['data']);
                $images = $zip->getFSPaths($images);
                $zip->addImage($images, $name);
            }
        }
    }

    if (class_exists('ZipArchive')) {
        $zip->download();
    } else {
        $name = 'CreativePopup Export ' . date('Y-m-d') . ' at ' . date('H.i.s') . '.json';
        header('Content-type: application/force-download');
        header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $name) . '"');
        exit(base64_encode(json_encode($data)));
    }
}

// TRANSITION BUILDER
// ------------------
function cp_save_user_css()
{
    // Get target file and content
    $file = _PS_MODULE_DIR_ . 'creativepopup/views/css/custom.css';

    // Attempt to save changes
    if (is_writable(_PS_MODULE_DIR_ . 'creativepopup/views/css')) {
        call_user_func('file_put_contents', $file, stripslashes(${'_POST'}['contents']));
        cp_redirect('index.php?controller=AdminCreativePopupStyle&edited=1');

    // File isn't writable
    } else {
        cp_die(
            cp__("It looks like your files isn't writable, so PHP couldn't make any changes (CHMOD)."),
            cp__('Cannot write to file')
        );
    }
}

// SKIN EDITOR
// -----------
function cp_save_user_skin()
{
    // Error checking
    if (empty(${'_POST'}['skin']) || strpos(${'_POST'}['skin'], '..') !== false) {
        cp_die(cp__("It looks like you haven't selected any skin to edit."), cp__('No skin selected.'));
    }

    // Get skin file and contents
    $skin = CpSources::getSkin(${'_POST'}['skin']);
    $file = $skin['file'];

    // Attempt to write the file
    if (is_writable($file)) {
        call_user_func('file_put_contents', $file, stripslashes(${'_POST'}['contents']));
        cp_redirect('index.php?controller=AdminCreativePopupSkin&skin=' . $skin['handle'] . '&edited=1');
    } else {
        cp_die(
            cp__("It looks like your files isn't writable, so PHP couldn't make any changes (CHMOD)."),
            cp__('Cannot write to file')
        );
    }
}

// TRANSITION BUILDER
// ------------------
function cp_save_user_transitions()
{
    $custom_trs = _PS_MODULE_DIR_ . 'creativepopup/views/js/custom.transitions.js';
    $data = 'var cpCustomTransitions = ' . stripslashes(${'_POST'}['cp-transitions']) . ';';
    call_user_func('file_put_contents', $custom_trs, $data);
    exit('SUCCESS');
}

// --
function cp_get_post_details()
{
    $params = ${'_POST'}['params'];

    $queryArgs = [
        'post_status' => 'publish',
        'limit' => 50,
        'img_size' => null,
    ];

    if (!empty($params['post_orderby'])) {
        $queryArgs['orderby'] = $params['post_orderby'];
    }

    if (!empty($params['post_order'])) {
        $queryArgs['order'] = $params['post_order'];
    }

    if (!empty($params['post_type'][0])) {
        $queryArgs['post_type'] = $params['post_type'];
    }

    if (!empty($params['post_categories'][0])) {
        $queryArgs['category__in'] = $params['post_categories'];
    }

    if (!empty($params['post_tags'][0])) {
        $queryArgs['tag__in'] = $params['post_tags'];
    }

    if (!empty($params['post_tax_terms'])) {
        $queryArgs['img_size'] = $params['post_tax_terms'];
    }

    $posts = CpPosts::find($queryArgs)->getParsedObject();

    exit(json_encode($posts));
}

function cp_change_status()
{
    $res = 0;
    if ($id = (int) Tools::getValue('id', 0)) {
        $published = (int) Tools::getValue('published', 0);
        $res = CpInstances::changeStatus($id, $published);
    }
    exit($res ? 'OK' : 'NOK');
}
