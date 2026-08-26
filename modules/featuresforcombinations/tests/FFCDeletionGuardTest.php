<?php

define('_PS_VERSION_', 'test');
define('_DB_PREFIX_', 'sf_');

class Product
{
    public $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }
}

class Db
{
    public static $instance;
    public $deletes = [];

    public static function getInstance()
    {
        return self::$instance;
    }

    public function delete($table, $where)
    {
        $this->deletes[] = [$table, $where];

        return true;
    }
}

require dirname(__DIR__) . '/classes/FFC.php';

function assertSame($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function runDeletion()
{
    Db::$instance = new Db();
    $ffc = new FFC(307);
    $ffc->deleteCombinationsFeatures(12);

    return Db::$instance;
}

$deletion = runDeletion();
assertSame(
    [['featuresforcombinations', '`id_product` = 307 AND `id_product_attribute` = 12']],
    $deletion->deletes,
    'FFC cleanup must remove only its own association.'
);

$moduleSource = file_get_contents(dirname(__DIR__) . '/featuresforcombinations.php');
$guardPosition = strpos($moduleSource, 'if (!array_key_exists($id_product_attribute, $ffc_form))');
$deletePosition = strpos($moduleSource, '$ffc->deleteCombinationsFeatures($id_product_attribute);');

