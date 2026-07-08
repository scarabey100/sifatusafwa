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
 * @copyright 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 */

// SQL

    $sql = array();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'redirect` (
			    `id_redirect` int(11) NOT NULL AUTO_INCREMENT,
				`old` text NOT NULL,
				`new` text NOT NULL,
				`type` varchar(10) NOT NULL,
				`hit` int(11) NOT NULL DEFAULT "0",
				`date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`date_add` timestamp NOT NULL,
				`regex` int(11) NOT NULL,
				`active` int(11) NOT NULL,
			  	PRIMARY KEY (`id_redirect`)
			  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			';

    $sql[] = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'redirect_shop` (
				`id_redirect_shop` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_redirect` int(10) unsigned NOT NULL,
				`id_shop` int(10) unsigned NOT NULL,
			  	PRIMARY KEY (`id_redirect_shop`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
			';
