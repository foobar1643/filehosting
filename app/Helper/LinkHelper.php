<?php

namespace Filehosting\Helper;

use Filehosting\Entity\File;
use Filehosting\Entity\Comment;

class LinkHelper
{
    private $lang;

    public function __construct($locale)
    {
        $this->lang = \Locale::parseLocale($locale)['language'];
    }

    public function getLinkToFile(File $file)
    {
        return $this->generateLink("/file/{$file->getId()}/");
    }

    public function getFileDownloadLink(File $file)
    {
        $encodedName = urlencode($file->getClientFilename());
        return $this->generateLink("/file/get/{$file->getId()}/{$encodedName}");
    }

    public function getFileDeletionLink(File $file)
    {
        return $this->generateLink("/file/{$file->getId()}/delete/");
    }

    public function getFileStreamLink(File $file)
    {
        $httpQuery = http_build_query(['type' => "stream"]);
        $encodedName = urlencode($file->getClientFilename());
        return $this->generateLink("/file/get/{$file->getId()}/{$encodedName}?{$httpQuery}");
    }

    public function getFileThumbnailLink(File $file)
    {
        $encodedName = rawurlencode($file->getThumbnailName());
        return "/thumbnails/{$encodedName}";
    }

    public function getCommentFormReplyLink($fileId)
    {
        return $this->generateLink("/file/{$fileId}/");
    }

    public function getCommentReplyLink(Comment $comment)
    {
        $httpQuery = http_build_query(['reply' => $comment->getId()]);
        return $this->getCommentFormReplyLink($comment->getFileId()) . "?{$httpQuery}";
    }

    public function generateLink($link)
    {
        return "/" . $this->lang . (($link[0] == "/") ? $link : "/" . $link);
    }
}