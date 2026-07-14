<?php
/**
 * One-time maintenance script for PrestaShop EventBus incremental sync schema.
 *
 * Where to place it:
 * - Put this file in the PrestaShop root directory, next to config/, init.php, and index.php.
 *
 * How to run it:
 * - From SSH/CLI: php fix_eventbus_action_column.php
 *
 * What it does:
 * - Checks that the ps_eventbus incremental sync table exists.
 * - Checks whether the optional `action` column exists.
 * - Adds `action` as VARCHAR(20) NULL DEFAULT NULL when it is missing.
 *
 * Important:
 * - Make a database backup before running it.
 * - Delete this file after a successful run.
 */

/*if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This maintenance script must be run from CLI. Example: php fix_eventbus_action_column.php\n");
}*/

$rootDir = __DIR__.'/../..';

$configFile = $rootDir . '/config/config.inc.php';
$initFile = $rootDir . '/init.php';

if (!file_exists($configFile) || !file_exists($initFile)) {
    exit("PrestaShop was not found. Put this file in the PrestaShop root directory.\n");
}

require_once $configFile;
require_once $initFile;

$table = _DB_PREFIX_ . 'eventbus_incremental_sync';
$db = Db::getInstance();
$tableExists = $db->executeS('SHOW TABLES LIKE "' . pSQL($table) . '"');

if (empty($tableExists)) {
    exit("Table {$table} does not exist. Nothing was changed.\n");
}

$actionColumn = $db->executeS('SHOW COLUMNS FROM `' . bqSQL($table) . '` LIKE "action"');

if (!empty($actionColumn)) {
    exit("Column action already exists in {$table}. Nothing was changed.\n");
}

$success = $db->execute('ALTER TABLE `' . bqSQL($table) . '` ADD `action` VARCHAR(20) NULL DEFAULT NULL AFTER `created_at`');

if (!$success) {
    exit("Failed to add column action to {$table}. Check database permissions and logs.\n");
}

echo "Column action was added successfully to {$table}. Delete this script now.\n";