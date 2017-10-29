<?php

namespace Filehosting\Helper;

use Filehosting\Entity\File;

class PathingHelper
{
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
        return "{$this->basePath}/public/thumbnails";
    }

    public function getPathToStorage()
    {
        return "{$this->basePath}/storage";
    }

    public function getPathToLocales()
    {
        return "{$this->basePath}/locale";
    }

    public function getPathToFileFolder(File $file)
    {
        return "{$this->getPathToStorage()}/{$file->getFolder()}";
    }

    public function getPathToFile(File $file)
    {
        return "{$this->getPathToStorage()}/{$file->getFolder()}/{$file->getDiskName()}";
    }

    public function getXsendfilePath(File $file)
    {
        return "{$this->getPathToBase()}/storage/{$file->getFolder()}/{$file->getDiskName()}";
    }

    public function getXaccelPath(File $file)
    {
        return "/storage/{$file->getFolder()}/{$file->getDiskName()}"; // /storage/ - as internal
    }
}
