<?php

namespace Filehosting\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\CookieHelper;
use \Filehosting\Helper\PathingHelper;
use \Filehosting\Helper\LanguageHelper;

class LocaleMiddleware
{
    private $pathingHelper;

    public function __construct(PathingHelper $h)
    {
        $this->pathingHelper = $h;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        \Locale::setDefault('en_US');
        $cookieHelper = new CookieHelper($request, $response);
        $languageHelper = new LanguageHelper($request);
        $appLocale = $languageHelper->getAppLocale();
        if(isset($appLocale) && $languageHelper->languageAvailable($appLocale)) {
            $this->setTextDomain($appLocale);
            if($languageHelper->canShowLangMsg() == true) {
                $msgViews = intval($cookieHelper->getRequestCookie('langChangeShown')) + 1;
                $response = $cookieHelper->setResponseCookie('langChangeShown', $msgViews, new \DateInterval("PT3H"), '/');
            }
        }
        return $next($request, $response);
    }

    private function setTextDomain($locale)
    {
        putenv("LC_ALL=$locale");
        setlocale(LC_ALL, $locale);
        bindtextdomain($locale, $this->pathingHelper->getPathToLocales());
        bind_textdomain_codeset($locale, 'UTF-8');
        textdomain($locale);
        \Locale::setDefault($locale);
    }
}