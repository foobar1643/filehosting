<?php

require("../app/init.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

session_start();

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$container = $app->getContainer();
$container = getServices($container);

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates/', [
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $newResponse = $response->withStatus(404)->withHeader('Content-Type', 'text/html');
        return $container['view']->render($newResponse, 'error.twig',
            ['title' => "404 Страница не найдена",
            'messageTitle' => "Страница которую вы искали не найдена.",
            'messageHelp' => 'Проверьте правильность URL,',
            'displayErrors' => false,
            'debugInfo' => null]
        );
    };
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $newResponse = $response->withStatus(503)->withHeader('Content-Type', 'text/html');
        error_log($exception->__toString());
        return $container['view']->render($newResponse, 'error.twig',
            ['title' => "503 Сервис временно недоступен",
            'messageTitle' => "Что-то пошло не так.",
            'messageHelp' => 'Обновите страницу через некоторое время, обратитесь к администратору,',
            'displayErrors' => ini_get("display_errors"),
            'debugInfo' => $exception->__toString()]);
    };
};

$app->get('/', function (Request $request, Response $response, $args)
{
    $fileMapper = $this->get("FileMapper");
    $lastFiles = $fileMapper->getLastFiles(10);
    $popularFiles = $fileMapper->getPopularFiles(10);
    return $this->get('view')->render($response, 'index.twig', [
        'lastFiles' => $lastFiles,
        'popularFiles' => $popularFiles]
    );
});
$app->map(['GET', 'POST'], '/upload', '\Filehosting\Controller\UploadController');
$app->get('/search', '\Filehosting\Controller\SearchController');
$app->get('/file/{id:[0-9]+}', '\Filehosting\Controller\FileController:viewFile')->add($container->get('csrf'));
$app->get('/file/get/{id:[0-9]+}[/{filename}]', '\Filehosting\Controller\DownloadController');
$app->post('/file/{id:[0-9]+}/comment/post', '\Filehosting\Controller\CommentController:postComment');
$app->post('/file/{id:[0-9]+}/comment/{commentId:[0-9]+}/post', '\Filehosting\Controller\CommentController:postReply');
$app->post('/file/{id:[0-9]+}/delete', '\Filehosting\Controller\FileController:deleteFile')->add($container->get('csrf'));

$app->run();
