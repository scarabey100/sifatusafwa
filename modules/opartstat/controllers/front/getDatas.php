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

include_once(dirname(__FILE__) . '/../../classes/opartStatTools.php');

class OpartStatGetDatasModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return ['success' => false, 'message' => 'Unauthorized method'];
        }

        $encryptedTokenBase64 = OpartStatTools::getTokenFromSaas();

        if ($encryptedTokenBase64 == "") {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token is empty']);
            die();
        }

        //if (($encryptedTokenBase64 == false) || !preg_match('/Bearer\s(\S+)/', $encryptedTokenBase64, $matches)) {
        if (($encryptedTokenBase64 == false)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token not found']);
            die();
        }

        //$encryptedTokenBase64WithoutBearer = preg_replace('/^Bearer\s/', '', $encryptedTokenBase64);
        
        $response = OpartStatTools::getSaasResponse("controllers/checkShopToken.php", null, false, false, null, $encryptedTokenBase64);

        if ($response == null || $response['success'] != true) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Token do not match']);
            die();
        }

        $jsonDatas = file_get_contents('php://input');

        if (empty($jsonDatas)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'jsonDatas is empty']);
            die();
        }

        $datas = json_decode($jsonDatas, true);

        if (empty($datas)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'datas is empty']);
            die();
        }

        $authorizedTableNames = array(
            "googleAdsAds",
            "googleAdsCampaigns",
            "googleAdsClicks",
            "googleAdsDailyDatas",
            "googleAdsGroups"
        );

        if (!in_array($datas['tableName'], $authorizedTableNames)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'this tableName is not authorized']);
            die();
        }

        $tableName = $datas['tableName'];
        $datasToUpload = $datas['datasToUpload'];
        $db = Db::getInstance();

        if ($tableName === 'googleAdsClicks') 
            $idColumn = 'gclid';
        else 
            $idColumn = 'id';        

        $ids = array_map('pSQL', array_column($datasToUpload, $idColumn));
        $sql = "SELECT `" . bqSQL($idColumn) . "` FROM `" . _DB_PREFIX_ . "opartstat_" . bqSQL($tableName) . "` WHERE `" . bqSQL($idColumn) . "` IN ('" . implode("', '", $ids) . "')";

        $existingRecords = $db->executeS($sql);
        $existingIds = array_column($existingRecords, $idColumn);
        
        $insertData = [];
        $updateData = [];

        foreach ($datasToUpload as $data) {
            if (in_array($data[$idColumn], $existingIds)) {
                $updateData[] = $data;
            } else {
                $insertData[] = $data;
            }
        }

        if (count($insertData) > 0) {
            $columnsArray = array_keys($insertData[0]);
            $columns = "";
            foreach($columnsArray as $column) {
                $columns .= ($columns == "")?"`".bqSQL($column)."`":",`".bqSQL($column)."`";
            }

            $allValues = "";

            foreach($insertData as $datas) {
                $values = "";
                foreach($datas as $key => $data) {
                   $values .= ($values == "")?"'".pSQL($data)."'":",'".pSQL($data)."'";
                }

                if($allValues == "")
                    $allValues = "(" . $values . ")";
                else
                    $allValues = $allValues . ", (" . $values . ")";
            }
            $insertSql = "INSERT INTO `" . _DB_PREFIX_ . "opartstat_" . bqSQL($tableName) . "` (" . $columns . ") VALUES " . $allValues;

            if(!$db->execute($insertSql)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error while inserting datas']);
                die();
            }
        }

        if (count($updateData) > 0) {
            $updateFields = [];
            $ids = [];
        
            foreach ($updateData as $data) {
                $ids[] = pSQL($data[$idColumn]);
                foreach ($data as $key => $value) {
                    if ($key !== $idColumn) {
                        if (!isset($updateFields[$key])) {
                            $updateFields[$key] = $key." = CASE ";
                        }
                        $updateFields[$key] .= "WHEN `" . bqSQL($idColumn) . "` = '" . pSQL($data[$idColumn]) . "' THEN '" . pSQL($value) . "' ";
                    }
                }
            }
        
            foreach ($updateFields as $key => $value) {
                $updateFields[$key] .= "ELSE `" . pSQL($key) . "` END";
            }
        
            $updateSql = "UPDATE `" . _DB_PREFIX_ . "opartstat_" . pSQL($tableName) . "` SET ";
            $updateSql .= implode(', ', $updateFields);
            $updateSql .= " WHERE `" . bqSQL($idColumn) . "` IN ('" . implode("', '", $ids) . "')";
            
            if(!$db->execute($updateSql)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error while updating datas']);
                die();
            }
        }

        $maxGadsClicks = Configuration::get('OPARTSTAT_MAX_GADS_CLICKS');
        $sqlCount = "SELECT COUNT(gclid) FROM `" . _DB_PREFIX_ . "opartstat_googleAdsClicks`";
        $countResult = $db->getValue($sqlCount);

        if ($countResult > $maxGadsClicks) {
            $excess = $countResult - $maxGadsClicks;
            $sqlDelete = "DELETE FROM `" . _DB_PREFIX_ . "opartstat_googleAdsClicks` ORDER BY `createdAt` ASC LIMIT " . (int)$excess;
            if (!$db->execute($sqlDelete)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error while deleting excess Google Ads clicks']);
                die();
            }
        }

        echo json_encode(['success' => true, 'message' => 'Datas saved']);
        die();
    }
}