<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\FigRequestCookies;
use \Filehosting\Model\File;
use \Filehosting\Helper\TokenGenerator;

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
            $uploadHelper = $this->container->get('UploadHelper');
            $file = new File();
            $dateTime = new \DateTime("now");
            $dateTime->add(new \DateInterval("P10D")); // 10 days
            $errors = $uploadHelper->validateUpload($_FILES);
            if(!$errors) {
                $authCookie = FigRequestCookies::get($request, 'auth');
                if($authCookie->getValue() == null) {
                    $token = TokenGenerator::generateToken(45);
                } else {
                    $token = $authCookie->getValue();
                }
                $file->setName($_FILES['filename']['name']);
                $file->setOriginalName($_FILES['filename']['tmp_name']);
                $file->setUploader('Anonymous');
                $file->setAuthToken($token);
                $response = FigResponseCookies::set($response,
                    SetCookie::create('auth')->withValue($file->getAuthToken())
                    ->withExpires($dateTime->format(\DateTime::COOKIE))->withPath('/'));
                $file = $fileHelper->createFile($file, false);
                return $response->withHeader('Location', "/file/{$file->getId()}");
            }
        }
        return $this->container->get('view')->render($response, 'upload.twig',
            ['sizeLimit' => $this->container->get('config')->getValue('app', 'sizeLimit'),
            'errors' => $errors]);
    }
}