<?php

namespace Filehosting\Helper;

use \Filehosting\Entity\File;

class PreviewHelper
{
    private $pathingHelper;
    private $image;
    private $file;

    const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];

    public function __construct(PathingHelper $h)
    {
        $this->pathingHelper = $h;
    }

    private function loadImage(File $file)
    {
        $pathToFile = $this->pathingHelper->getPathToFile($file);
        switch($file->getExtention()) {
            case "jpeg":
            case "jpg":
                return imagecreatefromjpeg($pathToFile);
            case "png":
                return imagecreatefrompng($pathToFile);
            case "gif":
                return imagecreatefromgif($pathToFile);
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
        $image = $this->loadImage($file);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $previewSize = $this->getPreviewSize($imageWidth, $imageHeight);
        $previewWidth = $previewSize["width"];
        $previewHeight = $previewSize["height"];
        $preview = imagecreatetruecolor($previewWidth, $previewHeight);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewWidth, $previewHeight, $imageWidth, $imageHeight);
        imagejpeg($preview, "{$this->pathingHelper->getPathToThumbnails()}/thumb_{$file->getDiskName()}");
        imagedestroy($preview);
        imagedestroy($image);
    }
}