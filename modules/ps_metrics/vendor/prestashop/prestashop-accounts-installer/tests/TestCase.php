<?php

namespace PrestaShop\PsAccountsInstaller\Tests;

use ps_metrics_module_v4_1_2\Faker\Generator;
class TestCase extends \ps_metrics_module_v4_1_2\PHPUnit\Framework\TestCase
{
    /**
     * @var Generator
     */
    public $faker;
    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->faker = \ps_metrics_module_v4_1_2\Faker\Factory::create();
    }
}
