<?php

require("../../app/init.php");

$container = new \Slim\Container();
$container = getServices($container);
$config = $container->get("config");

$dsn = sprintf("mysql:host=%s;port=%s",
    $config->getValue('sphinx', 'host'),
    $config->getValue('sphinx', 'port'));

$pdo = new \PDO($dsn);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$pdo->query("TRUNCATE RTINDEX rt_files");
