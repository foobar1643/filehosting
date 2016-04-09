<?php

namespace Filehosting\Helper;

use \GetId3\GetId3Core as GetId3;
use \Filehosting\Model\File;
use \Filehosting\Helper\FileHelper;

class IdHelper
{
    const IMAGES_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    const AUDIO_MIME_TYPES = ['audio/mpeg'];
    const VIDEO_MIME_TYPES = ['video/webm'];

    private $getId3;
    private $fileHelper;
    private $fileInfo;

    public function __construct(GetId3 $id3, FileHelper $helper)
    {
        $this->getId3 = $id3;
        $this->fileHelper = $helper;
    }

    public function analyzeFile(File $file)
    {
        $this->fileInfo = $this->getId3->setOptionMD5Data(true)
            ->setOptionMD5DataSource(true)
            ->setEncoding('UTF-8')
            ->analyze($this->fileHelper->getPathToFileFolder($file));
        return true;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function isPreviewable()
    {
        if(isset($this->fileInfo['mime_type']) && in_array($this->fileInfo['mime_type'], self::IMAGES_MIME_TYPES)) {
            return true;
        }
        return false;
    }

    public function isAudio()
    {
        if(isset($this->fileInfo['mime_type']) && in_array($this->fileInfo['mime_type'], self::AUDIO_MIME_TYPES)) {
            return true;
        }
        return false;
    }

    public function isVideo()
    {
        if(isset($this->fileInfo['mime_type']) && in_array($this->fileInfo['mime_type'], self::VIDEO_MIME_TYPES)) {
            return true;
        }
        return false;
    }
}