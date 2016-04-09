<?php

$pdo = new \PDO("mysql:host=127.0.0.1;port=9306");
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$pdo->query("TRUNCATE RTINDEX rt_files");
