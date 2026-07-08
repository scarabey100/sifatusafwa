<?php
/**
 * Script: Add All Missing Products to mncklevu_product_record Table
 * 
 * This script performs two main operations:
 * 1. CLEANUP: Removes orphaned records (products in mncklevu_product_record table
 *    that no longer exist in the product table) - deletes all variants for those products.
 * 2. ADD MISSING: Finds ALL products (active and inactive) that don't exist in the 
 *    mncklevu_product_record table and adds them directly to the table.
 * 
 * This only adds/removes records to/from the local database table - it does NOT sync to Klevu.
 * To sync to Klevu, run a full synchronization after adding records.
 * 
 * Processes ALL products in one shot - no pagination.
 * 
 * Usage:
 * - Via browser: http://yourdomain.com/modules/mncklevu/sync_all_missing_products.php
 * - Via CLI: php sync_all_missing_products.php
 */

// Load PrestaShop
require_once(dirname(__FILE__) . '/../../config/config.inc.php');

use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecord;
use MncKlevu\Synchronizer\Settings;
use Language;
use Db;
use DbQuery;
use Product;
use Validate;
use Context;
use Shop;
use Module;

echo "=== Cleanup and Add Missing Products to mncklevu_product_record Table ===\n";
echo "This script will:\n";
echo "  1. Remove orphaned records (products in table but not in product table)\n";
echo "  2. Add missing products to the table\n\n";

// Load the module
$module = Module::getInstanceByName('mncklevu');
if (!Validate::isLoadedObject($module)) {
    die("ERROR: Module 'mncklevu' not found or not installed.\n");
}

// Note: We don't need the synchronizer since we're only adding records to the table

// Get shop ID
$shopId = Context::getContext()->shop->id;
echo "Shop ID: {$shopId}\n";

// Get all languages
$languages = Language::getLanguages(false, false, true);
if (empty($languages)) {
    die("ERROR: No languages found.\n");
}

echo "Found " . count($languages) . " language(s)\n\n";

// ============================================================================
// STEP 1: Clean up orphaned records (products in mncklevu_product_record but not in product table)
// ============================================================================

echo "=== STEP 1: Cleaning up orphaned records ===\n";
echo "Finding products in mncklevu_product_record that don't exist in product table...\n\n";

// Get all distinct product IDs from mncklevu_product_record
$orphanedQuery = '
    SELECT DISTINCT pr.id_product
    FROM `' . _DB_PREFIX_ . 'mncklevu_product_record` pr
    LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = pr.id_product
    WHERE p.id_product IS NULL
    AND pr.id_shop = ' . (int)$shopId . '
    ORDER BY pr.id_product ASC
';

