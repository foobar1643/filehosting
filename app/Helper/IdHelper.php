<?php

namespace Filehosting\Helper;

use \Filehosting\Entity\File;

class IdHelper
{
    const IMAGES_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    const AUDIO_MIME_TYPES = ['audio/mpeg'];
    const VIDEO_MIME_TYPES = ['video/webm', 'video/x-flv', 'video/quicktime'];

    private $getId3;
    private $pathingHelper;

    public function __construct($id3, PathingHelper $helper)
    {
        $this->getId3 = $id3;
        $this->getId3->option_md5_data = true;
		$this->getId3->option_md5_data_source = true;
		$this->getId3->encoding = 'UTF-8';
        $this->pathingHelper = $helper;
    }

    public function analyzeFile(File $file)
    {
        $fileInfo = $this->getId3->analyze($this->pathingHelper->getPathToFile($file));
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
