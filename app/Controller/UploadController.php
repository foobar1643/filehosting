<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\File;
use \Filehosting\Helper\FileHelper;
use \Filehosting\Helper\AuthHelper;
use \Filehosting\Helper\PreviewHelper;
use \Filehosting\Helper\TokenGenerator;
use \Filehosting\Exception\FileUploadException;

class UploadController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        if(!isset($_FILES['filename'])) {
            throw new FileUploadException(UPLOAD_ERR_NO_FILE);
        }
        if($_FILES['filename']['error'] == "UPLOAD_ERR_OK") {
            $fileMapper = $this->container->get('FileMapper');
            $searchGateway = $this->container->get('SearchGateway');
            $config = $this->container->get('config');
            if($_FILES['filename']['size'] > $config->getValue('app', 'sizeLimit') * 1000000) {
                throw new FileUploadException(UPLOAD_ERR_FORM_SIZE);
            }
            $file = new File();
            $fileHelper = new FileHelper();
            $authHelper = new AuthHelper();
            $file->setName($_FILES['filename']['name']);
            $file->setAuthToken($authHelper->getAuthCookie());
            $fileMapper->beginTransaction();
            $file->setId($fileMapper->createFile($file));
            try {
                $fileHelper->isFolderAvailable($file);
            } catch(FileUploadException $e) {
                $fileMapper->rollBack();
                throw $e;
            }
            if(move_uploaded_file($_FILES['filename']['tmp_name'], "storage/{$file->getFolder()}/{$fileHelper->getDiskName($file)}")) {
                $fileMapper->commit();
                $searchGateway->insertRtValue($file->getId(), $file->getName());
                $previewHelper = new PreviewHelper($file);
                $previewHelper->generatePreview();
                $responseHeader = $response->withHeader('Location', "/file/{$file->getId()}");
                return $responseHeader;
            } else {
                $fileMapper->rollBack();
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        } else {
            throw new FileUploadException($_FILES['filename']['error']);
        }
    }
}