<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;

class UploadController
{
    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $errors = null;
        if($request->isPost() && isset($_FILES)) {
            $fileHelper = $this->container->get('FileHelper');
            $validator = $this->container->get('Validation');
            $authHelper = $this->container->get('AuthHelper');
            $file = new File();
            $errors = $validator->validateUploadForm($_FILES);
            if(!$errors) {
                if(!$authHelper->isAuthorized($request)) { // user is not authorized
                    $response = $authHelper->authorizeUser($response);
                }
                $file->setName($_FILES['filename']['name'])
                    ->setOriginalName($_FILES['filename']['tmp_name'])
                    ->setUploader('Anonymous')
                    ->setAuthToken($authHelper->getUserToken($request));
                $file = $fileHelper->createFile($file, false);
                return $response->withHeader('Location', "/file/{$file->getId()}");
            }
        }
        return $this->container->get('view')->render($response, 'upload.twig',
            ['sizeLimit' => $this->container->get('config')->getValue('app', 'sizeLimit'),
            'errors' => $errors]);
    }
}