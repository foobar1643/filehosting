<?php

namespace Filehosting\Helper;

use \Filehosting\Model\File;

class PreviewHelper
{
    private $container;
    private $image;
    private $file;

    const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    private function loadImage(File $file)
    {
        $fileHelper = $this->container->get("FileHelper");
        $filename = $fileHelper->getPathToFileFolder($file);
        switch($file->getExtention()) {
            case "jpeg":
            case "jpg":
                return imagecreatefromjpeg($filename);
            case "png":
                return imagecreatefrompng($filename);
            case "gif":
                return imagecreatefromgif($filename);
            default:
                throw new \Exception("Can't load this filetype.");
        }
    }

    private function getMaxRatio($width, $height)
    {
        $sizeSum = $width + $height;
        $maxRatio = null;
        if($sizeSum > 2000) {
            $maxRatio = 0.05;
        } else if($sizeSum > 500) {
            $maxRatio = 0.15;
        } else {
            $maxRatio = 0.45;
        }
        return $maxRatio;
    }

    private function getPreviewSize($width, $height)
    {
        if($width > $height) {
            $ratio = $width / $height;
        } else {
            $ratio = $height / $width;
        }
        if($ratio >= 1) {
            $ratio = $this->getMaxRatio($width, $height);
        }
        return ["width" => ceil($width * $ratio), "height" => ceil($height * $ratio)];
    }

    public function generatePreview(File $file)
    {
        if(!in_array($file->getExtention(), self::ALLOWED_EXTENSIONS)) {
            return false;
        }
        $pathingHelper = $this->container->get("PathingHelper");
        $fileHelper = $this->container->get("FileHelper");
        $image = $this->loadImage($file);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $previewSize = $this->getPreviewSize($imageWidth, $imageHeight);
        $previewWidth = $previewSize["width"];
        $previewHeight = $previewSize["height"];
        $preview = imagecreatetruecolor($previewWidth, $previewHeight);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewWidth, $previewHeight, $imageWidth, $imageHeight);
        imagejpeg($preview, "{$pathingHelper->getPathToThumbnails()}/thumb_{$fileHelper->getDiskName($file)}");
        imagedestroy($preview);
        imagedestroy($image);
    }
}