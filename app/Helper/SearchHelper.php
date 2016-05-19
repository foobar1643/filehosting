<?php

namespace Filehosting\Helper;

use \Filehosting\Database\FileMapper;
use \Filehosting\Model\File;

class SearchHelper
{
    public function escapeString($string)
    {
        $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=');
        $to = array('\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=');
        return str_replace($from, $to, $string);
    }

    public function showDeleted($ids, $results)
    {
        $newResults = $results;
        if(count($ids) > count($results)) {
            for($i = 0; $i < (count($ids) - count($results)); $i++) {
                $deletedFile = new File();
                $deletedFile->setDeleted(true);
                array_push($newResults, $deletedFile);
            }
        }
        return $newResults;
    }
}