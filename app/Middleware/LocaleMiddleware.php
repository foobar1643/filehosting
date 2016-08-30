<?php

namespace Filehosting\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Helper\CookieHelper;
use Filehosting\Helper\PathingHelper;
use Filehosting\Helper\LanguageHelper;

/**
 * Middleware for Slim Framework, provides basic localization capabilities.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class LocaleMiddleware
{
    /** @var PathingHelper $pathingHelper PathingHelper instance. */
    private $pathingHelper;

    /**
     * Constructor.
     *
     * @param PathingHelper $h A pathing helper instance.
     */
    public function __construct(PathingHelper $h)
    {
        $this->pathingHelper = $h;
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @param Request $request A request instance.
     * @param Response $response A response instance.
     * @param callable $next Next callable.
     *
     * @throws NotFoundException if requested language does not exists.
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        \Locale::setDefault(LanguageHelper::DEFAULT_LOCALE);
        $cookieHelper = new CookieHelper($request, $response);
        $languageHelper = new LanguageHelper($request);
        $appLocale = $languageHelper->getAppLocale();
        if(!$languageHelper->languageAvailable($appLocale)) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        } else if(isset($appLocale) && $languageHelper->languageAvailable($appLocale)) {
            $this->setTextDomain($appLocale);
            if($languageHelper->canShowLangMsg() == true) {
                $msgViews = intval($cookieHelper->getRequestCookie('langChangeShown')) + 1;
                $response = $cookieHelper->setResponseCookie('langChangeShown', $msgViews, new \DateInterval("PT3H"), '/');
            }
        }
        return $next($request, $response);
    }

    /**
     * Sets a env variable to a given locale, binds and switches textdomain in order for gettext to work.
     *
     * @todo Think about the effects of env variable, remove it if possible.
     *
     * @param string $locale Locale string.
     *
     * @return void
     */
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