<?php
/**
 * NOTICE OF LICENSE.
 *
 * @file Update for Magic Redirect new 1.4.1 version (February 2020)
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

function upgrade_module_1_4_1($object)
{
    $requests_results = array();

    // Changing old date_add function and adding hit counter
    $requests_sql = array(
        "ALTER TABLE `"._DB_PREFIX_."redirect` CHANGE `date_add` `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
        "ALTER TABLE `"._DB_PREFIX_."redirect` ADD `hit` INT NOT NULL DEFAULT '0' AFTER `type`;"
    );

    $db = Db::getInstance();

    foreach ($requests_sql as $request_sql) {
        $requests_results[] = $db->execute($request_sql);
    }

    $result = (bool) !in_array(false, $requests_results);

    return $result;
}
