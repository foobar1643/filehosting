<?php

namespace Filehosting\Controller;

use \GetId3\GetId3Core as GetId3;
use Dflydev\FigCookies\FigRequestCookies;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\File;
use \Filehosting\Helper\IdHelper;

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
        $commentGateway = $this->container->get('CommentMapper');
        $commentHelper = $this->container->get('CommentHelper');
        $file = $fileMapper->getFile($args['id']);
        $getParams = $request->getQueryParams();
        if($file == null) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        $fileHelper = $this->container->get('FileHelper');
        $getId3 = new GetId3();
        $idHelper = new IdHelper($getId3, $fileHelper);
        $idHelper->analyzeFile($file);
        $rawComments = $commentGateway->getComments($file->getId());
        $treeComments = $commentHelper->makeTrees($rawComments);
        return $this->container->get('view')->render($response, 'file.twig', [
            'file' => $file,
            'idHelper' => $idHelper,
            'fileInfo' => $idHelper->getFileInfo(),
            'fileHelper' => $fileHelper,
            'csrfName' => $request->getAttribute('csrf_name'),
            'csrfValue' => $request->getAttribute('csrf_value'),
            'authCookie' => FigRequestCookies::get($request, 'auth'),
            'comments' => $treeComments,
            'commentHelper' => $commentHelper]);
    }

    public function deleteFile(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        $fileMapper = $this->container->get('FileMapper');
        $fileHelper = $this->container->get('FileHelper');
        $file = $fileMapper->getFile($args['id']);
        if($file != null && $fileHelper->canDelete($file->getAuthToken())) {
            $fileHelper->deleteFile($file);
        }
        $responseHeader = $response->withHeader('Location', "/");
        return $responseHeader;
    }

}