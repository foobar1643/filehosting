<?php

namespace Filehosting\Validation;

use \Filehosting\Entity\File;
use \Filehosting\Entity\Comment;
use \Filehosting\Helper\UploadHelper;

class Validation
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function validateUploadedFiles($uploadedFiles)
    {
        $config = $this->container->get('config');
        $errors = null;
        $uploadedFile = array_key_exists("uploaded-file", $uploadedFiles) ? $uploadedFiles["uploaded-file"] : NULL;
        if(is_null($uploadedFile)) {
            $errors['noFile'] = _("Attach a file and try again.");
        }
        if(!is_null($uploadedFile) && $uploadedFile->getSize() > $config->getValue('app', 'sizeLimit') * 1000000) {
            $errors['sizeLimit'] = _("File size is exceeding the maximum.");
        }
        if(!is_null($uploadedFile) && $uploadedFile->getError() != UPLOAD_ERR_OK) {
            $errors['form'] = UploadHelper::parseCode($uploadedFile->getError());
        }
        return $errors;
    }

    public function validateComment(Comment $comment)
    {
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $parentComment = null;
        $errors = null;
        if($comment->getParentId() != null) {
            $parentComment = $commentMapper->getComment($comment->getParentId());
        }
        if($fileMapper->getFile($comment->getFileId()) == false) {
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