<?php

namespace Filehosting\Helper;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;

class AuthHelper
{
    private $authToken;
    private $cookieHelper;

    public function __construct(CookieHelper $c)
    {
        $this->cookieHelper = $c;
    }

    public function isAuthorized()
    {
        return $this->cookieHelper->requestCookieExists('auth');
    }

    public function getUserToken()
    {
        return !is_null($this->authToken) ? $this->authToken : $this->cookieHelper->getRequestCookie('auth');
    }

    public function canManageFile(File $file)
    {
        return ($this->cookieHelper->getRequestCookie('auth') == $file->getAuthToken());
    }

    public function authorizeUser()
    {
        $tokenGenerator = new TokenGenerator();
        $this->authToken = $tokenGenerator->generateToken(45);
        return $this->cookieHelper->setResponseCookie('auth', $this->authToken, new \DateInterval('P30D'), '/');
    }
}