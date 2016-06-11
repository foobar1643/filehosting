<?php

namespace Filehosting\Helper;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;

class LanguageHelper
{
    const PATH_TO_LOCALES = "/var/www/filehosting/locale";
    const AVAILABLE_LANGUAGES = ["en", "ru"];

    public function getUserLocale(Request $request)
    {
        $serverParams = $request->getServerParams();
        $httpLanguage = isset($serverParams['HTTP_ACCEPT_LANGUAGE']) ? $serverParams['HTTP_ACCEPT_LANGUAGE'] : NULL;
        return $this->composeFromLanguage($httpLanguage);
    }

    public function getAppLocale(Request $request)
    {
        $requestTarget = $request->getRequestTarget();
        $language = preg_split("/\//", $requestTarget);
        $urlLanguage = isset($language[1]) ? $language[1] : NULL;
        return $this->composeFromLanguage($urlLanguage);
    }

    public function composeFromLanguage($language)
    {
        $parsedLocale = \Locale::parseLocale($language);
        $split = explode(",", $parsedLocale['language']);
        $composedLocale = array(
            'language'=> $split[0],
            'region'  => isset($parsedLocale['region']) ? $parsedLocale['region'] : strtoupper($split[0])
        );
        return \Locale::composeLocale($composedLocale);
    }

    public function getLanguageDisplayName($language)
    {
        $locale = $this->composeFromLanguage($language);
        $displayLanguage = \Locale::getDisplayLanguage($locale, $locale);
        return mb_strtoupper(mb_substr($displayLanguage, 0, 1)) . mb_substr($displayLanguage, 1);
    }

    public function getAvailableLanguages()
    {
        return self::AVAILABLE_LANGUAGES;
    }

    public function languageAvailable($locale)
    {
        $parsedLocale = \Locale::parseLocale($locale);
        if(in_array($parsedLocale['language'], self::AVAILABLE_LANGUAGES)) {
            return true;
        }
        return false;
    }

    public function getLangMsgViews(Request $request)
    {
        return intval(FigRequestCookies::get($request, 'langChangeShown')->getValue());
    }

    public function setLangMsgViews($views, Request $request, Response $response)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add(new \DateInterval("PT3H")); // 3 hours
        $response = FigResponseCookies::set($response,
            SetCookie::create('langChangeShown')->withValue($views)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath('/'));
        return $response;
    }

    public function canShowLangMsg(Request $request)
    {
        $msgShown = $this->getLangMsgViews($request);
        $appLocale = $this->getAppLocale($request);
        $userLocale = $this->getUserLocale($request);
        if($userLocale != $appLocale && $this->languageAvailable($userLocale) && $msgShown < 10) {
            return true;
        }
        return false;
    }
}