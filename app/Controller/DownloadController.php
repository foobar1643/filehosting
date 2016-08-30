<?php

namespace Filehosting\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\Stream as Stream;
use Filehosting\Entity\File;
use Filehosting\Helper\FileHelper;
use Filehosting\Exception\FileNotFoundException;

/**
 * Callable, provides a way to download files.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class DownloadController
{
    /** @var FileMapper $fileMapper FileHelper instance. */
    private $fileMapper;
    /** @var PathingHelper $pathingHelper PathingHelper instance. */
    private $pathingHelper;
    /** @var FileHelper $fileHelper FileHelper instance. */
    private $fileHelper;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(\Slim\Container $c)
    {
        $this->fileMapper = $c->get('FileMapper');
        $this->pathingHelper = $c->get('PathingHelper');
        $this->fileHelper = $c->get('FileHelper');
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @todo Refactor this code.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     * @param array $args Array with additional arguments.
     *
     * @throws NotFoundException if file not found.
     *
     * @return Response
     */
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

    /**
     * Returns a Response object with headers for file download.
     *
     * @param Response $response Slim Framework response instance.
     *
     * @return Response
     */
    private function getDownloadHeaders(Response $response)
    {
        return $response->withHeader('Content-Description', "File Transfer")
            ->withHeader('Content-Type', "application/octet-stream")
            ->withHeader('Cache-Control', "must-revalidate")
            ->withHeader('Pragma', "public");
    }
}
