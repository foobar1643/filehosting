<?php

namespace Filehosting\Helper;

use \Filehosting\Model\File;

class PreviewHelper
{
    private $image;
    private $file;

    const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    private function loadImage()
    {
        $fileHelper = new FileHelper();
        $filename = "storage/{$this->file->getFolder()}/{$fileHelper->getDiskName($this->file)}";
        switch($this->file->getExtention()) {
            case "jpeg":
            case "jpg":
                $this->image = imagecreatefromjpeg($filename);
                break;
            case "png":
                $this->image = imagecreatefrompng($filename);
                break;
            case "gif":
                $this->image = imagecreatefromgif($filename);
                break;
            default:
                throw new \Exception("Can't load this filetype.");
                break;
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

    public function generatePreview()
    {
        if(!in_array($this->file->getExtention(), self::ALLOWED_EXTENSIONS)) {
            return false;
        }
        $this->loadImage();
        $imageWidth = imagesx($this->image);
        $imageHeight = imagesy($this->image);
        $previewSize = $this->getPreviewSize($imageWidth, $imageHeight);
        $previewWidth = $previewSize["width"];
        $previewHeight = $previewSize["height"];
        $preview = imagecreatetruecolor($previewWidth, $previewHeight);
        imagecopyresampled($preview, $this->image, 0, 0, 0, 0, $previewWidth, $previewHeight, $imageWidth, $imageHeight);
        $fileHelper = new FileHelper();
        $diskName = $fileHelper->getDiskName($this->file);
        imagejpeg($preview, "storage/previews/thumb_{$diskName}");
        imagedestroy($preview);
        imagedestroy($this->image);
    }
}