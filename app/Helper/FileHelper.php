<?php

namespace Filehosting\Helper;

use \GetId3\GetId3Core as GetId3;
use \Filehosting\Entity\File;
use \Filehosting\Exception\FileUploadException;
use \Filehosting\Database\FileMapper;
use \Filehosting\Database\SearchGateway;

class FileHelper
{
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
        $pathingHelper = $this->container->get('PathingHelper');
        $fileMapper = $this->container->get('FileMapper');
        $searchGateway = $this->container->get('SearchGateway');
        $idHelper =  $this->container->get('IdHelper');
        $fileMapper->beginTransaction();
        $file->setId($fileMapper->createFile($file));
        if(!is_dir("{$pathingHelper->getPathToStorage()}/{$file->getFolder()}")) {
            if(!mkdir("{$pathingHelper->getPathToStorage()}/{$file->getFolder()}")) {
                $fileMapper->rollBack();
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        if($adminFlag == true) {
            $fileMoved = copy($file->getOriginalName(), $pathingHelper->getPathToFile($file));
        } else {
            $fileMoved = move_uploaded_file($file->getOriginalName(), $pathingHelper->getPathToFile($file));
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
        $pathingHelper = $this->container->get('PathingHelper');
        if(!unlink("{$pathingHelper->getPathToStorage()}/{$file->getFolder()}/{$file->getDiskName()}")) {
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
}