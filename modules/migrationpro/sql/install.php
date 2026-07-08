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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_configuration` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_data`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_data` (
`id_data` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(3) NOT NULL,
  `source_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  PRIMARY KEY (`id_data`),
  UNIQUE KEY `type_source_id` (`type`,`source_id`),
  KEY `type` (`type`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_mapping`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_mapping` (
`id_mapping` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(128) NOT NULL,
  `type` varchar(128) NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_name` varchar(128) NOT NULL,
  `mapping` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_mapping`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_error_logs`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_error_logs` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `log_text` varchar(855) NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `log_date_add` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_warning_logs`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_warning_logs` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `log_text` varchar(855) NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `log_date_add` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_save_mapping`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_save_mapping` (
`id_mapping` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(128) NOT NULL,
  `type` varchar(128) NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_name` varchar(128) NOT NULL,
  `mapping` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_mapping`)
) DEFAULT CHARSET=utf8';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'migrationpro_process`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_process` (
`id_process` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `total` int(11) NOT NULL,
  `imported` int(11) NOT NULL,
  `id_source` int(11) NOT NULL,
  `error` varchar(255) NOT NULL,
  `error_count` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `time_start` timestamp NOT NULL,
  `finish` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_process`)
) DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_migrated_data` (
`id_data` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(100) NOT NULL,
  `source_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  PRIMARY KEY (`id_data`),
  UNIQUE KEY `entity_type_source_id` (`entity_type`,`source_id`)
) DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'migrationpro_pass` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `id_customer` int(11) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
