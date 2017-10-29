<?php

namespace Filehosting\Helper;

use Filehosting\Entity\File;

/**
 * Analyzes a file using getId3 library, also checks if a file is an image, audio or video type.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class IdHelper
{
    const IMAGES_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

    const AUDIO_MIME_TYPES = ['audio/mpeg'];

    const VIDEO_MIME_TYPES = ['video/webm', 'video/x-flv', 'video/quicktime'];

    /**
     * @var \getID3 getID3 instance.
     */
    private $getId3;

    /**
     * @var \Filehosting\Helper\PathingHelper PathingHelper instance.
     */
    private $pathingHelper;

    /**
     * Constructor.
     *
     * @param \getID3 $id3 A getID3 instance.
     * @param \Filehosting\Helper\PathingHelper $helper A PathingHelper instance.
     */
    public function __construct(\getID3 $id3, PathingHelper $helper)
    {
        $this->getId3 = $id3;
        $this->getId3->option_md5_data = true;
        $this->getId3->option_md5_data_source = true;
        $this->getId3->encoding = 'UTF-8';
        $this->pathingHelper = $helper;
    }

    /**
     * Analyzes a given file using getID3 library. Returns an array with information about file.
     *
     * @param \Filehosting\Entity\File $file A file entity to analyze.
     *
     * @return array
     */
    public function analyzeFile(File $file): array
    {
        return $this->getId3->analyze($this->pathingHelper->getPathToFile($file));
    }

    /**
     * Checks if a file with a given info can be previewed.
     *
     * @param array $fileInfo Array with a file info.
     *
     * @return bool
     */
    public function isPreviewable(array $fileInfo): bool
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::IMAGES_MIME_TYPES));
    }

    /**
     * Checks if a file with a given info is of audio type.
     *
     * @param array $fileInfo Array with a file info.
     *
     * @return bool
     */
    public function isAudio(array $fileInfo): bool
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::AUDIO_MIME_TYPES));
    }

    /**
     * Checks if a file with a given info is of video type.
     *
     * @param array $fileInfo Array with a file info.
     *
     * @return bool
     */
    public function isVideo(array $fileInfo): bool
    {
        return (isset($fileInfo['mime_type']) && in_array($fileInfo['mime_type'], self::VIDEO_MIME_TYPES));
    }
}
