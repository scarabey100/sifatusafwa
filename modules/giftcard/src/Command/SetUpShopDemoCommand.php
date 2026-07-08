<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */

namespace TimActive\Module\GiftCard\Command;

use Cookie;
use Doctrine\ORM\EntityManagerInterface;
use Employee;
use Module;
use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}
class SetUpShopDemoCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $metadata;

    private $dbName;

    private $dbPrefix;

    protected function configure()
    {
        $this
            ->setName('giftcard:setup_shop_demo')
            ->setDescription('Set up demo shop for giftcard module');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require_once $this->getContainer()->get('kernel')->getRootDir() . '/../config/config.inc.php';
        $config = include _PS_ROOT_DIR_ . '/app/config/parameters.php';
        $this->initContext($output);
        \Configuration::updateValue('PS_COOKIE_SAMESITE', 'None');
        $idLang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $shop_id = (int) \Configuration::get('PS_SHOP_DEFAULT');
        $m_i = \Module::getInstanceByName('giftcard');
        $id_lang_en = (int) \Language::getIdByIso('en');
        $id_lang_fr = (int) \Language::getIdByIso('fr');
        $output->writeln('<comment>CREATE DEFAULT TEMPLATE...</comment>');
        \GiftCardTools::creatingDefaultTemplates(true);
        $output->writeln('CREATE GIFT CARD...');
        \GiftCardTools::creatingDefaultGiftCards();
        $output->writeln('CREATE Profile...');
        $profile = new \Profile();
        $profile->name = [(int) \Configuration::get('PS_LANG_DEFAULT') => 'demo'];
        $profile->add();
        $access = new \Access();
        $access->updateLgcModuleAccess((int) $profile->id, $m_i->id, 'configure', 1);
        $access->updateLgcModuleAccess((int) $profile->id, $m_i->id, 'view', 1);
        $admin_tab_sell_id = \Tab::getIdFromClassName('SELL');
        $admin_tab_order_id = \Tab::getIdFromClassName('AdminParentOrders');
        $admin_tab_o_id = \Tab::getIdFromClassName('AdminOrders');
        $admin_tab_catalog_id = \Tab::getIdFromClassName('AdminCatalog');
        $admin_tab_gctemplate_id = \Tab::getIdFromClassName('AdminGiftCardTemplate');
        $admin_tab_gco_id = \Tab::getIdFromClassName('AdminGiftCardOrder');
        $admin_tab_gc_id = \Tab::getIdFromClassName('AdminGiftCard');
        $admin_tab_pm_id = \Tab::getIdFromClassName('AdminParentModulesSf');
        $admin_tab_msf_id = \Tab::getIdFromClassName('AdminModulesSf');
        $admin_tab_m_id = \Tab::getIdFromClassName('AdminModules');
        $admin_tab_i_id = \Tab::getIdFromClassName('IMPROVE');
        $access->updateLgcAccess((int) $profile->id, $admin_tab_sell_id, 'view', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_order_id, 'view', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_catalog_id, 'view', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_gctemplate_id, 'all', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_gc_id, 'all', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_gco_id, 'all', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_o_id, 'all', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_i_id, 'view', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_m_id, 'view', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_m_id, 'edit', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_pm_id, 'edit', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_msf_id, 'edit', 1);
        $access->updateLgcAccess((int) $profile->id, $admin_tab_msf_id, 'view', 1);
        $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
        $output->writeln('CREATE EMPLOYEES...');
        $e1 = new \Employee();
        $e1->lastname = 'gcemployeeen';
        $e1->firstname = 'gcemployeeen';
        $e1->default_tab = $admin_tab_gctemplate_id;
        $e1->id_lang = $id_lang_en;
        $e1->passwd = $crypto->hash('demodemo');
        $e1->active = true;
        $e1->id_profile = (int) $profile->id;
        $e1->email = 'demo@demo.com';
        $e1->add();
        $e2 = new \Employee();
        $e2->lastname = 'gcemployeefr';
        $e2->firstname = 'gcemployeefr';
        $e2->default_tab = $admin_tab_gctemplate_id;
        $e2->id_lang = $id_lang_fr;
        $e2->passwd = $crypto->hash('demodemo');
        $e2->active = true;
        $e2->id_profile = (int) $profile->id;
        $e2->email = 'demo@demo.fr';
        $e2->add();

        $meta = new \Meta();
        $meta->page = 'module-giftcard-choicegiftcard';
        $meta->title = [$id_lang_en => 'Gift cards', $id_lang_fr => 'Cartes cadeaux'];
        $meta->url_rewrite = [$id_lang_en => 'gift-cards', $id_lang_fr => 'cartes-cadeaux'];
        $meta->add();
        $module_menu = \Module::getInstanceByName('ps_mainmenu');
        if ($module_menu->isInstalled('ps_mainmenu')) {
            $mtl_label = [$id_lang_en => 'Gift cards', $id_lang_fr => 'Cartes cadeaux'];
            $shopDomain = \Configuration::get('PS_SHOP_DOMAIN');
            $shopDomain = (\Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $shopDomain;
            $mtl_link = [
                $id_lang_en => $shopDomain . '/en/gift-cards',
                $id_lang_fr => $shopDomain . '/fr/cartes-cadeaux',
            ];
            \Ps_MenuTopLinks::add($mtl_link, $mtl_label, 0, 1);
            $old_conf = \Configuration::get('MOD_BLOCKTOPMENU_ITEMS');
            \Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', $old_conf . ',LNK1');
            // https://giftcard.modules.timactive.com/fr/module/giftcard/choicegiftcard
        }
        /**$localization_pack = new \LocalizationPack();
         * $version = str_replace('.', '', _PS_VERSION_);
         * $version = substr($version, 0, 2);
         * $output->writeln('INSTALL PACK FR...');
         * $iso_localization_pack = "fr";
         * $packfr = @\Tools::file_get_contents(_PS_API_URL_.'/localization/'.$version.'/'.$iso_localization_pack.'.xml');
         * $localization_pack->loadLocalisationPack($packfr, array(), false, $iso_localization_pack);**/
        $output->writeln('INSTALL PAYMENT MODULE...');
        $wire_module = \Module::getInstanceByName('ps_wirepayment');
        if (!\Module::isInstalled('ps_wirepayment')) {
            $wire_module->install();
        }
        $check_module = \Module::getInstanceByName('ps_checkpayment');
        if (!\Module::isInstalled('ps_checkpayment')) {
            $check_module->install();
        }
        $ps_contactinfo = \Module::getInstanceByName('ps_contactinfo');
        if (\Module::isInstalled('ps_contactinfo')) {
            $ps_contactinfo->disable(true);
        }
        \Configuration::updateValue('PS_REWRITING_SETTINGS', 1);
        \Configuration::updateValue('BANK_WIRE_OWNER', 'TimActive');
        \Configuration::updateValue('BANK_WIRE_DETAILS', 'TEST');
        \Configuration::updateValue('BANK_WIRE_ADDRESS', '44000 Nantes, France');
        \Configuration::updateValue('CHEQUE_NAME', 'TimActive');
        \Configuration::updateValue('CHEQUE_ADDRESS', '44000 Nantes, France');
        $sql = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . 'module_country(id_module, id_shop, id_country)
    SELECT ' . $wire_module->id . ', 1, id_country from ' . _DB_PREFIX_ . 'country';
        echo $sql;
        \Db::getInstance()->execute($sql);
        $sql = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . 'module_country(id_module, id_shop, id_country)
    SELECT ' . $check_module->id . ', 1, id_country from ' . _DB_PREFIX_ . 'country';
        \Db::getInstance()->execute($sql);
        $output->writeln('GENERATE HTACCESS...');
        \Tools::generateHtaccess(_PS_ROOT_DIR_ . '/.htaccess', null, null, '', 0, false, 0);
        /**
         * Add this line to file .htaccess
         * <ifmodule mod_headers.c>
         * Header always edit Set-Cookie ^(.*)$ $1;SameSite=None;Secure
         * </ifmodule>
         */
        $fileHtaccess = _PS_ROOT_DIR_ . '/.htaccess';
        $content = \Tools::file_get_contents($fileHtaccess);
        $newContent = "<IfModule mod_headers.c>\nHeader always edit Set-Cookie ^(.*)$ $1;SameSite=None;Secure\n</IfModule>\n\n";
        $content = $newContent . $content;
        file_put_contents($fileHtaccess, $content);
        $output->writeln('ENABLE & RESET CACHE...');
        \Tools::enableCache();
        \Tools::clearCache(\Context::getContext()->smarty);
        \Tools::restoreCacheSettings();

        return true;
    }

    private function initContext($output)
    {
        /** @var LegacyContext $legacyContext */
        $legacyContext = $this->getContainer()->get('prestashop.adapter.legacy.context');
        // We need to have an employee or the module hooks don't work
        // see LegacyHookSubscriber
        if (!$legacyContext->getContext()->employee) {
            // Even a non existing employee is fine
            $legacyContext->getContext()->employee = new \Employee(1);
            $output->writeln('Employee set' . $legacyContext->getContext()->employee->firstname);
        }
    }
}
