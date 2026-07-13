<?php
/**
 * Script: Delete Products from Klevu (non-existent and inactive products)
 * 
 * This script uses cURL directly to call Klevu's deleteRecords API
 * following the official Klevu XML API documentation:
 * https://docs.klevu.com/indexing-apis/XML#PF_mJ
 * 
 * It finds and deletes from Klevu:
 * 1. Products that exist in mncklevu_product_record but don't exist in PrestaShop's product table
 * 2. Products that are inactive in PrestaShop (active = 0 or visibility not in 'both'/'catalog')
 * 
 * Usage:
 * - Via browser: http://yourdomain.com/modules/mncklevu/delete_with_curl.php
 * - Via CLI: php delete_with_curl.php
 */

// Load PrestaShop
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

use MncKlevu\PrestaShop\Adapter\Configuration;

// Get shop ID
$shopId = Context::getContext()->shop->id;

echo "=== Deleting Products from Klevu ===<br>";
echo "This will delete:<br>";
echo "  1. Products that exist in mncklevu_product_record but don't exist in PrestaShop's product table<br>";
echo "  2. Products that are inactive in PrestaShop<br><br>";

// Load the module
$module = Module::getInstanceByName('mncklevu');
if (!Validate::isLoadedObject($module)) {
    die("ERROR: Module 'mncklevu' not found or not installed.<br>");
}

$configuration = $module->getConfiguration();

// Get all languages
$languages = Language::getLanguages(false, false, true);
if (empty($languages)) {
    die("ERROR: No languages found.<br>");
}

$totalDeleted = 0;
$totalErrors = 0;
$totalProductsProcessed = 0;
$totalRecordsDeleted = 0;

