<?php

namespace Testsuite;

use PHPUnit\Framework\TestCase;
use Filehosting\Config;

class ConfigTest extends TestCase
{
    protected $config;

    protected function setUp()
    {
        $this->config = new Config();
        $this->config->loadFromFile(__DIR__ . "/Assets/test-config.ini");
    }

    public function testGetValue()
    {
        $this->assertEquals(500, $this->config->getValue('app', 'sizeLimit'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWrongFileLoading()
    {
        $config = new Config();
        $this->config->loadFromFile(__DIR__ . "/Assets/test-wrong-config.ini");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailureGetValue()
    {
        $this->config->getValue('non-existing-key', 'failure');
    }

}