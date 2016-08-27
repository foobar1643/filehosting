<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Filehosting\Helper\Utils;
use Testsuite\Utils\TestUtils;

class UtilsTest extends TestCase
{
    public function testTokenGeneration()
    { // (is_string($string) && !empty(trim($string)));
        $this->assertFalse(TestUtils::isStringEmpty(Utils::generateToken(45)));
        $this->assertRegExp('/^.{70}$/u', Utils::generateToken(70));
        $this->assertNotEquals(Utils::generateToken(50), Utils::generateToken(50));
    }
}