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
        $this->fileHelper = $c->get('FileHelper');
    }

    /**
     * A method that allows to use this class as a callable.
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
        if(!$this->fileHelper->fileExists($args['id'])) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        } else {
            $file = $this->fileMapper->getFile($args['id']);
            $file = ($request->getQueryParam('type') !== 'stream') ? $this->fileHelper->incrementDownloadsCounter($file) : $file;
            $response = $fileHelper->getDownloadHeaders($response, $file);
            try {
                $response = $this->fileHelper->getXsendfileHeaders($request, $response, $file);
            } catch(\Exception $e) {
                $response = $this->fileHelper->writeToResponse($response, $file);
            }
            return $response;
        }
    }
}