$orphanedProducts = Db::getInstance()->executeS($orphanedQuery);
if (!is_array($orphanedProducts)) {
    echo "⚠️  Could not check for orphaned products.\n\n";
} else {
    $orphanedCount = count($orphanedProducts);
    
    if ($orphanedCount > 0) {
        echo "Found {$orphanedCount} orphaned product(s) to clean up:\n";
        
        $deletedRecords = 0;
        $deletedProducts = [];
        
        foreach ($orphanedProducts as $orphanedRow) {
            $orphanedProductId = (int)$orphanedRow['id_product'];
            
            // Count how many records will be deleted for this product
            $recordsToDelete = Db::getInstance()->getValue('
                SELECT COUNT(*) 
                FROM `' . _DB_PREFIX_ . 'mncklevu_product_record`
                WHERE id_product = ' . (int)$orphanedProductId . '
                AND id_shop = ' . (int)$shopId . '
            ');
            
            echo "  Product ID {$orphanedProductId}: Deleting {$recordsToDelete} record(s) (all variants)...\n";
            
            // Delete all records for this product (including all variants)
            $deleteResult = Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'mncklevu_product_record`
                WHERE id_product = ' . (int)$orphanedProductId . '
                AND id_shop = ' . (int)$shopId . '
            ');
            
            if ($deleteResult) {
                echo "    ✅ Deleted {$recordsToDelete} record(s) for product ID {$orphanedProductId}\n";
                $deletedRecords += $recordsToDelete;
                $deletedProducts[] = $orphanedProductId;
            } else {
                $sqlError = Db::getInstance()->getMsgError();
                echo "    ❌ Failed to delete records for product ID {$orphanedProductId}\n";
                echo "    SQL Error: {$sqlError}\n";
            }
        }
        
        echo "\n";
        echo "✅ Cleanup Summary:\n";
        echo "   - Orphaned products found: {$orphanedCount}\n";
        echo "   - Total records deleted: {$deletedRecords}\n";
        if (!empty($deletedProducts)) {
            echo "   - Deleted product IDs: " . implode(', ', $deletedProducts) . "\n";
        }
        echo "\n";
    } else {
        echo "✅ No orphaned records found. All products in mncklevu_product_record exist in product table.\n\n";
    }
}

// ============================================================================
// STEP 2: Add missing products to mncklevu_product_record table
// ============================================================================
echo "=== STEP 2: Adding missing products ===\n\n";

// Get all products from PrestaShop that don't exist in mncklevu_product_record table
// Use LEFT JOIN to exclude products that already have records
// Process ALL products in one shot - no limit
$query = '
    SELECT DISTINCT p.id_product
    FROM `' . _DB_PREFIX_ . 'product` p
    LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.id_product = p.id_product AND ps.id_shop = ' . (int)$shopId . '
    LEFT JOIN `' . _DB_PREFIX_ . 'mncklevu_product_record` pr ON pr.id_product = p.id_product AND pr.id_shop = ' . (int)$shopId . '
    WHERE pr.id_mncklevu_product_record IS NULL and ps.active = 1 and p.visibility = "both"
    ORDER BY p.id_product ASC
';

$allProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
if (!is_array($allProducts)) {
    die("ERROR: Could not fetch products from database.\n");
}

$totalProducts = count($allProducts);
echo "Found {$totalProducts} product(s) to check\n\n";

if ($totalProducts === 0) {
    echo "No products found to sync.\n";
    exit;
}

$totalSynced = 0;
$totalSkipped = 0;
$totalErrors = 0;
$processed = 0;
$failedProducts = []; // Track products that failed to add records
 

foreach ($allProducts as $productRow) {
    $productId = (int)$productRow['id_product'];
   
    $processed++;
    
    echo "[{$processed}/{$totalProducts}] Processing Product ID: {$productId}\n";
    
    // Check if product exists in PrestaShop
    $product = new Product($productId);
    if (!Validate::isLoadedObject($product)) {
        echo "  ⚠️  Product not found in PrestaShop, skipping\n";
        echo "    → This might mean the product was deleted or doesn't exist\n\n";
        $totalSkipped++;
        continue;
    }

    // Check if product exists in current shop
    $productInShop = Db::getInstance()->getValue('
        SELECT COUNT(*) 
        FROM '._DB_PREFIX_.'product_shop 
        WHERE id_product = '.(int)$productId.'
        AND id_shop = '.(int)$shopId.'
    ');
    
    if (!$productInShop) {
        echo "  ⚠️  Product exists but not associated with shop {$shopId}\n";
        echo "    → Product ID: {$productId} exists in database but not in this shop\n";
        echo "    → Skipping (product not available in current shop context)\n\n";
        $totalSkipped++;
        continue;
    }
    
    // Get product name for display
    $contextLangId = Context::getContext()->language->id;
    $productName = isset($product->name[$contextLangId]) ? $product->name[$contextLangId] : (isset($product->name[1]) ? $product->name[1] : 'Unknown');
    echo "  Product: {$productName}\n";

    $productSynced = false;
    $productErrors = 0;
    $productSkippedAllLanguages = true;
    
    // Process each language
    foreach ($languages as $languageId) {
        // Check connection status
        if (!$module->getConnectionStatus($languageId)) {
            echo "  Language {$languageId}: ⚠️  SKIPPED (No connection configured)\n";
            continue;
        }
        
        // Check if product already exists in ProductRecord table
        $existingRecords = ProductRecord::getRecordsIdsByProductId($productId, $languageId);
        
        if (!empty($existingRecords)) {
            echo "  Language {$languageId}: ℹ️  Already exists (" . count($existingRecords) . " record(s)): " . implode(', ', $existingRecords) . "\n";
            // Verify records actually exist in database
            $verifyCount = Db::getInstance()->getValue('
                SELECT COUNT(*) 
                FROM '._DB_PREFIX_.'mncklevu_product_record 
                WHERE id_product = '.(int)$productId.'
                AND id_lang = '.(int)$languageId.'
                AND id_shop = '.(int)$shopId.'
            ');
            echo "    → Verified: {$verifyCount} record(s) in database\n";
            continue;
        }
        
        // Product doesn't exist - mark that we're processing this product
        $productSkippedAllLanguages = false;
        
        // Product doesn't exist - add records to table first, then sync to Klevu
        echo "  Language {$languageId}: Adding missing records...\n";
        
        // Step 1: Add records directly to table (ensures they exist)
        $productAttributes = Product::getProductAttributesIds($productId, true);
        $recordsAdded = 0;
        $recordIdsAdded = [];
        
        if (empty($productAttributes)) {
            // Product without attributes
            $recordId = Settings::ID_PREFIX_PRODUCT . $productId;
            
            // Check if record already exists (double check)
            $existing = Db::getInstance()->getValue('
                SELECT id_mncklevu_product_record 
                FROM '._DB_PREFIX_.'mncklevu_product_record 
                WHERE record_id = \''.pSQL($recordId).'\' 
                AND id_lang = '.(int)$languageId.'
                AND id_shop = '.(int)$shopId.'
            ');
            
            if (!$existing) {
                // Try using ObjectModel save first
                $productRecord = new ProductRecord();
                $productRecord->record_id = $recordId;
                $productRecord->id_product = $productId;
                $productRecord->id_product_attribute = 0;
                $productRecord->id_lang = $languageId;
                $productRecord->id_shop = $shopId;
                $productRecord->valid = 1;
                
                $saved = false;
                try {
                    $saved = $productRecord->save();
                } catch (Exception $e) {
                    echo "    ⚠️  Save exception: " . $e->getMessage() . "\n";
                }
                
                if ($saved) {
                    echo "    ✅ Record added to table: {$recordId}\n";
                    $recordsAdded++;
                    $recordIdsAdded[] = $recordId;
                } else {
                    // Fallback: Insert directly using SQL
                    echo "    ⚠️  ObjectModel save failed, trying direct SQL insert...\n";
                    try {
                        $sqlInserted = Db::getInstance()->execute('
                            INSERT INTO `'._DB_PREFIX_.'mncklevu_product_record`
                            (`record_id`, `id_product`, `id_product_attribute`, `id_lang`, `id_shop`, `valid`)
                            VALUES (
                                \''.pSQL($recordId).'\',
                                '.(int)$productId.',
                                0,
                                '.(int)$languageId.',
                                '.(int)$shopId.',
                                1
                            )
                            ON DUPLICATE KEY UPDATE `valid` = 1
                        ');
                        
                        if ($sqlInserted) {
                            echo "    ✅ Record added via SQL: {$recordId}\n";
                            $recordsAdded++;
                            $recordIdsAdded[] = $recordId;
                        } else {
                            $sqlError = Db::getInstance()->getMsgError();
                            echo "    ❌ SQL insert failed: {$recordId}\n";
                            echo "    SQL Error: {$sqlError}\n";
                        }
                    } catch (Exception $e) {
                        echo "    ❌ SQL insert exception: " . $e->getMessage() . "\n";
                    }
                }
            } else {
                echo "    ℹ️  Record already exists: {$recordId}\n";
            }
        } else {
            // Product with attributes
            echo "    Product has " . count($productAttributes) . " attribute(s)\n";
            
            foreach ($productAttributes as $attribute) {
                $productAttributeId = (int)$attribute['id_product_attribute'];
                $recordId = Settings::ID_PREFIX_PRODUCT_VARIANT . $productAttributeId;
                
                // Check if record already exists
                $existing = Db::getInstance()->getValue('
                    SELECT id_mncklevu_product_record 
                    FROM '._DB_PREFIX_.'mncklevu_product_record 
                    WHERE record_id = \''.pSQL($recordId).'\' 
                    AND id_lang = '.(int)$languageId.'
                    AND id_shop = '.(int)$shopId.'
                ');
                
                if (!$existing) {
                    // Try using ObjectModel save first
                    $productRecord = new ProductRecord();
                    $productRecord->record_id = $recordId;
                    $productRecord->id_product = $productId;
                    $productRecord->id_product_attribute = $productAttributeId;
                    $productRecord->id_lang = $languageId;
                    $productRecord->id_shop = $shopId;
                    $productRecord->valid = 1;
                    
                    $saved = false;
                    try {
                        $saved = $productRecord->save();
                    } catch (Exception $e) {
                        echo "    ⚠️  Save exception for {$recordId}: " . $e->getMessage() . "\n";
                    }
                    
                    if ($saved) {
                        echo "    ✅ Record added: {$recordId} (Attribute ID: {$productAttributeId})\n";
                        $recordsAdded++;
                        $recordIdsAdded[] = $recordId;
                    } else {
                        // Fallback: Insert directly using SQL
                        try {
                            $sqlInserted = Db::getInstance()->execute('
                                INSERT INTO `'._DB_PREFIX_.'mncklevu_product_record`
                                (`record_id`, `id_product`, `id_product_attribute`, `id_lang`, `id_shop`, `valid`)
                                VALUES (
                                    \''.pSQL($recordId).'\',
                                    '.(int)$productId.',
                                    '.(int)$productAttributeId.',
                                    '.(int)$languageId.',
                                    '.(int)$shopId.',
                                    1
                                )
                                ON DUPLICATE KEY UPDATE `valid` = 1
                            ');
                            
                            if ($sqlInserted) {
                                echo "    ✅ Record added via SQL: {$recordId} (Attribute ID: {$productAttributeId})\n";
                                $recordsAdded++;
                                $recordIdsAdded[] = $recordId;
                            } else {
                                $sqlError = Db::getInstance()->getMsgError();
                                echo "    ❌ SQL insert failed: {$recordId}\n";
                                echo "    SQL Error: {$sqlError}\n";
                            }
                        } catch (Exception $e) {
                            echo "    ❌ SQL insert exception: " . $e->getMessage() . "\n";
                        }
                    }
                } else {
                    echo "    ℹ️  Record already exists: {$recordId}\n";
                }
            }
        }
        
        if ($recordsAdded == 0) {
            echo "  Language {$languageId}: ℹ️  No new records to add (all already exist)\n";
            continue;
        }
        
        echo "    Added {$recordsAdded} record(s) to table\n";
        
        // Verify records were actually inserted
        $verifyRecords = ProductRecord::getRecordsIdsByProductId($productId, $languageId);
        if (empty($verifyRecords) && $recordsAdded > 0) {
            echo "  ⚠️  WARNING: Records were not found after insertion!\n";
            echo "    → This might indicate a database issue or constraint violation\n";
            echo "    → Product ID: {$productId}, Language: {$languageId}, Shop: {$shopId}\n";
            if (!isset($failedProducts[$productId])) {
                $failedProducts[$productId] = [];
            }
            $failedProducts[$productId][] = "Language {$languageId}: Inserted {$recordsAdded} but verification failed";
        } else if (!empty($verifyRecords)) {
            echo "    ✅ Verified: " . count($verifyRecords) . " record(s) now exist in database\n";
            $productSynced = true;
        }
    }
    
    if ($productSynced) {
        $totalSynced++;
    }
    
    if ($productErrors > 0) {
        $totalErrors++;
    }
    
    // If product was skipped for all languages (already exists everywhere), count as skipped
    if ($productSkippedAllLanguages) {
        echo "  ⚠️  Product skipped: Already exists in all configured languages\n";
        $totalSkipped++;
    } elseif (!$productSynced && $productErrors == 0) {
        echo "  ⚠️  Product skipped: No records added and no errors\n";
        $totalSkipped++;
    }
    
    echo "\n";
    
  
}

// Summary
echo "=== SUMMARY ===\n";
echo "Total Products Processed: {$processed}\n";
echo "Products Added: {$totalSynced}\n";
echo "Products Skipped (already exist): {$totalSkipped}\n";
echo "Products with Errors: {$totalErrors}\n\n";

if ($totalSynced > 0) {
    echo "✅ Successfully added records for {$totalSynced} product(s) to mncklevu_product_record table!\n";
    echo "   Note: Records are in the database but NOT yet synced to Klevu.\n";
    echo "   Run a full synchronization to sync these products to Klevu.\n";
} elseif ($totalSkipped > 0) {
    echo "ℹ️  All products already have records in the table\n";
} else {
    echo "❌ No records were added\n";
}

if ($totalErrors > 0) {
    echo "\n⚠️  {$totalErrors} product(s) had errors during record insertion\n";
    echo "   Check the output above for details\n";
}

if (!empty($failedProducts)) {
    echo "\n⚠️  Products that failed verification after insertion:\n";
    foreach ($failedProducts as $failedProductId => $reasons) {
        echo "   Product ID {$failedProductId}:\n";
        foreach ($reasons as $reason) {
            echo "     - {$reason}\n";
        }
    }
    echo "\n   These products may need manual intervention.\n";
}

echo "\n";
echo "To verify records were added:\n";
echo "  SELECT COUNT(*) FROM ps_mncklevu_product_record;\n";
echo "  → Should show all products with records\n";
echo "\n";
echo "To check specific products:\n";
echo "  SELECT * FROM ps_mncklevu_product_record WHERE id_product IN (19, 24);\n";
echo "  → Replace 19, 24 with product IDs you want to check\n";
echo "\n";
echo "Note: This script cleans up orphaned records and adds missing records to the database table.\n";
echo "      To sync these products to Klevu, run a full synchronization.\n";