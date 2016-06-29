<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Filehosting\Helper\TokenGenerator;

class TokenGeneratorTest extends TestCase
{
    public function testTokenGeneration()
    {
        $tokenGenerator = new TokenGenerator();
        $this->assertRegExp('/^[\d\w]{80}$/', $tokenGenerator->generateToken(80));
    }
}