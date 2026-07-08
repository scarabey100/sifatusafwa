<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MagicRedirect extends Module
{
    public function __construct()
    {
        $this->name = 'magicredirect';
        $this->tab = 'seo';
        $this->version = '1.8.01';
        $this->author = 'AgenceMalttt';

        $this->secure_key = Tools::encrypt($this->name);
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = 'a3e63c648fcae16db808f68e1eb18448';
        $this->author_address = '0xc1dcC59643a63D65e05F1F9d8e985dbb9CDe344f';

        if (version_compare(_PS_VERSION_, '1.5.4.1', '>')) {
            // Saw there was some incompatibility with previous versions.
            $this->ps_versions_compliancy = array('min' => '1.5.1.9', 'max' => _PS_VERSION_);
        }

        $this->tabs_form = array(
            'general' => $this->l('General Settings'),
            'cache' => $this->l('Cache & Performances Settings'),
        );

        $models = glob(_PS_MODULE_DIR_.$this->name.'/models/*.php');

        if (!empty($models)) {
            foreach ($models as $file) {
                if (!strpos($file, 'index.php')) {
                    require_once $file;
                }
            }
        }

        if (Shop::isFeatureActive() && version_compare(_PS_VERSION_, '1.5.4.1', '>')) {
            Shop::addTableAssociation('redirect', array('type' => 'shop'));
        }

        $this->fields_form = array(
            array(
                'name' => 'MGRT_AUTOCATCH',
                'type' => 'radio',
                'desc' => $this->l('Catch all 404 Url\'s and log them for future redirections'),
                'label' => $this->l('Catch 404 and log them'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_4_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_4_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_AUTOACTIVE',
                'type' => 'radio',
                'desc' => $this->l('Activate all generated rules by automatic catcher'),
                'label' => $this->l('Activate all generated rules'),
                'default' => '0',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_5_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_5_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_DELPARENTREDIR',
                'type' => 'radio',
                'desc' => $this->l('Redirect dead object (product, category, manufacturer, supplier, cms page, cms category) to their natural parent.'),
                'label' => $this->l('Redirect to parent if deleted.'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_6_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_6_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_AUTOPARENTREDIR',
                'type' => 'radio',
                'desc' => $this->l('Redirect disabled object (product, category, cms page, cms category, supplier, manufacturer) to their natural parent.'),
                'label' => $this->l('Redirect to parent if disabled'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_707_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_707_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_CORRECTREDIR',
                'type' => 'radio',
                'desc' => $this->l('Redirect to corrected URL, for instance if /product-rewrited does not exist and /product-rewrite exist, it will correct the url and redirect to the second one.'),
                'label' => $this->l('Redirect to corrected url (Products & Categories only)'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_co_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_co_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_FALLBACKREDIR',
                'type' => 'radio',
                'desc' => $this->l('Redirect to parent "folder" if possible. In the end it redirects every unknown thing to home'),
                'label' => $this->l('Redirect to parent folder and home'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_707_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_707_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_ADD_TRAILING_SLASH',
                'type' => 'radio',
                'desc' => $this->l('Add ending slash to all redirections on "Redirect to parent folder" function.'),
                'label' => $this->l('Add trailing slash to parent redirect folder'),
                'default' => '0',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_831_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_831_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_DEFAULT_REDIR',
                'type' => 'text',
                'desc' => $this->l('Which url do you want to redirect automatically if page is unfound and ? Also applies when redirect to parent folder hit "/" recursively.'),
                'label' => $this->l('Default redirect URL'),
                'default' => '/',
                'tab' => 'general',
            ),
            array(
                'name' => 'MGRT_HOOKDISP',
                'type' => 'radio',
                'desc' => $this->l('Display an remember on admin dashboard when there is new 404 to take a look.'),
                'label' => $this->l('Display new 404 alert on Admin Dashboard'),
                'default' => '1',
                'tab' => 'general',
                'values' => array(
                    array(
                        'id' => 'type_d_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_d_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_SEPARATOR',
                'type' => 'text',
                'desc' => $this->l('CSV Import Separator'),
                'label' => $this->l('CSV Import Separator'),
                'default' => ',',
                'tab' => 'general',
            ),
            array(
                'name' => 'MGRT_URLCACHE',
                'type' => 'radio',
                'desc' => $this->l('Enable cache for URL rules'),
                'label' => $this->l('Faster redirects with cache'),
                'default' => '1',
                'tab' => 'cache',
                'values' => array(
                    array(
                        'id' => 'type_4_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_4_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_CACHETIME',
                'type' => 'text',
                'desc' => $this->l('Cache time (in minutes)'),
                'label' => $this->l('Cache time (in minutes)'),
                'default' => '60',
                'validate' => 'isInt',
                'tab' => 'cache',
            ),
            array(
                'name' => 'MGRT_DISABLE_REGEX_SQL',
                'type' => 'radio',
                'desc' => $this->l('Disable search for regex redirects on all requests, disabling this feature might save a lot of server ressources if you do not use it.'),
                'label' => $this->l('Disable Regex Redirects'),
                'default' => '0',
                'tab' => 'cache',
                'values' => array(
                    array(
                        'id' => 'type_821_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_821_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_AVOID_STATIC_FILES',
                'type' => 'radio',
                'desc' => $this->l('Do not collect or redirect for missing static files (js/css/txt/json/png/jpg/gif/woff/eot/ttf/svg).'),
                'label' => $this->l('Disable for static files'),
                'default' => '0',
                'tab' => 'cache',
                'values' => array(
                    array(
                        'id' => 'type_892_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_892_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_DISABLE_HITS',
                'type' => 'radio',
                'desc' => $this->l('Disable hit count. The module count each time a not redirected page is hit. Stops at 25 to avoid unnecessary requests.'),
                'label' => $this->l('Disable counter for missing pages'),
                'default' => '0',
                'tab' => 'cache',
                'values' => array(
                    array(
                        'id' => 'type_881_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_881_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_ACTIVE_HITS_COUNT',
                'type' => 'radio',
                'desc' => $this->l('Add hits to already active redirection, also "updated date" on hit.'),
                'label' => $this->l('Hit and update active redirections'),
                'default' => '0',
                'tab' => 'cache',
                'values' => array(
                    array(
                        'id' => 'type_881_0',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                    array(
                        'id' => 'type_881_1',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                ),
            ),
            array(
                'name' => 'MGRT_HIT_LIMIT',
                'type' => 'text',
                'desc' => $this->l('Hit limit before stopping to count'),
                'label' => $this->l('Hit limit'),
                'default' => '25',
                'validate' => 'isInt',
                'tab' => 'cache',
            ),
        );

        $this->hooks = array(
            'actionDispatcher',
            'dashboardZoneOne',
            'actionObjectUpdateAfter',
            'actionObjectDeleteBefore',
            'backOfficeHeader',
        );

        $this->cache_folder = _PS_CACHE_DIR_.$this->name.'/cache/';

        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->displayName = $this->l('Magic Redirect');
        $this->description = $this->l('Predictive and easy redirect tools (Multishop/Multilang)');
    }

    public function install()
    {
        if (is_writable(_PS_CACHE_DIR_)) {
            if (!file_exists($this->cache_folder)) {
                try {
                    mkdir($this->cache_folder);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }

            if (!is_writable($this->cache_folder)) {
                try {
                    chmod($this->cache_folder, 0777);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }

        if ($this->fields_form && count($this->fields_form) > 0) {
            foreach ($this->fields_form as $value) {
                if (isset($value['default']) && !empty($value['default'])) {
                    Configuration::updateValue($value['name'], $value['default']);
                }
            }
        }

        if (!parent::install()) {
            return false;
        } else {
            if (isset($this->hooks) && !empty($this->hooks)) {
                foreach ($this->hooks as $v) {
                    if (!$this->registerHook($v)) {
                        return false;
                    }
                }
            }
        }

        $sql_install = dirname(__FILE__).'/sql/install.php';

        if (file_exists($sql_install)) {
            $sql = array();

            require_once $sql_install;

            if (!empty($sql)) {
                foreach ($sql as $s) {
                    if (!Db::getInstance()->execute($s)) {
                        return false;
                    }
                }
            }
        }

        $this->installTab();

        return true;
    }

    public function uninstallTab()
    {
        $idTab = Tab::getIdFromClassName('AdminRedirect');

        if (!empty($idTab)) {
            $tab = new Tab($idTab);
            $tab->delete();
        }
    }

    public function installTab()
    {

        // Clean install
        $this->uninstallTab();

        $langs = Language::getLanguages(true);

        $tab = new Tab();
        $tab->class_name = 'AdminRedirect';
        $tab->id_parent = 0;
        $tab->active = true;
        $tab->module = $this->name;

        foreach ($langs as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Magic Redirect');
        }

        $tab->add();
    }

    public function uninstall()
    {

        // We don't drop tables, datas are important, we must keep them if needed

        return parent::uninstall();
    }

    public function getContent()
    {
        $this->postProcess();

        $html = $this->getPreform().$this->renderForm().$this->getPostform();

        return $html;
    }

    public function getPostform()
    {
        return $this->display(__FILE__, 'postform.tpl');
    }

    public function getPreform()
    {
        $link = new Link();
        $redir_admin_link = $link->getAdminLink('AdminRedirect');
        $this->context->smarty->assign(
            array(
                'displayName' => $this->displayName,
                'description' => $this->description,
                'redir_admin_link' => $redir_admin_link,
                'logo_img' => __PS_BASE_URI__.'modules/'.$this->name.'/logo.png',
            )
        );

        return $this->display(__FILE__, 'preform.tpl');
    }

    public function renderForm()
    {
        if ($this->fields_form == null) {
            return false;
        }

        $fields = $this->fields_form;

        $fields_form = array(
            'tinymce' => true,
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration for ').$this->displayName,
                    'icon' => 'icon-cogs',
                ),
                'input' => $fields,
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (isset($this->tabs_form)) {
            $fields_form['form']['tabs'] = $this->tabs_form;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = (isset($this->show_toolbar) ? $this->show_toolbar : false);

        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper->submit_action = 'submit'.Tools::ucfirst($this->name);
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldValues()
    {
        if ($this->fields_form == null) {
            return false;
        }

        $array = $this->fields_form;
        $languages = Language::getLanguages(false);

        $field_values = array();

        foreach ($array as $value) {
            if (isset($value['lang']) && $value['lang'] == true) {
                foreach ($languages as $lang) {
                    $field_values[$value['name']][$lang['id_lang']] = Configuration::get($value['name'], $lang['id_lang']);
                }
            } else {
                $field_values[$value['name']] = Configuration::get($value['name']);
            }
        }

        return $field_values;
    }

    public static function getRootCategory()
    {
        $root_category = Category::getRootCategory();

        $root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);

        return $root_category;
    }

    public function postProcess()
    {
        if (!isset($this->fields_form)) {
            return parent::postProcess();
        }

        $values = $this->fields_form;

        if (!($languages = Language::getLanguages(true))) {
            return false;
        }

        if (Tools::isSubmit('submit'.Tools::ucfirst($this->name))) {
            foreach ($values as $value) {
                if (isset($value['lang']) && $value['lang'] == true) {
                    switch ($value['type']) {
                        case 'text':
                        case 'textarea':
                        case 'select':

                            $text = array();

                            foreach ($languages as $lang) {
                                $text[$lang['id_lang']] = Tools::getValue($value['name'].'_'.$lang['id_lang']);
                            }

                            if (isset($value['validate']) && !empty($value['validate'])) {
                                foreach ($text as $k => $v) {
                                    if ($value['validate'] == 'isInt') {
                                        $text[$k] = (int) $text[$k];
                                    } elseif ($value['validate'] == 'isFloat') {
                                        $text[$k] = (float) $text[$k];
                                    } elseif ($value['validate'] == 'isBool') {
                                        $text[$k] = (bool) $text[$k];
                                    }
                                }
                            }

                            Configuration::updateValue($value['name'], $text, (isset($value['html']) ? $value['html'] : false));

                            break;

                        case 'file':

                            foreach ($languages as $lang) {
                                if (isset($_FILES[$value['name'].'_'.$lang['id_lang']]) && isset($_FILES[$value['name'].'_'.$lang['id_lang']]['tmp_name']) && !empty($_FILES[$value['name'].'_'.$lang['id_lang']]['tmp_name'])) {
                                    if (ImageManager::validateUpload($_FILES[$value['name'].'_'.$lang['id_lang']], 4000000)) {
                                        return $this->displayError($this->l('Invalid image.'));
                                    } else {
                                        $ext = Tools::substr($_FILES[$value['name'].'_'.$lang['id_lang']]['name'], strrpos($_FILES[$value['name'].'_'.$lang['id_lang']]['name'], '.') + 1);
                                        $file_name = md5($_FILES[$value['name'].'_'.$lang['id_lang']]['name']).'.'.$ext;
                                        if (!move_uploaded_file($_FILES[$value['name'].'_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).'/public/img/'.$file_name)) {
                                            return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                                        } else {
                                            if (Configuration::hasContext($value['name'].'_'.$lang['id_lang'], null, Shop::getContext()) && Configuration::get($value['name'].'_'.$lang['id_lang']) != $file_name) {
                                                @unlink(dirname(__FILE__).'/public/imgs/'.Configuration::get($value['name'].'_'.$lang['id_lang']));
                                            }
                                            $text[$lang['id_lang']] = '/modules/'.$this->name.'/public/img/'.$file_name;
                                        }
                                    }
                                }

                                if (count($text) > 0) {
                                    Configuration::updateValue($value['name'], $text);
                                }
                            }

                            break;

                        case 'switch':

                            Configuration::updateValue($value['name'], Tools::getValue($value['name'].'_'.$lang['id_lang']));

                            break;

                        default:

                            Configuration::updateValue($value['name'], Tools::getValue($value['name'].'_'.$lang['id_lang']), (isset($value['html']) ? $value['html'] : false));

                            break;
                    }
                } else {
                    switch ($value['type']) {
                        case 'text':
                        case 'textarea':
                        case 'select':

                            $v = Tools::getValue($value['name']);

                            if (isset($value['validate']) && !empty($value['validate'])) {
                                if ($value['validate'] == 'isInt') {
                                    $v = (int) $v;
                                } elseif ($value['validate'] == 'isFloat') {
                                    $v = (float) $v;
                                } elseif ($value['validate'] == 'isBool') {
                                    $v = (bool) $v;
                                }
                            }

                            Configuration::updateValue($value['name'], $v, (isset($value['html']) ? $value['html'] : false));

                            break;

                        case 'file':

                            if (isset($_FILES[$value['name']]) && isset($_FILES[$value['name']]['tmp_name']) && !empty($_FILES[$value['name']]['tmp_name'])) {
                                if (ImageManager::validateUpload($_FILES[$value['name']], 4000000)) {
                                    return $this->displayError($this->l('Invalid image.'));
                                } else {
                                    $ext = Tools::substr($_FILES[$value['name']]['name'], strrpos($_FILES[$value['name']]['name'], '.') + 1);
                                    $file_name = md5($_FILES[$value['name']]['name']).'.'.$ext;
                                    if (!move_uploaded_file($_FILES[$value['name']]['tmp_name'], dirname(__FILE__).'/public/imgs/'.$file_name)) {
                                        return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                                    } else {
                                        if (Configuration::hasContext($value['name'], null, Shop::getContext()) && Configuration::get($value['name']) != $file_name) {
                                            @unlink(dirname(__FILE__).'/'.Configuration::get($value['name']));
                                        }
                                        Configuration::updateValue($value['name'], '/modules/'.$this->name.'/public/imgs/'.$file_name);
                                    }
                                }
                            }

                            break;

                        case 'switch':

                            Configuration::updateValue($value['name'], Tools::getValue($value['name']));

                            break;

                        default:

                            Configuration::updateValue($value['name'], Tools::getValue($value['name']), (isset($value['html']) ? $value['html'] : false));

                            break;

                    }
                }
            }

            // Display update confirmation, if they are here it's ok, they passed all errors
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6');
        }
    }

    public function hookDashboardZoneOne($params)
    {
        if ((bool) Configuration::get('MGRT_HOOKDISP') === true && (int) Configuration::get('MGRT_COUNTER') > 0) {
            $redir_admin_link = Context::getContext()->link->getAdminLink('AdminRedirect');
            $this->context->smarty->assign(
                array(
                    'admin_link' => $redir_admin_link,
                )
            );

            return $this->display(__FILE__, 'dashboard.tpl');
        }
    }

    public function hookHeader($params)
    {
        $this->catchUrls();
    }

    public function hookActionDispatcher($params)
    {
        $this->catchUrls();
    }

    private function getPageName()
    {
        $smarty = Context::getContext()->smarty;

        if (!empty($smarty->tpl_vars['page_name']->value)) {
            $page_name = $smarty->tpl_vars['page_name']->value;
        } elseif (!empty($this->page_name)) {
            $page_name = $this->page_name;
        } elseif (!empty($this->php_self)) {
            $page_name = $this->php_self;
        } elseif (preg_match('#^'.preg_quote($this->context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
            $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
        } else {
            $page_name = Dispatcher::getInstance()->getController();
            $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
        }

        return $page_name;
    }

    // Try to correct url mispelled
    private function autoCorrectUrl()
    {
        $request = $_SERVER['REQUEST_URI'];

        if ((bool) Configuration::get('MGRT_CORRECTREDIR') === true) {
            $solution = $this->findEquivalentUrl($request);
            
            if ($solution !== '/') {
                $redir = array(
                    "type" => 301,
                    "new" => $solution
                );

                return $this->makeRedirection($redir);
            }
        }
    }

    private function fallbackDirectory()
    {

        // Still no redirect ? lets see if we can extract something here
        $request = $_SERVER['REQUEST_URI'];

        if ((bool) Configuration::get('MGRT_FALLBACKREDIR') === true) {
            if (!in_array($request, array('//','/'))) {

                // Sanitize any useless parameters
                $full = explode("?", $request);

                $request = $full[0];

                $explode = explode('/', $request);

                $sanitized = array();

                // Remove empty values
                foreach ($explode as $value) {
                    if (!empty($value)) {
                        $sanitized[] = $value;
                    }
                }

                $count = count($sanitized);

                if ($count >= 1) {

                    // Remove last part then tests, fallback to parent virtual "directory" until it fallback to home
                    unset($sanitized[$count - 1]);

                    $result = '/'.implode('/', $sanitized);

                    if ((bool) Configuration::get('MGRT_ADD_TRAILING_SLASH') === true && substr($result, -1) !== "/") {
                        $result .= "/";
                    }

                    // Nothing found we redirect classic url with predefined settings
                    $default_fallback = Configuration::get('MGRT_DEFAULT_REDIR');

                    if (!empty($default_fallback) && $result == '/' && strpos($default_fallback, "/") !== false) {
                        $result = $default_fallback;
                    }

                    $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');

                    $redirect = 'http'.($force_ssl === true ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$result;

                    $redir = array(
                        "type" => 301,
                        "new" => $redirect
                    );

                    $this->makeRedirection($redir);
                }
            }
        }
    }

    private function redirectParentObject($page_name, $id_shop, $id_lang)
    {
        $id = Tools::getValue('id_'.$page_name) ? (int) Tools::getValue('id_'.$page_name) : null;
        $link_rewrite = Tools::getValue($page_name.'_rewrite');

        if (empty($id) && !empty($link_rewrite) && in_array($page_name, array('product', 'category', 'cms'))) {
            
            $new_id = (int) Db::getInstance()->getValue('SELECT id_product FROM '._DB_PREFIX_.pSQL($page_name).'_lang WHERE link_rewrite = "'.pSQL($link_rewrite).'" AND id_lang = '.(int) $id_lang.' AND id_shop = '.(int) $id_shop);
            
            if (!empty($new_id)) {
                $id = $new_id;
            }

        }

        $cookie = new Cookie('psAdmin'); //ajout de la notion de cookie admin

        if ((int) Tools::getValue('preview') === 1 || (isset($cookie->id_employee) && (int) $cookie->id_employee > 0)) {
            // Avoid previews requests
            return;
        }

        if (!empty($page_name)) {
            $link = new Link();

            switch ($page_name) {
                case 'manufacturer':

                    if (!empty($id)) {
                        $manufacturer = new Manufacturer($id);

                        $validate = Validate::isLoadedObject($manufacturer);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $manufacturer->active == false) {
                                // Redirect to manufacturers page
                                $redirect = $link->getPageLink('manufacturer');
                            }
                        }
                    }

                    break;

                case 'supplier':

                    if (!empty($id)) {
                        $supplier = new Supplier($id);

                        $validate = Validate::isLoadedObject($supplier);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $supplier->active == false) {
                                // Redirect to suppliers page
                                $redirect = $link->getPageLink('supplier');
                            }
                        }
                    }

                    break;

                case 'cms':

                    if (!empty($id)) {
                        $cms = new CMS($id);

                        $validate = Validate::isLoadedObject($cms);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $cms->active === false) {

                                // Redirect to CMS category page, it will loop until it finds the nearest available
                                $redirect = $link->getCMSCategoryLink($cms->id_cms_category);
                            }
                        }
                    }

                    break;

                case 'product':

                    if (!empty($id)) {
                        $product = new Product($id);

                        $test = isset($product->redirect_type) && in_array((int) $product->redirect_type, array(301, 302)) && isset($product->id_product_redirected) && !empty($product->id_product_redirected);

                        $validate = Validate::isLoadedObject($product);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $product->active == false && (bool) $test === false) {
                                $category = new Category($product->id_category_default);

                                if ((bool) $category->active === true) {
                                    $redirect = $link->getCategoryLink($category->id);
                                } else {
                                    $redirect = $link->getPageLink('index');
                                }
                            }
                        }
                    } else {
                        $this->fallbackDirectory();
                    }

                    break;

                case 'category':

                    if (!empty($id)) {
                        $category = new Category($id);

                        $validate = Validate::isLoadedObject($category);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $category->active === false) {

                                // It will loop over to find the nearest parent if all categories are disabled
                                $redirect = $link->getCategoryLink($category->id_parent);
                            }
                        }
                    } else {
                        $this->fallbackDirectory();
                    }

                    break;

                case 'cms_category':

                    if (!empty($id)) {
                        $cmscategory = new CMSCategory($id);

                        $validate = Validate::isLoadedObject($cmscategory);

                        if ($validate == false) {
                            $this->fallbackDirectory();
                        } else {
                            if ((bool) $cmscategory->active === false) {

                                // It will loop over to find the nearest parent if all categories are disabled
                                $redirect = $link->getCMSCategoryLink($cmscategory->id_parent);
                            }
                        }
                    }

                    break;

                default:

                    // UFO

                    break;

            }

            if (isset($redirect) && !empty($redirect)) {
                $redir = array(
                    "type" => 301,
                    "new" => $redirect
                );

                return $this->makeRedirection($redir);
            }
        }
    }

    private function catchUrlInDb($page_name, $id_lang, $id_shop)
    {
        $uri_var = $this->formatLink($_SERVER['REQUEST_URI']);

        if ((bool) Configuration::get('MGRT_AVOID_STATIC_FILES') == true) {

            if (preg_match('/\.css|\.js|.txt|\.jpg|\.jpeg|\.png|\.bmp|\.wav|\.webp|\.gif|\.mp|\.ogg|\.otf|\.ttf|\.woff|\.svg/i', $uri_var)) {
                return;
            }

        }

        $cache_enabled = (bool) Configuration::get('MGRT_URLCACHE');

        $cache_name = 'MR2_'.$id_shop.'_'.Tools::str2url($uri_var);

        $cache_file = $this->cache_folder.md5($cache_name);

        // Better checking this before
        if ($cache_enabled === true && is_writable(_PS_CACHE_DIR_) && file_exists($this->cache_folder)) {
            $time_cache = (int) Configuration::get('MGRT_CACHETIME');

            if (file_exists($cache_file) && (filemtime($cache_file) > (time() - ($time_cache * 60)))) {

                // Checked, can go ahead
                return;
            } else {
                try {
                    file_put_contents($cache_file, 'checked', LOCK_EX);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Redirect Cache folder not writable, please check your modules folder permissions.');
                }
            }
        }

        $link = new Link();

        $db = Db::getInstance();

        $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');

        $full_uri = 'http'.($force_ssl === true ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if ($full_uri == $link->getPageLink('404')) {
            // nothing to stare
            return;
        }

        if ($page_name == 'pagenotfound') {

            // Check if similar redirect exist, tryig to avoid duplicates
            $exist = $db->getRow('SELECT * FROM '._DB_PREFIX_.'redirect r WHERE r.old = "'.pSQL($uri_var).'"');

            if (!empty($uri_var) && empty($exist)) {

                // Let's try to find a solution
                $solution = $this->findEquivalentUrl($uri_var);

                // Let's log it
                $redirect = new Redirect();
                $redirect->old = trim($uri_var);
                $redirect->new = $solution;
                $redirect->type = '301';
                $redirect->hit = 1;
                $redirect->regex = false;
                $redirect->active = (bool) Configuration::get('MGRT_AUTOACTIVE');
                $redirect->add();

                // Multishop
                $db->execute('INSERT INTO `'._DB_PREFIX_.'redirect_shop` (`id_redirect`, `id_shop`) VALUES ('.(int) $redirect->id.','.(int) $id_shop.')');

                // Update counter
                $t = (int) Configuration::get('MGRT_COUNTER');
                Configuration::updateValue('MGRT_COUNTER', ($t + 1));

                // No reason to wait for redirecting a wrong url
                if ($redirect->active === true) {
                    $redir = array(
                        "type" => 301,
                        "new" => $solution,
                        "id_shop" => $id_shop
                    );

                    return $this->makeRedirection($redir);
                }
            }

            if (!empty($exist)) {

                // Test exist in current shop, if not adding it or pushit a hit count
                $exist_shop = $db->getRow('SELECT * FROM '._DB_PREFIX_.'redirect_shop WHERE id_shop = '.(int) $id_shop.' AND id_redirect = '.(int) $exist['id_redirect']);

                if (empty($exist_shop)) {
                    $db->execute('INSERT INTO `'._DB_PREFIX_.'redirect_shop` (`id_redirect`, `id_shop`) VALUES ('.(int) $exist['id_redirect'].','.(int) $id_shop.')');
                } else {

                    // Adding a hit if not enabled but already exists
                    if ((bool) Configuration::get('MGRT_DISABLE_HITS') === false && (bool) $exist['active'] === false) {
                        if (isset($exist['hit'])) {
                            $count = (int) $exist['hit'] + 1;
                        } else {
                            $count = 1;
                        }

                        $limit_count = Configuration::get('MGRT_HIT_LIMIT') ? Configuration::get('MGRT_HIT_LIMIT') : 25;
                        if ($count < $limit_count) {
                            $db->execute('UPDATE `'._DB_PREFIX_.'redirect` SET `hit` = "'.(int) $count.'" WHERE `id_redirect` = '.(int) $exist['id_redirect']);
                        }
                    }
                }
            }
        } else {

            // We try to find some concordance in available pages
            // considering link rewrite and manipulated url structures
            $rewrite = Tools::isSubmit('rewrite') ? Tools::getValue('rewrite') : null;

            if (empty($rewrite)) {
                $id = Tools::getValue('id_'.$page_name) ? (int) Tools::getValue('id_'.$page_name) : null;

                switch ($page_name) {
                    case 'manufacturer':

                        if (!empty($id)) {
                            $manufacturer = new Manufacturer($id, $id_lang);
                            $rewrite = $manufacturer->link_rewrite;
                        }

                        break;

                    case 'supplier':

                        if (!empty($id)) {
                            $supplier = new Supplier($id, $id_lang);
                            $rewrite = $supplier->link_rewrite;
                        }

                        break;

                    case 'cms':

                        if (!empty($id)) {
                            $link_rewrite = CMS::getUrlRewriteInformations($id);
                        }

                        if (isset($link_rewrite[$id_lang])) {
                            $rewrite = $link_rewrite[$id_lang]['link_rewrite'];
                        }

                        break;

                    case 'product':

                        if (!empty($id)) {
                            $product = new Product($id, false, $id_lang);
                            $rewrite = $product->link_rewrite;
                        }

                        break;

                    case 'category':

                        if (!empty($id)) {
                            $category = new Category($id, $id_lang, $id_shop);
                            $rewrite = $category->link_rewrite;
                        }

                        break;

                    case 'cms_category':

                        if (!empty($id)) {
                            $rewrite = CMSCategory::getLinkRewrite($id, $id_lang);
                        }

                        break;

                    default:

                        // UFO or Module ? Hard to find an equivalent.
                        if (strpos('module', $page_name)) {
                            $rewrite = $page_name;
                        } else {
                            $rewrite = $uri_var;
                        }

                        break;

                }
            }

            if (!empty($rewrite)) {

                // Preparing string
                $rewrite = str_replace('/', '-', $rewrite);
                $a = explode('-', $rewrite);
                $b = array();

                // Filtering, 3 words search maximum, with 3 letter minimum
                foreach ($a as $v) {
                    if (Tools::strlen($v) > 2) {
                        $b[] = $v;
                    }
                    if (count($b) > 2) {
                        break;
                    }
                }

                if (!empty($b)) {
                    $search = implode('%', $b);

                    $exist = $db->executeS('SELECT r.id_redirect FROM '._DB_PREFIX_.'redirect r, '._DB_PREFIX_.'redirect_shop rs WHERE rs.id_redirect = r.id_redirect AND rs.id_shop = '.(int) $id_shop.' AND r.regex = 0 AND r.old LIKE "/%'.pSQL($search).'"');

                    if (!empty($exist)) {

                        // Filling unactive empty settled redirect with new best match value
                        foreach ($exist as $value) {
                            $db->execute('UPDATE '._DB_PREFIX_.'redirect SET new = "'.pSQL($uri_var).'" WHERE id_redirect = '.(int) $value['id_redirect']);
                        }
                    }
                }
            }
        }
    }

    public function formatLink($url, $id_lang = null, $id_shop = null, $with_lang = false)
    {
        $context = Context::getContext();

        $shop = !empty($id_shop) ? new Shop((int) $id_shop) : $context->shop;

        $id_lang = empty($id_lang) ? (int) $context->language->id : (int) $id_lang;

        $shop_uri = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? $shop->domain_ssl : $shop->domain;

        //Secured input
        $url = str_replace(array('http', 'https', '://', ';', "'", '"', $shop_uri), '', $url);

        if ($with_lang !== false) {
            $iso = Language::getIsoById($id_lang);

            if ((bool) Configuration::get('PS_REWRITING_SETTINGS') == true) {

                // Removing all other parameters (lang & other custom get request)
                $url = str_replace('/'.$iso.'/', '/', $url);
                $explode = explode('?', $url);
                $url = $explode[0];
            } else {
                $url = str_replace('lang='.$iso, '', $url);
            }
        }

        return $url;
    }

    public function catchUrls()
    {
        $page_name = $this->getPageName();

        $context = Context::getContext();

        $id_shop = $context->shop->id;

        $id_lang = $context->cookie->id_lang;

        // Checking redirects first
        $this->getMissingUrl();

        // Collect missing pages then
        if ((bool) Configuration::get('MGRT_AUTOCATCH') === true) {
            $this->catchUrlInDb($page_name, $id_lang, $id_shop);
        }


        // Avoiding certain requests for convenience
        if ((bool) Configuration::get('MGRT_AVOID_STATIC_FILES') == true) {

            if (isset($_SERVER['REQUEST_URI']) && preg_match('/\.css|\.js|\.txt|\.jpg|\.jpeg|\.png|\.bmp|\.wav|\.webp|\.gif|\.mp|\.ogg|\.otf|\.ttf|\.woff|\.svg/i', $_SERVER['REQUEST_URI'])) {
                return;
            }

        }

        // Checking auto redirect if cms_page/cms_category/category/manufacturer/product disabled
        if ((bool) Configuration::get('MGRT_AUTOPARENTREDIR') === true) {
            $this->redirectParentObject($page_name, $id_lang, $id_shop);
        }

        if ($page_name == 'pagenotfound') {
            
            // Try to correct url mispelled
            $this->autoCorrectUrl();

            // Try to find a similar parent folder to this
            $this->fallbackDirectory();
        }
    }

    public function getMissingUrl()
    {

        // Setup
        $uri_var = $this->formatLink($_SERVER['REQUEST_URI']);

        $id_shop = Shop::getContextShopID();

        $db = Db::getInstance();

        $redir = array();

        $cache_enabled = (bool) Configuration::get('MGRT_URLCACHE');

        $cache_name = 'MR1_'.$id_shop.'_'.Tools::str2url($uri_var);

        $cache_file = $this->cache_folder.md5($cache_name);

        // Better checking this before
        if ($cache_enabled === true && is_writable(_PS_CACHE_DIR_) && file_exists($this->cache_folder)) {
            $time_cache = (int) Configuration::get('MGRT_CACHETIME');

            if (file_exists($cache_file) && (filemtime($cache_file) > (time() - ($time_cache * 60)))) {
                $datas = Tools::file_get_contents($cache_file);
                $redir = unserialize($datas);

                return $this->makeRedirection($redir);
            }
        }

        if (Shop::isFeatureActive() && version_compare(_PS_VERSION_, '1.5.4.1', '>')) {
            $sql_shop = ' AND rs.id_redirect = r.id_redirect AND rs.id_shop = '.(int) $id_shop;
            $join = ', '._DB_PREFIX_.'redirect_shop rs';
        } else {
            $sql_shop = '';
            $join = '';
        }

        $sql_redir = $db->getRow('SELECT * FROM '._DB_PREFIX_.'redirect r '.$join.' WHERE r.old = "'.pSQL($uri_var).'" '.pSQL($sql_shop).' AND r.active = 1 AND r.regex = 0 ORDER BY date_upd DESC');

        if (empty($sql_redir) && (bool) Configuration::get('MGRT_DISABLE_REGEX_SQL') === false) {

            if (Shop::isFeatureActive() && version_compare(_PS_VERSION_, '1.5.4.1', '>')) {
                $sql_extend = 'rs.id_redirect = r.id_redirect AND rs.id_shop = '.(int) $id_shop.' AND';
            } else {
                $sql_extend = "";
            }

            $sql_regex = $db->executeS('SELECT * FROM '._DB_PREFIX_.'redirect r '.$join.' WHERE 1=1 '.pSQL($sql_shop).' AND r.active = 1 AND r.regex = 1 GROUP BY r.old ORDER BY date_upd DESC LIMIT 0, 20');

            $before = '/';
            $after = '/i';

            if (!empty($sql_regex)) {
                $uri_var_lang = $this->formatLink($_SERVER['REQUEST_URI'], null, null, false);

                try {
                    foreach ($sql_regex as $value) {

                        // Bugfix in Prestashop's db saves
                        $value['old'] = str_replace('/', '\/', $value['old']);
                        $value['old'] = str_replace('\\\/', '\/', $value['old']);

                        $test = preg_match($before.$value['old'].$after, $uri_var_lang);
                        
                        if ((bool) $test === true) {
                            $redir['new'] = preg_replace($before.$value['old'].$after, $value['new'], $uri_var_lang);
                            $redir['type'] = $value['type'];
                        }
                    }
                } catch (Exception $e) {
                    // $e->getMessage();
                }
            }
        } else {
            // Match
            $redir = $sql_redir;
        }

        if ($cache_enabled === true && is_writable(_PS_CACHE_DIR_) && file_exists($this->cache_folder)) {
            try {
                file_put_contents($cache_file, serialize($redir), LOCK_EX);
            } catch (Exception $e) {
                PrestaShopLogger::addLog('Redirect Cache folder not writable, please check your modules folder permissions.');
            }
        }

        $this->makeRedirection($redir);
    }

    private function makeRedirection($redir = null)
    {
        if (!empty($redir) && isset($redir['type'])) {

            // Fallback redir on other shop workaround
            if (Shop::isFeatureActive() && isset($redir['id_shop']) && Shop::getContextShopID() != (int) $redir['id_shop']) {
                return;
            }

            switch ((int) $redir['type']) {
                case 301:
                    $redir_message = '301 Moved Permanently';
                    break;

                case 302:
                    $redir_message = '302 Moved Temporarily';
                    break;

                case 303:
                    $redir_message = '303 See Other';
                    break;

                default:
                    // Not handled
                    $redir_message = '301 Moved Permanently';
                    break;
            }

            $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');

            $actual_link = 'http'.($force_ssl === true ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            // Checking link type
            if (preg_match("%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i", $redir['new'])) {
                $new = $redir['new'];
            } else {
                $new = 'http'.($force_ssl === true ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$redir['new'];
            }

            if (isset($redir['id_redirect']) && (bool) Configuration::get('MGRT_DISABLE_HITS') === false && (bool) Configuration::get('MGRT_ACTIVE_HITS_COUNT') === true) {
                if (isset($redir['hit'])) {
                    $count = (int) $redir['hit'] + 1;
                } else {
                    $count = 1;
                }

                $limit_count = Configuration::get('MGRT_HIT_LIMIT') ? Configuration::get('MGRT_HIT_LIMIT') : 25;
                
                if ($count < $limit_count) {
                    Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'redirect` SET `hit` = "'.(int) $count.'" WHERE `id_redirect` = '.(int) $redir['id_redirect']);
                }
            }

            // Avoid redirect loop
            if ($actual_link != $new) {
                Tools::redirect($new, __PS_BASE_URI__, null, array('HTTP/1.1 '.$redir_message));
                exit;
            }
        }
    }

    public function getShopsIds()
    {
        $id_shops = array();

        if (Shop::getContext() == Shop::CONTEXT_ALL) {
            $shops = Shop::getShops();

            if (!empty($shops)) {
                foreach ($shops as $s) {
                    $id_shops[] = $s['id_shop'];
                }
            }
        } elseif (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $shopid = Shop::getContextShopGroupID(true);
            $shops = ShopGroup::getShopsFromGroup($shopid);

            if (!empty($shops)) {
                foreach ($shops as $s) {
                    $id_shops[] = $s['id_shop'];
                }
            }
        }

        if (empty($id_shops)) {
            $id_shops[] = Shop::getContextShopID();
        }

        $id_shops = array_unique($id_shops);

        return $id_shops;
    }

    public function hookActionObjectDeleteBefore($params)
    {
        if ((bool) Configuration::get('MGRT_DELPARENTREDIR') === true) {
            if (isset($params['object']) && !empty($params['object'])) {
                $object = $params['object'];

                $type = get_class($object);

                if (!in_array($type, array('Product', 'Manufacturer', 'Supplier', 'Category', 'CMS', 'CMSCategory'))) {
                    // Ok not our job
                    return;
                }

                $shops = $this->getShopsIds();

                $link = new Link();

                $redirect_list = array();

                // Incremental way, it's a bugfix, for supplier for example
                $shop_urls = array();
                $shop_urls[] = 'https:';
                $shop_urls[] = 'http:';

                foreach ($shops as $id_shop) {
                    $shop = new ShopUrl($id_shop);

                    $shop_urls[] = str_replace(array('http://', 'https://'), '', $shop->getURL(true));
                    $shop_urls[] = str_replace(array('http://', 'https://'), '', $shop->getURL(false));
                }

                $shop_urls = array_unique($shop_urls);

                foreach ($shops as $id_shop) {

                    // foreach langs, foreach shops ...
                    $langs = Language::getLanguages(false, $id_shop, true);

                    foreach ($langs as $id_lang) {
                        switch ($type) {

                            case 'Manufacturer':

                                $manufacturer = new Manufacturer($object->id, $id_lang);

                                if (Validate::isLoadedObject($manufacturer)) {
                                    $old_link = $link->getManufacturerLink($manufacturer, null, null, null, $id_lang, $id_shop);
                                    // Redirect to manufacturers page
                                    $new_link = $link->getPageLink(Tools::strtolower($type), null, $id_lang, null, false, $id_shop);
                                }

                                break;

                            case 'Supplier':

                                $supplier = new Supplier($object->id, $id_lang);

                                if (Validate::isLoadedObject($supplier)) {
                                    $old_link = $link->getSupplierLink($supplier, null, $id_lang, $id_shop);
                                    // Redirect to suppliers page
                                    $new_link = $link->getPageLink(Tools::strtolower($type), null, $id_lang, null, false, $id_shop);
                                }

                                break;

                            case 'CMS':

                                $cms = new CMS($object->id, $id_lang, $id_shop);

                                if (Validate::isLoadedObject($cms)) {
                                    $old_link = $link->getCMSLink($cms, null, null, $id_lang, $id_shop);
                                    $new_link = $link->getCMSCategoryLink($cms->id_parent, null, $id_lang, $id_shop);
                                }

                                break;

                            case 'Product':

                                $product = new Product($object->id, null, $id_lang, $id_shop);

                                if (Validate::isLoadedObject($product)) {
                                    $old_link = $link->getProductLink($product, null, null, null, $id_lang, $id_shop);
                                    $new_link = $link->getCategoryLink($product->id_category_default, null, $id_lang, null, $id_shop);
                                }

                                break;

                            case 'Category':

                                $category = new Category($object->id, $id_lang, $id_shop);

                                if (Validate::isLoadedObject($category)) {
                                    $old_link = $link->getCategoryLink($category, null, $id_lang, null, $id_shop);
                                    $new_link = $link->getCategoryLink($category->id_parent, null, $id_lang, null, $id_shop);
                                }

                                break;

                            case 'CMSCategory':

                                $cmscategory = new CMSCategory($object->id, $id_lang, $id_shop);

                                if (Validate::isLoadedObject($product)) {
                                    $old_link = $link->getCMSCategoryLink($cmscategory, null, $id_lang, $id_shop);
                                    $new_link = $link->getCMSCategoryLink($cmscategory->id_parent, null, $id_lang, $id_shop);
                                }

                                break;

                            default:

                                // UFO

                                break;

                        }

                        // Escape shops urls, it's a multishop relative approach
                        $old = str_replace('//', '/', $old_link);
                        $old = str_replace($shop_urls, '', $old_link);
                        $old = '/'.ltrim($old, '/');

                        $new = str_replace('//', '/', $new_link);
                        $new = str_replace($shop_urls, '', $new_link);
                        $new = '/'.ltrim($new, '/');

                        // Avoid duplicates, simplify complex mapings
                        $md5 = md5($old.$new);

                        if (!isset($redirect_list[$md5])) {
                            $redirect_list[$md5]['old'] = $old;
                            $redirect_list[$md5]['new'] = $new;
                            $redirect_list[$md5]['shops'][] = $id_shop;
                        } elseif (!in_array($id_shop, $redirect_list[$md5]['shops'])) {
                            $redirect_list[$md5]['shops'][] = $id_shop;
                        }
                    }
                }

                if (!empty($redirect_list)) {

                    // Force new redirect
                    $this->insertRedirects($redirect_list, true);
                }
            }
        }
    }

    public function hookActionObjectUpdateAfter($params)
    {
        if (isset($params['object']) && !empty($params['object'])) {
            $object = $params['object'];

            $type = get_class($object);

            if (!in_array($type, array('Product', 'Manufacturer', 'Supplier', 'Category', 'CMS', 'CMSCategory'))) {
                // Ok not our job
                return;
            }

            $shops = $this->getShopsIds();
            $link = new Link();
            $redirect_list = array();

            // Incremental way, it's a bugfix, for supplier for example
            $shop_urls = array();
            $shop_urls[] = 'https:';
            $shop_urls[] = 'http:';

            foreach ($shops as $id_shop) {
                $shop = new ShopUrl($id_shop);

                $shop_urls[] = str_replace(array('http://', 'https://'), '', $shop->getURL(true));
                $shop_urls[] = str_replace(array('http://', 'https://'), '', $shop->getURL(false));
            }

            $shop_urls = array_unique($shop_urls);

            foreach ($shops as $id_shop) {

                // foreach langs, foreach shops ...
                $langs = Language::getLanguages(false, $id_shop, true);

                foreach ($langs as $id_lang) {
                    $old_link = false;
                    
                    switch ($type) {

                        case 'Manufacturer':

                            $old_link = $link->getManufacturerLink($object, null, null, null, $id_lang, $id_shop);

                            break;

                        case 'Supplier':

                            $old_link = $link->getSupplierLink($object, null, null, null, $id_lang, $id_shop);

                            break;

                        case 'CMS':

                            $old_link = $link->getCMSLink($object, null, null, $id_lang, $id_shop);

                            break;

                        case 'Product':

                            $old_link = $link->getProductLink($object, null, null, null, $id_lang, $id_shop);

                            break;

                        case 'Category':

                            $old_link = $link->getCategoryLink($object, null, $id_lang, null, $id_shop);

                            break;

                        case 'CMSCategory':

                            $old_link = $link->getCMSCategoryLink($object, null, $id_lang, $id_shop);

                            break;

                        default:

                            // UFO

                            break;

                    }

                    if ($old_link !== false) {

                        // Escape shops urls, it's a multishop relative approach
                        $old = str_replace($shop_urls, '', $old_link);
                        $old = '/'.ltrim($old, '/');

                        // Avoid duplicates, simplify complex mapings
                        $md5 = md5($old);

                        if (!isset($redirect_list[$md5])) {
                            $redirect_list[$md5]['old'] = $old;
                            $redirect_list[$md5]['shops'][] = $id_shop;
                        } elseif (!in_array($id_shop, $redirect_list[$md5]['shops'])) {
                            $redirect_list[$md5]['shops'][] = $id_shop;
                        }
                    }
                }
            }

            if (!empty($redirect_list)) {
                foreach ($redirect_list as $redirect) {
                    foreach ($redirect['shops'] as $id_shop) {
                        $this->checkConflict($redirect['old'], $id_shop);
                    }
                }
            }
        }
    }

    public function checkConflict($url = '', $id_shop = '', $direction = 'old')
    {
        if (empty($id_shop)) {
            $id_shop = Shop::getContextShopID();
        }

        if (!empty($url)) {
            $url = '/'.ltrim($url, '/');

            $results = Db::getInstance()->executeS('SELECT r.id_redirect, rs.id_shop FROM '._DB_PREFIX_.'redirect r INNER JOIN '._DB_PREFIX_.'redirect_shop rs ON (id_shop IN ('.(int) $id_shop.')) WHERE r.'.pSQL($direction).' = "'.pSQL($url).'" AND r.regex = 0 AND r.active = 1 GROUP BY r.id_redirect');

            if (!empty($results)) {
                foreach ($results as $result) {
                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'redirect_shop WHERE id_redirect = '.(int) $result['id_redirect'].' AND id_shop = '.(int) $result['id_shop']);
                }

                // Empty values
                Db::getInstance()->execute('SELECT * FROM '._DB_PREFIX_.'redirect r WHERE r.id_redirect NOT IN (SELECT id_redirect FROM '._DB_PREFIX_.'redirect_shop rs WHERE 1) AND regex = 0 GROUP BY r.id_redirect');
            }
        }

        return false;
    }

    public function insertRedirects($redirects, $force_active = false)
    {
        $db = Db::getInstance();

        $active = (bool) ($force_active === true || (bool) Configuration::get('MGRT_AUTOACTIVE') === true);

        foreach ($redirects as $redirect) {
            if (empty($redirect['shops'])) {
                continue;
            }

            // Checking potential conflicts
            foreach ($redirect['shops'] as $id_shop) {
                $old = '/'.ltrim($redirect['old'], '/');
                $new = (isset($redirect['new']) ? '/'.ltrim($redirect['new'], '/') : '/');

                $this->checkConflict($old, $id_shop);

                $id_redirect = $db->getValue('SELECT * FROM '._DB_PREFIX_.'redirect r WHERE r.regex = 0 AND r.old = "'.pSQL($old).'" AND r.new = "'.pSQL($new).'"');

                //Making new if don't already exist
                if (empty($id_redirect)) {

                    // Let's log it
                    $obj = new Redirect();
                    $obj->old = $old;
                    $obj->new = $new;
                    $obj->type = '301';
                    $obj->regex = false;
                    $obj->active = $active;
                    $obj->add();

                    $id_redirect = $obj->id;
                }

                $db->execute('INSERT INTO `'._DB_PREFIX_.'redirect_shop` (`id_redirect_shop`, `id_redirect`, `id_shop`) VALUES (NULL, '.(int) $id_redirect.', '.(int) $id_shop.')');
            }
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name || Tools::getValue('controller') == 'adminredirect') {
            if (version_compare(_PS_VERSION_, '1.6.0.1', '<')) {
                $this->context->controller->addCSS($this->_path.'views/css/admin_15.css');
            }
        }
    }

    public function findEquivalentUrl($uri)
    {

        // Fallback solution
        $solution = '/';
        
        // Cannot guess for statics files, let's ignore them
        if (preg_match('/\.css|\.js|\.txt|\.jpg|\.jpeg|\.png|\.bmp|\.wav|\.webp|\.gif|\.mp|\.ogg|\.otf|\.ttf|\.woff|\.svg/i', $uri)) {
            return $solution;
        }
        
        // Removing useless file formats
        $uri = str_replace(array('.html', '.htm', ), '', $uri);
        
        // Let's remove parent folders, it will be handled by autoredirect
        $temp_arr = explode('/', $uri);
        
        // PHP 7, not compatible with PHP 5 sadly
        if (function_exists('array_key_last')) {
            $last_key = array_key_last($temp_arr);
        } else {
            $last_key = key(array_slice($temp_arr, -1, 1, true));
        }

        $uri = $temp_arr[$last_key];
        
        // Let's find if there is int (ID or something else)
        preg_match_all('!\d+!', $uri, $matches);
        $test_ints = array();
        
        if (isset($matches[0]) && !empty($matches[0])) {
            $test_ints = $matches[0];
        }

        $db = Db::getInstance();

        $objects_to_search = array(
            'product',
            'category',
            'cms',
        );
        
        if (!empty($test_ints)) {
            foreach ($test_ints as $value) {
                foreach ($objects_to_search as $object_name) {
                    
                    // Lets find first if there is a match with ids
                    $id = $db->getValue('SELECT id_'.pSQL($object_name).' as id FROM '._DB_PREFIX_.pSQL($object_name).'_lang WHERE id_'.pSQL($object_name).' = '.(int) $value);

                    if (!empty($id)) {
                        $link = $this->createLink($object_name, (int) $id);

                        if ($link !== false) {

                            // Found a solution !
                            return $link;
                        }
                    }
                }
            }
        }

        // Let's try to find with words then
        if ($solution == '/') {

            // Lets remove ids first
            if (!empty($test_ints)) {
                foreach ($test_ints as $test_int) {
                    $uri = str_replace(array($test_int.'-', $test_int.'_', '-'.$test_int, '_'.$test_int, $test_int), '', $uri);
                }
            }

            foreach ($objects_to_search as $object_name) {
                
                // Lets find first if there is a match with ids
                $id = $db->getValue('SELECT id_'.pSQL($object_name).' as id FROM '._DB_PREFIX_.pSQL($object_name).'_lang WHERE link_rewrite LIKE "%'.pSQL($uri).'%"');

                if (!empty($id)) {
                    $link = $this->createLink($object_name, $id);

                    if ($link !== false) {

                        // Found a solution !
                        return $link;
                    }
                }
            }

            // Nothing found we redirect classic url with predefined settings
            $default_fallback = Configuration::get('MGRT_DEFAULT_REDIR');

            if (!empty($default_fallback) && strpos($default_fallback, "/") !== false) {
                $solution = $default_fallback;
            }

        }

        return $solution;
    }

    public function createLink($object_name, $id_object, $id_lang = null, $id_shop = null)
    {
        $context = Context::getContext();

        if (!$id_shop) {
            $id_shop = $context->shop->id;
        }

        if (!$id_lang) {
            $id_lang = $context->cookie->id_lang;
        }

        $result = false;
        $link = new Link();

        switch ($object_name) {

            case 'manufacturer':

                $manufacturer = new Manufacturer($id_object, $id_lang);

                if (Validate::isLoadedObject($manufacturer)) {
                    $result = $link->getManufacturerLink($manufacturer, null, null, null, $id_lang, $id_shop);
                }

                break;

            case 'supplier':

                $supplier = new Supplier($id_object, $id_lang);

                if (Validate::isLoadedObject($supplier)) {
                    $result = $link->getSupplierLink($supplier, null, $id_lang, $id_shop);
                }

                break;

            case 'cms':

                $cms = new CMS($id_object, $id_lang, $id_shop);

                if (Validate::isLoadedObject($cms)) {
                    $result = $link->getCMSLink($cms, null, null, $id_lang, $id_shop);
                }

                break;

            case 'product':

                $product = new Product($id_object, null, $id_lang, $id_shop);

                if (Validate::isLoadedObject($product)) {
                    $result = $link->getProductLink($product, null, null, null, $id_lang, $id_shop);
                }

                break;

            case 'category':

                $category = new Category($id_object, $id_lang, $id_shop);

                if (Validate::isLoadedObject($category)) {
                    $result = $link->getCategoryLink($category, null, $id_lang, null, $id_shop);
                }

                break;

            case 'cms_category':

                $cmscategory = new CMSCategory($id_object, $id_lang, $id_shop);

                if (Validate::isLoadedObject($product)) {
                    $result = $link->getCMSCategoryLink($cmscategory, null, $id_lang, $id_shop);
                }

                break;

            default:

                // UFO

                break;

        }

        return $result;
    }
}
