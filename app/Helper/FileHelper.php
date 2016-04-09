<?php

namespace Filehosting\Helper;

use \GetId3\GetId3Core as GetId3;
use \Filehosting\Model\File;
use \Filehosting\Exception\FileUploadException;
use \Filehosting\Database\FileMapper;
use \Filehosting\Database\SearchGateway;

class FileHelper
{
    const WHITELISTED_EXTENSIONS = ["jpg", "jpeg", "png", "gif", "webm", "mp3", "mp4"];

    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function createFile(File $file, $adminFlag)
    {
        $fileMoved = false;
        $getId3 = new GetId3();
        $previewHelper = $this->container->get('PreviewHelper');
        $fileMapper = $this->container->get('FileMapper');
        $searchGateway = $this->container->get('SearchGateway');
        $idHelper = new IdHelper($getId3, $this);
        $fileMapper->beginTransaction();
        $file->setId($fileMapper->createFile($file));
        if(!is_dir("{$this->getPathToStorage()}/{$file->getFolder()}")) {
            if(!mkdir("{$this->getPathToStorage()}/{$file->getFolder()}")) {
                $fileMapper->rollBack();
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        if($adminFlag == true) {
            $fileMoved = copy($file->getOriginalName(), $this->getPathToFileFolder($file));
        } else {
            $fileMoved = move_uploaded_file($file->getOriginalName(), $this->getPathToFileFolder($file));
        }
        if($fileMoved) {
            $fileMapper->commit();
            $searchGateway->insertRtValue($file->getId(), $file->getName());
            $idHelper->analyzeFile($file);
            if($idHelper->isPreviewable()) {
                $previewHelper->generatePreview($file);
            }
        } else {
            $fileMapper->rollBack();
            throw new FileUploadException(UPLOAD_ERR_NO_TMP_DIR);
        }
        return $file;
    }

    public function deleteFile(File $file)
    {
        if(!unlink("{$this->getPathToStorage()}/{$file->getFolder()}/{$this->getDiskName($file)}")) {
            throw new \Exception('Произошла системная ошибка. Попробуйте позже или обратитесь к администратору.');
        }
        $fileMapper = $this->container->get('FileMapper');
        $commentMapper = $this->container->get('CommentMapper');
        $searchGateway = $this->container->get('SearchGateway');

        $commentMapper->deleteComments($file->getId());
        $fileMapper->deleteFile($file);
        $searchGateway->deleteRtValue($file->getId());
        return true;
    }

    public function getLinkToPreview(File $file)
    {
        return "/thumbnails/thumb_{$this->getDiskName($file)}";
    }

    public function canDelete($fileToken)
    {
        if(isset($_COOKIE['auth']) && $_COOKIE['auth'] == $fileToken) {
            return true;
        }
        return false;
    }

    public function getPathToStorage()
    {
        return "../storage";
    }

    public function getPathToFileFolder(File $file)
    {
        return "{$this->getPathToStorage()}/{$file->getFolder()}/{$this->getDiskName($file)}";
    }

    public function getDiskName(File $file)
    {
        $fileExtension = $file->getExtention();
        $normalized = $file->getName();
        if(strlen($normalized) > 20) {
            $normalized = $file->getStrippedName();
            $normalized = substr($normalized, 0, 20);
            $normalized .= "." . $fileExtension;
        }
        if(!in_array($fileExtension, self::WHITELISTED_EXTENSIONS)) {
            $normalized .= ".txt";
        }
        $normalized = substr_replace($normalized, "{$file->getId()}_", 0, 0);
        return $normalized;
    }
}