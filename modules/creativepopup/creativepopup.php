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

class CreativePopup extends Module
{
    public static $controllerClass;

    protected $init = false;
    protected $tabs = [
        'Creative Popup' => ['class' => 'AdminParentCreativePopup', 'active' => 1, 'icon' => 'filter_none'],
        'Popups' => ['class' => 'AdminCreativePopup', 'active' => 1],
        'Media Manager' => ['class' => 'AdminCreativePopupMedia', 'active' => 0],
        'Revisions' => ['class' => 'AdminCreativePopupRevisions', 'active' => 1],
        'Transition Builder' => ['class' => 'AdminCreativePopupTransition', 'active' => 1],
        'Skin Editor' => ['class' => 'AdminCreativePopupSkin', 'active' => 1],
        'CSS Editor' => ['class' => 'AdminCreativePopupStyle', 'active' => 1],
    ];
    protected $lang = [
        'fr' => [
            'Creative Popup' => 'Creative Popup',
            'Popups' => 'Popups',
            'Media Manager' => 'Directeur des médias',
            'Revisions' => 'Révisions',
            'Transition Builder' => 'Effets de Transition',
            'Skin Editor' => 'Éditeur de skin',
            'CSS Editor' => 'Éditeur de CSS',
        ],
    ];

    public function __construct()
    {
        $this->name = 'creativepopup';
        $this->tab = 'pricing_promotion';
        $this->version = '1.6.11';
        $this->author = 'WebshopWorks';
        $this->module_key = '23065bc7db8b0b549cbe2e13a83b572a';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.5.0.17', 'max' => _PS_VERSION_];
        $this->bootstrap = false;
        $this->displayName = 'Creative Popup';
        $this->description = 'Multifunctional responsive popup module';
        $this->confirmUninstall = 'Are you sure you want to uninstall?';
        parent::__construct();
        self::$controllerClass = str_replace('controller', '', strtolower(get_class($this->context->controller)));
    }

    public function install()
    {
        Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

        $db = Db::getInstance();
        $result = $db->execute('
            CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'creativepopup (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `author` int(11) NOT NULL DEFAULT 0,
                `name` varchar(100) DEFAULT "",
                `data` mediumtext NOT NULL,
                `date_c` int(11) NOT NULL,
                `date_m` int(11) NOT NULL,
                `schedule_start` int(11) NOT NULL DEFAULT 0,
                `schedule_end` int(11) NOT NULL DEFAULT 0,
                `flag_hidden` tinyint(1) NOT NULL DEFAULT 0,
                `flag_deleted` tinyint(1) NOT NULL DEFAULT 0,
                `flag_popup` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_general_ci
        ') && $db->execute('
            CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'creativepopup_revisions (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `popup_id` int(11) NOT NULL,
                `author` int(11) NOT NULL DEFAULT 0,
                `data` mediumtext NOT NULL,
                `date_c` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_general_ci
        ');

        if (!$result) {
            $this->_errors[] = $db->getMsgError();

            return false;
        }

        Configuration::get('CP_DATE_INSTALLED') || Configuration::updateValue('CP_DATE_INSTALLED', time());

        return parent::install();
    }

    protected function addTabs()
    {
        $parent = version_compare(_PS_VERSION_, '1.7.0', '<') ? 0 : (int) Tab::getIdFromClassName('CONFIGURE');
        foreach ($this->tabs as $name => $t) {
            $tab = new Tab();
            $tab->active = $t['active'];
            $tab->class_name = $t['class'];
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = isset($this->lang[$lang['iso_code']])
                    ? $this->lang[$lang['iso_code']][$name]
                    : $name
                ;
            }
            if (isset($t['icon'])) {
                $tab->icon = $t['icon'];
            }
            $tab->module = $this->name;
            $tab->id_parent = $parent;
            $tab->add();

            if ($t['class'] == 'AdminParentCreativePopup') {
                $parent = (int) Tab::getIdFromClassName($t['class']);
            }
        }
    }

