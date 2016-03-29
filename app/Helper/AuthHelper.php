<?php

namespace Filehosting\Helper;

class AuthHelper
{
    public function setAuthCookie()
    {
        $token = TokenGenerator::generateToken(45);
        setcookie('auth', $token, time()+3600000, "/");
        return $token;
    }

    public function getAuthCookie()
    {
        if(isset($_COOKIE['auth'])) {
            $token = $_COOKIE['auth'];
        } else {
            $token = $this->setAuthCookie();
        }
        return $token;
    }
}