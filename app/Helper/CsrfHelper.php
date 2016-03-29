<?php

namespace Filehosting\Helper;

class CsrfHelper
{

    public function validateCsrfToken($token)
    {
        if(preg_match("/(^[a-zA-Z0-9]{45}$)/", $token)) {
            return true;
        }
        return false;
    }

    public function checkCsrfToken($formToken)
    {
        if(isset($_COOKIE['token']) && $_COOKIE['token'] == $formToken) {
            return true;
        }
        return false;
    }

    public function setCsrfToken()
    {
        $currentToken = null;
        if(isset($_COOKIE['token'])) {
            $currentToken = $_COOKIE['token'];
        } else {
            $currentToken = TokenGenerator::generateToken(45);
        }
        setcookie("token", $currentToken, time()+3600000);
        return $currentToken;
    }
}