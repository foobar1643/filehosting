<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\File;
use \Filehosting\Helper\FileHelper;
use \Filehosting\Exception\FileNotFoundException;

class DownloadController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $file = new File();
        $fileHelper = new FileHelper();
        $fileMapper = $this->container->get('FileMapper');
        $file = $fileMapper->getFile($args['id']);
        $filePath = "storage/{$file->getFolder()}/{$fileHelper->getDiskName($file)}";
        if($file != null && file_exists($filePath)) {
            $file->setDownloads($file->getDownloads() + 1);
            $fileMapper->updateFile($file);
            $responseHeader = $response->withHeader('Content-Description', "File Transfer")
                ->withHeader('Content-Type', "application/octet-stream")
                ->withHeader('Content-Disposition', "attachment; filename={$file->getName()}")
                ->withHeader('Expires', "0")
                ->withHeader('Cache-Control', "must-revalidate")
                ->withHeader('Pragma', "public")
                ->withHeader('Content-Length', filesize($filePath));
            readfile($filePath);
            return $responseHeader;
        } else {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }
}
