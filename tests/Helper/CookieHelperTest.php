<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Filehosting\Helper\CookieHelper;
use Testsuite\Utils\Factory;
use Testsuite\Utils\CookieUtils;

class CookieHelperTest extends TestCase
{
    protected static $cookieHelper;

    public static function setUpBeforeClass()
    {
        self::$cookieHelper = new CookieHelper(Factory::requestFactory(), new Response());
    }

    public function testSettingResponseCookie()
    {
        $response = self::$cookieHelper->setResponseCookie('name', 'value', new \DateInterval('P1D'), '/');
        $this->assertEquals('value', CookieUtils::getResponse($response, 'name')->getValue());
    }

    public function testSettingRequestCookie()
    {
        $request = self::$cookieHelper->setRequestCookie('name', 'value');
        $requestCookie = CookieUtils::getRequest($request, 'name');
        $this->assertEquals('value', $requestCookie->getValue());
        return $requestCookie;
    }

    /**
     * @depends testSettingRequestCookie
     */
    public function testGettingRequestCookie($requestCookie)
    {

        $this->assertEquals($requestCookie->getValue(), self::$cookieHelper->getRequestCookie($requestCookie->getName()));
    }

    /**
     * @depends testSettingRequestCookie
     */
    public function testRequestCookieExists($requestCookie)
    {
        $this->assertTrue(self::$cookieHelper->requestCookieExists($requestCookie->getName()));
    }
}