foreach ($languages as $languageId) {
    echo "--- Language ID: {$languageId} ---<br>";
    
    // Check connection status
    if (!$module->getConnectionStatus($languageId)) {
        echo "  ⚠️  SKIPPED: No connection configured<br><br>";
        continue;
    }
    
    // Get REST API key
    $restAuthKey = $configuration->get(Configuration::KEY_REST_AUTH_KEY, $languageId);
    
    if (!$restAuthKey || trim($restAuthKey) === '') {
        echo "  ❌ ERROR: Missing or empty REST API key<br>";
        echo "  → Configure in Modules > mncklevu > Configuration<br>";
        echo "  → Language ID: {$languageId}<br><br>";
        continue;
    }
    
    // Trim whitespace from REST API key
    $restAuthKey = trim($restAuthKey);
    
    // Use the same URL as the Client class
    $baseUrl = 'http://rest.klevu.com/rest/service';
    
    echo "  REST API Key: " . substr($restAuthKey, 0, 20) . "... (length: " . strlen($restAuthKey) . ")<br>";
    echo "  Base URL: {$baseUrl}<br><br>";
    
    // Get all product records to delete:
    // 1. Products that exist in mncklevu_product_record but don't exist in product table
    // 2. Products that are inactive in PrestaShop
    $query = '
        SELECT pr.record_id, pr.id_product
        FROM ' . _DB_PREFIX_ . 'mncklevu_product_record pr
        LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = pr.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'product_shop ps ON ps.id_product = pr.id_product AND ps.id_shop = ' . (int)$shopId . '
        WHERE pr.id_lang = ' . (int)$languageId . '
        AND pr.id_shop = ' . (int)$shopId . '
        AND (
            p.id_product IS NULL
            OR
            (ps.id_product IS NOT NULL AND (ps.active = 0 OR ps.visibility NOT IN (\'both\', \'catalog\')))
        )
    ';
    
    $recordsToDelete = Db::getInstance()->executeS($query);
    
    if (empty($recordsToDelete) || !is_array($recordsToDelete)) {
        echo "  ℹ️  No products found to delete<br>";
        echo "  → All products in mncklevu_product_record exist in product table and are active<br><br>";
        continue;
    }
    
    // Extract record IDs
    $allRecordIds = array_map(function($row) {
        return $row['record_id'];
    }, $recordsToDelete);
    
    // Get unique product IDs for display
    $productIds = array_unique(array_map(function($row) {
        return (int)$row['id_product'];
    }, $recordsToDelete));
    
    echo "  Found " . count($productIds) . " product(s) to delete (non-existent or inactive in PrestaShop)<br>";
    echo "  Product IDs: " . implode(', ', $productIds) . "<br>";
    echo "  Total records to delete: " . count($allRecordIds) . "<br><br>";
    
    // Step 1: Get Session ID (once per language)
    echo "  Step 1: Getting Session ID...<br>";
    
    $sessionId = null;
    $startSessionUrl = $baseUrl . '/startSession';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $startSessionUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // Empty POST data for startSession
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $restAuthKey,
        'Content-Type: application/xml'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $sessionResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "  ❌ CURL ERROR: {$curlError}<br><br>";
        $totalErrors++;
        continue;
    }
    
    if ($httpCode !== 200) {
        echo "  ❌ ERROR: Failed to get session ID (HTTP {$httpCode})<br>";
        echo "  URL: {$startSessionUrl}<br>";
        echo "  Auth Key (first 20 chars): " . substr($restAuthKey, 0, 20) . "...<br>";
        echo "  Auth Key length: " . strlen($restAuthKey) . "<br>";
        echo "  Response:<br>";
        echo "    " . str_replace("\n", "<br>    ", $sessionResponse) . "<br>";
        echo "<br>";
        echo "  Debug info:<br>";
        echo "    - Check if REST API key is correct in module configuration<br>";
        echo "    - Verify the key matches the one in Klevu Merchant Center<br>";
        echo "    - Ensure the key doesn't have extra spaces or newlines<br><br>";
        $totalErrors++;
        continue;
    }
    
    // Parse session ID from XML response
    $sessionXml = @simplexml_load_string($sessionResponse);
    if ($sessionXml && isset($sessionXml->sessionId)) {
        $sessionId = (string)$sessionXml->sessionId;
        echo "  ✅ Session ID obtained: {$sessionId}<br><br>";
    } else {
        echo "  ❌ ERROR: Could not parse session ID from response<br>";
        echo "  Response: {$sessionResponse}<br>";
        echo "  Raw XML: " . print_r($sessionXml, true) . "<br><br>";
        $totalErrors++;
        continue;
    }
    
    // Step 2: Build XML for deletion (Klevu format)
    echo "  Step 2: Building delete XML (Klevu format)...<br>";
    
    $recordsXml = '';
    foreach ($allRecordIds as $recordId) {
        $recordsXml .= '<record><pairs>' .
            '<pair><key>id</key><value>' . htmlspecialchars($recordId, ENT_XML1) . '</value></pair>' .
            '</pairs></record>';
    }
    
    $deleteXml = '<?xml version="1.0" encoding="UTF-8"?>' .
        '<request>' .
        '<sessionId>' . htmlspecialchars($sessionId, ENT_XML1) . '</sessionId>' .
        '<records>' . $recordsXml . '</records>' .
        '</request>';
    
    echo "  XML prepared for " . count($allRecordIds) . " record(s)<br><br>";
    
    // Step 3: Send delete request using cURL
    echo "  Step 3: Sending delete request to Klevu...<br>";
    
    $deleteUrl = $baseUrl . '/deleteRecords';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $deleteUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $deleteXml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $restAuthKey,
        'Content-Type: application/xml'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    $deleteResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check response
    if ($curlError) {
        echo "  ❌ CURL ERROR: {$curlError}<br><br>";
        $totalErrors++;
        continue;
    }
    
    echo "  HTTP Status Code: {$httpCode}<br>";
    echo "  Response:<br>";
    echo "    {$deleteResponse}<br><br>";
    
    // Parse response
    $responseXml = @simplexml_load_string($deleteResponse);
    $success = false;
    
    if ($responseXml) {
        $status = isset($responseXml->status) ? (string)$responseXml->status : null;
        $message = isset($responseXml->msg) ? (string)$responseXml->msg : null;
        
        if (strtoupper($status) === 'SUCCESS') {
            $success = true;
            echo "  ✅ SUCCESS: {$message}<br>";
        } else {
            echo "  ❌ FAILED: Status = {$status}<br>";
            if ($message) {
                echo "  Message: {$message}<br>";
            }
        }
    } else {
        // Try to parse as plain text response
        if ($httpCode === 200 && stripos($deleteResponse, 'SUCCESS') !== false) {
            $success = true;
            echo "  ✅ SUCCESS: Deletion request accepted by Klevu<br>";
            echo "  Response: {$deleteResponse}<br>";
        } else {
            echo "  ⚠️  Could not parse XML response<br>";
            echo "  → HTTP {$httpCode} received<br>";
            if ($httpCode === 200) {
                echo "  → Response: {$deleteResponse}<br>";
            }
        }
    }
    
    if ($success) {
        $totalRecordsDeleted += count($allRecordIds);
        $totalDeleted++;
    } else {
        $totalErrors++;
    }
    
    $totalProductsProcessed += count($productIds);
    
    echo "<br>";
}

// Summary
echo "=== SUMMARY ===<br>";
echo "Total Products Processed: {$totalProductsProcessed}<br>";
echo "Total Records Deleted: {$totalRecordsDeleted}<br>";
echo "Languages Processed: {$totalDeleted}<br>";
echo "Errors: {$totalErrors}<br><br>";

if ($totalRecordsDeleted > 0 && $totalErrors == 0) {
    echo "✅ All products successfully deleted from Klevu using cURL!<br>";
} elseif ($totalRecordsDeleted > 0) {
    echo "⚠️  Products deleted with some errors<br>";
} else {
    echo "❌ No products were deleted<br>";
}

echo "<br>";
echo "To verify deletion:<br>";
echo "  SELECT COUNT(*) FROM ps_mncklevu_product_record pr<br>";
echo "  LEFT JOIN ps_product p ON p.id_product = pr.id_product<br>";
echo "  LEFT JOIN ps_product_shop ps ON ps.id_product = pr.id_product AND ps.id_shop = pr.id_shop<br>";
echo "  WHERE p.id_product IS NULL OR (ps.id_product IS NOT NULL AND (ps.active = 0 OR ps.visibility NOT IN ('both', 'catalog')));<br>";
echo "  → Should return 0 if all non-existent and inactive products were deleted<br>";
