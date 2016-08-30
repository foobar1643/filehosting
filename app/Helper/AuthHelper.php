<?php

namespace Filehosting\Helper;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Entity\File;

/**
 * Authorizes current user, gets a authorization token for current user,
 *  determines if current user is authorized or can manage a given file.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class AuthHelper
{
    /** @var string $authToken Stores an auth token for a current user. */
    private $authToken;
    /** @var CookieHelper $cookieHelper CookieHelper instance. */
    private $cookieHelper;

    /**
     * Constructor.
     *
     * @param CookieHelper $c A CookieHelper instance.
     */
    public function __construct(CookieHelper $c)
    {
        $this->cookieHelper = $c;
    }

    /**
     * Checks if current user authorized.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->cookieHelper->requestCookieExists('auth');
    }

    /**
     * Gets user authorization token.
     *
     * @return string
     */
    public function getUserToken()
    {
        return !is_null($this->authToken) ? $this->authToken : $this->cookieHelper->getRequestCookie('auth');
    }

    /**
     * Checks if current user can manage given file.
     *
     * @param File $file A File entity.
     *
     * @return bool
     */
    public function canManageFile(File $file)
    {
        return ($this->cookieHelper->getRequestCookie('auth') === $file->getAuthToken());
    }

    /**
     * Authorizes current user.
     *
     * @return Response
     */
    public function authorizeUser()
    {
        $this->authToken = Utils::generateToken(45);
        return $this->cookieHelper->setResponseCookie('auth', $this->authToken, new \DateInterval('P30D'), '/');
    }
}