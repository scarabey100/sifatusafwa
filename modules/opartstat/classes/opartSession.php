<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

/**
 * Class AdminRedirectionsController
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OpartSession extends ObjectModel
{
    public $sessionId;
    public $userIp;
    public $createdAt;
    public $userLanguage;
    public $country;
    public $device; //1 = desktop, 2 = tablet, 4 = mobile
    public $referrer;
    public $pageUrl;
    public $idCart;
    public $elementId;
    public $controllerName;
    public $shopId;
    public $utm_medium;
    public $utm_campaign;
    public $gclid;
    public $userId;
    public $userAgent;

    public static $definition = [
        'table' => 'opartstat_sessions',
        'primary' => 'visiteId',
        'fields' => [
            'createdAt' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'userIp' => ['type' => self::TYPE_STRING], //validate by "isIp" function
            'pageUrl' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl'],
            'device' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'country' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'referrer' =>  ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'userLanguage' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'idCart' =>  ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'allow_null' => true],
            'elementId' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'controllerName' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'shopId' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'utm_medium' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'utm_campaign' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'gclid' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'userId' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'allow_null' => true],
            'userAgent' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];

    public static function isIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP))
            return true;
        else
            return false;
    }

    public static function isBlockedIp($ip)
    {
        $sql = 'SELECT ipId FROM ' . _DB_PREFIX_ . 'opartstat_ips_blocking WHERE ip = "' . pSQL($ip) . '"';
        if (Db::getInstance()->getValue($sql) != false)
            return true;
        else
            return false;
    }

    public function save($nullValues = false, $autoDate = true)
    {
        if (!$this->isIp($this->userIp)) {
            return false;
        }

        $this->createdAt = date("Y-m-d H:i:s");

        $context = Context::getContext();
        $this->idCart = (int) $context->cart->id;

        $this->pageUrl = $this->removeUnsupportedCharactersUrl($this->pageUrl);

     
       if ((int)Configuration::get('OPARTSTAT_USE_SEPARATE_DB') === 1) {
        try {
            $pdo = self::getExternalPdo();


            if (!$this->idCartAlreadySavedExternal($pdo)) {
                $this->addCartIdToLastVisitExternal($pdo, $this->userIp, $this->idCart);
            }

            $this->idCart = null;

            $stmt = $pdo->prepare("
                INSERT INTO opartstat_sessions
                (createdAt, userIp, pageUrl, device, country, referrer, userLanguage, idCart, elementId, controllerName, shopId, utm_medium, utm_campaign, gclid, userId, userAgent)
                VALUES
                (:createdAt, :userIp, :pageUrl, :device, :country, :referrer, :userLanguage, :idCart, :elementId, :controllerName, :shopId, :utm_medium, :utm_campaign, :gclid, :userId, :userAgent)
            ");

            $stmt->execute(array(
                ':createdAt' => (string) $this->createdAt,
                ':userIp' => (string) $this->userIp,
                ':pageUrl' => (string) $this->pageUrl,
                ':device' => $this->device !== null ? (int)$this->device : null,
                ':country' => $this->country !== null ? (string)$this->country : null,
                ':referrer' => (string) $this->referrer,
                ':userLanguage' => (string) $this->userLanguage,
                ':idCart' => null,
                ':elementId' => $this->elementId ? (int)$this->elementId : null,
                ':controllerName' => $this->controllerName ? (string)$this->controllerName : null,
                ':shopId' => $this->shopId ? (int)$this->shopId : null,
                ':utm_medium' => $this->utm_medium ? (string)$this->utm_medium : null,
                ':utm_campaign' => $this->utm_campaign ? (string)$this->utm_campaign : null,
                ':gclid' => $this->gclid ? (string)$this->gclid : null,
                ':userId' => $this->userId ? (int)$this->userId : null,
                ':userAgent' => $this->userAgent ? (string)$this->userAgent : null,
            ));

            return true;

            }
            catch (Exception $e) {
                return false;
            }
        }

        // mode normal DB PS
        $nbLines = $this->getNbLines();
        if ($nbLines > (int)Configuration::get('OPARTSTAT_MAX_VISITS')) {
            $this->deleteFirstLine();
        }

        if (!$this->idCartAlreadySaved()) {
            $this->addCartIdToLastVisit($this->userIp, $this->idCart);
        }

        $this->idCart = null;
        return parent::save($nullValues, $autoDate);

    }


    public function getNbLines()
    {
        $sql = "SELECT COUNT(visiteId) FROM `" . bqSQL(_DB_PREFIX_ . self::$definition['table']) . "`";
        $nbLines = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
        return $nbLines;
    }

    public function deleteFirstLine()
    {
        $sql = "DELETE FROM `" . bqSQL(_DB_PREFIX_ . self::$definition['table']) . "` ORDER BY visiteId ASC LIMIT 1";
        Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->execute($sql);
    }

    static public function getLastStatDate()
    {
        // Externe
        if ((int)Configuration::get('OPARTSTAT_USE_SEPARATE_DB') === 1) {
            try {
                $dbHost = (string) Configuration::get('OPARTSTAT_DB_HOST');
                $dbPort = (string) (Configuration::get('OPARTSTAT_DB_PORT') ?: '3306');
                $dbName = (string) Configuration::get('OPARTSTAT_DB_NAME');
                $dbUser = (string) Configuration::get('OPARTSTAT_DB_USER');
                $dbPass = (string) Configuration::get('OPARTSTAT_DB_PASS');

                $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';
                $pdo = new PDO($dsn, $dbUser, $dbPass, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ));

                $sql = "SELECT createdAt FROM opartstat_sessions ORDER BY visiteId ASC LIMIT 1";
                $lastDate = $pdo->query($sql)->fetchColumn();

                if (!$lastDate) {
                    $lastDate = date("Y-m-d H:i:s");
                }

                return $lastDate;
            } catch (Exception $e) {
                // fallback : si DB externe KO, on évite de planter les métriques
                return date("Y-m-d H:i:s");
            }
        }

        // Interne (PS)
        $sql = "SELECT createdAt FROM `" . bqSQL(_DB_PREFIX_ . self::$definition['table']) . "` ORDER BY visiteId ASC";
        $lastDate = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($lastDate == false) {
            $lastDate = date("Y-m-d H:i:s");
        }

        return $lastDate;
    }


    public function idCartAlreadySaved() {
        $sql = "SELECT visiteId FROM "._DB_PREFIX_."opartstat_sessions WHERE idCart = ".(int)$this->idCart;
        $res = Db::getInstance()->getValue($sql);
        if($res == null)
            return false;
        return true;
    }

    public function addCartIdToLastVisit($userIp, $idCart)
    {        
        $sql = "UPDATE `" . bqSQL(_DB_PREFIX_ . self::$definition['table']) . "` SET idCart = " . (int)$idCart . "
        WHERE userIp='" . pSQL($userIp) . "' ORDER BY visiteId DESC LIMIT 1";
        Db::getInstance()->execute($sql);       
    }

    /* public function remoteSave()
    {
        OpartStatTools::getSaasResponse("controllers/saveSession.php", $this, false);
        return true;
    } */

    public function removeUnsupportedCharacters($string) {
        $cleanString = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $string);
        return $cleanString;
    }

    public function setReferrer($string) {
        $string = $this->removeUnsupportedCharacters($string);
        $this->referrer = pSQL($string);
    }

    public function setUtmMedium($string) {
        $string = $this->removeUnsupportedCharacters($string);
        $this->utm_medium = pSQL($string);
    }

    public function setUtmCampaign($string) {
        $string = $this->removeUnsupportedCharacters($string);
        $this->utm_campaign = pSQL($string);
    }

    public function setGclid($string) {
        $this->gclid = pSQL($string);
    }

    public function setUserLanguage($string) {
        $string = trim($string);
        $string = substr($string, 0, 5);        
        $this->userLanguage = pSQL($string);
    }

    public function removeUnsupportedCharactersUrl($url) {
        $url = str_replace("*", "", $url);
        return $url;
    }


    private static function getExternalPdo()
    {
        $dbHost = (string) Configuration::get('OPARTSTAT_DB_HOST');
        $dbPort = (string) (Configuration::get('OPARTSTAT_DB_PORT') ?: '3306');
        $dbName = (string) Configuration::get('OPARTSTAT_DB_NAME');
        $dbUser = (string) Configuration::get('OPARTSTAT_DB_USER');
        $dbPass = (string) Configuration::get('OPARTSTAT_DB_PASS');

        $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';

        return new PDO($dsn, $dbUser, $dbPass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ));
    }

    public function idCartAlreadySavedExternal(PDO $pdo)
    {
        $stmt = $pdo->prepare("SELECT visiteId FROM opartstat_sessions WHERE idCart = :idCart LIMIT 1");
        $stmt->execute(array(':idCart' => (int) $this->idCart));
        $res = $stmt->fetchColumn();

        return ($res !== false && $res !== null);
    }

    public function addCartIdToLastVisitExternal(PDO $pdo, $userIp, $idCart)
    {
        $stmt = $pdo->prepare("
            UPDATE opartstat_sessions
            SET idCart = :idCart
            WHERE userIp = :userIp
            ORDER BY visiteId DESC
            LIMIT 1
        ");
        $stmt->execute(array(
            ':idCart' => (int) $idCart,
            ':userIp' => (string) $userIp,
        ));
    }


}
