<?php
/**
 * DISCLAIMER.
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future. If you wish to customize the Module for
 * your needs please use "themes/" or/and "override/modules" directories
 * or refer to http://www.prestashop.com for more information.
 *
 *  .--.
 *  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
 *  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
 *  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
 *       w w w . d r e a m - m e - u p . f r       '
 *
 * @author    Dream me up <prestashop@dream-me-up.fr>
 * @copyright 2007 - 2024 Dream me up
 * @license   All Rights Reserved
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class DmuPromos extends Module
{
    public function __construct()
    {
        $this->name = 'dmupromos';
        $this->tab = 'pricing_promotion';
        $this->version = '3.1.2';
        $this->author = 'Dream me up';
        $this->module_key = 'bed9fe2d8aa00a9957a581e97078e5e3';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DMU Discount campaigns');
        $this->description = $this->l(
            'You can create campaigns in advance by selecting the products concerned and apply reductions in mass.'
        );

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        // Onglet d'Administration du module
        $this->tab_parent = [
            'default' => 'AdminPriceRule',
            '1.7' => 'AdminCatalog',
        ];
        $this->tab_controller = 'AdminDmuPromos';
        $this->tab_name = ['en' => 'Discount campaigns', 'fr' => 'Campagnes de promotions'];

        // Onglets à afficher dans la configuration du module
        $this->module_tabs_config = [];

        // Mise à jour si version antérieure installée
        $this->checkUpdates();
    }

    public function install()
    {
        // Installation de l'onglet d'Administration du module
        $position = 1;
        reset($this->tab_parent);
        $tab_parent = current($this->tab_parent);
        foreach ($this->tab_parent as $version => $parent) {
            if ('default' != $version && version_compare(_PS_VERSION_, $version, '>=')) {
                $tab_parent = $parent;
                $position = 0;
            }
        }
        if (!$this->installModuleTab($this->tab_controller, $this->tab_name, $tab_parent, $position)) {
            return false;
        }
        // Création des tables en Base de données
        if (!$this->installDataBase()) {
            return false;
        }
        // Version d'installation pour mises à jour éventuelles
        Configuration::updateGlobalValue('DMUPROMOS_VERSION', $this->version);

        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionUpdateQuantity');
    }

    public function uninstall()
    {
        // Suppression des tables en Base de données
        if (!$this->uninstallDataBase()) {
            return false;
        }
        // Désinstallation de l'onglet d'Administration du module
        $this->uninstallModuleTab($this->tab_controller);

        return parent::uninstall();
    }

    private function installDataBase()
    {
        $sql = [];
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'promos_campagnes` (
                    `id_promos_campagnes` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                    `date_debut` DATETIME NOT NULL ,
                    `date_fin` DATETIME NOT NULL ,
                    `name` VARCHAR( 255 ) NOT NULL,
                    `search_only_active` BOOLEAN NOT NULL DEFAULT 0,
                    `search_new_age` INT( 10 ) NOT NULL DEFAULT 0,
                    `search_stock_age` INT( 10 ) NOT NULL DEFAULT 0,
                    `apply_on_stock_only` BOOLEAN NOT NULL DEFAULT 0,
                    `is_onsale` TINYINT(1) NOT NULL DEFAULT 0,
                    `id_shop` INT( 10 ) NOT NULL,
                    `id_currency` INT(10) NOT NULL DEFAULT 0,
                    `id_country` INT(10) NOT NULL DEFAULT 0,
                    `id_group` INT(10) NOT NULL DEFAULT 0
                    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'promos_produits` (
                    `id_promos_campagnes` INT( 10 ) NOT NULL ,
                    `id_product` INT( 10 ) NOT NULL,
                    `id_product_attribute` INT( 10 ) NOT NULL,
                    `id_specific_price` INT( 10 ) NOT NULL,
                    `solde` BOOLEAN NOT NULL DEFAULT 0,
                    INDEX (id_promos_campagnes),
                    INDEX (id_product),
                    INDEX (id_specific_price)
                    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function uninstallDataBase()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'promos_produits`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'promos_campagnes`';
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function installModuleTab($tab_class_name, $tab_name, $parent_class_name = 'AdminAdmin', $position = false)
    {
        // Vérification que l'onglet n'existe pas déjà !
        if (!Tab::getIdFromClassName($tab_class_name)) {
            // création du "tableau de langue" pour le Nom de l'onglet
            $tab_names = [];
            if (is_array($tab_name)) {
                reset($tab_name);
                $default_name = current($tab_name);
                foreach (Language::getLanguages(false) as $lang) {
                    $tab_names[$lang['id_lang']] = $default_name;
                    if (isset($tab_name[$lang['iso_code']])) {
                        $tab_names[$lang['id_lang']] = $tab_name[$lang['iso_code']];
                    }
                }
            } else {
                foreach (Language::getLanguages(false) as $lang) {
                    $tab_names[$lang['id_lang']] = $tab_name;
                }
            }
            // Récupération de l'ID de l'onglet parent
            $sql = 'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = \'' . $parent_class_name . '\'';
            $tab_id_parent = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if (!$tab_id_parent) {
                return false;
            }
            // Création du nouvel onglet !
            $tab = new Tab();
            $tab->name = $tab_names;
            $tab->class_name = $tab_class_name;
            $tab->module = $this->name;
            $tab->id_parent = $tab_id_parent;
            if (!$tab->save()) {
                return false;
            }
            if ($position) {
                // Placement de l'onglet en première position (TODO : pouvoir choisir la position)
                $sql = 'SELECT `id_tab`,`position`
                        FROM `' . _DB_PREFIX_ . 'tab`
                        WHERE `id_parent` = ' . (int) $tab->id_parent . '
                        AND `id_tab` != ' . (int) $tab->id . '
                        ORDER BY `position`';
                $position = 2;
                foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $sel_tab) {
                    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'tab`
                                                SET `position` = ' . ($position++) . '
                                                WHERE `id_tab` = ' . (int) $sel_tab['id_tab']);
                }
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'tab`
                                            SET `position` = 1
                                            WHERE `id_tab` = ' . (int) $tab->id);
            }
        }

        return true;
    }

    private function uninstallModuleTab($tab_class_name)
    {
        $id_tab = Tab::getIdFromClassName($tab_class_name);
        if (0 != $id_tab) {
            // Récupération de l'ID de l'onglet parent et Suppression de l'onglet
            $tab = new Tab($id_tab);
            $tab_id_parent = $tab->id_parent;
            $tab->delete();

            // Rangement des onglets restants
            $sql = 'SELECT `id_tab`,`position`
                    FROM `' . _DB_PREFIX_ . 'tab`
                    WHERE `id_parent` = ' . (int) $tab_id_parent . '
                    ORDER BY `position`';
            $position = 1;
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $sel_tab) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'tab`
                                            SET `position` = ' . ($position++) . '
                                            WHERE `id_tab` = ' . (int) $sel_tab['id_tab']);
            }

            return true;
        }

        return false;
    }

    private function checkUpdates()
    {
        if (!Module::isInstalled($this->name)) {
            return;
        }

        $dmupromos_version = Configuration::getGlobalValue('DMUPROMOS_VERSION');

        if (!$dmupromos_version) {
            $dmupromos_version = '1.0';
            // version 1.0
            $sql = 'UPDATE ' . _DB_PREFIX_ . 'specific_price SET from_quantity = 1 WHERE from_quantity = 0';
            Db::getInstance()->execute($sql);

            // version 1.8.1
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits` ADD `solde` BOOLEAN NOT NULL DEFAULT 0';
            if (!Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . 'promos_produits` solde')) {
                Db::getInstance()->execute($sql);
            }

            // version 1.9.1
            $sql = [];
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits` ADD INDEX (`id_promos_campagnes`)';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits` ADD INDEX (`id_product`)';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits` ADD INDEX (`id_specific_price`)';
            foreach ($sql as $req) {
                Db::getInstance()->execute($req);
            }
        }

        // version 2.0.0
        if (version_compare($dmupromos_version, '2.0.0', '<')) {
            if (!Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . 'promos_campagnes` id_shop')) {
                $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                        ADD `id_shop` INT NOT NULL';
                Db::getInstance()->execute($sql);
                $id_shop = Shop::getContextShopID();
                if (empty($id_shop)) {
                    $id_shop = Configuration::get('PS_SHOP_DEFAULT');
                }
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'promos_campagnes`
                        SET `id_shop` = ' . (int) $id_shop . ' WHERE id_shop = 0';
                Db::getInstance()->execute($sql);
            }

            Configuration::deleteByName('DMUPROMOS_MAJ_SOLDE');
        }

        // version 2.0.2
        if (version_compare($dmupromos_version, '2.0.2', '<')) {
            $sql = [];
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                    ADD `search_all` BOOLEAN NOT NULL DEFAULT 0';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                    ADD `search_new_age` INT( 10 ) NOT NULL DEFAULT 0';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                    ADD `search_stock_age` INT( 10 ) NOT NULL DEFAULT 0';
            foreach ($sql as $req) {
                Db::getInstance()->execute($req);
            }
        }

        // version 2.1.0
        if (version_compare($dmupromos_version, '2.1.0', '<')) {
            $sql = [];
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits`
                    ADD `id_product_attribute` INT( 10 ) NOT NULL DEFAULT 0';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_produits`
                    ADD INDEX ( `id_product_attribute` )';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                    ADD `is_onsale` TINYINT( 1 ) NOT NULL DEFAULT 0';
            $sql[] = 'UPDATE `' . _DB_PREFIX_ . 'tab`
                    SET `' . _DB_PREFIX_ . 'tab`.`class_name` = \'AdminDmuPromos\'
                    WHERE `' . _DB_PREFIX_ . 'tab`.`class_name` LIKE \'admindmupromos\'';
            $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                    CHANGE COLUMN `search_all` `search_only_active` TINYINT(1) NOT NULL DEFAULT 0';
            foreach ($sql as $req) {
                Db::getInstance()->execute($req);
            }
        }

        // version 2.1.1
        if (version_compare($dmupromos_version, '2.1.1', '<')) {
            @unlink(dirname(__FILE__) . '/AdminDmuPromosController.php');
        }

        // version 2.1.4
        if (version_compare($dmupromos_version, '2.1.4', '<')) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes`
                ADD COLUMN `id_currency` INT(10) NOT NULL DEFAULT 0 AFTER `id_shop`,
                ADD COLUMN `id_country` INT(10) NOT NULL DEFAULT 0 AFTER `id_currency`,
                ADD COLUMN `id_group` INT(10) NOT NULL DEFAULT 0 AFTER `id_country`';
            Db::getInstance()->execute($sql);
        }

        // version 2.5.0
        if (version_compare($dmupromos_version, '2.5.0', '<')) {
            $this->registerHook('actionUpdateQuantity');
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'promos_campagnes` ADD `apply_on_stock_only` 
                    TINYINT(1) NOT NULL DEFAULT 0 AFTER `search_stock_age`';
            Db::getInstance()->execute($sql);
        }

        // version 2.5.1
        if (version_compare($dmupromos_version, '2.5.1', '<')) {
            // Au cas ou il y aurait des lignes avec le bug de l'id_product = 0
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'promos_produits` WHERE id_product = 0';
            Db::getInstance()->execute($sql);

            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'specific_price` WHERE id_product = 0';
            Db::getInstance()->execute($sql);
        }

        if (version_compare($dmupromos_version, $this->version, '<')) {
            Configuration::updateGlobalValue('DMUPROMOS_VERSION', $this->version);
        }
    }

    // Configuration du module
    public function getContent()
    {
        // Récupération du contenu des onglets
        $content = [];
        foreach ($this->module_tabs_config as $tab => $vals) {
            $content[$tab] = $this->{$vals['content']}();
        }
        // Creation du chemin via le Menu
        $how_to_path = '';
        reset($this->tab_parent);
        $tab_parent = current($this->tab_parent);
        foreach ($this->tab_parent as $version => $parent) {
            if ('default' != $version && version_compare(_PS_VERSION_, $version, '>=')) {
                $tab_parent = $parent;
            }
        }
        foreach ([$tab_parent, $this->tab_controller] as $menu) {
            $sql = 'SELECT name FROM `' . _DB_PREFIX_ . 'tab` t
                    INNER JOIN `' . _DB_PREFIX_ . 'tab_lang` tl
                    ON tl.id_tab = t.id_tab AND tl.id_lang = ' . $this->context->language->id . '
                    WHERE class_name = \'' . $menu . '\'';
            $how_to_path .= (empty($how_to_path) ? '' : ' &gt; ') .
                Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        $file_documentation = 'readme_' . $this->context->language->iso_code . '.pdf';
        if (!file_exists(dirname(__FILE__) . '/' . $file_documentation)) {
            $file_documentation = 'readme_en.pdf';
        }

        $this->context->smarty->assign([
            'version_prestashop' => _PS_VERSION_,
            'version_module' => $this->version,
            'module_name' => $this->displayName,
            'module_description' => $this->description,
            'module_path' => '../modules/' . $this->name,
            'module_how_to' => $this->l('To use the module, you must use the menu to access it')
                . ' : <b>' . $how_to_path . '</b>',
            'documentation_pdf' => $file_documentation,
            'config_tabs' => $this->module_tabs_config,
            'txt_follow' => $this->l('Follow us'),
            'txt_follow_our' => $this->l('Follow our'),
            'txt_on' => $this->l('on'),
            'txt_and' => $this->l('and'),
            'txt_know_actu' => $this->l('to know all the news around our Addons'),
            'txt_to_have_details' => $this->l('to have all details on our new Addons versions and for every new launch of Addon'),
            'content' => [],
        ]);

        $tpl_name = 'configure.tpl';
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $tpl_name = 'configure-1.5.tpl';
        }

        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/' . $tpl_name);
    }

    public function hookDisplayHeader($params)
    {
        if (!$this->active) {
            return;
        }
        require_once dirname(__FILE__) . '/classes/CampagnesPromos.php';
        CampagnesPromos::updateOnSales();
    }

    public function hookActionUpdateQuantity($params)
    {
        if (!$this->active) {
            return;
        }

        // Si le produit fait partie d'une campagne de promo avec restriction
        // Et qu'il n'est plus en stock
        // On l'enlève de la campagne
        if ($params['quantity'] <= 0) {
            require_once dirname(__FILE__) . '/classes/CampagnesPromos.php';
            $campagnes_products = CampagnesPromos::getProductsFromCampagne(
                $params['id_product'],
                $params['id_product_attribute']
            );
            foreach ($campagnes_products as $cp) {
                if (1 == (int) $cp['apply_on_stock_only']) {
                    // On enlève le produit de la campagne
                    CampagnesPromos::deleteProductFromCampagne(
                        $cp['id_promos_campagnes'],
                        $cp['id_product'],
                        $cp['id_product_attribute']
                    );

                    // Si c'est une déclinaison, on va regarder le stock du produit global
                    // et enlever la promo globale sur tout si <= 0
                    if ((int) $cp['id_product_attribute'] > 0) {
                        $stock_product = StockAvailable::getQuantityAvailableByProduct($cp['id_product'], 0);

                        if ($stock_product <= 0) {
                            CampagnesPromos::deleteProductFromCampagne(
                                $cp['id_promos_campagnes'],
                                $cp['id_product'],
                                0
                            );
                        }
                    }
                }
            }
        }
    }
}
