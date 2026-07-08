<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OpartRemoteTools {
    //public static $remoteUrl = 'https://scripts.store-opart.fr/demo-opartstat/check/';
    public static $remoteUrl = 'http://localhost/serveur-distant-opartstat/check/';
    public static $useRemote = false;
    public static $privateKey = '';
    public static $opartAfId = '';
}