<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;

class FileController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function viewFile(Request $request, Response $response, $args)
    {
        $fileMapper = $this->container->get('FileMapper');
        $commentHelper = $this->container->get('CommentHelper');
        $authHelper = $this->container->get('AuthHelper');
        $idHelper = $this->container->get('IdHelper');
        $file = $fileMapper->getFile($args['id']);
        if($file == false) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        } else {
            $idHelper->analyzeFile($file);
            if(isset($args['replyTo'])) {
                $replyTo = $args['replyTo'];
            } else {
                $params = $request->getQueryParams();
                $replyTo = isset($params['reply']) ? strval($params['reply']) : NULL;
            }
            return $this->container->get('view')->render($response, 'file.twig', [
                'file' => $file,
                'idHelper' => $idHelper,
                'replyTo' => $replyTo,
                'commentErrors' => isset($args['commentErrors']) ? $args['commentErrors'] : NULL,
                'csrf' => ["name" => $request->getAttribute('csrf_name'), "value" => $request->getAttribute('csrf_value')],
                'authToken' => $authHelper->getUserToken($request),
                'comments' => $commentHelper->getComments($file->getId())]);
        }
    }

    public function deleteFile(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        $fileMapper = $this->container->get('FileMapper');
        $fileHelper = $this->container->get('FileHelper');
        $authHelper = $this->container->get('AuthHelper');
        $file = $fileMapper->getFile($args['id']);
        if($file != null && $authHelper->canDeleteFile($request, $file)) {
            $fileHelper->deleteFile($file);
        }
        return $response->withHeader('Location', "/");
    }
}