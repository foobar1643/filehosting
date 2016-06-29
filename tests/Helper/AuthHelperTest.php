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
    protected static $authHelper;

    public static function setUpBeforeClass()
    {
        $request = CookieUtils::setRequest(Factory::requestFactory(), 'auth', '1234567890');
        self::$authHelper = new AuthHelper(new CookieHelper($request, new Response()));
    }

    public function testUserAuthorization()
    {
        $response = self::$authHelper->authorizeUser();
        $this->assertRegExp('/^[\d\w]{45}$/', CookieUtils::getResponse($response, 'auth')->getValue());
    }

    public function testUserAuthorized()
    {
        $this->assertTrue(self::$authHelper->isAuthorized());
    }

    public function testCanManageFile()
    {
        $file = $this->createMock(File::class);
        $file->method('getAuthToken')->willReturn('1234567890');
        $this->assertTrue(self::$authHelper->canManageFile($file));
    }

    public function testCannotManageFile()
    {
        $file = $this->createMock(File::class);
        $this->assertFalse(self::$authHelper->canManageFile($file));
    }
}