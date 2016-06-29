<?php

namespace Filehosting\Validation;

use \Filehosting\Entity\File;
use \Filehosting\Entity\Comment;
use \Filehosting\Helper\UploadHelper;

class Validation
{
    private $container;
    private $commentMapper;
    private $fileMapper;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
        $this->commentMapper = $c->get('CommentMapper');
        $this->fileMapper = $c->get('FileMapper');
    }

    public function validateFile(File $file)
    {
        $config = $this->container->get('config');
        $errors = null;
        if(is_null($file->getClientFilename())) {
            $errors['noFile'] = _("Attach a file and try again.");
        }
        if(!is_null($file->getClientFilename()) && $file->getSize() > $config->getValue('app', 'sizeLimit') * 1000000) {
            $errors['sizeLimit'] = _("File size is exceeding the maximum.");
        }
        if(!is_null($file->getClientFilename()) && $file->getError() != UPLOAD_ERR_OK) {
            $errors['other'] = UploadHelper::parseCode($file->getError());
        }
        return $errors;
    }

    public function validateComment(Comment $comment)
    {
        $parentComment = null;
        $errors = null;
        if($comment->getParentId() != null) {
            $parentComment = $this->commentMapper->getComment($comment->getParentId());
        }
        if($this->fileMapper->getFile($comment->getFileId()) == false) {
            $errors["noFile"] = _("File does not exists.");
        }
        if(trim($comment->getCommentText()) == "") {
            $errors["noComment"] = _("The comment field is empty.");
        }
        if(strlen(trim($comment->getCommentText())) > 300) {
            $errors["commentTooBig"] = _("Comment mustn't be longer than 300 symbols.");
        }
        if($comment->getParentId() != null && !isset($parentComment)) {
            $errors["noParent"] = _("Parent comment does not exists.");
        }
        if($comment->getParentId() != null && isset($parentComment) && $parentComment->getDepth() >= 5) {
            $errors["maxDepthReached"] = _("Maximum comment depth reached.");
        }
        return $errors;
    }
}