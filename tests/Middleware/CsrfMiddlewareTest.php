<?php

namespace Testsuite\Middleware;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use Filehosting\Middleware\CsrfMiddleware;
use Filehosting\Helper\TokenGenerator;
use Testsuite\Utils\Factory;
use Testsuite\Utils\CookieUtils;

class CsrfMiddlewareTest extends TestCase
{
    protected static $callable;
    protected static $csrfMiddleware;

    public static function setUpBeforeClass()
    {
        self::$callable = function($req, $res) { return $res; };
        self::$csrfMiddleware = new CsrfMiddleware('PT3H', 65);
    }

    public function testSetToken()
    {
        $mw = self::$csrfMiddleware;
        $response = $mw(Factory::requestFactory(), new Response(), self::$callable);
        $this->assertRegExp('/^[\d\w]{65}$/', CookieUtils::getResponse($response, 'csrf_token')->getValue());
    }

    public function testTokenValid()
    {
        $mw = self::$csrfMiddleware;
        $csrfToken = '1234567890';
        $request = Factory::requestFactory()->withMethod('POST')->withParsedBody(['csrf_token' => $csrfToken]);
        $request = CookieUtils::setRequest($request, 'csrf_token', $csrfToken);
        $this->assertEquals(200, $mw($request, new Response(), self::$callable)->getStatusCode());
    }

    public function testTokenInvalid()
    {
        $mw = self::$csrfMiddleware;
        // Request is missing CSRF POST variable
        $request = CookieUtils::setRequest(Factory::requestFactory()->withMethod('POST'), 'csrf_token', '1234567890');
        $this->assertEquals(400, $mw($request, new Response(), self::$callable)->getStatusCode());
    }
}