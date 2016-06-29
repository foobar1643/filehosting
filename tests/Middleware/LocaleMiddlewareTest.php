<?php

namespace Testsuite\Middleware;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use Filehosting\Middleware\LocaleMiddleware;
use Filehosting\Helper\TokenGenerator;
use Filehosting\Helper\PathingHelper;
use Testsuite\Utils\Factory;
use Testsuite\Utils\CookieUtils;

class LocaleMiddlewareTest extends TestCase
{
    protected static $localeMiddleware;
    protected static $request;
    protected static $callable;

    public static function setUpBeforeClass()
    {
        self::$localeMiddleware = new LocaleMiddleware(new PathingHelper('/'));
        self::$request = CookieUtils::setRequest(Factory::requestFactory([], 'https://example.com/ru/'), 'langChangeShown', 3);
        self::$callable = function($req, $res) { return $res; };
    }

    public function testSetLocale()
    {
        $mw = self::$localeMiddleware;
        $mw(self::$request, new Response(), self::$callable);
        $this->assertEquals('ru_RU', \Locale::getDefault());
    }

    public function testLangMsgViewsIncrement()
    {
        $mw = self::$localeMiddleware;
        $msgCount = CookieUtils::getResponse($mw(self::$request, new Response(), self::$callable), 'langChangeShown')->getValue();
        $this->assertEquals(4, $msgCount);
    }
}