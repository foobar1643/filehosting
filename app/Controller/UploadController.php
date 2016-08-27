<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\UploadedFile;
use \Filehosting\Entity\File;
use \Filehosting\Helper\LanguageHelper;
use \Filehosting\Helper\CookieHelper;
use \Filehosting\Helper\AuthHelper;

class UploadController
{
    private $view;
    private $fileHelper;
    private $config;
    private $validator;

    public function __construct(\Slim\Container $c)
    {
        $this->view = $c->get('view');
        $this->config = $c->get('config');
        $this->fileHelper = $c->get('FileHelper');
        $this->validator = $c->get('Validation');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $errors = null;
        if($request->isPost()) {
            $authHelper = new AuthHelper(new CookieHelper($request, $response));
            $file = $this->createFromRequest($request);
            $errors = $this->validator->validateUploadedFile($file->getUploadedFile());
            if(!$errors) {
                if(!$authHelper->isAuthorized()) {
                    $response = $authHelper->authorizeUser();
                }
                $file->setAuthToken($authHelper->getUserToken());
                $file = $this->fileHelper->uploadFile($file);
                return $response->withRedirect("/{$args['lang']}/file/{$file->getId()}/");
            }
        }
        return $this->view->render($response, 'upload.twig',
            ['sizeLimit' => $this->config->getValue('app', 'sizeLimit'),
            'errors' => $errors, 'langHelper' => new LanguageHelper($request)]);
    }

    private function createFromRequest(Request $request)
    {
        $uploadedFiles = $request->getUploadedFiles();
        if(array_key_exists("uploaded-file", $uploadedFiles)) {
            $file = new File();
            $file->setUploadedFile($uploadedFiles['uploaded-file'])
                //notes: Default uploader name for user, displays in file information section 
                ->setUploader(dcgettext('en_US', 'Anonymous', LC_MESSAGES));
            return $file;
        }
        return new File();
    }
}