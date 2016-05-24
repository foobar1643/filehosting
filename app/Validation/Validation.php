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

    public function validateUploadForm($postData)
    {
        $config = $this->container->get('config');
        $errors = null;
        if(!isset($postData['filename'])) {
            $errors['noFile'] = "Заполните все поля и попробуйте еще раз.";
        }
        if(isset($postData['filename']) && $postData['filename']['size'] > $config->getValue('app', 'sizeLimit') * 1000000) {
            $errors['sizeLimit'] = "Размер файла превышает максимально допустимый.";
        }
        if(isset($postData['filename']) && $postData['filename']['error'] != UPLOAD_ERR_OK) {
            $errors['form'] = UploadHelper::parseCode($postData['filename']['error']);
        }
        return $errors;
    }

    public function validateCommentForm($fileId, $postData)
    {
        // post data - comment text, parent comment id
        $commentMapper = $this->container->get('CommentMapper');
        $fileMapper = $this->container->get('FileMapper');
        $parentComment = null;
        $errors = null;
        if(isset($postData["parentComment"])) {
            $parentComment = $commentMapper->getComment($postData["parentComment"]);
        }
        if($fileMapper->getFile($fileId) == false) {
            $errors["noFile"] = "Файла с таким ID не существует.";
        }
        if(!isset($postData["comment"]) || trim($postData["comment"]) == "") {
            $errors["noComment"] = "Вы не заполнили поле комментария.";
        }
        if(isset($postData["comment"]) && strlen(trim($postData["comment"])) > 300) {
            $errors["commentTooBig"] = "Комментарий должен быть не длиннее 300 символов.";
        }
        if(isset($postData["parentComment"]) && !isset($parentComment)) {
            $errors["noParent"] = "Родительского комментария не существует.";
        }
        if(isset($postData["parentComment"]) && isset($parentComment) && $parentComment->getDepth() >= 5) {
            $errors["maxDepthReached"] = "Превышена максимальная глубина комментария.";
        }
        return $errors;
    }
}