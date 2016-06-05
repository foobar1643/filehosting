<?php

namespace Filehosting\Helper;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;
use \Filehosting\Entity\File;

class AuthHelper
{
    private $authToken;

    public function isAuthorized(Request $request)
    {
        $cookie = FigRequestCookies::get($request, 'auth');
        if($cookie->getValue() != null) {
            return true;
        }
        return false;
    }

    public function getUserToken(Request $request)
    {
        return !is_null($this->authToken) ? $this->authToken : FigRequestCookies::get($request, 'auth')->getValue();
    }

    public function canManageFile(Request $request, File $file)
    {
        if(FigRequestCookies::get($request, 'auth')->getValue() == $file->getAuthToken()) {
            return true;
        }
        return false;
    }

    public function authorizeUser(Response $response)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add(new \DateInterval("P30D")); // 30 days
        $this->authToken = TokenGenerator::generateToken(45);
        $response = FigResponseCookies::set($response,
            SetCookie::create('auth')->withValue($this->authToken)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath('/'));
        return $response;
    }
}