    protected function deleteTabs()
    {
        foreach ($this->tabs as $t) {
            $id_tab = (int) Tab::getIdFromClassName($t['class']);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
    }

    public function enable($force_all = false)
    {
        if ($res = parent::enable($force_all)) {
            $this->addTabs();
            $this->registerHook('displayHeader');
            if (version_compare(_PS_VERSION_, '1.7.1', '<')) {
                $this->registerHook('displayBackOfficeHeader');
            }
        }

        return $res;
    }

    public function disable($force_all = false)
    {
        $this->deleteTabs();
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'tab WHERE `module` = "creativepopup"');
        $this->unregisterHook('displayHeader');
        $this->unregisterHook('displayBackOfficeHeader');

        return parent::disable($force_all);
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCreativePopup'));
    }

    public function generatePopup($id)
    {
        $display = false;
        foreach (CpPopups::$popups as &$popup) {
            if ($popup['id'] == $id) {
                $display = true;
                break;
            }
        }

        return $display ? CpShortcode::handleShortcode(['id' => $id]) : '';
    }

    protected function getPopupTpls()
    {
        $tpls = [];
        foreach (CpPopups::$index as &$index) {
            if ($tpl = $this->generatePopup($index['id'])) {
                $tpls[] = $tpl;
            }
        }

        return $tpls;
    }

    protected function ajaxDie($return)
    {
        exit(json_encode($return));
    }

    protected function processAjaxSubmitMessage()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            ${'_POST'}['contactKey'] = Tools::getValue('token');
            require_once _PS_FRONT_CONTROLLER_DIR_ . 'ContactController.php';

