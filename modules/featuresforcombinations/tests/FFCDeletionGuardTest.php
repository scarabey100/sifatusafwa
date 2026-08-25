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

