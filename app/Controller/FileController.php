<?php

namespace Filehosting\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Helper\LanguageHelper;
use Filehosting\Helper\CookieHelper;
use Filehosting\Helper\AuthHelper;
use Filehosting\Translator;
use Slim\Container;
use Slim\Exception\NotFoundException;

/**
 * Callable, provides a way to download files.
 *
 * @todo Refactor this code.
 * @todo Relocate comments selection to CommentController
 *
 * @package Filehosting\Controller
 * @author foobar1643 <foobar76239@gmail.com>
 */
class FileController
{
    /**
     * @var \Slim\Container Slim Framework DI container.
     */
    private $container;

    /**
     * @var mixed View instance.
     */
    private $view;

    /**
     * @var \Filehosting\Helper\FileHelper FileHelper instance.
     */
    private $fileHelper;

    /**
     * @var \Filehosting\Database\FileMapper FileMapper instance.
     */
    private $fileMapper;

    /**
     * @var \Filehosting\Helper\IdHelper IdHelper instance.
     */
    private $idHelper;

    /**
     * @var \Filehosting\Helper\CommentHelper CommentHelper instance.
     */
    private $commentHelper;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->view = $c->get('view');
        $this->fileHelper = $c->get('FileHelper');
        $this->fileMapper = $c->get('FileMapper');
        $this->commentHelper = $c->get('CommentHelper');
        $this->idHelper = $c->get('IdHelper');
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
     * @throws \Slim\Exception\NotFoundException if file not found.
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $cookieHelper = new CookieHelper($request, $response);
        $authHelper = new AuthHelper($cookieHelper);
        $commentErrors = null;
        $replyTo = $request->getParam('reply');
        $file = $this->fileMapper->getFile($args['id']);
        if (!$this->fileHelper->fileExists($args['id'])) {
            throw new NotFoundException($request, $response);
        }
        if ($request->isPost()) {
            $commentController = new CommentController($this->container);
            $postResult = $commentController->__invoke($request, $response, $args);
            $commentErrors = $postResult["errors"];
            $replyTo = isset($commentErrors) ? $postResult["comment"]->getParentId() : null;
            if ($request->isXhr()) {
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
            'translator' => new Translator(\Locale::getDefault()),
            'langHelper' => new LanguageHelper($request),
            'csrf_token' => $cookieHelper->getRequestCookie('csrf_token')]);
    }

    /**
     * Provides a way to delete files from the database.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     * @param array $args Array with additional arguments.
     *
     * @return Response
     */
    public function deleteFile(Request $request, Response $response, array $args)
    {
        $authHelper = new AuthHelper(new CookieHelper($request, $response));
        $file = $this->fileMapper->getFile($args['id']);
        if ($this->fileHelper->fileExists($args['id']) && $authHelper->canManageFile($file)) {
            $this->fileHelper->deleteFile($file);
        }
        return $response->withRedirect("/{$args['lang']}/");
    }
}
