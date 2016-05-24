<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;
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
        $fileMapper = $this->container->get('FileMapper');
        $pathingHelper = $this->container->get('PathingHelper');
        $config = $this->container->get('config');
        $params = $request->getQueryParams();
        $file = $fileMapper->getFile($args['id']);
        $filePath = $pathingHelper->getPathToFile($file);
        if($file != null && file_exists($filePath)) {
            if(!isset($params['flag']) || $params['flag'] != 'nocount') {
                $file->setDownloads($file->getDownloads() + 1);
                $fileMapper->updateFile($file);
            }
            $responseHeader = $response->withHeader('Content-Description', "File Transfer") // ->withHeader('Content-Disposition', "attachment; filename={$file->getName()}")
                ->withHeader('Content-Type', "application/octet-stream")
                ->withHeader('Cache-Control', "must-revalidate")
                ->withHeader('Pragma', "public")
                ->withHeader('Content-Length', filesize($filePath));
            if($config->getValue('app', 'enableXsendfile') == 1) {
                if(strpos($_SERVER["SERVER_SOFTWARE"], "nginx") !== false) {
                    $responseHeader = $responseHeader->withHeader('X-Accel-Redirect', $pathingHelper->getXaccelPath($file))
                    ->withHeader('X-Accel-Charset', "utf-8");
                } else if(strpos($_SERVER["SERVER_SOFTWARE"], "apache") !== false && in_array("mod_xsendfile", apache_get_modules())) {
                    $responseHeader = $responseHeader->withHeader('X-Sendfile', $pathingHelper->getPathToFile($file));
                }
            } else {
                readfile($filePath);
            }
            return $responseHeader;
        } else {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }
}
