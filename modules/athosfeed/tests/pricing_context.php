<?php

class Shop { public $id_shop_group = 7; public function __construct($id) {} }
class Language { public function __construct($id) {} }
class Currency { public function __construct($id) {} }
class Country { public function __construct($id, $languageId) {} }
class Customer { public $id; public $id_default_group; }
class Cart {
    public $id;
    public $id_shop;
    public $id_shop_group;
    public $id_lang;
    public $id_currency;
    public $id_customer;
    public $id_guest;
    public $id_address_delivery;
    public $id_address_invoice;
    public $secure_key;
    public $deleted = false;
    public function add() { $this->id = 99; return true; }
    public function delete() { $this->deleted = true; return true; }
}
class Context {
    public $shop;
    public $language;
    public $currency;
    public $country;
    public $customer;
    public $cart;
    private static $instance;
    public static function getContext() { return self::$instance ?: self::$instance = new self(); }
}
class Configuration {
    public static function get($key) {
        $values = array('PS_CURRENCY_DEFAULT'=>2, 'PS_COUNTRY_DEFAULT'=>3, 'PS_UNIDENTIFIED_GROUP'=>4);
        return isset($values[$key]) ? $values[$key] : false;
    }
}
class Product {
    public static $arguments;
    public static function getPriceStatic() { self::$arguments = func_get_args(); return 12.5; }
}

require dirname(__DIR__) . '/classes/autoload.php';

$manager = new AthosFeed\Export\ExportManager(dirname(__DIR__));
$configureContext = new ReflectionMethod($manager, 'configureContext');
$configureContext->setAccessible(true);
$cart = $configureContext->invoke($manager, 1, 1, array('currency_id'=>2, 'country_id'=>3, 'group_id'=>4));
$context = Context::getContext();
if (!$context->cart instanceof Cart || $context->cart->id !== 99 || $context->cart->id_shop !== 1
    || $context->cart->id_currency !== 2 || $context->cart->id_customer !== 0) {
    throw new RuntimeException('Transient anonymous pricing cart was not initialized');
}

$provider = new AthosFeed\Provider\PrestaShopProductDataProvider($context);
$price = new ReflectionMethod($provider, 'price');
$price->setAccessible(true);
if ($price->invoke($provider, 42, 5, true) !== 12.5) {
    throw new RuntimeException('Unexpected calculated price');
}
$arguments = Product::$arguments;
if ($arguments[9] !== 0 || $arguments[10] !== 99 || $arguments[11] !== 0
    || $arguments[15] !== $context || $arguments[16] !== false) {
    throw new RuntimeException('Price calculation did not receive the deterministic CLI context');
}

$removePricingCart = new ReflectionMethod($manager, 'removePricingCart');
$removePricingCart->setAccessible(true);
$logger = new AthosFeed\Logger\FeedLogger();
$removePricingCart->invoke($manager, $cart, $logger);
if (!$cart->deleted) {
    throw new RuntimeException('Temporary pricing cart was not removed');
}

echo "OK: temporary cart lifecycle and explicit Product::getPriceStatic context\n";
