<?php

namespace Filehosting\Helper;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;

class CookieHelper
{
    private $request;
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function setResponseCookie($name, $value, \DateInterval $time, $path)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add($time);
        $this->response = FigResponseCookies::set($this->response,
            SetCookie::create($name)->withValue($value)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath($path));
        return $this->response;
    }

    public function setRequestCookie($name, $value)
    {
        $this->request = FigRequestCookies::set($this->request, Cookie::create($name, $value));
        return $this->request;
    }

    public function getRequestCookie($name)
    {
        return FigRequestCookies::get($this->request, $name)->getValue();
    }

    public function requestCookieExists($name)
    {
        if(!is_null(FigRequestCookies::get($this->request, $name)->getValue())) {
            return true;
        }
        return false;
    }
}