<?php

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__, 3) . '/config/config.inc.php';
require dirname(__DIR__) . '/athosfeed.php';

use AthosFeed\Export\ExportManager;
use AthosFeed\Validation\FeedValidator;

$action = isset($argv[1]) ? $argv[1] : 'help';
$options = array();
for ($index = 2; $index < count($argv); ++$index) {
    if (strpos($argv[$index], '--') !== 0) {
        fwrite(STDERR, 'ERROR: Unexpected argument: ' . $argv[$index] . "\n");
        exit(2);
    }
    $parts = explode('=', substr($argv[$index], 2), 2);
    $key = $parts[0];
    $value = isset($parts[1]) ? $parts[1] : (isset($argv[$index + 1]) && strpos($argv[$index + 1], '--') !== 0 ? $argv[++$index] : true);
    $options[$key] = $value;
}
$manager = new ExportManager(dirname(__DIR__));

try {
    if (!in_array($action, array('export', 'validate', 'status'), true)) {
        fwrite(STDERR, "Usage: php console.php export|validate|status --shop=ID [--language=ID] [--batch-size=N]\n");
        exit(2);
    }
    if (empty($options['shop']) || ($action !== 'status' && empty($options['language']))) {
        throw new InvalidArgumentException('--shop and --language are required for this command');
    }
    $shopId = (int) $options['shop'];
    $languageId = isset($options['language']) ? (int) $options['language'] : 0;
    if ($action === 'export') {
        $settings = array();
        if (isset($options['batch-size'])) {
            $settings['batch_size'] = (int) $options['batch-size'];
        }
        $result = $manager->export($shopId, $languageId, $settings);
    } elseif ($action === 'validate') {
        $minimum = isset($options['minimum-records']) ? (int) $options['minimum-records'] : 10;
        $result = (new FeedValidator())->validateFile($manager->feedPath($shopId, $languageId), $minimum);
    } else {
        $result = $manager->status($shopId);
    }
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'ERROR: ' . $exception->getMessage() . "\n");
    exit(1);
}
