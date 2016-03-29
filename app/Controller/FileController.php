<?php

namespace Filehosting\Controller;

use \GetId3\GetId3Core as GetId3;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\File;
use \Filehosting\Helper\FileHelper;
use \Filehosting\Helper\CsrfHelper;
use \Filehosting\Helper\AuthHelper;
use \Filehosting\Helper\CommentHelper;
use \Filehosting\Exception\FileNotFoundException;

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
        $file = $fileMapper->getFile($args['id']);
        if($file != null) {
            $error = false;
            $commentGateway = $this->container->get('CommentMapper');
            $getParams = $request->getQueryParams();
            $fileHelper = new FileHelper();
            $csrfHelper = new CsrfHelper();
            $authHelper = new AuthHelper();
            $commentHelper = new CommentHelper();
            $comments = $commentGateway->getComments($file->getId());
            $csrfToken = $csrfHelper->setCsrfToken();
            $authToken = $authHelper->getAuthCookie();
            if(isset($getParams['error']) && $getParams['error'] == 'comment') {
                $error = true;
            }
            $getId3 = new GetId3();
            $fileInfo = $getId3->setOptionMD5Data(true)
                ->setOptionMD5DataSource(true)
                ->setEncoding('UTF-8')
                ->analyze("storage/{$file->getFolder()}/{$fileHelper->getDiskName($file)}");
            return $this->container->get('view')->render($response, 'file.twig', [
                'file' => $file,
                'fileInfo' => $fileInfo,
                'fileHelper' => $fileHelper,
                'csrfToken' => $csrfToken,
                'authToken' => $authToken,
                'error' => $error,
                'comments' => $comments,
                'commentHelper' => $commentHelper]
            );
        } else {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }

    public function deleteFile(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        $csrfHelper = new CsrfHelper();
        if(isset($formData["token"]) && $csrfHelper->checkCsrfToken($formData["token"])) {
            $searchGateway = $this->container->get('SearchGateway');
            $fileMapper = $this->container->get('FileMapper');
            $fileHelper = new FileHelper();
            $file = $fileMapper->getFile($args['id']);
            if($fileHelper->fileExists($file) && $fileHelper->canDelete($file)) {
                $fileHelper->unlinkFile($file);
                $fileMapper->deleteFile($file);
                $searchGateway->deleteRtValue($file->getId());
            }
        }
        $responseHeader = $response->withHeader('Location', "/");
        return $responseHeader;
    }

}