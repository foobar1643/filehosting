<?php

namespace Filehosting\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Entity\File;
use Filehosting\Helper\LanguageHelper;
use Filehosting\Helper\CookieHelper;
use Filehosting\Helper\AuthHelper;
use Slim\Container;

/**
 * Callable, provides a way to upload files.
 *
 * @package Filehosting\Controller
 * @author foobar1643 <foobar76239@gmail.com>
 */
class UploadController
{
    /** @var mixed $view View object. */
    private $view;
    /** @var FileHelper $fileHelper FileHelper instance. */
    private $fileHelper;
    /** @var Config $config Application config instance. */
    private $config;
    /** @var Validation $validator Validation object. */
    private $validator;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(Container $c)
    {
        $this->view = $c->get('view');
        $this->config = $c->get('config');
        $this->fileHelper = $c->get('FileHelper');
        $this->validator = $c->get('Validation');
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     * @param array $args Array with additional arguments.
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $errors = null;
        if ($request->isPost()) {
            $authHelper = new AuthHelper(new CookieHelper($request, $response));
            $file = $this->createFromRequest($request);
            $errors = $this->validator->validateUploadedFile($file->getUploadedFile());
            if (!$errors) {
                if (!$authHelper->isAuthorized()) {
                    $response = $authHelper->authorizeUser();
                }
                $file->setAuthToken($authHelper->getUserToken());
                $file = $this->fileHelper->uploadFile($file);
                return $response->withRedirect("/{$args['lang']}/file/{$file->getId()}/");
            }
        }
        return $this->view->render(
            $response,
            'upload.twig',
            [
                'sizeLimit' => $this->config->getValue('app', 'sizeLimit'),
                'errors' => $errors,
                'langHelper' => new LanguageHelper($request)
            ]
        );
    }

    /**
     * Creates a File entity from a Request instance.
     *
     * @param Request $request Slim Framework request instance.
     *
     * @return File
     */
    private function createFromRequest(Request $request)
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (array_key_exists("uploaded-file", $uploadedFiles)) {
            $file = new File();
            $file->setUploadedFile($uploadedFiles['uploaded-file'])
                //notes: Default uploader name for user, displays in file information section
                ->setUploader(dcgettext('en_US', 'Anonymous', LC_MESSAGES));
            return $file;
        }
        return new File();
    }
}
