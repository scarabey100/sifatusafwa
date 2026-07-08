<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'cookiesplus/classes/CookiesPlusIdnovateValidation.php';
include_once _PS_MODULE_DIR_ . 'cookiesplus/classes/CookiesPlusCookie.php';
include_once _PS_MODULE_DIR_ . 'cookiesplus/classes/CookiesPlusFinality.php';
include_once _PS_MODULE_DIR_ . 'cookiesplus/classes/CookiesPlusUserConsent.php';
include_once _PS_MODULE_DIR_ . 'cookiesplus/classes/HTMLTemplateCookiesPlusModule.php';

class CookiesPlus extends Module
{
    public $addons_id_product;

    public function __construct()
    {
        $this->name = 'cookiesplus';
        $this->tab = 'front_office_features';
        $this->version = '1.8.2';
        $this->author = 'idnovate';
        $this->need_instance = 0;
        $this->module_key = '22c3b977fe9c819543a216a2fd948f22';
        // $this->author_address = '0xd89bcCAeb29b2E6342a74Bc0e9C82718Ac702160';
        $this->bootstrap = true;
        $this->addons_id_product = '21644';
        $this->ps_versions_compliancy = ['min' => '1.5', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Cookies - GDPR Cookie law (block before consent)');
        $this->description = $this->l('Make your store GDPR compliant using this module. This module lets you block the cookies until the customer gives his consent accepting the notice.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete the module and the related data?');

        if (empty($this->tabs)) {
            $this->tabs = [];
        }
        $tabs = $this->tabs;
        $tabs = array_merge($tabs, [
            [
                'class_name' => 'COOKIES',
                'parent_class_name' => 'CONFIGURE',
                'name' => [
                    'en' => 'Cookies configuration',
                    'es' => 'Configuración de cookies',
                    'de' => 'Konfiguration von Cookies',
                    'fr' => 'Configuration des cookies',
                    'it' => 'Configurazione dei cookie',
                    'nl' => 'Cookies configuratie',
                    'pl' => 'Konfiguracja plików cookie',
                    'pt' => 'Configuração de cookies',
                    'ro' => 'Configurarea modulelor cookie',
                    'ru' => 'Конфигурация файлов cookie',
                    'se' => 'Cookies konfiguration',
                ],
                'module' => $this->name,
                'icon' => 'group_work',
            ],
            [
                'class_name' => 'AdminCookiesPlusConfiguration',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Configuration',
                    'es' => 'Configuración',
                    'de' => 'Aufbau',
                    'fr' => 'Configuration',
                    'it' => 'Configurazione',
                    'nl' => 'Configuratie',
                    'pl' => 'Konfiguracja',
                    'pt' => 'Configuração',
                    'ro' => 'Configurație',
                    'ru' => 'Конфигурация',
                    'se' => 'Konfiguration',
                ],
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminCookiesPlusAppearance',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Appearance',
                    'es' => 'Apariencia',
                    'de' => 'Aussehen',
                    'fr' => 'Apparence',
                    'it' => 'Aspetto',
                    'nl' => 'Uiterlijk',
                    'pl' => 'Wygląd',
                    'pt' => 'Aparência',
                    'ro' => 'Aspect',
                    'ru' => 'вид',
                    'se' => 'Utseende',
                ],
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminCookiesPlusFinalities',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Cookie finalities',
                    'es' => 'Finalidades de cookie',
                    'de' => 'Cookie-Endgültigkeiten',
                    'fr' => 'Finalités des cookies',
                    'it' => 'Finalità dei cookie',
                    'nl' => 'Cookie finaliteiten',
                    'pl' => 'Ostateczna wersja plików cookie',
                    'pt' => 'Finalidades do cookie',
                    'ro' => 'Finalitățile cookie-urilor',
                    'ru' => 'Окончательность файлов cookie',
                    'se' => 'Cookie finaliteter',
                ],
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminCookiesPlusCookies',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Cookies',
                    'es' => 'Cookies',
                    'de' => 'Cookies',
                    'fr' => 'Cookies',
                    'it' => 'Cookies',
                    'nl' => 'Cookies',
                    'pl' => 'Cookies',
                    'pt' => 'Cookies',
                    'ro' => 'Cookies',
                    'ru' => 'Cookies',
                    'se' => 'Cookies',
                ],
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminCookiesPlusUsersConsent',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Users consent',
                    'es' => 'Consentimiento de los usuarios',
                    'de' => 'Zustimmung der Benutzer',
                    'fr' => 'Consentement des utilisateurs',
                    'it' => 'Consenso degli utenti',
                    'nl' => 'Gebruikers toestemming',
                    'pl' => 'Zgoda użytkowników',
                    'pt' => 'Consentimento dos usuários',
                    'ro' => 'Utilizatorii sunt de acord',
                    'ru' => 'Согласие пользователей',
                    'se' => 'Användarens samtycke',
                ],
                'module' => $this->name,
            ],
            [
                'class_name' => 'AdminCookiesPlusIntegration',
                'parent_class_name' => 'COOKIES',
                'name' => [
                    'en' => 'Integration with services',
                    'es' => 'Integración con servicios',
                    'de' => 'Integration mit Diensten',
                    'fr' => 'Intégration avec des services',
                    'it' => 'Integrazione con servizi',
                    'nl' => 'Integratie met diensten',
                    'pl' => 'Integracja z usługami',
                    'pt' => 'Integração com serviços',
                    'ro' => 'Integrare cu servicii',
                    'ru' => 'Интеграция с услугами',
                    'se' => 'Integration med tjänster',
                ],
                'module' => $this->name,
            ],
        ]);

        $this->tabs = $tabs;
    }

    public function install()
    {
        $result = $this->copyOverrideFolder();
        if (!$result) {
            $this->_errors[] = $this->l('Error copying overrides');

            return false;
        }

        $result = parent::install();
        if (!$result) {
            $this->_errors[] = $this->l('Error in parent::install') . ' ' . implode(' - ', $this->_errors);

            return false;
        }

        $result = include dirname(__FILE__) . '/sql/install.php';
        if (!$result) {
            $this->_errors[] = $this->l('Error creating tables') . ' ' . implode(' - ', $this->_errors);

            return false;
        }

        // Hooks
        $result = true;
        $result &= $this->registerHook('displayHeader');

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $result &= $this->registerHook('displayTop');
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $result &= $this->registerHook('displayBeforeBodyClosingTag');
        }

        if (Module::isInstalled('mobile_theme')) {
            $result &= $this->registerHook('displayMobileHeader');
        }
        $result &= $this->registerHook('displayMyAccountBlock');
        $result &= $this->registerHook('displayMyAccountBlockfooter');
        $result &= $this->registerHook('tmMegaLayoutFooter');
        $result &= $this->registerHook('displayCookies');
        $result &= $this->registerHook('displayCookiesHeader');
        $result &= $this->registerHook('displayCustomerAccount');
        $result &= $this->registerHook('displayBackOfficeHeader');
        $result &= $this->registerHook('displayAfterBodyOpeningTag');
        $result &= $this->registerHook('actionShopDataDuplication');
        $result &= $this->registerHook('actionOutputHTMLBefore');
        $result &= $this->registerHook('FilterCmsContent');

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $result &= $this->registerHook('actionFrontControllerSetMedia');
        }

        // There is a bug with the module cmsproductspro, which strips the code inserted by this module. So we move the cmsproductspro module to an above position
        $this->updatePosition(Hook::getIdByName('actionOutputHTMLBefore'), 0, 1);
        if ($module = Module::getInstanceByName('cmsproductspro')) {
            $module->updatePosition(Hook::getIdByName('actionOutputHTMLBefore'), 0, 1);
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $result &= $this->registerHook('actionDispatcher');
        } else {
            $result &= $this->registerHook('moduleRoutes');
        }

        // GDPR module
        $result &= $this->registerHook('registerGDPRConsent');

        if (!$result) {
            $this->_errors[] = $this->l('Error registering hooks');

            return false;
        }

        // Tabs
        $result = $this->installTabs($this->tabs);
        if (!Tab::getIdFromClassName($this->tabs[0]['class_name'])) {
            $result = $this->installTabs($this->tabs, true);
        }
        if (!$result) {
            $this->_errors[] = $this->l('Error installing tabs');

            return false;
        }

        $result = $this->setDefaultValues();
        if (!$result) {
            $this->_errors[] = $this->l('Error setting default values');

            return false;
        }

        if (!Configuration::get('C_P_COOKIES_POLICIES')) {
            $result = $this->createCookiesPolicyCms();
            if (!$result) {
                $this->_errors[] = $this->l('Error creating CMS');

                return false;
            }
        }

        $result = self::clearCache();
        if (!$result) {
            $this->_errors[] = $this->l('Error clearing cache');

            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $result = true;

        $result &= $this->copyOverrideFolder();

        $result &= parent::uninstall();

        $result &= $this->uninstallTabs();

        include dirname(__FILE__) . '/sql/uninstall.php';

        $result &= self::clearCache();

        return (bool) $result;
    }

    public function enable($force_all = false)
    {
        if (!$this->copyOverrideFolder()) {
            return false;
        }

        $result = true;

        $result &= parent::enable($force_all);

        return (bool) $result;
    }

    public function disable($force_all = false)
    {
        if (!$this->copyOverrideFolder()) {
            return false;
        }

        $result = true;

        $result &= parent::disable($force_all);

        return (bool) $result;
    }

    public function installTabs($moduleTabs = null, $force = false)
    {
        if (!$moduleTabs) {
            $moduleTabs = $this->tabs;
        }

        if (!$force
            && version_compare(_PS_VERSION_, '1.7.1', '>=')
            && version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            return true;
        }

        $languages = Language::getLanguages(false);

        foreach ($moduleTabs as $moduleTab) {
            if (!Tab::getIdFromClassName($moduleTab['class_name'])) {
                $tab = new Tab();
                $tab->class_name = $moduleTab['class_name'];
                $tab->module = $moduleTab['module'];
                $tab->active = 1;

                foreach ($languages as $language) {
                    if (is_array($moduleTab['name'])) {
                        if (isset($moduleTab['name'][$language['iso_code']]) && $moduleTab['name'][$language['iso_code']]) {
                            $tab->name[$language['id_lang']] = $moduleTab['name'][$language['iso_code']];
                        } else {
                            $tab->name[$language['id_lang']] = $moduleTab['name']['en'];
                        }
                    } else {
                        $tab->name[$language['id_lang']] = $moduleTab['name'];
                    }
                }

                if (isset($moduleTab['parent_class_name']) && is_string($moduleTab['parent_class_name'])) {
                    $tab->id_parent = Tab::getIdFromClassName($moduleTab['parent_class_name']);
                } elseif (isset($moduleTab['id_parent'])) {
                    $tab->id_parent = $moduleTab['id_parent'];
                } else {
                    $tab->id_parent = -1;
                }

                if (isset($moduleTab['icon'])) {
                    $tab->icon = $moduleTab['icon'];
                }

                $tab->add();
                if (!$tab->id) {
                    return false;
                }
            }
        }

        return true;
    }

    public function uninstallTabs($moduleTabs = null)
    {
        if (!$moduleTabs) {
            $moduleTabs = Tab::getCollectionFromModule($this->name);
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        } else {
            foreach ($moduleTabs as $moduleTab) {
                $idTab = Tab::getIdFromClassName($moduleTab['class_name']);

                if ($idTab) {
                    $tab = new Tab($idTab);
                    $tab->delete();
                }
            }
        }

        return true;
    }

    public static function clearCache()
    {
        if (method_exists('Tools', 'clearAllCache')) {
            Tools::clearAllCache();
        }

        if (method_exists('Tools', 'clearSmartyCache')) {
            Tools::clearSmartyCache();
        }

        if (method_exists('Tools', 'clearSf2Cache')) {
            Tools::clearSf2Cache();
        }

        if (method_exists('Tools', 'clearCache')) {
            Tools::clearCache(Context::getContext()->smarty);
        }

        if (method_exists('Media', 'clearCache')) {
            Media::clearCache();
        }

        $version = (int) Configuration::get('PS_CCCJS_VERSION');
        if ($version) {
            Configuration::updateValue('PS_CCCJS_VERSION', ++$version);
        }

        $version = (int) Configuration::get('PS_CCCCSS_VERSION');
        if ($version) {
            Configuration::updateValue('PS_CCCCSS_VERSION', ++$version);
        }

        return true;
    }

    public function setDefaultValues()
    {
        Configuration::updateValue('C_P_REVOKE_CONSENT', date('Y-m-d H:i:s', time()));

        Configuration::updateValue('C_P_REFRESH', 0);
        Configuration::updateValue('C_P_EXPIRY', '365');
        Configuration::updateValue('C_P_BOTS', 'Ahrefs|ADmantX|Alexa|AskJeeves|Baidu|Bing|Butterfly|Cookiebot|crawler|DuckDuckGo|exabot|Evaliant|Facebook|Firefly|Froogle|Gigabot|Google|Googlebot|Grapeshot|Inktomi|InfoSeek|Lighthouse|Looksmart|MeanPath|Mediapartners-Google|Me.dium|MJ12bot|MSN|NationalDirectory|OpenSiteExplorer|Pinterest|Proximic|Rankivabot|Scooter|Sogou|Sogouwebspider|Sosospider|Squider|TechnoratiSnoop|TECNOSEEK|Teoma|TweetmemeBot|TweetMeme|Twiceler|Twitturls|URL_Spider_SQL|WebAltaCrawler|WebFindBot|www.galaxy.com|Yaho|Yandex|Ahrefs|YodaoBot');
        Configuration::updateValue('C_P_HOOK_POSITION', 0);
        Configuration::updateValue('C_P_OVERLAY_OPACITY', '0.5');
        Configuration::updateValue('C_P_GEO', '1');
        Configuration::updateValue('C_P_POSITION', 'center');
        Configuration::updateValue('C_P_WIDTH', '50');
        Configuration::updateValue('C_P_ACCEPT_DISPLAY', 1);
        Configuration::updateValue('C_P_MORE_INFO_DISPLAY', 1);
        Configuration::updateValue('C_P_OVERLAY', 1);
        Configuration::updateValue('C_P_OVERLAY_OPACITY', '0.5');

        Configuration::updateValue('C_P_BUTTONS_LAYOUT', 6);

        Configuration::updateValue('C_P_FONT_COLOR', '#000');
        Configuration::updateValue('C_P_BACKGROUND_COLOR', '#FFFFFF');

        Configuration::updateValue('C_P_ACCEPT_FONT_SIZE', '16px');
        Configuration::updateValue('C_P_ACCEPT_BACKGROUND_COLOR', '#20BF6B');
        Configuration::updateValue('C_P_ACCEPT_BORDER_COLOR', '#20BF6B');
        Configuration::updateValue('C_P_ACCEPT_FONT_COLOR', '#FFFFFF');

        Configuration::updateValue('C_P_MORE_INFO_FONT_SIZE', '16px');
        Configuration::updateValue('C_P_MORE_INFO_BACKGROUND_COLOR', '#FFFFFF');
        Configuration::updateValue('C_P_MORE_INFO_BORDER_COLOR', '#7A7A7A');
        Configuration::updateValue('C_P_MORE_INFO_FONT_COLOR', '#000');

        Configuration::updateValue('C_P_REJECT_FONT_SIZE', '16px');
        Configuration::updateValue('C_P_REJECT_BACKGROUND_COLOR', '#20BF6B');
        Configuration::updateValue('C_P_REJECT_BORDER_COLOR', '#20BF6B');
        Configuration::updateValue('C_P_REJECT_FONT_COLOR', '#FFFFFF');

        Configuration::updateValue('C_P_SAVE_FONT_SIZE', '16px');
        Configuration::updateValue('C_P_SAVE_BACKGROUND_COLOR', '#FFFFFF');
        Configuration::updateValue('C_P_SAVE_BORDER_COLOR', '#7A7A7A');
        Configuration::updateValue('C_P_SAVE_FONT_COLOR', '#000');

        Configuration::updateValue('C_P_REJECT_DISPLAY', '1');
        Configuration::updateValue('C_P_DEFAULT_CONSENT', true);

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            Configuration::updateValue('C_P_MATERIAL_ICONS_LIBRARY', '1');
            Configuration::updateValue('C_P_MATERIAL_ICONS', 1);
        } else {
            if ($this->context->shop->theme->getName() === 'panda') {
                Configuration::updateValue('C_P_MATERIAL_ICONS_LIBRARY', 2);
            } else {
                Configuration::updateValue('C_P_MATERIAL_ICONS_LIBRARY', 1);
            }
        }

        // Enable Consent Mode by default if a Google module is detected
        if (self::googleModuleDetected()) {
            Configuration::updateValue('C_P_GTM_ENABLE', true);
        }

        Configuration::updateValue('C_P_GTM_URL_PASSTHROUGH', false);
        Configuration::updateValue('C_P_GTM_ADS_DATA_REDACTION', true);

        $cookiesDefault = [];
        // English
        $langCode = 'en';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Your cookie settings<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>This store asks you to accept cookies for performance, social media and advertising purposes. Social media and advertising cookies of third parties are used to offer you social media functionalities and personalized ads. Do you accept these cookies and the processing of personal data involved?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies help us enhance your browsing experience. By accepting, you\'ll enjoy a smoother and more personalized journey on our site. Please reconsider allowing cookies for the best experience.<' . '/p>';

        // Spanish
        $langCode = 'es';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Tu configuración de cookies<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Esta tienda te pide que aceptes cookies para fines de rendimiento, redes sociales y publicidad. Las redes sociales y las cookies publicitarias de terceros se utilizan para ofrecerte funciones de redes sociales y anuncios personalizados. ¿Aceptas estas cookies y el procesamiento de datos personales involucrados?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Las cookies nos ayudan a mejorar tu experiencia de navegación. Al aceptarlas, disfrutarás de una experiencia más fluida y personalizada en nuestro sitio. Por favor, reconsidera permitir las cookies para obtener la mejor experiencia.<' . '/p>';

        // French
        $langCode = 'fr';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Vos paramètres de cookies<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Ce magasin vous demande d\'accepter les cookies afin d\'optimiser les performances, les fonctionnalités des réseaux sociaux et la pertinence de la publicité. Les cookies tiers liés aux réseaux sociaux et à la publicité sont utilisés pour vous offrir des fonctionnalités optimisées sur les réseaux sociaux, ainsi que des publicités personnalisées. Acceptez-vous ces cookies ainsi que les implications associées à l\'utilisation de vos données personnelles ?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Les cookies nous aident à améliorer votre expérience de navigation. En les acceptant, vous profiterez d’une expérience plus fluide et personnalisée sur notre site. Veuillez reconsidérer l’acceptation des cookies pour une meilleure expérience.<' . '/p>';

        // French (Canada)
        $langCode = 'qc';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Vos paramètres de cookies<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Ce magasin vous demande d\'accepter les cookies afin d\'optimiser les performances, les fonctionnalités des réseaux sociaux et la pertinence de la publicité. Les cookies tiers liés aux réseaux sociaux et à la publicité sont utilisés pour vous offrir des fonctionnalités optimisées sur les réseaux sociaux, ainsi que des publicités personnalisées. Acceptez-vous ces cookies ainsi que les implications associées à l\'utilisation de vos données personnelles ?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Les cookies nous aident à améliorer votre expérience de navigation. En les acceptant, vous profiterez d’une expérience plus fluide et personnalisée sur notre site. Veuillez reconsidérer l’acceptation des cookies pour une meilleure expérience.<' . '/p>';

        // Polish
        $langCode = 'pl';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Ustawienia plików cookie<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Niniejsza witryna wykorzystuje pliki cookies w celu świadczenia usług na najwyższym poziomie i w sposób dostosowany do indywidualnych potrzeb. Korzystanie z witryny bez zmiany ustawień dotyczących cookies oznacza, że będą one zamieszczane w urządzeniu końcowym. Jeśli nie akceptujesz opuść tę stronę internetową.<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Pliki cookies pomagają nam poprawić Twoje doświadczenia podczas przeglądania. Akceptując je, będziesz cieszyć się płynniejszą i bardziej spersonalizowaną wizytą na naszej stronie. Proszę, przemyśl ponownie pozwolenie na pliki cookies dla lepszego doświadczenia.<' . '/p>';

        // Romanian
        $langCode = 'ro';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Setările cookie-urilor<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Acest magazin vă solicită să acceptați cookie-uri pentru performanță, media și publicitate. Mediile sociale și cookie-urile de publicitate ale unor terțe părți sunt utilizate pentru a vă oferi funcții de social media și anunțuri personalizate. Acceptați aceste cookie-uri și procesarea datelor personale implicate?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookie-urile ne ajută să îmbunătățim experiența ta de navigare. Acceptându-le, vei beneficia de o experiență mai fluidă și personalizată pe site-ul nostru. Te rugăm să reconsideri acceptarea cookie-urilor pentru cea mai bună experiență.<' . '/p>';

        // Portuguese
        $langCode = 'pt';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>As tuas configurações de cookies<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Esta loja pede-te para aceitares cookies para efeitos de desempenho, redes sociais e publicidade. Os cookies de publicidade e de redes sociais de terceiros são utilizados para te oferecer funcionalidades sociais e anúncios personalizados. Aceitas estes cookies e o processamento de dados pessoais envolvidos?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Os cookies nos ajudam a melhorar sua experiência de navegação. Ao aceitá-los, você desfrutará de uma experiência mais suave e personalizada em nosso site. Por favor, repense permitir cookies para a melhor experiência.<' . '/p>';

        // Slovak
        $langCode = 'sk';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Nastavenia súborov cookie<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Náš obchod používa súbory cookie za účelom zabezpečenia nevyhnutnej funkcionality stránok, sociálnych médií a marketingu. Súhlasíte s týmito súbormi cookies a spracovaním príslušných osobných údajov?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies nám pomáhajú zlepšiť vaše skúsenosti pri prehliadaní. Ak ich prijmete, užijete si plynulejšiu a personalizovanejšiu skúsenosť na našej stránke. Prosím, zvážte umožnenie cookies pre lepší zážitok.<' . '/p>';

        // Nederlands
        $langCode = 'nl';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Je cookie-instellingen<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Deze winkel vraagt je om cookies te accepteren voor betere prestaties en voor sociale-media- en advertentiedoeleinden. Er worden sociale-media- en advertentiecookies van derden gebruikt om je sociale-mediafunctionaliteit en persoonlijke advertenties te bieden. Accepteer je deze cookies en de bijbehorende verwerking van je persoonsgegevens?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies helpen ons uw browse-ervaring te verbeteren. Door cookies te accepteren, geniet u van een soepelere en meer gepersonaliseerde ervaring op onze site. Overweeg alstublieft opnieuw om cookies toe te staan voor de beste ervaring.<' . '/p>';

        // Deutsch
        $langCode = 'de';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Ihre Cookie-Einstellungen<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Für eine optimal Performance, eine reibungslose Verwendung sozialer Medien und aus Werbezwecken empfiehlt dir dieser Laden, der Verwendung von Cookies zuzustimmen. Durch Cookies von sozialen Medien und Werbecookies von Drittparteien hast du Zugriff auf Social-Media-Funktionen und erhältst personalisierte Werbung. Stimmst du der Verwendung dieser Cookies und der damit verbundenen Verarbeitung deiner persönlichen Daten zu?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies helfen uns, Ihr Surferlebnis zu verbessern. Wenn Sie Cookies akzeptieren, genießen Sie eine reibungslosere und persönlichere Erfahrung auf unserer Website. Bitte überdenken Sie, ob Sie Cookies zulassen möchten, um die beste Erfahrung zu erhalten.<' . '/p>';

        // Greek
        $langCode = 'gr';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Οι ρυθμίσεις cookie σας<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Αυτό το κατάστημα σου ζητά να αποδεχτείς τα cookies για σκοπούς απόδοσης, κοινωνικής δικτύωσης και διαφήμισης. Τα cookies κοινωνικής δικτύωσης και διαφήμισης παρέχονται από τρίτα μέρη για να σου προσφέρουν λειτουργίες κοινωνικής δικτύωσης και εξατομικευμένες διαφημίσεις. Αποδέχεσαι αυτά τα cookies και την συνεπαγόμενη επεξεργασία προσωπικών δεδομένων;<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Τα cookies μας βοηθούν να βελτιώσουμε την εμπειρία σας κατά την περιήγηση. Αποδεχόμενοι τα cookies, θα απολαύσετε μια πιο ομαλή και εξατομικευμένη εμπειρία στον ιστότοπό μας. Σκεφτείτε ξανά να επιτρέψετε τα cookies για την καλύτερη εμπειρία.<' . '/p>';

        // Italian
        $langCode = 'it';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Impostazioni dei cookie<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Questo negozio richiede di accettare i cookie per scopi legati a prestazioni, social media e annunci pubblicitari. I cookie di terze parti per social media e a scopo pubblicitario vengono utilizzati per offrire funzionalità social e annunci pubblicitari personalizzati. Accetti i cookie e l\'elaborazione dei dati personali interessati?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>I cookie ci aiutano a migliorare la tua esperienza di navigazione. Accettandoli, godrai di un’esperienza più fluida e personalizzata sul nostro sito. Ti preghiamo di considerare nuovamente l’accettazione dei cookie per la migliore esperienza possibile.<' . '/p>';

        // Svenska
        $langCode = 'sv';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Dina cookieinställningar<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Denna butik ber dig att godkänna cookies för anpassning av prestanda, sociala medier och marknadsföring. Tredjepartscookies för sociala medier och marknadsföring används för att erbjuda anpassade annonser och funktioner för sociala medier. Godkänner du dessa cookies och behandlingen av berörda personuppgifter?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies hjälper oss att förbättra din webbläsarupplevelse. Genom att acceptera cookies kommer du att få en smidigare och mer personlig upplevelse på vår webbplats. Vänligen överväg att tillåta cookies för den bästa upplevelsen.<' . '/p>';

        // Dansk
        $langCode = 'da';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Dine indstillinger for cookies<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Denne butik beder dig om at acceptere cookies til performance, sociale medier og reklameformål. Sociale medier og tredjeparts annoncecookies bruges til at tilbyde dig funktionaliteter og tilpassede annoncer på sociale medier. Vil du acceptere disse cookies og behandlingen af implicerede personoplysninger?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies hjælper os med at forbedre din browseroplevelse. Ved at acceptere dem vil du få en mere glidende og personlig oplevelse på vores site. Overvej venligst at tillade cookies for den bedste oplevelse.<' . '/p>';

        // Norsk
        $langCode = 'no';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Dine innstillinger for informasjonskapsler<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Denne butikken spør om du godtar informasjonskapsler for ytelsesformål, sosiale medier og annonsering. Informasjonskapsler for sosiale medier og annonsering fra tredjeparter brukes for å tilby deg funksjoner på sosiale medier og tilpassede annonser. Godtar du disse informasjonskapslene og den involverte behandlingen av personopplysningene dine?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies hjelper oss med å forbedre din nettleseropplevelse. Ved å akseptere dem vil du få en jevnere og mer personlig opplevelse på nettstedet vårt. Vennligst vurder å tillate cookies for best mulig opplevelse.<' . '/p>';

        // ČEŠTINA
        $langCode = 'cs';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Tvá nastavení souborů cookie<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Společnost tento obchod žádá o tvůj souhlas s používáním souborů cookie pro účely výkonu, sociálních médií a reklamy. Sociální média a reklamní soubory cookie třetích stran používáme k tomu, abychom ti mohli nabízet funkce sociálních médií a přizpůsobenou reklamu. Další informace nebo doplnění nastavení získáš kliknutím na tlačítko „Více informací“ nebo otevřením nabídky „Nastavení souborů cookie“ v dolní části webové stránky. Podrobnější informace o souborech cookie a zpracování tvých osobních údajů najdeš v našich Zásadách ochrany osobních údajů a používání souborů cookie. Souhlasíš s používáním souborů cookie a zpracováním souvisejících osobních údajů?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>Cookies nám pomáhají zlepšit vaše prohlížecí zkušenosti. Přijetím cookies si užijete plynulejší a personalizovanější zážitek na našich stránkách. Prosím, zvažte povolení cookies pro nejlepší možnou zkušenost.<' . '/p>';

        // Magyar
        $langCode = 'hu';
        $cookiesDefault['title'][$langCode] = '<' . 'strong>Cookie-beállítások<' . '/strong>';
        $cookiesDefault['text'][$langCode] = '<' . 'p>Ez a bolt a megfelelő teljesítmény és a közösségimédia-funkciók biztosításához, valamint a hirdetések megjelenítéséhez kéri a cookie-k elfogadását. A harmadik felek közösségimédia- és hirdetési cookie-jai használatával biztosítunk közösségimédia-funkciókat, és jelenítünk meg személyre szabott reklámokat. Ha több információra van szükséged, vagy kiegészítenéd a beállításaidat, kattints a További információ gombra, vagy keresd fel a webhely alsó részéről elérhető Cookie-beállítások területet. A cookie-kkal kapcsolatos további információért, valamint a személyes adatok feldolgozásának ismertetéséért tekintsd meg Adatvédelmi és cookie-kra vonatkozó szabályzatunkat. Elfogadod ezeket a cookie-kat és az érintett személyes adatok feldolgozását?<' . '/p>';
        $cookiesDefault['text_encourage'][$langCode] = '<' . 'p>A cookie-k segítenek javítani a böngészési élményedet. Ha elfogadod őket, simább és személyre szabottabb élményben lesz részed weboldalunkon. Kérlek, fontold meg a cookie-k engedélyezését a legjobb élmény érdekében.<' . '/p>';

        $cookiesDefault['cookie'][CookiesPlusFinality::NECESSARY_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::NECESSARY_COOKIE);
        $cookiesDefault['cookie'][CookiesPlusFinality::PREFERENCE_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::PREFERENCE_COOKIE);
        $cookiesDefault['cookie'][CookiesPlusFinality::STATISTIC_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::STATISTIC_COOKIE);
        $cookiesDefault['cookie'][CookiesPlusFinality::MARKETING_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::MARKETING_COOKIE);
        // $cookiesDefault['cookie'][CookiesPlusFinality::UNCLASSIFIED_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::UNCLASSIFIED_COOKIE);
        // $cookiesDefault['cookie'][CookiesPlusFinality::PERFORMANCE_COOKIE] = CookiesPlusFinality::getDefaultValues(CookiesPlusFinality::PERFORMANCE_COOKIE);

        $fields = [];
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $languageCode = strtok($lang['language_code'], '-');

            // $fields['C_P_TITLE'][$lang['id_lang']] = (isset($cookiesDefault['title'][$languageCode]) && $cookiesDefault['title'][$languageCode]) ? $cookiesDefault['title'][$languageCode] : $cookiesDefault['title']['en'];
            $fields['C_P_TEXT_BASIC'][$lang['id_lang']] = (isset($cookiesDefault['text'][$languageCode]) && $cookiesDefault['text'][$languageCode]) ? $cookiesDefault['text'][$languageCode] : $cookiesDefault['text']['en'];
            $fields['C_P_TEXT_ENCOURAGEMENT'][$lang['id_lang']] = (isset($cookiesDefault['text_encourage'][$languageCode]) && $cookiesDefault['text_encourage'][$languageCode]) ? $cookiesDefault['text'][$languageCode] : $cookiesDefault['text_encourage']['en'];
        }

        // Configuration::updateValue('C_P_TITLE', $fields['C_P_TITLE'], true);
        Configuration::updateValue('C_P_TEXT_BASIC', $fields['C_P_TEXT_BASIC'], true);
        Configuration::updateValue('C_P_TEXT_BASIC', $fields['C_P_TEXT_BASIC'], true);

        $modules = Module::getModulesOnDisk(true);
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops(false, null, true);
        } else {
            $shops = [1];
        }

        $cookiesPlusNecessaryFinalityId = null;
        $cookiesPlusStatisticFinalityId = null;
        $cookiesPlusMarketingFinalityId = null;

        foreach ($shops as $shop) {
            foreach ($cookiesDefault['cookie'] as $cookiesPlusFinalityId => $cookieDefault) {
                $cookiesPlusFinality = new CookiesPlusFinality();
                $cookiesPlusFinality->id_shop = $shop;
                $cookiesPlusFinality->technical = (isset($cookieDefault['technical']) && $cookieDefault['technical']) ? $cookieDefault['technical'] : 0;
                $cookiesPlusFinality->active = (isset($cookieDefault['active']) && $cookieDefault['active']) ? $cookieDefault['active'] : 0;
                $cookiesPlusFinality->position = $cookieDefault['position'];

                if (isset($cookieDefault['modules']) && $cookieDefault['modules']) {
                    $modulesIds = [];
                    foreach ($modules as $module) {
                        if ($module->installed && in_array($module->name, $cookieDefault['modules'])) {
                            $modulesIds[] = $module->id;
                        }
                    }

                    $cookiesPlusFinality->modules = json_encode($modulesIds);

                    // If store has any of the modules, enable this finality
                    if ($modulesIds) {
                        $cookiesPlusFinality->active = 1;
                    }
                }

                foreach ($languages as $lang) {
                    $languageCode = strtok($lang['language_code'], '-');
                    $cookiesPlusFinality->name[$lang['id_lang']] = (isset($cookieDefault['name'][$languageCode]) && $cookieDefault['name'][$languageCode]) ? $cookieDefault['name'][$languageCode] : $cookieDefault['name']['en'];
                    $cookiesPlusFinality->description[$lang['id_lang']] = (isset($cookieDefault['description'][$languageCode]) && $cookieDefault['description'][$languageCode]) ? $cookieDefault['description'][$languageCode] : $cookieDefault['description']['en'];
                }

                $cookiesPlusFinality->date_add = date('Y-m-d H:i:s');
                $cookiesPlusFinality->date_upd = date('Y-m-d H:i:s');

                // Enable Statistic and Marketing cookies by default for GTM
                if ($cookiesPlusFinalityId === CookiesPlusFinality::STATISTIC_COOKIE
                    || $cookiesPlusFinalityId === CookiesPlusFinality::MARKETING_COOKIE) {
                    $cookiesPlusFinality->active = 1;
                }

                $result = $cookiesPlusFinality->save();

                if ($cookiesPlusFinalityId === CookiesPlusFinality::NECESSARY_COOKIE) {
                    $cookiesPlusNecessaryFinalityId = $cookiesPlusFinality->id;
                }

                if ($cookiesPlusFinalityId === CookiesPlusFinality::STATISTIC_COOKIE) {
                    $cookiesPlusStatisticFinalityId = $cookiesPlusFinality->id;
                }

                if ($cookiesPlusFinalityId === CookiesPlusFinality::MARKETING_COOKIE) {
                    $cookiesPlusMarketingFinalityId = $cookiesPlusFinality->id;
                }

                if (!$result) {
                    return false;
                }

                if (isset($cookieDefault['cookies']) && $cookieDefault['cookies']) {
                    foreach ($cookieDefault['cookies'] as $cookie) {
                        $cookiesPlusCookie = new CookiesPlusCookie();
                        $cookiesPlusCookie->id_shop = $shop;
                        $cookiesPlusCookie->id_cookiesplus_finality = $cookiesPlusFinality->id;
                        $cookiesPlusCookie->active = $cookie['active'];

                        $cookiesPlusCookie->name = $cookie['name'];
                        $cookiesPlusCookie->provider = isset($cookie['provider']) ? $cookie['provider'] : '';
                        $cookiesPlusCookie->provider_url = isset($cookie['provider_url']) ? $cookie['provider_url'] : '';

                        // If store has any of the modules, enable this finality
                        if (isset($cookie['modules']) && $cookie['modules']) {
                            foreach ($modules as $module) {
                                if ($module->installed && isset($module->name) && in_array($module->name, $cookie['modules'])) {
                                    $cookiesPlusCookie->active = 1;
                                    $cookiesPlusFinality = new CookiesPlusFinality($cookiesPlusFinality->id);
                                    $cookiesPlusFinality->active = 1;

                                    $cookiesPlusFinality->save();
                                    break;
                                }
                            }
                        }

                        foreach ($languages as $lang) {
                            $languageCode = strtok($lang['language_code'], '-');

                            if (isset($cookie['purpose']['en'])) {
                                $cookiesPlusCookie->purpose[$lang['id_lang']] = (isset($cookie['purpose'][$languageCode]) && $cookie['purpose'][$languageCode]) ? $cookie['purpose'][$languageCode] : $cookie['purpose']['en'];
                            }

                            if (isset($cookie['expiry']['en'])) {
                                $cookiesPlusCookie->expiry[$lang['id_lang']] = (isset($cookie['expiry'][$languageCode]) && $cookie['expiry'][$languageCode]) ? $cookie['expiry'][$languageCode] : $cookie['expiry']['en'];
                            }
                        }

                        $cookiesPlusCookie->date_add = date('Y-m-d H:i:s');
                        $cookiesPlusCookie->date_upd = date('Y-m-d H:i:s');

                        $cookiesPlusCookie->save();
                    }
                }
            }

            // GTM
            $gtm = [
                $cookiesPlusNecessaryFinalityId => [
                    'cookiesPlusFinality' => $cookiesPlusNecessaryFinalityId,
                    'gtmFinality' => [
                        'functionality_storage' => true,
                        'personalization_storage' => true,
                        'security_storage' => true,
                    ],
                    'firingEvent' => '',
                ],
                $cookiesPlusStatisticFinalityId => [
                    'cookiesPlusFinality' => $cookiesPlusStatisticFinalityId,
                    'gtmFinality' => [
                        'analytics_storage' => true,
                    ],
                    'firingEvent' => '',
                ],
                $cookiesPlusMarketingFinalityId => [
                    'cookiesPlusFinality' => $cookiesPlusMarketingFinalityId,
                    'gtmFinality' => [
                        'ad_storage' => true,
                        'ad_user_data' => true,
                        'ad_personalization' => true,
                    ],
                    'firingEvent' => '',
                ],
            ];
            $gtm = json_encode($gtm);
            Configuration::updateValue('C_P_GTM_CONSENT', $gtm, false, null, $shop);
        }

        return true;
    }

    public function createCookiesPolicyCms()
    {
        $languages = Language::getLanguages(false);

        $cms = new CMS();
        $cms->link_rewrite = [];
        $cms->meta_title = [];
        $cms->meta_description = [];
        // $cms->meta_keywords = [];
        $cms->content = [];
        $cms->active = 1;

        foreach ($languages as $lang) {
            $id_lang = (int)$lang['id_lang'];
            $iso = strtolower($lang['iso_code']);

            switch ($iso) {
                case 'fr':
                    $cms->meta_title[$id_lang] = 'Politique relative aux cookies';
                    $cms->link_rewrite[$id_lang] = 'politique-cookies';
                    $cms->meta_description[$id_lang] = 'Politique sur les cookies de notre boutique.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, politique, confidentialité';
                    $cms->content[$id_lang] = '
                        <h1>Politique relative aux cookies</h1>
                        <p>Nous utilisons des cookies pour améliorer votre expérience, personnaliser le contenu et analyser notre trafic. Vous trouverez ci-dessous la liste des cookies utilisés.</p>
                        {cookiesplus_cookies_table}
                        <p>Vous pouvez modifier ou désactiver les cookies dans les paramètres de votre navigateur. Cela pourrait affecter certaines fonctionnalités de notre site.</p>
                        <p>Vous pouvez modifier vos préférences en matière de cookies en cliquant sur <a href="#cookiesplus-displaymodaladvanced">ce lien</a>.</p>
                    ';
                    break;

                case 'it':
                    $cms->meta_title[$id_lang] = 'Politica sui cookie';
                    $cms->link_rewrite[$id_lang] = 'politica-cookie';
                    $cms->meta_description[$id_lang] = 'Politica sui cookie del nostro negozio.';
                    // $cms->meta_keywords[$id_lang] = 'cookie, politica, privacy';
                    $cms->content[$id_lang] = '
                        <h1>Politica sui cookie</h1>
                        <p>Utilizziamo i cookie per migliorare la tua esperienza, personalizzare i contenuti e analizzare il nostro traffico. Di seguito trovi l’elenco dei cookie utilizzati.</p>
                        {cookiesplus_cookies_table}
                        <p>Puoi modificare o disattivare i cookie tramite le impostazioni del browser. Questo potrebbe influire sulla funzionalità del sito.</p>
                        <p>Puoi modificare le tue preferenze sui cookie cliccando su <a href="#cookiesplus-displaymodaladvanced">questo link</a>.</p>
                    ';
                    break;

                case 'es':
                    $cms->meta_title[$id_lang] = 'Política de cookies';
                    $cms->link_rewrite[$id_lang] = 'politica-cookies';
                    $cms->meta_description[$id_lang] = 'Política de cookies de nuestra tienda.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, política, privacidad';
                    $cms->content[$id_lang] = '
                        <h1>Política de cookies</h1>
                        <p>Utilizamos cookies para mejorar tu experiencia de navegación, personalizar el contenido y analizar nuestro tráfico. A continuación se muestra un listado detallado de las cookies utilizadas en nuestra tienda.</p>
                        {cookiesplus_cookies_table}
                        <p>Puedes modificar o desactivar las cookies desde la configuración de tu navegador. Ten en cuenta que algunas funcionalidades del sitio podrían verse afectadas.</p>
                        <p>Puedes modificar tus preferencias de cookies haciendo clic en <a href="#cookiesplus-displaymodaladvanced">este enlace</a>.</p>
                    ';
                    break;

                case 'de':
                    $cms->meta_title[$id_lang] = 'Cookie-Richtlinie';
                    $cms->link_rewrite[$id_lang] = 'cookie-richtlinie';
                    $cms->meta_description[$id_lang] = 'Cookie-Richtlinie unseres Shops.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, richtlinie, datenschutz';
                    $cms->content[$id_lang] = '
                        <h1>Cookie-Richtlinie</h1>
                        <p>Wir verwenden Cookies, um Ihre Erfahrung zu verbessern, Inhalte zu personalisieren und unseren Datenverkehr zu analysieren. Nachfolgend finden Sie eine Liste der verwendeten Cookies.</p>
                        {cookiesplus_cookies_table}
                        <p>Sie können Cookies in den Einstellungen Ihres Browsers deaktivieren oder ändern. Beachten Sie, dass dies die Funktionalität der Website beeinträchtigen kann.</p>
                        <p>Sie können Ihre Cookie-Einstellungen ändern, indem Sie auf <a href="#cookiesplus-displaymodaladvanced">diesen Link</a> klicken.</p>
                    ';
                    break;

                case 'nl':
                    $cms->meta_title[$id_lang] = 'Cookiebeleid';
                    $cms->link_rewrite[$id_lang] = 'cookiebeleid';
                    $cms->meta_description[$id_lang] = 'Cookiebeleid van onze winkel.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, beleid, privacy';
                    $cms->content[$id_lang] = '
                        <h1>Cookiebeleid</h1>
                        <p>We gebruiken cookies om uw winkelervaring te verbeteren, inhoud te personaliseren en ons verkeer te analyseren. Hieronder vindt u een overzicht van de gebruikte cookies.</p>
                        {cookiesplus_cookies_table}
                        <p>U kunt cookies beheren of uitschakelen via de instellingen van uw browser. Houd er rekening mee dat dit de werking van onze site kan beïnvloeden.</p>
                        <p>U kunt uw cookievoorkeuren aanpassen door op <a href="#cookiesplus-displaymodaladvanced">deze link</a> te klikken.</p>
                    ';
                    break;

                case 'pl':
                    $cms->meta_title[$id_lang] = 'Polityka cookies';
                    $cms->link_rewrite[$id_lang] = 'polityka-cookies';
                    $cms->meta_description[$id_lang] = 'Polityka cookies naszego sklepu.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, polityka, prywatność';
                    $cms->content[$id_lang] = '
                        <h1>Polityka cookies</h1>
                        <p>Używamy plików cookies, aby poprawić komfort przeglądania, personalizować treści i analizować ruch na stronie. Poniżej znajduje się szczegółowa lista wykorzystywanych cookies.</p>
                        {cookiesplus_cookies_table}
                        <p>Możesz zmienić lub wyłączyć cookies w ustawieniach przeglądarki. Może to wpłynąć na działanie sklepu.</p>
                        <p>Możesz zmienić swoje preferencje dotyczące cookies klikając w <a href="#cookiesplus-displaymodaladvanced">ten link</a>.</p>
                    ';
                    break;

                case 'pt':
                    $cms->meta_title[$id_lang] = 'Política de cookies';
                    $cms->link_rewrite[$id_lang] = 'politica-cookies';
                    $cms->meta_description[$id_lang] = 'Política de cookies da nossa loja.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, política, privacidade';
                    $cms->content[$id_lang] = '
                        <h1>Política de cookies</h1>
                        <p>Utilizamos cookies para melhorar a sua experiência de navegação, personalizar o conteúdo e analisar o tráfego. Abaixo está a lista detalhada dos cookies utilizados na nossa loja.</p>
                        {cookiesplus_cookies_table}
                        <p>Pode modificar ou desativar os cookies nas definições do seu navegador. Isto poderá afetar algumas funcionalidades do site.</p>
                        <p>Pode alterar as suas preferências de cookies clicando neste <a href="#cookiesplus-displaymodaladvanced">link</a>.</p>
                    ';
                    break;

                case 'ru':
                    $cms->meta_title[$id_lang] = 'Политика использования файлов cookie';
                    $cms->link_rewrite[$id_lang] = 'politika-cookie';
                    $cms->meta_description[$id_lang] = 'Политика cookie нашего магазина.';
                    // $cms->meta_keywords[$id_lang] = 'cookie, политика, конфиденциальность';
                    $cms->content[$id_lang] = '
                        <h1>Политика использования файлов cookie</h1>
                        <p>Мы используем файлы cookie для улучшения вашего взаимодействия с сайтом, персонализации контента и анализа трафика. Ниже представлен список используемых файлов cookie.</p>
                        {cookiesplus_cookies_table}
                        <p>Вы можете изменить или отключить файлы cookie в настройках браузера. Это может повлиять на функциональность сайта.</p>
                        <p>Вы можете изменить настройки файлов cookie, нажав на <a href="#cookiesplus-displaymodaladvanced">эту ссылку</a>.</p>
                    ';
                    break;

                default: // English fallback
                    $cms->meta_title[$id_lang] = 'Cookies policy';
                    $cms->link_rewrite[$id_lang] = 'cookies-policy';
                    $cms->meta_description[$id_lang] = 'Cookies policy of our store.';
                    // $cms->meta_keywords[$id_lang] = 'cookies, policy, privacy';
                    $cms->content[$id_lang] = '
                        <h1>Cookies Policy</h1>
                        <p>We use cookies to enhance your shopping experience, personalize content, and analyze our traffic. Below you will find a detailed list of the cookies used on our site.</p>
                        {cookiesplus_cookies_table}
                        <p>You can modify or disable cookies by adjusting your browser settings. Please note that disabling certain cookies may affect the functionality of our store and your overall experience.</p>
                        <p>You can modify your cookies preferences by clicking <a href="#cookiesplus-displaymodaladvanced">this link</a>.</p>
                    ';
                    break;
            }
        }

        $cms->id_cms_category = 1;

        if ($cms->add()) {
            Configuration::updateValue('C_P_COOKIES_POLICIES', $cms->id);
            return $cms->id;
        }

        return false;
    }

    public function getContent()
    {
        Tools::redirectAdmin('index.php?controller=AdminCookiesPlusConfiguration&token=' . Tools::getAdminTokenLite('AdminCookiesPlusConfiguration'));
    }

    public function getWarnings($getAll = true)
    {
        $warnings = [];

        if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
            $warnings[] = sprintf($this->l('%1$s "%2$s" at %3$s - %4$s'), $this->l('Disable'), $this->l('Disable non PrestaShop modules'), $this->l('Advanced Parameters'), $this->l('Performance'));
        }

        if (Configuration::get('PS_DISABLE_OVERRIDES')) {
            $warnings[] = sprintf($this->l('%1$s "%2$s" at %3$s - %4$s'), $this->l('Disable'), $this->l('Disable all overrides'), $this->l('Advanced Parameters'), $this->l('Performance'));
        }

        $cookiesPlusFinalitiesList = CookiesPlusFinality::getCookiesPlusFinalities();
        $atLeastOneFinalityNonTechnical = false;
        $atLeastOneFinalityTechnical = false;
        foreach ($cookiesPlusFinalitiesList as $cookiesPlusFinality) {
            if ($cookiesPlusFinality['active'] && $cookiesPlusFinality['technical']) {
                $atLeastOneFinalityTechnical = true;
            }

            if ($cookiesPlusFinality['active'] && !$cookiesPlusFinality['technical']) {
                $atLeastOneFinalityNonTechnical = true;
            }
        }

        // If there's any technical cookie finality enabled
        if (!$atLeastOneFinalityTechnical) {
            $warnings[] = $this->l('Please check "Cookie finalities". You need to enable at least one technical cookie finality.');
        }

        // If there's only technical cookies, there's no need to display the warnings
        if (!$atLeastOneFinalityNonTechnical) {
            $warnings[] = $this->l('Please check "Cookie finalities". You need to enable at least one non-technical cookie finality. If there\'s only technical cookies finalities enabled, the cookie notice will not be displayed');
        }

        /*if (Module::isInstalled('litespeedcache')) {
            $warnings[] = $this->l('It seems that you are using litespeedcache cache. An additional configuration in this module may be required.');
        }

        if (Module::isInstalled('stadvancedcache')) {
            $warnings[] = $this->l('It seems that you are using stadvancedcache cache. An additional configuration in this module may be required.');
        }

        if (Module::isInstalled('jprestaspeedpack')) {
            $warnings[] = $this->l('It seems that you are using jprestaspeedpack cache. An additional configuration in this module may be required.');
        }*/
        if (self::cacheModuleDetected()) {
            $warnings[] = $this->l('If you are using a cache module please ensure that the cookies module is working correctly.');
        }

        if ($module = Module::getInstanceByName('cdc_googletagmanager')) {
            if (version_compare($module->version, '5.2.0', '<=')) {
                $warnings[] = $this->l('Upgrade the module cdc_googletagmanager');
            }
        }

        if ($module = Module::getInstanceByName('ganalyticspro')) {
            if (version_compare($module->version, '2.1.5', '<')) {
                $warnings[] = $this->l('Upgrade the module ganalyticspro');
            }
        }

        if ($module = Module::getInstanceByName('gadwordstracking')) {
            if (version_compare($module->version, '2.3.6', '<')) {
                $warnings[] = $this->l('Upgrade the module gadwordstracking');
            }
        }

        if (count($warnings) && version_compare(_PS_VERSION_, '1.6.1', '<')) {
            return $warnings[0];
        }

        if (!$getAll && count($warnings)) {
            return $warnings[0];
        }

        return $warnings;
    }

    public function getModuleList()
    {
        $query = 'SELECT m.`id_module`, m.`name`, m.`active`
                FROM `' . _DB_PREFIX_ . 'module` m';

        $module_list = Db::getInstance()->executeS($query);

        foreach ($module_list as $key => &$module) {
            $module['displayName'] = Module::getModuleName($module['name']);

            if ((int) $module['id_module'] === 0) {
                unset($module_list[$key]);
            }

            if ($module['name'] === $this->name) {
                unset($module_list[$key]);
            }
        }
        unset($module);

        usort($module_list, static function ($a, $b) {
            return strnatcasecmp($a['displayName'], $b['displayName']);
        });

        return $module_list;
    }

    protected static function executeModule($params = null)
    {
        if (!Configuration::get('C_P_ENABLE')) {
            return false;
        }

        $moduleCaller = '';
        if ($params !== null) {
            $moduleCaller = $params['module']->name;
        }

        $moduleCallerExceptions = [
            'klaviyopsautomation',
        ];

        // if (defined('_PS_ADMIN_DIR_')) {
        if (is_object(Context::getContext()->controller)
            && isset(Context::getContext()->controller->controller_type)
            && (Context::getContext()->controller->controller_type === 'admin'
                || Context::getContext()->controller->controller_type === 'moduleadmin')
            && !in_array($moduleCaller, $moduleCallerExceptions)) {
            return false;
        }

        // Validate allowed IPs
        if (!self::onlyIPDebug()) {
            return false;
        }

        // Validate user agent
        if (self::byPassUserAgent()) {
            return false;
        }

        // Validate disallow IPs
        if (self::bypassIP()) {
            return false;
        }

        return true;
    }

    protected static function getGeo()
    {
        // Don't display outside EU
        if (Configuration::get('PS_GEOLOCATION_ENABLED')
            && !Configuration::get('C_P_GEO')
            && !in_array(Tools::getRemoteAddr(), ['localhost', '127.0.0.1', '::1'])) {
            // Check if Maxmind Database exists
            if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    include_once _PS_GEOIP_DIR_ . 'geoipcity.inc';

                    $gi = geoip_open(realpath(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_), GEOIP_STANDARD);
                    $record = geoip_record_by_addr($gi, Tools::getRemoteAddr());

                    if (is_object($record)
                        && $record->continent_code
                        && $record->continent_code !== 'EU') {
                        return false;
                    }
                } else {
                    $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
                    try {
                        $record = $reader->city(Tools::getRemoteAddr());
                    } catch (GeoIp2\Exception\AddressNotFoundException $e) {
                        $record = null;
                    }

                    if (is_object($record)
                        && $record->continent->code
                        && $record->continent->code !== 'EU') {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected static function byPassUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])
            && Configuration::get('C_P_BOTS')
            && preg_match('/' . Configuration::get('C_P_BOTS') . '/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }

        return false;
    }

    protected static function bypassIP()
    {
        if (Configuration::get('C_P_IPS')
            && in_array(Tools::getRemoteAddr(), explode('|', Configuration::get('C_P_IPS')))) {
            return true;
        }

        return false;
    }

    protected static function onlyIPDebug()
    {
        if (!Configuration::get('C_P_IPS_DEBUG')) {
            return true;
        }

        if (in_array(Tools::getRemoteAddr(), explode('|', Configuration::get('C_P_IPS_DEBUG')))) {
            return true;
        }

        return false;
    }

    public function getCookiesPlusCookiesList()
    {
        $idCookiesPlusFinality = (int) Tools::getValue('id_cookiesplus_finality');

        if (!Tools::getIsset('addcookiesplus_finality') && !$idCookiesPlusFinality) {
            return $this->displayError('Error loading cookies');
        }

        $cookiesPlusCookiesList = CookiesPlusCookie::getCookiesPlusCookies($idCookiesPlusFinality, null, false, $this->context->shop->id);

        $fields_list = [
            'active' => [
                'title' => $this->l('Enabled'),
                'active' => 'active',
                'type' => 'bool',
            ],
            'name' => [
                'title' => $this->l('Cookie name'),
                'filter_key' => 'a!name',
            ],
            'provider' => [
                'title' => $this->l('Provider'),
            ],
            'purpose' => [
                'title' => $this->l('Purpose'),
                'callback' => 'getCookiePurposeCallback',
                'callback_object' => 'CookiesPlusCookie',
            ],
            'expiry' => [
                'title' => $this->l('Expiry'),
            ],
        ];

        $helperList = new HelperList();

        $helperList->shopLinkType = '';
        $helperList->simple_header = false;
        $helperList->show_toolbar = true;
        $helperList->module = $this;
        $helperList->actions = ['edit', 'deletecookie'];
        $helperList->identifier = 'id_cookiesplus_cookie';
        $helperList->table = 'cookiesplus_cookie';
        $helperList->token = Tools::getAdminTokenLite('AdminCookiesPlusCookies');
        $helperList->currentIndex = $this->context->link->getAdminLink('AdminCookiesPlusCookies', false) . '&back=1&id_cookiesplus_finality=' . (int) Tools::getValue('id_cookiesplus_finality');

        $helperList->title = $this->l('Cookies detail');

        if (!Tools::getIsset('addcookiesplus_finality')) {
            $helperList->toolbar_btn['new'] = [
                'href' => $helperList->currentIndex . '&add' . $helperList->table . '&token=' . $helperList->token . '&id_cookiesplus_finality=' . Tools::getValue('id_cookiesplus_finality'),
                'desc' => $this->l('Add new'),
            ];
        }

        $helperList->listTotal = count($cookiesPlusCookiesList);

        return $helperList->generateList($cookiesPlusCookiesList, $fields_list);
    }

    public function displayDeleteCookieLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->context->smarty->createTemplate('helpers/list/list_action_delete.tpl');

        $tpl->assign([
            'href' => $this->context->link->getAdminLink('AdminCookiesPlusCookies', false) . '&id_cookiesplus_cookie=' . $id . '&deletecookiesplus_cookie&token=' . $token,
            'confirm' => $this->l('Delete the selected item?') . $name,
            'action' => $this->l('Delete'),
            'consent_hash' => $id,
        ]);

        return $tpl->fetch();
    }

    /* Hooks */
    /* Don't place in this header anything that can NOT be cachable */
    public function hookDisplayHeader()
    {
        if (!self::executeModule()) {
            return;
        }

        $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
        if (isset($cookiesPlusCookiePreferences['consent_date'])) {
            $consentDate = strtotime($cookiesPlusCookiePreferences['consent_date']);
            $revokeConsentDate = strtotime(Configuration::get('C_P_REVOKE_CONSENT'));

            if ($revokeConsentDate > $consentDate) {
                $this->initializeCookiesPlusPreferences(true);
            }
        }

        // Check if consent file exists
        if (Configuration::get('C_P_SAVE_CONSENT')) {
            if (isset($cookiesPlusCookiePreferences['consent_hash']) && $cookiesPlusCookiePreferences['consent_hash']) {
                if (!CookiesPlusUserConsent::getCookiesPlusUserConsentDataByHash($cookiesPlusCookiePreferences['consent_hash'])) {
                    $this->initializeCookiesPlusPreferences(true);
                }
            }
        }

        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/cookiesplus.css');
        if (Configuration::get('C_P_MATERIAL_ICONS')) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/cookiesplus-material-icons.css');
            // $html .= '<link rel="preload" href="'._MODULE_DIR_ . $this->name . '/views/css/cookiesplus-material-icons.css'.'" as="style" crossorigin="anonymous" />';
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/cookiesplus-front.js');
        } else {
            $this->context->controller->registerJavascript(
                'cookiesplus-front',
                'modules/' . $this->name . '/views/js/cookiesplus-front.js'
                /*[
                    'attributes' => 'async',
                ]*/
            );
        }

        // Just assign to smarty, in case user add an IF condition in template for a custom script
        $this->context->smarty->assign([
            //'C_P_COOKIE_VALUE' => $cookiesPlusCookiePreferences ?: '{}', // empty JSON
            'C_P_CSS' => Configuration::get('C_P_CSS'),
            'C_P_BACKGROUND_COLOR' => Configuration::get('C_P_BACKGROUND_COLOR'),
            'C_P_FONT_COLOR' => Configuration::get('C_P_FONT_COLOR'),
            'C_P_ACCEPT_DISPLAY' => Configuration::get('C_P_ACCEPT_DISPLAY'),
            'C_P_ACCEPT_BACKGROUND_COLOR' => Configuration::get('C_P_ACCEPT_BACKGROUND_COLOR'),
            'C_P_ACCEPT_BORDER_COLOR' => Configuration::get('C_P_ACCEPT_BORDER_COLOR'),
            'C_P_ACCEPT_FONT_COLOR' => Configuration::get('C_P_ACCEPT_FONT_COLOR'),
            'C_P_ACCEPT_FONT_SIZE' => Configuration::get('C_P_ACCEPT_FONT_SIZE'),
            'C_P_ACCEPT_PADDING' => Configuration::get('C_P_ACCEPT_PADDING'),
            'C_P_MORE_INFO_DISPLAY' => Configuration::get('C_P_MORE_INFO_DISPLAY'),
            'C_P_MORE_INFO_BACKGROUND_COLOR' => Configuration::get('C_P_MORE_INFO_BACKGROUND_COLOR'),
            'C_P_MORE_INFO_BORDER_COLOR' => Configuration::get('C_P_MORE_INFO_BORDER_COLOR'),
            'C_P_MORE_INFO_FONT_COLOR' => Configuration::get('C_P_MORE_INFO_FONT_COLOR'),
            'C_P_MORE_INFO_FONT_SIZE' => Configuration::get('C_P_MORE_INFO_FONT_SIZE'),
            'C_P_MORE_INFO_PADDING' => Configuration::get('C_P_MORE_INFO_PADDING'),
            'C_P_REJECT_DISPLAY' => Configuration::get('C_P_REJECT_DISPLAY'),
            'C_P_REJECT_BACKGROUND_COLOR' => Configuration::get('C_P_REJECT_BACKGROUND_COLOR'),
            'C_P_REJECT_BORDER_COLOR' => Configuration::get('C_P_REJECT_BORDER_COLOR'),
            'C_P_REJECT_FONT_COLOR' => Configuration::get('C_P_REJECT_FONT_COLOR'),
            'C_P_REJECT_FONT_SIZE' => Configuration::get('C_P_REJECT_FONT_SIZE'),
            'C_P_REJECT_PADDING' => Configuration::get('C_P_REJECT_PADDING'),
            'C_P_SAVE_BACKGROUND_COLOR' => Configuration::get('C_P_SAVE_BACKGROUND_COLOR'),
            'C_P_SAVE_BORDER_COLOR' => Configuration::get('C_P_SAVE_BORDER_COLOR'),
            'C_P_SAVE_FONT_COLOR' => Configuration::get('C_P_SAVE_FONT_COLOR'),
            'C_P_SAVE_FONT_SIZE' => Configuration::get('C_P_SAVE_FONT_SIZE'),
            'C_P_SAVE_PADDING' => Configuration::get('C_P_SAVE_PADDING'),
            'C_P_MATERIAL_ICONS_LIBRARY' => Configuration::get('C_P_MATERIAL_ICONS_LIBRARY'),
            'C_P_ICONS' => Configuration::get('C_P_ICONS'),
            'C_P_TAB_ENABLED' => Configuration::get('C_P_TAB_ENABLED'),
            'C_P_TAB_POSITION' => Configuration::get('C_P_TAB_POSITION'),
            'C_P_TAB_BACKGROUND_COLOR' => Configuration::get('C_P_TAB_BACKGROUND_COLOR'),
            'C_P_TAB_FONT_COLOR' => Configuration::get('C_P_TAB_FONT_COLOR'),
        ]);

        $htmlCookiesStyle = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/cookies-style.tpl');

        if (method_exists('Media', 'minifyCSS')) {
            $htmlCookiesStyle = Media::minifyCSS($htmlCookiesStyle);
        }

        $html = $htmlCookiesStyle;

        if (!self::getGeo()) {
            return $html;
        }

        if (!Configuration::get('C_P_GTM_ENABLE')
            && Configuration::get('C_P_GTM_FIRE_CONSENT')) {
            if (Configuration::get('C_P_GTM_FIRE_CONSENT') === 'false') {
                $html .= Configuration::get('C_P_GTM_HEAD');
            } else {
                $random = Tools::substr(md5(microtime()), 0, 10);
                $divName = 'hookDisplayHeader' . $this->id . '_' . $random;

                $this->context->smarty->assign([
                    'divName' => $divName,
                    'id_module' => $this->id,
                    'finalities' => implode(',', array_keys(json_decode(Configuration::get('C_P_GTM_FIRE_CONSENT') !== 'false' ? Configuration::get('C_P_GTM_FIRE_CONSENT') : '{}', true)) ?: []),
                    'script' => json_encode(Configuration::get('C_P_GTM_HEAD')),
                    'js' => '[]',
                    'css' => '[]',
                ]);

                $html .= $this->context->smarty->fetch($this->local_path . 'views/templates/hook/hookmoduledata.tpl');
            }
        }

        return $html;
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        $html = '';

        if (Configuration::get('C_P_GTM_ENABLE')) {
            $html .= Configuration::get('C_P_GTM_BODY');
        } elseif (Configuration::get('C_P_GTM_FIRE_CONSENT')) {
            if (Configuration::get('C_P_GTM_FIRE_CONSENT') === 'false') {
                $html .= Configuration::get('C_P_GTM_BODY');
            } else {
                $random = Tools::substr(md5(microtime()), 0, 10);
                $divName = 'hookDisplayAfterBodyOpeningTag_' . $this->id . '_' . $random;

                $this->context->smarty->assign([
                    'divName' => $divName,
                    'id_module' => $this->id,
                    'finalities' => implode(',', array_keys(json_decode(Configuration::get('C_P_GTM_FIRE_CONSENT'), true)) ?: []),
                    'script' => json_encode(Configuration::get('C_P_GTM_BODY')),
                    'js' => '[]',
                    'css' => '[]',
                ]);

                $html .= $this->context->smarty->fetch($this->local_path . 'views/templates/hook/hookmoduledata.tpl');
            }
        }

        return $html;
    }

    public function hookDisplayCookiesHeader()
    {
        $this->hookDisplayHeader();
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            return;
        }

        $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();

        // Don't display modal with Creative Elements editor
        /*if (Tools::isSubmit('cp_type')) {
            $displayModal = false;
        }*/

        $cookiesPlusConfiguration = [];
        if (Configuration::get('C_P_GTM_ENABLE')) {
            $gtmConsents = json_decode(Configuration::get('C_P_GTM_CONSENT'), true);
        }
        if (Configuration::get('C_P_MUET_ENABLE')) {
            $muetConsents = json_decode(Configuration::get('C_P_MUET_CONSENT'), true);
        }
        if (Configuration::get('C_P_FB_ENABLE')) {
            $fbConsents = json_decode(Configuration::get('C_P_FB_CONSENT'), true);
        }
        if (Configuration::get('C_P_YT_ENABLE')) {
            $ytConsents = json_decode(Configuration::get('C_P_YT_CONSENT'), true);
        }

        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
        // $atLeastOneFinalityNonTechnical = false;
        foreach ($cookiesPlusFinalities as &$cookiesPlusFinality) {
            $cookiesPlusFinality['cookies'] = CookiesPlusCookie::getCookiesPlusCookies($cookiesPlusFinality['id_cookiesplus_finality'], (int) $this->context->language->id, true, $this->context->shop->id, true);
            /*if ($cookiesPlusFinality['active'] && !$cookiesPlusFinality['technical']) {
                $atLeastOneFinalityNonTechnical = true;
            }*/
            if ($cookiesPlusFinality['js_script']) {
                $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['script'] = $cookiesPlusFinality['js_script'];
            }

            if ($cookiesPlusFinality['body_code']) {
                $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['body_code'] = $cookiesPlusFinality['body_code'];
            }

            if ($cookiesPlusFinality['js_not_script']) {
                $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['script_not'] = $cookiesPlusFinality['js_not_script'];
            }

            if ($cookiesPlusFinality['cookies']) {
                $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['cookies'] = $cookiesPlusFinality['cookies'];
            }

            if (Configuration::get('C_P_GTM_ENABLE')) {
                if ($cookiesPlusFinality['technical']) {
                    continue;
                }

                if (isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                    if (isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'])
                        && $gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality']) {
                        $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['gtm_consent_type'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'];
                        $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['firingEvent'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['firingEvent'];
                    }
                }
            }

            if (Configuration::get('C_P_MUET_ENABLE')) {
                if ($cookiesPlusFinality['technical']) {
                    continue;
                }

                if (isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                    if (isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'])
                        && $muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality']) {
                        $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['muet_consent_type'] = $muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'];
                    }
                }
            }

            if (Configuration::get('C_P_FB_ENABLE')) {
                if ($cookiesPlusFinality['technical']) {
                    continue;
                }

                if (isset($fbConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                    $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['fb'] = true;
                }
            }

            if (Configuration::get('C_P_YT_ENABLE')) {
                if ($cookiesPlusFinality['technical']) {
                    continue;
                }

                if (isset($ytConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                    $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['yt'] = true;
                }
            }
        }
        unset($cookiesPlusFinality);

        // If there's only technical cookies, there's no need to display the warning
        /*if (!$atLeastOneFinalityNonTechnical) {
            $displayModal = false;
        }*/

        // $cookiesPlusConfiguration = json_encode($cookiesPlusConfiguration);
        // $cookiesPlusConfiguration = self::sanitizeJson($cookiesPlusConfiguration);

        Media::addJsDef([
            'C_P_DOMAIN' => self::getDomain(),
            'C_P_DATE' => date('Y-m-d H:i:s', time()),
            'C_P_REFRESH' => (int) Configuration::get('C_P_REFRESH'),
            'C_P_EXPIRY' => (int) Configuration::get('C_P_EXPIRY') ?: 365,
            'C_P_OVERLAY' => Configuration::get('C_P_OVERLAY'),
            'C_P_OVERLAY_OPACITY' => Configuration::get('C_P_OVERLAY_OPACITY'),
            'C_P_NOT_AVAILABLE_OUTSIDE_EU' => (int) self::getGeo(), // Don't display modal outside EU
            'C_P_FINALITIES_COUNT' => count($cookiesPlusFinalities),
            'C_P_CONSENT_DOWNLOAD' => $this->context->link->getModuleLink('cookiesplus', 'front', [], true),
            'C_P_DISPLAY_AGAIN' => (int) Configuration::get('C_P_DISPLAY_AGAIN'),
            'C_P_CMS_PAGE' => (int) Configuration::get('C_P_CMS_PAGE'),
            'C_P_COOKIES_POLICIES' => (int) Configuration::get('C_P_COOKIES_POLICIES'),
            'C_P_COOKIE_VALUE' => $cookiesPlusCookiePreferences ?: '{}', // empty JSON
            'C_P_COOKIE_CONFIG' => $cookiesPlusConfiguration,
            'PS_COOKIE_SAMESITE' => Configuration::get('PS_COOKIE_SAMESITE') ?: 'Lax',
            'PS_COOKIE_SECURE' => (int) (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')),
        ]);
    }

    public function hookDisplayFooter()
    {
        if (!self::executeModule()) {
            return;
        }

        $html = null;

        if (Tools::getIsset('debugCookies')) {
            $html .= $this->context->smarty->fetch($this->local_path . 'views/templates/hook/debug-cookies.tpl');
        }

        $cpClass = '';
        if (Configuration::get('C_P_WIDTH') == '100') {
            $cpClass = 'col-12 col-xs-12';
        } elseif (Configuration::get('C_P_WIDTH') == '75') {
            $cpClass = 'col-12 col-xs-12 col-md-9';
        } elseif (Configuration::get('C_P_WIDTH') == '50') {
            $cpClass = 'col-12 col-xs-12 col-md-9 col-lg-6';
        } elseif ((int) Configuration::get('C_P_WIDTH') === 25) {
            $cpClass = 'col-12 col-xs-12 col-md-6 col-lg-4 col-xl-3';
        }

        if ($this->context->language->id) {
            $idLang = $this->context->language->id;
        } elseif ($this->context->cookie->id_lang) {
            $idLang = $this->context->cookie->id_lang;
        } else {
            $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();

        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
        foreach ($cookiesPlusFinalities as &$cookiesPlusFinality) {
            $cookiesPlusFinality['cookies'] = CookiesPlusCookie::getCookiesPlusCookies($cookiesPlusFinality['id_cookiesplus_finality'], (int) $this->context->language->id, true, $this->context->shop->id, false);

            if ($cookiesPlusFinality['cookies']) {
                $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['cookies'] = $cookiesPlusFinality['cookies'];
            }
        }
        unset($cookiesPlusFinality);

        $this->context->smarty->assign([
            'link' => $this->context->link,
            //'C_P_COOKIE_VALUE' => $cookiesPlusCookiePreferences ?: '{}', // empty JSON
            'C_P_POSITION' => Configuration::get('C_P_POSITION'),
            'C_P_WIDTH' => Configuration::get('C_P_WIDTH'),
            'C_P_CLASS' => $cpClass,
            'C_P_BACKGROUND_COLOR' => Configuration::get('C_P_BACKGROUND_COLOR'),
            'C_P_FONT_COLOR' => Configuration::get('C_P_FONT_COLOR'),
            'C_P_DISPLAY_TITLE' => Configuration::get('C_P_DISPLAY_TITLE'),
            // 'C_P_TITLE' => Configuration::get('C_P_TITLE', $idLang),
            'C_P_JS' => Configuration::get('C_P_JS'),
            'C_P_TEXT_BASIC' => Configuration::get('C_P_TEXT_BASIC', $idLang),
            'C_P_DISPLAY_ENCOURAGEMENT' => (int) Configuration::get('C_P_DISPLAY_ENCOURAGEMENT'),
            'C_P_TEXT_ENCOURAGEMENT' => Configuration::get('C_P_TEXT_ENCOURAGEMENT', $idLang),
            'C_P_TEXT_REQUIRED' => Configuration::get('C_P_TEXT_REQUIRED', $idLang),
            'C_P_TEXT_3RDPARTY' => Configuration::get('C_P_TEXT_3RDPARTY', $idLang),
            'C_P_CMS_PAGE' => Configuration::get('C_P_CMS_PAGE'),
            'C_P_COOKIES_POLICIES' => (int) Configuration::get('C_P_COOKIES_POLICIES'),
            'C_P_BUTTONS_LAYOUT' => (int) Configuration::get('C_P_BUTTONS_LAYOUT'),
            'C_P_ACCEPT_DISPLAY' => Configuration::get('C_P_ACCEPT_DISPLAY'),
            'C_P_ACCEPT_BACKGROUND_COLOR' => Configuration::get('C_P_ACCEPT_BACKGROUND_COLOR'),
            'C_P_ACCEPT_BORDER_COLOR' => Configuration::get('C_P_ACCEPT_BORDER_COLOR'),
            'C_P_ACCEPT_FONT_COLOR' => Configuration::get('C_P_ACCEPT_FONT_COLOR'),
            'C_P_ACCEPT_FONT_SIZE' => Configuration::get('C_P_ACCEPT_FONT_SIZE'),
            'C_P_ACCEPT_PADDING' => Configuration::get('C_P_ACCEPT_PADDING'),
            'C_P_MORE_INFO_DISPLAY' => Configuration::get('C_P_MORE_INFO_DISPLAY'),
            'C_P_MORE_INFO_BACKGROUND_COLOR' => Configuration::get('C_P_MORE_INFO_BACKGROUND_COLOR'),
            'C_P_MORE_INFO_BORDER_COLOR' => Configuration::get('C_P_MORE_INFO_BORDER_COLOR'),
            'C_P_MORE_INFO_FONT_COLOR' => Configuration::get('C_P_MORE_INFO_FONT_COLOR'),
            'C_P_MORE_INFO_FONT_SIZE' => Configuration::get('C_P_MORE_INFO_FONT_SIZE'),
            'C_P_MORE_INFO_PADDING' => Configuration::get('C_P_MORE_INFO_PADDING'),
            'C_P_REJECT_DISPLAY' => Configuration::get('C_P_REJECT_DISPLAY'),
            'C_P_REJECT_BACKGROUND_COLOR' => Configuration::get('C_P_REJECT_BACKGROUND_COLOR'),
            'C_P_REJECT_BORDER_COLOR' => Configuration::get('C_P_REJECT_BORDER_COLOR'),
            'C_P_REJECT_FONT_COLOR' => Configuration::get('C_P_REJECT_FONT_COLOR'),
            'C_P_REJECT_FONT_SIZE' => Configuration::get('C_P_REJECT_FONT_SIZE'),
            'C_P_REJECT_PADDING' => Configuration::get('C_P_REJECT_PADDING'),
            'C_P_SAVE_BACKGROUND_COLOR' => Configuration::get('C_P_SAVE_BACKGROUND_COLOR'),
            'C_P_SAVE_BORDER_COLOR' => Configuration::get('C_P_SAVE_BORDER_COLOR'),
            'C_P_SAVE_FONT_COLOR' => Configuration::get('C_P_SAVE_FONT_COLOR'),
            'C_P_SAVE_FONT_SIZE' => Configuration::get('C_P_SAVE_FONT_SIZE'),
            'C_P_SAVE_PADDING' => Configuration::get('C_P_SAVE_PADDING'),
            'C_P_MATERIAL_ICONS_LIBRARY' => Configuration::get('C_P_MATERIAL_ICONS_LIBRARY'),
            'C_P_FINALITIES' => $cookiesPlusFinalities,
            'C_P_ICONS' => Configuration::get('C_P_ICONS'),
            'C_P_TAB_ENABLED' => Configuration::get('C_P_TAB_ENABLED'),
            'C_P_TAB_POSITION' => Configuration::get('C_P_TAB_POSITION'),
            'C_P_TAB_BACKGROUND_COLOR' => Configuration::get('C_P_TAB_BACKGROUND_COLOR'),
            'C_P_TAB_FONT_COLOR' => Configuration::get('C_P_TAB_FONT_COLOR'),
            'C_P_SAVE_CONSENT' => (int) Configuration::get('C_P_SAVE_CONSENT'),
            'C_P_CONSENT_DATE' => isset($cookiesPlusCookiePreferences['consent_date']) ? $cookiesPlusCookiePreferences['consent_date'] : '',
            'C_P_REVOKE_CONSENT' => Tools::displayDate(date('Y-m-d', strtotime(Configuration::get('C_P_REVOKE_CONSENT'))), false),
            'C_P_DISPLAY_X' => Configuration::get('C_P_DISPLAY_X'),
            'C_P_DISPLAY_DATE' => Configuration::get('C_P_DISPLAY_DATE'),
            'C_P_DEFAULT_CONSENT' => Configuration::get('C_P_DEFAULT_CONSENT'),
            'C_P_TPL_DIR' => _PS_MODULE_DIR_ . $this->name,
        ]);

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $html .= $this->display(__FILE__, 'cookies-notice.tpl');
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $html .= $this->context->smarty->fetch($this->local_path . 'views/templates/hook/cookies-notice_15.tpl');

            $cookiesPlusCookiePreferences = json_encode($cookiesPlusCookiePreferences, JSON_THROW_ON_ERROR | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
            // $cookiesPlusCookiePreferences = self::sanitizeJson($cookiesPlusCookiePreferences);

            $cookiesPlusConfiguration = [];

            // $atLeastOneFinalityNonTechnical = false;
            foreach ($cookiesPlusFinalities as &$cookiesPlusFinality) {
                $cookiesPlusFinality['cookies'] = CookiesPlusCookie::getCookiesPlusCookies($cookiesPlusFinality['id_cookiesplus_finality'], (int) $this->context->language->id, true, $this->context->shop->id, false);
                /*if ($cookiesPlusFinality['active'] && !$cookiesPlusFinality['technical']) {
                    $atLeastOneFinalityNonTechnical = true;
                }*/
                if ($cookiesPlusFinality['js_script']) {
                    $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['script'] = $cookiesPlusFinality['js_script'];
                }

                if ($cookiesPlusFinality['body_code']) {
                    $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['body_code'] = $cookiesPlusFinality['body_code'];
                }

                if ($cookiesPlusFinality['js_not_script']) {
                    $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['script_not'] = $cookiesPlusFinality['js_not_script'];
                }

                if ($cookiesPlusFinality['cookies']) {
                    $cookiesPlusConfiguration[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['cookies'] = $cookiesPlusFinality['cookies'];
                }

                if (Configuration::get('C_P_GTM_ENABLE')) {
                    if ($cookiesPlusFinality['technical']) {
                        continue;
                    }

                    if (isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                        if (isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'])
                            && $gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality']) {
                            $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['gtm_consent_type'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'];
                            $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['firingEvent'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['firingEvent'];
                        }
                    }
                }

                if (Configuration::get('C_P_MUET_ENABLE')) {
                    if ($cookiesPlusFinality['technical']) {
                        continue;
                    }

                    if (isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                        if (isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'])
                            && $muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality']) {
                            $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['muet_consent_type'] = $muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'];
                        }
                    }
                }

                if (Configuration::get('C_P_FB_ENABLE')) {
                    if ($cookiesPlusFinality['technical']) {
                        continue;
                    }

                    if (isset($fbConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])) {
                        $cookiesPlusConfiguration[$cookiesPlusFinality['id_cookiesplus_finality']]['fb'] = true;
                    }
                }
            }
            unset($cookiesPlusFinality);

            $cookiesPlusConfiguration = json_encode($cookiesPlusConfiguration, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
            // $cookiesPlusConfiguration = self::sanitizeJson($cookiesPlusConfiguration);

            $this->context->smarty->assign([
                'C_P_DOMAIN' => self::getDomain(),
                'C_P_DATE' => date('Y-m-d H:i:s', time()),
                'C_P_REFRESH' => (int) Configuration::get('C_P_REFRESH'),
                'C_P_EXPIRY' => (int) Configuration::get('C_P_EXPIRY') ?: 365,
                'C_P_OVERLAY' => Configuration::get('C_P_OVERLAY'),
                'C_P_OVERLAY_OPACITY' => Configuration::get('C_P_OVERLAY_OPACITY'),
                'C_P_NOT_AVAILABLE_OUTSIDE_EU' => (int) self::getGeo(), // Don't display modal outside EU
                'C_P_FINALITIES_COUNT' => count($cookiesPlusFinalities),
                'C_P_CONSENT_DOWNLOAD' => $this->context->link->getModuleLink('cookiesplus', 'front', [], true),
                'C_P_DISPLAY_AGAIN' => (int) Configuration::get('C_P_DISPLAY_AGAIN'),
                'C_P_CMS_PAGE' => (int) Configuration::get('C_P_CMS_PAGE'),
                'C_P_COOKIES_POLICIES' => (int) Configuration::get('C_P_COOKIES_POLICIES'),
                'PS_COOKIE_SAMESITE' => Configuration::get('PS_COOKIE_SAMESITE') ?: 'Lax',
                'PS_COOKIE_SECURE' => (int) (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')),
                'C_P_COOKIE_VALUE' => $cookiesPlusCookiePreferences ?: '{}',
                'C_P_COOKIE_CONFIG' => $cookiesPlusConfiguration ?: '{}',
            ]);

            $html .= $this->context->smarty->fetch($this->local_path . 'views/templates/hook/cookies-notice-vars.tpl');
        }

        return $html;
    }

    public function hookDisplayMobileHeader()
    {
        return $this->hookDisplayHeader();
    }

    public function hookDisplayFooterLinks()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        return $this->hookDisplayFooter();
    }

    public function hookTmMegaLayoutFooter()
    {
        return $this->hookDisplayFooter();
    }

    public function hookBlockFooter1()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayFooterBefore()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayFooterAfter()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplaySidebar()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayFooterNavOne()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayFooterNavTwo()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayBanner()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayCookies()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayMyAccountBlock()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->hookDisplayMyAccountBlockFooter();
        }
    }

    public function hookDisplayMyAccountBlockFooter()
    {
        if (!self::executeModule()) {
            return;
        }

        if (!self::getGeo()) {
            return;
        }

        if (Configuration::get('C_P_ENABLE')) {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return $this->display(__FILE__, 'my-account-block-footer-15.tpl');
            }

            return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/my-account-block-footer-17.tpl');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (!self::executeModule()) {
            return;
        }

        if (!self::getGeo()) {
            return;
        }

        if (Configuration::get('C_P_ENABLE')) {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return $this->display(__FILE__, 'customer_account_15.tpl');
            }

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                return $this->display(__FILE__, 'customer_account_16.tpl');
            }

            $this->context->smarty->assign([
                'C_P_MATERIAL_ICONS_LIBRARY' => Configuration::get('C_P_MATERIAL_ICONS_LIBRARY'),
            ]);

            return $this->display(__FILE__, 'customer_account_17.tpl');
        }

        return false;
    }

    public function hookDisplayNav()
    {
        if (!self::executeModule()) {
            return;
        }

        if (!self::getGeo()) {
            return;
        }

        if (Configuration::get('C_P_ENABLE')) {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return $this->display(__FILE__, 'nav_16.tpl');
            }

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                return $this->display(__FILE__, 'nav_16.tpl');
            }

            return $this->display(__FILE__, 'nav_17.tpl');
        }

        return false;
    }

    public function hookDisplayNav2()
    {
        if (!self::executeModule()) {
            return;
        }

        if (!self::getGeo()) {
            return;
        }

        return $this->hookDisplayNav();
    }

    public function hookDisplayTop()
    {
        return $this->hookDisplayFooter();
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')
            && method_exists($this->context->controller, 'addCSS')) {
            $this->context->controller->addCSS($this->_path . 'views/css/menuTabIcon.css');
        }

        // Remove expired CookiesPlusUserConsent
        $expiredCookiesPlusUserConsents = CookiesPlusUserConsent::getCookiesPlusUserConsentExpired($this->context->shop->id);
        foreach ($expiredCookiesPlusUserConsents as $expiredCookiesPlusUserConsent) {
            $expiredCookiesPlusUserConsent = new CookiesPlusUserConsent((int) $expiredCookiesPlusUserConsent['id_cookiesplus_user_consent']);
            $expiredCookiesPlusUserConsent->delete();
        }
    }

    /**
     * empty listener for registerGDPRConsent hook
     */
    public function hookRegisterGDPRConsent()
    {
        /* registerGDPRConsent is a special kind of hook that doesn't need a listener, see :
           https://build.prestashop.com/howtos/module/how-to-make-your-module-compliant-with-prestashop-official-gdpr-compliance-module/
          However since Prestashop 1.7.8, modules must implement a listener for all the hooks they register: a check is made
          at module installation.
        */
    }

    public function hookActionShopDataDuplication($params)
    {
        $cookiesPlusCookies = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'cookiesplus_cookie
            WHERE id_shop = ' . (int) $params['old_id_shop']
        );

        foreach ($cookiesPlusCookies as $id => $cookiesPlusCookie) {
            Db::getInstance()->execute('
                INSERT IGNORE INTO ' . _DB_PREFIX_ . 'cookiesplus_cookie (id_cookiesplus_cookie, id_shop, active, id_cookiesplus_finality, name, provider, provider_url, date_add, date_upd)
                VALUES (null, ' . (int) $params['new_id_shop'] . ', ' . (int) $cookiesPlusCookie['active'] . ', ' . (int) $cookiesPlusCookie['id_cookiesplus_finality'] . ', \'' . pSQL($cookiesPlusCookie['name']) . '\', \'' . pSQL($cookiesPlusCookie['provider']) . '\', \'' . pSQL($cookiesPlusCookie['provider_url']) . '\', \'' . date('Y-m-d H:i:s') . '\', \'' . date('Y-m-d H:i:s') . '\')');

            $cookiesPlusCookies[$id]['new_id_cookiesplus_cookie'] = Db::getInstance()->Insert_ID();
        }

        foreach ($cookiesPlusCookies as $cookiesPlusCookie) {
            $languages = Db::getInstance()->executeS('
                    SELECT id_lang, purpose, expiry
                    FROM ' . _DB_PREFIX_ . 'cookiesplus_cookie_lang
                    WHERE id_cookiesplus_cookie = ' . (int) $cookiesPlusCookie['id_cookiesplus_cookie']);

            foreach ($languages as $language) {
                Db::getInstance()->execute('
                    INSERT IGNORE INTO ' . _DB_PREFIX_ . 'cookiesplus_cookie_lang (id_cookiesplus_cookie, id_lang, purpose, expiry)
                    VALUES (' . (int) $cookiesPlusCookie['new_id_cookiesplus_cookie'] . ', ' . (int) $language['id_lang'] . ', \'' . pSQL($language['purpose']) . '\', \'' . pSQL($language['expiry']) . '\')');
            }
        }

        $cookiesPlusFinalities = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'cookiesplus_finality
            WHERE id_shop = ' . (int) $params['old_id_shop']
        );

        foreach ($cookiesPlusFinalities as $id => $cookiesPlusFinality) {
            Db::getInstance()->execute('
                INSERT IGNORE INTO ' . _DB_PREFIX_ . 'cookiesplus_finality (id_cookiesplus_finality, id_shop, active, technical, modules, js_script, body_code, js_not_script, position, date_add, date_upd)
                VALUES (null, ' . (int) $params['new_id_shop'] . ', ' . (int) $cookiesPlusFinality['active'] . ', ' . (int) $cookiesPlusFinality['technical'] . ', \'' . pSQL($cookiesPlusFinality['modules']) . '\', \'' . pSQL($cookiesPlusFinality['js_script']) . '\', \'' . pSQL($cookiesPlusFinality['body_code']) . '\', \'' . pSQL($cookiesPlusFinality['js_not_script']) . '\', ' . (int) $cookiesPlusFinality['position'] . ', \'' . date('Y-m-d H:i:s') . '\', \'' . date('Y-m-d H:i:s') . '\')');

            $cookiesPlusFinalities[$id]['new_id_cookiesplus_finality'] = Db::getInstance()->Insert_ID();
        }

        foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
            $languages = Db::getInstance()->executeS('
                    SELECT id_lang, name, description
                    FROM ' . _DB_PREFIX_ . 'cookiesplus_finality_lang
                    WHERE id_cookiesplus_finality = ' . (int) $cookiesPlusFinality['id_cookiesplus_finality']);

            foreach ($languages as $language) {
                Db::getInstance()->execute('
                    INSERT IGNORE INTO ' . _DB_PREFIX_ . 'cookiesplus_finality_lang (id_cookiesplus_finality, id_lang, name, description)
                    VALUES (' . (int) $cookiesPlusFinality['new_id_cookiesplus_finality'] . ', ' . (int) $language['id_lang'] . ', \'' . pSQL($language['name']) . '\', \'' . pSQL($language['description']) . '\')');
            }
        }
    }

    public function hookActionDispatcher()
    {
        return $this->hookModuleRoutes();
    }

    public function hookModuleRoutes()
    {
        if (!self::executeModule()) {
            return;
        }

        self::initializeCookiesPlusPreferences();
    }

    public function hookActionOutputHTMLBefore($params)
    {
        if (!self::executeModule()) {
            return;
        }

        // Check if we have already added the script
        if (preg_match('/<meta\s+name=["\']cookiesplus-added["\']\s*\/?>/i', $params['html'])) {
            return;
        }

        // Find the position of "<head>"
        if (!preg_match('/<head[^>]*>/', $params['html'], $matches, PREG_OFFSET_CAPTURE)) {
            return;
        }

        // Get the position where "< head >" tag ends
        $headEndPosition = $matches[0][1] + strlen($matches[0][0]);

        // Search for the first < script > tag within the first 1024 bytes after the <head> tag
        $headContent = substr($params['html'], $headEndPosition, 1024);
        $firstScriptPosition = strpos($headContent, '<script');

        // Detect the start of the conditional comment
        $conditionalCommentStart = strpos($headContent, '<!--[if');

        // Initialize the insert position
        $insertPosition = false;

        if ($firstScriptPosition !== false) {
            // If a conditional comment exists and comes before the < script > tag
            if ($conditionalCommentStart !== false && $conditionalCommentStart < $firstScriptPosition) {
                // Insert before the conditional comment
                $insertPosition = $headEndPosition + $conditionalCommentStart;
            } else {
                // Insert before the < script > tag as usual
                $insertPosition = $headEndPosition + $firstScriptPosition;
            }
        } else {
            // No <script> tag found, fallback to another method (e.g., insert after the last <meta> tag)
            $lastMetaPosition = strrpos($headContent, '<meta');

            if ($lastMetaPosition !== false) {
                // If <meta> tag found, calculate the end position of the last <meta> tag
                $lastMetaEndPosition = $headEndPosition + $lastMetaPosition;
                $lastMetaTag = substr($headContent, $lastMetaPosition);
                preg_match('/<meta[^>]*\/>/', $lastMetaTag, $matches, PREG_OFFSET_CAPTURE);
                if (isset($matches[0])) {
                    $insertPosition = $lastMetaEndPosition + $matches[0][1] + strlen($matches[0][0]);
                } else {
                    $insertPosition = $lastMetaEndPosition;
                }
            } else {
                // If no <meta> tag found, insert at the end of the <head> tag
                $insertPosition = $headEndPosition;
            }
        }

        // Add the GTM HEAD script defined in the module
        if (Configuration::get('C_P_GTM_ENABLE')
            && Configuration::get('C_P_GTM_HEAD')
            && $insertPosition !== false) {
            // Insert the new variable just after the "<head>" tag
            $params['html'] = substr_replace($params['html'], Configuration::get('C_P_GTM_HEAD'), $insertPosition, 0);
        }

        if (Configuration::get('C_P_MUET_ENABLE')
            && Configuration::get('C_P_MUET_HEAD')
            && $insertPosition !== false) {
            // Insert the new variable just after the "<head>" tag
            $params['html'] = substr_replace($params['html'], Configuration::get('C_P_MUET_HEAD'), $insertPosition, 0);
        }

        // Add the Consent Mode tags just after the <HEAD> tag
        if ($insertPosition !== false) {
            $html = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/gtm_consentmode.tpl');

            if (method_exists('Media', 'packJS')) {
                $html = trim(Media::packJS($html), ';');
            }

            $html = '<s' . "cript data-keepinline='true' data-cfasync='false'>" . $html . '<' . '/script>'; // We don't include the tag inside the TPL because the minify action products a W3C error removing the space between attributes

            $html = '<met' . "a name='cookiesplus-added' content='true'>" . $html; // Prevent error with W3C when CCC

            // Insert the new variable just after the "<head>" tag
            $params['html'] = substr_replace($params['html'], $html, $insertPosition, 0);
        }

        if (Configuration::get('C_P_FB_ENABLE')) {
            $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
            $fbConsents = json_decode(Configuration::get('C_P_FB_CONSENT'), true) ?: [];
            $fbAllConsent = true;
            foreach (array_keys($fbConsents) as $fbConsent) {
                $key = 'cookiesplus-finality-' . (int) $fbConsent;
                if (!isset($cookiesPlusCookiePreferences['consents'][$key])
                    || (isset($cookiesPlusCookiePreferences['consents'][$key])
                        && $cookiesPlusCookiePreferences['consents'][$key] !== 'on')) {
                    $fbAllConsent = false;
                    break;
                }
            }

            $doNotConsentToPixelPosition = strpos($params['html'], 'doNotConsentToPixel');
            if ($doNotConsentToPixelPosition !== false
                && $insertPosition !== false) {
                if (!$fbAllConsent) {
                    $params['html'] = substr_replace($params['html'], '<' . 'script>window.doNotConsentToPixel = true;<' . '/script>', $insertPosition, 0);
                } else {
                    $params['html'] = substr_replace($params['html'], '<' . 'script>window.doNotConsentToPixel = false;<' . '/script>', $insertPosition, 0);
                }
            } else {
                $fbqPosition = strpos($params['html'], "fbq('init'");
                if ($fbqPosition !== false) {
                    if ($fbAllConsent) {
                        $params['html'] = substr_replace($params['html'], "fbq('consent', 'grant');", $fbqPosition, 0);
                    } else {
                        $params['html'] = substr_replace($params['html'], "fbq('consent', 'revoke');", $fbqPosition, 0);
                    }
                }
            }
        }

        if (Configuration::get('C_P_YT_ENABLE')) {
            $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
            $ytConsents = json_decode(Configuration::get('C_P_YT_CONSENT'), true) ?: [];
            $ytAllConsent = true;
            foreach (array_keys($ytConsents) as $ytConsents) {
                $key = 'cookiesplus-finality-' . (int) $ytConsents;
                if (!isset($cookiesPlusCookiePreferences['consents'][$key])
                    || (!isset($cookiesPlusCookiePreferences['consents'][$key])
                        && $cookiesPlusCookiePreferences['consents'][$key] !== 'on')) {
                    $ytAllConsent = false;
                    break;
                }
            }

            if (!$ytAllConsent) {
                // Youtube
                $params['html'] = str_replace('youtube.com/embed/', 'youtube-nocookie.com/embed/', $params['html']);
                $params['html'] = str_replace('youtube.com/s/player/', 'youtube-nocookie.com/s/player/', $params['html']);
                $params['html'] = str_replace('youtube.com/yts/jsbin/', 'youtube-nocookie.com/yts/jsbin/', $params['html']);
                $params['html'] = str_replace('youtube.com/iframe_api/', 'youtube-nocookie.com/iframe_api/', $params['html']);

                // Elementor
                $params['html'] = str_replace('data-video-id=', 'data-video-id-blocked=', $params['html']);

                if (Configuration::get('C_P_YT_ENABLE_FORCE')
                    && $insertPosition !== false) {
                    // For cache modules
                    $html = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/youtube.tpl');

                    // Insert the new variable just after the "<head>" tag
                    $params['html'] = substr_replace($params['html'], $html, $insertPosition, 0);
                }

                // stprovideos
                if ($insertPosition !== false) {
                    $params['html'] = substr_replace($params['html'], '<' . 'script>window.stProVideosEnablePrivacyEnhancedMode = true;<' . '/script>', $insertPosition, 0);
                }
            }
        }

        if (Configuration::get('C_P_DEBUG')
            && $insertPosition !== false) {
            $debug = '<' . 'script>window.cookiesplus_debug = ' . (Configuration::get('C_P_DEBUG') ? 'true' : 'false') . '<' . '/script>';
            $params['html'] = substr_replace($params['html'], $debug, $insertPosition, 0);
        }
    }

    /* Hola Linea Grafica! Esto es nuevo, todavía no lo habéis copiado en vuestro módulo */
    public function hookFilterCmsContent($params)
    {
        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
        foreach ($cookiesPlusFinalities as &$cookiesPlusFinality) {
            $cookiesPlusFinality['cookies'] = CookiesPlusCookie::getCookiesPlusCookies($cookiesPlusFinality['id_cookiesplus_finality'], (int)$this->context->language->id, true, $this->context->shop->id, false);
        }
        unset($cookiesPlusFinality);

        $this->context->smarty->assign([
            'link' => $this->context->link,
            'C_P_FINALITIES' => $cookiesPlusFinalities,
        ]);

        $customHtml = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/cookies-cms.tpl');

        // Replace the placeholder with the HTML
        $params['object']['content'] = str_replace('{cookiesplus_cookies_table}', $customHtml, $params['object']['content']);

        return $params;
    }

    /* Module functions */
    /* Backward compatibility */
    public static function updateCookie($modules)
    {
        return self::filterHookModuleExecList($modules);
    }

    public static function filterHookModuleExecList($modules, $hook_name = null)
    {
        // return $modules;
        if (!self::executeModule()) {
            return $modules;
        }

        if (!self::getGeo()) {
            return $modules;
        }

        // Exclude .map extensions
        $url = parse_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        if (isset($url['path']) && pathinfo($url['path'], PATHINFO_EXTENSION) === 'map') {
            return $modules;
        }
        $url = parse_url("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        if (isset($url['path']) && pathinfo($url['path'], PATHINFO_EXTENSION) === 'map') {
            return $modules;
        }

        $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities(null, true);

        foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
            $key = 'cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality'];
            if (!$cookiesPlusFinality['technical']
                && (!isset($cookiesPlusCookiePreferences['consents'][$key])
                    || (isset($cookiesPlusCookiePreferences['consents'][$key])
                        && $cookiesPlusCookiePreferences['consents'][$key] !== 'on'))) {
                $blockedModulesId = json_decode($cookiesPlusFinality['modules'], true) ?: [];

                if (is_array($modules) && is_array($blockedModulesId)) {
                    foreach ($modules as $key => $module) {
                        // Cookiesplus module can not be blocked
                        if ($module['module'] === 'cookiesplus') {
                            continue;
                        }

                        if (in_array($module['id_module'], $blockedModulesId)) {
                            unset($modules[$key]);
                        }
                    }
                }
            }
        }

        return $modules;
    }

    public function blockHookCall($params)
    {
        $blockedModulesByFinality = self::getBlockedModulesByFinality();
        $blockedModules = [
            [
                'module' => 'pspixel',
                'hook' => 'hookactionAjaxDieProductControllerdisplayAjaxQuickviewBefore',
            ],
        ];

        foreach (array_keys($blockedModulesByFinality) as $blockedModuleByFinality) {
            foreach ($blockedModules as $blockedModule) {
                if ($params['module']->name == $blockedModule['module']
                    && $params['hookName'] == $blockedModule['hook']
                    && $params['module']->id == $blockedModuleByFinality) {
                    return true;
                }
            }
        }

        return false;
    }

    public function blockModuleCode($params)
    {
        // Recursive call
        if (!self::executeModule($params)) {
            return;
        }

        if (!self::getGeo()) {
            return;
        }

        // Exclude admin calls
        /*
        if (defined('_PS_ADMIN_DIR_')) {
            return $modules;
        }
        */
        $context = Context::getContext();

        if (!$context->controller) {
            return;
        }

        // Exclude .map extensions
        $url = parse_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        if (isset($url['path']) && pathinfo($url['path'], PATHINFO_EXTENSION) === 'map') {
            return;
        }
        $url = parse_url("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        if (isset($url['path']) && pathinfo($url['path'], PATHINFO_EXTENSION) === 'map') {
            return;
        }

        $blockedModulesByFinality = self::getBlockedModulesByFinality();

        if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
            return $this->blockModuleCode17($params, $context, $blockedModulesByFinality);
        }

        return $this->blockModuleCode15($params, $context, $blockedModulesByFinality);
    }

    public function getBlockedModulesByFinality()
    {
        $cacheKey = 'CookiesPlus::blockModuleCode';

        if (!Cache::isStored($cacheKey)) {
            $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
            $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities(null, true);

            $blockedModulesByFinality = [];
            foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
                $key = 'cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality'];
                if (!$cookiesPlusFinality['technical']
                    && (!isset($cookiesPlusCookiePreferences['consents'][$key])
                        || (isset($cookiesPlusCookiePreferences['consents'][$key])
                            && $cookiesPlusCookiePreferences['consents'][$key] !== 'on'))
                ) {
                    $blockedModulesId = json_decode($cookiesPlusFinality['modules'], true) ?: [];

                    if (is_array($blockedModulesId)) {
                        foreach ($blockedModulesId as $module) {
                            // Cookiesplus module can not be blocked
                            if ($module === $this->id) {
                                continue;
                            }

                            $blockedModulesByFinality[(int) $module]['finalities'][] = (int) $cookiesPlusFinality['id_cookiesplus_finality'];
                        }
                    }
                }
            }

            Cache::store($cacheKey, $blockedModulesByFinality);
        }

        return Cache::retrieve($cacheKey);
    }

    public function blockModuleCode17($params, $context, $blockedModulesByFinality)
    {
        if (isset($params['module']) && is_object($params['module']) && isset($params['module']->id)) {
            if (isset($blockedModulesByFinality[$params['module']->id])) {
                // Remove JS and CSS files from blocked modules
                $js_files = [];
                $css_files = [];

                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $jsFileList = $context->controller->js_files;
                    foreach ($jsFileList as $jsFile) {
                        if (strpos($jsFile, '/modules/' . $params['module']->name) !== false) {
                            $js_files[] = $jsFile;
                            $context->controller->removeJs($jsFile);
                        }
                    }

                    $cssFileList = $context->controller->css_files;
                    foreach ($cssFileList as $cssFile) {
                        if (strpos($cssFile, '/modules/' . $params['module']->name) !== false) {
                            $css_files[] = $cssFile;
                            $context->controller->removeJs($cssFile);
                        }
                    }
                } elseif (method_exists($context->controller, 'getJavascript')) {
                    $jsFileList = $context->controller->getJavascript();
                    foreach ($jsFileList as $jsFileListPart) {
                        foreach ($jsFileListPart as $jsFileListPartContainer) {
                            foreach ($jsFileListPartContainer as $jsFileListPartContainerFile) {
                                if (strpos($jsFileListPartContainerFile['path'], '/modules/' . $params['module']->name) !== false
                                    || strpos($jsFileListPartContainerFile['id'], $params['module']->name) !== false) {
                                    $js_files[] = $jsFileListPartContainerFile['uri'];
                                    $context->controller->removeJs($jsFileListPartContainerFile['path']);
                                    $context->controller->unregisterJavascript($jsFileListPartContainerFile['id']);
                                }
                            }
                        }
                    }

                    $cssFileList = $context->controller->getStylesheets();
                    foreach ($cssFileList as $cssFileListPartContainer) {
                        foreach ($cssFileListPartContainer as $cssFileListPartContainerFile) {
                            if (strpos($cssFileListPartContainerFile['path'], '/modules/' . $params['module']->name) !== false
                                || strpos($cssFileListPartContainerFile['id'], $params['module']->name) !== false) {
                                $css_files[] = $cssFileListPartContainerFile['uri'];
                                $context->controller->removeCSS($cssFileListPartContainerFile['path']);
                                $context->controller->unregisterStylesheet($cssFileListPartContainerFile['id']);
                            }
                        }
                    }
                }

                // Remove cookies
                if (isset($params['headersBeforeExecution']) && $params['headersBeforeExecution']) {
                    // Remove the original headers
                    header_remove();

                    // Set old headers
                    foreach ($params['headersBeforeExecution'] as $header) {
                        header($header, false);
                    }
                }

                if (Configuration::get('C_P_REFRESH')) {
                    // The module is blocked but with refresh. Don't display any content
                    $params['display'] = '';
                } else {
                    $originalReturn = $params['display'];
                    $random = Tools::substr(md5(microtime()), 0, 10);
                    $divName = $params['hookName'] . '_' . $params['module']->id . '_' . $random;

                    if ($params['display']) {
                        $this->context->smarty->assign([
                            'divName' => $divName,
                            'id_module' => $params['module']->id,
                            'finalities' => implode(',', $blockedModulesByFinality[$params['module']->id]['finalities']),
                            'script' => json_encode($originalReturn),
                            'js' => empty($js_files) ? '[]' : json_encode($js_files),
                            'css' => empty($css_files) ? '[]' : json_encode($css_files),
                        ]);

                        $params['display'] = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/hookmoduledata.tpl');
                    } else {
                        $data = [
                            'divName' => $divName,
                            'id_module' => $params['module']->id,
                            'finalities' => implode(',', $blockedModulesByFinality[$params['module']->id]['finalities']),
                            'script' => $originalReturn ? json_encode($originalReturn) : null,
                            'js' => empty($js_files) ? '[]' : json_encode($js_files),
                            'css' => empty($css_files) ? '[]' : json_encode($css_files),
                        ];

                        Media::addJsDef([
                            'cookiesplus_js_' . $divName => json_encode($data),
                        ]);
                    }
                }
            }
        }
    }

    public function blockModuleCode15($params, $context, $blockedModulesByFinality)
    {
        if (!is_array($params['return'])) {
            return;
        }

        foreach (array_keys($params['return']) as $module) {
            if ($module = Module::getInstanceByName($module)) {
                if (isset($blockedModulesByFinality[$module->id])) {
                    // Remove JS and CSS files from blocked modules
                    $js_files = [];
                    $css_files = [];

                    if (version_compare(_PS_VERSION_, '1.7', '<')) {
                        $jsFileList = $context->controller->js_files;
                        foreach ($jsFileList as $jsFile) {
                            if (strpos($jsFile, '/modules/' . $module->name) !== false) {
                                $js_files[] = $jsFile;
                                $context->controller->removeJs($jsFile);
                            }
                        }

                        $cssFileList = $context->controller->css_files;
                        foreach ($cssFileList as $cssFile) {
                            if (strpos($cssFile, '/modules/' . $module->name) !== false) {
                                $css_files[] = $cssFile;
                                $context->controller->removeJs($cssFile);
                            }
                        }
                    } else {
                        $jsFileList = $context->controller->getJavascript();
                        foreach ($jsFileList as $jsFileListPart) {
                            foreach ($jsFileListPart as $jsFileListPartContainer) {
                                foreach ($jsFileListPartContainer as $jsFileListPartContainerFile) {
                                    if (strpos($jsFileListPartContainerFile['path'], '/modules/' . $module->name) !== false) {
                                        $js_files[] = $jsFileListPartContainerFile['path'];
                                        $context->controller->removeJs($jsFileListPartContainerFile['path']);
                                    }
                                }
                            }
                        }

                        $cssFileList = $context->controller->getStylesheets();
                        foreach ($cssFileList as $cssFileListPartContainer) {
                            foreach ($cssFileListPartContainer as $cssFileListPartContainerFile) {
                                if (strpos($cssFileListPartContainerFile['path'], '/modules/' . $module->name) !== false) {
                                    $css_files[] = $cssFileListPartContainerFile['path'];
                                    $context->controller->removeCSS($cssFileListPartContainerFile['path']);
                                }
                            }
                        }
                    }

                    if (Configuration::get('C_P_REFRESH')) {
                        $params['return'][$module->name] = '';
                    } else {
                        $originalReturn = $params['return'][$module->name];
                        $random = Tools::substr(md5(microtime()), 0, 10);
                        $divName = $params['hookName'] . '_' . $module->id . '_' . $random;

                        $this->context->smarty->assign([
                            'divName' => $divName,
                            'id_module' => $module->id,
                            'finalities' => implode(',', $blockedModulesByFinality[$module->id]['finalities']),
                            'script' => json_encode($originalReturn),
                            'js' => empty($js_files) ? '[]' : json_encode($js_files),
                            'css' => empty($css_files) ? '[]' : json_encode($css_files),
                        ]);

                        $params['return'][$module->name] = $this->context->smarty->fetch($this->local_path . 'views/templates/hook/hookmoduledata.tpl');
                    }
                }
            }
        }
    }

    public static function blockModuleCacheStatic($modulesToInvoke, $hookName)
    {
        if (empty($modulesToInvoke)) {
            return false;
        }

        // Don't filter in BO
        $context = Context::getContext();
        if (is_object(Context::getContext()->controller)
            && isset(Context::getContext()->controller->controller_type)
            && (Context::getContext()->controller->controller_type === 'admin'
                || Context::getContext()->controller->controller_type === 'moduleadmin')) {
            return $modulesToInvoke;
        }

        if (!Configuration::get('C_P_REFRESH')) {
            return $modulesToInvoke;
        }

        $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
        $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities(null, true);
        $cookiesPlusIdModule = self::getIdModuleByName('cookiesplus');

        $blockedModulesByFinality = [];
        foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
            $key = 'cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality'];
            if (!$cookiesPlusFinality['technical']
                && (!isset($cookiesPlusCookiePreferences['consents'][$key])
                    || (isset($cookiesPlusCookiePreferences['consents'][$key])
                        && $cookiesPlusCookiePreferences['consents'][$key] !== 'on'))
            ) {
                $blockedModulesId = json_decode($cookiesPlusFinality['modules'], true) ?: [];

                if (is_array($blockedModulesId)) {
                    foreach ($blockedModulesId as $module) {
                        // Cookiesplus module can not be blocked
                        if ($module === $cookiesPlusIdModule) {
                            continue;
                        }

                        $blockedModulesByFinality[(int) $module]['finalities'][] = (int) $cookiesPlusFinality['id_cookiesplus_finality'];
                    }
                }
            }
        }

        if (null === $hookName) {
            foreach ($modulesToInvoke as $modulesToInvokeByHook) {
                foreach ($modulesToInvokeByHook as $moduleToInvokeKey => $moduleToInvoke) {
                    if (array_key_exists($moduleToInvoke['id_module'], $blockedModulesByFinality)) {
                        unset($modulesToInvoke[$moduleToInvokeKey]);
                    }
                }
            }
        } else {
            foreach ($modulesToInvoke as $moduleToInvokeKey => $moduleToInvoke) {
                if (array_key_exists($moduleToInvoke['id_module'], $blockedModulesByFinality)) {
                    unset($modulesToInvoke[$moduleToInvokeKey]);
                }
            }
        }

        return $modulesToInvoke;
    }

    public function blockModuleCache($modulesToInvoke, $hookName)
    {
        return self::blockModuleCacheStatic($modulesToInvoke, $hookName);
    }

    public function initializeCookiesPlusPreferences($reset = false)
    {
        // There are some requests done after the cookie consent has been set that initialize the cookie again and the popup reappears
        if (!$reset && isset($_COOKIE['cookiesplus'])) {
            return;
        }

        // Retrieve the existing cookie value, if it exists
        if ($reset) {
            $existingCookieValue = json_decode('[]', true);
        } else {
            $existingCookieValue = isset($_COOKIE['cookiesplus']) ? $_COOKIE['cookiesplus'] : '[]';
            $existingCookieValue = json_decode($existingCookieValue, true);

        	// Consent hash
            // $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
            // $existingCookieValue['consent_hash'] = (Configuration::get('C_P_SAVE_CONSENT') && isset($cookiesPlusCookiePreferences['consent_hash'])) ? $cookiesPlusCookiePreferences['consent_hash'] : '';
        }

        // Set the consents in the cookie. It's necessary for cache modules
        if (Configuration::get('C_P_GTM_ENABLE')
            || Configuration::get('C_P_MUET_ENABLE')) {
            $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
            $gtmConsents = json_decode(Configuration::get('C_P_GTM_CONSENT'), true);
            $muetConsents = json_decode(Configuration::get('C_P_MUET_CONSENT'), true);

            $gtmConsentsForCookie = [];
            $muetConsentsForCookie = [];
            $consentsForCookie = [];

            foreach ($cookiesPlusFinalities as $cookiesPlusFinality) {
                if ($cookiesPlusFinality['technical']
                    && isset($gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'])) {
                    $consentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']] = 'on';
                    $gtmConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['gtm_consent_type'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'];
                    continue;
                }

                if ($cookiesPlusFinality['technical']
                    && isset($muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'])) {
                    $consentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']] = 'on';
                    $muetConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['muet_consent_type'] = $muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'];
                    continue;
                }

                // If the banner is not displayed because of GEO, grant all
                if (!self::getGeo()) {
                    $consentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']] = 'on';
                    $gtmConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['gtm_consent_type'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'];
                    $muetConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['muet_consent_type'] = $muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'];
                    continue;
                }

                if (isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])
                    && isset($gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'])
                    && $gtmConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality']) {
                        $gtmConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['gtm_consent_type'] = $gtmConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['gtmFinality'];
                }

                if (isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']])
                    && isset($muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'])
                    && $muetConsents[(int) $cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality']) {
                        $muetConsentsForCookie['cookiesplus-finality-' . $cookiesPlusFinality['id_cookiesplus_finality']]['muet_consent_type'] = $muetConsents[$cookiesPlusFinality['id_cookiesplus_finality']]['muetFinality'];
                }
            }

            // Add the consents
            $existingCookieValue['gtm_consent_mode'] = $gtmConsentsForCookie;
            $existingCookieValue['gtm_consent_mode']['url_passthrough'] = (bool) Configuration::get('C_P_GTM_URL_PASSTHROUGH');
            $existingCookieValue['gtm_consent_mode']['ads_data_redaction'] = Configuration::get('C_P_GTM_ADS_DATA_REDACTION');

            $existingCookieValue['muet_consent_mode'] = $muetConsentsForCookie;

            if ($consentsForCookie) {
                if (isset($existingCookieValue['consents'])) {
                    $existingCookieValue['consents'] += $consentsForCookie;
                } else {
                    $existingCookieValue['consents'] = $consentsForCookie;
                }
            }
        }

        // Encode the merged array into JSON
        $mergedJson = json_encode($existingCookieValue);
        $expiry = time() + ((Configuration::get('C_P_EXPIRY') ?: 365) * 86400);
        $domain = self::getDomain();
        $secure = Configuration::get('PS_SSL_ENABLED') && (int) Configuration::get('PS_SSL_ENABLED_EVERYWHERE') === 1;
        // $path = "'/'" . (Configuration::get('PS_COOKIE_SAMESITE') ? '; SameSite=' . Configuration::get('PS_COOKIE_SAMESITE') : '; SameSite=Lax');
        $path = '/'; // https://stackoverflow.com/questions/39750906/php-setcookie-samesite-strict

        if (headers_sent()) {
            echo 'Headers already sent';
        }

        if (strlen($mergedJson) > 4096) {
            echo 'Cookie data exceeds the maximum size limit';
        }

        setcookie('cookiesplus', $mergedJson, $expiry, $path, $domain, $secure, false);
    }

    public function saveCookiesPlusPreferences()
    {
        $cookiesPlusFinalityValue = [];

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Generate PDF consent
        if (Configuration::get('C_P_SAVE_CONSENT')) {
            do {
                $consentHash = md5(openssl_random_pseudo_bytes(20)) . '-' . Tools::substr(md5(openssl_random_pseudo_bytes(20)), 0, 8);
            } while (!$consentHash);

            $cookiesPlusFinalityValue['consent_hash'] = $consentHash;
            $consentDate = date('Y-m-d H:i:s', time());

            $data = [];
            $data['cookiesPlus']['info']['last_update'] = Tools::displayDate(date('Y-m-d', strtotime(Configuration::get('C_P_REVOKE_CONSENT'))), false);
            $data['cookiesPlus']['info']['consent_hash'] = $cookiesPlusFinalityValue['consent_hash'];
            $data['cookiesPlus']['info']['consent_date'] = $consentDate;
            $data['cookiesPlus']['info']['consent_ip'] = $ip;

            $cookiesPlusFinalities = CookiesPlusFinality::getCookiesPlusFinalities((int) $this->context->language->id, true);
            foreach ($cookiesPlusFinalities as &$cookiesPlusFinality) {
                $cookiesPlusFinality['cookies'] = CookiesPlusCookie::getCookiesPlusCookies($cookiesPlusFinality['id_cookiesplus_finality'], (int) $this->context->language->id, true, $this->context->shop->id, false);
                if (Tools::getValue('cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']) !== 'on'
                    && Tools::getValue('cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']) !== 'off'
                ) {
                    $_POST['cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']] = 'off';
                }
                $cookiesPlusFinality['cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']] = Tools::getValue('cookiesplus-finality-' . (int) $cookiesPlusFinality['id_cookiesplus_finality']);
            }
            unset($cookiesPlusFinality);
            $data['cookiesPlus']['cookiesPlusFinalities'] = $cookiesPlusFinalities;

            // Send an email to admin because of an error
            /*if (!$result) {
                Configuration::updateValue('C_P_SAVE_CONSENT', 0);
            }*/
            // Save consent
            $cookiesPlusUserConsent = new CookiesPlusUserConsent();
            $cookiesPlusUserConsent->data = json_encode($data);
            $cookiesPlusUserConsent->hash = $cookiesPlusFinalityValue['consent_hash'];
            $cookiesPlusUserConsent->date = $consentDate;
            $cookiesPlusUserConsent->ip = $ip;
            $cookiesPlusUserConsent->date_add = date('Y-m-d H:i:s');
            $cookiesPlusUserConsent->save();
        }

        if (!Tools::getValue('ajax')) {
            $cookiesPlusCookiePreferences = self::getCookiesPlusCookiePreferences();
            $cookiesPlusCookiePreferences['consent_hash'] = $consentHash;
            //self::initializeCookiesPlusPreferences('false');

            // Encode the merged array into JSON
            $mergedJson = json_encode($cookiesPlusCookiePreferences);
            $expiry = time() + ((Configuration::get('C_P_EXPIRY') ?: 365) * 86400);
            $domain = self::getDomain();
            $secure = Configuration::get('PS_SSL_ENABLED') && (int) Configuration::get('PS_SSL_ENABLED_EVERYWHERE') === 1;
            // $path = "'/'" . (Configuration::get('PS_COOKIE_SAMESITE') ? '; SameSite=' . Configuration::get('PS_COOKIE_SAMESITE') : '; SameSite=Lax');
            $path = '/'; // https://stackoverflow.com/questions/39750906/php-setcookie-samesite-strict

            if (headers_sent()) {
                echo 'Headers already sent';
            }

            if (strlen($mergedJson) > 4096) {
                echo 'Cookie data exceeds the maximum size limit';
            }

            setcookie('cookiesplus', $mergedJson, $expiry, $path, $domain, $secure, false);
        }

        return $cookiesPlusFinalityValue;
    }

    public static function getCookiesPlusCookiePreferences()
    {
        if (isset($_COOKIE['cookiesplus'])) {
            return json_decode($_COOKIE['cookiesplus'], true);
        }

        return [];
    }

    public function copyOverrideFolder()
    {
        if (!is_writable(_PS_MODULE_DIR_ . $this->name)) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')
            && version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->copyDir(
                _PS_MODULE_DIR_ . $this->name . '/override_16/classes/controller',
                _PS_MODULE_DIR_ . $this->name . '/override_17/classes/controller'
            );
        }

        $override_folder_name = 'override';
        if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
            $psVersion = '171';
        } elseif (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
            $psVersion = '17';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $psVersion = '16';
        } else {
            $psVersion = '15';
        }

        $version_override_folder = _PS_MODULE_DIR_ . $this->name . '/' . $override_folder_name . '_' . $psVersion;
        $override_folder = _PS_MODULE_DIR_ . $this->name . '/' . $override_folder_name;

        if (file_exists($override_folder) && is_dir($override_folder)) {
            $this->recursiveRmdir($override_folder);
        }

        if (is_dir($version_override_folder)) {
            $this->copyDir($version_override_folder, $override_folder);
        }

        // We don't need the Hook override for these modules
        if (Module::isInstalled('pagecache')
            || Module::isInstalled('jprestaspeedpack')) {
            $sourceFilePath = $override_folder . '/classes/Hook.php';
            $destinationFilePath = $override_folder . '/classes/Hook.php.old';

            rename($sourceFilePath, $destinationFilePath);
        }

        return true;
    }

    public function copyDir($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            if (!is_dir($dst) && !mkdir($dst)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
            }
            while (false !== ($file = readdir($dir))) {
                if (($file !== '.') && ($file !== '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }
    }

    public function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        $this->recursiveRmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function sanitizeJson($json)
    {
        $escapers = ['\\', '/', '"', "\n", "\r", "\t", "\x08", "\x0c", "\'"];
        $replacements = ['\\\\', '\\/', '\\"', '\\n', '\\r', '\\t', '\\f', '\\b', "\\\'"];

        return str_replace($escapers, $replacements, addslashes($json));
    }

    public function getDatabaseVersion()
    {
        $query = 'SELECT `version`
            FROM `' . _DB_PREFIX_ . 'module`
            WHERE `name` = \'' . $this->name . '\';';

        return Db::getInstance()->getValue($query);
    }

    public static function isModuleRegisteredOnHook($module_instance, $hook_name, $id_shop)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'hook_module
                  WHERE `id_hook` = ' . (int) Hook::getIdByName($hook_name, true) . '
                  AND `id_module` = ' . (int) $module_instance->id . '
                  AND `id_shop` = ' . (int) $id_shop;

        $rows = Db::getInstance()->executeS($sql);

        return !empty($rows);
    }

    public static function generateRandomString($length = 12)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function googleModuleDetected()
    {
        $knownGoogleModules = [
            'cdc_googletagmanager',
            'rcpganalytics',
            'rcpgtagmanager',
            'gadwordstracking',
            'ps_googleanalytics',
            'ganalyticspro',
        ];

        foreach ($knownGoogleModules as $knownGoogleModule) {
            if (Module::getInstanceByName($knownGoogleModule)) {
                return true;
            }
        }
    }

    public static function cacheModuleDetected()
    {
        $knownCacheModules = [
            'litespeedcache',
            'stadvancedcache',
            'jprestaspeedpack',
            'pagecache',
        ];

        foreach ($knownCacheModules as $knownCacheModule) {
            if (Module::getInstanceByName($knownCacheModule)) {
                return true;
            }
        }
    }

    public static function getDomain()
    {
        $domain = Tools::getHttpHost(false);
        $domain = preg_replace('/^www\./', '', $domain);

        // Prepend the protocol if it's not included
        if (!preg_match('~^(?:f|ht)tps?://~i', $domain)) {
            $domain = 'http://' . $domain;
        }

        // Parse the URL to extract the host
        $parsed_url = parse_url($domain);

        if ($parsed_url && isset($parsed_url['host'])) {
            $domain = $parsed_url['host'];
        } else {
            // Failed to parse URL, handle accordingly
            // For example, set domain to an empty string
            $domain = '';
        }

        if ($domain !== 'locahost') {
            $domain = '.' . $domain;
        }

        return $domain;
    }

    public static function getIdModuleByName($moduleName)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT `id_module`
             FROM `' . _DB_PREFIX_ . 'module`
             WHERE `name` = "' . pSQL($moduleName) . '"'
        );
    }
}
