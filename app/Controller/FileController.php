<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\FigRequestCookies;
use \Filehosting\Entity\File;

class FileController
{
    private $container;
    private $view;
    private $fileHelper;
    private $fileMapper;
    private $langHelper;
    private $authHelper;
    private $idHelper;
    private $commentHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
        $this->view = $c->get('view');
        $this->langHelper = $c->get('LanguageHelper');
        $this->fileHelper = $c->get('FileHelper');
        $this->fileMapper = $c->get('FileMapper');
        $this->commentHelper = $c->get('CommentHelper');
        $this->authHelper = $c->get('AuthHelper');
        $this->idHelper = $c->get('IdHelper');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $commentErrors = null;
        $params = $request->getQueryParams();
        $replyTo = isset($params['reply']) ? strval($params['reply']) : NULL;
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
            'canManageFile' => $this->authHelper->canManageFile($request, $file),
            'comments' => $this->commentHelper->getComments($file->getId()),
            'messageFormatter' => new \MessageFormatter(\Locale::getDefault(), _("{0, plural, =0{No downloads} one{# download} other{# downloads}}")),
            'lang' => $args['lang'],
            'showLangMessage' => $this->langHelper->canShowLangMsg($request),
            'csrf_token' => FigRequestCookies::get($request, "csrf_token")->getValue()]);
    }

    public function deleteFile(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        $file = $this->fileMapper->getFile($args['id']);
        if($this->fileHelper->fileExists($args['id']) && $this->authHelper->canManageFile($request, $file)) {
            $this->fileHelper->deleteFile($file);
        }
        return $response->withRedirect("/{$args['lang']}/");
    }
}