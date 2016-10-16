<?php

namespace Filehosting\Helper;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

/**
 * Gets an application or user locale, checks if language change message needs to be shown to user.
 *
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class LanguageHelper
{
    /** @var string DEFAULT_LOCALE Default application locale. */
    const DEFAULT_LOCALE = "en_US";
    /** @var array AVAILABLE_LANGUAGES Available application languages. */
    const AVAILABLE_LANGUAGES = ["en", "ru"];

    /** @var Request $request Slim Framework request instance. */
    private $request;

    /**
     * Constructor.
     *
     * @param Request $request Slim Framework request instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns readable language display name. Example: en -> English.
     *
     * @param string $language String with a language name.
     *
     * @return string
     */
    public function getLanguageDisplayName($language)
    {
        $locale = $this->composeFromLanguage($language);
        $displayLanguage = \Locale::getDisplayLanguage($locale, $locale);
        return mb_strtoupper(mb_substr($displayLanguage, 0, 1)) . mb_substr($displayLanguage, 1);
    }

    /**
     * Checks if language change message needs to be shown to user.
     *
     * @return bool
     */
    public function canShowLangMsg()
    {
        $cookieHelper = new CookieHelper($this->request, new Response());
        $msgShown = $cookieHelper->getRequestCookie('langChangeShown');
        $appLocale = $this->getAppLocale();
        $userLocale = $this->getUserLocale();
        return ($userLocale != $appLocale && $this->languageAvailable($userLocale) && intval($msgShown) < 7);
    }

    /**
     * Returns a constant with available languages. Generally this is used in templates.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        return self::AVAILABLE_LANGUAGES;
    }

    /**
     * Checks if a given language is available in the application.
     *
     * @return bool
     */
    public function languageAvailable($locale)
    {
        $parsedLocale = \Locale::parseLocale($locale);
        return (!is_null($locale) && in_array($parsedLocale['language'], self::AVAILABLE_LANGUAGES));
    }

    /**
     * Returns current application locale.
     *
     * @return string
     */
    public function getAppLocale()
    {
        $requestTarget = $this->request->getRequestTarget();
        $language = preg_split("/\//", $requestTarget);
        $urlLanguage = isset($language[1]) ? $language[1] : NULL;
        return $this->composeFromLanguage($urlLanguage);
    }

    /**
     * Returns current user locale from HTTP headers (if present).
     *
     * @return string|null
     */
    public function getUserLocale()
    {
        if($this->request->hasHeader('HTTP_ACCEPT_LANGUAGE')) {
            $header = $this->request->getHeader('HTTP_ACCEPT_LANGUAGE');
            return $this->composeFromLanguage($header[0]);
        }
        return null;
    }

    /**
     * Composes a locale from given language string
     *
     * @param string $language A language string.
     *
     * @return string
     */
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