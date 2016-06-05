<?php

namespace Filehosting\Helper;

use \GetId3\GetId3Core as GetId3;
use \Filehosting\Entity\File;
use \Filehosting\Helper\FileHelper;

class IdHelper
{
    const IMAGES_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    const AUDIO_MIME_TYPES = ['audio/mpeg'];
    const VIDEO_MIME_TYPES = ['video/webm'];

    private $getId3;
    private $pathingHelper;

    public function __construct(GetId3 $id3, PathingHelper $helper)
    {
        $this->getId3 = $id3;
        $this->pathingHelper = $helper;
    }

    public function analyzeFile(File $file)
    {
        $fileInfo = $this->getId3->setOptionMD5Data(true)
            ->setOptionMD5DataSource(true)
            ->setEncoding('UTF-8')
            ->analyze($this->pathingHelper->getPathToFile($file));
        return $fileInfo;
    }
    public function isPreviewable(array $fileInfo)
    {
        if(isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::IMAGES_MIME_TYPES)) {
            return true;
        }
        return false;
    }

    public function isAudio(array $fileInfo)
    {
        if(isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::AUDIO_MIME_TYPES)) {
            return true;
        }
        return false;
    }

    public function isVideo(array $fileInfo)
    {
        if(isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::VIDEO_MIME_TYPES)) {
            return true;
        }
        return false;
    }
}