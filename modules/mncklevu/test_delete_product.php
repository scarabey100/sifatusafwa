<?php
/**
 * Test Script: Delete Product ID 4588 from Klevu
 * 
 * Usage:
 * - Via browser: http://yourdomain.com/modules/mncklevu/test_delete_product.php?product_id=4588
 * - Via CLI: php test_delete_product.php 4588
 * 
 * SECURITY WARNING: 
 * - This script allows deletion of products from Klevu
 * - Add authentication/IP restriction before using in production!
 * - Delete this file after testing!
 */

// Load PrestaShop
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecord;
use MncKlevu\Synchronizer\Product\ProductSynchronizer;
use Language;
use PrestaShopLogger;

// Get product ID from parameter
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : (isset($argv[1]) ? (int)$argv[1] : 4588);

echo "=== Testing Deletion of Product ID: {$productId} ===\n\n";

// Load the module
$module = Module::getInstanceByName('mncklevu');
if (!Validate::isLoadedObject($module)) {
    die("ERROR: Module 'mncklevu' not found or not installed.\n");
}

$synchronizer = $module->getProductSynchronizer();
if (!$synchronizer) {
    die("ERROR: Could not get ProductSynchronizer instance.\n");
}

// Get all languages
$languages = Language::getLanguages(false, false, true);
if (empty($languages)) {
    die("ERROR: No languages found.\n");
}

echo "Found " . count($languages) . " language(s)\n\n";

$overallSuccess = true;

foreach ($languages as $languageId) {
    echo "--- Processing Language ID: {$languageId} ---\n";
    
    // Check connection status
    if (!$module->getConnectionStatus($languageId)) {
        echo "  ⚠️  SKIPPED: No connection configured for language {$languageId}\n\n";
        continue;
    }
    
    // Get record IDs from database
    $recordIds = ProductRecord::getRecordsIdsByProductId($productId, $languageId);
    
    if (empty($recordIds)) {
        echo "  ℹ️  INFO: No records found in database for product {$productId} (language {$languageId})\n";
        echo "  → Product may not be synced to Klevu for this language\n\n";
        continue;
    }
    
    echo "  Found " . count($recordIds) . " record(s) in database:\n";
    foreach ($recordIds as $recordId) {
        echo "    - Record ID: {$recordId}\n";
    }
    echo "\n";
    
    // Display record IDs that will be deleted
    echo "  Record IDs that will be deleted:\n";
    foreach ($recordIds as $recordId) {
        echo "    - {$recordId}\n";
    }
    echo "\n";
    
    // Try to delete
    echo "  Attempting deletion...\n";
    
    try {
        $result = $synchronizer->deleteProduct($productId, $languageId);
        
        if ($result) {
            echo "  ✅ SUCCESS: Product deleted from Klevu for language {$languageId}\n";
            
            // Verify deletion from database
            $remainingRecords = ProductRecord::getRecordsIdsByProductId($productId, $languageId);
            if (empty($remainingRecords)) {
                echo "  ✅ SUCCESS: Records also removed from local database\n";
            } else {
                echo "  ⚠️  WARNING: Records still exist in local database:\n";
                foreach ($remainingRecords as $recordId) {
                    echo "    - {$recordId}\n";
                }
            }
        } else {
            echo "  ❌ FAILED: Deletion returned false\n";
            echo "  → Check PrestaShop logs for errors (ProductSynchronizer::validateResponse)\n";
            $overallSuccess = false;
        }
    } catch (Exception $e) {
        echo "  ❌ ERROR: Exception occurred: " . $e->getMessage() . "\n";
        echo "  → File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        $overallSuccess = false;
    }
    
    echo "\n";
}

// Summary
echo "=== SUMMARY ===\n";
if ($overallSuccess) {
    echo "✅ Test completed successfully\n";
} else {
    echo "❌ Test completed with errors - check logs above\n";
}

echo "\n";
echo "To check PrestaShop logs:\n";
echo "  - Go to Advanced Parameters > Logs\n";
echo "  - Look for entries from 'ProductSynchronizer::validateResponse'\n";
echo "\n";
echo "To verify deletion in Klevu:\n";
echo "  - Check Klevu Merchant Center\n";
echo "  - Search for product ID {$productId}\n";
echo "  - It should not appear in search results\n";

