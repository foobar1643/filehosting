<?php

namespace Filehosting\Helper;

use Filehosting\Entity\File;
use Filehosting\Entity\Comment;

/**
 * Generates relative URLs for given file or comment entites.
 *  Generally it is used in the templates.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class LinkHelper
{
    /** @var string $lang Current application language. */
    private $lang;

    /**
     * Constructor.
     *
     * @param string $locale A locale string.
     */
    public function __construct($locale)
    {
        $this->lang = \Locale::parseLocale($locale)['language'];
    }

    /**
     * Returns a relative URL to given file.
     *
     * @param File $file A file entity.
     *
     * @return string
     */
    public function getLinkToFile(File $file)
    {
        return $this->generateLink("/file/{$file->getId()}/");
    }

    /**
     * Returns a relative URL to download page for a given file.
     *
     * @param File $file A file entity.
     *
     * @return string
     */
    public function getFileDownloadLink(File $file)
    {
        $encodedName = urlencode($file->getClientFilename());
        return $this->generateLink("/file/get/{$file->getId()}/{$encodedName}");
    }

    /**
     * Returns a relative URL to deletion page for a given file.
     *
     * @param File $file A file entity.
     *
     * @return string
     */
    public function getFileDeletionLink(File $file)
    {
        return $this->generateLink("/file/{$file->getId()}/delete/");
    }

    /**
     * Returns a relative URL for a stream page for a given file.
     *
     * @param File $file A file entity.
     *
     * @return string
     */
    public function getFileStreamLink(File $file)
    {
        $httpQuery = http_build_query(['type' => "stream"]);
        $encodedName = urlencode($file->getClientFilename());
        return $this->generateLink("/file/get/{$file->getId()}/{$encodedName}?{$httpQuery}");
    }

    /**
     * Returns a relative URL to a thumbnail location for a given file.
     *
     * @param File $file A file entity.
     *
     * @return string
     */
    public function getFileThumbnailLink(File $file)
    {
        $encodedName = rawurlencode($file->getThumbnailName());
        return "/thumbnails/{$encodedName}";
    }

    /**
     * Returns a relative URL to a comment post form for a given file ID.
     *
     * @param int $fileId A file ID in the database.
     *
     * @return string
     */
    public function getCommentFormReplyLink($fileId)
    {
        return $this->generateLink("/file/{$fileId}/");
    }

    /**
     * Returns a relative URL to a comment reply form for a given comment.
     *
     * @param Comment $comment A comment entity.
     *
     * @return string
     */
    public function getCommentReplyLink(Comment $comment)
    {
        $httpQuery = http_build_query(['reply' => $comment->getId()]);
        return $this->getCommentFormReplyLink($comment->getFileId()) . "?{$httpQuery}";
    }

    /**
     * Returns a relative URL for a current language.
     *
     * @param string $link A relative URL without a language parameter.
     *
     * @return string
     */
    public function generateLink($link)
    {
        return "/" . $this->lang . (($link[0] == "/") ? $link : "/" . $link);
    }
}
