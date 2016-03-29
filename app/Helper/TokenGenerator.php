<?php

namespace Filehosting\Helper;

class TokenGenerator
{
    public static function generateToken($length)
    {
        $result = null;
        $source = str_split('abcdefghijklmnopqrstuvwxyz'
          .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
          .'0123456789');
        for($i = 0; $i < $length; $i++) {
            $result .= $source[mt_rand(0, count($source) - 1)];
        }
        return $result;
    }
}