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

class AdminOpartRedirectToOldStatController extends ModuleAdminController
{
  public function __construct()
  {
    Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminStats', true));
  }
}
