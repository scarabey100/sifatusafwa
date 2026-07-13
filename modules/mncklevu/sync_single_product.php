<?php
/**
 * Script: Add Product Directly to mncklevu_product_record Table
 * 
 * This script gets a product from PrestaShop and adds it directly
 * to the mncklevu_product_record table without syncing to Klevu
 * 
 * Usage:
 * - Via browser: http://yourdomain.com/modules/mncklevu/add_product_to_table.php?product_id=4588
 * - Via CLI: php add_product_to_table.php 4588
 */

// Load PrestaShop
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecord;
use MncKlevu\Synchronizer\Product\ProductSynchronizer;
use MncKlevu\Synchronizer\Settings;

// Get product ID from parameter
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : (isset($argv[1]) ? (int)$argv[1] : 4588);

echo "=== Adding Product ID: {$productId} to mncklevu_product_record ===\n\n";

// Load the module
$module = Module::getInstanceByName('mncklevu');
if (!Validate::isLoadedObject($module)) {
    die("ERROR: Module 'mncklevu' not found or not installed.\n");
}

$synchronizer = $module->getProductSynchronizer();
if (!$synchronizer) {
    die("ERROR: Could not get ProductSynchronizer instance.\n");
}

// Get product from PrestaShop
$product = new Product((int)$productId);
if (!Validate::isLoadedObject($product)) {
    die("ERROR: Product {$productId} not found in PrestaShop.\n");
}

$contextLangId = Context::getContext()->language->id;
$productName = isset($product->name[$contextLangId]) ? $product->name[$contextLangId] : (isset($product->name[1]) ? $product->name[1] : 'Unknown');
echo "Product: {$productName}\n";

// Get shop ID
$shopId = Context::getContext()->shop->id;
echo "Shop ID: {$shopId}\n\n";

// Get all languages
$languages = Language::getLanguages(false, false, true);
if (empty($languages)) {
    die("ERROR: No languages found.\n");
}

echo "Found " . count($languages) . " language(s)\n\n";

$totalAdded = 0;
$totalSkipped = 0;
$totalErrors = 0;

foreach ($languages as $languageId) {
    echo "--- Language ID: {$languageId} ---\n";
    
    // Check if records already exist
    $existingRecords = ProductRecord::getRecordsIdsByProductId($productId, $languageId);
    if (!empty($existingRecords)) {
        echo "  ℹ️  Records already exist (" . count($existingRecords) . "):\n";
        foreach ($existingRecords as $recordId) {
            echo "    - {$recordId}\n";
        }
        echo "  → Skipping (already exists)\n\n";
        $totalSkipped++;
        continue;
    }
    
    // Get product attributes (variants)
    $productAttributes = Product::getProductAttributesIds($productId, true);
    
    if (empty($productAttributes)) {
        // Product without attributes - create one record
        echo "  Product has no attributes\n";
        
        // Generate record ID (format: p{productId})
        $recordId = Settings::ID_PREFIX_PRODUCT . $productId;
        
        // Create ProductRecord
        $productRecord = new ProductRecord();
        $productRecord->record_id = $recordId;
        $productRecord->id_product = $productId;
        $productRecord->id_product_attribute = 0;
        $productRecord->id_lang = $languageId;
        $productRecord->id_shop = $shopId;
        $productRecord->valid = 1;
        
        if ($productRecord->save()) {
            echo "  ✅ Record created: {$recordId}\n";
            $totalAdded++;
        } else {
            echo "  ❌ Failed to save record: {$recordId}\n";
            $totalErrors++;
        }
    } else {
        // Product with attributes - create records for each variant
        echo "  Product has " . count($productAttributes) . " attribute(s)\n";
        
        foreach ($productAttributes as $attribute) {
            $productAttributeId = (int)$attribute['id_product_attribute'];
            
            // Generate record ID (format: v{productAttributeId})
            $recordId = Settings::ID_PREFIX_PRODUCT_VARIANT . $productAttributeId;
            
            // Check if record already exists
            $existing = Db::getInstance()->getValue('
                SELECT id_mncklevu_product_record 
                FROM '._DB_PREFIX_.'mncklevu_product_record 
                WHERE record_id = \''.pSQL($recordId).'\' 
                AND id_lang = '.(int)$languageId.'
                AND id_shop = '.(int)$shopId.'
            ');
            
            if ($existing) {
                echo "    ⚠️  Record {$recordId} already exists, skipping\n";
                continue;
            }
            
            // Create ProductRecord
            $productRecord = new ProductRecord();
            $productRecord->record_id = $recordId;
            $productRecord->id_product = $productId;
            $productRecord->id_product_attribute = $productAttributeId;
            $productRecord->id_lang = $languageId;
            $productRecord->id_shop = $shopId;
            $productRecord->valid = 1;
            
            if ($productRecord->save()) {
                echo "    ✅ Record created: {$recordId} (Attribute ID: {$productAttributeId})\n";
                $totalAdded++;
            } else {
                echo "    ❌ Failed to save record: {$recordId}\n";
                $totalErrors++;
            }
        }
    }
    
    echo "\n";
}

// Summary
echo "=== SUMMARY ===\n";
echo "Product ID: {$productId}\n";
echo "Records Added: {$totalAdded}\n";
echo "Records Skipped: {$totalSkipped}\n";
echo "Errors: {$totalErrors}\n\n";

if ($totalAdded > 0) {
    echo "✅ Records successfully added to mncklevu_product_record table!\n";
    echo "\nTo verify:\n";
    echo "  SELECT * FROM ps_mncklevu_product_record WHERE id_product = {$productId};\n";
} elseif ($totalSkipped > 0) {
    echo "ℹ️  Records already exist in table\n";
} else {
    echo "❌ No records were added\n";
}

