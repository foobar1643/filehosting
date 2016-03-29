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

    public function filterSearchResults(FileMapper $fileMapper, $fileIds)
    {
        $files = [];
        foreach($fileIds as $searchId) {
            $file = $fileMapper->getFile($searchId['id']);
            if($file != null) {
                array_push($files, $file);
            }
        }
        return $files;
    }
}