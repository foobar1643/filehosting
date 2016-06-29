<?php

namespace Filehosting\Helper;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Http\Response;

class LanguageHelper
{
    const AVAILABLE_LANGUAGES = ["en", "ru"];

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getLanguageDisplayName($language)
    {
        $locale = $this->composeFromLanguage($language);
        $displayLanguage = \Locale::getDisplayLanguage($locale, $locale);
        return mb_strtoupper(mb_substr($displayLanguage, 0, 1)) . mb_substr($displayLanguage, 1);
    }

    public function canShowLangMsg()
    {
        $cookieHelper = new CookieHelper($this->request, new Response());
        $msgShown = $cookieHelper->getRequestCookie('langChangeShown');
        $appLocale = $this->getAppLocale();
        $userLocale = $this->getUserLocale();
        if($userLocale != $appLocale && $this->languageAvailable($userLocale) && intval($msgShown) < 7) {
            return true;
        }
        return false;
    }

    public function getAvailableLanguages()
    {
        return self::AVAILABLE_LANGUAGES;
    }

    public function languageAvailable($locale)
    {
        $parsedLocale = \Locale::parseLocale($locale);
        if(!is_null($locale) && in_array($parsedLocale['language'], self::AVAILABLE_LANGUAGES)) {
            return true;
        }
        return false;
    }

    public function getAppLocale()
    {
        $requestTarget = $this->request->getRequestTarget();
        $language = preg_split("/\//", $requestTarget);
        $urlLanguage = isset($language[1]) ? $language[1] : NULL;
        return $this->composeFromLanguage($urlLanguage);
    }

    public function getUserLocale()
    {
        if($this->request->hasHeader('HTTP_ACCEPT_LANGUAGE')) {
            $header = $this->request->getHeader('HTTP_ACCEPT_LANGUAGE');
            return $this->composeFromLanguage($header[0]);
        }
        return null;
    }

    private function composeFromLanguage($language)
    {
        $parsedLocale = \Locale::parseLocale($language);
        $split = explode(",", $parsedLocale['language']);
        $composedLocale = array(
            'language'=> $split[0],
            'region'  => isset($parsedLocale['region']) ? $parsedLocale['region'] : strtoupper($split[0])
        );
        return \Locale::composeLocale($composedLocale);
    }
}