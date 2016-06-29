<?php

namespace Testsuite\Utils;
use Slim\Http\Request;
use Slim\Http\Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;

class CookieUtils
{
    public static function setResponse(Response $response, $name, $value, \DateInterval $time, $path)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add($time);
        $response = FigResponseCookies::set($response,
            SetCookie::create($name)->withValue($value)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath($path));
        return $response;
    }

    public static function getResponse(Response $response, $name)
    {
        return FigResponseCookies::get($response, $name);
    }

    public static function setRequest(Request $request, $name, $value)
    {
        return FigRequestCookies::set($request, Cookie::create($name, $value));
    }

    public static function getRequest(Request $request, $name)
    {
        return FigRequestCookies::get($request, $name);
    }
}