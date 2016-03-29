<?php

namespace Filehosting\Helper;

class CommentHelper
{
    public function getMargin($path)
    {
        $split = preg_split("/[.]/", $path);
        return (count($split) - 1) * 25;
    }

    public function normalizePath($path)
    {
        $split = preg_split("/[.]/", $path);
        $newStr = "";
        foreach($split as $elem) {
            $newStr .= ($newStr != "" ? "." . str_pad($elem, 3, "0", STR_PAD_LEFT) : str_pad($elem, 3, "0", STR_PAD_LEFT));
        }
        return $newStr;
    }
}