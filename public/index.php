<?php
require_once("../vendor/autoload.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Database\FileMapper;
use \Filehosting\Database\SearchGateway;
use \Filehosting\Database\CommentMapper;
use \Filehosting\Config;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$container = $app->getContainer();

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

$container['config'] = function ($container) {
    $config = new Config();
    $config->loadFromFile("../config.ini");
    return $config;
};

$container['pdo'] = function ($container) {
    $cfg = $container->get('config');
    $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s",
        $cfg->getValue('db', 'host'),
        $cfg->getValue('db', 'port'),
        $cfg->getValue('db', 'name'));
    $pdo = new \PDO($dsn,
        $cfg->getValue('db', 'username'), $cfg->getValue('db', 'password'));
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

$container['SearchGateway'] = function ($container) {
    $cfg = $container->get('config');
    $dsn = sprintf("mysql:host=%s;port=%s",
        $cfg->getValue('sphinx', 'host'),
        $cfg->getValue('sphinx', 'port'));
    $pdo = new \PDO($dsn);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return new SearchGateway($pdo);
};

$container['CommentMapper'] = function ($container) {
    return new CommentMapper($container->get('pdo'));
};

$container['FileMapper'] = function ($container) {
    return new FileMapper($container->get('pdo'));
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $newResponse = $response->withStatus(404)->withHeader('Content-Type', 'text/html');
        return $container['view']->render($newResponse, 'error.twig',
            ['title' => "404 Страница не найдена",
            'messageTitle' => "Страница которую вы искали не найдена.",
            'messageHelp' => 'Проверьте правильность URL,',
            'displayErrors' => null,
            'debugInfo' => null]
        );
    };
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        switch(get_class($exception)) {
            case "Filehosting\Exception\FileUploadException":
                return $container['view']->render($response, 'upload.twig',
                        ['pageTitle' => "Загрузить файл",
                        'navLink' => "upload",
                        'error' => true,
                        'errorCode' => $exception->getCode(),
                        'errorMessage' => $exception->getMessage()]);
                break;
            default:
                $newResponse = $response->withStatus(503)->withHeader('Content-Type', 'text/html');
                return $container['view']->render($newResponse, 'error.twig',
                    ['title' => "503 Сервис временно недоступен",
                    'messageTitle' => "Что-то пошло не так.",
                    'messageHelp' => 'Обновите страницу через некоторое время, обратитесь к администратору,',
                    'displayErrors' => ini_get("display_errors"),
                    'debugInfo' => $exception->__toString()]);
                break;
        }
    };
};

set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

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
$app->get('/upload', function (Request $request, Response $response, $args)
{
    return $this->get('view')->render($response, 'upload.twig', ['sizeLimit' => $this->get('config')->getValue('app', 'sizeLimit')]);
});
$app->get('/search', '\Filehosting\Controller\SearchController');
$app->get('/file/{id:[0-9]+}', '\Filehosting\Controller\FileController:viewFile');
$app->get('/file/{id:[0-9]+}/preview', '\Filehosting\Controller\PreviewController');
$app->get('/file/get/{id:[0-9]+}', '\Filehosting\Controller\DownloadController');
$app->post('/upload', '\Filehosting\Controller\UploadController');
$app->post('/file/{id:[0-9]+}/comment/post', '\Filehosting\Controller\CommentController:postComment');
$app->post('/file/{id:[0-9]+}/comment/{commentId:[0-9]+}/post', '\Filehosting\Controller\CommentController:postReply');
$app->post('/file/{id:[0-9]+}/delete', '\Filehosting\Controller\FileController:deleteFile');

$app->run();
