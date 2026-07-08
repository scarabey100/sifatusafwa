<?php
/**
 * NOTICE OF LICENSE.
 *
 * @file Redirect object model for ORM
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

class Redirect extends ObjectModel
{
    public $id_redirect;
    public $old;
    public $new;
    public $type;
    public $hit;
    public $date_add;
    public $date_upd;
    public $regex;
    public $active;

    public static $definition = array(
        'table' => 'redirect',
        'primary' => 'id_redirect',
        'multilang' => false,
        'fields' => array(
            'old' => array(
                'type' => ObjectModel::TYPE_STRING,
                'required' => true,
            ),
            'new' => array(
                'type' => ObjectModel::TYPE_STRING,
                'required' => true,
            ),
            'type' => array(
                'type' => ObjectModel::TYPE_STRING,
                'required' => true,
            ),
            'hit' => array(
                'type' => ObjectModel::TYPE_INT,
                'required' => false,
            ),
            'date_add' => array(
                'type' => ObjectModel::TYPE_DATE,
                'required' => false,
            ),
            'date_upd' => array(
                'type' => ObjectModel::TYPE_DATE,
                'required' => false,
            ),
            'regex' => array(
                'type' => ObjectModel::TYPE_BOOL,
                'required' => true,
            ),
            'active' => array(
                'type' => ObjectModel::TYPE_BOOL,
                'required' => true,
            ),
        ),
    );
}
