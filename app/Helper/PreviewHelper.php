<?php

namespace Filehosting\Helper;

use Filehosting\Entity\File;

/**
 * Generates thumbnails for files.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class PreviewHelper
{
    const ALLOWED_EXTENSIONS = ["jpg", "jpeg", "png", "gif"];

    /**
     * @var \Filehosting\Helper\PathingHelper PathingHelper instance.
     */
    private $pathingHelper;

    /**
     * @var resource Current image resource.
     */
    private $image;

    /**
     * Constructor.
     *
     * @param \Filehosting\Helper\PathingHelper $h A pathing helper instance.
     */
    public function __construct(PathingHelper $h)
    {
        $this->pathingHelper = $h;
    }

    /**
     * Loads given file to the memory.
     *
     * @param \Filehosting\Entity\File $file A file entity to load.
     *
     * @throws \Exception if file of this type can't be loaded.
     *
     * @return resource|bool
     */
    private function loadImage(File $file)
    {
        $pathToFile = $this->pathingHelper->getPathToFile($file);
        switch (strtolower($file->getExtension())) {
            case "jpeg":
            case "jpg":
                return imagecreatefromjpeg($pathToFile);
            case "png":
                return imagecreatefrompng($pathToFile);
            case "gif":
                return imagecreatefromgif($pathToFile);
            default:
                throw new \Exception(_("Can't load this filetype."));
        }
    }

    /**
     * Returns a compression ratio for a given width and height parameters.
     *
     * @param int $width Width of an image.
     * @param int $height Height of an image.
     *
     * @return int
     */
    private function getMaxRatio(int $width, int $height)
    {
        $sizeSum = $width + $height;

        switch (true) {
            case ($sizeSum > 2000):
                return 0.05;
            case ($sizeSum > 500):
                return 0.15;
            default:
                return 0.45;
        }
    }

    /**
     * Returns an array with compressed width and height for a given width and height values.
     *
     * @param int $width Width of an image.
     * @param int $height Height of an image.
     *
     * @return array
     */
    private function getPreviewSize(int $width, int $height)
    {
        $ratio = ($width > $height) ? $width / $height : $height / $width;

        if ($ratio >= 1) {
            $ratio = $this->getMaxRatio($width, $height);
        }

        return ["width" => ceil($width * $ratio), "height" => ceil($height * $ratio)];
    }

    /**
     * Generates a thumbnail for a given file entity.
     *
     * @todo Think about how Exceptions are thrown, maybe it is unnecessary to throw it here?
     *
     * @param File $file File entity for which the thumbnail will be generated.
     *
     * @throws \Exception if can't generate a thumbnail for this file type.
     *
     * @return void
     */
    public function generatePreview(File $file)
    {
        if (!in_array(strtolower($file->getExtension()), self::ALLOWED_EXTENSIONS)) {
            throw new \Exception(_("Can't generate preview for {$file->getExtension()} type."));
        }
        $image = $this->loadImage($file);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $previewSize = $this->getPreviewSize($imageWidth, $imageHeight);
        $previewWidth = $previewSize["width"];
        $previewHeight = $previewSize["height"];
        $preview = imagecreatetruecolor($previewWidth, $previewHeight);
        imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewWidth, $previewHeight, $imageWidth, $imageHeight);
        imagejpeg($preview, "{$this->pathingHelper->getPathToThumbnails()}/{$file->getThumbnailName()}");
        imagedestroy($preview);
        imagedestroy($image);
    }

    /**
     * Deletes a thumbnail for a given file entity.
     *
     * @param File $file File entity for which the thumbnail will be deleted.
     *
     * @throws \Exception if can't delete a file from the filesystem.
     *
     * @return bool
     */
    public function deletePreview(File $file)
    {
        if (!unlink("{$this->pathingHelper->getPathToThumbnails()}/{$file->getThumbnailName()}")) {
            throw new \Exception(_("Can't unlink thumbnail. Try again or contact server administrators."));
        }
        return true;
    }
}
