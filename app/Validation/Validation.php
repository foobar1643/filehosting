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
            $errors['noFile'] = "Заполните все поля и попробуйте еще раз.";
        }
        if(!is_null($uploadedFile) && $uploadedFile->getSize() > $config->getValue('app', 'sizeLimit') * 1000000) {
            $errors['sizeLimit'] = "Размер файла превышает максимально допустимый.";
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
            $errors["noFile"] = "Файла с таким ID не существует.";
        }
        if(trim($comment->getCommentText()) == "") {
            $errors["noComment"] = "Вы не заполнили поле комментария.";
        }
        if(strlen(trim($comment->getCommentText())) > 300) {
            $errors["commentTooBig"] = "Комментарий должен быть не длиннее 300 символов.";
        }
        if($comment->getParentId() != null && !isset($parentComment)) {
            $errors["noParent"] = "Родительского комментария не существует.";
        }
        if($comment->getParentId() != null && isset($parentComment) && $parentComment->getDepth() >= 5) {
            $errors["maxDepthReached"] = "Превышена максимальная глубина комментария.";
        }
        return $errors;
    }
}