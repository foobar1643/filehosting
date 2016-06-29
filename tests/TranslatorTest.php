<?php

namespace Testsuite;

use PHPUnit\Framework\TestCase;
use Filehosting\Translator;

class TranslatorTest extends TestCase
{
    public function testTranslatePlural()
    {
        $translator = new Translator("en_US");
        $pluralString = "{0, plural, =0{No translations} one{translation} other{translations}}";
        $this->assertEquals("No translations", $translator->translatePlural($pluralString, 0));
        $this->assertEquals("translation", $translator->translatePlural($pluralString, 1));
        $this->assertEquals("translations", $translator->translatePlural($pluralString, 2));
    }
}