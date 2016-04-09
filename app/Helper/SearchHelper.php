<?php

namespace Filehosting\Helper;

use \Filehosting\Database\FileMapper;

class SearchHelper
{
    public function escapeString($string)
    {
        $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=');
        $to = array('\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=');
        return str_replace($from, $to, $string);
    }

    public function filterArray($array)
    {
        $files = [];
        foreach($array as $searchId) {
            array_push($files, $searchId['id']);
        }
        if(empty($array)) {
            $files[0] = null;
        }
        return $files;
    }
}