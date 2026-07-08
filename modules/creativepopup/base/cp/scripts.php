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

cp_add_action('cp_enqueue_scripts', 'cp_enqueue_content_res');
cp_add_action('admin_enqueue_scripts', 'cp_enqueue_admin_res');
cp_add_action('admin_enqueue_scripts', 'cp_load_google_fonts');
cp_add_action('cp_enqueue_scripts', 'cp_load_google_fonts');

function cp_enqueue_content_res()
{
    // Register resources
    cp_enqueue_script('layerslider-greensock', CP_VIEWS_URL . 'js/core/greensock.js', false, '1.19.0');
    cp_enqueue_script('cp-core', CP_VIEWS_URL . 'js/core/creativepopup.js', ['jquery'], CP_PLUGIN_VERSION);
    cp_enqueue_script('layerslider-transitions', CP_VIEWS_URL . 'js/core/cp.transitions.js', false, CP_PLUGIN_VERSION);
    cp_enqueue_style('cp-core', CP_VIEWS_URL . 'css/core/creativepopup.css', false, CP_PLUGIN_VERSION);

    // Origami plugin
    cp_register_script(
        'cp-origami',
        CP_VIEWS_URL . 'js/core/plugins/origami/cp.origami.js',
        ['jquery'],
        CP_PLUGIN_VERSION
    );
    cp_register_style('cp-origami', CP_VIEWS_URL . 'css/core/plugins/origami/cp.origami.css', false, CP_PLUGIN_VERSION);

    // 3rd-party: Font Awesome
    cp_register_style('ce-font-awesome', CP_VIEWS_URL . 'lib/font-awesome/css/font-awesome.min.css', false, '4.7.0');

    // Build LS_Meta object
    $LS_Meta = [];

    if (!cp_is_admin() && cp_get_option('ls_gsap_sandboxing', false)) {
        $LS_Meta['fixGSAP'] = true;
    }

    // Print LS_Meta object
    cp_localize_script('greensock', 'LS_Meta', $LS_Meta);

    // User resources
    if (file_exists(_PS_MODULE_DIR_ . 'creativepopup/views/js/custom.transitions.js')) {
        cp_enqueue_script('cp-user-transitions', CP_VIEWS_URL . 'js/custom.transitions.js', false, CP_PLUGIN_VERSION);
    }

    if (file_exists(_PS_MODULE_DIR_ . 'creativepopup/views/css/custom.css')) {
        cp_enqueue_style('cp-user', CP_VIEWS_URL . 'css/custom.css', false, CP_PLUGIN_VERSION);
    }

    if (cp_get_option('cp_force_load_origami', false)) {
        cp_enqueue_script('cp-origami');
        cp_enqueue_style('cp-origami');
    }

    if (cp_get_option('ls_load_fontawesome', true)) {
        cp_enqueue_style('ce-font-awesome');
    }
}