            $ctrl = new ContactController();
            $ctrl->postProcess();
            $this->ajaxDie(['errors' => $ctrl->errors]);
        } elseif ($contactform = Module::getInstanceByName('contactform')) {
            $contactform->sendMessage();
            $ctrl = $this->context->controller;
            $this->ajaxDie(['errors' => $ctrl->errors]);
        } else {
            // TODO
        }
    }

    protected function processAjaxSubmitAccount()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            require_once _PS_FRONT_CONTROLLER_DIR_ . 'AuthController.php';

            $ctrl = new AuthController();
            $ctrl->postProcess();
            $this->ajaxDie(['errors' => $ctrl->errors]);
        } else {
            $mcf = new ReflectionMethod($this->context->controller, 'makeCustomerForm');
            $mcf->setAccessible(true);
            $form = $mcf->invoke($this->context->controller)
                ->setGuestAllowed(false)
                ->fillWith(Tools::getAllValues());

            $errors = [];
            $res = array_reduce(
                Hook::exec('actionSubmitAccountBefore', [], null, true),
                function ($carry, $item) {
                    return $carry && $item;
                },
                true
            );

            if ($res) {
                $res = $form->submit();

                foreach ($form->getErrors() as $err) {
                    if (!empty($err)) {
                        $errors[] = $err[0];
                        break;
                    }
                }
            } else {
                $errors[] = 'Error during actionSubmitAccountBefore';
            }

            $this->ajaxDie([
                'redirect' => $res ? Tools::getValue('back', $_SERVER['REQUEST_URI']) : '',
                'errors' => $errors,
            ]);
        }
    }

    protected function processAjaxSubmitLogin()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            require_once _PS_MODULE_DIR_ . 'creativepopup/classes/CpAuthController.php';

            $cpAuth = new CpAuthController();
            $cpAuth->ajaxLogin();
        } else {
            $mcf = new ReflectionMethod($this->context->controller, 'makeLoginForm');
            $mcf->setAccessible(true);
            $form = $mcf->invoke($this->context->controller)
                ->fillWith(Tools::getAllValues());
            $res = $form->submit();
            $errors = [];
            foreach ($form->getErrors() as $err) {
                if (!empty($err)) {
                    $errors[] = $err[0];
                    break;
                }
            }
            $this->ajaxDie([
                'redirect' => $res ? Tools::getValue('back', $_SERVER['REQUEST_URI']) : '',
                'errors' => $errors,
            ]);
        }
    }

    protected function processAjaxSubmitNewsletter()
    {
        $module = version_compare(_PS_VERSION_, '1.7', '<') ? 'blocknewsletter' : 'ps_emailsubscription';

        class_exists($module) or require_once _PS_MODULE_DIR_ . "$module/$module.php";

        $result = Closure::bind(function () {
            $this->{'newsletterRegistration'}(${'_POST'}['blockHookName'] = 'displayCreativePopup');

            return [
                'success' => empty($this->valid) ? '' : $this->valid, // @phpstan-ignore-line
                'errors' => empty($this->error) ? [] : [$this->error], // @phpstan-ignore-line
            ];
        }, Module::getInstanceByName($module), $module)->__invoke(); // @phpstan-ignore-line

        $this->ajaxDie($result);
    }

    protected function processAjaxSubmitNewsletterAndName()
    {
        $errors = [];
        $email = Tools::getValue('email');

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $customer = new Customer();

            if (Validate::isEmail($email) && $customer->getByEmail($email, null, false)) {
                return $this->processAjaxSubmitNewsletter();
            }
            $customer->email = $email;
            $customer->passwd = md5(time() . _COOKIE_KEY_);
            $errors += $customer->validateController();
            $errors += $customer->validateFieldsRequiredDatabase();

            if (empty($errors)) {
                Configuration::set('PS_GUEST_CHECKOUT_ENABLED', true);

                $customer->active = true;
                $customer->is_guest = true;
                $customer->newsletter = false;
                $customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());

                if ($customer->add()) {
                    return $this->processAjaxSubmitNewsletter();
                }
                $errors[] = $this->l('An error occurred while subscribing to newsletter.');
            }
            $errors = array_values(array_unique($errors));
        } else {
            $customer = $this->context->customer;

            if (Customer::customerExists($email, true, false)) {
                return $this->processAjaxSubmitNewsletter();
            }
            $voucher = Configuration::get('NW_VOUCHER_CODE', false);
            Configuration::set('NW_VOUCHER_CODE', false);
            Configuration::set('PS_GUEST_CHECKOUT_ENABLED', true);

            $mcf = new ReflectionMethod($this->context->controller, 'makeCustomerForm');
            $mcf->setAccessible(true);
            $form = $mcf->invoke($this->context->controller)->fillWith(Tools::getAllValues());
            $form->submit();

            foreach ($form->getErrors() as $err) {
                if (!empty($err)) {
                    $errors[] = $err[0];
                    break;
                }
            }
            if (empty($errors)) {
                Configuration::set('NW_VOUCHER_CODE', $voucher);
                // restore original customer
                $this->context->updateCustomer($customer);
                $this->context->cart->update();

                return $this->processAjaxSubmitNewsletter();
            }
        }
        $this->ajaxDie(['errors' => $errors]);
    }

    protected function processAjax()
    {
        if (Tools::isSubmit('submitMessage')) {
            $this->processAjaxSubmitMessage();
        }
        if (Tools::isSubmit('submitAccount')) {
            $this->processAjaxSubmitAccount();
        }
        if (Tools::isSubmit('submitLogin')) {
            $this->processAjaxSubmitLogin();
        }
        if (Tools::isSubmit('submitNewsletter')) {
            if (Tools::isSubmit('firstname') || Tools::isSubmit('lastname')) {
                $this->processAjaxSubmitNewsletterAndName();
            } else {
                $this->processAjaxSubmitNewsletter();
            }
        }
    }

    public function hookDisplayHeader()
    {
        if (Tools::isSubmit('ajax')) {
            $this->processAjax();
        }
        require_once _PS_MODULE_DIR_ . 'creativepopup/helper.php';
        require_once _PS_MODULE_DIR_ . 'creativepopup/base/core.php';

        $tpls = $this->getPopupTpls();
        if (!empty($tpls)) {
            // add contact form token on PS v1.7.4+ | v1.6.1.17+
            if (version_compare(_PS_VERSION_, '1.7.4', '>=')) {
                if (empty($this->context->cookie->contactFormTokenTTL) || $this->context->cookie->contactFormTokenTTL < time()) {
                    $this->context->cookie->contactFormToken = md5(uniqid());
                    $this->context->cookie->contactFormTokenTTL = time() + 600;
                }
                $token = $this->context->cookie->contactFormToken;
            } elseif (version_compare(_PS_VERSION_, '1.6.1.17', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
                if (empty($this->context->cookie->contactFormKey)) {
                    $this->context->cookie->contactFormKey = md5(uniqid(microtime(), true));
                }
                $token = $this->context->cookie->contactFormKey;
            }
            empty($token) or Media::addJsDef(['cpContactToken' => $token]);

            cp_do_action('cp_enqueue_scripts');

            return cp_get_template($tpls);
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        return $this->display(__FILE__, 'views/templates/admin/header.tpl');
    }
}
