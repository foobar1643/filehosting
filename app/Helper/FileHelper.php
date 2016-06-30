<?php

namespace Filehosting\Helper;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;
use \Filehosting\Exception\FileUploadException;
use \Filehosting\Database\FileMapper;
use \Filehosting\Database\SearchGateway;

class FileHelper
{
    private $config;
    private $previewHelper;
    private $pathingHelper;
    private $fileMapper;
    private $commentMapper;
    private $searchGateway;
    private $idHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->config = $c->get('config');
        $this->previewHelper = $c->get('PreviewHelper');
        $this->pathingHelper = $c->get('PathingHelper');
        $this->fileMapper = $c->get('FileMapper');
        $this->commentMapper = $c->get('CommentMapper');
        $this->searchGateway = $c->get('SearchGateway');
        $this->idHelper =  $c->get('IdHelper');
    }

    public function uploadFile(File $file)
    {
        $this->fileMapper->beginTransaction();
        $file->setId($this->fileMapper->createFile($file));
        try {
            $this->isFileFolderAvailable($this->pathingHelper->getPathToFileFolder($file)); // throws FileUploadException
            $file->moveTo($this->pathingHelper->getPathToFile($file)); //  throws \InvalidArgumentException and \RuntimeException
        } catch(\Exception $e) {
            $this->fileMapper->rollBack();
            throw new $e;
        }
        $this->searchGateway->indexNewFile($file);
        $fileInfo = $this->idHelper->analyzeFile($file);
        if($this->idHelper->isPreviewable($fileInfo)) {
            $this->previewHelper->generatePreview($file);
        }
        $this->fileMapper->commit();
        return $file;
    }

    public function deleteFile(File $file)
    {
        $fileInfo = $this->idHelper->analyzeFile($file);
        $this->commentMapper->purgeComments($file->getId());
        $this->searchGateway->deleteIndexedFile($file);
        if($this->idHelper->isPreviewable($fileInfo)) {
            $this->previewHelper->deletePreview($file);
        }
        $this->fileMapper->deleteFile($file);
        if(!unlink($this->pathingHelper->getPathToFile($file))) {
            throw new \Exception(_("Can't unlink file. Try again or contact server administrators."));
        }
        return true;
    }

    public function fileExists($fileId)
    {
        $file = $this->fileMapper->getFile($fileId);
        if($file && file_exists($this->pathingHelper->getPathToFile($file))) {
            return true;
        }
        return false;
    }

    public function getXsendfileHeaders(Request $request, Response $response, File $file)
    {
        $serverParams = $request->getServerParams();
        if($this->config->getValue('app', 'enableXsendfile') == 1) {
            if(strpos($serverParams["SERVER_SOFTWARE"], "nginx") !== false) {
                return $response->withHeader('X-Accel-Redirect', $this->pathingHelper->getXaccelPath($file))
                    ->withHeader('X-Accel-Charset', "utf-8");
            } else if(strpos($serverParams["SERVER_SOFTWARE"], "apache") !== false && in_array("mod_xsendfile", apache_get_modules())) {
                return $response->withHeader('X-Sendfile', $this->pathingHelper->getPathToFile($file));
            }
        }
        throw new \Exception(_("X-Sendfile either is not enabled or not supported by the server."));
    }

    private function isFileFolderAvailable($fileFolder)
    {
        if(!is_dir($fileFolder)) {
            if(!mkdir($fileFolder)) {
                throw new FileUploadException(UPLOAD_ERR_CANT_WRITE);
            }
        }
        return true;
    }
}
