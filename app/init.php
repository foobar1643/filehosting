<?php

require(__DIR__ . "/../vendor/autoload.php");

use \GetId3\GetId3Core as GetId3;
use \Filehosting\Database\FileMapper;
use \Filehosting\Database\SearchGateway;
use \Filehosting\Database\CommentMapper;
use \Filehosting\Config;
use \Filehosting\Helper\FileHelper;
use \Filehosting\Helper\CommentHelper;
use \Filehosting\Helper\AuthHelper;
use \Filehosting\Helper\PathingHelper;
use \Filehosting\Helper\IdHelper;
use \Filehosting\Validation\Validation;
use \Filehosting\Helper\SearchHelper;
use \Filehosting\Helper\PreviewHelper;

set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function getServices(\Slim\Container $container)
{
    $container['config'] = function ($container) {
        $config = new Config();
        $config->loadFromFile(__DIR__ . "/../config.ini");
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

    $container['SearchHelper'] = function ($container) {
        return new SearchHelper();
    };

    $container['FileHelper'] = function ($container) {
        return new FileHelper($container);
    };

    $container['CommentHelper'] = function ($container) {
        return new CommentHelper($container);
    };

    $container['PathingHelper'] = function ($container) {
        return new PathingHelper(__DIR__);
    };

    $container['AuthHelper'] = function ($container) {
        return new AuthHelper();
    };

    $container['Validation'] = function ($container) {
        return new Validation($container);
    };

    $container['IdHelper'] = function ($container) {
        $getId3 = new GetId3();
        return new IdHelper($getId3, $container->get('PathingHelper'));
    };

    $container['PreviewHelper'] = function ($container) {
        $cfg = $container->get('config');
        return new PreviewHelper($container->get('PathingHelper'));
    };
    return $container;
}