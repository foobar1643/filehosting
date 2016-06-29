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
    }

    public function testGetValue()
    {
        $this->assertEquals('127.0.0.1', $this->config->getValue('db', 'host'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailureGetValue()
    {
        $this->config->getValue('example', 'failure');
    }

}