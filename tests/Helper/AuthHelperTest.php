<?php

namespace Testsuite\Helper;

use PHPUnit\Framework\TestCase;
use Slim\Http\Response;
use Filehosting\Helper\TokenGenerator;
use Filehosting\Helper\AuthHelper;
use Filehosting\Helper\CookieHelper;
use Filehosting\Entity\File;
use Testsuite\Utils\Factory;
use Testsuite\Utils\CookieUtils;

class AuthHelperTest extends TestCase
{
    protected $authHelper;
    protected $authCookie;
    protected $authToken;

    public function setUp()
    {
        $this->authHelper = new AuthHelper(new CookieHelper(Factory::requestFactory(), new Response()));
        $response = $this->authHelper->authorizeUser();
        $this->authCookie = CookieUtils::getResponse($response, 'auth');
        $this->authToken = $this->authHelper->getUserToken();
    }

    public function testAuthCookies()
    {
        $this->assertNotEmpty($this->authCookie->getValue());
    }

    public function testAuthTokenSecurity()
    {
        $this->assertRegExp('/^.{45,}$/u', $this->authToken);
    }

    public function testUserNotAuthorized()
    {
        $newHelper = new AuthHelper(new CookieHelper(Factory::requestFactory(), new Response()));
        $this->assertFalse($newHelper->isAuthorized());
    }

    public function testUserAuthorized()
    {
        $request = Factory::requestFactory();
        $request = CookieUtils::setRequest($request, $this->authCookie->getName(), $this->authCookie->getValue());
        $newHelper = new AuthHelper(new CookieHelper($request, new Response()));
        $this->assertTrue($newHelper->isAuthorized());
        $this->assertEquals($this->authToken, $newHelper->getUserToken());
    }
}