<?php

namespace Filehosting\Helper;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;

/**
 * Gets or sets request or response cookies.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class CookieHelper
{
    /** @var Request $request Slim Framework request instance. */
    private $request;
    /** @var Response $response Slim Framework response instance. */
    private $response;

    /**
     * Constructor.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Sets the response cookie, returns a response object with a cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie Value.
     * @param DateInterval $time Expires date.
     * @param string $path Cookie Path.
     *
     * @return Request
     */
    public function setResponseCookie($name, $value, \DateInterval $time, $path)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add($time);
        $this->response = FigResponseCookies::set($this->response,
            SetCookie::create($name)->withValue($value)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath($path));
        return $this->response;
    }

    /**
     * Sets the request cookie, returns a request object with a cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie Value.
     *
     * @return Request
     */
    public function setRequestCookie($name, $value)
    {
        $this->request = FigRequestCookies::set($this->request, Cookie::create($name, $value));
        return $this->request;
    }

    /**
     * Returns a value of a request cookie with a given name.
     *
     * @param string $name Cookie name.
     *
     * @return string
     */
    public function getRequestCookie($name)
    {
        return FigRequestCookies::get($this->request, $name)->getValue();
    }

    /**
     * Checks if cookie with a given name exists.
     *
     * @param string $name Cookie name.
     *
     * @return bool
     */
    public function requestCookieExists($name)
    {
        return !is_null(FigRequestCookies::get($this->request, $name)->getValue());
    }
}