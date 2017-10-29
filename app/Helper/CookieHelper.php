<?php

namespace Filehosting\Helper;

use Psr\Http\Message\RequestInterface;
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
    /**
     * @var \Psr\Http\Message\ServerRequestInterface PSR-7 Request instance.
     */
    private $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface PSR-7 Response instance.
     */
    private $response;

    /**
     * Constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 Request instance.
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 Response instance.
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
     * @param \DateInterval $time Expires date.
     * @param string $path Cookie Path.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setResponseCookie(string $name, string $value, \DateInterval $time, string $path): Response
    {
        $dateTime = new \DateTime("now");
        $dateTime->add($time);
        return FigResponseCookies::set(
            $this->response,
            SetCookie::create($name)->withValue($value)
                ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath($path)
        );
    }

    /**
     * Sets the request cookie, returns a request object with a cookie.
     *
     * @param string $name Cookie name.
     * @param string $value Cookie Value.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function setRequestCookie(string $name, string $value): RequestInterface
    {
        return FigRequestCookies::set($this->request, Cookie::create($name, $value));
    }

    /**
     * Returns a value of a request cookie with a given name.
     *
     * @param string $name Cookie name.
     *
     * @return string
     */
    public function getRequestCookie(string $name): string
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
    public function requestCookieExists(string $name): bool
    {
        return !is_null(FigRequestCookies::get($this->request, $name)->getValue());
    }
}
