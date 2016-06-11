<?php

namespace Filehosting\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\LanguageHelper;

class LocaleMiddleware
{
    private $languageHelper;

    public function __construct(LanguageHelper $h)
    {
        $this->languageHelper = $h;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $appLocale = $this->languageHelper->getAppLocale($request);
        if(isset($appLocale) && $this->languageHelper->languageAvailable($appLocale)) {
            $this->setTextDomain($appLocale);
            if($this->languageHelper->canShowLangMsg($request) == true) {
                $msgViews = $this->languageHelper->getLangMsgViews($request) + 1;
                $response = $this->languageHelper->setLangMsgViews($msgViews, $request, $response);
            }
        }
        $response = $next($request, $response);
        return $response;
    }

    private function setTextDomain($locale)
    {
        putenv("LC_ALL=$locale");
        setlocale(LC_ALL, $locale);
        bindtextdomain($locale, LanguageHelper::PATH_TO_LOCALES);
        bind_textdomain_codeset($locale, 'UTF-8');
        textdomain($locale);
        \Locale::setDefault($locale);
    }
}