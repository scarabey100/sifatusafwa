<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from MigrationPro
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the MigrationPro is strictly forbidden.
 * In order to obtain a license, please contact us: contact@migration-pro.com
 *
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe MigrationPro
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la MigrationPro est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter la MigrationPro a l'adresse: contact@migration-pro.com
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2022 MigrationPro
 * @license   Commercial license
 * @package   MigrationPro: Prestashop Upgrade and Migrate tool
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_6_2_2($module)
{
    /**
     * Do everything you want right there,
     * You could add a column in one of your module's tables
     */
    $res = Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_configuration` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(128) NOT NULL,
                `value` varchar(128) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY (`name`)
            ) DEFAULT CHARSET=utf8'
    );

    if ($res) {
        MigrationPro::mpConfigure('latest_migrated_product_id', MigrationProMigratedData::getLastId('product'));
        MigrationPro::mpConfigure('latest_migrated_customer_id', MigrationProMigratedData::getLastId('customer'));
        MigrationPro::mpConfigure('latest_migrated_order_id', MigrationProMigratedData::getLastId('order'));
    } else {
        return false;
    }
    return true;
}