function cp_enqueue_admin_res()
{
    // Load global CSS
    cp_enqueue_style('cp-global', CP_VIEWS_URL . 'css/admin/global.css', false, CP_PLUGIN_VERSION);

    // Embed CSS. Hides the admin menu bar and the sidebar.
    if (!empty(${'_GET'}['cp-embed'])) {
        cp_enqueue_style('cp-embed', CP_VIEWS_URL . 'css/admin/embed.css', false, CP_PLUGIN_VERSION);
    }

    // Load CreativePopup-only resources
    $screen = cp_get_current_screen();
    if (strpos($screen->base, 'cp_page') !== false) {
        // Dashicons
        cp_enqueue_style('dashicons', CP_VIEWS_URL . 'css/dashicons/dashicons.css', false, CP_PLUGIN_VERSION);

        cp_localize_script('ps-modules', 'PS_Modules', cp_get_modules());

        // Global scripts & stylesheets
        cp_enqueue_script('greensock', CP_VIEWS_URL . 'js/core/greensock.js', false, '1.19.0');
        cp_enqueue_script('kreaturamedia-ui', CP_VIEWS_URL . 'js/admin/km-ui.js', ['jquery'], CP_PLUGIN_VERSION);
        cp_enqueue_script(
            'cp-admin-global',
            CP_VIEWS_URL . 'js/admin/cp-admin-common.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_style('cp-admin', CP_VIEWS_URL . 'css/admin/admin.css', false, CP_PLUGIN_VERSION);
        cp_enqueue_style('cp-admin-new', CP_VIEWS_URL . 'css/admin/admin_new.css', false, CP_PLUGIN_VERSION);
        cp_enqueue_style('kreaturamedia-ui', CP_VIEWS_URL . 'css/admin/km-ui.css', false, CP_PLUGIN_VERSION);

        // 3rd-party: Font Awesome
        cp_enqueue_style('ce-font-awesome', CP_VIEWS_URL . 'lib/font-awesome/css/font-awesome.min.css', false, '4.7.0');

        // 3rd-party: CodeMirror
        cp_enqueue_style('codemirror', CP_VIEWS_URL . 'css/codemirror/lib/codemirror.css', false, CP_PLUGIN_VERSION);
        cp_enqueue_script(
            'codemirror',
            CP_VIEWS_URL . 'js/codemirror/lib/codemirror.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_style(
            'codemirror-solarized',
            CP_VIEWS_URL . 'css/codemirror/theme/solarized.mod.css',
            false,
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-syntax-css',
            CP_VIEWS_URL . 'js/codemirror/mode/css/css.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-syntax-javascript',
            CP_VIEWS_URL . 'js/codemirror/mode/javascript/javascript.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-foldcode',
            CP_VIEWS_URL . 'js/codemirror/addon/fold/foldcode.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-foldgutter',
            CP_VIEWS_URL . 'js/codemirror/addon/fold/foldgutter.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-brace-fold',
            CP_VIEWS_URL . 'js/codemirror/addon/fold/brace-fold.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
        cp_enqueue_script(
            'codemirror-active-line',
            CP_VIEWS_URL . 'js/codemirror/addon/selection/active-line.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );

        // Localize admin scripts
        $cp_l10n = null;
        include CP_ROOT_PATH . '/cp/scripts_l10n.php';
        cp_localize_script('cp-admin-global', 'CP_l10n', $cp_l10n);

        // Popups list page
        if (!empty(${'_GET'}['page']) && ${'_GET'}['page'] != 'transition-builder' && ${'_GET'}['page'] != 'revisions' && empty(${'_GET'}['action'])) {
            cp_enqueue_script(
                'cp-admin-popups',
                CP_VIEWS_URL . 'js/admin/cp-admin-popups.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
        } else {
            // Slider & Transition Builder

            // Load some bundled PS resources
            cp_enqueue_script('jquery-ui-sortable');
            cp_enqueue_script('jquery-ui-selectable');
            cp_enqueue_script('jquery-ui-draggable');
            cp_enqueue_script('jquery-ui-resizable');
            cp_enqueue_script('jquery-ui-slider');

            cp_register_script(
                'cp-admin',
                CP_VIEWS_URL . 'js/admin/cp-admin-popup-builder.js',
                ['jquery', 'json2'],
                CP_PLUGIN_VERSION
            );

            // Slider Builder JS. Don't load automatically other than the Slider Builder
            if (!empty(${'_GET'}['page']) && ${'_GET'}['page'] != 'transition-builder' && ${'_GET'}['page'] != 'revisions') {
                cp_enqueue_script('cp-admin');
            }

            // includes for preview
            cp_enqueue_script(
                'cp-core',
                CP_VIEWS_URL . 'js/core/creativepopup.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
            cp_enqueue_script(
                'cp-transitions',
                CP_VIEWS_URL . 'js/core/cp.transitions.js',
                false,
                CP_PLUGIN_VERSION
            );
            cp_enqueue_script(
                'cp-tr-gallery',
                CP_VIEWS_URL . 'js/admin/cp.transition.gallery.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
            cp_enqueue_style(
                'cp-core',
                CP_VIEWS_URL . 'css/core/creativepopup.css',
                false,
                CP_PLUGIN_VERSION
            );
            cp_enqueue_style(
                'cp-tr-gallery',
                CP_VIEWS_URL . 'css/admin/cp.transitiongallery.css',
                false,
                CP_PLUGIN_VERSION
            );

            // Timeline plugin
            cp_enqueue_script(
                'cp-timeline',
                CP_VIEWS_URL . 'js/core/plugins/timeline/cp.timeline.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
            cp_enqueue_style(
                'cp-timeline',
                CP_VIEWS_URL . 'css/core/plugins/timeline/cp.timeline.css',
                false,
                CP_PLUGIN_VERSION
            );

            // Origami plugin
            cp_enqueue_script(
                'cp-origami',
                CP_VIEWS_URL . 'js/core/plugins/origami/cp.origami.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
            cp_enqueue_style(
                'cp-origami',
                CP_VIEWS_URL . 'css/core/plugins/origami/cp.origami.css',
                false,
                CP_PLUGIN_VERSION
            );

            // 3rd-party: MiniColor
            cp_enqueue_script(
                'minicolor',
                CP_VIEWS_URL . 'js/minicolors/jquery.minicolors.js',
                ['jquery'],
                CP_PLUGIN_VERSION
            );
            cp_enqueue_style(
                'minicolor',
                CP_VIEWS_URL . 'css/minicolors/jquery.minicolors.css',
                false,
                CP_PLUGIN_VERSION
            );

            // 3rd-party: Air Datepicker
            cp_enqueue_style(
                'air-datepicker',
                CP_VIEWS_URL . 'css/air-datepicker/air-datepicker.min.css',
                false,
                '2.1.0'
            );
            cp_enqueue_script(
                'air-datepicker',
                CP_VIEWS_URL . 'js/air-datepicker/air-datepicker.min.js',
                ['jquery'],
                '2.1.0en'
            );

            // 3rd party: html2canvas
            cp_enqueue_script(
                'html2canvas',
                CP_VIEWS_URL . 'js/html2canvas/html2canvas.min.js',
                ['jquery'],
                '1.0.0a9'
            );

            // User transitions
            if (file_exists(_PS_MODULE_DIR_ . 'creativepopup/views/js/custom.transitions.js')) {
                cp_enqueue_script(
                    'cp-user-transitions',
                    CP_VIEWS_URL . 'js/custom.transitions.js',
                    false,
                    CP_PLUGIN_VERSION
                );
            }

            // User CSS
            if (file_exists(_PS_MODULE_DIR_ . 'creativepopup/views/css/custom.css')) {
                cp_enqueue_style('cp-user', CP_VIEWS_URL . 'css/custom.css', false, CP_PLUGIN_VERSION);
            }
        }
    }

    // Transition builder
    if (strpos($screen->base, 'transition-builder') !== false) {
        cp_enqueue_script(
            'cp_tr_builder',
            CP_VIEWS_URL . 'js/admin/cp-admin-transition-builder.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
    }

    // Revisions
    if (strpos($screen->base, 'revisions') !== false) {
        cp_enqueue_style('cp-revisions', CP_VIEWS_URL . 'css/admin/revisions.css', false, CP_PLUGIN_VERSION);
        cp_enqueue_script(
            'cp-revisions',
            CP_VIEWS_URL . 'js/admin/cp-admin-revisions.js',
            ['jquery'],
            CP_PLUGIN_VERSION
        );
    }

    // Skin editor
    if (strpos($screen->base, 'skin-editor') !== false || strpos($screen->base, 'style-editor') !== false) {
        cp_enqueue_style('cp-skin-editor', CP_VIEWS_URL . 'css/admin/skin.editor.css', false, CP_PLUGIN_VERSION);
    }
}

function cp_load_google_fonts()
{
    // Get font list
    $fonts = cp_get_option('cp-google-fonts', []);
    $scripts = cp_get_option('cp-google-font-scripts', ['latin', 'latin-ext']);

    // Check fonts if any
    if (!empty($fonts) && is_array($fonts)) {
        $cpFonts = [];
        foreach ($fonts as $item) {
            if (cp_is_admin() || !$item['admin']) {
                $cpFonts[] = htmlspecialchars($item['param']);
            }
        }

        if (!empty($cpFonts)) {
            $cpFonts = implode('%7C', $cpFonts);
            $protocol = cp_is_ssl() ? 'https' : 'http';
            $query_args = [
                'family' => $cpFonts,
                'subset' => implode('%2C', $scripts),
            ];

            cp_enqueue_style(
                'cp-google-fonts',
                cp_add_query_arg($query_args, "$protocol://fonts.googleapis.com/css"),
                [],
                null
            );
        }
    }
}

function cp_get_template(&$tpls)
{
    if (!$tpl = cp_get_option('cp_init_type', '')) {
        $tpl = version_compare(_PS_VERSION_, '1.7', '<') ? 'cdata' : 'script';
    }

    if ('cdata' === $tpl) {
        $prefix = '<!--[CDATA[';
        $suffix = ']]-->';
    } else {
        $prefix = '<script type="text/html">';
        $suffix = '</script>';
    }

    return '<meta id="cp-meta" name="Generator" content="Powered by Creative Popup ' . CP_PLUGIN_VERSION .
        ' - Multi-Purpose, Responsive, Parallax, Mobile-Friendly Popup Module for PrestaShop.">' .
        $prefix . PHP_EOL . implode("\n", str_replace('<img', '<embed', $tpls)) . PHP_EOL . $suffix;
}
