<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Entity\File;
use \Filehosting\Helper\LanguageHelper;
use \Filehosting\Helper\CookieHelper;
use \Filehosting\Helper\AuthHelper;
use \Filehosting\Translator;

class FileController
{
    private $container;
    private $view;
    private $fileHelper;
    private $fileMapper;
    private $idHelper;
    private $commentHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
        $this->view = $c->get('view');
        $this->fileHelper = $c->get('FileHelper');
        $this->fileMapper = $c->get('FileMapper');
        $this->commentHelper = $c->get('CommentHelper');
        $this->idHelper = $c->get('IdHelper');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $cookieHelper = new CookieHelper($request, $response);
        $authHelper = new AuthHelper($cookieHelper);
        $commentErrors = null;
        $replyTo = $request->getParam('reply');
        $file = $this->fileMapper->getFile($args['id']);
        if(!$this->fileHelper->fileExists($args['id'])) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        if($request->isPost()) {
            $commentController = new CommentController($this->container);
            $postResult = $commentController->__invoke($request, $response, $args);
            $commentErrors = $postResult["errors"];
            $replyTo = isset($commentErrors) ? $postResult["comment"]->getParentId() : NULL;
            if($request->isXhr()) {
                return $response->withJson($postResult);
            }
        }
        return $this->view->render($response, 'file.twig', [
            'file' => $file,
            'idHelper' => $this->idHelper,
            'fileInfo' => $this->idHelper->analyzeFile($file),
            'replyTo' => $replyTo,
            'commentErrors' => $commentErrors,
            'canManageFile' => $authHelper->canManageFile($file),
            'comments' => $this->commentHelper->getComments($file->getId()),
            'lang' => $args['lang'],
            'translator' => new Translator(\Locale::getDefault()),
            'langHelper' => new LanguageHelper($request),
            'csrf_token' => $cookieHelper->getRequestCookie('csrf_token')]);
    }

    public function deleteFile(Request $request, Response $response, $args)
    {
        $authHelper = new AuthHelper(new CookieHelper($request, $response));
        $formData = $request->getParsedBody();
        $file = $this->fileMapper->getFile($args['id']);
        if($this->fileHelper->fileExists($args['id']) && $authHelper->canManageFile($file)) {
            $this->fileHelper->deleteFile($file);
        }
        return $response->withRedirect("/{$args['lang']}/");
    }
}