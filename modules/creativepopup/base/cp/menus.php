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

// Register sidebar menu
cp_add_action('admin_menu', 'cp_settings_menu');
function cp_settings_menu()
{
    $capability = 'manage_options';

    // Add "All Sliders" submenu
    cp_add_submenu_page('cp', 'Creative Popup - Popups', cp__('Popups'), $capability, 'popups', 'cp_router');

    // Add "Revisions" submenu
    cp_add_submenu_page('cp', 'Creative Popup - Revisions', cp__('Revisions'), $capability, 'revisions', 'cp_router');

    // Add "Skin Editor" submenu
    cp_add_submenu_page(
        'cp',
        'Creative Popup - Skin Editor',
        cp__('Skin Editor'),
        $capability,
        'skin-editor',
        'cp_router'
    );

    // Add "CSS Editor submenu"
    cp_add_submenu_page(
        'cp',
        'Creative Popup - CSS Editor',
        cp__('CSS Editor'),
        $capability,
        'style-editor',
        'cp_router'
    );

    // Add "Transition Builder" submenu
    cp_add_submenu_page(
        'cp',
        'Creative Popup - Transition Builder',
        cp__('Transition Builder'),
        $capability,
        'transition-builder',
        'cp_router'
    );
}

// Help menu
cp_add_filter('contextual_help', 'cp_help', 10, 3);
function cp_help($contextual_help, $screen_id, $screen)
{
    $contextual_help .= '';
    $screen_id += 0;
    if (strpos($screen->base, 'cp_page') !== false && !empty(${'_GET'}['page'])) {
        $screen->add_help_tab([
            'id' => 'help',
            'title' => 'Getting Help',
            'content' => '<p>Please read our <a href="http://docs.webshopworks.com/creative-popup" target="_blank">' .
                'Online Documentation</a> carefully, it will likely answer all of your questions.</p>',
        ]);
    }
}

function cp_router()
{
    // Get current screen details
    $screen = cp_get_current_screen();

    if (strpos($screen->base, 'skin-editor') !== false) {
        include CP_ROOT_PATH . '/views/skin_editor.phtml';
    } elseif (strpos($screen->base, 'transition-builder') !== false) {
        include CP_ROOT_PATH . '/views/transition_builder.phtml';
    } elseif (strpos($screen->base, 'revisions') !== false) {
        include CP_ROOT_PATH . '/views/revisions.phtml';
    } elseif (strpos($screen->base, 'style-editor') !== false) {
        include CP_ROOT_PATH . '/views/style_editor.phtml';
    } elseif (isset(${'_GET'}['action']) && ${'_GET'}['action'] == 'edit') {
        include CP_ROOT_PATH . '/views/popup_edit.phtml';
    } else {
        include CP_ROOT_PATH . '/views/popup_list.phtml';
    }
}
