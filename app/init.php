<?php

require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . '/../vendor/james-heinrich/getid3/getid3/getid3.php');

use \Slim\Container;
use Filehosting\Database\FileMapper;
use Filehosting\Database\SearchGateway;
use Filehosting\Database\CommentMapper;
use Filehosting\Config;
use Filehosting\Helper\FileHelper;
use Filehosting\Helper\CommentHelper;
use Filehosting\Helper\PathingHelper;
use Filehosting\Helper\IdHelper;
use Filehosting\Validation\Validation;
use Filehosting\Helper\SearchHelper;
use Filehosting\Helper\PreviewHelper;

set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

$configuration = [
    'settings' => [
        'displayErrorDetails' => ini_get("display_errors"),
    ],
];

$container = new Container($configuration);

$container['config'] = function () {
    $config = new Config();
    $config->loadFromFile(__DIR__ . "/../config.ini");
    return $config;
};

$container['pdo'] = function (Container $container): \PDO {
    $cfg = $container->get('config');
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $cfg->getValue('db', 'host'),
        $cfg->getValue('db', 'port'),
        $cfg->getValue('db', 'name')
    );

    $pdo = new \PDO(
        $dsn,
        $cfg->getValue('db', 'username'),
        $cfg->getValue('db', 'password')
    );
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

$container['SearchGateway'] = function (Container $container): SearchGateway {
    $cfg = $container->get('config');
    $dsn = sprintf(
        "mysql:host=%s;port=%s",
        $cfg->getValue('sphinx', 'host'),
        $cfg->getValue('sphinx', 'port')
    );
    $pdo = new \PDO($dsn);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return new SearchGateway($pdo);
};

$container['CommentMapper'] = function (Container $container): CommentMapper {
    return new CommentMapper($container->get('pdo'));
};

$container['FileMapper'] = function (Container $container): FileMapper {
    return new FileMapper($container->get('pdo'));
};

$container['SearchHelper'] = function (Container $container): SearchHelper {
    return new SearchHelper($container->get('SearchGateway'), $container->get('FileMapper'));
};

$container['FileHelper'] = function (Container $container): FileHelper {
    return new FileHelper($container);
};

$container['CommentHelper'] = function (Container $container): CommentHelper {
    return new CommentHelper($container->get('CommentMapper'));
};

$container['PathingHelper'] = function (): PathingHelper {
    return new PathingHelper(__DIR__);
};

$container['Validation'] = function (Container $container): Validation {
    return new Validation($container);
};

$container['IdHelper'] = function (Container $container): IdHelper {
    $getId3 = new getID3();
    return new IdHelper($getId3, $container->get('PathingHelper'));
};

$container['PreviewHelper'] = function (Container $container): PreviewHelper {
    return new PreviewHelper($container->get('PathingHelper'));
};
