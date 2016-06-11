<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;

class UploadController
{
    private $view;
    private $fileHelper;
    private $config;
    private $validator;
    private $authHelper;
    private $langHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->view = $c->get('view');
        $this->config = $c->get('config');
        $this->fileHelper = $c->get('FileHelper');
        $this->langHelper = $c->get('LanguageHelper');
        $this->authHelper = $c->get('AuthHelper');
        $this->validator = $c->get('Validation');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $errors = null;
        if($request->isPost()) {
            $uploadedFiles = $request->getUploadedFiles();
            $file = new File();
            $errors = $this->validator->validateUploadedFiles($uploadedFiles);
            if(!$errors) {
                if(!$this->authHelper->isAuthorized($request)) {
                    $response = $this->authHelper->authorizeUser($response);
                }
                $file->setName($uploadedFiles["uploaded-file"]->getClientFilename())
                    ->setUploadObject($uploadedFiles["uploaded-file"])
                    ->setUploader('Anonymous')
                    ->setAuthToken($this->authHelper->getUserToken($request));
                $file = $this->fileHelper->uploadFile($file, $uploadedFiles["uploaded-file"]);
                return $response->withRedirect("/{$args['lang']}/file/{$file->getId()}/");
            }
        }
        return $this->view->render($response, 'upload.twig',
            ['sizeLimit' => $this->config->getValue('app', 'sizeLimit'),
            'errors' => $errors, 'lang' => $args['lang'],
            'showLangMessage' => $this->langHelper->canShowLangMsg($request)]);
    }
}