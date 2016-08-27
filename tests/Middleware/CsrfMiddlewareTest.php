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
    protected $middleware;
    protected $callable;
    protected $csrfCookie;

    public function setUp()
    {
        $this->middleware = new CsrfMiddleware('PT3H', 65);
        $this->callable = function($req, $res) { return $res; };
        $response = $this->middleware->__invoke(Factory::requestFactory(), new Response(), $this->callable);
        $this->csrfCookie = CookieUtils::getResponse($response, 'csrf_token');
    }

    public function testCsrfToken()
    {
        $this->assertNotEmpty($this->csrfCookie->getValue());
        $this->assertRegExp('/^.{45,}$/u', $this->csrfCookie->getValue());
    }

    public function testTokenValid()
    {
        $request = Factory::requestFactory()->withMethod('POST')->withParsedBody(['csrf_token' => $this->csrfCookie->getValue()]);
        $request = CookieUtils::setRequest($request, $this->csrfCookie->getName(), $this->csrfCookie->getValue());
        $response = $this->middleware->__invoke($request, new Response(), $this->callable);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTokenInvalid()
    {
        // Request is missing CSRF POST variable
        $request = Factory::requestFactory()->withMethod('POST');
        $request = CookieUtils::setRequest($request, $this->csrfCookie->getName(), $this->csrfCookie->getValue());
        $response = $this->middleware->__invoke($request, new Response(), $this->callable);
        $this->assertEquals(400, $response->getStatusCode());
    }
}