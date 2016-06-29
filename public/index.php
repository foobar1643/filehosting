<?php

require("../app/init.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Middleware\LocaleMiddleware;
use \Filehosting\Middleware\CsrfMiddleware;
use \Filehosting\Helper\LanguageHelper;

$app = new \Slim\App($container);

$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates/', [
        'cache' => false
    ]);
    $view->addExtension(new Twig_Extensions_Extension_I18n());
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

$container['LocaleMiddleware'] = function ($container) {
    return new LocaleMiddleware($container->get('PathingHelper'));
};

$container['CsrfMiddleware'] = function ($container) {
    return new CsrfMiddleware;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $response = $response->withStatus(404)->withHeader('Content-Type', 'text/html');
        return $container['view']->render($response, 'error.twig',
            ['title' => _("404 Page not found"),
            'messageTitle' => _("Page you were looking for is not found on this server."),
            'messageHelp' => _('Check for errors in the URL.'),
            'displayErrors' => false,
            'debugInfo' => null]
        );
    };
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $response = $response->withStatus(503)->withHeader('Content-Type', 'text/html');
        error_log($exception->__toString());
        return $container['view']->render($response, 'error.twig',
            ['title' => _("503 Service temporarily unavailable"),
            'messageTitle' => _("Something went wrong."),
            'messageHelp' => _('Refresh the page after some time or contact the server administrator.'),
            'displayErrors' => ini_get("display_errors"),
            'debugInfo' => $exception->__toString()]);
    };
};

$app->add($container->get('LocaleMiddleware'));

$app->get('/', function (Request $request, Response $response, $args)
{
    $redirectLocale = \Locale::getDefault();
    $langHelper = new LanguageHelper($request);
    $userLocale = $langHelper->getUserLocale();
    if(!is_null($userLocale) && $langHelper->languageAvailable($userLocale)) {
        $redirectLocale = $userLocale;
    }
    $redirectLang = \Locale::parseLocale($redirectLocale);
    return $response->withRedirect("/{$redirectLang['language']}/");
});

$app->get('/{lang}/', function (Request $request, Response $response, $args)
{
    $fileMapper = $this->get("FileMapper");
    $lastFiles = $fileMapper->getLastFiles(10);
    $popularFiles = $fileMapper->getPopularFiles(10);
    return $this->get('view')->render($response, 'index.twig', [
        'lastFiles' => $lastFiles,
        'popularFiles' => $popularFiles,
        'lang' => $args['lang'],
        'langHelper' => new LanguageHelper($request)]);
});

$app->map(['GET', 'POST'], '/{lang}/settings/', function (Request $request, Response $response, $args)
{
    $langHelper = new LanguageHelper($request);
    if($request->isPost()) {
        $postVars = $request->getParsedBody();
        $selectedLocale = isset($postVars['language']) ? strval($postVars['language']) : NULL;
        if($langHelper->languageAvailable($selectedLocale)) {
            return $response->withRedirect("/{$selectedLocale}/settings/");
        }
    }
    return $this->get('view')->render($response, 'settings.twig',
        ['lang' => $args['lang'], 'langHelper' => $langHelper]);
});
$app->map(['GET', 'POST'], '/{lang}/upload/', '\Filehosting\Controller\UploadController');
$app->map(['GET', 'POST'], '/{lang}/file/{id:[0-9]+}/', '\Filehosting\Controller\FileController')->add($container->get('CsrfMiddleware'));
$app->post('/{lang}/file/{id:[0-9]+}/delete', '\Filehosting\Controller\FileController:deleteFile')->add($container->get('CsrfMiddleware'));
$app->get('/{lang}/search/', '\Filehosting\Controller\SearchController');
$app->get('/file/get/{id:[0-9]+}[/{filename}]', '\Filehosting\Controller\DownloadController');

$app->run();
