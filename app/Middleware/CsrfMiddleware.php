<?php

namespace Filehosting\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\CookieHelper;
use \Filehosting\Helper\Utils;

class CsrfMiddleware
{
    private $cookieLifetime;
    private $tokenLength;

    public function __construct($cookieLifetime = "PT3H", $tokenLength = 65)
    {
        $this->cookieLifetime = $cookieLifetime;
        $this->tokenLength = $tokenLength;
    }

    public function __invoke(Request $request, Response $response, callable $next) // split this into two methods - validate csrf token and set csrf token
    {
        $cookieHelper = new CookieHelper($request, $response);
        if($request->isPost()) {
            $formToken = $request->getParsedBodyParam('csrf_token');
            $cookieToken = $cookieHelper->getRequestCookie('csrf_token');
            if(!$this->validateCsrfToken($formToken, $cookieToken)) {
                $failure = $this->getFailureCallable();
                return $failure($request, $response, $next);
            }
        }
        if(!$cookieHelper->requestCookieExists('csrf_token')) {
            $csrfToken = Utils::generateToken($this->tokenLength);
            $request = $cookieHelper->setRequestCookie('csrf_token', $csrfToken);
        } else {
            $csrfToken = $cookieHelper->getRequestCookie('csrf_token');
        }
        $response = $cookieHelper->setResponseCookie('csrf_token', $csrfToken, new \DateInterval($this->cookieLifetime), '/');
        return $next($request, $response);
    }

    private function validateCsrfToken($formToken, $cookieToken)
    {
        return ($formToken !== NULL && $cookieToken !== NULL && $formToken === $cookieToken);
    }

    private function getFailureCallable()
    {
        return function (Request $request, Response $response, $next) {
            $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
            $body->write('CSRF check failed.');
            return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
        };
    }
}