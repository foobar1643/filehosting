<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\Stream as Stream;
use \Filehosting\Entity\File;
use \Filehosting\Helper\FileHelper;
use \Filehosting\Exception\FileNotFoundException;

class DownloadController
{
    private $container;
    private $fileMapper;
    private $pathingHelper;
    private $fileHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->fileMapper = $c->get('FileMapper');
        $this->pathingHelper = $c->get('PathingHelper');
        $this->fileHelper = $c->get('FileHelper');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        if($this->fileHelper->fileExists($args['id'])) {
            $file = $this->fileMapper->getFile($args['id']);
            $filePath = $this->pathingHelper->getPathToFile($file);
            if(!isset($params['type']) || $params['type'] != 'stream') {
                $file->setDownloads($file->getDownloads() + 1);
                $this->fileMapper->updateFile($file);
            }
            $response = $this->getDownloadHeaders($response);
            $response = $response->withHeader('Content-Length', filesize($filePath));
            try {
                $response = $this->fileHelper->getXsendfileHeaders($request, $response, $file);
            } catch(\Exception $e) {
                $fileStream = new Stream(fopen($filePath, "r"));
                $response = $response->write($fileStream->getContents());
            }
            return $response;
        } else {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }

    private function getDownloadHeaders(Response $response)
    {
        return $response->withHeader('Content-Description', "File Transfer")
            ->withHeader('Content-Type', "application/octet-stream")
            ->withHeader('Cache-Control', "must-revalidate")
            ->withHeader('Pragma', "public");
    }
}
