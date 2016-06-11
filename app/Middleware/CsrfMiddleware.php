<?php

namespace Filehosting\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;
use \Filehosting\Helper\LanguageHelper;
use \Filehosting\Helper\TokenGenerator;

class CsrfMiddleware
{
    private $cookieLifetime;
    private $tokenLength;

    public function __construct($cookieLifetime = "PT3H", $tokenLength = 65)
    {
        $this->cookieLifetime = $cookieLifetime;
        $this->tokenLength = $tokenLength;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if($request->isPost()) {
            $postVars = $request->getParsedBody();
            $formToken = isset($postVars["csrf_token"]) ? $postVars["csrf_token"] : NULL;
            $cookieToken = FigRequestCookies::get($request, "csrf_token")->getValue();
            if(!isset($formToken) || !isset($cookieToken) || !$this->validateCsrfToken($formToken, $cookieToken)) {
                $failure = $this->getFailureCallable();
                return $failure($request, $response, $next);
            }
        }
        if(!$this->tokenExists($request)) {
            $response = $this->setCsrfToken($response, TokenGenerator::generateToken($this->tokenLength));
        }
        return $next($request, $response);
    }

    private function tokenExists(Request $request)
    {
        $token = FigRequestCookies::get($request, "csrf_token")->getValue();
        if($token) {
            return true;
        }
        return false;
    }

    private function setCsrfToken(Response $response, $csrfToken)
    {
        $dateTime = new \DateTime("now");
        $dateTime->add(new \DateInterval($this->cookieLifetime));
        $response = FigResponseCookies::set($response,
            SetCookie::create("csrf_token")->withValue($csrfToken)
            ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath('/'));
        return $response;
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