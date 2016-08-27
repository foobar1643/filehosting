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

    public function __construct(\getID3 $id3, PathingHelper $helper)
    {
        $this->getId3 = $id3;
        $this->getId3->option_md5_data = true;
		$this->getId3->option_md5_data_source = true;
		$this->getId3->encoding = 'UTF-8';
        $this->pathingHelper = $helper;
    }

    public function analyzeFile(File $file)
    {
        return $this->getId3->analyze($this->pathingHelper->getPathToFile($file));
    }
    public function isPreviewable(array $fileInfo)
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::IMAGES_MIME_TYPES));
    }

    public function isAudio(array $fileInfo)
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::AUDIO_MIME_TYPES));
    }

    public function isVideo(array $fileInfo)
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::VIDEO_MIME_TYPES));
    }
}
