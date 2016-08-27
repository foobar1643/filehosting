<?php

namespace Testsuite\Utils;

class TestUtils
{
    public static function isStringEmpty($str)
    {
        return !(is_string($str) && !empty(trim($str)));
    }
}