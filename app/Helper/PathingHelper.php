<?php

namespace Filehosting\Helper;

class PathingHelper {

    private $basePath;

    public function __construct($initDir)
    {
        $this->basePath = str_replace("/app", "", $initDir);
    }

    public function getPathToBase()
    {
        return $this->basePath;
    }

    public function getPathToThumbnails()
    {
        return $this->basePath . "/public/thumbnails";
    }
}