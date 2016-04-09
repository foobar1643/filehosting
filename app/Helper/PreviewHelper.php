<?php

namespace Filehosting\Helper;

use \Filehosting\Model\File;

class PreviewHelper
{
    private $fileHelper;
    private $thumbnailsFolder;
    private $image;
    private $file;

    const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];

    public function __construct(FileHelper $h, $thumbnailsFolder)
    {
        $this->fileHelper = $h;
        $this->thumbnailsFolder = $thumbnailsFolder;
    }

    private function loadImage(File $file)
    {
        $filename = $this->fileHelper->getPathToFileFolder($file);
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
        $image = $this->loadImage($file);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $previewSize = $this->getPreviewSize($imageWidth, $imageHeight);
        $previewWidth = $previewSize["width"];
        $previewHeight = $previewSize["height"];
        $preview = imagecreatetruecolor($previewWidth, $previewHeight);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewWidth, $previewHeight, $imageWidth, $imageHeight);
        imagejpeg($preview, "{$this->thumbnailsFolder}/thumb_{$this->fileHelper->getDiskName($file)}");
        imagedestroy($preview);
        imagedestroy($image);
    }
}