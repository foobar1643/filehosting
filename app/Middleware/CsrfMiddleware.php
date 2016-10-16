<?php

namespace Filehosting\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Helper\CookieHelper;
use Filehosting\Helper\Utils;

/**
 * Middleware for Slim Framework, it provides protection against CSRF exploit using Cookies.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class CsrfMiddleware
{
    /** @var string $cookieLifetime Cookie lifetime. */
    private $cookieLifetime;
    /** @var int $tokenLength Csrf token length in symbols. */
    private $tokenLength;

    /**
     * Cookie helper instance.
     * @var \Filehosting\Helper\CookieHelper
     */
    protected $cookieHelper;

    /**
     * Constructor.
     *
     * @param string $cookieLifetime Cookie lifetime in DateInterval format.
     * @param int $tokenLength Csrf token length.
     */
    public function __construct($cookieLifetime = "PT3H", $tokenLength = 65)
    {
        $this->cookieLifetime = $cookieLifetime;
        $this->tokenLength = $tokenLength;
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @param Request $request A request instance.
     * @param Response $response A response instance.
     * @param callable $next Next callable.
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $this->cookieHelper = new CookieHelper($request, $response);
        if($request->isPost()) {
            $formToken = $request->getParsedBodyParam('csrf_token');
            $cookieToken = $this->cookieHelper->getRequestCookie('csrf_token');
            if(!$this->validateCsrfToken($formToken, $cookieToken)) {
                $failure = $this->getFailureCallable();
                return $failure($request, $response, $next);
            }
        }
        $csrfToken = $this->getCsrfToken();
        $request = $this->cookieHelper->setRequestCookie('csrf_token', $csrfToken);
        $response = $this->cookieHelper->setResponseCookie('csrf_token', $csrfToken, new \DateInterval($this->cookieLifetime), '/');
        return $next($request, $response);
    }

    /**
     * [getCsrfToken description]
     *
     * @return string CSRF token.
     */
    protected function getCsrfToken()
    {
        $requestToken = $this->cookieHelper->getRequestCookie('csrf_token');
        return !empty($requestToken) ? $requestToken : Utils::generateToken($this->tokenLength);
    }

    /**
     * Validates CSRF token.
     *
     * @param string $formToken CSRF token from POST form.
     * @param string $cookieToken CSRF token from a cookie.
     *
     * @return bool
     */
    protected function validateCsrfToken($formToken, $cookieToken)
    {
        return ($formToken !== NULL && $cookieToken !== NULL && $formToken === $cookieToken);
    }

    /**
     * Returns a failure callable.
     *
     * @return callable
     */
    protected function getFailureCallable()
    {
        return function (Request $request, Response $response, $next) {
            $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
            $body->write('CSRF check failed.');
            return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
        };
    }
}