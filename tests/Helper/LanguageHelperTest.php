<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Filehosting\Helper\LanguageHelper;
use Testsuite\Utils\Factory;

class LanguageHelperTest extends TestCase
{
    protected static $languageHelper;

    public static function setUpBeforeClass()
    {
        $request = Factory::requestFactory(['HTTP_ACCEPT_LANGUAGE' => "ru"]);
        $response = new Response();
        self::$languageHelper = new LanguageHelper($request);
    }

    public function testLanguageDisplayName()
    {
        $this->assertEquals("English",self::$languageHelper->getLanguageDisplayName('en'));
    }

    public function testAppLocale()
    {
        $this->assertEquals("en_EN", self::$languageHelper->getAppLocale());
    }

    public function testUserLocale()
    {
        $this->assertEquals("ru_RU", self::$languageHelper->getUserLocale());
    }

    public function testLangMsg()
    {
        $this->assertTrue(self::$languageHelper->canShowLangMsg());
    }
}